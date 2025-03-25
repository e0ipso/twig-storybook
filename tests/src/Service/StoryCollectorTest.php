<?php

namespace TwigStorybook\Tests\Service;

use PHPUnit\Framework\TestCase;
use TwigStorybook\Service\StoryCollector;
use TwigStorybook\Story;

/**
 * Test the StoryCollector service.
 *
 * @coversDefaultClass \TwigStorybook\Service\StoryCollector
 * @group TwigStorybook
 */
class StoryCollectorTest extends TestCase {

  /**
   * Story collector instance.
   *
   * @var \TwigStorybook\Service\StoryCollector
   */
  protected StoryCollector $collector;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->collector = new StoryCollector();
  }

  /**
   * Tests collecting and retrieving stories.
   */
  public function testCollectAndGetStories(): void {
    $this->collector->collect('story-1', 'path/to/story1', ['title' => 'Story 1']);
    $this->collector->collect('story-2', 'path/to/story1', ['title' => 'Story 2']);

    $stories = $this->collector->getAllStories('path/to/story1');

    $this->assertCount(2, $stories);
    $this->assertInstanceOf(Story::class, $stories[0]);
    $this->assertInstanceOf(Story::class, $stories[1]);
    $this->assertEquals('story-1', $stories[0]->id);
    $this->assertEquals('story-2', $stories[1]->id);
  }

  /**
   * Tests the wrapper data.
   */
  public function testWrapperData(): void {
    $meta = [
      'title' => 'My Component',
      'component' => 'Button',
      'argTypes' => ['text' => ['type' => 'string']],
    ];

    $this->collector->setWrapperData('button', 'path/to/story', $meta);
    $wrapperData = $this->collector->getWrapperData('path/to/story');

    $this->assertEquals($meta, $wrapperData);
  }

}
