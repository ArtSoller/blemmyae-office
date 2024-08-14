<?php

/**
 * Release instruction to update Topic and Type for Editorial entity.
 *
 * @author  Anastasia Lukyanova <stacylkv@gmail.com>
 * @license proprietary
 */

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\Entity\Term;
use Cra\BlemmyaeDeployment\A9SMigration;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

/**
 * @return void
 */
function port2285_migration_ri_001(): void
{
    (new RI())->setStatus('port2285_migration_ri_003', true);
    (new RI())->setStatus('port2285_migration_ri_004', true);
}

/**
 * Update MSSP Alert Editorial entity.
 *
 * @throws Exception
 */
function port2285_migration_ri_003(): void
{
    port2285_update_posts(
        A9SMigration::ALERT_DB_NAME,
        BlemmyaeApplications::MSSP,
    );
}

/**
 * Update MSSP Alert Editorial entity.
 *
 * @throws Exception
 */
function port2285_migration_ri_004(): void
{
    port2285_update_posts(
        A9SMigration::CE2E_DB_NAME,
        BlemmyaeApplications::CE2E,
    );
}

/**
 * Update Posts entity.
 *
 * @param string $dbName
 * @param string $app
 * @throws Exception
 */
function port2285_update_posts(
    string $dbName,
    string $app,
): void {
    // Fix memory leak.
    wp_suspend_cache_addition(true);

    global $wpdb;

    $utils = new Utils();

    $utils->switchDB($dbName);
    $postIds = array_column($wpdb->get_results(
        "SELECT ID FROM `wp_posts` WHERE post_type='post' AND post_status!='auto-draft'"
    ), 'ID');

    // Get all post's categories.
    $postsCategories = A9SMigration::getPostsCategories($postIds);

    $utils->switchDBToDefault();

    $itemsCount = count($postIds);
    $count = 1;

    $appTerm = Term::getAppTermBy('slug', $app);
    if (!$appTerm) {
        Logger::log('Missing app term', 'error');
        return;
    }

    $editorialTypeTerm = get_term_by('name', 'Post', 'editorial_type');

    foreach ($postIds as $id) {
        $msgPrefix = '[' . $count . '/' . $itemsCount . ']';
        $newId = A9SMigration::getMapping('post', $app, $id);
        $status = !empty($newId) ? 'updating' : 'creating';
        Logger::log("$msgPrefix: $id $status...", 'status');

        try {
            $options = [
                'newId' => $newId,
                'categories' => $postsCategories[$id],
                'app' => $appTerm,
                'type' => $editorialTypeTerm,
            ];
            $editorial = A9SMigration::upsertEditorial($options);

            Logger::log("$msgPrefix: $id ~> {$editorial->ID}", 'success');
            unset($editorial);
        } catch (Exception $exception) {
            Logger::log(
                $msgPrefix . 'Failed to create new Editorial for post with ID ' . $id,
                'warning'
            );
            Logger::log($exception->getMessage(), 'warning');
        }

        $count++;
    }

    wp_suspend_cache_addition(false);
}
