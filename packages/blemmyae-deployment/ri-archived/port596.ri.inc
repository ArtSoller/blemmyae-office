<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-596/Remove-business-continuity-duplicate-topic
 */

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

function port596_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port596_blemmyae_deployment_ri_002', true);
}

function port596_blemmyae_deployment_ri_002(): void
{
    $termName = 'business-contunuity';

    $termObject = get_term_by('slug', $termName, 'topic');
    /**
     * @var ?WP_Term
     */
    $termObject = (is_countable($termObject) ? $termObject[0] : $termObject) ?? null;
    if ($termObject) {
        Logger::log(
            "Removing $termName term",
            'info'
        );
        $termDeletionResult = wp_delete_term($termObject->term_id, 'topic');
        if ($termDeletionResult) {
            Logger::log("Term $termName removed successfully", 'info');
            Logger::log('Purging all graphcdn caches', 'info');
            wp_cache_flush();
            return;
        }

        Logger::log("Error, $termName term was not deleted", 'info');
        return;
    }

    Logger::log(
        "$termName term not found",
        'info'
    );
}
