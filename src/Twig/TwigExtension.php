<?php

namespace Drupal\twig_storybook\Twig;

use Drupal\twig_storybook\Service\StoryRenderer;
use Drupal\twig_storybook\Service\StoryCollector;
use Drupal\twig_storybook\Twig\TokenParser\StoriesTokenParser;
use Drupal\twig_storybook\Twig\TokenParser\StoryTokenParser;
use Twig\Extension\AbstractExtension;

/**
 * The twig extension so Drupal can recognize the new code.
 */
final class TwigExtension extends AbstractExtension {

  /**
   * TwigComponentExtension constructor.
   *
   * @param \Drupal\twig_storybook\Service\StoryRenderer $storyRenderer
   *   Renderer.
   * @param \Drupal\twig_storybook\Service\StoryCollector $storyCollector
   *   Collector.
   */
  public function __construct(
    public readonly StoryRenderer $storyRenderer,
    public readonly StoryCollector $storyCollector,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getTokenParsers(): array {
    return [new StoriesTokenParser(), new StoryTokenParser()];
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeVisitors(): array {
    return [new StoryNodeVisitor()];
  }

}
