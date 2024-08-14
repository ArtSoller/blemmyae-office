<?php

/**
 * BlemmyaeApplications class, does BlemmyaeApplications
 *
 * @package   Cra\BlemmyaeApplications
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeApplications;

use Cra\BlemmyaeApplications\Entity\Permalink;
use Cra\CtNewsletter\NewsletterCT;
use Redirection_Capabilities;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

/**
 * BlemmyaeApplications class.
 */
class BlemmyaeApplications
{
    public const CE2E = 'ce2e';

    public const CISO = 'ciso';

    public const CSC = 'csc';

    public const MSSP = 'mssp';

    public const NLT = 'nlt';

    public const SCM = 'scm';

    public const CRC = 'crc';

    public const TAXONOMY = 'applications';

    public const CROSS_APP_REDIRECTS_TABLE = 'wp_cross_application_redirects';

    /**
     * Initialize hooks.
     */
    public function __construct()
    {
        add_action('rest_api_init', function () {
            register_rest_route('blemmyae-applications/v1', '/app-redirects', [
                'methods' => 'POST',
                'callback' => [$this, 'getCrossApplicationRedirects'],
                'args' => [
                    'app' => [
                        'validate_callback' => function ($param, $request, $key) {
                            return is_string($param);
                        },
                    ],
                ],
                'permission_callback' => function () {
                    /** @phpstan-ignore-next-line */
                    return Redirection_Capabilities::has_access(Redirection_Capabilities::CAP_IO_MANAGE);
                },
            ]);
        });
    }

    /**
     * Endpoint for fetching redirects by app.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function getCrossApplicationRedirects(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;

        $response = new WP_REST_Response();

        try {
            $redirects = [];
            $table = self::CROSS_APP_REDIRECTS_TABLE;
            $app = $request->get_param('app');

            if (!$app) {
                throw new \Exception('No Application was provided');
            }

            if (!in_array($app, BlemmyaeApplications::getListOfAvailableApps())) {
                throw new \Exception('Provided Application doesn\'t exist');
            }

            $posts = $wpdb->get_results(
                "SELECT post_id FROM $table WHERE source = '$app' AND destination != '$app';",
                ARRAY_N
            );

            if (!$posts) {
                throw new \Exception('No redirects found for this app');
            }

            foreach ($posts as $post) {
                $postId = reset($post);

                if ($permalink = get_permalink($postId)) {
                    $redirects[wp_make_link_relative($permalink)] = $permalink;
                }
            }
        } catch (\Exception $exception) {
            $response->set_data($exception->getMessage());
            $response->set_status(404);

            return $response;
        }

        $response->set_data($redirects);
        $response->set_status(200);

        return $response;
    }

    /**
     * Get list of available apps.
     *
     * This list contain only apps, with which we can work on SCM by editing
     * content. For ex. create landings for this page.
     *
     * @return string[]
     *  Array of available apps.
     */
    public static function getListOfAvailableApps(): array
    {
        # @todo: Investigate if missing `nlt` app is an intended behaviour.
        return [
            self::SCM,
            self::CISO,
            self::CSC,
            self::MSSP,
            self::CE2E,
            self::CRC
        ];
    }

    /**
     * Get list of supported post types from field group settings.
     *
     * @todo need to do that dynamically, based on location rules in field group.
     */
    public static function supportedPostTypes(): array
    {
        $fieldGroupSettings = acf_get_field_group(CerberusApps::APPLICATION_FIELD_GROUP_ID);

        // Right now we need settings from 1st location rule in every OR group.
        // If we change it in DB, then we must change the code.
        $locationOrFirstElement = array_column($fieldGroupSettings['location'], '0');

        return array_column($locationOrFirstElement, 'value');
    }

    /**
     * Check that post supports applications.
     *
     * @param mixed $postId
     *
     * @return bool
     */
    public static function doesPostSupportApplication(mixed $postId): bool
    {
        $postType = get_post_type($postId);

        return $postType && in_array($postType, self::supportedPostTypes());
    }

    /**
     * Get app from post.
     *
     * @param WP_Post $post
     *   WP Post.
     *
     * @return string
     *  Return app name.
     */
    public static function getAppByPostObject(WP_Post $post): string
    {
        // Load application from application field.
        if ($applicationField = get_field(CerberusApps::APPLICATION_FIELD, $post)) {
            // Fallback to null in case application field is not a term object
            $app = $applicationField->slug ?? null;

            // If application is available => return it.
            if (in_array($app, self::getListOfAvailableApps())) {
                return $app;
            }
        }

        // If we can not determine application => use SCM by default.
        return self::SCM;
    }

    /**
     * Get frontend path for specific post.
     *
     * @param WP_Post $post
     *
     * @return string
     */
    public static function getFrontendPathByPost(WP_Post $post): string
    {
        // Load app by post.
        $app = self::getAppByPostObject($post);

        // Load path by app.
        return Permalink::buildFrontendPathByApp($app);
    }

    /**
     * Get app from post id.
     *
     * @param mixed $postId
     *   WP Post ID.
     *
     * @return string
     *  Return app name.
     */
    public static function getAppIdByPostId(mixed $postId): string
    {
        if (!self::doesPostSupportApplication($postId)) {
            return self::SCM;
        }

        $post = get_post($postId);

        $postType = get_post_type($postId);

        // For newsletter apps should be nlt.
        // @todo create action hook. Because sometimes we need to use apps based on some specific rules for post type.
        if ($postType === NewsletterCT::POST_TYPE) {
            return self::NLT;
        }

        // Default value.
        if (!$post) {
            return self::SCM;
        }

        return self::getAppByPostObject($post);
    }

    /**
     * Check that path is related to specific application.
     *
     * Work only with Landing Page.
     *
     * @param string $app
     *  Application name.
     * @param string $path
     *  Path to the post.
     * @param string $type
     *  Post type.
     *
     * @return bool
     */
    public static function isAppsLandingPath(string $app, string $path, string $type): bool
    {
        // Do not work with non-landing pages.
        if ($type !== 'landing') {
            return false;
        }

        return $app === self::getAppsNameByLandingPath($path);
    }

    /**
     * Get apps prefix from landing path.
     *
     * @param string $path
     *  Landing path.
     *
     * @return string
     *  Return prefix.
     */
    public static function getAppsNameByLandingPath(string $path): string
    {
        $matches = [];

        preg_match("/^_(\w+)-/", ltrim($path, '/'), $matches);

        return $matches[1] ?? self::SCM;
    }

    /**
     * Checks if app is supported.
     *
     * @throws Exception
     */
    public static function isA9sAppSupported(string $app): bool
    {
        $sitesSupported = [
            self::MSSP,
            self::CE2E,
        ];

        if (!$app) {
            throw new \OutOfBoundsException(
                'Unsupported a9s website. Currently supported sites: ' .
                implode(', ', $sitesSupported)
            );
        }

        return in_array($app, $sitesSupported);
    }

    /**
     * Get list of available post statuses.
     */
    public static function applicationSlugAvailablePostStatuses($postId): array
    {
        require_once ABSPATH . 'wp-admin/includes/post.php';

        $postStatusExclude = ['draft', 'pending', 'auto-draft', 'trash'];

        return array_diff(get_available_post_statuses(get_post_type($postId)), $postStatusExclude);
    }

    /**
     * Check if post in publish state, and we can generate publish link.
     *
     * @param mixed $postId
     *
     * @return bool
     */
    public static function isPublishedPost(mixed $postId): bool
    {
        $postStatus = get_post_status($postId);

        return in_array($postStatus, self::applicationSlugAvailablePostStatuses($postId));
    }

    public static function skipSlugUpdate($postId): bool
    {
        $postType = get_post_type($postId);
        $postStatus = get_post_status($postId);

        // We do not need to check post slugs for drafts, revisions and rewrite/republish actions.
        return !self::isPublishedPost($postId)
               || ('inherit' === $postStatus && 'revision' === $postType)
               || 'user_request' === $postType
               || in_array($postStatus, ['dp-rewrite-republish', 'future'])
               || ($_POST['original_post_status'] ?? '') === 'auto-draft';
    }

    /**
     * Get Url prefix for apps.
     *
     * Prefix = code of apps with _, only SCM should use empty string.
     * If we do not have apps in list => anyway it will return prefix `_` + app
     * name.
     *
     * @param mixed $app
     *  Application name.
     *
     * @return string
     *  Return prefix code for landing page for $app.
     */
    public static function urlPrefixByApps(mixed $app): string
    {
        return $app !== self::SCM ? '_' . $app . '-' : '';
    }

    /**
     * Get app slug by term id.
     *
     * @param mixed $termId
     *
     * @return string
     */
    public static function getAppSlugByTermId(mixed $termId): string
    {
        $term = get_term_by('term_id', $termId, self::TAXONOMY);

        if (!is_object($term)) {
            return '';
        }

        return in_array($term->slug, self::getListOfAvailableApps()) ? $term->slug : '';
    }
}
