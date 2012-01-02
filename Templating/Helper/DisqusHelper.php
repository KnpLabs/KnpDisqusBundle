<?php

namespace Knp\Bundle\KnpDisqusBundle\Templating\Helper;

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

    public function render(array $parameters = array(), $name = null)
    {
        $content = $this->disqus->fetch($parameters);

        return $this->renderTemplate($name, array('content' => $content));
    }

    public function thread(array $parameters = array(), $name = null)
    {
        return $this->render($parameters, $name ?: 'KnpDisqusBundle::thread.html.php');
    }

    /**
     * @param array $parameters An array of parameters for the template
     * @param string $name A template name
     *
     * @return string
     */
    public function comments(array $parameters = array(), $name = null)
    {
        return $this->render($parameters, $name ?: 'KnpDisqusBundle::comments.html.php');
    }

    /**
     * @param array $parameters An array of parameters for the template
     * @param string $name A template name
     *
     * @return string
     */
    public function commentCount(array $parameters = array(), $name = null)
    {
        return $this->render($parameters, $name ?: 'KnpDisqusBundle::commentCount.html.php');
    }

    public function getName()
    {
        return 'disqus';
    }

    protected function renderTemplate($name, array $parameters)
    {
        return $this->templating->render($name, $parameters);
    }
}
