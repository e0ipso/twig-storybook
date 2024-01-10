<?php

namespace TwigStorybook\Service;

use TwigStorybook\Story;

final class StoryCollector
{

    private \ArrayObject $storage;

    public function __construct()
    {
        $this->storage = new \ArrayObject();
    }

    public function collect(string $path, string $story_id, array $story_meta = []): void
    {
        $story = new Story($path, $story_id, $story_meta);
        $this->storage[$path]['stories'][spl_object_id($story)] = $story;
    }

    public function getAllStories(string $path): array
    {
        return array_values($this->storage[$path]['stories'] ?? []);
    }

    public function setWrapperData(string $stories_id, string $path, array $meta = []): void
    {
        $title = $meta['title'] ?? $stories_id;
        $meta['title'] = $title;
        $this->storage[$path]['wrapper'] = $meta;
    }

    public function getWrapperData(string $path): ?array
    {
        return $this->storage[$path]['wrapper'] ?? [];
    }
}
