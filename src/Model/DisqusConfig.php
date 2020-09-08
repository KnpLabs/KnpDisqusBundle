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

    private $id;

    public function __construct(string $apiKey, ?string $secretKey, bool $debug)
    {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->debug = $debug;
    }

    public function setId(array $id): void
    {
        $this->id = $id;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function getParameters(): array
    {
        return [
            'id' => $this->id,
            'debug' => $this->debug,
            'api_key' => $this->apiKey,
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
}
