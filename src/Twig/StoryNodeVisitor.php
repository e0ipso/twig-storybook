<?php

namespace TwigStorybook\Twig;

use TwigStorybook\Twig\tag\StoryNode;
use Twig\Environment;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

/**
 * Node visitor for the components.
 */
final class StoryNodeVisitor implements NodeVisitorInterface
{

    /**
     * The internal counter.
     *
     * @var int
     */
    private int $nestingDepth = 0;

    /**
     * {@inheritdoc}
     */
    public function enterNode(Node $node, Environment $env): Node
    {
        if ($node instanceof StoryNode) {
            $node->setAttribute('nesting_depth', $this->nestingDepth++);
        }
        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node, Environment $env): ?Node
    {
        if ($node instanceof StoryNode) {
            $node->setAttribute('nesting_depth', $this->nestingDepth--);
        }
        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return 0;
    }

}
