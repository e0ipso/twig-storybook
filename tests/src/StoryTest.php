<?php

namespace TwigStorybook\Tests;

use PHPUnit\Framework\TestCase;
use TwigStorybook\Story;

/**
 * Test the Story class.
 *
 * @coversDefaultClass \TwigStorybook\Story
 * @group TwigStorybook
 */
class StoryTest extends TestCase {

  /**
   * Tests Story construction.
   */
  public function testStoryConstruction(): void {
    $path = 'path/to/story';
    $id = 'story-id';
    $meta = ['title' => 'Story Title', 'component' => 'Button'];

    $story = new Story($path, $id, $meta);

    $this->assertSame($path, $story->path);
    $this->assertSame($id, $story->id);
    $this->assertSame($meta, $story->meta);
  }

}
