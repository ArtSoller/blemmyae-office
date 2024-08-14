<?php

/**
 * Release instruction to update Topic and Type for Editorial entity.
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
function port2417_migration_ri_001(): void
{
    (new RI())->setStatus('port2417_migration_ri_002', true);
}

function port2417_migration_ri_002(): void
{
    $currentType = get_term_by('slug', 'esummit', LearningCT::TAXONOMY__LEARNING_TYPE)->term_id;
    $newType = get_term_by('slug', 'virtual-conference', LearningCT::TAXONOMY__LEARNING_TYPE)->term_id;

    if ($currentType && $newType) {
        port2417_update_learning_type($currentType, $newType);
    } else {
        Logger::log("Invalid content types provided.", 'error');
    }
}

/**
 * Update Learning Type.
 * @param int $currentType
 * @param int $newType
 */
function port2417_update_learning_type(
    int $currentType,
    int $newType
): void {
    // Fix memory leak.
    wp_suspend_cache_addition(true);

    $posts = get_posts([
        'posts_per_page'    => -1,
        'post_type'     => 'learning',
        'meta_key'      => 'learning_type',
        'meta_value'    => $currentType
    ]);

    $itemsCount = count($posts);
    $count = 1;

    foreach ($posts as $post) {
        $msgPrefix = '[' . $count . '/' . $itemsCount . ']';
        Logger::log("$msgPrefix: $post->ID updating...", 'status');

        update_field(
            LearningCT::GROUP_LEARNING_TAXONOMY__FIELD_LEARNING_TYPE,
            $newType,
            $post->ID
        );
        do_action('acf/save_post', $post->ID);

        Logger::log("$msgPrefix: $post->ID updated.", 'success');

        $count++;
    }

    wp_suspend_cache_addition(false);
}
