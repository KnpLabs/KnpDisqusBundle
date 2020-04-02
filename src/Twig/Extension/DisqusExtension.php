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
    private $disqusHelper;

    public function __construct(DisqusHelper $disqusHelper)
    {
        $this->disqusHelper = $disqusHelper;
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('knp_disqus_render', [$this, 'render'], ['is_safe' => ['html']]),
        ];
    }

    public function render($name, $parameters = [], $template = '@KnpDisqus/list.html.twig')
    {
        return $this->disqusHelper->render($name, $parameters, $template);
    }
}
