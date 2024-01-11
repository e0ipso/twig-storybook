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
     * Class constructor.
     *
     * @param StoryCollector $storyCollector The story collector instance.
     * @param string $root The root directory path.
     *
     * @return void
     */
    public function __construct(
        public readonly StoryCollector $storyCollector,
        private readonly string $root,
    ) {
    }

    /**
     * Retrieve an array of token parsers.
     *
     * This method returns an array of token parsers, used for parsing tokens in a specific format.
     *
     * @return array An array of token parsers.
     */
    public function getTokenParsers(): array
    {
        return [
            new StoriesTokenParser($this->root),
            new StoryTokenParser($this->root)
        ];
    }
}
