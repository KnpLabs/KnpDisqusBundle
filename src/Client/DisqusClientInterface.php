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

/**
 * @author Sadicov Vladimir <vladimir@symfonycasts.com>
 */
interface DisqusClientInterface
{
    /**
     * @return array the JSON-decoded data from whatever Disqus endpoint that was fetched
     */
    public function fetch(string $shortname, array $options, string $fetch = 'threads/listPosts'): array;

    public function getSsoParameters(array $parameters): array;

    public function getParameters(): array;
}
