<?php

/*
* This file is part of the KnpDisqusBundle package.
*
* (c) KnpLabs <hello@knplabs.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Knp\Bundle\DisqusBundle\Twig\Extension;

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
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
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

        if (\Twig_Environment::MAJOR_VERSION === 1) {
            return array(
                'knp_disqus_render' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html'))),
            );
        } elseif (\Twig_Environment::MAJOR_VERSION === 2) {
            return array(
                new \Twig_SimpleFunction('knp_disqus_render', array($this, 'render'), array('is_safe' => array('html'))),
            );
        }

        return array();
    }

    public function render($name, $parameters = array(), $template = 'KnpDisqusBundle::list.html.twig')
    {
        return $this->container->get('knp_disqus.helper')->render($name, $parameters, $template);
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
