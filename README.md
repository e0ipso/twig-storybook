# Twig Storybook

![GitHub](https://img.shields.io/github/license/e0ipso/twig-storybook)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/e0ipso/twig-storybook)
![GitHub issues](https://img.shields.io/github/issues-raw/e0ipso/twig-storybook)

Twig Storybook is a Composer package that enhances the Twig templating language by introducing two new Twig tags: `stories` and `story`. With Twig Storybook, you can easily create and manage Storybook stories directly in your Twig templates, making it a powerful tool for documenting and showcasing your frontend components.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Example](#example)
- [Contributing](#contributing)
- [License](#license)

## Installation

You can install Twig Storybook via Composer:

```bash
composer require e0ipso/twig-storybook
```

## Usage

### With Drupal
You don't need to use this package directly, use the [Twig Storybook](https://www.drupal.org/project/twig_storybook)
drupal module instead.

### Adding Twig Tags

After installing the package, you need to register the Twig tags in your Twig environment. Here's how you can do it:

```php
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use E0ipso\TwigStorybook\Twig\StorybookExtension;

// Initialize the Twig environment
$loader = new FilesystemLoader('path/to/your/templates');
$twig = new Environment($loader);

// Register the Storybook extension
$twig->addExtension(new \TwigStorybook\Twig\TwigExtension());
```

### Creating Stories

Once the Twig Storybook extension is registered, you can start creating stories within your Twig templates. We recommend
writing the stories in a file with name `<file-name>.stories.twig`.

- Use the `{% stories %}` tag to define a group of stories.
- Use the `{% story %}` tag to define an individual story.

Here's an example:

```twig
{% set lorem = 'Hey!' %}
{% stories 'Button' %}
    {% story 'Primary' with {
      parameters: { server: { params: { foo: 'Yay!' } } },
      args: { bar: 'Yes' }
    } %}
      <div class="decorator">
        <button class="btn-primary">{{ foo }} / {{ bar }}</button>
        <p>{{ lorem }}</p>
      </div>
    {% endstory %}

    {% story 'Secondary' with {
      parameters: { server: { params: { foo: 'Nay!' } } },
      args: { bar: 'No' }
    } %}
        <button class="btn-secondary">{{ foo }} / {{ bar }}</button>
    {% endstory %}
{% endstories %}
```

### Rendering Stories

To render the Storybook stories, you can use the `StoryRenderer` service.

To use this service, wire the services to the container. The Twig Storybook Drupal module does it like this:

```yaml
services:
  logger.channel.twig_storybook:
    parent: logger.channel_base
    arguments: ['twig_storybook']

  TwigStorybook\Service\StoryCollector: {}
  TwigStorybook\Service\StoryRenderer:
    arguments:
      - '@TwigStorybook\Service\StoryCollector'
      - '@logger.channel.twig_storybook'
      - '%app.root%'
    calls:
      - ['setTwigEnvironment', ['@twig']]

  TwigStorybook\Twig\TwigExtension:
    arguments:
      - '@TwigStorybook\Service\StoryRenderer'
      - '@TwigStorybook\Service\StoryCollector'
      - '%app.root%'
    tags:
      - { name: twig.extension }
```

Then you can use the `$storyRenderer` to generate the Storybook stories in JSON format. Or to render a story in a
`*.stories.twig` template.

## Contributing

We welcome contributions from the community. If you want to contribute to Twig Storybook, please follow these guidelines:

1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Make your changes and ensure all tests pass.
4. Submit a pull request with a clear description of your changes.

## License

Twig Storybook is open-source software licensed under the [GPL-2 License](LICENSE).
