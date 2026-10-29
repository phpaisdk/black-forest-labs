<?php

declare(strict_types=1);

use AiSdk\BlackForestLabs\BlackForestLabsOptions;
use AiSdk\BlackForestLabs\Models\BlackForestLabsImageModel;
use AiSdk\BlackForestLabs\Tests\Fakes\SequenceHttpClient;
use AiSdk\Exceptions\InvalidResponseException;
use AiSdk\Requests\ImageRequest;
use AiSdk\Support\Sdk;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;

require_once __DIR__ . '/Fakes/SequenceHttpClient.php';

function followUp(string $url, string $baseUrl = 'https://api.example.com/v1'): array
{
    $client = new SequenceHttpClient([
        new Response(200, [], json_encode(['id' => 'job', 'polling_url' => $url], JSON_THROW_ON_ERROR)),
        new Response(200, [], json_encode(['status' => 'Ready', 'result' => ['sample' => 'https://media.example/image.png']], JSON_THROW_ON_ERROR)),
    ]);
    $factory = new Psr17Factory();
    $model = new BlackForestLabsImageModel('flux', new BlackForestLabsOptions(apiKey: 'secret', baseUrl: $baseUrl, pollingIntervalMs: 0, sdk: new Sdk($client, $factory, $factory)));
    $run = fn() => $model->generate(new ImageRequest('Hello'));

    return [$run, $client];
}

it('permits follow-ups only on the configured origin', function (string $url) {
    [$run, $client] = followUp($url);
    $run();
    expect($client->requests)->toHaveCount(2);
    expect(end($client->requests)->getHeaderLine('x-key'))->toBe('secret');
})->with([
    'https://api.example.com/result?token=signed',
    'https://API.EXAMPLE.COM:443/result',
]);

it('rejects untrusted follow-up origins before sending credentials', function (string $url) {
    [$run, $client] = followUp($url);
    try {
        $run();
        test()->fail('Expected origin rejection.');
    } catch (InvalidResponseException $error) {
        expect($error->getMessage())->toBe('Provider follow-up URL has an untrusted origin.');
        expect((string) $error)->not->toContain('signed-secret');
    }
    expect($client->requests)->toHaveCount(1);
})->with([
    'https://evil.test/result?token=signed-secret',
    'http://api.example.com/result',
    'https://api.example.com:444/result',
    'https://api.example.com@evil.test/result',
    'https://user:password@api.example.com/result',
    'https://api.example.com.evil.test/result',
    'https://api.example.com\\@evil.test/result',
    '/relative/result',
    '//api.example.com/result',
    'https://api.example.com:99999/result',
]);

it('honors an explicitly configured non-default port', function () {
    [$run, $client] = followUp('https://custom.example:8443/result', 'https://custom.example:8443/v1');
    $run();
    expect($client->requests)->toHaveCount(2);
});

it('permits documented BFL regional polling origins only for BFL configurations', function (string $host) {
    [$run, $client] = followUp('https://' . $host . '/v1/get_result', 'https://api.bfl.ai/v1');
    $run();
    expect($client->requests)->toHaveCount(2);
    foreach (['http://' . $host . '/result', 'https://' . $host . ':444/result', 'https://' . $host . '.evil.test/result'] as $url) {
        [$run, $client] = followUp($url, 'https://api.bfl.ai/v1');
        expect($run)->toThrow(InvalidResponseException::class);
        expect($client->requests)->toHaveCount(1);
    }
    [$run, $client] = followUp('https://' . $host . '/result', 'https://custom.example');
    expect($run)->toThrow(InvalidResponseException::class);
    expect($client->requests)->toHaveCount(1);
})->with(['api.us1.bfl.ai', 'api.eu1.bfl.ai']);
