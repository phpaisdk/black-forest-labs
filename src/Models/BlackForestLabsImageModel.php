<?php

declare(strict_types=1);

namespace AiSdk\BlackForestLabs\Models;

use AiSdk\BlackForestLabs\BlackForestLabsOptions;
use AiSdk\Contracts\BaseModel;
use AiSdk\Contracts\ImageModelInterface;
use AiSdk\Exceptions\InvalidResponseException;
use AiSdk\Requests\ImageRequest;
use AiSdk\Responses\ImageResponse;
use AiSdk\Results\ImageData;
use AiSdk\Utils\Support\Url;

final class BlackForestLabsImageModel extends BaseModel implements ImageModelInterface
{
    // BFL global requests can return regional polling URLs:
    // https://docs.bfl.ai/quick_start/generating_images
    private const POLLING_ORIGINS = ['https://api.bfl.ai', 'https://api.us1.bfl.ai', 'https://api.eu1.bfl.ai'];

    public function __construct(private readonly string $modelId, private readonly BlackForestLabsOptions $options) {} public function provider(): string
    {
        return BlackForestLabsOptions::PROVIDER_NAME;
    } public function modelId(): string
    {
        return $this->modelId;
    } public function generate(ImageRequest $request): ImageResponse
    {
        $opts = $request->providerOptionsFor($this->provider());
        $body = array_replace(['prompt' => $request->prompt], is_array($opts['raw'] ?? null) ? $opts['raw'] : []);
        if ($request->size !== null && preg_match('/^(\d+)x(\d+)$/', $request->size, $matches) === 1) {
            $body['width'] = (int) $matches[1];
            $body['height'] = (int) $matches[2];
        } if ($request->aspectRatio !== null) {
            $body['aspect_ratio'] = $request->aspectRatio;
        } if ($request->seed !== null) {
            $body['seed'] = $request->seed;
        } $submitted = $this->runner($this->options->sdk)->postJson(Url::joinPath($this->options->baseUrl, '/' . $this->modelId), $body, $this->options->authHeaders(), $this->provider());
        $pollingUrl = (string) ($submitted['polling_url'] ?? '');
        $id = (string) ($submitted['id'] ?? '');
        if ($pollingUrl === '' || $id === '') {
            throw new InvalidResponseException('Black Forest Labs response missing id or polling_url.');
        }
        $this->assertFollowUpOrigin($pollingUrl, $this->options->baseUrl);
        do {
            if ($this->options->pollingIntervalMs > 0) {
                usleep($this->options->pollingIntervalMs * 1000);
            } $result = $this->runner($this->options->sdk)->getJson($pollingUrl, $this->options->authHeaders(), $this->provider());
        } while (in_array($result['status'] ?? '', ['Pending', 'Processing'], true));
        if (($result['status'] ?? '') !== 'Ready') {
            throw new InvalidResponseException('Black Forest Labs generation failed.');
        } $sample = (string) ($result['result']['sample'] ?? '');
        if ($sample === '') {
            throw new InvalidResponseException('Black Forest Labs result missing sample URL.');
        } return new ImageResponse([new ImageData(url: $sample, mimeType: (string) ($body['output_format'] ?? 'image/jpeg'))], rawResponse: $result, providerMetadata: ['blackforestlabs' => ['id' => $id]]);
    }

    private function assertFollowUpOrigin(string $url, string $baseUrl): void
    {
        $origin = static function (string $value): ?string {
            if (preg_match('/[\x00-\x20\x7f\\\\]/', $value) === 1) {
                return null;
            }
            $parts = parse_url($value);
            if ($parts === false || ! isset($parts['scheme'], $parts['host'])
                || isset($parts['user']) || isset($parts['pass'])) {
                return null;
            }
            $scheme = strtolower($parts['scheme']);
            if (! in_array($scheme, ['http', 'https'], true) || ($parts['port'] ?? 1) < 1) {
                return null;
            }

            return $scheme . '://' . strtolower($parts['host']) . ':' . ($parts['port'] ?? ($scheme === 'https' ? 443 : 80));
        };
        $actual = $origin($url);
        $expected = $origin($baseUrl);
        if ($actual !== null && $expected !== null && $actual === $expected) {
            return;
        }

        $allowedOrigins = array_map($origin, self::POLLING_ORIGINS);
        if ($actual !== null && $expected !== null
            && in_array($expected, $allowedOrigins, true) && in_array($actual, $allowedOrigins, true)) {
            return;
        }

        throw new InvalidResponseException('Provider follow-up URL has an untrusted origin.');
    }
}
