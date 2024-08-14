<?php

/**
 * @file Graphql class which adds support of ACFE fields to Graphql.
 *
 * @author Konstantin Gusev (guvkon.net@icloud.com)
 */

declare(strict_types=1);

namespace Scm\Acf_Extended;

use ACFE;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ResolveInfo;
use Scm\WP_GraphQL\Options;
use WPGraphQL\ACF\ACF;
use WPGraphQL\AppContext;
use WPGraphQL\Model\Comment;
use WPGraphQL\Model\Menu;
use WPGraphQL\Model\MenuItem;
use WPGraphQL\Model\Post;
use WPGraphQL\Model\Term;
use WPGraphQL\Model\User;

/**
 * Class Graphql
 *
 * @package Scm\Acf_Extended
 */
class Graphql
{
    /**
     * Graphql constructor.
     */
    public function __construct()
    {
        if (!class_exists(ACFE::class) || !class_exists(ACF::class)) {
            return;
        }

        add_filter(
            'wpgraphql_acf_register_graphql_field',
            [$this, 'acfRegisterGraphqlField'],
            10,
            4
        );

        // Resolve graphql fields.
        add_filter('graphql_resolve_field', [$this, 'resolveDraftsInPostObjectField'], 10, 9);
    }

    /**
     * Callback for 'wpgraphql_acf_register_graphql_field'.
     *
     * @param array $fieldConfig
     * @param string $typeName
     * @param string $fieldName
     * @param array $config
     *
     * @return array
     */
    public function acfRegisterGraphqlField(
        array $fieldConfig,
        string $typeName,
        string $fieldName,
        array $config
    ): array {
        $acfField = $config['acf_field'] ?? null;
        $acfType = $acfField['type'] ?? null;
        switch ($acfType) {
            case 'acfe_post_types':
                $this->handleAcfePostTypesField($fieldConfig, $acfField);
                break;
            case 'acfe_taxonomy_terms':
                $this->handleAcfeTaxonomyTermsField($fieldConfig, $acfField);
                break;
            default:
                break;
        }

        return $fieldConfig;
    }

    /**
     * Copy of code for 'select' type from \WPGraphQL\ACF\Config::register_graphql_field().
     *
     * @param array $field_config
     * @param array $acf_field
     *
     * @see \WPGraphQL\ACF\Config::register_graphql_field()
     */
    protected function handleAcfePostTypesField(
        array &$field_config,
        array $acf_field
    ): void {
        /**
         * If the select field is configured to not allow multiple values
         * the field will return a string, but if it is configured to allow
         * multiple values it will return a list of strings, and an empty array
         * if no values are set.
         *
         * @see: https://github.com/wp-graphql/wp-graphql-acf/issues/25
         */
        if (empty($acf_field['multiple'])) {
            if ('array' === $acf_field['return_format']) {
                $field_config['type'] = ['list_of' => 'String'];
                $field_config['resolve'] = function ($root) use ($acf_field) {
                    $value = $this->get_acf_field_value($root, $acf_field, true);

                    return !empty($value) && is_array($value) ? $value : [];
                };
            } else {
                $field_config['type'] = 'String';
            }
        } else {
            $field_config['type'] = ['list_of' => 'String'];
            $field_config['resolve'] = function ($root) use ($acf_field) {
                $value = $this->get_acf_field_value($root, $acf_field);

                return !empty($value) && is_array($value) ? $value : [];
            };
        }
    }

    /**
     * Handle 'acfe_taxonomy_terms' field type.
     *
     * This is a workaround until ACFE adds proper support. Only supports return of IDs.
     *
     * @param array $fieldConfig
     * @param array $acfField
     */
    protected function handleAcfeTaxonomyTermsField(array &$fieldConfig, array $acfField): void
    {
        $type = 'termNode';

        if (!empty($acfField['multiple'])) {
            $type = ['list_of' => $type];
        }

        $fieldConfig = [
            'type' => $type,
            'resolve' => function ($root, $args, $context, $info) use ($acfField) {
                $value = $this->get_acf_field_value($root, $acfField);

                if (empty($value)) {
                    return empty($acfField['multiple']) ? null : [];
                }

                // If field is not multiple, but value is array => return first element.
                if (empty($acfField['multiple']) && is_array($value)) {
                    $value = !empty($value) ? reset($value) : null;
                }

                return is_array($value) ? array_map(
                    static function ($value): ?Term {
                        $term = get_term($value);
                        return $term ? new Term($term) : null;
                    },
                    $value
                ) : new Term(get_term($value));
            },
        ];
    }

    /**
     * Copy of protected method get_acf_field_value from \WPGraphQL\ACF\Config::get_acf_field_value().
     *
     * @param       $root
     * @param       $acf_field
     * @param false $format
     *
     * @return mixed|void|null
     *
     * @see \WPGraphQL\ACF\Config::get_acf_field_value()
     */
    // phpcs:ignore
    protected function get_acf_field_value($root, $acf_field, $format = false)
    {
        $value = null;
        $id = null;

        if (is_array($root) && isset($root['node'])) {
            $id = $root['node']->ID;
        }

        if (is_array($root) && !(!empty($root['type']) && 'options_page' === $root['type'])) {
            if (isset($root[$acf_field['key']])) {
                $value = $root[$acf_field['key']];

                if ('wysiwyg' === $acf_field['type']) {
                    $value = apply_filters('the_content', $value);
                }
            }
        } else {
            switch (true) {
                case $root instanceof Term:
                    $id = 'term_' . $root->term_id;
                    break;
                case $root instanceof Post:
                    $id = absint($root->databaseId);
                    break;
                case $root instanceof MenuItem:
                    $id = absint($root->menuItemId);
                    break;
                case $root instanceof Menu:
                    $id = 'term_' . $root->menuId;
                    break;
                case $root instanceof User:
                    $id = 'user_' . absint($root->userId);
                    break;
                case $root instanceof Comment:
                    $id = 'comment_' . absint($root->databaseId);
                    break;
                case is_array($root) && !empty($root['type']) && 'options_page' === $root['type']:
                    $id = $root['post_id'];
                    break;
                default:
                    $id = null;
                    break;
            }
        }

        if (empty($value)) {
            /**
             * Filters the root ID, allowing additional Models the ability to provide a way to resolve their ID
             *
             * @param int $id The ID of the object. Default null
             * @param mixed $root The Root object being resolved. The ID is typically a property of this object.
             */
            $id = apply_filters('graphql_acf_get_root_id', $id, $root);

            if (empty($id)) {
                return null;
            }

            $format = false;

            if ('wysiwyg' === $acf_field['type']) {
                $format = true;
            }

            if ('select' === $acf_field['type']) {
                $format = true;
            }

            /**
             * Check if cloned field and retrieve the key accordingly.
             */
            if (!empty($acf_field['_clone'])) {
                $key = $acf_field['__key'];
            } else {
                $key = $acf_field['key'];
            }

            $field_value = get_field($key, $id, $format);

            $value = !empty($field_value) ? $field_value : null;
        }

        /**
         * Filters the returned ACF field value
         *
         * @param mixed $value The resolved ACF field value
         * @param array $acf_field The ACF field config
         * @param mixed $root The Root object being resolved. The ID is typically a property of this object.
         * @param int $id The ID of the object
         */
        return apply_filters('graphql_acf_field_value', $value, $acf_field, $root, $id);
    }

    /**
     * Remove drafts from post object field.
     *
     * GraphQL module does not support drafts in post object field with multiple
     * posts, because part of the posts may return `null`, when Post object
     * expected. This fix should not be needed for non-multiple field, because
     * user can use `Allow Null` settings for this type of field.
     *
     * @param mixed $result
     * @param mixed $source
     * @param array $args
     * @param AppContext $context
     * @param ResolveInfo $info
     * @param string $typeName
     * @param string $fieldKey
     * @param FieldDefinition $field
     * @param mixed $fieldResolver
     *
     * @return mixed
     */
    public function resolveDraftsInPostObjectField(
        mixed $result,
        mixed $source,
        array $args,
        AppContext $context,
        ResolveInfo $info,
        string $typeName,
        string $fieldKey,
        FieldDefinition $field,
        mixed $fieldResolver
    ): mixed {
        if (!empty($field->config['acf_field'])) {
            $acfFieldConfig = $field->config['acf_field'];

            if (
                $acfFieldConfig['type'] === 'post_object' &&
                $acfFieldConfig['multiple']
            ) {
                // Work only with public posts.
                $result = array_filter($result ?? [], static function (Post $post): bool {
                    return in_array($post->post_status, Options::getPublicPostStatuses(), true);
                });
            }
        }

        return $result;
    }
}
