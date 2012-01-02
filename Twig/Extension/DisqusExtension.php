<?php

namespace Knp\Bundle\KnpDisqusBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class DisqusExtension extends \Twig_Extension
{
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        return array(
            'knp_disqus_render'        => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html'))),
            'knp_disqus_thread'        => new \Twig_Function_Method($this, 'renderThread', array('is_safe' => array('html'))),
            'knp_disqus_comments'      => new \Twig_Function_Method($this, 'renderComments', array('is_safe' => array('html'))),
            'knp_disqus_comment_count' => new \Twig_Function_Method($this, 'renderCommentCount', array('is_safe' => array('html'))),
        );
    }

    public function render($name, $parameters = array(), $template = 'KnpDisqusBundle::list.html.twig')
    {
        return $this->container->get('knp_disqus.helper')->render($name, $parameters, $template);
    }

    public function renderThread($name, $parameters = array(), $template = 'KnpDisqusBundle::thread.html.twig')
    {
        return $this->render($name, $parameters, $template);
    }

    public function renderComments($name, $parameters = array(), $template = 'KnpDisqusBundle::comments.html.twig')
    {
        return $this->render($name, $parameters, $template);
    }

    public function renderCommentCount($name, $parameters = array(), $template = 'KnpDisqusBundle::commentCount.html.twig')
    {
        return $this->render($name, $parameters, $template);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'knp_disqus';
    }
}
