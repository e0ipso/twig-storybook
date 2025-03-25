<?php

namespace TwigStorybook\Tests\Twig\TokenParser;

use PHPUnit\Framework\TestCase;
use TwigStorybook\Twig\TokenParser\StoryTokenParser;
use Twig\Token;
use Twig\TokenStream;

/**
 * Test the StoryTokenParser.
 *
 * @coversDefaultClass \TwigStorybook\Twig\TokenParser\StoryTokenParser
 * @group TwigStorybook
 */
class StoryTokenParserTest extends TestCase {

  /**
   * Tests the getTag method.
   */
  public function testGetTag(): void {
    $root = '/path/to/root';
    $parser = new StoryTokenParser($root);

    $this->assertEquals('story', $parser->getTag());
  }

}
