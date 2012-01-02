<?php

/*
* This file is part of the KnpDisqusBundle package.
*
* (c) KnpLabs <hello@knplabs.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Knp\Bundle\DisqusBundle\Templating\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\Helper\Helper;

class DisqusHelper extends Helper
{
    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templating;
    protected $disqus;

    public function __construct(EngineInterface $templating, $disqus)
    {
        $this->templating = $templating;
        $this->disqus     = $disqus;
    }

    public function render($name, array $parameters = array(), $template = 'KnpDisqusBundle::list.html.php')
    {
        $content = $this->disqus->fetch($name, $parameters);

        return $this->templating->render($template, array('content' => $content) + $parameters + $this->disqus->getParameters());
    }

    public function thread($name, array $parameters = array(), $template = 'KnpDisqusBundle::thread.html.php')
    {
        return $this->render($name, $parameters, $template);
    }

    public function comments($name, array $parameters = array(), $template = 'KnpDisqusBundle::comments.html.php')
    {
        return $this->render($name, $parameters, $template);
    }

    public function commentCount($name, array $parameters = array(), $template = 'KnpDisqusBundle::commentCount.html.php')
    {
        return $this->render($name, $parameters, $template);
    }

    public function getName()
    {
        return 'knp_disqus';
    }
}
