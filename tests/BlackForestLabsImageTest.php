<?php

declare(strict_types=1);
use AiSdk\BlackForestLabs;
use AiSdk\Generate;
use AiSdk\Support\Sdk;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

afterEach(function (): void {
    Generate::reset();
    BlackForestLabs::reset();
});
it('submits and polls a native FLUX request', function (): void {
    $client = new class implements ClientInterface {
        public array $requests = [];
        private array $responses = [['id' => 'bfl_1', 'polling_url' => 'https://api.bfl.ai/v1/get_result?id=bfl_1'], ['status' => 'Ready', 'result' => ['sample' => 'https://signed.example/image.jpeg']]];
        public function sendRequest(RequestInterface $request): ResponseInterface
        {
            $this->requests[] = $request;
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(array_shift($this->responses)));
        }
    };
    $factory = new Psr17Factory();
    Generate::configure(new Sdk($client, $factory, $factory));
    BlackForestLabs::create(['apiKey' => 'bfl-test', 'pollingIntervalMs' => 0]);
    $result = Generate::image('Mountain lake')->model(BlackForestLabs::model('flux-2-pro-preview'))->size('1440x810')->run();
    expect($result->output->url)->toBe('https://signed.example/image.jpeg')->and((string) $client->requests[0]->getUri())->toBe('https://api.bfl.ai/v1/flux-2-pro-preview')->and($client->requests[0]->getHeaderLine('x-key'))->toBe('bfl-test');
});
