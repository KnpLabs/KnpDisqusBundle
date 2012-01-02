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
use Symfony\Component\Config\FileLocator;

class KnpDisqusExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->setParameter('knp_disqus.api_key', $configs[0]['api_key']);
        $container->setParameter('knp_disqus.debug', array_key_exists('debug', $configs[0]) ? (bool)$configs[0]['debug'] : $container->getParameter('kernel.debug'));

        if ($container->hasParameter('knp_zend_cache')) {
            foreach ($configs[0]['forums'] as $config) {
                if (isset($config['cache'])) {
                    if (!$container->hasParameter('knp_zend_cache.templates.'.$config['cache'])) {
                        throw new \InvalidArgumentException('Unknown cache template key used: '.$config['cache']);
                    }

                    $container->setParameter('knp_disqus.cache.'.$config['shortname'], $config['cache']);
                }
            }
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
