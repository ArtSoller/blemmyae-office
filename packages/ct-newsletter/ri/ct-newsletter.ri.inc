<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-9
 * @noinspection PhpUnused
 */

declare(strict_types=1);

const TAXONOMY_NEWSLETTER_TYPE = 'newsletter_type';

use Cra\CtNewsletter\Newsletter;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

/**
 * Import Newsletter type taxonomy.
 */
function ct_newsletter_ri_034(): void
{
    $topicTaxonomy = array_map(
        fn(array $row) => [
            'category' => $row[0],
            'subcategory' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/newsletter_type.csv')
    );

    // Insert categories, subcategories and topics.
    foreach ($topicTaxonomy as $item) {
        Utils::insertTaxonomyTerm($item['category'], TAXONOMY_NEWSLETTER_TYPE);
        Utils::insertTaxonomyTerm($item['subcategory'], TAXONOMY_NEWSLETTER_TYPE, $item['category']);
    }
    wp_cache_flush();
}

/**
 * Import allowed topics options.
 */
function ct_newsletter_ri_029(): void
{
    // Due to a bug in phpcs, the next line is required.
    $allowedTopicsByNewsletterType = array_map(
        fn(array $row) => [
            'newsletter_type' => get_term_by('name', $row[0], 'newsletter_type')->term_id,
            'topics' => array_map(
                fn(string $name) => get_term_by('name', $name, 'topic')->term_id,
                explode(',', $row[1])
            ),
        ],
        Utils::importCsv(__DIR__ . '/allowed_topics_by_newsletter_type.csv')
    );

    try {
        $success = update_field(
            'allowed_topics_by_newsletter_type',
            $allowedTopicsByNewsletterType,
            Newsletter::GENERATE_NEWSLETTER_POST_ID
        );
        if ($success) {
            Logger::log('Updated allowed_topics_by_newsletter_type', 'success');
        } else {
            Logger::log('Skipped allowed_topics_by_newsletter_type', 'warning');
        }
    } catch (Exception $exception) {
        Logger::log('Error updating allowed_topics_by_newsletter_type: ' . $exception->getMessage(), 'error');
    }
}

/**
 * Import allowed application options.
 */
function ct_newsletter_ri_033(): void // @todo: combine import allowed topics options and application option in a separate function.
{
    // Due to a bug in phpcs, the next line is required.
    $allowedAppByNewsletterType = array_map(
        static fn(array $row) => [
            'newsletter_type' => get_term_by('name', $row[0], 'newsletter_type')->term_id,
            'apps' => array_map(
                static fn(string $name) => get_term_by('name', $name, 'applications')->term_id,
                explode(',', $row[1])
            ),
        ],
        Utils::importCsv(__DIR__ . '/allowed_apps_by_newsletter_type.csv')
    );

    try {
        $success = update_field(
            'allowed_apps_by_newsletter_type',
            $allowedAppByNewsletterType,
            Newsletter::GENERATE_NEWSLETTER_POST_ID
        );
        if ($success) {
            Logger::log('Updated allowed_apps_by_newsletter_type', 'success');
        } else {
            Logger::log('Skipped allowed_apps_by_newsletter_type', 'warning');
        }
    } catch (Exception $exception) {
        Logger::log('Error updating allowed_apps_by_newsletter_type: ' . $exception->getMessage(), 'error');
    }
}

/**
 * Set schedule_date field for all newsletters based on their title.
 */
function ct_newsletter_ri_016(): void
{
    $query = new WP_Query(['post_type' => Newsletter::POST_TYPE, 'nopaging' => true]);
    while ($query->have_posts()) {
        $post = $query->next_post();
        $currentPost = $query->current_post + 1;
        Logger::log("Processing $currentPost/$query->post_count.", 'info');

        // Example: DailyScan20210928
        $regex = '/(?<year>\d{4})(?<month>\d{2})(?<day>\d{2})$/m';
        preg_match($regex, $post->post_title, $matches);
        $year = $matches['year'] ?? null;
        $month = $matches['month'] ?? null;
        $day = $matches['day'] ?? null;
        if (!$year || !$month || !$day) {
            Logger::log("Cannot match schedule date for newsletter titled $post->post_title.", 'warning');
            continue;
        }

        $value = "$year-$month-$day";
        $success = update_field('schedule_date', $value, $post->ID);
        if (!$success) {
            Logger::log("Error updating schedule_date field to $value for newsletter titled $post->post_title.", 'error');
        }
    }
}
