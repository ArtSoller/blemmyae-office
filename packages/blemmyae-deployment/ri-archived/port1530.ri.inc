<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

use Scm\Tools\Logger;

/**
 * Set long-running RIs to be executed manually.
 *
 * @return void
 * @noinspection PhpUnused
 */
function port1530_blemmyae_deployment_ri_001(): void
{
    $count = 0;
    /** @phpstan-ignore-next-line */
    foreach (Red_Item::get_all() as $redirect) {
        if ($redirect->get_match_type() !== 'url') {
            continue;
        }
        $from = untrailingslashit($redirect->get_url());
        $to = untrailingslashit($redirect->get_action_data());
        if ($from === $to) {
            $redirect->delete();
            Logger::log("Deleted redirect to itself - $from", 'success');
            $count++;
        }
    }
    Logger::log("Successfully deleted $count redirect(s) to itself.", 'success');
}
