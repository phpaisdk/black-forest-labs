# PHP AI SDK — Black Forest Labs

Image generation with Black Forest Labs for the framework-agnostic [PHP AI SDK](https://phpaisdk.com).

## Installation

```bash
composer require aisdk/black-forest-labs
```

Set `BFL_API_KEY` in your environment.

## Usage

```php
use AiSdk\BlackForestLabs;
use AiSdk\Generate;

$result = Generate::image('A mountain lake at sunrise, soft natural light.')
    ->model(BlackForestLabs::model('flux-2-pro-preview'))
    ->run();

$result->output->save(__DIR__.'/image.png');
```

## Documentation

[Read the documentation](https://phpaisdk.com/docs/0.x/providers/black-forest-labs) for configuration, supported features, and more examples.

[Contributing](https://github.com/phpaisdk/.github/blob/main/CONTRIBUTING.md) · [Support](https://github.com/phpaisdk/.github/blob/main/SUPPORT.md)

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
