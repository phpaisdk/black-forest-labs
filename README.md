# aisdk/black-forest-labs

<a href="https://github.com/phpaisdk/black-forest-labs/actions"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/phpaisdk/black-forest-labs/tests.yml?branch=main&label=Tests"></a>
<a href="https://packagist.org/packages/aisdk/black-forest-labs"><img alt="Latest Version" src="https://img.shields.io/packagist/v/aisdk/black-forest-labs"></a>
<a href="https://packagist.org/packages/aisdk/black-forest-labs"><img alt="License" src="https://img.shields.io/packagist/l/aisdk/black-forest-labs"></a>

------

Official Black Forest Labs provider for the PHP AI SDK. Uses BFL's native FLUX image-generation API.

## Installation

```bash
composer require aisdk/black-forest-labs
```

## Basic Usage

```php
use AiSdk\BlackForestLabs;
use AiSdk\Generate;

BlackForestLabs::create(['apiKey' => $_ENV['BFL_API_KEY']]);
$image = Generate::image('A cinematic mountain lake at sunrise')
    ->model(BlackForestLabs::model('flux-2-pro-preview'))
    ->size('1440x810')
    ->run();
```

## Configuration

| Variable | Description | Default |
| --- | --- | --- |
| `BFL_API_KEY` | API key for authentication | Required |
| `BFL_BASE_URL` | Base URL for API requests | `https://api.bfl.ai/v1` |

## Supported Capabilities

| Capability | Support |
| --- | --- |
| Text-to-image generation | Native |
| Async job polling | Native |

The provider submits a native FLUX request, polls BFL's returned `polling_url`, and returns the signed image URL. Use `providerOptions('blackforestlabs', ['raw' => [...]])` for documented BFL image fields outside the portable SDK surface.

## Documentation

- [BFL FLUX image-generation documentation](https://docs.bfl.ai/flux_2/flux2_text_to_image)
- [PHP AI SDK documentation](https://phpaisdk.com/docs/black-forest-labs)
