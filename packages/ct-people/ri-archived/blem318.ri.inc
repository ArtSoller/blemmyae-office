<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-318
 */

declare(strict_types=1);

use Scm\Tools\Logger;
use Scm\CoAuthors_Plus\Options as CAPOptions;

/**
 * @param string $url
 * @return string
 */
function forceRelativeUrl(string $url): string
{
    return preg_replace('/^(http)?s?:?\/\/[^\/]*(\/?.*)$/i', '$2', '' . $url);
}

/**
 * @param WP_POST $guestAuthor
 * @param CAPOptions $capOptions
 * @return mixed
 */
function blem318_upsert_person(\WP_POST $guestAuthor, CAPOptions $capOptions)
{
    $page = get_page_by_title($guestAuthor->post_title, OBJECT, 'people');
    $pageId = null;

    if ($page instanceof \WP_POST) {
        $pageId = $page->ID;
    }

    if (is_array($page)) {
        return null;
    }

    if (is_null($page)) {
        $postData = [
            'post_title' => $guestAuthor->post_title,
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 0,
            'post_type' => 'people',
        ];
        $pageId = wp_insert_post($postData);

        if ($pageId instanceof \WP_Error) {
            return null;
        }
    }
    $capData = [];
    $metaKeys = $capOptions->guestAuthorFields();
    foreach ($metaKeys as $metaKey) {
        $capData[$metaKey['key']] = get_post_meta($guestAuthor->ID, $capOptions->postMetaKey($metaKey['key']), true);
    }
    $fieldsMap = [
        'ID' => '',
        'display_name' => '',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'user_login' => '',
        'user_email' => 'email',
        'linked_account' => '',
        'website' => '',
        'aim' => '',
        'yahooim' => '',
        'jabber' => '',
        'description' => 'bio',
    ];

    foreach ($capData as $key => $value) {
        if ($fieldsMap[$key]) {
            update_field($fieldsMap[$key], $value, $pageId);
        }
    }

    $termType = get_term_by('name', 'Author', 'people_type'); // 'Industry Figure'
    if ($termType) {
        update_field('field_607583f54f5f6', $termType, $pageId);
    }

    $posts = get_field('post', $guestAuthor->ID ?: []);
    foreach ($posts as $key => $post) {
        if (in_array($post->post_type, ['guest-author', 'advanced_ads'])) {
            unset($posts[$key]);
        }
    }
    update_field('post', $posts, $pageId);
    do_action('acf/save_post', $pageId);

    return $pageId;
}

/**
 * Import authors from guest authors.
 */
function blem318_ct_people_ri_001(): void
{
    // Require config update before calling this code.
    ct_people_ri_006();

    $posts = get_posts(
        [
            'post_type' => 'guest-author',
            'numberposts' => -1,
        ]
    );

    $capOptions = new Scm\CoAuthors_Plus\Options();

    $countAll = $count = count($posts);
    foreach ($posts as $post) {
        $count--;
        $msgPrefix = '[' . $countAll . '/' . $count . '] ';
        $newUri = '/';
        $capId = $post->ID;
        $field = get_field('post', $capId ?: []);
        if (!$field) {
            Logger::log("$msgPrefix`$post->post_title` ($post->ID) was skipped", 'info');
        } else {
            $personId = (int)blem318_upsert_person($post, $capOptions);
            $newUri = forceRelativeUrl(get_permalink($personId));
            Logger::log("$msgPrefix`$post->post_title` ($capId) was duplicated as person ($personId)", 'success');
        }
        $oldUri = forceRelativeUrl(get_permalink($capId));

        Red_Item::create(
            [
                'url' => $oldUri,
                'action_data' => ['url' => $newUri],
                'regex' => false,
                'group_id' => 1,
                'match_type' => 'url',
                'action_type' => 'url',
                'action_code' => 301,
            ]
        );
        Logger::log("$msgPrefix`$oldUri => $newUri redirect was created", 'success');
        wp_delete_post($capId, true);
        Logger::log("$msgPrefix`$post->post_title` ($capId) was removed", 'info');
    }

    wp_cache_flush();
}
