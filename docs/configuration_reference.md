# Configuration Reference

This is where you will find the detailed configuration options for the bundle.

The example below show either suggestions or default values.

```yaml
# config/packages/presta_datadog.yaml

presta_datadog:
    env: '%env(APP_ENV)%' # [example] The environment the application is running in.
    api_url: 'https://http-intake.logs.datadoghq.eu/api/v2/logs' # [default] The Datadog API url to send logs to.
    api_key: '%env(DATADOG_API_KEY)%' # [example] The Datadog API key used to sign requests.
    service_name: 'app' # [example] An identifier for your application's logs in Datadog Log Management platform.
    excluded_channels: '%env(csv:DATADOG_EXCLUDED_CHANNELS)%' # [example] A list of monolog channels not to send to the Datadog API.
```

Considering the following environment variables are defined:

```dotenv
APP_ENV=prod # "dev", "test" or any other environment supported in your application are fine
DATADOG_API_KEY=S3cr3t # you need to set your actual Datadog API key here
DATADOG_EXCLUDED_CHANNELS=deprecation # a comma separated list of values
```

You may dump the configuration reference by running the following command:

```shell
bin/console config:dump-reference presta_datadog
```

---

You may return to the [README.md][1] or read the [Getting Started][2] file.

[1]: ../README.md
[2]: getting_started.md
