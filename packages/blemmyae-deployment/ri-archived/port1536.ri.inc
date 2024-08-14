<?php

declare(strict_types=1);

use Scm\Entity\MergePosts;
use Scm\Tools\Logger;

/**
 * Merge person data.
 *
 * @return void
 */
function port1536_blemmyae_deployment_ri_003(): void
{
    // Get all persons.
    $allPersons = get_posts([
        'post_type' => 'people',
        'posts_per_page' => -1,
        'post_status' => ['publish'],
    ]);

    $allPersonsData = [];
    $duplicates = [];

    array_map(function ($post) use (&$allPersonsData) {
        if ($post->post_title) {
            $allPersonsData[$post->ID] = $post->post_title;
        }
    }, $allPersons);

    // Get duplicates by post title.
    $getUnique = array_unique($allPersonsData);
    $duplicateValues = array_diff_assoc($allPersonsData, $getUnique);

    // Prepare array of duplicated ids.
    foreach ($duplicateValues as $title) {
        $duplicateIds = array_keys($allPersonsData, $title);
        if ($title === "" || in_array($duplicateIds, $duplicates)) {
            continue;
        }
        $duplicates[] = $duplicateIds;
    }

    // Migrate duplicate person data.
    foreach ($duplicates as $duplicatePersons) {
        sort($duplicatePersons);

        $size = sizeof($duplicatePersons);
        do {
            $mergePosts = new MergePosts($duplicatePersons[$size - 2], $duplicatePersons[$size - 1]);
            $mergePosts->migrateDuplicateAuthorData();
            $mergePosts->mergeDuplicateByIds();
            unset($duplicatePersons[$size - 1]);
            $size--;
        } while ($size > 1);

        Logger::log("-----------------------------------", '');
    }
}
