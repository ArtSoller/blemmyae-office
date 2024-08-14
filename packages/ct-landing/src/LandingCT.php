<?php

namespace Cra\CtLanding;

use Scm\Entity\CustomPostType;

/**
 * Landing class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class LandingCT extends CustomPostType
{
    public const POST_TYPE = 'landing';
    public const GROUP_APPLICATION__FIELD_APPLICATION = 'field_634fcdce7572f';
    public const GROUP_APPLICATION__FIELD_APPLICATIONS = 'field_646c59e467bbf';
    public const GROUP_APPLICATION__FIELD_SLUG = 'field_6475be6ffc7a9';
    public const GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR = 'field_608f8eda9ccf7';
    public const GROUP_COLLECTION_WIDGET__FIELD_LAYOUTS = 'field_60c1fd708e2d6';
    public const GROUP_COLLECTION_WIDGET__FIELD_LAYOUTS__SUBFIELD_TYPE = 'field_60c1fbf8f50d1';
    public const GROUP_COLLECTION_WIDGET__FIELD_LAYOUTS__SUBFIELD_COLUMNS = 'field_60c1e796e4d04';
    public const GROUP_COLLECTION_WIDGET__FIELD_LAYOUTS__SUBFIELD_TITLE = 'field_60c1fd498d260';
    public const GROUP_COLLECTION_WIDGET__FIELD_LAYOUTS__SUBFIELD_BACKGROUND_IMAGE = 'field_60c1eda53e988';
    public const GROUP_COLLECTION_WIDGET__FIELD_LAYOUTS__SUBFIELD_LAYOUT_OPTIONS = 'field_61b70853a9bfd';
    public const GROUP_COLLECTION_WIDGET__FIELD_LAYOUTS__SUBFIELD_DESIGN_THEME = 'field_61b32e9def1d1';
    public const GROUP_COLLECTION_WIDGET__FIELD_LAYOUTS__SUBFIELD_CUSTOM_ACCENT_COLOR = 'field_61b32ecfef1d2';
    public const GROUP_COLLECTION_WIDGET__FIELD_LAYOUTS__SUBFIELD_CUSTOM_BACKGROUND_COLOR = 'field_61b32f0fef1d3';
    public const GROUP_COLLECTION_WIDGET__FIELD_LAYOUTS__SUBFIELD_CUSTOM_TEXT_COLOR = 'field_61b32f34ef1d4';
    public const GROUP_COLLECTION_WIDGET__FIELD_LAYOUTS__SUBFIELD_BACKGROUND_IMAGE_POSITION = 'field_63510ecebf466';
    public const GROUP_LANDING_TAXONOMY__FIELD_TYPE = 'field_607d0fcebd4b3';
    public const GROUP_LANDING_TAXONOMY__FIELD_TOPIC = 'field_607d0f183d443';
    public const GROUP_ADVANCED_PAGE_OPTIONS__FIELD_ADDITIONAL_CSS_CLASSES = 'field_6167e0291cc1c';
    public const GROUP_ADVANCED_PAGE_OPTIONS__FIELD_DESIGN_THEME = 'field_61b320c25512d';
    public const GROUP_ADVANCED_PAGE_OPTIONS__FIELD_CUSTOM_ACCENT_COLOR = 'field_61b3210a5512e';
    public const GROUP_ADVANCED_PAGE_OPTIONS__FIELD_CUSTOM_BACKGROUND_COLOR = 'field_61b322b95512f';
    public const GROUP_ADVANCED_PAGE_OPTIONS__FIELD_CUSTOM_TEXT_COLOR = 'field_61b323c555130';
    public const GROUP_SPONSOR__FIELD_SPONSORS = 'field_613054fb8ff7e';
    public const GROUP_META__FIELD_TITLE = 'field_61168e54ff82e';
    public const GROUP_META__FIELD_DESCRIPTION = 'field_61168e6aff82f';
    public const GROUP_META__FIELD_IMAGE = 'field_628b5738d6c15';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const GRAPHQL_NAME = 'Landing';
    public const GRAPHQL_PLURAL_NAME = 'Landings';

    /**
     * Initialize hooks.
     */
    public function __construct()
    {
        parent::__construct();
        add_filter('register_post_type_args', static function ($args, $postType) {
            if (self::POST_TYPE === $postType) {
                $args['show_in_graphql'] = true;
                $args['graphql_single_name'] = self::GRAPHQL_NAME;
                $args['graphql_plural_name'] = self::GRAPHQL_PLURAL_NAME;
            }
            return $args;
        }, 10, 2);
    }
}
