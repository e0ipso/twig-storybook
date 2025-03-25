<?php

namespace TwigStorybook\Tests\Service;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TwigStorybook\Service\StoryCollector;
use TwigStorybook\Service\StoryRenderer;

/**
 * Test the StoryRenderer service.
 *
 * @coversDefaultClass \TwigStorybook\Service\StoryRenderer
 * @group TwigStorybook
 */
class StoryRendererTest extends TestCase {

  /**
   * Tests the story renderer constructor.
   */
  public function testConstructor(): void {
    // Since StoryCollector is final we need to use a real instance.
    $storyCollector = new StoryCollector();
    $logger = $this->createMock(LoggerInterface::class);
    $root = '/path/to/root';

    // Create the renderer.
    $renderer = new StoryRenderer($storyCollector, $logger, $root);

    // Assert that the object was constructed successfully.
    $this->assertInstanceOf(StoryRenderer::class, $renderer);
  }

}
