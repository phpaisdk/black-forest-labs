<?php

declare(strict_types=1);

namespace AiSdk\BlackForestLabs;

use AiSdk\BlackForestLabs\Models\BlackForestLabsImageModel;
use AiSdk\Contracts\BaseProvider;
use AiSdk\Contracts\ImageModelInterface;
use AiSdk\Contracts\ImageProviderInterface;

final class BlackForestLabsProvider extends BaseProvider implements ImageProviderInterface
{
    public function __construct(public readonly BlackForestLabsOptions $options) {} public function name(): string
    {
        return BlackForestLabsOptions::PROVIDER_NAME;
    } protected function imageModel(string $modelId): ImageModelInterface
    {
        return new BlackForestLabsImageModel($modelId, $this->options);
    }
}
