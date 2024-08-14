<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-1558
 */

declare(strict_types=1);

use Cra\CtWhitepaper\Whitepaper as CtWhitepaper;
use Cra\CtWhitepaper\WhitepaperCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Entity\Media;
use Scm\Tools\Logger;
use Scm\Tools\WpCore;

function port1558_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port1558_blemmyae_deployment_ri_002', true);
}

/**
 * Upsert media item (copy-pasted private method of blemmyae-webhook-consumer)
 *
 * @param array $mediaIds
 * @param array $mediaUrls
 * @param string $description
 *
 * @return array
 */
function port1558_upsertMediaItems(
    array $mediaIds,
    array $mediaUrls,
    string $description = ''
): array {
    $existingMedia = [];
    foreach ($mediaIds as $mediaId) {
        $source = get_field(Media::FIELD__ORIGINAL_SOURCE, $mediaId) ?: '';
        if ($source) {
            $existingMedia[$source] = $mediaId;
        }
    }

    $outputMediaIds = [];
    foreach ($mediaUrls as $mediaUrl) {
        try {
            $mediaId = $existingMedia[$mediaUrl] ?? WpCore::mediaHandleSideload($mediaUrl, $description);
            update_field(Media::FIELD__ORIGINAL_SOURCE, $mediaUrl, $mediaId);
            $outputMediaIds[] = $mediaId;
        } catch (Exception $exception) {
            Logger::log($exception->getMessage(), 'warning');
        }
    }

    return $outputMediaIds;
}

/**
 * Converts draft convertr whitepapers that should be published internal
 * to published internal
 *
 * @return void
 */
function port1558_blemmyae_deployment_ri_002(): void
{
    $draftWhitepapersWpQuery = new WP_Query([
        'post_type' => 'whitepaper',
        'post_status' => 'draft',
        'posts_per_page' => -1,
    ]);

    $draftWhitepapers = $draftWhitepapersWpQuery->get_posts();

    foreach ($draftWhitepapers as $draftWhitepaper) {
        $whitepaperId = $draftWhitepaper->ID;
        Logger::log("Converting whitepaper $whitepaperId to internal", 'info');
        // phpcs:ignore
        /** @phpstan-ignore-next-line */
        $fileAttachment = get_field(CtWhitepaper::FIELD_WHITEPAPER_FILE_ATTACHMENT, $whitepaperId);
        $pdfDownloadLinkFieldValue = get_field(
            WhitepaperCT::GROUP_WHITEPAPER_ADVANCED__FIELD_VENDOR,
            $whitepaperId
        )[0]['download_link'] ?? null;
        if (!$pdfDownloadLinkFieldValue) {
            Logger::log(
                "Could not access pdf download link of $whitepaperId whitepaper, skipping it",
                'info'
            );
            continue;
        };
        $pdfDownloadLink = $pdfDownloadLinkFieldValue . '.pdf';
        $fileAttachmentId = $fileAttachment ? (int)$fileAttachment['ID'] : null;
        $newAttachmentIds = port1558_upsertMediaItems(
            $fileAttachmentId ? [$fileAttachmentId] : [],
            [$pdfDownloadLink],
        );
        $newAttachmentId = $newAttachmentIds[0] ?? null;
        if (!$newAttachmentId) {
            Logger::log(
                "Attachment could not be added to $whitepaperId whitepaper, skipping it",
                'info'
            );
            continue;
        }
        if ($newAttachmentId !== $fileAttachmentId) {
            update_field(
                WhitepaperCT::GROUP_WHITEPAPER_ADVANCED__FIELD_VENDOR,
                [
                    [
                        'acf_fc_layout' =>
                            WhitepaperCT::VENDOR__INTERNAL_WHITEPAPER,
                        'file' => $newAttachmentId,
                    ],
                ],
                $whitepaperId
            );
        }
        wp_update_post(
            [
                'ID' => $whitepaperId,
                'post_status' => 'publish',
            ]
        );
        Logger::log("Converted whitepaper $whitepaperId to internal", 'info');
        Logger::log("---------------------------------", 'info');
    }

    wp_cache_flush();
}
