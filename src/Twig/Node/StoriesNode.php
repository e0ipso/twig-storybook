<?php

namespace Drupal\twig_storybook\Twig\Node;

use Drupal\twig_storybook\Twig\TwigExtension;
use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;

final class StoriesNode extends Node {

  use NodeTrait;

  // we don't inject the module to avoid node visitors to traverse it twice (as it will be already visited in the main module)
  public function __construct(
    string $title,
    Node $body,
    string $parent_template,
    int $index,
    ?AbstractExpression $variables,
    int $lineno,
    string $tag = NULL,
  ) {
    $nodes = ['body' => $body];
    if ($variables !== NULL) {
      $nodes['variables'] = $variables;
    }
    parent::__construct($nodes, [], $lineno, $tag);

    $this->setAttribute('index', $index);

    // Set attributes for later.
    $this->setAttribute('title', $title);
    $this->setAttribute('parent_template', $parent_template);
  }

  public function compile(Compiler $compiler): void {
    $this->collectStoryMetadata($compiler);
    $compiler->addDebugInfo($this);
    // If the template adds args or parameters at a stories level, then they
    // should be available in the individual story scope.
    $this->compileMergeContext($compiler);
    $this->getNode('body')->compile($compiler);
  }

  /**
   * @param \Twig\Compiler $compiler
   */
  public function collectStoryMetadata(Compiler $compiler): void {
    $compiler
      ->addDebugInfo($this)
      ->write('if ($context[\'_story\'] === FALSE) {')
      ->indent();
    $stories_id = $this->getAttribute('title');
    // $_stories_meta = ['foo' => 'bar'];
    $compiler->write('$_stories_meta = ');
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
    $compiler->raw('$extension->storyCollector->setWrapperData(')
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
