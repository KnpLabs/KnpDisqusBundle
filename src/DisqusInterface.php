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

/**
 * @author Sadicov Vladimir <vladimir@symfonycasts.com>
 */
interface DisqusInterface
{
    public function fetch(string $shortname, array $options, string $fetch = 'threads/listPosts'): array;
}
