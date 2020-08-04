<?php


namespace Knp\Bundle\DisqusBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false !== $container->hasDefinition('twig')) {
            return;
        }

        $container->removeDefinition('knp_disqus.helper');
    }
}
