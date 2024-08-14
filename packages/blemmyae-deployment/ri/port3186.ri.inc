<?php

/** @noinspection PhpUnused */

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-3186
 */

declare(strict_types=1);

use Cra\CtEditorial\EditorialCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

function port3186_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port3186_blemmyae_deployment_ri_002', true);
    (new RI())->setStatus('port3186_blemmyae_deployment_ri_003', true);
}

/**
 * Re-imports missing collectionAuthor fields.
 *
 * @return void
 */
function port3186_blemmyae_deployment_ri_002(): void
{
    $postMeta = array_map(
        static fn(array $row) => [
            'post_id' => $row[0],
            'author_id' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/port3186/wp_postmeta_authorCollection.csv', ',')
    );

    foreach ($postMeta as $item) {
        $authorCollectionField = get_field(EditorialCT::GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR, $item['post_id'], false);

        if ($authorCollectionField) {
            Logger::log('Author is already populated. Skipping...', 'info');
        } else {
            update_field(EditorialCT::GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR, [ $item['author_id'] ], $item['post_id']);

            Logger::log('Author field for post: ' . $item['post_id'] . ' updated', 'success');
        }
    }

    wp_cache_flush();
}

/**
 * Clears the transient cache associated with a person.
 *
 * @return void
 */
function port3186_blemmyae_deployment_ri_003(): void
{
    global $wpdb;

    $wpdb->query(
        "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE '_transient_person_%' OR 
        `option_name` LIKE '_transient_timeout_person_%'"
    );

    wp_cache_flush();
}
