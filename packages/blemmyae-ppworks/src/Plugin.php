<?php

/**
 * Main plugin file
 *
 * @package   Cra\BlemmyaePpworks
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaePpworks;

use BrightNucleus\Config\ConfigInterface;
use BrightNucleus\Config\ConfigTrait;
use BrightNucleus\Config\Exception\FailedToProcessConfigException;
use BrightNucleus\Settings\Settings;
use DateTime;
use Exception;
use Scm\WP_GraphQL\Options;
use WP_Query;

use function add_action;

/**
 * Main plugin class.
 *
 * @since 0.1.0
 *
 * @package Cra\BlemmyaePpworks
 * @author  Gary Jones
 */
class Plugin
{
    use ConfigTrait;

    private const PPWORKS_TO_BE_PUBLISHED_CRON = 'ppworks_to_be_published_cron';

    /**
     * Static instance of the plugin.
     *
     * @since 0.1.0
     *
     * @var self
     */
    protected static Plugin $instance;

    /**
     * Instantiate a Plugin object.
     *
     * Don't call the constructor directly, use the `Plugin::get_instance()`
     * static method instead.
     *
     * @param ConfigInterface $config Config to parametrize the object.
     *
     * @throws FailedToProcessConfigException If the Config could not be parsed correctly.
     *
     * @since 0.1.0
     *
     */
    public function __construct(ConfigInterface $config)
    {
        $this->processConfig($config);
    }

    /**
     * Launch the initialization process.
     *
     * @since 0.1.0
     */
    public function run(): void
    {
        add_action('admin_menu', [$this, 'hidePpworksStructureFromMenu']);
        add_action('init', [$this, 'registerPostStatus']);
        add_filter(
            Options::PUBLIC_POST_STATUSES_FILTER,
            static fn(array $statuses): array => array_merge(
                $statuses,
                [Ppworks::POST_STATUS__UNFINISHED, Ppworks::POST_STATUS__TO_BE_PUBLISHED]
            )
        );

        add_action(self::PPWORKS_TO_BE_PUBLISHED_CRON, [$this, 'publishPosts']);
        if (!wp_next_scheduled(self::PPWORKS_TO_BE_PUBLISHED_CRON)) {
            wp_schedule_event(time(), 'hourly', self::PPWORKS_TO_BE_PUBLISHED_CRON);
        }

        foreach (Ppworks::postTypes() as $postType) {
            new PpworksByQueryResolver($postType);
        }

        new PpworksAnnouncementCT();
        new PpworksArticleCT();
        new PpworksEpisodeCT();
        new PpworksSegmentCT();
        new PpworksSponsorProgramCT();
    }

    /**
     * Hide ppworks content types' pages from admin menu for non-admins.
     */
    public function hidePpworksStructureFromMenu(): void
    {
        // Hide ppworks structure from menu for non-admins.
        $showMenu = current_user_can('administrator');
        if (!$showMenu) {
            remove_menu_page('edit.php?post_type=' . PpworksAnnouncementCT::POST_TYPE);
            remove_menu_page('edit.php?post_type=' . PpworksEpisodeCT::POST_TYPE);
            remove_menu_page('edit.php?post_type=' . PpworksSegmentCT::POST_TYPE);
            remove_menu_page('edit.php?post_type=' . PpworksSponsorProgramCT::POST_TYPE);
        }

        // Always hide ppworks articles from admin menu.
        remove_menu_page('edit.php?post_type=' . PpworksArticleCT::POST_TYPE);
    }

    /**
     * Register post status for unfinished episodes and segments.
     *
     * @return void
     */
    public function registerPostStatus(): void
    {
        register_post_status(
            Ppworks::POST_STATUS__UNFINISHED,
            [
                'label' => __('Unfinished', 'blemmyae-ppworks'),
                'exclude_from_search' => true,
                'public' => true,
            ]
        );
        register_post_status(
            Ppworks::POST_STATUS__TO_BE_PUBLISHED,
            [
                'label' => __('To Be Published', 'blemmyae-ppworks'),
                'exclude_from_search' => true,
                'public' => true,
            ]
        );
    }

    /**
     * Publish to_be_published posts which are ready.
     *
     * @return void
     * @throws Exception
     */
    public function publishPosts(): void
    {
        $now = new DateTime('now', wp_timezone());
        $args = [
            'post_status' => Ppworks::POST_STATUS__TO_BE_PUBLISHED,
            'post_type' => 'any',
            'nopaging' => true,
            'date_query' => [
                'before' => $now->format('c'),
            ],
        ];
        $query = new WP_Query($args);
        while ($query->have_posts()) {
            if ($post = $query->next_post()) {
                wp_publish_post($post);
            }
        }
    }
}
