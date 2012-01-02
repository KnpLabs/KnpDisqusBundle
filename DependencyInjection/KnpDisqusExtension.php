<?php

/*
* This file is part of the KnpDisqusBundle package.
*
* (c) KnpLabs <hello@knplabs.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Knp\Bundle\KnpDisqusBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;

class KnpDisqusExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->setParameter('knp_disqus.api_key', $configs[0]['api_key']);
        $container->setParameter('knp_disqus.debug', array_key_exists('debug', $configs[0]) ? (bool)$configs[0]['debug'] : $container->getParameter('kernel.debug'));

        $forums = array();
        foreach ($configs as $config) {
            $forums = array_merge($forums, $config['forums']);
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
