<?php

/**
 * ElasticPressOptions.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

declare(strict_types=1);

namespace Scm\Advanced_Custom_Fields;

use WP_Post;

use function add_filter;

class ElasticPressOptions
{
    /**
     * @todo: Add support for 'ep_weighting_configuration_for_autosuggest'.
     *
     * Initialize hooks.
     */
    public function __construct()
    {
        add_filter(
            'ep_prepare_meta_data',
            [$this, 'epPrepareMetaData'],
            10,
            2
        );
        add_filter(
            'ep_search_fields',
            [$this, 'epSearchFields'],
            10,
            2
        );
        add_filter(
            'ep_weighting_configuration',
            [$this, 'epWeightingConfiguration'],
            10,
            1
        );
        add_filter(
            'ep_weighting_fields_for_post_type',
            [$this, 'epWeightingFieldsForPostType'],
            10,
            2
        );
        add_filter(
            'ep_weighting_configuration_for_search',
            [$this, 'epWeightingConfigurationForSearch'],
            10,
            2
        );
    }

    /**
     * Filter pre-prepare meta for a post
     *
     * @hook ep_prepare_meta_data
     * @param array $meta
     * @param mixed $post
     * @return array New meta
     */
    public function epPrepareMetaData(array $meta, mixed $post): array
    {
        if (!($post instanceof WP_Post)) {
            return $meta;
        }
        $groups = acf_get_field_groups(['post_type' => $post->post_type]);
        foreach ($groups as $group) {
            $fields = acf_get_fields($group);
            foreach ($fields as $field) {
                $meta['_' . $field['name']] = [$field['key']];
                if ($field['name'] === 'author') {
                    $fieldValue = get_field($field['name'], $post->ID);

                    // In some cases field value contains WP_Object instead of array.
                    // @todo migrate all authors into array via RI.
                    $author = is_array($fieldValue) ? $fieldValue[0] : $fieldValue;

                    $fieldValue = !empty($author) && is_object($author) ?
                        [
                            'name' => $author->post_title ?? '',
                            'slug' => $author->post_name ?? '',
                        ] : null;
                } elseif ($field['type'] === 'image') {
                    $fieldValue = get_field($field['name'], $post->ID);
                    if (!empty($fieldValue) && is_array($fieldValue)) {
                        $fieldValue = [
                            'id' => $fieldValue['id'] ?? '',
                            'url' => $fieldValue['url'] ?? '',
                            'title' => $fieldValue['title'] ?? '',
                            'description' => $fieldValue['description'] ?? '',
                            'caption' => $fieldValue['caption'] ?? '',
                            'alt' => $fieldValue['alt'] ?? '',
                            'width' => $fieldValue['width'] ?? '',
                            'height' => $fieldValue['height'],
                            'mime_type' => $fieldValue['mime_type'],
                        ];
                    }
                } else {
                    $fieldValue = get_field($field['name'], $post->ID, false);
                }
                if ($fieldValue) {
                    $meta[$field['name']] = is_array($fieldValue) ? $fieldValue : [$fieldValue];
                }
            }
        }

        // Filter out unnecessary meta fields from indexing
        return array_filter($meta, static function ($metaItem) {
            $excludedKeyPatterns = [
                // Exclude private meta fields
                '/^_.+/m',
                //Exclude layouts acf field for landings
                '/^layouts.+/m',
                // Exclude common non-indexed fields
                '/^vendor.+/m',
                '/^review.+/m',
                '/^flags.*/m',
                '/^logo.+/m',
                '/^job.+/m',
                '/^swoogo.+/m',
                '/about/m',
                '/.*community.*/m',
                '/years/m',
                '/.*name$/m',
                '/.*product.*/m',
                '/position/m',
                '/.*video.*/m',
                '/.*audio.*/m',
                '/.*id$/m',
                '/.*transcription.*/m',
                '/.*count$/m',
                '/.*podcast.*/m',
                '/^head.*/'
            ];
            foreach ($excludedKeyPatterns as $pattern) {
                if (preg_match($pattern, $metaItem)) {
                    return false;
                }
            }

            $matches = [];
            // Example matches:
            // _meta_field_10_example, another_meta_0_example
            preg_match("/(?:.+)?_(\d+)_(?:.+)?/m", $metaItem, $matches);
            // Filter out any meta items that are part of repeater
            // or flexible content except for the first one
            return !(isset($matches[1]) && $matches[1] > 0);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Filter weighting configuration for search
     *
     * @hook ep_weighting_configuration_for_search
     * @param array $weightConfig
     * @param array $args
     * @return array New configuration
     */
    public function epWeightingConfigurationForSearch(array $weightConfig, array $args): array
    {
        $postType = (array)$args['post_type'];
        if (in_array('post', $postType, true)) {
            $groups = acf_get_field_groups(['post_type' => $postType]);
            foreach ($groups as $group) {
                $fields = acf_get_fields($group);
                foreach ($fields as $field) {
                    if (empty($weightConfig['post'][$field['name']])) {
                        $weightConfig['post'][$field['name']] = [
                            'weight' => 1,
                            'enabled' => 1,
                        ];
                        if (isset($field['layouts'])) {
                            foreach ($field['layouts'] as $layout) {
                                foreach ($layout['sub_fields'] as $subField) {
                                    // @todo: Workaround for a flexible_content fields. Index only first item in a row.
                                    $subFieldName = $field['name'] . '_0_' . $subField['name'];
                                    if (empty($weightConfig['post'][$subFieldName])) {
                                        $weightConfig['post'][$subFieldName] = [
                                            'weight' => 1,
                                            'enabled' => 0,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $weightConfig;
    }

    /**
     * Filter default post search fields
     *
     * If you are using the weighting engine, this filter should not be used.
     * Instead, you should use the ep_weighting_configuration_for_search filter.
     *
     * @hook ep_search_fields
     * @param array $searchFields
     * @param array $args
     * @return array New defaults
     */
    public function epSearchFields(array $searchFields, array $args): array
    {
        $newMetaFields = [];
        $postType = (array)$args['post_type'];
        if (in_array('post', $postType, true)) {
            $groups = acf_get_field_groups(['post_type' => $postType]);
            foreach ($groups as $group) {
                $fields = acf_get_fields($group);
                foreach ($fields as $field) {
                    $newMetaFields[] = 'meta.' . $field['name'] . '.value';
                    if (isset($field['layouts'])) {
                        foreach ($field['layouts'] as $layout) {
                            foreach ($layout['sub_fields'] as $subField) {
                                // @todo: Workaround for a flexible_content fields. Index only first item in a row.
                                $subFieldName = $field['name'] . '_0_' . $subField['name'];
                                $newMetaFields[] = 'meta.' . $subFieldName . '.value';
                            }
                        }
                    }
                }
            }
        }
        return array_merge($searchFields, $newMetaFields);
    }

    /**
     * Filter weighting fields for a post type
     *
     * @hook ep_weighting_fields_for_post_type
     * @param array $searchFields
     * @param string $postType
     * @return array New fields
     */
    public function epWeightingFieldsForPostType(array $searchFields, string $postType): array
    {
        $groups = acf_get_field_groups(['post_type' => $postType]);
        foreach ($groups as $group) {
            $fields = acf_get_fields($group);
            foreach ($fields as $field) {
                if (empty($searchFields['attributes']['children'][$field['name']])) {
                    $searchFields['attributes']['children'][$field['name']] = [
                        'key' => $field['name'],
                        'label' => $field['label'],
                    ];
                    if (isset($field['layouts'])) {
                        foreach ($field['layouts'] as $layout) {
                            foreach ($layout['sub_fields'] as $subField) {
                                // @todo: Workaround for a flexible_content fields. Index only first item in a row.
                                $subFieldName = $field['name'] . '_0_' . $subField['name'];
                                $subFieldLabel = $field['label'] . ': ' . $subField['label'];
                                if (empty($searchFields['attributes']['children'][$subFieldName])) {
                                    $searchFields['attributes']['children'][$subFieldName] = [
                                        'key' => $subFieldName,
                                        'label' => $subFieldLabel,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $searchFields;
    }

    /**
     * Filter weighting configuration
     *
     * @hook ep_weighting_configuration
     * @param array $weightConfig
     * @return array New configuration
     */
    public function epWeightingConfiguration(array $weightConfig): array
    {
        foreach ($weightConfig as $postType => $postConfig) {
            $groups = acf_get_field_groups(['post_type' => $postType]);
            foreach ($groups as $group) {
                $fields = acf_get_fields($group);
                foreach ($fields as $field) {
                    if (empty($weightConfig[$postType][$field['name']])) {
                        $weightConfig[$postType][$field['name']] = [
                            'weight' => 1,
                            'enabled' => 0,
                        ];
                        if (isset($field['layouts'])) {
                            foreach ($field['layouts'] as $layout) {
                                foreach ($layout['sub_fields'] as $subField) {
                                    // @todo: Workaround for a flexible_content fields. Index only first item in a row.
                                    $subFieldName = $field['name'] . '_0_' . $subField['name'];
                                    if (empty($weightConfig[$postType][$subFieldName])) {
                                        $weightConfig[$postType][$subFieldName] = [
                                            'weight' => 1,
                                            'enabled' => 0,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $weightConfig;
    }
}
