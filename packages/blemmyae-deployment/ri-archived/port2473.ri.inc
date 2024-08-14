<?php

/**
 * Release instruction to update duplicated slug for post with different apps.
 *
 * @author  Anastasia Lukyanova <stacylkv@gmail.com>
 * @license proprietary
 */

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\CtEditorial\EditorialCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

const PORT2473_MSSP_SLUG_LIST = [
    'mobile-device-security',
    'sen-wyden-blames-microsofts-negligent-cybersecurity-practices-for-china-hack',
];

const PORT2473_CE2E_SLUG_LIST = [
    'updates-13-august-2021',
    'updates-08-april-2021',
    'updates-15-may-2020',
    'updates-06-april-2021',
    'updates-25-july-2022',
    'updates-28-april-2020',
    'updates-23-june-2021',
    'updates-15-june-2021',
    'updates-17-march-2021',
    'updates-25-august-2021',
    'updates-10-june-2020',
    'updates-16-jan-2020',
    'updates-09-july-2020',
    'updates-16-july-2021',
    'updates-18-march-2020',
    'updates-29-jan-2020',
    'updates-29-april-2020',
    'updates-06-may-2021',
    'updates-05-april-2021',
    'updates-02-february-2022',
    'updates-22-december-2021',
    'updates-16-november-2021',
    'updates-28-september-2020',
    'updates-10-november-2021',
    'updates-22-july-2021',
    'updates-27-feb-2020',
    'updates-18-august-2021',
    'updates-09-march-2021',
    'updates-22-december-2020',
    'updates-22-september-2021',
    'updates-08-february-2022',
    'updates-30-april-2020',
    'updates-15-september-2021',
    'updates-04-september-2020',
    'updates-02-november-2021',
    'updates-15-october-2021',
    'updates-22-june-2021',
    'updates-03-august-2021',
    'updates-20-july-2021',
    'updates-21-april-2021',
    'updates-07-may-2021',
    'updates-21-december-2020',
    'updates-10-jan-2020',
    'updates-05-november-2021',
    'updates-21-september-2021',
    'updates-21-april-2020',
    'updates-12-august-2020',
    'updates-28-jan-2020',
    'updates-30-october-2020',
    'updates-11-may-2021',
    'updates-16-june-2021',
    'updates-03-february-2021',
    'updates-30-june-2020',
    'updates-16-december-2020',
    'updates-27-january-2021',
    'updates-28-july-2022',
    'updates-18-may-2020',
    'updates-17-august-2020',
    'updates-10-november-2021',
    'updates-05-october-2021',
    'updates-30-april-2020',
    'updates-11-january-2021',
    'updates-07-april-2020',
    'updates-06-april-2021',
    'updates-10-november-2020',
    'updates-21-may-2021',
    'updates-20-january-2021',
    'updates-21-october-2021',
    'updates-07-april-2022',
    'updates-21-october-2021',
    'updates-17-may-2022',
    'updates-03-august-2021',
    'updates-02-november-2021',
    'updates-16-september-2020',
    'updates-19-feb-2020',
    'updates-17-march-2020',
    'updates-24-march-2021',
    'updates-21-june-2021',
    'updates-18-may-2022',
    'updates-24-march-2020',
    'updates-04-november-2020',
    'updates-06-january-2021',
    'updates-01-march-2021',
    'updates-13-april-2021',
    'most-read-07-january-2022',
    'updates-09-march-2020',
    'updates-12-january-2022',
    'updates-14-jan-2020',
    'updates-07-august-2020',
    'updates-28-october-2021',
    'updates-20-july-2020',
    'updates-09-february-2021',
    'updates-10-august-2020',
    'updates-27-september-2021',
    'updates-30-august-2021',
    'updates-21-july-2022',
    'updates-12-may-2020',
    'updates-15-november-2021',
    'updates-09-march-2021',
    'updates-14-january-2021',
    'updates-17-jan-2020',
    'updates-28-may-2021',
    'updates-04-april-2022',
    'updates-17-august-2020',
    'updates-10-feb-2020',
    'updates-10-march-2020',
    'updates-23-december-2020',
    'updates-21-june-2021',
    'updates-11-january-2022',
    'updates-08-september-2020',
    'updates-30-november-2020',
    'updates-11-dec-2019',
    'updates-20-april-2021',
    'updates-14-june-2021',
    'updates-06-march-2020',
    'updates-01-december-2020',
    'updates-26-february-2021',
    'updates-22-october-2020',
    'updates-04-feb-2020',
    'updates-31-october-2022',
    'updates-02-june-2021',
    'updates-14-june-2021',
    'updates-30-august-2021',
    'updates-10-august-2021',
    'updates-23-february-2022',
    '5-channel-partner-updates-friday-11-may-2018',
    'educational-programs-to-enhance-diversity-in-the-cybersecurity-profession',
    'connectwise-on-the-sales-block',
    'mobile-device-security',
    'sen-wyden-blames-microsofts-negligent-cybersecurity-practices-for-china-hack',
    'updates-11-june-2020',
    'updates-03-march-2021',
    'updates-26-october-2021',
    'updates-27-april-2020',
    'updates-03-august-2020',
    'updates-18-march-2022',
    'updates-04-october-2021',
    'updates-06-jan-2020',
    'updates-07-jan-2020',
    'updates-01-november-2021',
    'updates-13-july-2021',
    'updates-25-march-2021',
    'updates-11-september-2020',
    'updates-25-feb-2020',
    'updates-25-january-2021',
    'updates-22-february-2021',
    'updates-15-jan-2020',
    'updates-17-august-2021',
    'updates-08-august-2022',
    'updates-07-december-2021',
    'updates-12-july-2021',
    'updates-05-may-2021',
    'updates-06-october-2021',
    'updates-13-july-2020',
    'updates-23-december-2021',
    'updates-09-april-2021',
    'updates-23-february-2021',
    'updates-29-september-2020',
    'updates-23-september-2021',
    'updates-03-june-2021',
];

/**
 * @return void
 */
function port2473_migration_ri_001(): void
{
    (new RI())->setStatus('port2473_migration_ri_003', true);
    (new RI())->setStatus('port2473_migration_ri_004', true);
}

/**
 * Update MSSP Alert Editorial entity.
 *
 * @throws Exception
 */
function port2473_migration_ri_003(): void
{
    port2473_update_posts(
        BlemmyaeApplications::MSSP,
        PORT2473_MSSP_SLUG_LIST
    );
}

/**
 * Update Channel E2E Editorial entity.
 *
 * @throws Exception
 */
function port2473_migration_ri_004(): void
{
    port2473_update_posts(
        BlemmyaeApplications::CE2E,
        PORT2473_CE2E_SLUG_LIST
    );
}

/**
 * Update Posts entity.
 *
 * @param array $slugList
 */
function port2473_update_posts(
    string $app,
    array $slugList,
): void {
    // Fix memory leak.
    wp_suspend_cache_addition(true);

    $itemsCount = count($slugList);
    $count = 1;
    foreach ($slugList as $slug) {
        global $wpdb;

        $msgPrefix = '[' . $count . '/' . $itemsCount . ']';
        Logger::log("$msgPrefix: $slug is updating...", 'status');

        $postIds = array_column($wpdb->get_results(
            "SELECT * FROM `wp_posts` WHERE CONVERT(`post_name` USING utf8) REGEXP '$slug'"
        ), 'ID');

        foreach ($postIds as $id) {
            Logger::log("Post $id is updating...", 'status');
            $post = get_post($id);
            $regex = '/^' . $slug . '-(2|3)$/';
            $postApp = get_field(EditorialCT::GROUP_APPLICATION__FIELD_APPLICATION, $id)->slug;

            if (preg_match($regex, $post->post_name) && ($app === $postApp)) {
                update_field(EditorialCT::GROUP_APPLICATION__FIELD_SLUG, $slug, $id);
            }
        }

        $count++;
    }

    wp_suspend_cache_addition(false);
}
