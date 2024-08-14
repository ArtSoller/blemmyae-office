<?php

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;

const PORT2376_LAST_CHUNK_OPTION = 'port2376_last_chunk_value';

/**
 * Import post types and taxonomies.
 * @throws JsonException
 */
function port2376_blemmyae_deployment_import_posts_and_taxonomies(): void
{
    // @phpstan-ignore-next-line
    $configPath = ABSPATH . 'packages/administration/config/';

    foreach (port2376_blemmyae_deployment_get_all_configs($configPath . 'post_types') as $post) {
        $data = ['acf_import' => $post['json'], 'acf_type' => 'post'];

        $newSettings = port2376_blemmyae_deployment_get_new_settings($data);

        foreach ($newSettings as $setting) {
            acf_import_post_type($setting);
        }
    }

    foreach (port2376_blemmyae_deployment_get_all_configs($configPath . 'taxonomy') as $taxonomy) {
        $data = ['acf_import' => $taxonomy['json'], 'acf_type' => 'post'];

        $newSettings = port2376_blemmyae_deployment_get_new_settings($data);

        foreach ($newSettings as $setting) {
            acf_import_taxonomy($setting);
        }
    }
}

/**
 * Copy of Scm\Entity\CustomImport->getAllConfigs()
 */
function port2376_blemmyae_deployment_get_all_configs(string $path): Generator
{
    $files = glob(untrailingslashit($path) . '/*.json') ?: [];
    $configs = [];
    foreach ($files as $file) {
        $configs[] = [
            'json' => file_get_contents($file, true) ?: '',
            'filename' => basename($file),
        ];
    }
    // After all configs are loaded we can start returning them.
    foreach ($configs as $config) {
        yield $config;
    }
}

/**
 * Partial copy of Scm\Advanced_Custom_Fields\Util::acfImport()
 *
 * @throws JsonException
 * @phpstan-ignore-next-line
 */
function port2376_blemmyae_deployment_get_new_settings(array $data): array
{
    if (empty($data['acf_import'])) {
        return [];
    }

    // Read JSON.
    $acfData = stripslashes_deep(trim($data['acf_import']));
    $newSettings = json_decode($acfData, true, 512, JSON_THROW_ON_ERROR);

    // Check if empty.
    if (!$newSettings || !is_array($newSettings)) {
        return [];
    }

    // Ensure $newSettings is an array of groups.
    if (isset($newSettings['key'])) {
        $newSettings = [$newSettings];
    }

    return $newSettings;
}

/**
 * @return void
 */
function port2376_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port2376_blemmyae_deployment_ri_002', true);
}

/**
 * @return void
 */
function port2376_blemmyae_deployment_ri_002(): void
{
    global $wpdb;
    $wpdb->update('wp_term_taxonomy', ['taxonomy' => 'years'], ['taxonomy' => 'year']);
    wp_cache_flush();
}
