<?php

declare(strict_types=1);

namespace Presta\DatadogBundle\Tests\App\Monolog\Handler\Configurator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Presta\DatadogBundle\Tests\App\Model\User;
use Symfony\Component\Security\Core\Security;

final class ConfigureSecurityCallingGetUser
{
    public function __construct(
        private readonly TestCase $testCase,
        private readonly User|null $user,
        private readonly int $getUserCallsCount = 1,
    ) {
    }

    public function __invoke(MockObject&Security $security): void
    {
        $security
            ->expects($this->testCase->exactly($this->getUserCallsCount))
            ->method('getUser')
            ->willReturn($this->user)
        ;
    }
}
