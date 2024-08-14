<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-630
 */

declare(strict_types=1);

use Cra\CtWhitepaper\WhitepaperCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

function blem630_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('blem630_blemmyae_deployment_ri_002', true);
}

/**
 * Return current featured image id and current pdf file attachment id, if exists
 *
 * @param int $postId
 *
 * @return int[]
 */
function blem630_find_current_attachments(int $postId): array
{
    $featuredImageId = get_field(WhitepaperCT::GROUP_WHITEPAPER_ADVANCED__FIELD_FEATURED_IMAGE, $postId)['ID'] ?? 0;
    $vendorId = get_field(WhitepaperCT::GROUP_WHITEPAPER_ADVANCED__FIELD_VENDOR, $postId)[0]['file']['ID'] ?? 0;
    return [$featuredImageId, $vendorId];
}

/**
 * Removes excessive media items attached to whitepapers
 * from s3 bucket and from media library
 *
 * @return void
 */
function blem630_blemmyae_deployment_ri_002(): void
{
    $wpQuery = new WP_Query([
        'post_type' => 'whitepaper',
        'post_status' => ['publish', 'draft'],
        'posts_per_page' => -1,
        'fields' => 'ids',
    ]);

    $whitepapers = $wpQuery->get_posts();

    /**
     * ['whitepaperId' => [attachment1, attachment2, ...]]
     */
    $excessiveWhitepaperAttachments = [];

    foreach ($whitepapers as $whitepaperId) {
        $currentAttachments = blem630_find_current_attachments($whitepaperId);
        $childrenIds = array_map(
            fn($mediaItem) => $mediaItem->ID,
            get_children(['post_parent' => $whitepaperId, 'post_type' => 'attachment'])
        );
        $excessiveChildrenIds = array_filter(
            $childrenIds,
            fn($mediaId) => !in_array($mediaId, $currentAttachments, true)
        );

        if (count($excessiveChildrenIds)) {
            $excessiveWhitepaperAttachments[$whitepaperId] = $excessiveChildrenIds;
        }
    }

    foreach ($excessiveWhitepaperAttachments as $whitepaperId => $attachments) {
        $i = 0;
        $totalItemsInGroupCount = count($attachments);
        Logger::log("Processing media attached to $whitepaperId", 'info');

        foreach ($attachments as $attachmentId) {
            $i += 1;

            Logger::log('-----------', 'info');
            Logger::log("[$i / $totalItemsInGroupCount]", 'info');

            $deletionFromMediaLibraryResult = wp_delete_attachment($attachmentId, true);
            if (!$deletionFromMediaLibraryResult) {
                // phpcs:ignore
                Logger::log("Error deleting attachment {$attachmentId} of whitepaper {$whitepaperId} from media library", 'warning');
                continue;
            }
            Logger::log("Attachment {$attachmentId} of whitepaper {$whitepaperId} deleted from media library", 'info');
        }
        Logger::log("All media items attached to $whitepaperId were processed", 'info');
        Logger::log('-------------------------------------------', 'info');
    }

    wp_cache_flush();
}
