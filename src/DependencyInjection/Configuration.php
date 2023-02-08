<?php

declare(strict_types=1);

namespace Presta\DatadogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('presta_datadog');

        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('env')
                    ->info('The environment the application is running in.')
                    ->example('%env(APP_ENV)%')
                    ->isRequired()
                ->end()
                ->scalarNode('api_url')
                    ->info('The Datadog API url to send logs to.')
                    ->defaultValue('https://http-intake.logs.datadoghq.eu/api/v2/logs')
                ->end()
                ->scalarNode('api_key')
                    ->info(<<<TEXT
The Datadog API key used to sign requests.
See https://docs.datadoghq.com/account_management/api-app-keys/#api-keys to learn how to generate it.
TEXT
                    )
                    ->example('%env(DATADOG_API_KEY)%')
                    ->isRequired()
                ->end()
                ->scalarNode('service_name')
                    ->info('An identifier for your application\'s logs in Datadog Log Management platform.')
                    ->example('The name of your application.')
                    ->isRequired()
                ->end()
                ->variableNode('excluded_channels')
                    ->info('A list of monolog channels not to send to the Datadog API.')
                    ->example('%env(csv:DATADOG_EXCLUDED_CHANNELS)%')
                    ->validate()
                        ->ifTrue(static fn ($value): bool => !\is_array($value))
                        ->thenInvalid('an array was expected.')
                    ->end()
                    ->defaultValue([])
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
