<?php

namespace TwigStorybook\Tests\Exception;

use PHPUnit\Framework\TestCase;
use TwigStorybook\Exception\StoryRenderException;
use TwigStorybook\Exception\StorySyntaxException;

/**
 * Test the exceptions.
 *
 * @group TwigStorybook
 */
class ExceptionsTest extends TestCase {

  /**
   * Tests the StoryRenderException.
   */
  public function testStoryRenderException(): void {
    $message = 'Test message';
    $previous = new \Exception('Previous exception');
    $code = 123;

    $exception = new StoryRenderException($message, $code, $previous);

    $this->assertEquals($message, $exception->getMessage());
    $this->assertEquals($code, $exception->getCode());
    $this->assertSame($previous, $exception->getPrevious());
  }

  /**
   * Tests the StorySyntaxException.
   */
  public function testStorySyntaxException(): void {
    $message = 'Test message';
    $previous = new \Exception('Previous exception');
    $code = 123;

    $exception = new StorySyntaxException($message, $code, $previous);

    $this->assertEquals($message, $exception->getMessage());
    $this->assertEquals($code, $exception->getCode());
    $this->assertSame($previous, $exception->getPrevious());
  }

}
