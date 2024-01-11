<?php

namespace TwigStorybook\Service;

use TwigStorybook\Story;

/**
 * Collects story metadata for Storybook format generation.
 */
final class StoryCollector
{
    private \ArrayObject $storage;

    public function __construct()
    {
        $this->storage = new \ArrayObject();
    }

    /**
     * Collects information about an individual story.
     *
     * @param string $story_id
     *   The ID of the story.
     * @param string $path
     *    The template path for the stories' template.
     * @param array $story_meta
     *   The metadata to collect.
     */
    public function collect(string $story_id, string $path, array $story_meta = []): void
    {
        $story = new Story($path, $story_id, $story_meta);
        $this->storage[$path]['stories'][spl_object_id($story)] = $story;
    }

    /**
     * Gets the wrapper data for the stories template.
     *
     * @param string $stories_id
     *   The ID on the {% stories %} tag.
     * @param string $path
     *   The template path for the stories' template.
     * @param array $meta
     *   The metadata to collect.
     */
    public function setWrapperData(string $stories_id, string $path, array $meta = []): void
    {
        $title = $meta['title'] ?? $stories_id;
        $meta['title'] = $title;
        $this->storage[$path]['wrapper'] = $meta;
    }

    /**
     * Gets all the metadata for the stories in a given template.
     *
     * @param string $path
     *   The template path for the stories' template.
     *
     * @return array
     *   The metadata.
     */
    public function getAllStories(string $path): array
    {
        return array_values($this->storage[$path]['stories'] ?? []);
    }

    /**
     * Gets the top-level metadata for the stories' template.
     *
     * @param string $path
     *   The template path for the stories' template.
     *
     * @return array
     *   The metadata.
     */
    public function getWrapperData(string $path): array
    {
        return $this->storage[$path]['wrapper'] ?? [];
    }
}
