<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Scm\Entity;

use Generator;
use Scm\Tools\Logger;
use Scm\Tools\Utils;
use WP_Post;
use WP_Query;

use function wp_update_post;

/**
 * Class which handles Publishing Options settings.
 */
class PublishingOptions
{
    public const UNPUBLISH_DATE_FIELD = 'field_61af47d3a014d';

    private const UNPUBLISH_CRON = 'archive_posts_past_unpublish_date';

    /**
     * Init WP hooks.
     *
     * @return void
     */
    public function init(): void
    {
        add_action(self::UNPUBLISH_CRON, [$this, 'archivePostsPastUnpublishDate']);
        if (!wp_next_scheduled(self::UNPUBLISH_CRON)) {
            wp_schedule_event(time(), 'hourly', self::UNPUBLISH_CRON);
        }
    }

    /**
     * Archive posts which past Unpublish Date in Publishing Options.
     *
     * @return void
     */
    public function archivePostsPastUnpublishDate(): void
    {
        foreach ($this->postsPastUnpublishDate() as $post) {
            wp_update_post(
                [
                    'ID' => $post->ID,
                    'post_status' => 'archive',
                ]
            );
            if (Utils::isCLI()) {
                Logger::log("Archived post: ID = $post->ID, title = $post->post_title", 'info');
            }
        }
    }

    /**
     * Get posts past unpublish date.
     *
     * @return Generator
     */
    private function postsPastUnpublishDate(): Generator
    {
        $query = new WP_Query([
            'post_status' => 'publish',
            'post_type' => ['learning', 'whitepaper', 'newsletter'],
            'meta_query' => [
                [
                    'key' => 'unpublish_date',
                    'value' => date(Utils::ACF_DB_DATETIME_FORMAT),
                    'compare' => '<=',
                ],
                [
                    'key' => 'unpublish_date',
                    'value' => '',
                    'compare' => '!=',
                ],
            ],
            'nopaging' => true,
        ]);
        while ($query->have_posts()) {
            yield $query->next_post();
        }
    }
}
