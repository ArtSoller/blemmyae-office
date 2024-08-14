# Webhook Consumer

Webhook Consumer is a WP plugin for handling incoming webhooks from CRA integrations in Blemmyae.

## Installation

### Git

In a terminal, browse to your `/wp-content/plugins/` directory and clone this repository:

~~~sh
git clone git@github.com:cra-repo/blemmyae-webhook-consumer.git
~~~

Then go to your Plugins screen and click __Activate__.

### Composer

Add the following config to `repositories` section of `composer.json` file:

~~~json
{
  "type": "vcs",
  "url": "https://github.com/cra-repo/blemmyae-webhook-consumer"
}
~~~

and then run:

~~~sh
compose require cra/blemmyae-webhook-consumer
~~~

## Configuration

The following environment variables are required for processing webhook messages to work:
- `SQS_INTEGRATIONS_ACCESS_KEY` -- IAM access key with sufficient privileges to manipulate provided queues
- `SQS_INTEGRATIONS_SECRET_KEY` -- IAM secret key with sufficient privileges to manipulate provided queues
- `SQS_INTEGRATIONS_ACCOUNT` -- account number
- `SQS_INTEGRATIONS_ENDPOINT` -- e.g. `https://sqs.us-west-1.amazonaws.com`
- `SQS_INTEGRATIONS_REGION` -- e.g. `us-west-1`
- `SQS_INTEGRATIONS_INCOMING_QUEUE` -- incoming messages queue name
- `SQS_INTEGRATIONS_PROCESSED_QUEUE` -- processed messages queue name
- `SQS_INTEGRATIONS_FAILED_QUEUE` -- failed messages queue name
- (optional) `SQS_INTEGRATIONS_POLL_TIMEOUT` -- time in seconds to poll for messages. Default: `2`.

Additional optional configuration:
- `WEBHOOK_MESSAGE_BUFFER_SIZE` -- number of messages to process per cron job. Default: `5`.
- `WEBHOOK_MESSAGE_INTERVAL` -- how often to execute cron job in seconds. Default: `180` (3 minutes).

## Usage

Webhook cron job is called `webhook_message_cron` and by default it runs every 3 minutes.
It processes 5 messages (by default) per cron job run.
See Configuration section to learn how to change these default values.

To manually execute the cron job from console run the following command (via WP CLI tool):

~~~sh
wp cron event run webhook_message_cron
~~~

## Change Log

Please see [CHANGELOG.md](CHANGELOG.md).

## Contributing

See the [contributing document](.github/CONTRIBUTING.md).

## Licensing

The code in this project is licensed under proprietary.

## Credits

- Built by [Gary Jones](https://twitter.com/GaryJ)  
- Forked by [Alexander Kucherov](https://github.com/Zinkutal)
- Created from template by [Konstantin Gusev](https://github.com/guvkon)
- Copyright 2021 [CRA](https://www.cyberriskalliance.com)
