<?php

/**
 * BLEM-645 or PORT-650.
 *
 * https://cra.myjetbrains.com/youtrack/issue/BLEM-645.
 */

declare(strict_types=1);

use Cra\CtEditorial\EditorialCT;
use Cra\CtNewsletter\NewsletterCT;
use Cra\CtPeople\PeopleCT;
use Scm\Entity\CustomPostType;
use Scm\Tools\Logger;

/**
 * Remove all authors info from field.
 */
function port650_administration_ri_005(): void
{
    // Load newsletter.
    $posts = get_posts([
        'numberposts' => -1,
        'post_type' => NewsletterCT::POST_TYPE,
    ]);

    $total = count($posts);
    $count = 1;

    foreach ($posts as $post) {
        // Save author field with empty field value => remove relations.
        // Delete field remove value from DB table => we will not have any
        // relations.
        delete_field(EditorialCT::GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR, $post->ID);

        // Logs.
        $msg = sprintf(
            "Processing: Newsletter #%s - %s - [%s/%s]",
            $post->ID,
            $post->post_name,
            $count++,
            $total
        );

        Logger::log($msg, 'info');
    }

    wp_cache_flush();
}

/**
 * Remove newsletter posts from posts field in people content.
 *
 * We need to do that, because in DB we have some information about newsletter
 * in posts field. It looks like some posts has been added in one-direction way
 * instead of bidirectional.
 */
function port650_administration_ri_006(): void
{
    // Load authors.
    $authors = get_posts([
        'numberposts' => -1,
        'post_type' => PeopleCT::POST_TYPE,
    ]);

    // Init vars for log.
    $total = count($authors);
    $count = 1;

    // Related field.
    $relatedPostField = "field_608fcb58aa715";

    // Go through autors and remove newsletter posts from related field.
    foreach ($authors as $author) {
        // Get author posts from post related field.
        $posts = get_field($relatedPostField, $author->ID);

        // Logs.
        $msg = sprintf(
            "Processing: Author #%s - %s - [%s/%s]",
            $author->ID,
            $author->post_name,
            $count++,
            $total
        );

        Logger::log($msg, 'info');

        if (!empty($posts)) {
            $updatedPosts = array_filter($posts, function ($post) use ($author) {
                $isNewsletter = $post->post_type == NewsletterCT::POST_TYPE;

                if ($isNewsletter) {
                    // Logs.
                    $msg = sprintf(
                        "Processing: Remove newsletter #%s - %s from author #%s - %s",
                        $post->ID,
                        $post->post_name,
                        $author->ID,
                        $author->post_name,
                    );

                    Logger::log($msg, 'warning');
                }

                return !$isNewsletter;
            });

            // Update field without newsletter.
            update_field($relatedPostField, $updatedPosts, $author->ID);
        }
    }

    wp_cache_flush();
}

/**
 * Logs.
 *
 * Try to load author with relationships.
 */
function port650_administration_ri_007(): void
{
    // This ri causes errors on qa1, and since it was run on prod, it's no longer needed.
    return;
    // Load posts by author field.
    // If author field has been removed => number of posts should be 0.
    $posts = get_posts([
        'numberposts' => -1,
        'post_type' => NewsletterCT::POST_TYPE,
        'meta_query' => [
            [
                'key' => 'author',
            ],
        ],
    ]);

    // If we have posts with author => report error, in this case we do not need to remove author
    // collection widget before fixing this error.
    $total = count($posts);

    if ($total !== 0) {
        foreach ($posts as $post) {
            // Logs.
            $msg = sprintf(
                "Newsletter #%s - %s - AUTHOR FIELD IS NOT EMPTY!",
                $post->ID,
                $post->post_name,
            );

            Logger::log($msg, 'warning');
        }

        // Report error.
        Logger::log(
            "All authors relations should be removed for 
        newsletter! Right now we have $total posts with authors",
            'error'
        );
    } else {
        Logger::log(
            "All authors relations has been removed",
            'success'
        );
    }
}
