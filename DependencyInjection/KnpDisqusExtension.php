<?php

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
        $container->setParameter('knp_disqus.debug', isset($configs[0]['debug']) ? $configs[0]['debug'] : $container->getParameter('kernel.debug'));

        $forums = array();
        foreach ($configs as $config) {
            $forums = array_merge($forums, $config['forums']);
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
