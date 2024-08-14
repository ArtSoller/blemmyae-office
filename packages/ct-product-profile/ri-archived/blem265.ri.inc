<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-265
 */

declare(strict_types=1);

use Scm\Tools\Logger;

/**
 * @param string $title
 * @param string $uri
 * @return mixed
 */
function blem265_upsert_company(string $title, string $uri)
{
    $page = get_page_by_title($title, OBJECT, 'company_profile');
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
            'post_type' => 'company_profile',
        ];
        $pageId = wp_insert_post($postData);

        if ($pageId instanceof \WP_Error) {
            return null;
        }

        // Link
        update_field('website_url', [
            'url' => $uri,
            'title' => 'URL',
            'target' => '_blank',
        ], $pageId);

        // Default type
        $termType = get_term_by('name', 'Vendor', 'company_profile_type');
        if ($termType) {
            update_field('type', $termType, $pageId);
        }
    }

    return $pageId;
}

/**
 * Creating|updating products from Directory.
 */
function blem265_ct_product_profile_ri_002(): void
{
    $xlsx = \SimpleXLSX::parseFile(__DIR__ . '/blem265/Cybersecurity_Directory_Project.xlsx');
    if (!$xlsx) {
        Logger::log(\SimpleXLSX::parseError(), 'error');
    }
    $postsData = $xlsx->rows(3);
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
    $unsupportedCategories = [];
    foreach ($postsData as $postData) {
        $count--;
        $msgPrefix = '[' . $countAll . '/' . $count . '] ';
        $page = get_page_by_title($postData['product_service_name'], OBJECT, 'product_profile');
        if (is_array($page)) {
            Logger::log(
                $msgPrefix . ': Skipping - Several products with a name: ' . $postData['product_service_name'],
                'notice'
            );
            continue;
        }
        $pageId = null;
        if ($page instanceof \WP_POST) {
            $pageId = $page->ID;
            Logger::log(
                $msgPrefix . ': Updating - Found product with a name: ' . $postData['product_service_name'],
                'info'
            );
        }
        if (is_null($page)) {
            $newPostData = [
                'post_title' => $postData['product_service_name'],
                'post_content' => '',
                'post_status' => 'publish',
                'post_author' => 0,
                'post_type' => 'product_profile',
            ];
            $pageId = wp_insert_post($newPostData);

            if ($pageId instanceof \WP_Error) {
                Logger::log(
                    $msgPrefix . ': Skipping - Unable to create product with a name: ' . $postData['product_service_name'],
                    'notice'
                );
                continue;
            }
            Logger::log(
                $msgPrefix . ': Creating - New product with a name: ' . $postData['product_service_name'],
                'info'
            );
        }
        $simpleMap = [
            // Text
            'product_name' => 'product_service_name',
            'licensing_model' => '',
            'platform_support' => '',
            'mgmt_architecture' => '',
            'pricing' => '',
            'percent_of_revenue' => '',
            'product_growth' => '',
            'customer_count' => '',
            'unit_count' => '',
            // Date Picker
            'general_availability_date' => '',
            // Wysiwyg Editor
            'product_description' => 'product_service_description',
            // Text Area
            'gtm_strategy' => '',
        ];
        foreach ($simpleMap as $field => $key) {
            if ($key) {
                update_field($field, $postData[$key], $pageId);
            }
        }
        $categories = [
            'primary_category',
            'secondary_category',
            'tertiary_category',
        ];

        $field = get_field('categories', $pageId) ?: [];
        foreach ($categories as $category) {
            if ($postData[$category]) {
                $termTopic = get_term_by('name', $postData[$category], 'topic');
                if ($termTopic) {
                    $field[] = $termTopic;
                } else {
                    $unsupportedCategories[] = $postData[$category];
                    Logger::log(
                        $msgPrefix . ': Unsupported topic - ' . $postData[$category],
                        'notice'
                    );
                }
            }
        }
        update_field('categories', array_unique(array_column($field, 'term_id')), $pageId);
        // Relationship
        $company = blem265_upsert_company(
            $postData['company_name'],
            $postData['url']
        );
        if ($company) {
            update_field('company_name', [$company], $pageId);
        }
        // Image
        //logo
    }

    Logger::log(
        'Unsupported topics: ' . implode(',', array_unique($unsupportedCategories)),
        'notice'
    );

    wp_cache_flush();
}
