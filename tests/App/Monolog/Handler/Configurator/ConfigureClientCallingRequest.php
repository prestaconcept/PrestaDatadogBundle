<?php

declare(strict_types=1);

namespace Presta\DatadogBundle\Tests\App\Monolog\Handler\Configurator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Presta\DatadogBundle\Tests\App\Monolog\Handler\Model\Message;
use Presta\DatadogBundle\Tests\Unit\Monolog\Handler\DatadogHandlerTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ConfigureClientCallingRequest
{
    /**
     * @param array<Message> $messages
     */
    public function __construct(private readonly TestCase $testCase, private readonly array $messages)
    {
    }

    public function __invoke(MockObject&HttpClientInterface $client): void
    {
        $client
            ->expects($this->testCase->once())
            ->method('request')
            ->with(
                Request::METHOD_POST,
                DatadogHandlerTest::DEFAULT_API_URL,
                [
                    'headers' => [
                        'DD-API-KEY' => DatadogHandlerTest::DEFAULT_API_KEY,
                    ],
                    'json' => array_map(fn (Message $message): array => (array) $message, $this->messages),
                ],
            )
        ;
    }
}
