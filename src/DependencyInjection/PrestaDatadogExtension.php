<?php

declare(strict_types=1);

namespace Presta\DatadogBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class PrestaDatadogExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        /**
         * @var array{
         *     env: string,
         *     api_url: string,
         *     api_key: string,
         *     service_name: string,
         *     excluded_channels: array<string>,
         * } $mergedConfig
         */
        $mergedConfig = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        $container->setParameter('presta_datadog.env', $mergedConfig['env']);
        $container->setParameter('presta_datadog.api_url', $mergedConfig['api_url']);
        $container->setParameter('presta_datadog.api_key', $mergedConfig['api_key']);
        $container->setParameter('presta_datadog.service_name', $mergedConfig['service_name']);
        $container->setParameter('presta_datadog.excluded_channels', $mergedConfig['excluded_channels']);
    }
}
