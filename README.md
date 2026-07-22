# Black Forest Labs provider for PHP AI SDK

```bash
composer require aisdk/black-forest-labs
```

```php
use AiSdk\BlackForestLabs;
use AiSdk\Generate;

BlackForestLabs::create(['apiKey' => $_ENV['BFL_API_KEY']]);
$image = Generate::image('A cinematic mountain lake at sunrise')
    ->model(BlackForestLabs::model('flux-2-pro-preview'))
    ->size('1440x810')
    ->run();
```

This provider submits a native FLUX request to BFL, polls the returned `polling_url`, and returns BFL’s signed image URL. See [BFL’s image-generation docs](https://docs.bfl.ai/flux_2/flux2_text_to_image).
