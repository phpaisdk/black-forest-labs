<?php

declare(strict_types=1);

namespace AiSdk\BlackForestLabs;

use AiSdk\Support\Sdk;
use AiSdk\Utils\Support\Env;
use AiSdk\Utils\Support\Url;

final class BlackForestLabsOptions
{
    public const string DEFAULT_BASE_URL = 'https://api.bfl.ai/v1';
    public const string PROVIDER_NAME = 'blackforestlabs';
    /** @param array<string,string> $headers */ public function __construct(public readonly string $apiKey, public readonly string $baseUrl = self::DEFAULT_BASE_URL, public readonly int $pollingIntervalMs = 500, public readonly array $headers = [], public readonly ?Sdk $sdk = null) {} /** @param array<string,mixed> $config */ public static function fromArray(array $config = []): self
    {
        $key = Env::loadApiKey(isset($config['apiKey']) ? (string) $config['apiKey'] : null, 'BFL_API_KEY', self::PROVIDER_NAME);
        $base = Url::withoutTrailingSlash(Env::loadOptionalSetting(isset($config['baseUrl']) ? (string) $config['baseUrl'] : null, 'BFL_BASE_URL') ?? self::DEFAULT_BASE_URL);
        /** @var array<string,string> $headers */ $headers = isset($config['headers']) && is_array($config['headers']) ? $config['headers'] : [];
        $sdk = $config['sdk'] ?? null;
        return new self($key, $base, max(0, (int) ($config['pollingIntervalMs'] ?? 500)), $headers, $sdk instanceof Sdk ? $sdk : null);
    } /** @return array<string,string> */ public function authHeaders(): array
    {
        return array_merge(['x-key' => $this->apiKey], $this->headers);
    }
}
