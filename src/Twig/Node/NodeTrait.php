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
     * @param \Twig\Compiler $compiler The Twig compiler instance.
     *
     * @return \Twig\Compiler          The modified compiler instance with the added context.
     */
    private function compileMergeContext(Compiler $compiler, string $var_name): Compiler
    {
        return $this->putMetadataIntoVariable($compiler, $var_name)
            // Merge parameters.
            ->raw(sprintf(
                '$context = twig_array_merge($%s%s ?? [], twig_array_merge($%s%s ?? [], $context));',
                $var_name,
                "['parameters']['server']['params']",
                $var_name,
                "['args']",
            ))
            ->write(PHP_EOL);
    }

    /**
     * Puts the metadata into a variable.
     *
     * @param \Twig\Compiler $compiler The compiler object to write the metadata to.
     * @param string $var_name         The name of the variable to store the metadata.
     *
     * @return \Twig\Compiler          The modified compiler instance with the added context.
     */
    private function putMetadataIntoVariable(Compiler $compiler, string $var_name): Compiler
    {
        // Write the story metadata to the compiled template.
        $compiler->write(sprintf('$%s = ', $var_name));
        // Check if 'variables' attribute is set and compile accordingly.
        $this->hasAttribute('variables')
            ? $compiler->subcompile($this->getAttribute('variables'))->write(";\n")
            : $compiler->raw("[];\n");
        return $compiler;
    }
}
