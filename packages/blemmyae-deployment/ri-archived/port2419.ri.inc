<?php

/**
 * Release instruction to update learning type in collection widget queries.
 *
 * @author  Anastasia Lukyanova <stacylkv@gmail.com>
 * @license proprietary
 */

declare(strict_types=1);

use Cra\CtLearning\LearningCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

/**
 * @return void
 */
function port2419_migration_ri_001(): void
{
    (new RI())->setStatus('port2419_migration_ri_002', true);
}

/**
 * @return void
 */
function port2419_migration_ri_002(): void
{
    $currentType = get_term_by('slug', 'esummit', LearningCT::TAXONOMY__LEARNING_TYPE)->term_id;
    $newType = get_term_by('slug', 'virtual-conference', LearningCT::TAXONOMY__LEARNING_TYPE)->term_id;

    if ($currentType && $newType) {
        port2419_update_collection_widget_queries($currentType, $newType);
    } else {
        Logger::log("Invalid Learning types provided.", 'error');
    }
}

/**
 * Update collection widget queries.
 * @param int $currentType
 * @param int $newType
 *
 * @return void
 */
function port2419_update_collection_widget_queries(
    int $currentType,
    int $newType
): void {
    // Fix memory leak.
    wp_suspend_cache_addition(true);

    $posts = get_posts([
        'posts_per_page' => -1,
        'post_type' => 'landing',
    ]);

    $itemsCount = count($posts);
    $count = 1;

    foreach ($posts as $post) {
        $msgPrefix = '[' . $count . '/' . $itemsCount . ']';
        Logger::log("$msgPrefix: $post->ID updating...", 'status');

        $postMeta = get_post_meta($post->ID);

        foreach ($postMeta as $metaKey => $metaValue) {
            $value = array_shift($metaValue);
            $unserializedValue = is_serialized($value) ? unserialize($value) : false;
            if ($unserializedValue && in_array($currentType, $unserializedValue)) {
                unset($unserializedValue[array_search($currentType, $unserializedValue)]);
                $unserializedValue = array_values($unserializedValue);

                if (!in_array($newType, $unserializedValue)) {
                    $unserializedValue[] = (string)$newType;
                }

                update_field($metaKey, $unserializedValue, $post->ID);
            }
            do_action('acf/save_post', $post->ID);
        }

        Logger::log("$msgPrefix: $post->ID updated.", 'success');
        $count++;
    }

    wp_suspend_cache_addition(false);
}
