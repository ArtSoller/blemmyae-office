<?php

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\Entity\Term;
use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\CtEditorial\EditorialCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

const PORT2013_EMAIL = 'jessica.c.davis@cyberriskalliance.com';
const PORT2013_TITLE = 'Jessica C. Davis';

function port2013_migration_ri_001(): void
{
    (new RI())->setStatus('port2013_migration_ri_002', true);
    (new RI())->setStatus('port2013_migration_ri_003', true);
}

/**
 * @throws Exception
 */
function port2013_migration_ri_002(): void
{
    port2013_upsert_a9s_posts_by_author(
        PORT2013_EMAIL,
        A9SMigration::ALERT_DB_NAME,
        A9SMigration::ALERT_AUTHOR_MAPPING_TABLE,
        A9SMigration::ALERT_ID_COLUMN,
        BlemmyaeApplications::MSSP,
        ['A9s', 'MSSP Alert']
    );
}

/**
 * @throws Exception
 */
function port2013_migration_ri_003(): void
{
    port2013_upsert_a9s_posts_by_author(
        PORT2013_EMAIL,
        A9SMigration::CE2E_DB_NAME,
        A9SMigration::CE2E_AUTHOR_MAPPING_TABLE,
        A9SMigration::CE2E_ID_COLUMN,
        BlemmyaeApplications::CE2E,
        ['A9s', 'ChannelE2E']
    );
}

/**
 * @param string $email
 * @param string $db
 * @param string $table
 * @param string $oldIdCol
 * @return array
 * @throws Exception
 */
function port2013_upsert_a9s_posts_by_author(
    string $email,
    string $db,
    string $table,
    string $oldIdCol,
    string $app,
    array $brands
): void {
    wp_suspend_cache_addition(true);

    global $wpdb;

    $utils = new Utils();
    $utils->switchDB($db);
    $userIds = array_column($wpdb->get_results(
        "SELECT id FROM `wp_users` WHERE user_email='$email';"
    ), 'id');

    $userId = array_shift($userIds);
    $postIds = array_column($wpdb->get_results(
    // phpcs:ignore
        "SELECT DISTINCT id FROM `wp_posts` WHERE post_type='post' AND post_author=$userId AND post_status!='auto-draft';"
    ), 'id');

    // Get all post's data.
    $postsData = A9SMigration::getPostsData($postIds);
    $postsMeta = A9SMigration::getPostsMetaData($postIds);
    $postsCategories = A9SMigration::getPostsCategories($postIds);

    $utils->switchDBToDefault();

    $itemsCount = count($postIds);
    $count = 1;

    // Update A9s User -> Blemmyae People mapping.
    $newId = port2013_get_author_id();
    $result = A9SMigration::saveIdMapping($table, $oldIdCol, $userId, $newId);

    if (!$result) {
        Logger::log(
            "Could not insert mapping pair for WP User with ID: $userId",
            'notice'
        );
    }

    // Migrate A9s Posts -> Blemmyae Editorial.
    // @todo: move duplicated code to the separate function.
    $appTerm = Term::getAppTermBy('slug', $app);
    if (is_null($appTerm)) {
        Logger::log('Missing app term', 'error');
        return;
    }
    $editorialTypeTerm = get_term_by('name', 'Post', 'editorial_type');
    $brandTerms = [];
    foreach ($brands as $brand) {
        $brandTerms [] = get_term_by('name', $brand, 'brand');
    }

    foreach ($postIds as $id) {
        $msgPrefix = '[' . $count . '/' . $itemsCount . ']';
        $newId = A9SMigration::getMapping('post', $app, $id);
        $status = !empty($newId) ? 'updating' : 'creating';
        Logger::log("$msgPrefix: $id $status...", 'status');

        try {
            $options = [
                'newId' => $newId,
                'post' => $postsData[$id],
                'meta' => $postsMeta[$id],
                'categories' => $postsCategories[$id],
                'app' => $appTerm,
                'type' => $editorialTypeTerm,
                'brand' => $brandTerms,
            ];
            $editorial = A9SMigration::upsertEditorial($options);

            // Create id mapping for new entity.
            $result = A9SMigration::saveIdMapping(
                $table,
                $oldIdCol,
                $id,
                $editorial->ID
            );

            if (!$result) {
                Logger::log("Could not insert mapping pair for WP User with ID: $id", 'notice');
            }

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

/**
 * @param string $app
 * @param string|int $oldId
 * @throws Exception
 */
function port2013_update_author(string $app, string|int $oldId): void
{
    $newId = A9SMigration::getMapping('post', $app, $oldId);

    if (empty($newId)) {
        Logger::log('Failed to get mapping for post with ID: ' . $oldId, 'notice');
    }

    $id = array_shift($newId);

    $author = port2013_get_author_id();

    update_field(
        EditorialCT::GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR,
        $author,
        $id
    );

    do_action('acf/save_post', $id);
}

/**
 * @throws Exception
 */
function port2013_get_author_id(): int
{
    $author = get_posts([
        'post_type' => 'people',
        'title' => PORT2013_TITLE,
        'fields' => 'ids'
    ]);

    if (count($author) > 1) {
        throw new \Exception("There are multiply pages with the same title.");
    }

    $authorId = array_shift($author);

    return $authorId;
}
