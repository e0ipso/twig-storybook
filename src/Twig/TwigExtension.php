<?php

namespace TwigStorybook\Twig;

use TwigStorybook\Service\StoryRenderer;
use TwigStorybook\Service\StoryCollector;
use TwigStorybook\Twig\TokenParser\StoriesTokenParser;
use TwigStorybook\Twig\TokenParser\StoryTokenParser;
use Twig\Extension\AbstractExtension;

/**
 * The twig extension so the app can recognize the new code.
 */
final class TwigExtension extends AbstractExtension
{

    /**
     * TwigComponentExtension constructor.
     *
     * @param \TwigStorybook\Service\StoryRenderer  $storyRenderer
     *   Renderer.
     * @param \TwigStorybook\Service\StoryCollector $storyCollector
     *   Collector.
     */
    public function __construct(
        public readonly StoryRenderer $storyRenderer,
        public readonly StoryCollector $storyCollector,
        private readonly string $root,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getTokenParsers(): array
    {
        return [new StoriesTokenParser($this->root), new StoryTokenParser($this->root)];
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getNodeVisitors(): array
    {
        return [new StoryNodeVisitor()];
    }
}
