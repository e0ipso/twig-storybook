<?php

namespace TwigStorybook\Tests\Twig\TokenParser;

use PHPUnit\Framework\TestCase;
use TwigStorybook\Twig\TokenParser\StoriesTokenParser;
use Twig\Token;
use Twig\TokenStream;

/**
 * Test the StoriesTokenParser.
 *
 * @coversDefaultClass \TwigStorybook\Twig\TokenParser\StoriesTokenParser
 * @group TwigStorybook
 */
class StoriesTokenParserTest extends TestCase {

  /**
   * Tests the getTag method.
   */
  public function testGetTag(): void {
    $root = '/path/to/root';
    $parser = new StoriesTokenParser($root);

    $this->assertEquals('stories', $parser->getTag());
  }

}
