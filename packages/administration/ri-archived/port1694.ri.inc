<?php

/**
 * PORT-1694.
 *
 * https://cra.myjetbrains.com/youtrack/issue/PORT-1694.
 */

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\CerberusApps;
use Cra\BlemmyaeApplications\Entity\Term;
use Cra\CtLearning\SessionCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

/**
 * Disable ri run for port1694_administration_ri_002.
 */
function port1694_administration_ri_001(): void
{
    // Need to be run manually.
    (new RI())->setStatus('port1694_administration_ri_003', true);
    (new RI())->setStatus('port1694_administration_ri_004', true);
    (new RI())->setStatus('port1694_administration_ri_006', true);
}

/**
 * Add new applications: ce2e and alert.
 *
 * @return void
 */
function port1694_administration_ri_005(): void
{
    // Add ce2e and alert into application taxonomy.
    $newApplication = [
        'ce2e' => 'CE2E',
        'mssp' => 'Mssp',
        'nlt' => 'Newsletter (nlt)',
    ];

    foreach ($newApplication as $slug => $name) {
        if (empty(term_exists($slug, BlemmyaeApplications::TAXONOMY))) {
            wp_insert_term($name, BlemmyaeApplications::TAXONOMY, [
                'slug' => $slug,
                'description' => sprintf('Taxonomy term for %s CERB application', $slug)
            ]);
        }
    }
}

/**
 * Re-save all posts.
 *
 * @return void
 */
function port1694_administration_ri_006(): void
{
    // Fix memory leak.
    wp_suspend_cache_addition(true);

    $results = new WP_Query([
        'post_type' => BlemmyaeApplications::supportedPostTypes(),
        'post_status' => 'any',
        'fields' => 'ids',
        'posts_per_page' => -1,
        'order' => 'ASC'
    ]);

    $results_all = $results_count = count($results->posts);

    foreach ($results->posts as $postId) {
        $application = get_post_meta($postId, CerberusApps::APPLICATION_FIELD_META_KEY, true);
        $status = 'Skipped';
        if ($application instanceof \WP_Term) {
            update_post_meta(
                $postId,
                CerberusApps::APPLICATION_FIELD_META_KEY,
                $application->term_id
            );
            $status = 'Updated';
        }

        Logger::log(
            "[$results_all/$results_count] Updating App meta with postId #$postId - $status",
            'info'
        );

        $results_count--;
    }

    wp_suspend_cache_addition(false);

    wp_cache_flush();
}

/**
 * Get posts without application.
 *
 * @param array $fields
 *    Array with field keys.
 *
 * @return array
 *  Array with IDs of the posts.
 */
function port1694_get_posts_without_fields(array $fields): array
{
    $fieldsMetaArgs = array_map(fn($field) => [
        'key' => $field,
        'compare' => 'NOT EXISTS',
    ], $fields) ?? [];

    // Load all posts without application.
    $args = array(
        'posts_per_page' => -1,
        'fields' => 'ids',
        'post_type' => BlemmyaeApplications::supportedPostTypes(),
        'post_status' => 'any',
    );

    if (!empty($fields)) {
        $args ['meta_query'] = [
            'relation' => 'OR',
            ...$fieldsMetaArgs
        ];
    }

    $query = new WP_Query($args);

    return $query->get_posts() ?? [];
}

/**
 * Fill all application fields.
 */
function port1694_administration_ri_003(): void
{
    // Fix memory leak.
    wp_suspend_cache_addition(true);

    $posts = port1694_get_posts_without_fields([
        CerberusApps::APPLICATION_FIELD_META_KEY,
        CerberusApps::APPLICATIONS_FIELD_META_KEY,
        CerberusApps::APPLICATION_SLUG_FIELD_META_KEY,
    ]);

    $updatedPosts = [];
    $total = count($posts);
    $count = 1;

    foreach ($posts as $postId) {
        $postTitle = get_the_title($postId);
        $postType = get_post_type($postId);

        // Load application field, if app is not specified => SCM will be used.
        $app = BlemmyaeApplications::getAppIdByPostId($postId);

        // For session type we need to get app from the parent event (learning).
        if ($postType === 'session') {
            $eventId = get_field(SessionCT::GROUP_SESSION_ADVANCED__FIELD_EVENT, $postId, false);

            // Get App from parent event.
            $app = BlemmyaeApplications::getAppIdByPostId($eventId);
        }

        // Set application field.
        $appTerm = Term::getAppTermBy('slug', $app);

        // Update old application field.
        update_field(CerberusApps::APPLICATION_FIELD, $appTerm->term_id, $postId);

        // Update application slug.
        $applicationSlug = get_field(CerberusApps::APPLICATION_SLUG_FIELD, $postId);

        if (empty($applicationSlug)) {
            // Update field will trigger update value action => app slug will be generated automatically.
            update_field(CerberusApps::APPLICATION_SLUG_FIELD, '', $postId);
        }

        // Save updated posts into array for future check.
        if (empty($updatedPosts[$postType])) {
            $updatedPosts[$postType] = [];
        }

        $updatedPosts[$postType][] = $postId;

        // Log.
        $msg = sprintf(
            "Add application `%s` for %s #%s - %s ",
            $app,
            $postType,
            $postId,
            $postTitle,
        );

        Logger::log($msg, 'info');

        $msg = sprintf(
            "Processing: %s #%s - %s - [%s/%s]",
            $postType,
            $postId,
            $postTitle,
            $count++,
            $total
        );

        Logger::log($msg, 'info');
    }

    wp_suspend_cache_addition(false);

    // Write all updated posts into JSON.
    $updatedPostsJson = json_encode($updatedPosts);

    if (file_put_contents("port1694_log.json", $updatedPostsJson)) {
        Logger::log("Updated posts was added to JSON file successfully!", "success");
    } else {
        Logger::log("Oops! Error creating json file!", "warning");
    }
}

/**
 * Check that all CTs have application in their field.
 */
function port1694_administration_ri_004(): void
{
    $posts = port1694_get_posts_without_fields([CerberusApps::APPLICATION_SLUG_FIELD_META_KEY]);

    $total = count($posts);
    $count = 1;

    foreach ($posts as $postId) {
        // Update application slug.
        $applicationSlug = get_field(CerberusApps::APPLICATION_SLUG_FIELD, $postId);

        if (empty($applicationSlug)) {
            // Update field will trigger update value action => app slug will be generated automatically.
            update_field(CerberusApps::APPLICATION_SLUG_FIELD, '', $postId);
        }

        // Logs.
        $postTitle = get_the_title($postId);
        $slug = get_field(CerberusApps::APPLICATION_SLUG_FIELD, $postId);

        $msg = sprintf(
            "Add application slug for #%s - %s, slug - %s",
            $postId,
            $postTitle,
            $slug
        );

        Logger::log($msg, 'info');

        $msg = sprintf(
            "Processing: #%s - %s - [%s/%s]",
            $postId,
            $postTitle,
            $count++,
            $total
        );

        Logger::log($msg, 'info');
    }
}
