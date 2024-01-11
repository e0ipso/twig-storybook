<?php

namespace TwigStorybook\Twig\TokenParser;

use TwigStorybook\Twig\Node\StoryNode;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Class StoryTokenParser
 *
 * This class is responsible for parsing tokens and generating a StoryNode object.
 * It extends the AbstractTokenParser class.
 *
 * @package Your\Namespace
 */
class StoryTokenParser extends AbstractTokenParser
{
    /**
     * Constructor method for initializing the root property.
     *
     * @param string $root The root directory path.
     */
    public function __construct(private readonly string $root)
    {
    }

    /**
     * Parses a Token and returns a Node.
     *
     * @param Token $token The token to be parsed.
     *
     * @return Node The parsed node.
     */
    public function parse(Token $token): Node
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $name = $stream->expect(Token::NAME_TYPE)->getValue();
        [$variables] = $this->parseArguments();

        $body = $this->parser->subparse([$this, 'decideBlockEnd'], true);
        $stream->expect(Token::BLOCK_END_TYPE);

        $node = new StoryNode($name, $body, $variables, $lineno, $this->getTag(), $this->root);
        $node->setSourceContext($stream->getSourceContext());
        return $node;
    }

    /**
     * Returns the tag used for the content.
     *
     * @return string The tag used for the content.
     */
    public function getTag(): string
    {
        return 'story';
    }

    /**
     * Determine if the given token represents the end of a block.
     *
     * @param Token $token The token to check.
     *
     * @return bool True if the token represents the end of a block, false otherwise.
     */
    public function decideBlockEnd(Token $token): bool
    {
        return $token->test('endstory');
    }

    /**
     * Parses the arguments from a stream.
     *
     * This method is responsible for extracting the arguments from a given stream. It expects the stream
     * to be in a specific format. If the stream starts with the "with" keyword, it will parse the expression
     * following it as the arguments. Otherwise, it will return null.
     *
     * @return array An array containing the parsed arguments. If no arguments are found, it will return an
     *               array with null as the only element.
     */
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
