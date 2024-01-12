<?php

namespace TwigStorybook\Twig\Node;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;
use TwigStorybook\Twig\TwigExtension;

/**
 * The StoriesNode class extends the Twig Node class, providing functionality
 * specific to story nodes in Twig templates.
 */
final class StoriesNode extends Node
{
    use NodeTrait; // Use the functionality defined in NodeTrait.

    /**
     * Constructor for the StoriesNode class.
     *
     * @param string               $id         The identifier for the story node.
     * @param Node                 $body       The body of the node.
     * @param AbstractExpression   $variables  Additional variables for the node.
     * @param int                  $lineno     The line number where the node is defined.
     * @param string               $tag        The tag associated with the node.
     * @param string               $root       The root directory for the node.
     */
    public function __construct(
        string $id,
        Node $body,
        ?AbstractExpression $variables,
        int $lineno,
        string $tag,
        private readonly string $root
    ) {
        // Initialize the parent Node with the provided parameters.
        parent::__construct(['body' => $body], [], $lineno, $tag);

        // Set additional attributes for later use.
        $this->setAttribute('variables', $variables);
        $this->setAttribute('id', $id);
    }

    /**
     * Compiles the node into PHP code.
     *
     * @param Compiler $compiler The compiler instance.
     */
    public function compile(Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$_selected_story = $context[\'_story\'] ?? NULL;')
            ->write(";\n");
        // Compile the context merging and the body of the node.
        $this->compileMergeContext($compiler, '_stories_meta');

        // Collect story metadata for inclusion in the compiled template.
        $this->collectStoryMetadata($compiler);
        // Add debugging information to the compiler.
        $compiler
            ->addDebugInfo($this)
            ->subcompile($this->getNode('body'));
    }

    /**
     * Collects metadata for the story and writes it into the compiled template.
     *
     * @param Compiler $compiler The compiler instance.
     */
    public function collectStoryMetadata(Compiler $compiler): void
    {
        // Begin adding debug information for this node.
        $compiler
            ->addDebugInfo($this)
            // Write a conditional to check the story context.
            ->write('if ($_selected_story === FALSE) {')
            ->indent(); // Increase indentation for better readability.

        // Retrieve the story ID attribute.
        $stories_id = $this->getAttribute('id');

        // Retrieve the TwigExtension and prepare to set wrapper data.
        $compiler->raw('$extension = $this->extensions[')
            ->string(TwigExtension::class)
            ->write('];')
            ->raw(PHP_EOL);

        // Collect and set all stories data for the given path.
        $path = $this->getRelativeTemplatePath($this->root);
        $compiler->raw('$extension->storyCollector->setWrapperData(')
            ->string($stories_id)
            ->raw(', ')
            ->string($path)
            ->raw(', ')
            ->write('$_stories_meta')
            ->raw(');')
            ->raw(PHP_EOL);

        // End the conditional block and reduce indentation.
        $compiler
            ->outdent()
            ->write('}');
    }
}
