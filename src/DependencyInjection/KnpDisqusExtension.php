<?php

/*
* This file is part of the KnpDisqusBundle package.
*
* (c) KnpLabs <hello@knplabs.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Knp\Bundle\DisqusBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class KnpDisqusExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $forums = array();
        foreach ($config['forums'] as $shortname => $data) {
            $forums[] = $shortname;

            if (isset($data['cache'])) {
                $container->setParameter('knp_disqus.cache.'.$shortname, $data['cache']);
            }
        }
        $container->setParameter('knp_disqus.forums', $forums);

        $container->setParameter('knp_disqus.api_key', $config['api_key']);
        if (isset($config['secret_key'])) {
            $container->setParameter('knp_disqus.secret_key', $config['secret_key']);
        }
        if (isset($config['base_url'])) {
            $container->setParameter('knp_disqus.base_url', $config['base_url']);
        }
    }
}
