<?php

/**
 * Newsml posts update.
 */

/**
 * Cleans up newsml post duplicates - moves them to trash.
 */
function blem128_blemmyae_deployment_ri_001(): void
{
    // Get all newsml posts in publish & draft states.
    $queryArg = [
        'post_type' => 'newsml_post',
        'numberposts' => -1,
    ];
    $publishPosts = get_posts($queryArg);
    $draftPosts = get_posts(array_merge($queryArg, ['post_status' => 'draft']));

    // Exit if none found.
    if (!array_merge($draftPosts, $publishPosts)) {
        return;
    }

    // All published.
    $publish = [];
    foreach ($publishPosts as $page) {
        $publish[] =  [
            'id' => $page->ID,
            'title' => $page->post_title,
        ];
    }
    $publishTitles = array_column($publish, 'title');

    // All drafts.
    $draft = [];
    foreach ($draftPosts as $page) {
        $draft[] = [
            'id' => $page->ID,
            'title' => $page->post_title,
            'post_date' => $page->post_date,
        ];
    }
    $draftTitles = array_column($draft, 'title');

    // Both all drafts & published.
    $all = array_merge($publish, $draft);
    $allTitles = array_column($all, 'title');

    // All duplicate titles.
    $duplicates = array_unique(array_diff_assoc($allTitles, array_unique($allTitles)));

    // Remove duplicates for publish status.
    foreach ($duplicates as $duplicate) {
        if (\in_array($duplicate, $publishTitles, true)) {
            foreach ($draft as $page) {
                if ($duplicate === $page['title']) {
                    \wp_update_post(
                        [
                            'ID' => $page['id'],
                            'post_status' => 'trash',
                        ]
                    );
                }
            }
        }
    }

    // Remove duplicates for draft status.
    foreach ($duplicates as $duplicate) {
        if (\in_array($duplicate, $draftTitles, true)) {
            $temp = [];
            foreach ($draft as $page) {
                if ($duplicate === $page['title']) {
                    $temp[] = $page;
                }
            }
            \usort($temp, static function (array $a, array $b): string {
                return \strtotime($a['post_date']) - \strtotime($b['post_date']);
            });
            \array_pop($temp);
            foreach ($temp as $tempPage) {
                \wp_update_post(
                    [
                        'ID' => $tempPage['id'],
                        'post_status' => 'trash',
                    ]
                );
            }
        }
    }
}
