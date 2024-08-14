# Blemmyae Deployment

Short summary about the plugin.

## Installation

### Upload

1. Download the latest tagged archive (choose the "zip" option).
* Go to the __Plugins__ → __Add New__ screen and click the __Upload__ tab.
* Upload the zipped archive directly.
* Go to the Plugins screen and click __Activate__.

### Manual

1. Download the latest tagged archive (choose the "zip" option).
* Unzip the archive.
* Copy the folder to your `/wp-content/plugins/` directory.
* Go to the Plugins screen and click __Activate__.

Check out the Codex for more information about [installing plugins manually](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

### Git

In a terminal, browse to your `/wp-content/plugins/` directory and clone this repository:

~~~sh
git clone git@github.com:cra-repo/blemmyae-deployment.git
~~~

Then go to your Plugins screen and click __Activate__.

### Composer

~~~sh
compose require cra/blemmyae-deployment
~~~

## Development

 - Search and replace with match case:
	- Blemmyae Deployment
	- BLEMMYAE_DEPLOYMENT
	- blemmyae_deployment
	- blemmyaedeployment
	- BlemmyaeDeployment
	- blemmyaeDeployment
	- blemmyae-deployment
	- Foo
	- FooTest
	 
 - Re-name files: 
    - blemmyae-deployment.php
	- src/Foo.php
	- tests/Unit/FooTest.php
	- tests/Integration/FooTest.php

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
