<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-265
 */

declare(strict_types=1);

use Scm\Tools\Logger;

/**
 * @param string $title
 * @param int $company
 * @param string $role
 * @param string $linkedin
 * @return mixed
 */
function blem265_upsert_person(string $title, int $company, string $role, string $linkedin = '')
{
    $page = get_page_by_title($title, OBJECT, 'people');
    $pageId = null;

    if ($page instanceof \WP_POST) {
        $pageId = $page->ID;
    }

    if (is_array($page)) {
        return null;
    }

    if (is_null($page)) {
        $postData = [
            'post_title' => $title,
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

    $name = array_filter(explode(' ', $title));
    if ($name) {
        if (count($name) > 2) {
            update_field('first_name', $name[0], $pageId);
            update_field('middle_name', $name[1], $pageId);
            update_field('last_name', $name[2], $pageId);
        } else {
            update_field('first_name', $name[0], $pageId);
            update_field('last_name', $name[1], $pageId);
        }
    }

    if ($linkedin) {
        update_field('linkedin', $linkedin, $pageId);
    }

    update_row('companies', 1, [
        'company' => [$company],
        'job_title' => $role,
    ], $pageId);

    $termType = get_term_by('name', 'Industry Figure', 'people_type'); // 'Industry Figure'
    if ($termType) {
        update_field('field_607583f54f5f6', $termType, $pageId);
    }
    do_action('acf/save_post', $pageId);

    return $pageId;
}

/**
 * Creating|updating companies from Directory.
 */
function blem265_ct_company_profile_ri_003(): void
{
    $xlsx = \SimpleXLSX::parseFile(__DIR__ . '/blem265/Cybersecurity_Directory_Project.xlsx');
    if (!$xlsx) {
        Logger::log(\SimpleXLSX::parseError(), 'error');
    }
    $postsData = $xlsx->rows(1);
    unset($xlsx);

    // Define field keys.
    $keys = array_filter(array_shift($postsData));
    foreach ($keys as &$key) {
        $temp = str_replace([',', '"', '?', ' ', '/'], ['', '', '', '_', '_'], $key);
        $key = strtolower(ltrim($temp, '_'));
    }
    unset($key);
    $keysAmount = count($keys);

    // Normalize fields.
    array_walk($postsData, static function (&$value) use ($keys, $keysAmount) {
        $temp = array_slice($value, 0, $keysAmount);
        $value = array_combine($keys, $temp);
    });

    $countAll = $count = count($postsData);
    foreach ($postsData as $postData) {
        $count--;
        $msgPrefix = '[' . $countAll . '/' . $count . '] ';
        $page = get_page_by_title($postData['company_name'], OBJECT, 'company_profile');
        if (is_array($page)) {
            Logger::log(
                $msgPrefix . ': Skipping - Several companies with a name: ' . $postData['company_name'],
                'notice'
            );
            continue;
        }
        $pageId = null;
        if ($page instanceof \WP_POST) {
            $pageId = $page->ID;
            Logger::log(
                $msgPrefix . ': Updating - Found company with a name: ' . $postData['company_name'],
                'info'
            );
        }
        if (is_null($page)) {
            $newPostData = [
                'post_title' => $postData['company_name'],
                'post_content' => '',
                'post_status' => 'publish',
                'post_author' => 0,
                'post_type' => 'company_profile',
            ];
            $pageId = wp_insert_post($newPostData);

            if ($pageId instanceof \WP_Error) {
                Logger::log(
                    $msgPrefix . ': Skipping - Unable to create company with a name: ' . $postData['company_name'],
                    'notice'
                );
                continue;
            }
            Logger::log(
                $msgPrefix . ': Creating - New company with a name: ' . $postData['company_name'],
                'info'
            );
        }
        $simpleMap = [
            // Text
            'company_name' => 'company_name',
            'legal_name' => 'legal_name',
            'reason_closed' => '',
            'pure_play' => '',
            'public_market' => '',
            'subsidiary_status' => 'company_status',
            'public_symbol' => 'stock_symbol',
            'funding_round' => 'funding_round',
            'employee_count' => 'number_of_employees',
            'total_funding' => 'total_funding',
            'revenue' => 'revenue',
            'revenue_growth' => '',
            // Date Picker
            'date_founded' => 'founded_date',
            'acquired_date' => 'date_acquired',
            'public_filing_date' => '',
            // Wysiwyg Editor
            'about' => 'about',
        ];
        foreach ($simpleMap as $field => $key) {
            if ($key) {
                update_field($field, $postData[$key], $pageId);
            }
        }
        // Taxonomy Terms
        $termState = get_term_by('name', $postData['hq_state'], 'region');
        if ($termState) {
            update_field('state', [$termState->term_id], $pageId);
        }
        $termType = get_term_by('name', 'Vendor', 'company_profile_type');
        if ($termType) {
            update_field('type', $termType, $pageId);
        }
        // Relationship
        //parent_company
        //child_companies
        //products
        $personReferenceMap = [
            'ceo' => [
                'title' => 'ceo_name',
                'linkedin' => 'ceo_linkedin',
            ],
            'cmo' => [
                'title' => 'cmo_vp_marketing_name',
                'linkedin' => 'cmo_vp_marketing_linkedin',
            ],
        ];
        foreach ($personReferenceMap as $field => $map) {
            $person = blem265_upsert_person(
                $postData[$map['title']],
                (int)$pageId,
                strtoupper($field),
                $postData[$map['linkedin']]
            );
            if ($person) {
                update_field($field, [$person], $pageId);
            }
        }
        $location = $postData['hq_country'] . ', ' . $postData['hq_state'] . ', ' . $postData['hq_city'];
        // Text
        update_field('headquarters', $location, $pageId);
        // Google Map
        update_field('incorporated_location', ['address' => $location], $pageId);
        // Link
        update_field('website_url', [
            'url' => $postData['url'],
            'title' => 'URL',
            'target' => '_blank',
        ], $pageId);
        // Image
        //logo
    }

    wp_cache_flush();
}
