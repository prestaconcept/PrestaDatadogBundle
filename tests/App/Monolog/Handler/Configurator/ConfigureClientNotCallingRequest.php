<?php

declare(strict_types=1);

namespace Presta\DatadogBundle\Tests\App\Monolog\Handler\Configurator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ConfigureClientNotCallingRequest
{
    public function __construct(private readonly TestCase $testCase)
    {
    }

    public function __invoke(MockObject&HttpClientInterface $client): void
    {
        $client->expects($this->testCase->never())->method('request');
    }
}
