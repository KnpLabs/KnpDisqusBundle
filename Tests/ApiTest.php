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

use Knp\Bundle\DisqusBundle\Disqus;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    protected $disqus;

    protected function setUp()
    {
        $container = $this->getMock('Symfony\\Component\\DependencyInjection\\ContainerInterface');

        $this->disqus = new Disqus($container, 'PUBLIC_KEY');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyOptions()
    {
        $this->disqus->fetch('test', array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidOrderOption()
    {
        $this->disqus->fetch('test', array('order' => 'lorem'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidIncludeOption()
    {
        $this->disqus->fetch('test', array('include' => 'lorem'));
    }

    public function testFetch()
    {
        if (false === class_exists('Buzz\Browser')) {
            $this->markTestAsSkipped('Buzz library is required.');
        }

        $content = $this->disqus->fetch('test', array('identifier' => 'lorem'));

        $this->assertEquals(array('code' => 5, 'response' => 'Invalid API key'), $content);
    }
}
