<?php

declare(strict_types=1);

use Scm\Tools\Utils;

/**
 * Create Blemmyae admin users.
 */
function a9s135_migration_ri_103(): void
{
    $admins = [
        'jessica.c.davis@cyberriskalliance.com',
        'james.masters@cyberriskalliance.com',
        'slflorentine@gmail.com',
    ];

    $role = 'administrator';

    a9s135_create_users($admins, $role);
}

/**
 * Create Blemmyae editor users.
 */
function a9s135_migration_ri_104(): void
{
    $editors = [
        'tyler.trumbull@gmail.com',
        'kobialka.daniel@gmail.com',
        'dhkview@gmail.com',
    ];

    $role = 'editor';

    a9s135_create_users($editors, $role);
}

/**
 * Create Blemmyae users.
 */
function a9s135_create_users(array $users, string $role): void
{
    $args = $role . ' ' . implode(' ', $users);

    if (Utils::environment() === 'dev') {
        $script = ABSPATH . 'scripts/create-users.sh ' . $args;
    } else {
        $script = '/opt/copilot/scripts/create-users.sh ' . $args;
    }

    system($script);
}
