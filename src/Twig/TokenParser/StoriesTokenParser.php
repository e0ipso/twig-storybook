<?php

namespace TwigStorybook\Twig\TokenParser;

use TwigStorybook\Exception\StorySyntaxException;
use TwigStorybook\Twig\Node\StoriesNode;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Class StoriesTokenParser
 *
 * This class is responsible for parsing the "stories" tag in a template and generating a StoriesNode.
 *
 * @package YourPackageName
 */
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

        $stories_id = $stream->expect(Token::NAME_TYPE)->getValue();
        [$variables] = $this->parseArguments();

        $child_template = $this->parser->subparse([$this, 'decideBlockEnd'], true);

        $stream->expect(Token::BLOCK_END_TYPE);

        return new StoriesNode(
            $stories_id,
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

    /**
     * Checks whether the given token marks the end of a block.
     *
     * This method retrieves the stream from the parser and checks if the given
     * token matches the string "endstories".
     * If it does, the method returns true.
     * Otherwise, the method checks if the next token in the stream is of type
     * "NAME_TYPE" and its value is "story".
     * If it is not, a StorySyntaxException is thrown, indicating that only
     * {% story %} tokens are allowed inside {% stories %} blocks.
     * Finally, if none of the above conditions are met, the method returns
     * false.
     *
     * @param Token $token The token to check.
     *
     * @return bool Returns true if the token marks the end of a block, or false otherwise.
     * @throws StorySyntaxException When the token does not match the expected conditions.
     */
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

    /**
     * Parses the arguments for the method.
     *
     * This method retrieves the stream from the parser and checks if it has
     * the
     * keyword "with".
     * If "with" is found, it then uses the expression parser to parse the
     * expression and assigns it to the $variables variable.
     * After that, it expects the token of the end of the block.
     *
     * @return array Returns an array containing the parsed variables, or null
     *     if "with" was not found.
     *
     * @throws \Twig\Error\SyntaxError
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
