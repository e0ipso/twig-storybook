<?php

namespace TwigStorybook;

final class Story {

  public function __construct(
    public readonly string $path,
    public readonly string $id,
    public readonly array $meta,
  ) {}

}
