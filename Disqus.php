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
use Buzz\Message\Request;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Disqus
{
    /**
     * @var string
     */
    const DISQUS_URL = 'https://disqus.com/api/3.0/';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
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

    protected $id;

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
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param string $apiKey
     * @param int    $debug
     */
    public function __construct(ContainerInterface $container, $apiKey, $secretKey = null, $debug = 0)
    {
        $this->container = $container;

        $this->apiKey    = $apiKey;
        $this->secretKey = $secretKey;
        $this->debug     = $debug;
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
        if ($this->container->has('knp_zend_manager')) {
            $cache = $this->container->get('knp_zend_manager');
            $cache = $cache->getCache($this->container->getParameter('knp_disqus.cache.'.$shortname));
            $key   = sha1($url);
            if (false === ($content = $cache->load($key))) {
                $content = json_decode($this->httpRequest($url), true);

                $cache->save($content, $key);
            }
        } else {
            $content = json_decode($this->httpRequest($url), true);
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

        // @todo this should be more based on API docs (many params for many different fetch routes)
        return self::DISQUS_URL.$fetch.'.'.$format.'?thread'.$id.'&forum='.$this->shortname.'&api_key='.$this->apiKey;
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
    protected function httpRequest($url, $method = Request::METHOD_GET)
    {
        $buzz = new Browser(new Curl());

        $response = $buzz->call($url, $method);

        return $response->getContent();
    }
}
