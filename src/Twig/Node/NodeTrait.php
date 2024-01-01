<?php

namespace Drupal\twig_storybook\Twig\Node;

use Twig\Compiler;

trait NodeTrait {

  /**
   * Gets the path relative to the Drupal root.
   *
   * @return string
   *   The relative path.
   */
  private function getRelativeTemplatePath(): string {
    $path = $this->getSourceContext()?->getPath() ?? '';
    $root = \Drupal::root();
    $pos = strpos($path, $root);
    if ($pos === FALSE) {
      return $path;
    }
    return ltrim(substr($path, $pos + strlen($root)), '/');
  }

  private function compileMergeContext(Compiler $compiler) {
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
