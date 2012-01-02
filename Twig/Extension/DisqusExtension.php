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

    public function render($parameters = array())
    {
        return $this->container->get('knp_disqus.helper')->render($parameters, 'KnpDisqusBundle::list.html.twig');
    }

    public function renderThread($parameters = array(), $name = null)
    {
        return $this->container->get('knp_disqus.helper')->render($parameters, $name ?: 'KnpDisqusBundle::thread.html.twig');
    }

    public function renderComments($parameters = array(), $name = null)
    {
        return $this->container->get('knp_disqus.helper')->render($parameters, $name ?: 'KnpDisqusBundle::comments.html.twig');
    }

    public function renderCommentCount($parameters = array(), $name = null)
    {
        return $this->container->get('knp_disqus.helper')->render($parameters, $name ?: 'KnpDisqusBundle::commentCount.html.twig');
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'disqus';
    }
}
