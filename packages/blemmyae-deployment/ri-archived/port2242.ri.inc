<?php

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\BlemmyaeDeployment\SetupMappingsTable;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

function port2242_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port2242_blemmyae_deployment_ri_200', true);
    (new RI())->setStatus('port2242_blemmyae_deployment_ri_003', true);
    (new RI())->setStatus('port2242_blemmyae_deployment_ri_004', true);
}

/**
 * Results are already stored in port2242/ce2e_media.json and port2242/mssp_media.json.
 * Could be used for future migrations.
 *
 * @throws Exception
 */
function port2242_blemmyae_deployment_ri_200(): void
{
    global $wpdb;

    // $alertIds = $wpdb->get_results("SELECT ce2e_id FROM wp_ce2e_media_to_media", ARRAY_N);
    $alertIds = $wpdb->get_results("SELECT alert_id FROM wp_alert_media_to_media", ARRAY_N);
    $mediaToPostsArray = [];

    $size = sizeof((array)$alertIds);
    $count = 1;

    foreach ($alertIds as $alertId) {
        $msgPrefix = '[' . $count . '/' . $size . ']';
        $alertId = reset($alertId);
        $postIds = $wpdb->get_results(
            // phpcs:ignore
            "SELECT ID FROM wp_posts WHERE post_content REGEXP CONCAT('<!-- wp:image.*\"id\":', $alertId, '(\\\D|$).*-->') AND post_type = 'editorial' AND post_status = 'publish'",
            ARRAY_N
        );

        if (!empty($postIds)) {
            $mediaToPostsArray[$alertId] = $postIds;
        }

        Logger::log("$msgPrefix Old media ID: $alertId", 'status');
        $count++;
    }

    $mediaToPostsArray = array_map(static fn($item) => array_merge(...$item), $mediaToPostsArray);

    file_put_contents(
        dirname(__FILE__) . "/port2242/mssp_media.json",
        json_encode($mediaToPostsArray)
    );
}

function port2242_blemmyae_deployment_ri_003(): void
{
    port2242_update_media_ids(
        BlemmyaeApplications::MSSP,
        A9SMigration::ALERT_MEDIA_TO_MEDIA_IN_CONTENT_TABLE,
        A9SMigration::ALERT_ID_COLUMN,
        A9SMigration::BLEMMYAE_ID_COLUMN
    );
}

function port2242_blemmyae_deployment_ri_004(): void
{
    port2242_update_media_ids(
        BlemmyaeApplications::CE2E,
        A9SMigration::CE2E_MEDIA_TO_MEDIA_IN_CONTENT_TABLE,
        A9SMigration::CE2E_ID_COLUMN,
        A9SMigration::BLEMMYAE_ID_COLUMN
    );
}

/**
 * Migrate media ids in content.
 */
function port2242_update_media_ids($app, $tableName, $oldValueColumnName, $newValueColumnName): void
{
    $setupMappingsTable = new SetupMappingsTable();
    $setupMappingsTable->setupTable($tableName, $oldValueColumnName, $newValueColumnName);

    $oldMediaIds = file_get_contents(dirname(__FILE__) . "/port2242/{$app}_media.json");
    $oldMediaIds = json_decode($oldMediaIds);
    $size = sizeof((array)$oldMediaIds);
    $count = 1;

    foreach ($oldMediaIds as $oldMediaId => $newPostIds) {
        $dbEntry = A9SMigration::getMapping('media_to_media_in_content', $app, $oldMediaId);

        if (!$dbEntry) {
            $blemMediaId = A9SMigration::getMapping('media', $app, $oldMediaId);
            $blemMediaId = is_array($blemMediaId) ? array_shift($blemMediaId) : $blemMediaId;

            if ($blemMediaId) {
                $msgPrefix = '[' . $count . '/' . $size . ']';

                foreach ($newPostIds as $newPostId) {
                    // Make sure not to mess apps.
                    if (BlemmyaeApplications::getAppIdByPostId($newPostId) === $app) {
                        $post = get_post($newPostId);
                        $pattern = "/<!-- wp:image(.*?)\"id\":$oldMediaId(.*?)-->/";
                        $post->post_content = preg_replace(
                            $pattern,
                            "<!-- wp:image$1\"id\":$blemMediaId$2-->",
                            $post->post_content
                        );
                        wp_update_post($post);
                        Logger::log("$msgPrefix Updated post ID: $newPostId", 'status');
                    }
                }
            }

            // Save a9s_id -> blem_id media mapping.
            $result = A9SMigration::saveIdMapping(
                $tableName,
                $oldValueColumnName,
                $oldMediaId,
                $blemMediaId,
            );

            if (!$result) {
                Logger::log(
                    "Could not insert mapping pair for media with ID: $oldMediaId",
                    'notice'
                );
            }
        } else {
            Logger::log("Skip already migrated media ID: $oldMediaId", 'notice');
        }

        $count++;
    }
}
