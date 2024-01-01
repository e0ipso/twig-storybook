<?php

namespace Drupal\twig_storybook\Twig\TokenParser;

use Drupal\twig_storybook\Exception\StorySyntaxException;
use Drupal\twig_storybook\Twig\Node\StoriesNode;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

final class StoriesTokenParser extends AbstractTokenParser {

  /**
   * @inheritDoc
   */
  public function parse(Token $token): Node {
    $stream = $this->parser->getStream();

    $parent = $this->parser->getExpressionParser()->parseExpression();

    [$variables] = $this->parseArguments();

    $child_template = $this->parser->parse($stream, [$this, 'decideBlockEnd'], TRUE);
    $child_template->setIndex(mt_rand());

    $stream->expect(Token::BLOCK_END_TYPE);

    return new StoriesNode(
      $parent instanceof ConstantExpression ? $parent->getAttribute('value') : '',
      $child_template->getNode('body'),
      $child_template->getTemplateName(),
      $child_template->getAttribute('index'),
      $variables,
      $token->getLine(),
      $this->getTag(),
    );
  }

  /**
   * @inheritDoc
   */
  public function getTag(): string {
    return 'stories';
  }

  public function decideBlockEnd(Token $token): bool {
    $stream = $this->parser->getStream();
    if ($token->test('endstories')) {
      return TRUE;
    }
    // Here we only allow {% story %} tokens.
    if (!$stream->test(Token::NAME_TYPE, 'story')) {
      throw new StorySyntaxException(
        sprintf(
          'You can only have {%% story %%} nodes inside of {%% stories %%} in "%s" (found "%s" in line %d).',
          $stream->getSourceContext()->getPath(),
          $token->getValue(),
          $token->getLine(),
        )
      );
    }
    return FALSE;
  }

  protected function parseArguments(): array {
    $stream = $this->parser->getStream();

    $variables = NULL;
    if ($stream->nextIf(Token::NAME_TYPE, 'with')) {
      $variables = $this->parser->getExpressionParser()->parseExpression();
    }
    $stream->expect(Token::BLOCK_END_TYPE);

    return [$variables];
  }

}
