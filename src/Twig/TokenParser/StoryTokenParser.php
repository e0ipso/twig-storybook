<?php

namespace TwigStorybook\Twig\TokenParser;

use TwigStorybook\Twig\Node\StoryNode;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class StoryTokenParser extends AbstractTokenParser
{

    public function __construct(private readonly string $root)
    {
    }

    public function parse(Token $token): Node
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $name = $stream->expect(Token::STRING_TYPE)->getValue();
        [$variables] = $this->parseArguments();

        $body = $this->parser->subparse([$this, 'decideBlockEnd'], true);
        $stream->expect(Token::BLOCK_END_TYPE);

        $node = new StoryNode($name, $body, $variables, $lineno, $this->getTag(), $this->root);
        $node->setSourceContext($stream->getSourceContext());
        return $node;
    }

    public function getTag(): string
    {
        return 'story';
    }

    public function decideBlockEnd(Token $token): bool
    {
        return $token->test('endstory');
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
