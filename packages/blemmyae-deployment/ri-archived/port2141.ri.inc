<?php

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\BlemmyaeDeployment\SetupMappingsTable;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

function port2141_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port2141_blemmyae_deployment_ri_002', true);
    (new RI())->setStatus('port2141_blemmyae_deployment_ri_003', true);
}

function port2141_blemmyae_deployment_ri_002(): void
{
    port2141_update_media_ids(
        BlemmyaeApplications::CE2E,
        A9SMigration::CE2E_MEDIA_IN_CONTENT_TABLE,
        A9SMigration::CE2E_ID_COLUMN,
        A9SMigration::BLEMMYAE_ID_COLUMN
    );
}

function port2141_blemmyae_deployment_ri_003(): void
{
    port2141_update_media_ids(
        BlemmyaeApplications::MSSP,
        A9SMigration::ALERT_MEDIA_IN_CONTENT_TABLE,
        A9SMigration::ALERT_ID_COLUMN,
        A9SMigration::BLEMMYAE_ID_COLUMN
    );
}

function port2141_update_media_ids($app, $tableName, $oldValueColumnName, $newValueColumnName): void
{
    $setupMappingsTable = new SetupMappingsTable();
    $setupMappingsTable->setupTable($tableName, $oldValueColumnName, $newValueColumnName);

    $oldMediaIds = file_get_contents(dirname(__FILE__) . "/port2141/{$app}_media.json");
    $oldMediaIds = json_decode($oldMediaIds);
    $size = sizeof((array)$oldMediaIds);
    $count = 1;

    foreach ($oldMediaIds as $oldMediaId => $oldPostsIds) {
        $dbEntry = A9SMigration::getMapping('media_in_content', $app, $oldMediaId);

        if (!$dbEntry) {
            $blemMediaId = A9SMigration::getMapping('media', $app, $oldMediaId);
            $blemMediaId = is_array($blemMediaId) ? array_shift($blemMediaId) : $blemMediaId;

            if ($blemMediaId) {
                $msgPrefix = '[' . $count . '/' . $size . ']';

                $blemPostsToFix = array_map(function ($n) use ($app) {
                    $id = A9SMigration::getMapping('post', $app, $n);
                    return is_array($id) ? array_shift($id) : $id;
                }, $oldPostsIds);

                $blemPostsToFix = array_filter($blemPostsToFix);
                $query = new WP_Query(['post__in' => $blemPostsToFix, 'post_type' => 'editorial']);

                while ($query->have_posts()) {
                    $post = $query->next_post();
                    $pattern = "/<!-- wp:image(.*?)\"id\":$oldMediaId(.*?)-->/";
                    $post->post_content = preg_replace(
                        $pattern,
                        "<!-- wp:image$1\"id\":$blemMediaId$2-->",
                        $post->post_content
                    );
                    wp_update_post($post);

                    Logger::log("$msgPrefix Updated post ID: $post->ID", 'status');
                }
                $count++;
            }

            // Save a9s_id -> blem_id media mapping.
            $result = A9SMigration::saveIdMapping(
                $tableName,
                $oldValueColumnName,
                $oldMediaId,
                1 // just flag is enough
            );

            if (!$result) {
                Logger::log("Could not insert mapping pair for media with ID: $oldMediaId", 'notice');
            }
        }
    }
}
