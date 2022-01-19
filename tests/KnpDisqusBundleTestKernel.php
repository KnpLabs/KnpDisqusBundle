<?php

/*
 * This file is part of the KnpDisqusBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\DisqusBundle\Tests;

use Knp\Bundle\DisqusBundle\KnpDisqusBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Vladimir Sadicov <vladimir@symfonycasts.com>
 *
 * @internal
 */
class KnpDisqusBundleTestKernel extends Kernel
{
    private $builder;

    public function __construct(ContainerBuilder $builder = null)
    {
        $this->builder = $builder;

        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new KnpDisqusBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        if (null === $this->builder) {
            $this->builder = new ContainerBuilder();
        }

        $builder = $this->builder;

        $loader->load(function (ContainerBuilder $container) use ($builder) {
            $container->merge($builder);
            $container->loadFromExtension(
                'framework',
                [
                    'secret' => 'foo'
                ]
            );
            $container->loadFromExtension(
                'knp_disqus',
                [
                    'api_key' => 'foo',
                ]
            );

            $container->register('kernel', static::class)
                ->setPublic(true)
            ;
        });
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/cache'.spl_object_hash($this);
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/logs'.spl_object_hash($this);
    }
}
