<?php

/** @noinspection PhpUnused */

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-3283
 */

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\CerberusApps;
use Cra\BlemmyaeApplications\Entity\Term;
use Cra\CtLearning\LearningCT;
use Cra\CtLearning\SessionCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\WpCore;

function port3283_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port3283_blemmyae_deployment_ri_002', true);
}

/**
 * Migrate learnings and sessions from CISO & CSC to CRC.
 *
 * @return void
 * @throws Exception
 */
function port3283_blemmyae_deployment_ri_002(): void
{
    $query = new WP_Query([
        'nopaging' => true,
        'post_type' => [LearningCT::POST_TYPE, SessionCT::POST_TYPE],
        'post_status' => ['publish', 'future'],
        'tax_query' => [
            [
                'field' => 'slug',
                'taxonomy' => BlemmyaeApplications::TAXONOMY,
                'terms' => [BlemmyaeApplications::CISO, BlemmyaeApplications::CSC],
                'operator' => 'IN',
            ]
        ]
    ]);

    $processed = 0;
    $total = $query->post_count;
    while ($query->have_posts()) {
        try {
            $post = $query->next_post();
            WpCore::setPostTerms(
                BlemmyaeApplications::TAXONOMY,
                Term::getAppTermIdByAppSlug(BlemmyaeApplications::CRC),
                $post->ID,
                CerberusApps::APPLICATION_FIELD,
            );
            $processed += 1;
            Logger::log("[$processed/$total] Processed '$post->post_title' (ID: $post->ID)", 'info');
        } catch (Exception $exception) {
            Logger::log($exception->getMessage(), 'error');
        }
    }

    wp_cache_flush();
    Logger::log('Migrated learnings and sessions from CISO & CSC to CRC.', 'success');
}
