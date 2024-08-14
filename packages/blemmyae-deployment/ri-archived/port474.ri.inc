<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-474
 */

declare(strict_types=1);

use Cra\CtLearning\LearningCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

function port474_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port474_blemmyae_deployment_ri_002', true);
}

function port474_blemmyae_deployment_ri_002(): void
{
    $wpQueryArgs = [
        'post_type' => 'learning',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ];

    $allLearnings = (new WP_Query($wpQueryArgs))->get_posts();

    $vendorSlugOverrides = [
        'member_portal' => 'csc-member-portal',
    ];

    $learningsWithVendors = array_map(
        static function (int $learningId) use ($vendorSlugOverrides): array {
            $vendorAcfValue = get_field('vendor', $learningId)[0]['acf_fc_layout'] ?? null;

            return [
                'learningId' => $learningId,
                'learningVendor' => $vendorSlugOverrides[$vendorAcfValue] ?? $vendorAcfValue,
            ];
        },
        array_filter($allLearnings, fn($learningId) => !get_field('vendor_type', $learningId))
    );

    foreach ($learningsWithVendors as $learning) {
        $term = get_term_by('slug', $learning['learningVendor'], 'learning_vendor_type');
        $learningId = $learning['learningId'];
        if ($term) {
            Logger::log(
                "Updating vendor field of $learningId to {$learning['learningVendor']}",
                'info'
            );
            update_field(LearningCT::GROUP_LEARNING_ADVANCED__FIELD_VENDOR_TYPE, $term->term_id, $learningId);
            continue;
        }
        Logger::log("Vendor of $learningId could not be identified", 'info');
    }

    /**
     * Trigger hooks to link vendor terms to learnings that
     * do not have it linked
     */
    foreach ($allLearnings as $learningId) {
        do_action('acf/save_post', $learningId);
    }

    wp_cache_flush();
}
