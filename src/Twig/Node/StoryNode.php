<?php

namespace TwigStorybook\Twig\Node;

use TwigStorybook\Twig\TwigExtension;
use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;
use Twig\Node\NodeOutputInterface;

/**
 * StoryNode Class
 *
 * This class extends the `Node` class from the Twig library and implements the `NodeOutputInterface`.
 * It represents a single "story" node in the template tree.
 */
final class StoryNode extends Node implements NodeOutputInterface
{
    use NodeTrait;

    /**
     * StoryNode Constructor.
     *
     * @param string $name Name of the story node.
     * @param Node $body The content inside the story node.
     * @param AbstractExpression|null $variables The variables related to this node.
     * @param int $lineno The line number where the node starts.
     * @param string $tag The tag name.
     * @param string $root Root path of the template.
     */
    public function __construct(
        string $name,
        Node $body,
        ?AbstractExpression $variables,
        int $lineno,
        string $tag,
        private readonly string $root
    ) {
        parent::__construct(['body' => $body], ['name' => $name, 'variables' => $variables], $lineno, $tag);
    }

    /**
     * This method is responsible for node compilation.
     * It generates the PHP code representing this node.
     *
     * @param Compiler $compiler Compilation context details.
     */
    public function compile(Compiler $compiler): void
    {
        // Story nodes should only print their interior if the $context['_story']
        // variable is set to $this->getAttribute('name') at the time of rendering.
        $compiler
            ->addDebugInfo($this)
            ->write('$_selected_story = $context[\'_story\'] ?? NULL;')
            ->write(";\n");
        $compiler
            ->write('if ($_selected_story === ')
            ->string($this->getAttribute('name'))
            ->write(') {')
            ->indent();

        $this->compileMergeContext($compiler, '_story_meta');

        // Compile the story context and compile the story body
        $compiler
            ->subcompile($this->getNode('body'))
            ->write(';')
            ->write(PHP_EOL)
            ->outdent()
            ->write('}');

        // Collect the metadata related to the story
        $this->collectStoryMetadata($compiler);
    }

    /**
     * This method is responsible for collecting story metadata.
     *
     * @param Compiler $compiler Compilation context details.
     */
    public function collectStoryMetadata(Compiler $compiler): void
    {
        $story_id = $this->getAttribute('name');

        // Collect all the stories for the given path, as we process them.
        $path = $this->getRelativeTemplatePath($this->root);

        $compiler
            ->addDebugInfo($this)
            ->write('if ($_selected_story === FALSE) {')
            ->indent();

        $this->putMetadataIntoVariable($compiler, '_story_meta')
            // Get the extension.
            ->raw('$extension = $this->extensions[')
            ->string(TwigExtension::class)
            ->write('];')
            ->write(PHP_EOL)
            // Collect the story metadata
            ->raw('$extension->storyCollector->collect(')
            ->string($story_id)
            ->raw(', ')
            ->string($path)
            ->raw(', ')
            ->write('$_story_meta')
            ->raw(');')
            ->raw(PHP_EOL)
            ->outdent()
            ->write('}');
    }
}
