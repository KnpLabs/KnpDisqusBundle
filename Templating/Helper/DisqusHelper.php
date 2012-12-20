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

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DisqusHelper extends Helper
{
    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $templating;
    protected $disqus;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    protected $container;

    public function __construct(EngineInterface $templating, $disqus, ContainerInterface $container)
    {
        $this->templating = $templating;
        $this->disqus     = $disqus;
        $this->container  = $container;
    }

    public function render($name, array $parameters = array(), $template = 'KnpDisqusBundle::list.html.php')
    {
        try {
            $content = $this->disqus->fetch($name, $parameters);
        } catch (\Exception $e) {
            if ($this->container->getParameter('kernel.environment') == 'dev') {
                $error = $e->getMessage();
            } else {
                $error = 'Oops! Seems there are problem with access to discus.com. Please refresh the page in a few minutes.';
            }
        }

        $sso = $this->disqus->getSsoParameters($parameters);

        $parameters['error'] = isset($error) ? $error : '';
        $parameters['content'] = isset($content) ? $content : '';
        $parameters = $parameters + $this->disqus->getParameters();
        $parameters['sso'] = $sso;

        return $this->templating->render($template, $parameters);
    }

    public function getName()
    {
        return 'knp_disqus';
    }
}
