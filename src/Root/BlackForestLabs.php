<?php

declare(strict_types=1);

namespace AiSdk;

use AiSdk\BlackForestLabs\BlackForestLabsOptions;
use AiSdk\BlackForestLabs\BlackForestLabsProvider;
use AiSdk\Contracts\Model;

final class BlackForestLabs
{
    private static ?BlackForestLabsProvider $default = null;
    /** @param array<string,mixed> $config */ public static function create(array $config = []): BlackForestLabsProvider
    {
        return self::$default = new BlackForestLabsProvider(BlackForestLabsOptions::fromArray($config));
    } public static function default(): BlackForestLabsProvider
    {
        return self::$default ??= self::create();
    } public static function reset(): void
    {
        self::$default = null;
    } public static function model(string $modelId): Model
    {
        return self::default()->model($modelId);
    }
}
