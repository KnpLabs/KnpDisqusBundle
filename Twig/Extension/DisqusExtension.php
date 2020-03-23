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

use Knp\Bundle\DisqusBundle\Helper\DisqusHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DisqusExtension extends AbstractExtension
{
    /**
     * @var DisqusHelper
     */
    protected $disqusHelper;

    public function __construct($disqusHelper)
    {
        $this->disqusHelper = $disqusHelper;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        return array(
            new TwigFunction('knp_disqus_render', array($this, 'render'), array('is_safe' => array('html'))),
        );
    }

    public function render($name, $parameters = array(), $template = '@KnpDisqus/list.html.twig')
    {
        return $this->disqusHelper->render($name, $parameters, $template);
    }
}
