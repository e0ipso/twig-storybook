<?php

namespace TwigStorybook\Service;

use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use TwigStorybook\Exception\StoryRenderException;
use TwigStorybook\Exception\StorySyntaxException;
use TwigStorybook\Story;

/**
 * Render a component with some context.
 */
final class StoryRenderer
{

  /**
   * The twig environment.
   *
   * @var \Twig\Environment|null
   */
    private ?Environment $environment = null;

  /**
   * Creates a new ComponentRenderer.
   */
    public function __construct(
        private readonly StoryCollector $storyCollector,
        private readonly LoggerInterface $logger,
        private readonly string $root,
    ) {
    }

  /**
   * Renders the Twig markup of a component.
   *
   * @param string $name
   *   The component to render.
   * @param array $context
   *   The context of the component.
   *
   * @return string
   *   The rendered markup.
   */
    public function renderStory(string $story_id, array $story_meta, string $story_template, array $context): string
    {
        if (!is_string($context['_story'] ?? null)) {
            $message = 'Impossible to render the story, the `_story` variable is not set in the render context.';
            throw new StoryRenderException($message);
        }
      // Only render the story if it matches the $context['story'] requested.
        if ($story_id !== $context['_story']) {
            return '';
        }
        if (!$this->environment instanceof Environment) {
            return '';
        }
        try {
            return $this->environment->createTemplate($story_template)->render(
                array_merge($context, $story_meta)
            );
        } catch (LoaderError|SyntaxError $exception) {
            $this->logger->error($exception->getMessage());
            return '';
        }
    }

    public function generateStoriesJsonFile(string $stories_path, string $url)
    {
      // Trigger the compilation of the template to collect the stories.
        $wrapper = $this->environment->load($stories_path);
      // This will execute the `compile` method without rendering anything.
        $wrapper->render(['_story' => false]);
        $path = ltrim(
            str_replace(
                $this->root,
                '',
                $wrapper->getSourceContext()->getPath(),
            ),
            '/'
        );
        $wrapper_data = $this->storyCollector->getWrapperData($path);
        $stories = array_map(
            fn(Story $story) => $this->massageStory($story, $path, $url),
            $this->storyCollector->getAllStories($path),
        );

        return [
        ...$wrapper_data,
        'stories' => $stories,
        ];
    }

  /**
   * @throws \TwigStorybook\Exception\StorySyntaxException
   * @throws \JsonException
   */
    private function massageStory(Story $story, string $stories_path, string $url): Story
    {
        $meta = $story->meta;
        if ($meta['parameters']['server']['id'] ?? null) {
            $message = 'The parameters.server.id property for a story will be ';
            $message .= 'generated automatically. Do not provide it.';
            throw new StorySyntaxException($message);
        }
      // This ID will be used by Storybook to call the server. Something like:
      // "https://example.com/$id"
        $meta['parameters']['server']['url'] = $url;
        $meta['parameters']['server']['id'] = base64_encode(
            json_encode(
                [
                'path' => $stories_path,
                'name' => $story->id,
                ],
                JSON_THROW_ON_ERROR
            )
        );
        return new Story(
            $story->path,
            $story->id,
            $meta,
        );
    }

    private function validateStory(array $story)
    {
    }

  /**
   * Sets the Twig environment.
   *
   * @param \Twig\Environment $environment
   *   The environment.
   */
    public function setTwigEnvironment(Environment $environment): void
    {
        $this->environment = $environment;
    }
}
