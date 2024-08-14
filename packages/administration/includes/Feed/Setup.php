<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Scm\Feed;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Exception;
use Scm\Tools\Utils as ToolsUtils;
use Scm\WP_GraphQL\Utils;
use WP_Post;
use WP_Term;

/**
 * Class sets up feed via WordPress hooks.
 */
class Setup
{
    public const PUBSUBHUBBUB_HUB_URL = 'https://pubsubhubbub.appspot.com/';

    private const FEEDS_REGISTERED_VERSION_OPTION = 'cra_feeds_registered_version';

    private const FEEDS_CURRENT_VERSION = '1.4';

    /**
     * Get frontend feed path.
     *
     * @param WP_Term $term
     *
     * @return string
     */
    public static function frontendFeedPath(WP_Term $term): string
    {
        return "/feed/$term->taxonomy/$term->slug";
    }

    /**
     * Init WP hooks.
     *
     * @return void
     */
    public function initHooks(): void
    {
        add_action('init', [$this, 'registerFeeds']);
        add_action('save_post_editorial', [$this, 'savePostEditorialCallback'], 10, 2);
    }

    /**
     * Register feeds.
     *
     * @return void
     * @throws Exception
     */
    public function registerFeeds(): void
    {
        // todo refactor
        $apps = [BlemmyaeApplications::SCM, BlemmyaeApplications::MSSP, BlemmyaeApplications::CE2E];
        foreach ($apps as $app) {
            $terms = get_terms(['taxonomy' => ['topic']]);
            // Add the 'Latest' feed for Google News.
            $terms[] = new WP_Term(
                (object)[
                    'name' => 'Latest',
                    'slug' => 'latest',
                    'taxonomy' => 'topic',
                    'term_group' => 0,
                ]
            );
            if (is_wp_error($terms)) {
                throw new Exception($terms->get_error_message());
            }

            foreach ($terms as $term) {
                // We have to do unique slug prefix for the feed because otherwise it can lead to.
                // an infinite redirect loop due to remove-cpt-base plugin matching by slug and
                // finding an actual post (usually landing) and trying to incorrectly redirect.
                add_feed("$app-$term->taxonomy-$term->slug", static function () use ($term, $app) {
                    load_template(
                        ADMINISTRATION_PATH . '/templates/gpc-atom-feed.php',
                        true,
                        [
                            'term' => $term,
                            'app' => $app,
                        ]
                    );
                });
            }

            // Flush rewrite rules if it is the first time this set of rules is being added.
            $feedRegisteredVersion = get_option(self::FEEDS_REGISTERED_VERSION_OPTION, '0.1');
            if (
                version_compare(
                    $feedRegisteredVersion,
                    self::FEEDS_CURRENT_VERSION,
                    '<'
                ) === true
            ) {
                flush_rewrite_rules();
                update_option(self::FEEDS_REGISTERED_VERSION_OPTION, self::FEEDS_CURRENT_VERSION);
            }
        }
    }

    /**
     * Callback for save_post_editorial hook.
     *
     * @param int $postId
     * @param WP_Post $post
     *
     * @return void
     */
    public function savePostEditorialCallback(int $postId, WP_Post $post): void
    {
        if ($post->post_status !== 'publish') {
            return;
        }

        // Since save_post_POST_TYPE hook fires before acf/save_post but acf/save_post is only triggered from
        // updating posts though Gutenberg, there is a very important assumption here that all posts
        // spend some time in draft and their topics set ahead of the final publishing (manually or through scheduler).
        // If the assumption becomes invalid then this (or any other solution) becomes unreliable and quite challenging
        // to properly resolve.

        $this->notifyPubSubHubbubHub(get_field('topic', $postId) ?? []);
    }

    /**
     * Notify Google PubSubHubbub Hub about feed changes.
     *
     * @param array|WP_Term[] $terms
     *
     * @return void
     */
    private function notifyPubSubHubbubHub(array $terms): void
    {
        if (empty($terms) || !ToolsUtils::isProd()) {
            return;
        }

        $params = array_map(
            static fn(WP_Term $term
            ) => 'hub.url=' . esc_url(Utils::frontendUri() . self::frontendFeedPath($term)),
            $terms
        );
        array_unshift($params, 'hub.mode=publish');

        wp_remote_post(self::PUBSUBHUBBUB_HUB_URL, [
            'timeout' => 10,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'blocking' => false,
            'body' => implode('&', $params),
        ]);
    }
}
