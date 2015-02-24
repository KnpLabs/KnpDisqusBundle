<?php

/*
* This file is part of the KnpDisqusBundle package.
*
* (c) KnpLabs <hello@knplabs.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Knp\Bundle\DisqusBundle;

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Message\RequestInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Disqus
{
    const DEFAULT_TIMEOUT = 5;

    /**
     * @var string
     */
    protected $baseUrl = 'https://disqus.com/api/3.0/';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $apiKey;
    /**
     * @var string
     */
    protected $shortname;
    /**
     * @var integer
     */
    protected $debug;
    /**
     * @var integer
     */
    protected $timeout;

    protected $id;

    /**
     * @var array
     */
    protected $options = array(
        'since'   => null,
        'cursor'  => null,
        'query'   => null,
        'include' => array('approved'),
        'order'   => 'desc',
        'limit'   => 100,
        'debug'   => 0,
    );

    /**
     * @param ContainerInterface $container
     * @param string $apiKey
     * @param string $secretKey
     * @param string $baseUrl
     * @param int    $debug
     */
    public function __construct(ContainerInterface $container, $apiKey, $secretKey = null, $baseUrl = null, $debug = 0, $timeout = self::DEFAULT_TIMEOUT)
    {
        $this->container = $container;

        $this->apiKey    = $apiKey;
        $this->secretKey = $secretKey;
        $this->debug     = $debug;

        if ($baseUrl) {
            $this->baseUrl = $baseUrl;
        }
        $this->timeout = $timeout;
    }

    /**
     * @param string $shortname
     * @param array  $options
     * @param string $fetch
     *
     * @return string
     */
    public function fetch($shortname, array $options, $fetch = 'threads/listPosts')
    {
        $this->shortname = $shortname;

        $options = $this->setOptions($options);

        $url = $this->buildUrl($options, $fetch);
        if ($this->container->has('knp_zend_cache.manager')) {
            $cache = $this->container->get('knp_zend_cache.manager');
            $cache = $cache->getCache($this->container->getParameter('knp_disqus.cache.'.$shortname));
            $key   = sha1($url);
            if (false === ($content = $cache->load($key))) {
                $content = json_decode($this->httpRequest($url), true);

                // we cache, even if we have a bad response
                // sometimes disqus goes down (request times out), and we don't want to keep making
                // this hanging request each time - just don't server-side load the comments for now
                $cache->save($content, $key);
            }
        } else {
            $content = json_decode($this->httpRequest($url), true);
        }

        // in case we got a bad response, fake some stuff
        if (!is_array($content) || !isset($content['response'])) {
            $content = array('response' => false);
        }

        /**
         * Huge temporary hack to make SSL possible. The cache URL breaks
         * down into 2 cases:
         *
         *  1) If the user does not have an avatar, then we get something
         *      like http://www.gravatar..., which ultimately redirects
         *      to http://mediacdn... showing the default image
         *  2) If the user *has* an avatar, then it's always http://mediacdn...
         *      and shows that user's avatar
         *
         * The problem is that in order to be able to swap out http://mediacdn
         * for https:///securecdn to make ssl work, the avatar must be normalized
         * here to always return http://mediacdn... To do that, we're checking
         * to see if it will be situation (1), and if it is, we hardcode
         * directly to the default avatar that lives at http://mediacdn.
         *
         * Overall, this is necessary because disqus really doesn't seem to
         * support https very well - we call their JS file via https, but
         * they still serve us a bunch of http images :/.
         */

        if (is_array($content['response'])) {
            foreach ($content['response'] as $key => $comment) {
                if (isset($comment['author']['avatar']['cache'])) {
                    $cache = $comment['author']['avatar']['cache'];
                    if (strpos($cache, 'http://www.gravatar.com/avatar.php') === 0) {
                        // we have a default URL, which we need to rewrite so that it's possible to make it secure if needed
                        $content['response'][$key]['author']['avatar']['cache'] = 'http://mediacdn.disqus.com/1341862960/images/noavatar92.png';
                    }
                }
            }
        }

        return $content;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return array(
            'id'         => $this->id,
            'shortname'  => $this->shortname,
            'debug'      => $this->debug,
            'api_key'    => $this->apiKey
        );
    }

    /**
     * @param array $parameters
     *
     * @return array
     */
    public function getSsoParameters($parameters)
    {
        $sso = array();

        if ($this->secretKey && isset($parameters['sso']) && isset($parameters['sso']['user'])) {
            $sso = $parameters['sso'];

            if (isset($sso['user'])) {
                $message = base64_encode(json_encode($sso['user']));
                $timestamp = time();
                $hmac = hash_hmac('sha1', "$message $timestamp", $this->secretKey);

                unset($sso['user']);
                $sso['auth'] = array(
                    'message' => $message,
                    'hmac' => $hmac,
                    'timestamp' => $timestamp,
                );
            }
        }

        return $sso;
    }

    /**
     * @param array  $options
     * @param string $fetch
     * @param string $format
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function buildUrl(array $options, $fetch, $format = 'json')
    {
        if (isset($options['identifier'])) {
            $this->id = array('identifier' => $options['identifier']);
            $id       = ':ident='.$options['identifier'];
        } elseif (isset($options['link'])) {
            $this->id = array('link' => $options['link']);
            $id       = ':link='.$options['link'];
        } elseif (isset($options['id'])) {
            $this->id = array('id' => $options['id']);
            $id       = '='.$options['id'];
        }

        if (!isset($id)) {
            throw new \InvalidArgumentException('You need to give an id.');
        }
        
        $limit = isset($options['limit']) ? $options['limit'] : 25;

        // @todo this should be more based on API docs (many params for many different fetch routes)
        return $this->baseUrl.$fetch.'.'.$format.'?thread'.$id.'&forum='.$this->shortname.'&api_key='.$this->apiKey.'&limit='.$limit;
    }

    /**
     * @param array $options
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function setOptions(array $options)
    {
        if (isset($options['order']) && !in_array($options['order'], array('asc', 'desc'))) {
            throw new \InvalidArgumentException(sprintf('Unknown `order` value used (%s), allowed are: asc, desc', $options['order']));
        }

        if (isset($options['include'])) {
            if (false !== strpos(',', $options['include'])) {
                $options['include'] = explode(',', $options['include']);
            }
            if (!is_array($options['include'])) {
                $options['include'] = array($options['include']);
            }

            $allowedIncludes = array('unapproved', 'approved', 'spam', 'deleted', 'flagged', 'highlighted');
            foreach ($options['include'] as $include) {
                $include = trim($include);
                if (!in_array($include, $allowedIncludes)) {
                    throw new \InvalidArgumentException(sprintf('Unknown `include` value used (%s), allowed are: %s', $include, implode(', ', $allowedIncludes)));
                }
            }

            $options['include'] = implode(', ', $options['include']);
        }

        // Maximum value of 100 (Disqus API limit)
        if (isset($options['limit']) && $options['limit'] > 100) {
            $options['limit'] = 100;
        }

        return array_merge($this->options, $options);
    }

    /**
     * @param string $url
     * @param mixed  $method
     *
     * @return string
     */
    protected function httpRequest($url, $method = RequestInterface::METHOD_GET)
    {
        $curl = new Curl();
        $curl->setTimeout($this->timeout);
        $buzz = new Browser($curl);

        try {
            $response = $buzz->call($url, $method);
        } catch (\RuntimeException $e) {
            return array(
                'response' => $e->getMessage(),
            );
        }

        return $response->getContent();
    }
}
