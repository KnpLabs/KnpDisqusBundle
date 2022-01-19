<?php

/*
 * This file is part of the KnpDisqusBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\DisqusBundle\Model;

/**
 * @internal
 */
final class DisqusConfig
{
    const DISQUS_API_BASE_URI = 'https://disqus.com/api/3.0/';

    private $apiKey;
    private $secretKey;
    private $debug;

    public function __construct(string $apiKey, ?string $secretKey, bool $debug)
    {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->debug = $debug;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @return array|string
     */
    public function getThreadIdentifierParam(array $options, bool $asQueryParam = false)
    {
        if (\array_key_exists('identifier', $options)) {
            return $asQueryParam
                ? 'thread:ident='.$options['identifier']
                : ['identifier' => $options['identifier']];
        } elseif (\array_key_exists('link', $options)) {
            return $asQueryParam
                ? 'thread:link='.$options['link']
                : ['link' => $options['link']];
        } elseif (\array_key_exists('id', $options)) {
            return $asQueryParam
                ? 'thread='.$options['id']
                : ['id' => $options['id']];
        }

        throw new \InvalidArgumentException('You need to give an id.');
    }

    public function getTemplateParameters(string $shortname, array $parameters, array $content, ?string $error): array
    {
        $parameters['id'] = $parameters['id'] ?? $this->getThreadIdentifierParam($parameters);
        $parameters['api_key'] = $parameters['api_key'] ?? $this->getApiKey();
        $parameters['debug'] = $parameters['debug'] ?? $this->isDebug();
        $parameters['shortname'] = $shortname;
        $parameters['error'] = $error;
        $parameters['content'] = $content;
        $parameters['sso'] = $this->getSsoParameters($parameters);

        return $parameters;
    }

    private function getSsoParameters(array $parameters): array
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
}
