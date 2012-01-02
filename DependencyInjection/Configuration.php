<?php

namespace Knp\Bundle\KnpDisqusBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private $debug;

    public function __construct($debug)
    {
        $this->debug = $debug;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('knp_disqus');

        $rootNode
            ->children()
            ->scalarNode('api_key')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('shortname')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('debug')->defaultValue($this->debug)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
