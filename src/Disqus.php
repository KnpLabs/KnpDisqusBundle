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

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Disqus
{
    private $baseUrl = 'https://disqus.com/api/3.0/';
    private $apiKey;
    private $secretKey;
    private $debug;

    private $shortname;

    private $id;

    private $options = [
        'since'   => null,
        'cursor'  => null,
        'query'   => null,
        'include' => ['approved'],
        'order'   => 'desc',
        'limit'   => 100,
        'debug'   => 0,
    ];

    public function __construct(string $apiKey, string $secretKey = null, string $baseUrl = null, $debug = 0)
    {
        $this->apiKey    = $apiKey;
        $this->secretKey = $secretKey;
        $this->debug     = $debug;

        if ($baseUrl) {
            $this->baseUrl = $baseUrl;
        }
    }

    public function fetch(string $shortname, array $options, string $fetch = 'threads/listPosts'): array
    {
        $this->shortname = $shortname;

        $options = $this->setOptions($options);

        $url = $this->buildUrl($options, $fetch);

        $content = $this->httpRequest($url);

        // in case we got a bad response, fake some stuff
        if (!is_array($content) || !isset($content['response'])) {
            $content = ['response' => false];
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

    public function getParameters(): array
    {
        return [
            'id'         => $this->id,
            'shortname'  => $this->shortname,
            'debug'      => $this->debug,
            'api_key'    => $this->apiKey
        ];
    }

    public function getSsoParameters(array $parameters): array
    {
        $sso = [];

        if ($this->secretKey && isset($parameters['sso']) && isset($parameters['sso']['user'])) {
            $sso = $parameters['sso'];

            if (isset($sso['user'])) {
                $message = base64_encode(json_encode($sso['user']));
                $timestamp = time();
                $hmac = hash_hmac('sha1', "$message $timestamp", $this->secretKey);

                unset($sso['user']);
                $sso['auth'] = [
                    'message' => $message,
                    'hmac' => $hmac,
                    'timestamp' => $timestamp,
                ];
            }
        }

        return $sso;
    }

    private function buildUrl(array $options, string $fetch, string $format = 'json'): string
    {
        if (isset($options['identifier'])) {
            $this->id = [
                'identifier' => $options['identifier']
            ];
            $id = ':ident='.$options['identifier'];
        } elseif (isset($options['link'])) {
            $this->id = [
                'link' => $options['link']
            ];
            $id = ':link='.$options['link'];
        } elseif (isset($options['id'])) {
            $this->id = [
                'id' => $options['id']
            ];
            $id = '='.$options['id'];
        }

        if (!isset($id)) {
            throw new \InvalidArgumentException('You need to give an id.');
        }

        $limit = isset($options['limit']) ? $options['limit'] : 25;

        // @todo this should be more based on API docs (many params for many different fetch routes)
        return $this->baseUrl.$fetch.'.'.$format.'?thread'.$id.'&forum='.$this->shortname.'&api_key='.$this->apiKey.'&limit='.$limit;
    }

    private function setOptions(array $options): array
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

    private function httpRequest(string $url, string $method = 'GET'): ?array
    {
        $httpClient = HttpClient::create();

        try {
            $response = $httpClient->request($method, $url);
        } catch (TransportExceptionInterface $e) {
            return null;
        }

        return $response->toArray();
    }
}
