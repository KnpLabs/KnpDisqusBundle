<?php

/*
 * This file is part of the KnpDisqusBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\DisqusBundle\Client;

use Knp\Bundle\DisqusBundle\Model\DisqusConfig;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DisqusClient implements DisqusClientInterface
{
    /**
     * @var DisqusConfig
     */
    private $config;
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var array
     */
    private $options = [
        'since' => null,
        'cursor' => null,
        'query' => null,
        'include' => ['approved'],
        'order' => 'desc',
        'limit' => 100,
        'debug' => 0,
    ];

    public function __construct(DisqusConfig $config)
    {
        $this->config = $config;
        $this->httpClient = HttpClient::createForBaseUri(DisqusConfig::DISQUS_API_BASE_URI);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(string $shortname, array $options, string $fetch = 'threads/listPosts'): array
    {
        $options = $this->setOptions($options);

        $url = $this->buildUrl($shortname, $options, $fetch);

        $content = $this->request($url);

        // in case we got a bad response, fake some stuff
        if (!\is_array($content) || !isset($content['response'])) {
            throw new \RuntimeException(sprintf('Somehow we got bad response requesting thi url: "%s"', $url));
        }

        /**
         * Huge temporary hack to make SSL possible. The cache URL breaks
         * down into 2 cases:.
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
        if (\is_array($content['response'])) {
            foreach ($content['response'] as $key => $comment) {
                if (isset($comment['author']['avatar']['cache'])) {
                    $cache = $comment['author']['avatar']['cache'];
                    if (0 === strpos($cache, 'http://www.gravatar.com/avatar.php')) {
                        // we have a default URL, which we need to rewrite so that it's possible to make it secure if needed
                        $content['response'][$key]['author']['avatar']['cache'] = 'http://mediacdn.disqus.com/1341862960/images/noavatar92.png';
                    }
                }
            }
        }

        return $content;
    }

    private function buildUrl(string $shortname, array $options, string $fetch, string $format = 'json'): string
    {
        // @todo this should be more based on API docs (many params for many different fetch routes)
        //$fetch.'.'.$format.'?thread'.$id.'&forum='.$shortname.'&api_key='.$this->apiKey.'&limit='.$limit;
        return sprintf(
            '%s.%s?%s&forum%s&api_key=%s&limit=%s',
            $fetch,
            $format,
            $this->config->getThreadIdentifierParam($options, true),
            $shortname,
            $this->config->getApiKey(),
            $options['limit'] ?? 25
        );
    }

    private function setOptions(array $options): array
    {
        if (isset($options['order']) && !\in_array($options['order'], ['asc', 'desc'])) {
            throw new \InvalidArgumentException(sprintf('Unknown `order` value used (%s), allowed are: asc, desc', $options['order']));
        }

        if (isset($options['include'])) {
            if (false !== strpos(',', $options['include'])) {
                $options['include'] = explode(',', $options['include']);
            }
            if (!\is_array($options['include'])) {
                $options['include'] = [$options['include']];
            }

            $allowedIncludes = ['unapproved', 'approved', 'spam', 'deleted', 'flagged', 'highlighted'];
            foreach ($options['include'] as $include) {
                $include = trim($include);
                if (!\in_array($include, $allowedIncludes)) {
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
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function request(string $url, string $method = 'GET'): array
    {
        return $this->httpClient->request($method, $url)->toArray();
    }
}
