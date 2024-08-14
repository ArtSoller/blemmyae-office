<?php

declare(strict_types=1);

use Scm\Tools\Logger;

function port2297_convert_legacy_layouts(array $layoutOptions): array
{
    return array_map(
        fn($option) => str_contains($option, 'mt-') || str_contains($option, 'mb-') ? preg_replace(
            '/m([t, b])/m',
            "p$1",
            $option
        ) : $option,
        $layoutOptions
    );
}

/**
 * Insert existing redirects for SCM -> CE2E.
 *
 * @throws Exception
 */
function port2297_blemmyae_deployment_ri_001(): void
{
    $wpQuery = new WP_Query([
        'post_type' => 'landing',
        'post_status' => ['publish', 'draft'],
        'posts_per_page' => -1,
        'fields' => 'ids',
    ]);

    $result = $wpQuery->posts;

    foreach ($result as $post) {
        $postMeta = get_post_meta($post);
        $layoutOptionsFieldKeys = array_filter(
            array_keys($postMeta),
            static fn($key) => str_contains($key, 'layout_options')
        );

        foreach ($layoutOptionsFieldKeys as $fieldKey) {
            $layoutOptionsArray = unserialize($postMeta[$fieldKey][0]);
            if (!is_countable($layoutOptionsArray)) {
                continue;
            }

            $updatedLayoutOptions = port2297_convert_legacy_layouts($layoutOptionsArray);

            Logger::log("Updating mt-x and mb-x to pt-x pb-x, post id $post", 'info');
            update_post_meta($post, $fieldKey, $updatedLayoutOptions);
        }
    }
}
