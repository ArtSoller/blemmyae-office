<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/PORT-1639/SW-PPWorks-Wiki-404s
 */

declare(strict_types=1);

use Scm\Tools\Logger;
use Scm\Tools\WpCore;

/**
 * Fix topics for SW articles.
 *
 * @return void
 * @noinspection PhpUnused
 */
function port1639_blemmyae_deployment_ri_001(): void
{
    $contentTerm = get_term_by('slug', 'content', 'topic');
    if (!($contentTerm instanceof \WP_Term)) {
        Logger::log('Topic "content" does not exist!', 'warning');
        return;
    }

    $query = new WP_Query([
        'nopaging' => true,
        'post_type' => 'editorial',
        'post_status' => 'publish',
        'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => 'editorial_type',
                'field' => 'slug',
                'terms' => ['sw-article'],
            ],
            [
                'taxonomy' => 'topic',
                'field' => 'slug',
                'terms' => ['uncategorized'],
            ],
        ],
    ]);
    $count = 0;
    foreach ($query->posts as $post) {
        try {
            $topics = get_field('topic', $post->ID) ?: [];
            $terms = array_map(
                static fn(WP_Term $topic) => $topic->term_id,
                array_filter(
                    $topics,
                    static fn(WP_Term $topic) => $topic->name !== 'Uncategorized'
                )
            );
            if (count($topics) === count($terms)) {
                continue;
            }

            if (empty($terms)) {
                $terms[] = $contentTerm->term_id;
            }
            WpCore::setPostTerms('topic', $terms, $post->ID, 'topic');
            Logger::log("Updated topics for SW Article $post->post_title ($post->ID).", 'success');
            $count++;
        } catch (Exception $exception) {
            Logger::log($exception->getMessage(), 'warning');
        }
    }
    Logger::log("Updated topics for $count SW articles.", 'info');
}
