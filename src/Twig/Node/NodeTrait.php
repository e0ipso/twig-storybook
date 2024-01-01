<?php

namespace TwigStorybook\Twig\Node;

use Twig\Compiler;

trait NodeTrait
{

    /**
     * Gets the path relative to the app root.
     *
     * @return string
     *   The relative path.
     */
    private function getRelativeTemplatePath(string $root): string
    {
        $path = $this->getSourceContext()?->getPath() ?? '';
        $pos = strpos($path, $root);
        if ($pos === false) {
            return $path;
        }
        return ltrim(substr($path, $pos + strlen($root)), '/');
    }

    private function compileMergeContext(Compiler $compiler)
    {
        // Merge parameters.
        $compiler
            ->raw('$context = twig_array_merge($context, ')
            ->subcompile($this->getNode('variables'))
            ->write('[\'parameters\'][\'server\'][\'params\'] ?? []);')
            ->write(PHP_EOL);
        // Merge args.
        $compiler
            ->raw('$context = twig_array_merge($context, ')
            ->subcompile($this->getNode('variables'))
            ->write('[\'args\'] ?? []);')
            ->write(PHP_EOL);
    }

}
