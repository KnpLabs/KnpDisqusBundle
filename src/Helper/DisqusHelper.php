<?php

/*
 * This file is part of the KnpDisqusBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\DisqusBundle\Helper;

use Knp\Bundle\DisqusBundle\Client\DisqusClientInterface;
use Knp\Bundle\DisqusBundle\Model\DisqusConfig;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class DisqusHelper implements RuntimeExtensionInterface
{
    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var DisqusConfig
     */
    private $config;
    /**
     * @var DisqusClientInterface
     */
    private $disqus;

    public function __construct(Environment $twig, DisqusConfig $config, DisqusClientInterface $disqus)
    {
        $this->twig = $twig;
        $this->config = $config;
        $this->disqus = $disqus;
    }

    public function render(string $shortname, array $parameters = [], string $template = '@KnpDisqus/list.html.twig'): string
    {
        try {
            $content = $this->disqus->fetch($shortname, $parameters);
        } catch (\Exception $e) {
            if ($this->config->isDebug()) {
                $error = $e->getMessage();
            } else {
                $error = 'Oops! Seems there are problem with access to disqus.com. Please refresh the page in a few minutes.';
            }
        }

        return $this->twig->render(
            $template,
            $this->config->getTemplateParameters(
                $shortname,
                $parameters,
                $content ?? [],
                $error ?? null
            )
        );
    }
}
