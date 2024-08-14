<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

use Scm\Tools\Logger;

/**
 * Remove SW redirects.
 *
 * @return void
 * @noinspection PhpUnused
 */
function port1540_blemmyae_deployment_ri_001(): void
{
    /** @phpstan-ignore-next-line */
    $group = Red_Group::get_all(['filterBy' => ['name' => 'securityweekly']])[0];
    $groupId = $group['id'];
    $count = 0;
    /** @phpstan-ignore-next-line */
    foreach (Red_Item::get_all() as $redirect) {
        if ($redirect->get_group_id() === $groupId) {
            $redirect->delete();
            $count++;
        }
    }
    Logger::log("Successfully deleted $count SW redirect(s).", 'success');
}
