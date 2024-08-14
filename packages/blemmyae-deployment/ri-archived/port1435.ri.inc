<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

use Scm\Tools\Logger;
use Scm\Tools\Utils;

/**
 * Create redirection groups and SW redirects.
 *
 * @return void
 * @throws Exception
 */
function port1435_blemmyae_deployment_ri_001(): void
{
    // Create groups.
    $domains = [
        'sw' => 'securityweekly.com',
        'ciso' => 'cybersecuritycollaboration.com',
        'csc' => 'cyberleadersunite.com',
    ];
    $groups = [];
    foreach ($domains as $key => $domain) {
        /** @phpstan-ignore-next-line */
        $group = Red_Group::create($domain, 1, true);
        /** @phpstan-ignore-next-line */
        if (!($group instanceof Red_Group)) {
            throw new Exception("An error creating redirection group for $domain");
        }
        $groups[$key] = $group;
    }

    // Create SW redirects.
    $count = 0;
    foreach (Utils::importCsv(__DIR__ . '/port1435/sw_redirects.csv', ',') as $redirect) {
        /** @phpstan-ignore-next-line */
        $result = Red_Item::create([
            'url' => $redirect[0],
            'action_data' => ['url' => $redirect[1]],
            'group_id' => $groups['sw']->get_id(),
            'match_type' => 'url',
            'action_type' => 'url',
            'action_code' => 301,
        ]);
        if ($result instanceof WP_Error) {
            throw new Exception($result->get_error_message());
        }
        $count++;
        Logger::log('.', 'info');
    }

    Logger::log("Created $count redirects for securityweekly.com", 'success');
}
