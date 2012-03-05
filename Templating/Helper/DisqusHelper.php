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
        try {
            $content = $this->disqus->fetch($name, $parameters);
        } catch (\Exception $e) {
            $content = '';
        }

        $sso = $this->disqus->getSsoParameters($parameters);

        $parameters['content'] = $content;
        $parameters = $parameters + $this->disqus->getParameters();
        $parameters['sso'] = $sso;

        return $this->templating->render($template, $parameters);
    }

    public function getName()
    {
        return 'knp_disqus';
    }
}
