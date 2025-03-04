<?php

namespace Oro\Bundle\ApiBundle\Twig;

use Nelmio\ApiDocBundle\Twig\Extension\MarkdownExtension as BaseMarkdownExtension;

/**
 * Overrides \Nelmio\ApiDocBundle\Twig\Extension\MarkdownExtension to properly initialize Markdown parser.
 */
class MarkdownExtension extends BaseMarkdownExtension
{
    public function __construct()
    {
    }

    #[\Override]
    public function markdown($text)
    {
        if (!$this->markdownParser) {
            parent::__construct();
        }

        return parent::markdown($text);
    }
}
