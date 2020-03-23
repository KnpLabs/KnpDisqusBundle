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
        $treeBuilder = new TreeBuilder('knp_disqus');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('knp_disqus');
        }

        $rootNode
            ->children()
                ->scalarNode('api_key')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('secret_key')->defaultValue('')->end()
                ->scalarNode('base_url')->defaultValue('')->end()
                ->booleanNode('debug')->defaultValue($this->debug)->end()
            ->end();

        $this->addForumsSection($rootNode);

        return $treeBuilder;
    }

    private function addForumsSection(ArrayNodeDefinition $node)
    {
        $node
            ->fixXmlConfig('forum')
            ->children()
                ->arrayNode('forums')
                    ->useAttributeAsKey('shortname')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('shortname')->defaultNull()->end()
                        ->scalarNode('cache')->defaultNull()->end()
                    ->end()
                ->end()
            ->end();
    }
}
