<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-326
 */

use Scm\Tools\Logger;
use Scm\Tools\Utils;

const BLEM326_FIELD_PERSON_TYPE = 'field_607583f54f5f6';
const BLEM326_SC_STAFF_WP_USER_ID = 2;
const BLEM326_COUNT_OPTION = 'BLEM_326_last_count_value';

/**
 * @param string $guestAuthor
 * @return mixed
 */
function blem326_upsert_person(string $guestAuthor)
{
    /** @phpstan-ignore-next-line */
    $page = get_page_by_title($guestAuthor, OBJECT, 'people');
    $pageId = null;

    if ($page instanceof \WP_POST) {
        $pageId = $page->ID;
    }

    if (is_array($page)) {
        return null;
    }

    if (is_null($page)) {
        $postData = [
            'post_title' => $guestAuthor,
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

    $termType = get_term_by('name', 'Author', 'people_type');
    if ($termType) {
        update_field(BLEM326_FIELD_PERSON_TYPE, $termType, $pageId);
    }

    do_action('acf/save_post', $pageId);

    return $pageId;
}

/**
 * Import legacy authors.
 */
function blem326_blemmyae_deployment_ri_001(): void
{
    set_time_limit(0);

    $peopleListPath = wp_get_upload_dir()['basedir'] . '/blem326.personsList.csv';

    file_put_contents($peopleListPath, 'personDisplayName,personPageLink' . PHP_EOL);

    $authorsPersonsMap = [
        'Alexander Kucherov' => 'SC Staff',
        'Andrea Timbol' => 'SC Staff',
        'bradleybarth' => 'Bradley Barth',
        'brianbyrne' => 'Brian Byrne',
        'cmcnasby' => 'SC Staff',
        'dashasolovieva' => 'SC Staff',
        'Haddock' => 'SC Staff',
        'ianclarkson' => 'Ian Clarkson',
        'jbooras' => 'SC Staff',
        'katiebottrell' => 'Katie Bottrell',
        'kgusev' => 'SC Staff',
        'Mikel King' => 'SC Staff',
        'okirilyuk' => 'SC Staff',
        'paulafox' => 'Paula Fox',
        'richardscalise' => 'Richard Scalise',
        'SC Magazine' => 'SC Staff',
        'vsazonov' => 'SC Staff',
        'warunaperera' => 'Waruna Perera',
        'waseemqureshi' => 'Waseem Qureshi',
        'wpengine' => 'SC Staff',
    ];

    $postWpUsers = @array_map(
        static fn(array $row) => [
            'id' => $row[0],
            'author' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/blem326/wp_posts_wp_users.csv', ',')
    );
    $postWpIds = array_column($postWpUsers, 'author', 'id');

    $postAuthors = @array_map(
        static fn(array $row) => [
            'id' => $row[0],
            'author' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/blem326/wp_posts.csv', ',')
    );
    $postAuthorIds = array_column($postAuthors, 'author', 'id');

    $termsGuestAuthors = @array_map(
        static fn(array $row) => [
            'author_id' => $row[0],
            'author_name' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/blem326/author_id_author_name.csv', ',')
    );
    $termsGuestAuthorNames = array_column($termsGuestAuthors, 'author_name', 'author_id');

    $postGuestAuthors = @array_map(
        static fn(array $row) => [
            'id' => $row[0],
            'author' => $termsGuestAuthorNames[$row[1]],
        ],
        Utils::importCsv(__DIR__ . '/blem326/post_id_author_id.csv', ',')
    );
    $postGuestAuthorIds = array_column($postGuestAuthors, 'author', 'id');

    $multiplePeople = @array_map(
        static fn(array $row) => [
            'id' => $row[0],
            'author' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/blem326/many_authors.csv', ',')
    );
    $multiplePeopleByNames = array_column($multiplePeople, 'id', 'author');

    $persons = [];

    global $wpdb;
    $row = $wpdb->get_results("SELECT import_id, post_id, unique_key FROM wp_pmxi_posts");
    $countAll = $count = count($row);
    $scStaffContentCount = $mappedScStaffCount = $nonMappedScStaffCount = $fallbackScStaffCount = 0;
    if (!get_option(BLEM326_COUNT_OPTION)) {
        add_option(BLEM326_COUNT_OPTION, $count);
    }

    foreach ($row as $item) {
        $countOption = get_option(BLEM326_COUNT_OPTION);
        if ($countOption && $countOption < $count) {
            $count--;
            Logger::log('Already processed', 'skip');
            continue;
        }

        $count--;
        $msgPrefix = '[' . $countAll . '/' . $count . '] ';

        if (!in_array($item->import_id, [2, 3, 4, 5, 6, 9, 10, 11], false)) {
            Logger::log('Skipping', 'skip');
            continue;
        }

        if ($item->import_id == 9) {
            // E.g. "digital safety - coming now - 1234" => 1234
            $item->unique_key = trim(array_values(array_slice(explode('-', $item->unique_key), -1))[0] ?? null);
            if (!is_numeric($item->unique_key)) {
                Logger::log('Not mappable due to lack of id in a mixed key' . $item->unique_key, 'skip');
                continue;
            }
            Logger::log('Mixed unique key' . $item->unique_key, 'notice');
        }

        if (in_array($item->unique_key, array_keys($postWpIds))) {
            Logger::log(
                $msgPrefix . 'ImportID:' . $item->import_id . ' ' . $item->unique_key . ' ~> ' . $item->post_id,
                'info'
            );

            $tempName = $postGuestAuthorIds[$item->unique_key] ?? $postAuthorIds[$item->unique_key];
            $authorName = $authorsPersonsMap[$tempName] ?? $tempName;
            if ($authorsPersonsMap[$tempName] == 'SC Staff') {
                $mappedScStaffCount += 1;
            } elseif ($authorName == 'SC Staff') {
                $nonMappedScStaffCount += 1;
            }
            if (!$authorName) {
                Logger::log(
                    $msgPrefix . 'Unable to identify author name',
                    'skip'
                );
                $fallbackScStaffCount += 1;
                update_option(BLEM326_COUNT_OPTION, $count);
                continue;
            }

            if ($authorName == 'SC Staff') {
                $scStaffContentCount += 1;
            }

            if (!$multiplePeopleByNames[$authorName]) {
                $authorName = explode(',', $authorName, 2)[0] ?? $authorName;
                if (explode(',', $authorName, 2)[1]) {
                    Logger::log('Name exploded, author name set to ' . $authorName, 'info');
                }
            }

            $personId = (int)blem326_upsert_person($authorName);
            if (empty($persons[$authorName])) {
                $persons[$authorName] = get_post($personId);
                // phpcs:ignore
                file_put_contents($peopleListPath, "\"$authorName\",\"https://cms-dev.scmagazine.com/wp-admin/post.php?post=$personId&action=edit\"" . PHP_EOL, FILE_APPEND);
            }

            $field = get_field('author', $item->post_id ?: []);
            if (is_countable($field)) {
                // phpcs:ignore
                // Faster than `wp_insert_post(['ID' => $item->post_id, 'post_author' => $postWpIds[$item->unique_key]]);`
                $wpdb->update(
                    'wp_posts',
                    // phpcs:ignore
                    ['post_author' => $postAuthorIds[$item->unique_key] ? $postWpIds[$item->unique_key] : BLEM326_SC_STAFF_WP_USER_ID],
                    ['ID' => $item->post_id],
                    ['%d'],
                    ['%d']
                );
                update_post_meta($item->post_id, '_edit_last', $postWpIds[$item->unique_key]);
                Logger::log(
                    $msgPrefix . 'Set new wp user `' . $postWpIds[$item->unique_key] . '`',
                    'skip'
                );

                if (count($field) > 1) {
                    Logger::log(
                        $msgPrefix . 'Has more than one author',
                        'skip'
                    );
                    update_option(BLEM326_COUNT_OPTION, $count);
                    continue;
                }
                if ($field[0]->post_title === $authorName) {
                    Logger::log(
                        $msgPrefix . ' - ' . $authorName . ' - Nothing to update',
                        'skip'
                    );
                    update_option(BLEM326_COUNT_OPTION, $count);
                    continue;
                }
            }

            update_field('author', [$persons[$authorName]], $item->post_id);
            do_action('acf/save_post', $item->post_id);

            Logger::log(
                $msgPrefix . 'Set new author `' . $authorName . '`',
                'skip'
            );

            update_option(BLEM326_COUNT_OPTION, $count);
            continue;
        }

        Logger::log($msgPrefix . 'Not mappable', 'skip');
    }

    Logger::log('Mapped SC Staff posts count - ' . $mappedScStaffCount, 'info');
    Logger::log('Non-mapped SC Staff posts count - ' . $nonMappedScStaffCount, 'info');
    Logger::log('SC Staff posts count - ' . $scStaffContentCount, 'info');

    delete_option(BLEM326_COUNT_OPTION);
    wp_cache_flush();
}
