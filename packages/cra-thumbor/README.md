# CRA Thumbor

Implements Thumbor service for intermediate image sizes.

This plugin is based on https://github.com/CodeKitchenBV/Thumbor

## Setup

1. Activate the plugin.
2. In `wp-config.php`, add the following lines:

```php
define('THUMBOR_SERVER', 'your-server-url');
define('THUMBOR_SECRET', 'your-secret');
```

If `THUMBOR_SECRET` is empty, generated URLs will be unsafe.

## Installation

### Git

In a terminal, browse to your `/wp-content/plugins/` directory and clone this repository:

~~~sh
git clone git@github.com:cra-repo/cra-thumbor.git
~~~

Then go to your Plugins screen and click __Activate__.

### Composer

~~~sh
compose require cra/cra-thumbor
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
- Copyright 2021 [CRA](https://www.cyberriskalliance.com)
