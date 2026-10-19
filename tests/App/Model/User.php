<?php

declare(strict_types=1);

namespace Presta\DatadogBundle\Tests\App\Model;

use Symfony\Component\Security\Core\User\UserInterface;

final class User implements UserInterface
{
    /**
     * @param non-empty-string $userIdentifier
     */
    public function __construct(private readonly string $userIdentifier = 'john.doe')
    {
    }

    public function getRoles(): array
    {
        return [];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }
}
