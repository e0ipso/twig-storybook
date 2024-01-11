<?php

namespace TwigStorybook;

/**
 * Class Story
 * Represents a story with its path, id, and metadata.
 */
final class Story
{
    /**
     * Class constructor.
     *
     * @param string $path The path parameter.
     * @param string $id The ID parameter.
     * @param array $meta The meta parameter.
     *
     * @return void
     */
    public function __construct(
        public readonly string $path,
        public readonly string $id,
        public readonly array $meta,
    ) {
    }
}
