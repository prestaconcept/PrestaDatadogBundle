<?php

declare(strict_types=1);

namespace Presta\DatadogBundle\Tests\Unit\Monolog\Handler;

use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Presta\DatadogBundle\Monolog\Handler\DatadogHandler;
use Presta\DatadogBundle\Tests\App\Model\User;
use Presta\DatadogBundle\Tests\App\Monolog\Handler\Configurator\ConfigureClientCallingRequest;
use Presta\DatadogBundle\Tests\App\Monolog\Handler\Configurator\ConfigureClientNotCallingRequest;
use Presta\DatadogBundle\Tests\App\Monolog\Handler\Configurator\ConfigureSecurityCallingGetUser;
use Presta\DatadogBundle\Tests\App\Monolog\Handler\Configurator\ConfigureSecurityNotCallingGetUser;
use Presta\DatadogBundle\Tests\App\Monolog\Handler\Model\Message;
use Presta\DatadogBundle\Tests\App\Monolog\Handler\Model\Record;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class DatadogHandlerTest extends TestCase
{
    public const DEFAULT_API_KEY = 'S3cr3t';
    public const DEFAULT_API_URL = 'https://http-intake.logs.datadoghq.eu/api/v2/logs';
    public const DEFAULT_CHANNEL = 'main';
    public const DEFAULT_ENV = 'test';
    private const DEFAULT_EXCLUDED_CHANNELS = [];
    private const DEFAULT_MESSAGE = 'Lorem ipsum';
    private const DEFAULT_SERVICE_NAME = 'app';
    private const EMPTY_API_KEY = '';

    /**
     * @param iterable<Record> $records
     * @param array<string> $excludedChannels
     *
     * @dataProvider records
     */
    public function testLogging(
        iterable $records,
        callable $configureSecurity,
        callable $configureClient,
        string $apiKey = self::DEFAULT_API_KEY,
        array $excludedChannels = self::DEFAULT_EXCLUDED_CHANNELS
    ): void {
        $security = $this->createMock(Security::class);
        $configureSecurity($security);

        $client = $this->createMock(HttpClientInterface::class);
        $configureClient($client);

        $handlers = [
            new DatadogHandler(
                $client,
                $security,
                self::DEFAULT_ENV,
                self::DEFAULT_API_URL,
                $apiKey,
                self::DEFAULT_SERVICE_NAME,
                $excludedChannels,
            ),
        ];

        /** @var array<Logger> $loggers */
        $loggers = [];

        foreach ($records as $record) {
            $logger = $loggers[$record->channel] ?? new Logger($record->channel, $handlers);
            $logger->log($record->level, $record->message, $record->context);

            $loggers[$record->channel] = $logger;
        }
    }

    public function records(): iterable
    {
        $channel = self::DEFAULT_CHANNEL;
        $debugLevel = Level::Debug;
        $errorLevel = Level::Error;

        yield from $this->lowerThanErrorLevelSingleRecords();
        yield from $this->higherThanOrEqualToErrorLevelSingleRecords();

        yield "a record of level {$debugLevel->getName()} then a record of level {$errorLevel->getName()} "
            . 'should both be sent to Datadog in 1 request' => [
            [
                new Record($channel, $debugLevel, '1st message'),
                new Record($channel, $errorLevel, '2nd message'),
            ],
            new ConfigureSecurityCallingGetUser($this, null, 2),
            new ConfigureClientCallingRequest(
                $this,
                [
                    new Message('1st message', $debugLevel, null),
                    new Message('2nd message', $errorLevel, null),
                ],
            ),
        ];

        yield "a record of level {$errorLevel->getName()} then a record of level {$debugLevel->getName()} "
            . "should end up with only the {$errorLevel->getName()} record being sent to Datadog" => [
            [
                new Record($channel, $errorLevel, '1st message'),
                new Record($channel, $debugLevel, '2nd message'),
            ],
            new ConfigureSecurityCallingGetUser($this, null, 2),
            new ConfigureClientCallingRequest(
                $this,
                [
                    new Message('1st message', $errorLevel, null),
                ],
            ),
        ];

        yield "a record of level {$debugLevel->getName()} in a not excluded channel "
            . "then a record of level {$errorLevel->getName()} in an excluded channel "
            . "should end up with none of the records to be sent to Datadog" => [
            [
                new Record($channel, $debugLevel, '1st message'),
                new Record('deprecation', $errorLevel, '2nd message'),
            ],
            new ConfigureSecurityCallingGetUser($this, null),
            new ConfigureClientNotCallingRequest($this),
            self::DEFAULT_API_KEY,
            ['deprecation'],
        ];

        yield "a record of level {$errorLevel->getName()} in a not excluded channel including some context "
            . 'should submit it\'s context to Datadog' => [
            [new Record($channel, $errorLevel, self::DEFAULT_MESSAGE, ['foo' => 'bar'])],
            new ConfigureSecurityCallingGetUser($this, null),
            new ConfigureClientCallingRequest(
                $this,
                [
                    new Message(self::DEFAULT_MESSAGE, $errorLevel, null, ['foo' => 'bar']),
                ],
            ),
        ];
    }

    /**
     * @return iterable<string, array<mixed>>
     */
    private function lowerThanErrorLevelSingleRecords(): iterable
    {
        $channel = self::DEFAULT_CHANNEL;
        $message = self::DEFAULT_MESSAGE;
        $user = new User();

        foreach ($this->lowerThanErrorLevels() as $level) {
            yield from $this->withoutApiKeySingleRecord($channel, $level, $message);
            yield from $this->inExcludedChannelSingleRecord($channel, $level, $message);

            $levelName = $level->getName();

            yield "a single record of level $levelName for an anonymous user should not be sent to Datadog" => [
                [new Record($channel, $level, $message)],
                new ConfigureSecurityCallingGetUser($this, null),
                new ConfigureClientNotCallingRequest($this),
            ];

            yield "a single record of level $levelName for an authenticated user should not be sent to Datadog" => [
                [new Record($channel, $level, $message)],
                new ConfigureSecurityCallingGetUser($this, $user),
                new ConfigureClientNotCallingRequest($this),
            ];
        }
    }

    /**
     * @return iterable<string, array<mixed>>
     */
    private function higherThanOrEqualToErrorLevelSingleRecords(): iterable
    {
        $channel = self::DEFAULT_CHANNEL;
        $message = self::DEFAULT_MESSAGE;
        $user = new User();

        foreach ($this->higherThanOrEqualToErrorLevels() as $level) {
            yield from $this->withoutApiKeySingleRecord($channel, $level, $message);
            yield from $this->inExcludedChannelSingleRecord($channel, $level, $message);

            $levelName = $level->getName();

            yield "a single record of level $levelName for an anonymous user should be sent to Datadog" => [
                [new Record($channel, $level, $message)],
                new ConfigureSecurityCallingGetUser($this, null),
                new ConfigureClientCallingRequest($this, [new Message($message, $level, null)]),
            ];

            yield "a single record of level $levelName for an authenticated user should be sent to Datadog" => [
                [new Record($channel, $level, $message)],
                new ConfigureSecurityCallingGetUser($this, $user),
                new ConfigureClientCallingRequest($this, [new Message($message, $level, $user)]),
            ];
        }
    }

    /**
     * @return iterable<string, array<mixed>>
     */
    private function withoutApiKeySingleRecord(string $channel, Level $level, string $message): iterable
    {
        yield "a single record of level {$level->getName()} without api key should not be sent to Datadog" => [
            [new Record($channel, $level, $message)],
            new ConfigureSecurityNotCallingGetUser($this),
            new ConfigureClientNotCallingRequest($this),
            self::EMPTY_API_KEY,
        ];
    }

    /**
     * @return iterable<string, array<mixed>>
     */
    private function inExcludedChannelSingleRecord(string $channel, Level $level, string $message): iterable
    {
        yield "a single record of level {$level->getName()} in an excluded channel should not be sent to Datadog" => [
            [new Record($channel, $level, $message)],
            new ConfigureSecurityNotCallingGetUser($this),
            new ConfigureClientNotCallingRequest($this),
            self::DEFAULT_API_KEY,
            [$channel, md5($channel)],
        ];
    }

    /**
     * @return iterable<Level>
     */
    private function lowerThanErrorLevels(): iterable
    {
        return array_filter(Level::cases(), static fn (Level $level): bool => $level->isLowerThan(Level::Error));
    }

    /**
     * @return iterable<Level>
     */
    private function higherThanOrEqualToErrorLevels(): iterable
    {
        return array_filter(Level::cases(), static fn (Level $level): bool => $level->isHigherThan(Level::Warning));
    }
}
