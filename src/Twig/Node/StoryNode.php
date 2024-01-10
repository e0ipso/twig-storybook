<?php

namespace TwigStorybook\Twig\Node;

use TwigStorybook\Twig\TwigExtension;
use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;
use Twig\Node\NodeOutputInterface;

/**
 * Represents a story call node.
 */
final class StoryNode extends Node implements NodeOutputInterface
{

    use NodeTrait;

    public function __construct(
        string $name,
        Node $body,
        ?AbstractExpression $variables,
        int $lineno,
        string $tag,
        private readonly string $root
    ) {
        parent::__construct(['variables' => $variables], ['name' => $name, 'body' => $body], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $this->collectStoryMetadata($compiler);

        // Story nodes should only print their interior if the $context['_story']
        // variable is set to $this->getAttribute('name') at the time of rendering.
        $compiler
            ->addDebugInfo($this)
            ->write('if (($context[\'_story\'] ?? NULL) === ')
            ->string($this->getAttribute('name'))
            ->write(') {')
            ->indent();
        $this->compileMergeContext($compiler)
            ->subcompile($this->getAttribute('body'))
            ->write(';')
            ->write(PHP_EOL)
            ->outdent()
            ->write('}');
    }

    /**
     * @param \Twig\Compiler $compiler
     */
    public function collectStoryMetadata(Compiler $compiler): void
    {
        $story_id = $this->getAttribute('name');
      // Collect all the stories for the given path, as we process them.
        $path = $this->getRelativeTemplatePath($this->root);
        $compiler
            ->addDebugInfo($this)
            ->write('if (($context[\'_story\'] ?? NULL) === FALSE) {')
            ->indent()
            // $_story_meta = ['foo' => 'bar'];
            ->write('$_story_meta = ');
        $this->hasNode('variables')
        ? $compiler->subcompile($this->getNode('variables'))
        : $compiler->raw('[]');
        $compiler
          ->write(';')
          ->write(PHP_EOL)
          // Get the extension.
          ->raw('$extension = $this->extensions[')
          ->string(TwigExtension::class)
          ->write('];')
          ->write(PHP_EOL)
          ->raw('$extension->storyCollector->collect(')
          ->string($path)
          ->raw(', ')
          ->string($story_id)
          ->raw(', ')
          ->write('$_story_meta')
          ->raw(');')
          ->raw(PHP_EOL)
          ->outdent()
          ->write('}');
    }
}