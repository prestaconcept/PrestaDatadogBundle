<?php

declare(strict_types=1);

namespace Presta\DatadogBundle\Tests\App\Monolog\Handler\Configurator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;

final class ConfigureSecurityNotCallingGetUser
{
    public function __construct(private readonly TestCase $testCase)
    {
    }

    public function __invoke(MockObject&Security $security): void
    {
        $security->expects($this->testCase->never())->method('getUser');
    }
}
