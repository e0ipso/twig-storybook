<?php

namespace TwigStorybook\Twig\Node;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;
use TwigStorybook\Twig\TwigExtension;

final class StoriesNode extends Node
{

    use NodeTrait;

    public function __construct(
        string $id,
        Node $body,
        ?AbstractExpression $variables,
        int $lineno,
        string $tag,
        private readonly string $root,
    ) {
        parent::__construct(['body' => $body], [], $lineno, $tag);

        // Set attributes for later.
        $this->setAttribute('variables', $variables);
        $this->setAttribute('id', $id);
    }

    public function compile(Compiler $compiler): void
    {
        $this->collectStoryMetadata($compiler);
        $compiler->addDebugInfo($this);
        // If the template adds args or parameters at a stories level, then they
        // should be available in the individual story scope.
        $this->compileMergeContext($compiler)
           ->subcompile($this->getNode('body'));
    }

    /**
     * @param \Twig\Compiler $compiler
     */
    public function collectStoryMetadata(Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write('if ($context[\'_story\'] === FALSE) {')
            ->indent();
        $stories_id = $this->getAttribute('id');
        // $_stories_meta = ['foo' => 'bar'];
        $compiler->write('$_stories_meta = ');
        $this->hasAttribute('variables')
        ? $compiler->subcompile($this->getAttribute('variables'))
        : $compiler->raw('[]');
        $compiler->write(';')->raw(PHP_EOL);
        // Get the extension.
        $compiler->raw('$extension = $this->extensions[')
            ->string(TwigExtension::class)
            ->write('];')
            ->raw(PHP_EOL);

        // Collect all the stories for the given path, as we process them.
        $path = $this->getRelativeTemplatePath($this->root);
        $compiler->raw('$extension->storyCollector->setWrapperData(')
            ->string($stories_id)
            ->raw(', ')
            ->string($path)
            ->raw(', ')
            ->write('$_stories_meta')
            ->raw(');')
            ->raw(PHP_EOL);
        $compiler
            ->outdent()
            ->write('}');
    }
}
