<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-1238
 */

declare(strict_types=1);

/**
 * Update slug for persons.
 *
 * @return void
 * @noinspection PhpUnused
 */
function port1238_blemmyae_deployment_ri_001(): void
{
    update_option('people_structure', '/contributor/%postname%');
}
