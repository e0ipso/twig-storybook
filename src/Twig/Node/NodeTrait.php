<?php

namespace TwigStorybook\Twig\Node;

use Twig\Compiler;

/**
 * Provides utility functions for Twig node operations.
 *
 * This trait includes methods to interact with Twig nodes, particularly
 * for path manipulation and context compilation within the Twig environment.
 */
trait NodeTrait
{
    /**
     * Gets the relative path of the template from the specified root.
     *
     * This method calculates the relative path of the current Twig template
     * by comparing it with the provided root path. It is useful for
     * determining the location of a template within a larger directory structure.
     *
     * @param string $root The root path to compare against. This should be an absolute path.
     *
     * @return string The relative path of the template, or the full path if the root is not found.
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

    /**
     * Compiles and merges additional context into the Twig environment.
     *
     * This method is responsible for compiling additional context information
     * and merging it into the existing Twig context. It handles merging both
     * 'parameters' and 'args' into the context, ensuring that any variables
     * and arguments are appropriately incorporated.
     *
     * @param Compiler $compiler The Twig compiler instance.
     *
     * @return Compiler The modified compiler instance with the added context.
     */
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
