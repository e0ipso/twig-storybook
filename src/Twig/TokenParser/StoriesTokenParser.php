<?php

namespace TwigStorybook\Twig\TokenParser;

use TwigStorybook\Exception\StorySyntaxException;
use TwigStorybook\Twig\Node\StoriesNode;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

final class StoriesTokenParser extends AbstractTokenParser
{

    public function __construct(private readonly string $root)
    {
    }

    /**
     * @inheritDoc
     */
    public function parse(Token $token): Node
    {
        $stream = $this->parser->getStream();

        $parent = $this->parser->getExpressionParser()->parseExpression();

        [$variables] = $this->parseArguments();

        $child_template = $this->parser->subparse([$this, 'decideBlockEnd'], true);

        $stream->expect(Token::BLOCK_END_TYPE);

        return new StoriesNode(
            $parent instanceof ConstantExpression ? $parent->getAttribute('value') : '',
            $child_template,
            $variables,
            $token->getLine(),
            $this->getTag(),
            $this->root,
        );
    }

    /**
     * @inheritDoc
     */
    public function getTag(): string
    {
        return 'stories';
    }

    public function decideBlockEnd(Token $token): bool
    {
        $stream = $this->parser->getStream();
        if ($token->test('endstories')) {
            return true;
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
        return false;
    }

    protected function parseArguments(): array
    {
        $stream = $this->parser->getStream();

        $variables = null;
        if ($stream->nextIf(Token::NAME_TYPE, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }
        $stream->expect(Token::BLOCK_END_TYPE);

        return [$variables];
    }
}
