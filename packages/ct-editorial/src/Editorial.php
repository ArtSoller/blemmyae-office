<?php

/**
 * Editorial class, customizes default Post Type
 *
 * @package   Cra\CtEditorial
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtEditorial;

use Scm\Entity\CustomPostType;
use WP_Post_Type;
use WP_Term;

/**
 * Editorial class.
 * @deprecated use EditorialCT
 *
 * @psalm-suppress UndefinedClass
 */
class Editorial extends CustomPostType
{
    public const POST_TYPE = 'editorial';
    public const EDITORIAL_TYPE_FIELD = 'field_60654eeddc40e';
    public const AUTHOR_FIELD = 'field_608f8eda9ccf7';
    public const FEATURED_IMAGE = 'field_6066d54dac469';
    public const FEATURED_IMAGE_CAPTION = 'field_61924b6e1912d';
    public const SHOW_FEATURED_IMAGE = 'field_61a5cb6aa53eb';
    public const TYPE_FIELD = 'field_60654eeddc40e';
    public const BRAND_FIELD = 'field_60655b3f1908e';
    public const INDUSTRY_FIELD = 'field_60655ab51908d';
    public const REGION_FIELD = 'field_6065761244f70';
    public const TOPIC_FIELD = 'field_6066d31112bc0';
    public const PARENT_TOPIC_FIELD = 'field_6257b772835fd';
    public const YEAR_FIELD = 'field_63999ef9de607';
    public const RELATED_BLOCK_FIELD = 'field_625522550cb54';
    public const VENDOR_FIELD = 'field_60d5afe17d694';
    public const DECK_FIELD = 'field_6065ae75dc539';
    public const META_TITLE = 'field_61168e54ff82e';
    public const META_DESCRIPTION = 'field_61168e6aff82f';

    public const EDITORIAL_TYPE_TAXONOMY = 'editorial_type';

    /**
     * @param WP_Term[]|null $postTopics
     * @return WP_Term|null
     */
    public static function findMainTopic(?array $postTopics): ?WP_Term
    {
        $mainTopicValue = count($postTopics ?: []) ? array_shift($postTopics) : null;

        if (is_string($mainTopicValue) || is_numeric($mainTopicValue)) {
            $mainTopicTerm = get_term($mainTopicValue, 'topic');
            $mainTopicValue = $mainTopicTerm instanceof WP_Term ? $mainTopicTerm : null;
        }

        return $mainTopicValue;
    }

    /**
     * Editorial constructor:
     *  - Calls hook init.
     * @param string $pluginDirPath Plugin dir path.
     */
    public function __construct(string $pluginDirPath = '')
    {
        parent::__construct($pluginDirPath);
        $this->hookInit();
    }

    /**
     * Registers hooks.
     *
     * @return self
     * @psalm-suppress MixedPropertyFetch, MixedArrayAccess
     */
    public function hookInit(): self
    {
        add_action('init', [$this, 'hidePostObject']);
        /**
         * This filter modifies "editorial" post rows,
         * such as "Edit", "Quick Edit" and "Trash".
         *
         * @param $actions
         * @param $post
         *
         * @return mixed
         */
        add_filter('post_row_actions', static function ($actions, $post) {
            if ($post->post_type === 'editorial') {
                // Remove "Quick Edit"
                unset($actions['inline hide-if-no-js']);
            }
            return $actions;
        }, 10, 2);

        /**
         * This filter adds 'acfe_taxonomy_terms' field to graphql supported.
         * @link https://wordpress.org/support/topic/graphql-support/
         *
         * @param array $fields
         *
         * @return array
         */
        add_filter('wpgraphql_acf_supported_fields', static function (array $fields) {
            $fields[] = 'acfe_taxonomy_terms';
            return $fields;
        });

        return $this;
    }

    /**
     * Hide default WP Post type.
     * @psalm-suppress MixedAssignment, UndefinedClass
     */
    public function hidePostObject(): void
    {
        if (!function_exists('get_post_type_object')) {
            return;
        }
        $postType = get_post_type_object('post');
        if ($postType instanceof WP_Post_Type) {
            $postType->show_in_menu = false;
            $postType->show_in_nav_menus = false;
            $postType->exclude_from_search = true;
            $postType->public = false;
            $postType->show_ui = false;
            $postType->show_in_rest = false;
            if (class_exists('WPGraphQL')) {
                $postType->show_in_graphql = false;
            }
        }
    }
}
