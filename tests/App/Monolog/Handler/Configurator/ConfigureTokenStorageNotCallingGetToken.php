<?php

declare(strict_types=1);

namespace Presta\DatadogBundle\Tests\App\Monolog\Handler\Configurator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class ConfigureTokenStorageNotCallingGetToken
{
    public function __construct(private readonly TestCase $testCase)
    {
    }

    public function __invoke(MockObject&TokenStorageInterface $tokenStorage): void
    {
        $tokenStorage->expects($this->testCase->never())->method('getToken');
    }
}
