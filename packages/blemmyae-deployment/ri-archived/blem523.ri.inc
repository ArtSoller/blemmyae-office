<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-523
 */

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

function blem523_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('blem523_blemmyae_deployment_ri_002', true);
}

function blem523_blemmyae_deployment_ri_002(): void
{
    $businessContinuityTermObject = get_term_by('slug', 'business-contunuity', 'topic');
    if ($businessContinuityTermObject) {
        Logger::log(
            'Updating slug of Business Continuity term from business-contunuity to business-continuity',
            'info'
        );
        wp_update_term($businessContinuityTermObject->term_id, 'topic', [
            'slug' => 'business-continuity'
        ]);
        Logger::log('Slug updated successfully', 'info');
        Logger::log('Purging all graphcdn caches', 'info');
        /** @phpstan-ignore-next-line */
        graphcdn_purge_all();
        wp_cache_flush();
        return;
    }

    Logger::log(
        'Term with slug business-contunuity not found',
        'info'
    );
}
