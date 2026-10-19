<?php

declare(strict_types=1);

namespace Presta\DatadogBundle\Tests\App\Monolog\Handler\Configurator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Presta\DatadogBundle\Tests\App\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class ConfigureTokenStorageCallingGetToken
{
    public function __construct(
        private readonly TestCase $testCase,
        private readonly User|null $user,
        private readonly int $getTokenCallsCount = 1,
    ) {
    }

    public function __invoke(MockObject&TokenStorageInterface $tokenStorage): void
    {
        $tokenStorage
            ->expects($this->testCase->exactly($this->getTokenCallsCount))
            ->method('getToken')
            ->willReturn(null !== $this->user ? new UsernamePasswordToken($this->user, 'main') : null)
        ;
    }
}
