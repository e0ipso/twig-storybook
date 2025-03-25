<?php

namespace TwigStorybook\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
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
     * @param string $hash
     *   The encoded story hash.
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The inbound request.
     *
     * @return string
     *   The rendered markup.
     * @throws \TwigStorybook\Exception\StoryRenderException
     */
    public function renderStory(string $hash, Request $request): string
    {
        try {
            $decoded = json_decode(
                base64_decode(urldecode($hash)),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (\JsonException $e) {
            throw new StoryRenderException(
                'Unable to decode the story ID. Avoid tampering with the generated URL.',
                previous: $e
            );
        }
        $template_path = $decoded['path'] ?? '';
        $story_id = $decoded['id'] ?? '';
        if (empty($template_path) || empty($story_id)) {
            throw new StoryRenderException(
                'Impossible to locate a story to render without the template path or the story name.'
            );
        }
        if (!$this->environment instanceof Environment) {
            return '';
        }
        // Merge into the $context, the values for the args from the HTTP request originated in Storybook.
        $context = [
            ...$this->getArguments($request, $template_path, $story_id),
            '_story' => $story_id,
        ];
        try {
            return $this->environment->load($template_path)->render($context);
        } catch (LoaderError | SyntaxError | RuntimeError $exception) {
            $this->logger->error($exception->getMessage());
            return '';
        }
    }

    /**
     * Gets the arguments.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   The inbound request.
     * @param string $template_path
     * @param string $story_id
     *
     * @return array
     *   The array of arguments.
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function getArguments(Request $request, string $template_path, string $story_id): array
    {
        $wrapper = $this->environment->load($template_path);
        $wrapper->render(['_story' => false]);
        $stories = $this->storyCollector->getAllStories($template_path);
        // Generate the story based on the path and ID. We need to inspect the args.
        $filtered = array_filter(
            $stories,
            static fn(Story $st) => $st->id === $story_id,
        );
        $story = reset($filtered);
        if (empty($story)) {
            $message = sprintf('Impossible to find the story with ID "%s" in "%s".', $story_id, $template_path);
            throw new NotFoundHttpException($message);
        }
        $arg_names = array_keys($story->meta['args'] ?? []);
        $wrapper_data = $this->storyCollector->getWrapperData($template_path);
        if (!empty($wrapper_data['argTypes'])) {
            $arg_types = array_keys($wrapper_data['argTypes']);
            $arg_names = array_unique(array_merge($arg_names, $arg_types), SORT_REGULAR);
        }
        return array_map(
            static function (string $value) {
                try {
                    return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    return $value;
                }
            },
            array_intersect_key(
                $request->query->getIterator()->getArrayCopy(),
                array_flip($arg_names),
            )
        );
    }

    public function generateStoriesJsonFile(string $stories_path, string $url = '')
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
        if (!empty($url)) {
            $wrapper_data['parameters']['server']['url'] = $url;
        }
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
     * @param \TwigStorybook\Story $story
     *   The story object.
     * @param string $stories_path
     *   The path to the stories file.
     * @param string $url
     *   The URL to use.
     *
     * @return array
     *   The massaged story data.
     * @throws \TwigStorybook\Exception\StorySyntaxException
     * @throws \JsonException
     */
    private function massageStory(Story $story, string $stories_path, string $url = ''): array
    {
        $meta = $story->meta;
        if ($meta['parameters']['server']['id'] ?? null) {
            $message = 'The parameters.server.id property for a story will be ';
            $message .= 'generated automatically. Do not provide it.';
            throw new StorySyntaxException($message);
        }
        // This ID will be used by Storybook to call the server. Something like:
        // "https://example.com/$id"
        $meta['parameters']['server']['id'] = urlencode(base64_encode(
            json_encode(
                [
                    'path' => $stories_path,
                    'id' => $story->id,
                ],
                JSON_THROW_ON_ERROR
            )
        ));
        $name = $meta['name'] ?? $story->id;
        $meta['name'] = $name;
        return $meta;
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
