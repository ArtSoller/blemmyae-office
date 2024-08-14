# Blemmyae

Repository for headless WordPress project:
- https://www.scmagazine.com (scm)
- https://www.channele2e.com (ce2e)
- https://www.msspalert.com (mssp)
- https://www.cyberleadersunite.com (csc) - to be removed
- https://www.cybersecuritycollaboration.com (ciso) - to be removed
- https://www.cyberriskalliance.com (cra) - not supported yet
- https://www.cyberriskcollaborative.com (crc) - new

## Local Development

[ddev](https://ddev.readthedocs.io/en/stable/) is recommended for local development (repository even includes ddev config).

### Requirements

* Docker – https://ddev.readthedocs.io/en/stable/users/docker_installation/
* ddev itself: https://ddev.readthedocs.io/en/stable/#installation

### Setup - Outdated!

1. Copy `wp-config-ddev.php` into `wp-config.php`.
    1. Add row `define( 'WPMDB_LICENCE', '' );`, actual value you can get from non-local environment.
2. Update `composer config -g OPTION`, where option is:
    1. `github-oauth.github.com TOKEN` you personal developer token to access private cra repositories via composer
    2. `http-basic.composer.deliciousbrains.com USERNAME PASSWORD` username and password you can get from lastpass `WP Migrate DB Pro` notice or [current account settings on deliciousbrains.com](https://deliciousbrains.com/my-account/settings/).
    3. `http-basic.connect.advancedcustomfields.com LICENSE_KEY https://cms.scmagazine.com` license key you can get from [licenses page on acf site](https://www.advancedcustomfields.com/my-account/view-licenses/).
3.Install package dependencies from _project root_:
    - Composer: `composer update --prefer-dist`
    - Yarn: `npm install --global yarn`
    - NPM: `yarn install`
4. Go to the repository root and execute `ddev start` (actually works from any directory inside the repository)
5. Download database from [here](https://us-east-2.console.aws.amazon.com/s3/buckets/cra-portal-backend-backup?region=us-east-2&bucketType=general&tab=objects). Run `ddev import-db --src=/PATH/TO/DBDUMP.sql.gz`
6. That's it! Go to https://blemmyae.ddev.site, to see the site in action. See section below for the list of common ddev commands.
7. Install pre-commit `brew install pre-commit` and run `pre-commit install -f` to enable pre-commit hooks.
8. (optional) For Firefox to recognise SSL certificate run `mkcert -install` once.

### Common DDEV commands

* `ddev start`, `ddev stop`, `ddev restart` should be self-explanatory
* `ddev describe` to get information on how to reach various services set up by the DDEV (e.g. site URLs, Mailhog, PhpMyAdmin)
* `ddev import-db --file=/PATH/TO/DBDUMP.sql.gz`
* `ddev . COMMAND` to execute `COMMAND` inside the container. E.g. `ddev . ls`
* `ddev xdebug` and `ddev xdebug off` to enable and disable xdebug.
* `ddev auth ssh` to copy your SSH keys into the container
* `ddev poweroff` to quickly stop all DDEV projects. Can be executed from any directory on your system
* `ddev . composer codegen` to run code generator for content types

### Config

#### Base

Right now we have few instances in our backend: proxy, api and cms. Some plugins, like a JWT Auth, allow requests only
for main CMS url and not for API URL. For ex. JWT auth will work correctly with `cms.cyberriskalliance.com`, but not
with `api.cyberriskalliance.com`, if you forgot to add `WP_APIURL` into config.

```php
define( 'WP_HOME', 'https://' . $_SERVER['HTTP_HOST'] . '/' );
define( 'WP_SITEURL', 'https://' . $_SERVER['HTTP_HOST'] . '/' );
define( 'WP_APIURL', 'https://' . $_SERVER['HTTP_HOST'] . '/' );
define( 'WP_CMSURL', 'https://' . $_SERVER['HTTP_HOST'] . '/' );
define( 'WP_AUTO_UPDATE_CORE', false );

if ( ! defined( 'WP_CLI' ) ) {
	switch($_SERVER['REQUEST_URI']) {
    		case '/':
		    case '/sitemap':
		    case '/sitemap.xml':
    		case '/wp-admin':
        		$_SERVER['REQUEST_URI'] = '/wp-admin/';
        	break;
	}

	define('FORCE_SSL_ADMIN', true);
	if($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
		$_SERVER['HTTPS'] = 'on';
		$_SERVER['SERVER_PORT'] = 443;
	}
}
```

#### Disable local cache

```php
define('GRAPHQL_CDN_IGNORE_CONFIG_WARNING', true);
define('VARNISH_VERBOSE_CACHE_PURGE', false);
```

#### Redis

To enable local Redis cache run:
```shell
ddev . wp redis enable
```

The following configs are needed for the local Redis cache to work:

```php
# Redis
define('WP_REDIS_HOST', 'redis');
define('WP_REDIS_PASSWORD', ['redis', 'redis']);
```

#### Api

```php
// GraphQL
define('WP_HEADLESS_SECRET', 'your-top-secret-key');
define('FRONTEND_URI', 'https://blemmyae.ddev.site');
define('GRAPHQL_JWT_AUTH_SECRET_KEY', 'your-top-secret-key');
define('JWT_AUTH_SECRET_KEY', 'your-top-secret-key');
```

#### Apps

```php
# Apps
define('FRONTEND_URI_SCM', 'https://www.scmagazine.com');
define('FRONTEND_URI_CISO', 'https://www.cybersecuritycollaboration.com');
define('FRONTEND_URI_CSC', 'https://www.cyberleadersunite.com');
```

#### Ewww

```php
// EWWW.io
define('EWWW_IMAGE_OPTIMIZER_NOAUTO', true);
define('EWWW_IMAGE_OPTIMIZER_AUTO', false);
```

#### Offload

```php
// S3 Offload Media
define( 'AS3CF_SETTINGS', serialize( [
    'provider' => 'aws',
    'access-key-id' => '',
    'secret-access-key' => '',
] ) );

// S3 Offload SES
define( 'WPOSES_AWS_ACCESS_KEY_ID', '' );
define( 'WPOSES_AWS_SECRET_ACCESS_KEY', '' );
```

#### SQS

```php
# Amazon SQS settings
define('SQS_INTEGRATIONS_ACCESS_KEY', '');
define('SQS_INTEGRATIONS_SECRET_KEY', '');
define('SQS_INTEGRATIONS_ACCOUNT', '');
define('SQS_INTEGRATIONS_ENDPOINT', 'https://sqs.us-east-2.amazonaws.com');
define('SQS_INTEGRATIONS_REGION', 'us-east-2');
define('SQS_INTEGRATIONS_POLL_TIMEOUT', 2);

// Webhook consumer config
define('WEBHOOK_MESSAGE_INTERVAL', 60);
define('WEBHOOK_MESSAGE_BUFFER_SIZE', 10);

define('SQS_INTEGRATIONS_INCOMING_QUEUE', 'local-blemmyae-from-integrations.fifo');
define('SQS_INTEGRATIONS_PROCESSED_QUEUE', 'local-blemmyae-from-integrations-processed.fifo');
define('SQS_INTEGRATIONS_FAILED_QUEUE', 'local-blemmyae-from-integrations-failed.fifo');
```

#### Marketo

```php
// Marketo settings
define('MARKETO_REST_BASE_URL', 'https://188-UNZ-660.mktorest.com/rest');
define('MARKETO_IDENTITY_BASE_URL', 'https://188-UNZ-660.mktorest.com/identity');
define('MARKETO_CLIENT_ID', '');
define('MARKETO_CLIENT_SECRET', '');
define('MARKETO_ENVIRONMENT', 'dev');
```

#### Innodata

```php
// Innodata SFTP credentials
define('INNODATA_SFTP_HOST', 'blemmyaedev.sftp.wpengine.com');
define('INNODATA_SFTP_PORT', 2222);
define('INNODATA_SFTP_USERNAME', '');
define('INNODATA_SFTP_PASSWORD', '');
```

#### Skip JWT token validation process for REST API

These settings are based on JWT_SKIP_TOKEN_VALIDATION_SETTINGS variable, if you want to use this feature, then you need
to define variable in wp-config.php first. You need to specify url patterns and list of IPs, that will skip JWT token
validation. Below you can find example with whitelisted `127.0.0.1` for all HUM-related requests.

**_Notices_**

* URL pattern should be compatible with `preg_replace()` format.
* If these settings do not work, please check that patch file `/patches/port-1771-api-key-request.patch` is presented
  and correctly installed.
* Right now we use Cloudflare + nginx reverse proxy + docker, as result backend can see `127.0.0.1` in
  `$_SERVER['REMOTE_ADDR']` be sure that you have `HTTP_CF_CONNECTING_IP` in `$_SERVER`. To find original ip you need to
  be sure that `$_SERVER['HTTP_X_FORWARDED_FOR']` was created by nginx. NGINX settings for forwarding ip you can find below

```apacheconf
add_header X-Real-IP $remote_addr always;
add_header X-Forwarded-Proto $scheme always;
add_header X-Forwarded-For $proxy_add_x_forwarded_for always;
```

```php
define('JWT_SKIP_TOKEN_VALIDATION_SETTINGS', [
    [
    'uri_pattern' => '/^\/wp-json\/hum\/v1([\/\w\-]+)/',
    'ips' => ['127.0.0.1']
    ],  
]);
```

#### Etc

```php
define( 'CONVERTR_DOMAIN', 'cyberriskalliance.cvtr.io' );

define( 'SWOOGO_ENCODED_TOKEN', '' );

// ElasticPress credentials
define( 'EP_INDEX_PREFIX', '' );
define( 'ES_SHIELD', '' );
define( 'EP_HOST', '' );

// WP DB PRO
define( 'WPMDB_LICENCE', '' );

# 10up
define('TENUP_DISABLE_BRANDING', true);
define('TENUPSSO_DISABLE', true);
```

## Load Testing

See [load-tests/README.md](load-tests/README.md) for the documentation.

## Additional Technical Notes

Redirection plugin's monitor of slug changes only works for admin pages out-of-the-box.
This features has been made to work in these cases:

- Processing of Integrations Webhook messages (see `\Cra\WebhookConsumer\Mapper\AbstractPostWebhookMapper::construct()`).

## Releases

This section will contain handy command for [GitHub CLI]((https://github.com/cli/cli)), which developer can use for release

### Release plan

* Go to the board and check tickets for release
* Mark all required tickets with `Release:Ready` label
* Create PRs for preprod with `Release:Draft` label
* Preprod release
    * Merge tickets to preprod (you can use handy command below)
    * Run `prepare_release` workflow with corresponding params
    * Make a release to preprod
* Prod release
    * Merge tickets to master/main (all PRs with `Release:Ready` label)
    * Run `prepare_release` workflow with corresponding params
    * Make a GitHub release

### Create PR to preprod

This script will create preprod PRs from master PRs with `Release:Ready` label

```bash
gh pr list --label "Release:Ready" -R cra-repo/blemmyae --json title,headRepositoryOwner,headRefName,url --template '{{range .}}{{.headRepositoryOwner.login}}:{{.headRefName}} {{.headRepositoryOwner.login}} {{.headRefName}} {{.url}} {{"\n"}}{{end}}' | xargs -n 4 sh -c 'gh pr create -R cra-repo/blemmyae --head $0 --base cra-repo:preprod --assignee "$1" --title "(preprod) $2" --body "$2 <br /> $3" --label "Release:Draft" --web'
```

### Merge PRs

#### Merge PR into preprod

```bash
gh pr list --label "Release:Draft" -R cra-repo/blemmyae --json number,url,state --template '{{range .}}{{.number}}{{"\n"}}{{end}}' | xargs -n 1 sh -c 'gh pr merge $0 -R cra-repo/blemmyae -s --admin'
```

#### Merge PR into master/main

```bash
gh pr list --label "Release:Ready" -R cra-repo/blemmyae --json number,url,state --template '{{range .}}{{.number}}{{"\n"}}{{end}}' | xargs -n 1 sh -c 'gh pr merge $0 -R cra-repo/blemmyae -s --admin'
```

### Run version bump workflow

Every release require composer update and version bump, which can be done by following command

```bash
gh workflow run prepare_release.yml -R cra-repo/blemmyae --ref master -f env=prod -f tag=3.41.0 -f release_type=Release:Ready
```

**DO NOT FORGET** You need to update tag version, based on your release every time, 3.41.0 is just test value.

For preprod release you need to:

* Replace `Release:Ready` label to `Release:Draft` for preprod release
* Update tag
* Update env variable to `preprod`

### Make a release

#### Preprod release

Do not forget that preprod release should contain postfix with letter. If you want to make a release `3.41.0` then for preprod you need to add
some postfix, like `-a`. As result, release to preprod will be `3.41.0-a`

```bash
gh release create 3.41.0-a --target preprod --prerelease --generate-notes -R cra-repo/blemmyae
```

#### Prod release

```bash
gh release create 3.41.0 --target master --latest --generate-notes -R cra-repo/blemmyae
```

## Additional Docs

* [Qan Setup](docs/QAN.md)
* [Reverse Proxy](docs/REVERSE_PROXY.md)
