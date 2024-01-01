<?php

namespace Drupal\twig_storybook\Twig\Node;

use Drupal\twig_storybook\Twig\TwigExtension;
use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;
use Twig\Node\NodeOutputInterface;

/**
 * Represents a story call node.
 */
final class StoryNode extends Node implements NodeOutputInterface {

  use NodeTrait;

  public function __construct(string $name, Node $body, ?AbstractExpression $variables, int $lineno, string $tag = NULL) {
    parent::__construct(['body' => $body, 'variables' => $variables], ['name' => $name], $lineno, $tag);
  }

  public function compile(Compiler $compiler): void {
    $this->collectStoryMetadata($compiler);

    // Story nodes should only print their interior if the $context['_story']
    // variable is set to $this->getAttribute('name') at the time of rendering.
    $compiler
      ->addDebugInfo($this)
      ->write('if (($context[\'_story\'] ?? NULL) === ')
      ->string($this->getAttribute('name'))
      ->write(') {')
      ->indent();
    $this->compileMergeContext($compiler);
    $this->getNode('body')->compile($compiler);
    $compiler
      ->outdent()
      ->write('}');
  }

  /**
   * @param \Twig\Compiler $compiler
   */
  public function collectStoryMetadata(Compiler $compiler): void {
    $compiler
      ->addDebugInfo($this)
      ->write('if (($context[\'_story\'] ?? NULL) === FALSE) {')
      ->indent();
    $story_id = $this->getAttribute('name');
    // $_story_meta = ['foo' => 'bar'];
    $compiler->write('$_story_meta = ');
    $this->hasNode('variables')
      ? $compiler->subcompile($this->getNode('variables'))
      : $compiler->raw('[]');
    $compiler->write(';')->raw(PHP_EOL);
    // Get the extension.
    $compiler->raw('$extension = $this->extensions[')
      ->string(TwigExtension::class)
      ->write('];')
      ->raw(PHP_EOL);

    // Collect all the stories for the given path, as we process them.
    $path = $this->getRelativeTemplatePath();
    $compiler->raw('$extension->storyCollector->collect(')
      ->string($path)
      ->raw(', ')
      ->string($story_id)
      ->raw(', ')
      ->write('$_story_meta')
      ->raw(');')
      ->raw(PHP_EOL);
    $compiler
      ->outdent()
      ->write('}');
  }

}
