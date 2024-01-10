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

    private function compileMergeContext(Compiler $compiler): Compiler
    {
        $compiler
        // Merge parameters.
        ->raw('$context = twig_array_merge(');
        $this->hasNode('variables')
        ? $compiler->subcompile($this->getNode('variables'))
        : $compiler->raw('[]');
        $compiler
        ->write('[\'parameters\'][\'server\'][\'params\'] ?? [], $context);')
        ->write(PHP_EOL)
        // Merge args.
        ->raw('$context = twig_array_merge(');
        $this->hasNode('variables')
        ? $compiler->subcompile($this->getNode('variables'))
        : $compiler->raw('[]');
        $compiler
        ->write('[\'args\'] ?? [], $context);')
        ->write(PHP_EOL);
        return $compiler;
    }
}
