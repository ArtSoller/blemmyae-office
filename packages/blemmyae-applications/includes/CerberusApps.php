<?php

declare(strict_types=1);

// @todo Add import application terms from csv file, like we have for topics.

namespace Cra\BlemmyaeApplications;

use Cra\BlemmyaeApplications\Entity\Permalink;
use Scm\Tools\Exception;
use Scm\Tools\Redirects;
use WP_Query;
use WPGraphQL\Model\Post;

/**
 * Cerberus Applications handy function.
 */
class CerberusApps
{
    /**
     * @deprecated Use BlemmyaeApplications::TAXONOMY instead.
     */
    public const TAXONOMY = 'applications';

    public const APPLICATION_FIELD = 'field_634fcdce7572f';

    public const APPLICATION_FIELD_META_KEY = 'application';

    public const APPLICATIONS_FIELD = 'field_646c59e467bbf';

    public const APPLICATIONS_FIELD_META_KEY = 'applications';

    public const APPLICATION_SLUG_FIELD = 'field_6475be6ffc7a9';

    public const APPLICATION_SLUG_FIELD_META_KEY = 'slug';

    public const APPLICATION_FIELD_GROUP_ID = 459053;

    /**
     * @deprecated use BlemmyaeApplications::SCM instead
     */
    public const SCM = BlemmyaeApplications::SCM;

    /**
     * @deprecated use BlemmyaeApplications::CISO instead
     */
    public const CISO = BlemmyaeApplications::CISO;

    /**
     * @deprecated use BlemmyaeApplications::CSC instead
     */
    public const CSC = BlemmyaeApplications::CSC;

    /**
     * @deprecated use BlemmyaeApplications::NLT instead
     */
    public const NLT = BlemmyaeApplications::NLT;

    /**
     * @deprecated use BlemmyaeApplications::MSSP instead
     */
    public const MSSP = BlemmyaeApplications::MSSP;

    /**
     * @deprecated use BlemmyaeApplications::CE2E instead
     */
    public const CE2E = BlemmyaeApplications::CE2E;

    /**
     * @deprecated use BlemmyaeApplications::CRC instead
     */
    public const CRC = BlemmyaeApplications::CRC;

    /**
     * Initialize hooks.
     */
    public function __construct()
    {
        // Clone application field.
        add_filter('acf/update_value/key=' . self::APPLICATION_FIELD, [$this, 'updateApplicationField'], 10, 4);
        add_filter('acf/update_value/key=' . self::APPLICATIONS_FIELD, [$this, 'updateApplicationsField'], 10, 4);
        add_filter('acf/prepare_field/key=' . self::APPLICATIONS_FIELD, [$this, 'makeFieldReadonly']);

        // Work with application slug and permalink.
        $this->applicationSlugHooks();
    }

    /**
     * Work with applicaitons permalinks.
     *
     * @return void
     */
    private function applicationSlugHooks(): void
    {
        // Application slug field.
        add_filter('acf/update_value/key=' . self::APPLICATION_SLUG_FIELD, [$this, 'generatePermalink'], 20, 2);
        add_filter('acf/update_value/key=' . self::APPLICATION_SLUG_FIELD, [$this, 'uniqApplicationSlug'], 30, 2);
        add_filter('acf/update_value/key=' . self::APPLICATION_SLUG_FIELD, [$this, 'checkRedirects'], 35, 3);
        add_filter('acf/update_value/key=' . self::APPLICATION_FIELD, [$this, 'createCrossAppRedirect'], 40, 2);

        // Add permalink after the field.
        add_action(
            'acf/render_field/key=' . self::APPLICATION_SLUG_FIELD,
            [$this, 'updateApplicationSlugFieldRender'],
            10,
            0,
        );

        // Override default slug by application slug.
        add_filter('graphql_resolve_field', [$this, 'updateSlugBasedOnApplication'], 10, 9);

        // Update permalink render.
        add_filter('save_post', [$this, 'generatePermalinkOnSavePost'], 20, 1);
        add_filter('post_type_link', [$this, 'replacePermalink'], 20, 4);

        // Hide default permalink and render our custom permalink.
        add_filter('get_sample_permalink_html', [$this, 'buildSamplePermalinkHTML'], 20, 2);

        // Override styles for specific needs, for first time -> hide old permalink field from sidebar.
        add_action('admin_enqueue_scripts', [$this, 'customCerberusAppsStyles']);
    }

    /**
     * Replace default slug field for all queries with applications.
     *
     * @param $result
     * @param $source
     * @param $args
     * @param $context
     * @param $info
     * @param $type_name
     * @param $field_key
     * @param $field
     * @param $field_resolver
     *
     * @return mixed
     */
    public function updateSlugBasedOnApplication(
        $result,
        $source,
        $args,
        $context,
        $info,
        $type_name,
        $field_key,
        $field,
        $field_resolver
    ): mixed {
        // If some fields, which we use is not set => return result.
        if (empty($field) || empty($source) || empty($info)) {
            return $result;
        }

        // Replace slug by application slug.
        // Need to do that only for posts and when parent is post => to avoid replace all subfields with slug key.
        $parentModel = $info->parentType->config['model'] ?? '';
        if ($field->name === 'slug' && $source instanceof Post && $parentModel == Post::class) {
            if (BlemmyaeApplications::doesPostSupportApplication($source->ID)) {
                $applicationSlug = get_field(self::APPLICATION_SLUG_FIELD, $source->ID);

                // If we have empty application slug => use permalink.
                return $applicationSlug ?: $result;
            }
        }

        return $result;
    }

    /**
     * Override styles for admin ui.
     *
     * @return void
     */
    public function customCerberusAppsStyles(): void
    {
        // Need to run only for supported content.
        $postId = get_the_ID();

        if ($postId && BlemmyaeApplications::doesPostSupportApplication($postId)) {
            wp_register_style(
                'custom-cerberus-apps-styles',
                plugins_url('src/applications/customCerberusAppsStyles.css', dirname(__DIR__)),
                [],
                null
            );

            wp_enqueue_style('custom-cerberus-apps-styles');
        }
    }

    /**
     * Build HTML with permalinks and applications.
     *
     * @param $return
     *  HTML output.
     * @param $postId
     *  Post ID.
     *
     * @return string
     *  HTML markup.
     * @todo need to check that content is supported apps.
     */
    public function buildSamplePermalinkHTML($return, $postId): string
    {
        return '';
    }

    /**
     * Render HTML with application permalinks after the field.
     *
     * @return void
     */
    public function updateApplicationSlugFieldRender(): void
    {
        if ($postId = get_the_ID()) {
            echo '</br>' . Permalink::buildApplicationSlugHTML($postId) . '</br>';
        }
    }

    /**
     * Replace permalink.
     */
    public function replacePermalink($permalink, $post)
    {
        if (empty($permalink) || !in_array($post->post_type, BlemmyaeApplications::supportedPostTypes())) {
            return $permalink;
        }

        // Get app slug.
        $applicationSlug = get_field(self::APPLICATION_SLUG_FIELD, $post->ID, false);

        // WP default slug.
        $slug = $post->post_name;

        // Remove app prefix, if it exists.
        $slug = Permalink::removeAppsPrefixFromSlug($slug);

        // Replace default slug at the end of permalink.
        return substr_replace($permalink, $applicationSlug, -strlen($slug));
    }

    /**
     * Make 2nd application field read-only for the first time.
     *
     * @todo need to be removed when we remove old application field.
     */
    public function makeFieldReadonly($field)
    {
        $field['disabled'] = true;

        return $field;
    }

    /**
     * Update new application field based on old application field value, when new application field was updated.
     *
     * @todo need to be removed when we remove old application field.
     */
    public function updateApplicationsField($value, $postId, $field, $original)
    {
        // Applications field right now read-only => load data from old application field
        $application = get_field(self::APPLICATION_FIELD, $postId, false);

        return [$application];
    }

    /**
     * Update new application field based on old application field value, when application field was updated.
     *
     * @todo need to be removed when we remove old application field.
     */
    public function updateApplicationField($value, $postId, $field, $original)
    {
        // When we add application => save new data in to the new applications field too.
        remove_filter('acf/update_value/key=' . self::APPLICATIONS_FIELD, [
            $this,
            'updateApplicationsField',
        ], 10, 4);

        update_field(self::APPLICATIONS_FIELD, [$value], $postId);

        add_filter('acf/update_value/key=' . self::APPLICATIONS_FIELD, [
            $this,
            'updateApplicationsField',
        ], 10, 4);

        return $value;
    }

    /**
     * Generate slug based on title, if it's not specified.
     *
     * @param $value
     * @param $postId
     *
     * @return string
     */
    public function generatePermalink($value, $postId): string
    {
        // Create slug based on title.
        if (empty($value)) {
            $title = get_the_title($postId);

            return str_replace('%ef%bf%bc', '', sanitize_title($title));
        }

        return str_replace('%ef%bf%bc', '', $value);
    }

    /**
     * Generate slug based on title, if it's not specified.
     *
     * @param $postId
     *
     * @return void
     */
    public function generatePermalinkOnSavePost($postId): void
    {
        // Post status.
        $postStatus = get_post_status($postId);

        // Application slug.
        $slug = get_field(self::APPLICATION_SLUG_FIELD, $postId, false);

        // No needs to generate new slug for auto-drafts.
        if (empty($slug) && $postStatus !== 'auto-draft') {
            // Update slug field.
            update_field(self::APPLICATION_SLUG_FIELD, '', $postId);
        }
    }

    /**
     * Add/update entry in the cross application redirects table if post's app was changed.
     *
     * @param int|string $newApp
     * @param int|string $postId
     *
     * @return int|string
     */
    public function createCrossAppRedirect(mixed $newApp, mixed $postId): mixed
    {
        $oldApp = get_field(self::APPLICATION_FIELD, $postId, false);
        // Proceed only if application field was updated meaningfully.
        if (!$oldApp || !$newApp || $oldApp === $newApp) {
            return $newApp;
        }

        global $wpdb;

        $table = BlemmyaeApplications::CROSS_APP_REDIRECTS_TABLE;
        $oldAppSlug = BlemmyaeApplications::getAppSlugByTermId($oldApp);
        $newAppSlug = BlemmyaeApplications::getAppSlugByTermId($newApp);

        // Remove bad (empty, duplicate, or circular) redirects.
        $redirects = $wpdb->get_results($wpdb->prepare(
            "SELECT source FROM $table WHERE post_id = %d AND source IN ('', '%s', '%s');",
            $postId,
            $oldAppSlug,
            $newAppSlug
        ));
        foreach ($redirects as $redirect) {
            $wpdb->delete($table, [
                'post_id' => $postId,
                'source' => $redirect->source,
            ]);
        }

        // Safely insert new redirect without fearing duplicate keys because it was just cleaned up.
        $wpdb->insert($table, [
            'post_id' => $postId,
            'source' => $oldAppSlug,
            'destination' => $newAppSlug,
        ]);

        return $newApp;
    }

    /**
     * Check redirects based on application slug field and create a new one if needed.
     *
     * @param $slug
     * @param $postId
     * @param $field
     *
     * @return string
     * @throws \Exception
     */
    public function checkRedirects($slug, $postId, $field): string
    {
        // If post skip slug update => we do not need redirects here.
        if (BlemmyaeApplications::skipSlugUpdate($postId)) {
            return $slug;
        }

        // Load old value.
        $oldValue = get_field($field['key'], $postId, false);

        // If slug was updated => we need to add redirects.
        if (!empty($oldValue) && $slug !== $oldValue) {
            $source = get_permalink($postId);

            // Remove base url.
            $source = wp_make_link_relative($source);

            // @todo refactor -> need to update buildPermalinkByApp and move replace slug as separate function.
            // Build permalink with new slug.
            $app = BlemmyaeApplications::getAppIdByPostId($postId);
            $target = Permalink::buildPermalinkByApp($source, $app, $slug);
            $target = wp_make_link_relative($target);

            Redirects::upsertRedirect($source, $target);
        }

        return $slug;
    }

    /**
     * Add number prefix, if application's slug exists.
     *
     * @param $slug
     * @param $postId
     *
     * @return string
     */
    public function uniqApplicationSlug($slug, $postId): string
    {
        // We do not need to check post slugs for drafts or revisions.
        if (BlemmyaeApplications::skipSlugUpdate($postId)) {
            return $slug;
        }

        // Get application.
        $applications = get_field(self::APPLICATIONS_FIELD, $postId, false) ?: [];

        // Prepare suffix.
        $suffix = 1;
        $isUniqApplicationSlug = false;

        // Copy slug value into temp variable, which will be modified in do-while.
        $updatedSlug = $slug;

        // Build wp query args without slug condition.
        $wpQueryArgs = [
            'post_type' => get_post_type($postId),
            'post_status' => BlemmyaeApplications::applicationSlugAvailablePostStatuses($postId),
            'fields' => 'ids',
            'post__not_in' => [$postId],
            'tax_query' => [
                'relation' => 'AND',
                [
                    'taxonomy' => BlemmyaeApplications::TAXONOMY,
                    'terms' => $applications,
                    'compare' => 'IN',
                ],
            ],
        ];

        // Add suffix to slug until application slugs is uniq.
        do {
            // Add slug to args..
            $query = new WP_Query([
                ...$wpQueryArgs,
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => self::APPLICATION_SLUG_FIELD_META_KEY,
                        'value' => $updatedSlug,
                    ],
                ],
            ]);

            $isUniqApplicationSlug = !$query->have_posts();

            if (!$isUniqApplicationSlug) {
                $updatedSlug = $slug . '-' . $suffix++;
            }
        } while (!$isUniqApplicationSlug);

        return $updatedSlug;
    }

    /**
     * Get upload directory path.
     *
     * @param string $app
     *  Application name.
     *
     * @return string
     *  Path to upload directory for apps.
     */
    public static function getUploadDirBasePath(string $app): string
    {
        return self::getUploadSubDir($app, '');
    }

    /**
     * Get upload subdirectory path.
     *
     * If path does not exist => we will create folder.
     *
     * @param string $app
     *  Application name.
     * @param string $subdirectory
     *  Subdirectory path.
     *
     * @return string
     *  Path to upload directory for apps.
     */
    public static function getUploadSubDir(string $app, string $subdirectory): string
    {
        // Build directory path.
        // Path structure - {based_dir}/apps/{apps_name}/{subdir}.
        $directory = implode(
            '/',
            array_filter([
                wp_get_upload_dir()['basedir'],
                'apps',
                $app,
                $subdirectory,
            ])
        );

        // Of directory does not exist => create it.
        if (!file_exists($directory)) {
            wp_mkdir_p($directory);
        }

        // Return directory path.
        return $directory;
    }
}
