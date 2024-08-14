# Load Tests

Currently, load tests are meant to be run manually from a developer's machine.
In the future, it should be added to GitHub Actions or something like that and the results integrated into our Grafana dashboard.

## Pre-Requisites

The only requirement is k6 -> https://grafana.com/docs/k6/latest/set-up/install-k6/.

## How to Run

Go to [/load-tests](.) directory and run the following command.

```shell
k6 run index.js --env APP_ENV="qa2" --config options/average-load.json
```

The above command will run load tests directed towards API QA2 instance using "Average Load" preset.

### Environments

To specify an environment use `--env APP_ENV="<ENVIRONMENT_NAME>"` (defaults to local Blemmyae).
See `getHost()` function in [lib/base.js](lib/base.js) for the full list of supported `<ENVIRONMENT_NAME>`.

### Options

All options presets are located in [options](options) directory.
See [k6 documentation on options](https://grafana.com/docs/k6/latest/using-k6/k6-options/how-to/) for more details.

## Extending Tests

There are 2 ways to extend tests. The first one is to add more options. See https://grafana.com/docs/k6/latest/testing-guides/test-types/ for more details.

The second one is to write more actual tests. Add them to [lib/tests.js](lib/tests.js). New queries put into [lib/queries](lib/queries) directory. When using `open()` to load new queries keep in mind that it cannot be called inside test functions.

Naming convention for test functions is that it should start with `check`.
