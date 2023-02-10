# Getting started

Prequisites
===========

First make sure you have [installed Monolog][1].

You will also need a [Datadog account][2] and an access to their [Log Management][4] product.

Usage
=====

Configure the bundle
--------------------

First you need to make sure the `config/packages/presta_datadog.yaml` file exists, or to create it.

You will have to set the 3 following required options:

```yaml
# config/packages/presta_datadog.yaml

presta_datadog:
    env: '%env(APP_ENV)%'
    api_key: 'Your Datadog API key'
    service_name: 'Your project name'
```

> The `service_name` value is nothing but a suggestion, feel free to set any name that will help you identify your application's logs in the Datadog Log Management platform.

You can find all available options in the [Configuration Reference][9] file.

Configure Monolog
-----------------

Once the bundle is configured, you need to tell Monolog to [use it's handler][5].

Let's assume you want to use it only in the production environment.

```yaml
# config/packages/monolog.yaml

when@prod:
    monolog:
        handlers:
            datadog:
                type: 'service'
                id: 'Presta\DatadogBundle\Monolog\Handler\DatadogHandler'
```

How it works?
=============

Once setup properly, any time your application logs a message, it will be passed to the handler.

If the channel the message was logged to is in the configured excluded channels (see the [Configuration Reference][9] file), the log record will be skipped.

If the [log level][6] is less than [error][7], nothing will be sent through the Datadog [logs API][3].
The log record will be stacked in the handler instead.

At the end of the process, if no messages with a [level][6] equal to or higher than [error][7] has been logged, the stack is cleared and nothing else happens.

However if at any point of the process a message is logged with a [level][6] equal to or higher than [error][7], all the log records previously stacked by the handler will be sent through a `POST` request on the Datadog [logs API][3].

That was it!
============

From now on, any log that reaches at least the [error level][7] will trigger sending all previously stacked logs to Datadog through their [logs API][3].

---

You may return to the [README.md][8] or read the [Configuration Reference][9] file.

[1]: https://github.com/Seldaek/monolog#installation
[2]: https://app.datadoghq.com/account/login
[3]: https://docs.datadoghq.com/api/latest/logs/
[4]: https://www.datadoghq.com/product/log-management/
[5]: https://symfony.com/doc/current/logging.html#handlers-writing-logs-to-different-locations
[6]: https://github.com/php-fig/log/blob/master/src/LogLevel.php
[7]: https://github.com/php-fig/log/blob/master/src/LogLevel.php#L13
[8]: ../README.md
[9]: configuration_reference.md
