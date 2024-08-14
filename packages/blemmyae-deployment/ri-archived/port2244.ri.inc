<?php

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Utils as AdministrationUtils;

/**
 * Set Stellate configs.
 */
function port2244_blemmyae_deployment_ri_003(): void
{
    $getPurgeToken = fn(string $env): string => get_option("graphcdn_purging_token_$env", '');

    [$serviceName, $purgeToken] = match (AdministrationUtils::environment()) {
        'live', 'prod', 'production' => ['scmagazine', $getPurgeToken('prod')],
        'preprod' => ['scmagazine-preprod', $getPurgeToken('preprod')],
        'qa1' => ['scmagazine-qa1', $getPurgeToken('qa1')],
        'qa2', 'test' => ['scmagazine-qa2', $getPurgeToken('qa2')],
        default => ['scmagazine-local', $getPurgeToken('local')],
    };

    update_option('stellate_service_name', $serviceName);
    update_option('stellate_purging_token', $purgeToken);
    update_option('stellate_soft_purge', 'on');

    wp_cache_flush();

    (new RI())->setStatus('port2244_blemmyae_deployment_ri_004', false);
}

/**
 * Re-run setting Stellate configs each time.
 */
function port2244_blemmyae_deployment_ri_004(): void
{
    (new RI())->setStatus('port2244_blemmyae_deployment_ri_003', false);
}
