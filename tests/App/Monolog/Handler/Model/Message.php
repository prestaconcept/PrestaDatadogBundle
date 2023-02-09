<?php

declare(strict_types=1);

namespace Presta\DatadogBundle\Tests\App\Monolog\Handler\Model;

use Monolog\Level;
use Presta\DatadogBundle\Tests\App\Exception\UnexpectedTypeException;
use Presta\DatadogBundle\Tests\App\Model\User;
use Presta\DatadogBundle\Tests\Unit\Monolog\Handler\DatadogHandlerTest;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class Message implements \ArrayAccess
{
    public readonly string $ddsource;
    public readonly string $ddtags;
    public readonly string|null $hostname;
    public readonly string $message;
    public readonly string $service;
    public readonly string $status;

    public function __construct(string $message, Level $level, User|null $user)
    {
        $tags = sprintf(
            "channel:%s,env:%s,level:$level->value,level_name:{$level->getName()}",
            DatadogHandlerTest::DEFAULT_CHANNEL,
            DatadogHandlerTest::DEFAULT_ENV,
        );

        if (null !== $user) {
            $tags .= sprintf(",user:{$user->getUserIdentifier()},user_type:%s", get_class($user));
        }

        $this->ddsource = 'php';
        $this->ddtags = $tags;
        $this->hostname = gethostname() ?: null;
        $this->message = "$message [] []\n";
        $this->service = 'app';
        $this->status = $level->getName();
    }

    public function offsetExists($offset): bool
    {
        if (!\is_string($offset)) {
            throw new UnexpectedTypeException($offset, 'string');
        }

        return PropertyAccess::createPropertyAccessor()->isReadable($this, $offset);
    }

    public function offsetGet($offset): mixed
    {
        if (!\is_string($offset)) {
            throw new UnexpectedTypeException($offset, 'string');
        }

        return PropertyAccess::createPropertyAccessor()->getValue($this, $offset);
    }

    public function offsetSet($offset, $value): void
    {
        if (!\is_string($offset)) {
            throw new UnexpectedTypeException($offset, 'string');
        }

        throw new \RuntimeException("$offset is readonly.");
    }

    public function offsetUnset($offset): void
    {
        if (!\is_string($offset)) {
            throw new UnexpectedTypeException($offset, 'string');
        }

        throw new \RuntimeException("$offset is readonly.");
    }
}
