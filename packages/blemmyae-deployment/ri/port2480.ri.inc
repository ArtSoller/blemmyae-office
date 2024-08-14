<?php

/**
 * https://cra.myjetbrains.com/youtrack/issue/PORT-2480
 */

declare(strict_types=1);

use Cra\CtCompanyProfile\CompanyProfileCT;
use Cra\CtPeople\PeopleCT;
use Scm\Tools\Logger;
use ReleaseInstructions\Command\CoreCommand as RI;

function port2480_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port2480_blemmyae_deployment_ri_002', true);
}

/**
 * Removes duplicated assets.
 *
 * @return void
 */
function port2480_blemmyae_deployment_ri_002(): void
{
    $duplicatedSwoogoUrls = port2480_get_duplicated_swoogo_urls();

    if (empty($duplicatedSwoogoUrls)) {
        return;
    }

    // Flatten Swoogo URLs.
    $swoogoUrlsArray = array_map(fn($url) => $url->meta_value, $duplicatedSwoogoUrls);

    // Fetch Swoogo post IDs.
    $swoogoPostsResult = port2480_get_swoogo_post_ids($swoogoUrlsArray);

    // Process each duplicated attachment post.
    foreach ($swoogoPostsResult as $swoogoPost) {
        port2480_process_attachment($swoogoPost);
    }
}

/**
 * Retrieves duplicated Swoogo image URLs from wp_postmeta.
 *
 * @return array<stdClass> Array of stdClass objects representing meta_value entries.
 */
function port2480_get_duplicated_swoogo_urls(): array
{
    global $wpdb;
    $duplicatesQuery = "
        SELECT meta_value
        FROM {$wpdb->postmeta}
        WHERE meta_key = 'original_source'
          AND meta_value LIKE '%assets.swoogo.com/uploads%'
        GROUP BY meta_value
        HAVING COUNT(*) > 1
    ";

    return $wpdb->get_results($duplicatesQuery);
}

/**
 * Retrieves Swoogo post IDs based on provided URLs.
 *
 * @param array<string> $swoogoUrlsArray Array of Swoogo URLs.
 *
 * @return array<stdClass> Array of stdClass objects representing post IDs.
 */
function port2480_get_swoogo_post_ids(array $swoogoUrlsArray): array
{
    global $wpdb;
    $swoogoUrlsString = implode("','", $swoogoUrlsArray);

    $swoogoPostsQuery = "
            SELECT post_id
            FROM {$wpdb->postmeta}
            WHERE meta_key = 'original_source'
              AND meta_value IN ('" . $swoogoUrlsString . "')
        ";

    return $wpdb->get_results($swoogoPostsQuery);
}

/**
 * Deletes an attachment from the database.
 *
 * @param int $postId The post ID of the attachment to delete.
 *
 * @return void
 */
function port2480_delete_attachment(int $postId): void
{
    $removalResult = wp_delete_attachment($postId, true);

    if ($removalResult instanceof WP_Post) {
        Logger::log("{$removalResult->ID} {$removalResult->post_title} was successfully deleted",
            "success");
    } else {
        Logger::log("Error while deleting {$postId}", "error");
    }
}

/**
 * Processes a single duplicated attachment based on Swoogo post ID.
 *
 * @param stdClass $swoogoPost stdClass object representing the post ID.
 * @return void
 */
function port2480_process_attachment(stdClass $swoogoPost): void
{
    $attachment = get_post($swoogoPost->post_id);
    if (!($attachment instanceof WP_Post)) {
        return;
    }

    $attachmentParent = get_post($attachment->post_parent);
    if (!($attachmentParent instanceof WP_Post)) {
        Logger::log(
            "{$attachment->ID} {$attachment->post_title} No parent specified for the attachment -> DELETE",
            "warning"
        );

        port2480_delete_attachment($attachment->ID);
        return;
    }

    $metaKey = match ($attachmentParent->post_type) {
        'people' => PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_HEADSHOT,
        'company_profile' => CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_LOGO,
        default => null,
    };
    if (!$metaKey) {
        Logger::log(
            "{$attachmentParent->ID} {$attachmentParent->post_title} {$attachmentParent->post_type} isn't supported",
            'warning'
        );
        return;
    }

    $parentActiveAttachmentId = (int)get_field($metaKey, $attachmentParent->ID, false);

    if ($parentActiveAttachmentId !== $attachment->ID) {
        port2480_delete_attachment($attachment->ID);
    }
}
