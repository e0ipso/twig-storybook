<?php

namespace TwigStorybook\Tests\Twig;

use PHPUnit\Framework\TestCase;
use TwigStorybook\Service\StoryCollector;
use TwigStorybook\Twig\TokenParser\StoriesTokenParser;
use TwigStorybook\Twig\TokenParser\StoryTokenParser;
use TwigStorybook\Twig\TwigExtension;

/**
 * Test the TwigExtension class.
 *
 * @coversDefaultClass \TwigStorybook\Twig\TwigExtension
 * @group TwigStorybook
 */
class TwigExtensionTest extends TestCase {

  /**
   * Tests token parsers.
   */
  public function testGetTokenParsers(): void {
    // Since the StoryCollector is final we need to use a real instance.
    $storyCollector = new StoryCollector();
    $root = '/path/to/root';

    $extension = new TwigExtension($storyCollector, $root);
    $parsers = $extension->getTokenParsers();

    $this->assertCount(2, $parsers);
    $this->assertInstanceOf(StoriesTokenParser::class, $parsers[0]);
    $this->assertInstanceOf(StoryTokenParser::class, $parsers[1]);
  }

}
