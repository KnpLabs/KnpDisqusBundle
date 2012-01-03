<?php

namespace Knp\Bundle\DisqusBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
                ->booleanNode('debug')->defaultValue($this->debug)->end()
            ->end()
        ;

        $this->addForumsSection($rootNode);

        return $treeBuilder;
    }

    private function addForumsSection(ArrayNodeDefinition $node)
    {
        $node
            ->fixXmlConfig('forum')
            ->children()
                ->arrayNode('forums')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('shortname')->defaultNull()->end()
                        ->scalarNode('cache')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
