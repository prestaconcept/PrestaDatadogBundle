# PrestaDatadogBundle

The PrestaDatadogBundle provides a [Monolog][1] handler for [Datadog Log Management][2] platform.

It exposes a friendly configuration to help you make your Symfony application communicate with your Datadog instance painlessly. 

Installation
============

Make sure Composer is installed globally, as explained in the [installation chapter][3] of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require presta/datadog-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

```console
$ composer require presta/datadog-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Presta\DatadogBundle\PrestaDatadogBundle::class => ['all' => true],
];
```

Documentation
=============

- Read the [Getting Started guide][7].
- Read the [Configuration Reference][6].

Contributing
============

Pull requests are welcome.

Thanks to [our contributors][5].

---

*This project is supported by [PrestaConcept][4].*

[1]: https://github.com/Seldaek/monolog
[2]: https://docs.datadoghq.com/logs/
[3]: https://getcomposer.org/doc/00-intro.md
[4]: https://www.prestaconcept.net/
[5]: https://github.com/prestaconcept/PrestaDatadogBundle/graphs/contributors
[6]: docs/configuration_reference.md
[7]: docs/getting_started.md
