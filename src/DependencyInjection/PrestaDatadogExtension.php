<?php

declare(strict_types=1);

namespace Presta\DatadogBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class PrestaDatadogExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        $container->setParameter('presta_datadog.env', $mergedConfig['env']);
        $container->setParameter('presta_datadog.api_url', $mergedConfig['api_url']);
        $container->setParameter('presta_datadog.api_key', $mergedConfig['api_key']);
        $container->setParameter('presta_datadog.service_name', $mergedConfig['service_name']);
        $container->setParameter('presta_datadog.excluded_channels', $mergedConfig['excluded_channels']);
    }
}
