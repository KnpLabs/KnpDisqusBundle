<?php

namespace Knp\Bundle\DisqusBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

use Knp\Bundle\DisqusBundle\DependencyInjection\KnpDisqusExtension;

class KnpDisqusExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testApiKeyParameter()
    {
        $this->createConfiguration('empty');

        $this->assertEquals('PUBLIC_KEY', $this->configuration->getParameter('knp_disqus.api_key'));
    }

    public function testCacheKeyParameter()
    {
        $this->createConfiguration('full');

        $this->assertEquals(array('lorem', 'foobar'), $this->configuration->getParameter('knp_disqus.forums'));
    }

    /**
     * @return ContainerBuilder
     */
    protected function createConfiguration($type)
    {
        $this->configuration = new ContainerBuilder();
        $this->configuration->setParameter('kernel.debug', 0);

        $loader = new KnpDisqusExtension();
        $config = $type =='empty' ? $this->getEmptyConfig() : $this->getFullConfig();
        $loader->load(array($config), $this->configuration);

        $this->assertTrue($this->configuration instanceof ContainerBuilder);
    }

    protected function getEmptyConfig()
    {
        $yaml = <<<EOF
api_key: PUBLIC_KEY
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    protected function getFullConfig()
    {
        $yaml = <<<EOF
api_key: PUBLIC_KEY
forums:
    lorem:
        shortname: lorem
        cache: test_cache_key

    test:
        shortname: foobar
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }
}
