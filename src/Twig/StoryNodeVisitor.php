<?php

namespace TwigStorybook\Twig;

use Twig\Environment;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;
use TwigStorybook\Twig\Node\StoriesNode;
use TwigStorybook\Twig\Node\StoryNode;

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
    private ?ModuleNode $module = null;

    /**
     * {@inheritdoc}
     */
    public function enterNode(Node $node, Environment $env): Node
    {
        if ($node instanceof ModuleNode && str_ends_with($node->getTemplateName(), '.stories.twig')) {
          // Here we can set nodes to the different sections to alter what the
          // generated Template class looks like.
            $this->module = $node;
        }
        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(Node $node, Environment $env): ?Node
    {
        if ($node instanceof StoriesNode) {
            $this->module = null;
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
