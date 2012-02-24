<?php

/*
 * This file is part of the KnpDisqusBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\DisqusBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

use Knp\Bundle\DisqusBundle\DependencyInjection\KnpDisqusExtension;

class KnpDisqusExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testApiKeyIsRequired()
    {
        $this->createConfiguration('error');
    }

    public function testApiKeyParameter()
    {
        $this->createConfiguration('empty');

        $this->assertEquals('PUBLIC_KEY', $this->configuration->getParameter('knp_disqus.api_key'));
    }

    public function testDebugParameter()
    {
        $this->createConfiguration('empty');

        $this->assertEquals(0, $this->configuration->getParameter('knp_disqus.debug'));

        $this->createConfiguration('empty', 1);

        $this->assertEquals(1, $this->configuration->getParameter('knp_disqus.debug'));
    }

    public function testCacheKeyParameter()
    {
        $this->createConfiguration('full');

        $this->assertEquals(array('lorem', 'foobar'), $this->configuration->getParameter('knp_disqus.forums'));
    }

    protected function createConfiguration($type, $debug = 0)
    {
        $this->configuration = new ContainerBuilder();
        $this->configuration->setParameter('kernel.debug', $debug);

        $loader = new KnpDisqusExtension();
        $config = $this->getConfig($type);
        $loader->load(array($config), $this->configuration);

        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    protected function getConfig($type)
    {
        switch ($type) {
            case 'empty':
                $yaml = <<<EOF
api_key: PUBLIC_KEY
EOF;
                break;

            case 'full':
                $yaml = <<<EOF
api_key: PUBLIC_KEY
forums:
    lorem:
        cache: test_cache_key

    test:
        shortname: foobar
EOF;
                break;

            case 'error':
                $yaml = <<<EOF
forums:
    test:
        shortname: foobar
EOF;
                break;
        }

        $parser = new Parser();

        return $parser->parse($yaml);
    }
}
