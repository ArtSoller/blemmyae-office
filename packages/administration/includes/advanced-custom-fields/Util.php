<?php

/**
 * Util.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

namespace Scm\Advanced_Custom_Fields;

use Scm\Entity\Import;
use Scm\Tools\Logger;
use WP_Post;

/**
 * @todo: Update description.
 *
 * Class Util
 * @package Scm\Advanced_Custom_Fields
 */
class Util implements Import
{
    /**
     * Remake of @param array $data Data as json. Optional.
     *
     * @return bool false on nothing to do, otherwise true.
     * @throws \JsonException
     * @see ACF_Admin_Tool_Import.
     * Re-usable acf import tool function.
     *
     * Import the posted JSON data from a separate export.
     *
     * @internal
     *
     * @since 1.0.0
     */
    public static function acfImport(array $data = []): bool
    {
        $success = false;
        $remove = $data['remove'] ?? false;

        if (empty($data['acf_import'])) {
            return $success;
        }

        // Read JSON.
        $acfData = stripslashes_deep(trim($data['acf_import']));
        $newSettings = json_decode($acfData, true, 512, JSON_THROW_ON_ERROR);

        // Check if empty.
        if (!$newSettings || !is_array($newSettings)) {
            return $success;
        }

        // Ensure $newSettings is an array of groups.
        if (isset($newSettings['key'])) {
            $newSettings = [$newSettings];
        }

        // Remember imported field group ids.
        $ids = [];
        foreach ($newSettings as $setting) {
            $setting['ID'] = '';

            // Search database for existing field group.
            if (isset($setting['key'])) {
                $post = acf_get_field_group_post($setting['key']);
                $setting['ID'] = $post->ID ?? '';
            }

            switch ($data['acf_type']) {
                case 'block_type':
                    $blockAcf = acf_get_instance('acfe_dynamic_block_types');
                    if ($blockAcf instanceof \acfe_dynamic_block_types) {
                        $block = $remove ?
                            get_page_by_path($setting['name'], OBJECT, $blockAcf->post_type) :
                            null;
                        if ($block) {
                            // Delete block type.
                            wp_delete_post($block->ID, true);
                            $setting['ID'] = $block->ID;
                            $blockAcf->trashed_post($block->ID);
                        } else {
                            // Import block type.
                            $setting['ID'] = $blockAcf->import($setting['name'], $setting);
                        }
                    }
                    break;
                case 'field_group':
                    if ($remove) {
                        // Delete field group.
                        $setting['ID'] = $setting['ID'] ? acf_delete_field_group($setting['ID']) : '';
                    } else {
                        // Import field group.
                        $setting = acf_import_field_group($setting);
                    }
                    break;
                case 'post':
                case 'taxonomy':
                    if ($remove) {
                        // Delete post type.
                        $setting['ID'] = $setting['ID'] ? acf_delete_post_type($setting['ID']) : '';
                    } else {
                        $internalPostType = (string)acf_determine_internal_post_type($setting['key']);
                        $post = acf_get_internal_post_type_post($setting['key'], $internalPostType);

                        if ($post instanceof WP_Post) {
                            $setting['ID'] = $post->ID;
                        }

                        // Import the post.
                        acf_import_internal_post_type($setting, $internalPostType);
                    }
                    break;
                case 'options_page':
                    $optionsAcf = acf_get_instance('acfe_dynamic_options_pages');
                    if ($optionsAcf instanceof \acfe_dynamic_options_pages) {
                        /** @phpstan-ignore-next-line */
                        $options = get_page_by_title(
                            $setting['page_title'],
                            OBJECT,
                            $optionsAcf->post_type
                        );
                        if ($options) {
                            // Delete options page.
                            wp_delete_post($options->ID, true);
                            $setting['ID'] = $options->ID;
                            $optionsAcf->trashed_post($options->ID);
                        }
                        if (!$remove) {
                            $setting['ID'] = $optionsAcf->import($setting['post_id'], $setting);
                        }
                    }
                    break;
                default:
                    Logger::log(
                        $data['acf_type'] . ' type isn\'t supported by custom acf/acfe import.',
                        'error'
                    );
                    break;
            }

            // Enlist items.
            $ids[] = $setting['ID'];
        }

        // Any imported/deleted field groups.
        return (bool)array_filter($ids);
    }

    /**
     * @param array $data
     *
     * @return bool
     * @todo: Update description.
     *
     */
    public static function import(array $data): bool
    {
        try {
            self::acfImport($data);

            return true;
        } catch (\JsonException $err) {
            Logger::log($err->getMessage(), 'error');
            error_log($err->getMessage(), 0);

            return false;
        }
    }
}
