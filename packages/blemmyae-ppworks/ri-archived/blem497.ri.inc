<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-497
 * @noinspection PhpUnused
 */

use Cra\BlemmyaePpworks\Ppworks;
use Cra\BlemmyaePpworks\PpworksEpisodeCT;
use Cra\BlemmyaePpworks\PpworksSegmentCT;
use Cra\CtEditorial\EditorialCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\WP_GraphQL\Options;

/**
 * Set status of the next long-running RI to completed because it should be called manually.
 */
function blem497_blemmyae_ppworks_ri_001(): void
{
    (new RI())->setStatus('blem497_blemmyae_ppworks_ri_002', true);
}

/**
 * Create redirects from podcasts and group podcasts to segments and episodes.
 */
function blem497_blemmyae_ppworks_ri_002(): void
{
    $query = new WP_Query([
        'nopaging' => true,
        'post_type' => 'editorial',
        'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => 'editorial_type',
                'field' => 'name',
                'terms' => [
                    'Group Podcast',
                    'Podcast',
                ],
            ],
        ],
    ]);

    $startTime = time();
    while ($query->have_posts()) {
        $post = $query->next_post();

        $unprocessedPostsCount = $query->post_count - $query->current_post;
        $completed = round(100 * $query->current_post / $query->post_count, 2);
        $elapsed_time = format_elapsed_time($startTime, time());
        Logger::log(
            "Processed $completed% of posts in $elapsed_time. Left to process: $unprocessedPostsCount.",
            'info'
        );

        $postUri = _blem497_get_post_permalink_uri($post);
        Logger::log("Processing '$post->post_title' ($post->ID) - $postUri", 'info');
        $redirectUri = blem497_find_post_redirect_uri($post);
        if (!$redirectUri) {
            Logger::log("Cannot find redirect URL for '$post->post_title' ($post->ID) - $postUri", 'warning');
            continue;
        }

        /** @phpstan-ignore-next-line */
        $redirect = Red_Item::create([
            'url' => $postUri,
            'action_data' => ['url' => $redirectUri],
            'regex' => false,
            'group_id' => 1,
            'match_type' => 'url',
            'action_type' => 'url',
            'action_code' => 301,
        ]);
        if (is_wp_error($redirect)) {
            Logger::log(
            // phpcs:ignore Generic.Files.LineLength.TooLong
                "Error creating redirect for '$post->post_title' ($post->ID) from $postUri, to $redirectUri. Error = {$redirect->get_error_message()}",
                'warning'
            );
            continue;
        }

        Logger::log(
            "Successfully created redirect for '$post->post_title' ($post->ID) from $postUri, to $redirectUri",
            'success'
        );

        if (!wp_delete_post($post->ID, true)) {
            Logger::log("Error deleting post '$post->post_title' ($post->ID)", 'warning');
            continue;
        }

        Logger::log("Successfully deleted post '$post->post_title' ($post->ID)", 'success');
    }

    wp_cache_flush();
}

/**
 * Format elapsed time in easy to comprehend format.
 *
 * @param int $startTime
 * @param int $now
 *
 * @return string
 */
function format_elapsed_time(int $startTime, int $now): string
{
    $seconds = $now - $startTime;
    if ($seconds < 60) {
        return "$seconds seconds";
    }
    if ($seconds < 3600) {
        $minutes = round($seconds / 60);
        return "$minutes minutes";
    }
    $hours = round($seconds / 3600);
    return "$hours hours";
}

/**
 * Find post redirect URI.
 *
 * @param WP_Post $post
 *
 * @return string|null
 */
function blem497_find_post_redirect_uri(WP_Post $post): ?string
{
    /** @var WP_Term|null $editorialType */
    $editorialType = get_field(EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TYPE, $post->ID);
    if (!$editorialType) {
        return null;
    }
    switch ($editorialType->name) {
        case 'Group Podcast':
            $redirectUri = _blem497_find_group_podcast_redirect_url($post);
            break;
        case 'Podcast':
            $redirectUri = _blem497_find_podcast_redirect_url($post);
            break;
        default:
            $redirectUri = null;
    }

    return $redirectUri ? untrailingslashit(Options::forceRelativeUrl($redirectUri)) : null;
}

/**
 * Find group podcast redirect URL.
 *
 * @param WP_Post $post
 *
 * @return string|null
 */
function _blem497_find_group_podcast_redirect_url(WP_Post $post): ?string
{
    // E.g. "PSW #736" or "Tomatoes, Jeff Bezos, Vuln. In AMD ATI Radeon, ‘The Rise of Skywalker’ – PSW #636"
    $re = '/\b(?<show>[\w ]+) #(?<number>\d+)/m';
    preg_match($re, $post->post_title, $matches);
    if (empty($matches)) {
        return null;
    }

    $showMap = [
        'ASW' => "Application Security Weekly",
        'Application Security Weekly' => "Application Security Weekly",
        'BSW' => "Business Security Weekly",
        'Business Security Weekly' => "Business Security Weekly",
        'ESW' => "Enterprise Security Weekly",
        'Enterprise Security Weekly' => "Enterprise Security Weekly",
        'HNN' => "Hack Naked News",
        'Hack Naked News' => "Hack Naked News",
        'PSW' => "Paul's Security Weekly",
        "Paul's Security Weekly" => "Paul's Security Weekly",
        'RSA21' => "RSAC 2021",
        'SCW' => "Security and Compliance Weekly",
        'Security and Compliance Weekly' => "Security and Compliance Weekly",
        'SWN' => "Security Weekly News",
        'Security Weekly News' => "Security Weekly News",
        'CSP' => "The CISO Stories Podcast",
    ];

    if (!isset($showMap[$matches['show']])) {
        return null;
    }

    $query = new WP_Query([
        'post_type' => PpworksEpisodeCT::POST_TYPE,
        'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => Ppworks::TAXONOMY__SHOW,
                'field' => 'name',
                'terms' => [$showMap[$matches['show']]],
            ],
        ],
        'meta_query' => [
            [
                'key' => 'number',
                'value' => $matches['number'],
            ],
        ],
    ]);
    $episodePost = $query->have_posts() ? $query->next_post() : null;

    return $episodePost ? get_post_permalink($episodePost->ID) : null;
}

/**
 * Get post permalink with generic %topic% instead of an actual topic.
 *
 * @param WP_Post $post
 *
 * @return string
 */
function _blem497_get_post_permalink_uri(WP_Post $post): string
{
    if ($post->post_type !== 'editorial') {
        return get_post_permalink($post);
    }

    // e.g. /group-podcast/risk-management/psw-701
    $uri = untrailingslashit(Options::forceRelativeUrl(get_post_permalink($post)));
    $parts = explode('/', $uri);
    if (count($parts) !== 4) {
        return $uri;
    }
    $parts[2] = '%topic%';

    return implode('/', $parts);
}

/**
 * Find podcast redirect URL.
 *
 * @param WP_Post $post
 *
 * @return string|null
 */
function _blem497_find_podcast_redirect_url(WP_Post $post): ?string
{
    if (_blem497_is_old_psw($post->post_title)) {
        // This is missing in ppworks.
        return '/podcast-show/pauls-security-weekly';
    }
    if (_blem497_is_old_show($post->post_title)) {
        // This show is missing in ppworks.
        return '/security-weekly';
    }

    foreach (_blem497_search_queries($post) as $query) {
        if ($query->post_count > 1) {
            // Too vague, continuing the search.
            continue;
        }

        if ($query->post_count === 1) {
            $segmentPost = $query->next_post();
            if ($segmentPost) {
                return get_post_permalink($segmentPost->ID);
            }
        }
    }

    // Let's just try mapping to an episode.
    // Users will be able to find segment they are looking from there.
    return _blem497_find_group_podcast_redirect_url($post);
}

/**
 * Check if it is an old Paul's Security Weekly.
 *
 * @param string $title
 *
 * @return bool
 */
function _blem497_is_old_psw(string $title): bool
{
    $re = '/Security Weekly .*?\bEpisode (?<number>\d+)\b/m';
    preg_match($re, $title, $matches);
    if (empty($matches)) {
        return false;
    }

    $number = (int) $matches['number'];
    return $number < 357 && !in_array($number, [275, 252, 251, 250], true);
}

/**
 * Check if it is an old show.
 *
 * @param string $title
 *
 * @return bool
 */
function _blem497_is_old_show(string $title): bool
{
    if (str_contains($title, 'Getting the Real Work Done in Cybersecurity #')) {
        return true;
    }

    return false;
}

/**
 * Get all possible search queries for episodes and segments to map into.
 *
 * @param WP_Post $post
 *
 * @return Generator<WP_Query>
 */
function _blem497_search_queries(WP_Post $post): Generator
{
    $baseQuery = [
        'posts_per_page' => 2,
        'post_type' => [
            PpworksSegmentCT::POST_TYPE,
            PpworksEpisodeCT::POST_TYPE,
        ],
    ];

    yield new WP_Query(
        $baseQuery + [
            'post_name__in' => [
                $post->post_name,
                substr($post->post_name, 0, strlen($post->post_name) - 2), // Remove -2, -3, etc. suffix from the end.
            ],
        ]
    );

    $sanitizedTitle = htmlentities($post->post_title);
    $searches = [
        $post->post_title,
        $sanitizedTitle,
        // This is special ndash symbol on the right.
        // Don't mistake it with regular dash "-"
        // with this extra longer special one "–".
        str_replace('&ndash;', '–', $sanitizedTitle),
        str_replace('&amp;', '&', $sanitizedTitle),
    ];
    foreach ($searches as $search) {
        yield new WP_Query(
            $baseQuery + [
                's' => $search,
            ]
        );
    }
}
