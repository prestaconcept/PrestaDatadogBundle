<?php

declare(strict_types=1);

namespace Presta\DatadogBundle\Monolog\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class DatadogHandler extends AbstractProcessingHandler
{
    /**
     * @var array<string>
     */
    private readonly array $excludedChannels;

    /**
     * @var array<array{
     *     ddsource: string,
     *     ddtags: string,
     *     hostname: string|null,
     *     message: string,
     *     service: string,
     *     status: string,
     * }>
     */
    private array $messages = [];

    /**
     * @param array<string> $excludedChannels
     *
     * @phpstan-param value-of<Level::VALUES>|value-of<Level::NAMES>|Level $level
     */
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly Security $security,
        private readonly string $env,
        private readonly string $apiUrl,
        private readonly string $apiKey,
        private readonly string $serviceName,
        array $excludedChannels,
        int|string|Level $level = Level::Debug,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);

        $this->excludedChannels = \array_filter($excludedChannels);
    }

    protected function write(LogRecord $record): void
    {
        // do not use that logger if there is no API key
        if (0 === \strlen($this->apiKey)) {
            return;
        }

        // do not bufferize messages from excluded channels
        if (\in_array($record->channel, $this->excludedChannels, true)) {
            return;
        }

        // this should not happen in a real world context
        // @codeCoverageIgnoreStart
        if (!\is_string($record->formatted)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $tags = [
            'channel' => $record->channel,
            'env' => $this->env,
            'level' => $record->level->value,
            'level_name' => $record->level->getName(),
        ];

        $user = $this->security->getUser();
        if ($user instanceof UserInterface) {
            $tags['user'] = $user->getUserIdentifier();
            $tags['user_type'] = str_replace('App\\Entity\\', '', get_class($user));
        }

        $message = preg_replace('/\[[^]]+] [^:]+: /', '', $record->formatted);
        \assert(\is_string($message));

        $this->messages[] = [
            'ddsource' => 'php',
            'ddtags' => implode(
                ',',
                \array_map(
                    static fn (string $key, $value): string => "$key:$value",
                    array_keys($tags),
                    $tags,
                ),
            ),
            'hostname' => gethostname() ?: null,
            'message' => $message,
            'service' => $this->serviceName,
            'status' => $record->level->getName(),
        ];

        if ($record->level->value < Level::Error->value) {
            return;
        }

        $this->client->request(
            Request::METHOD_POST,
            $this->apiUrl,
            [
                'headers' => [
                    'DD-API-KEY' => $this->apiKey,
                ],
                'json' => $this->messages,
            ],
        );
    }
}
