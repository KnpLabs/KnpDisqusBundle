<?php

/*
 * This file is part of the KnpDisqusBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\DisqusBundle\Tests\IntegrationTests;

use Knp\Bundle\DisqusBundle\Client\DisqusClientInterface;
use Knp\Bundle\DisqusBundle\Tests\KnpDisqusBundleTestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Vladimir Sadicov <vladimir@symfonycasts.com>
 */
final class KnpDisqusBundleAutowireTest extends TestCase
{
    public function testVerifyEmailBundleInterfaceIsAutowiredByContainer(): void
    {
        $builder = new ContainerBuilder();
        $builder->autowire(DisqusClientAutowireTest::class)
            ->setPublic(true)
        ;

        $kernel = new KnpDisqusBundleTestKernel($builder);
        $kernel->boot();

        $container = $kernel->getContainer();
        $container->get(DisqusClientAutowireTest::class);

        $this->expectNotToPerformAssertions();
    }
}

class DisqusClientAutowireTest
{
    public function __construct(DisqusClientInterface $client)
    {
    }
}
