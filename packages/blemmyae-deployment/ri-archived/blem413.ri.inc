<?php

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

const BLEM413_WP_CONTENT_PATH = ABSPATH . 'wp-content/uploads/2021';

const BLEM413_WP_CONTENT_MONTHS = ['06', '07'];

function blem413_update_import_to_database_001($attachment, $absolute_path)
{
    if (!stream_resolve_include_path($absolute_path)) {
        return array(
            'errorMessage' => sprintf('File to import not found at path: %s.', $absolute_path)
        );
    }

    // Insert the attachment.
    try {
        $attach_id = wp_insert_attachment($attachment, $absolute_path);
    } catch (Exception $e) {
        return array(
            'errorMessage' => sprintf('Error inserting attachment (`wp_posts` table) for file: %s.', $absolute_path),
            'error' => $e->getMessage()
        );
    }

    if (!($attach_id > 0)) {
        return array(
            // phpcs:ignore
            'errorMessage' => sprintf('Attach ID not received for inserted attachment (`wp_posts` table) for file: %s.', $absolute_path)
        );
    }

    try {
        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata($attach_id, $absolute_path);
    } catch (Exception $e) {
        return array(
            // phpcs:ignore
            'errorMessage' => sprintf('Error generating attachment metadata (`wp_postmeta` table) for file: %s.', $absolute_path),
            'error' => $e->getMessage()
        );
    }

    if (!$attach_data || empty($attach_data)) {
        return array(
            'errorMessage' => sprintf('Attachment metadata could not be generated from %s.', $absolute_path),
            'error' => json_encode($attachment)
        );
    }

    try {
        wp_update_attachment_metadata($attach_id, $attach_data);
    } catch (Exception $e) {
        return array(
            // phpcs:ignore
            'errorMessage' => sprintf('Error updating attachment metadata (`wp_postmeta` table) for file: %s.', $absolute_path),
            'error' => $e->getMessage()
        );
    }

    return true;
}

function blem413_get_mime_type_001($absolute_path)
{
    try {
        $mime_type = '';
        if (function_exists('mime_content_type')) {
            $mime_type = mime_content_type($absolute_path);
        }

        if (!empty($mime_type)) {
            return $mime_type;
        }

        $file_type = wp_check_filetype(basename($absolute_path), null);

        if (!$file_type['type']) {
            return array(
                'errorMessage' => sprintf('Invalid mime type for file: %s.', $absolute_path),
            );
        }

        return $file_type['type'];
    } catch (Exception $e) {
        return array(
            'errorMessage' => sprintf('Error getting mime type for file: %s.', $absolute_path),
            'error' => $e->getMessage()
        );
    }
}

function blem413_post_date_001($file_path, $type)
{
    // Default is empty string so that WordPress can set its default value (current date)
    $post_date = '';

    try {
        // Take "time modified" of file being imported
        $file_timestamp = filemtime($file_path);
        if ($file_timestamp) {
            // Convert to datetime
            $file_time = date('Y-m-d H:i:s', $file_timestamp);
            if ($file_time) {
                // For "File time" option - return file time
                if ($type === 'file_time') {
                    $post_date = $file_time;
                }

                // For "Smart file time" option - determine post date by comparing file's modified date
                // with folder in which it is
                if ($type === 'smart_file_time') {
                    // Use file time as a fallback
                    $post_date = $file_time;

                    // Compare the file date with the folder date structure
                    // Try to extract year and month from file path (e.g. "2020/02" or "2020\02")
                    // But this path is now always using forward slash (even on Windows Server), so we
                    // can again use just '/(\d{4})\/(\d{2})/'
                    preg_match('/(\d{4})[\/|\\\\](\d{2})/', $file_path, $matches);
                    if (!empty($matches)) {
                        $year_and_month = $matches[1] . '-' . $matches[2];
                        $folder_time = $year_and_month . '-' . '01' . ' 00:00:00';

                        // If what we found for folder date is a date (double check for regex above)
                        if (date('Y-m-d H:i:s', strtotime($folder_time)) == $folder_time) {
                            // If file's year and month values do not match with folder's year and month
                            if (date('Y-m', $file_timestamp) != $year_and_month) {
                                // Use the folder date instead
                                $post_date = $folder_time;
                            }
                        }
                    }
                }
            }
        }


        // Receive external function for setting custom post date
        $external = apply_filters('media_sync_filter_post_date', $post_date, $file_path, $type);

        // Return received custom post date if it's empty string or a date
        if (is_string($external) && ($external === '' || (date('Y-m-d H:i:s', strtotime($external)) == $external))) {
            $post_date = $external;
        }

        return $post_date;
    } catch (Exception $e) {
        return $post_date;
    }
}

function blem413_prepare_attachment_data_001($absolute_path, $relative_path, $post_date_type)
{
    try {
        $mime_type = blem413_get_mime_type_001($absolute_path);

        if (isset($mime_type['error']) || isset($mime_type['errorMessage'])) {
            return $mime_type;
        }

        // Prepare an array of post data for the attachment.
        $attachment = array(
            'guid' => get_site_url() . $relative_path,
            'post_mime_type' => $mime_type,
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($relative_path)),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        // Try to get post date based on settings - "Default" option will not set anything, so WP can use defaults
        $post_date = blem413_post_date_001($absolute_path, $post_date_type);

        if (!empty($post_date)) {
            $attachment['post_date'] = $post_date;
            $attachment['post_date_gmt'] = $post_date;
        }

        return $attachment;
    } catch (Exception $e) {
        return array(
            'errorMessage' => sprintf('Error preparing attachment data for file: %s.', $absolute_path),
            'error' => $e->getMessage()
        );
    }
}

function blem413_get_relative_path_001($absolute_path)
{
    // Since get_home_path() and WP in general always use forward slashes, we need to convert it as well
    $absolute_path = wp_normalize_path($absolute_path);

    // Always using forward slash ("/")
    return str_replace(get_home_path(), '/', $absolute_path);
}

function blem413_import_files_001($mediaList, &$missingItemsCount, $files_in_db)
{
    global $wpdb;

    $results = array();

    if ($mediaList) {
        $mediaListCount = $count = count($mediaList);

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once(ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'image.php');

        $post_date_type = 'default';

        foreach ($mediaList as $media_item) {
            $msgPrefix = '[' . $count . '/' . $mediaListCount . ']';

            if (isset($media_item['file']) && !empty($media_item['file'])) {
                // This comes from JS and it's taken from checkbox value, which is
                // $item['absolute_path'] from media_sync_get_list_of_files()
                $absolute_path = urldecode($media_item['file']);
                $relative_path = blem413_get_relative_path_001($absolute_path);

                // It's quicker to get all files already in db and check that array, than to do this query for each file
                // $query = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE guid LIKE '%{$relative_path}'";
                // $is_in_db = intval($wpdb->get_var($query)) > 0;

                $is_in_db = isset($files_in_db[$relative_path]) && !empty($files_in_db[$relative_path]);

                // Check if file is already in database
                if (!$is_in_db) {
                    Logger::log($msgPrefix . ' Item ' . $media_item['file'] . ' does not exist in db. Adding', 'info');
                    $missingItemsCount += 1;
                    // Prepare data to be saved to `wp_posts` and `wp_postmeta`
                    $attachment = blem413_prepare_attachment_data_001($absolute_path, $relative_path, $post_date_type);

                    if (!$attachment || isset($attachment['error']) || isset($attachment['errorMessage'])) {
                        continue;
                    }

                    // Import file to database (`wp_posts` and `wp_postmeta`)
                    $import_response = blem413_update_import_to_database_001($attachment, $absolute_path);
                    $is_inserted = $import_response === true;

                    if (!$is_inserted) {
                        Logger::log($msgPrefix . 'Error: ', 'notice');
                        print_r($import_response);
                    }

                    $count -= 1;
                    continue;
                }
                Logger::log($msgPrefix . ' Item ' . $media_item['file'] . ' exists', 'info');
                $count -= 1;
            }
        }
    }
}

function blem413_get_files_in_db_001()
{
    $media_query = new WP_Query(array(
        'post_type' => 'attachment',
        'post_status' => array('inherit', 'trash'),
        'posts_per_page' => -1
    ));

    $files = array();
    foreach ($media_query->posts as $post) {
        $file_url = '';
        if (function_exists('wp_get_original_image_url')) {
            // First try to find original image URL, to get proper URL without "-scaled" part for bigger images
            // https://make.wordpress.org/core/2019/10/09/introducing-handling-of-big-images-in-wordpress-5-3/
            $file_url = wp_get_original_image_url($post->ID);
        }

        // If not image, get attachment URL
        if (!$file_url || empty($file_url)) {
            $file_url = wp_get_attachment_url($post->ID);
        }

        // Should already have forward slashes since it's URL
        $relative_path = parse_url($file_url, PHP_URL_PATH);

        $files[$relative_path] = array(
            'id' => $post->ID,
            'name' => $post->post_title,
            'status' => $post->post_status
        );
    }

    return $files;
}

function blem413_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('blem413_blemmyae_deployment_ri_002', true);
}

function blem413_blemmyae_deployment_ri_002(): void
{
    $regex = '/(.+)-(\d+x\d+)\.(jpe?g|png|bmp)/';
    $extRegex = '/((?:\.jpg|\.png)\.webp|\.pdf|\.html)/m';

    $missingItemsCount = 0;

    Logger::log('Getting media items list from db', 'info');
    $dbItems = blem413_get_files_in_db_001();

    foreach (BLEM413_WP_CONTENT_MONTHS as $month) {
        $mediaList = array_diff(scandir(BLEM413_WP_CONTENT_PATH . '/' . $month), ['.', '..', '.DS_Store']);

        // phpcs:ignore
        $mediaList = array_filter($mediaList, static fn($fileName) => !preg_match($regex, $fileName) && !preg_match($extRegex, $fileName));

        $mediaList = array_map(static fn($fileName) => [
            'file' => ABSPATH . 'wp-content/uploads/2021/' . $month . '/' . $fileName,
            'row_id' => date("Ymd") . '_' . $fileName
        ], $mediaList);

        Logger::log('Processing ' . $month . ' month', 'info');
        blem413_import_files_001($mediaList, $missingItemsCount, $dbItems);
    }
}
