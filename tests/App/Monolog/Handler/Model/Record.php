<?php

declare(strict_types=1);

namespace Presta\DatadogBundle\Tests\App\Monolog\Handler\Model;

use Monolog\Level;

final class Record
{
    public function __construct(
        public readonly string $channel,
        public readonly Level $level,
        public readonly string $message,
    ) {
    }
}
