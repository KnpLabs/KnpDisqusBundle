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

use Knp\Bundle\DisqusBundle\Disqus;
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
        $this->assertEquals('SECRET_KEY', $this->configuration->getParameter('knp_disqus.secret_key'));
    }

    public function testSecretKeyParameter()
    {
        $this->createConfiguration('empty');

        $this->assertEquals('SECRET_KEY', $this->configuration->getParameter('knp_disqus.secret_key'));
    }

    public function testBaseUrlParameter()
    {
        $container = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerInterface');

        $disqus = new Disqus($container, 'PUBLIC_KEY', null, 'http://disqus.com/api/3.0/');

        $content = $disqus->fetch('test', array('identifier' => 'lorem'));

        $this->assertEquals(array('code' => 5, 'response' => 'Invalid API key'), $content);
    }

    public function testDebugParameter()
    {
        $this->createConfiguration('empty');

        $this->assertEquals(0, $this->configuration->getParameter('knp_disqus.debug'));

        $this->createConfiguration('empty', 1);

        $this->assertEquals(1, $this->configuration->getParameter('knp_disqus.debug'));
    }

    public function testCurlTimeoutParameter()
    {
        $this->createConfiguration('empty');

        $this->assertEquals(Disqus::DEFAULT_TIMEOUT, $this->configuration->getParameter('knp_disqus.curl_timeout'));

        $this->createConfiguration('full');

        $this->assertEquals(4, $this->configuration->getParameter('knp_disqus.curl_timeout'));
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
secret_key: SECRET_KEY
EOF;
                break;

            case 'full':
                $yaml = <<<EOF
api_key: PUBLIC_KEY
secret_key: SECRET_KEY
curl_timeout: 4
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
