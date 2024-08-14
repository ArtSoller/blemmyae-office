<?php

namespace Cra\CtWhitepaper;

use Scm\Entity\CustomPostType;

/**
 * Whitepaper class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class WhitepaperCT extends CustomPostType
{
    public const POST_TYPE = 'whitepaper';
    public const GROUP_APPLICATION__FIELD_APPLICATION = 'field_634fcdce7572f';
    public const GROUP_APPLICATION__FIELD_APPLICATIONS = 'field_646c59e467bbf';
    public const GROUP_APPLICATION__FIELD_SLUG = 'field_6475be6ffc7a9';
    public const GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR = 'field_608f8eda9ccf7';
    public const GROUP_POST_STATUS__FIELD_POST_STATUS = 'field_6474c00a815ec';
    public const GROUP_PUBLISHING_OPTIONS__FIELD_UNPUBLISH_DATE = 'field_61af47d3a014d';
    public const GROUP_WHITEPAPER_ADVANCED__FIELD_FEATURED_IMAGE = 'field_60758315798bf';
    public const GROUP_WHITEPAPER_ADVANCED__FIELD_COMPANY_PROFILE = 'field_60758329798c0';
    public const GROUP_WHITEPAPER_ADVANCED__FIELD_VENDOR = 'field_60a21f7043b2f';
    public const GROUP_WHITEPAPER_ADVANCED__FIELD_REDIRECT_OPTIONS = 'field_62b192d9e920d';
    public const GROUP_WHITEPAPER_ADVANCED__FIELD_REDIRECT_OPTIONS__SUBFIELD_URL = 'field_62b19308e920e';
    public const GROUP_WHITEPAPER_ADVANCED__FIELD_REDIRECT_OPTIONS__SUBFIELD_DELAY = 'field_62b19317e920f';
    public const GROUP_WHITEPAPER_ADVANCED__FIELD_FEATURED_IMAGE_HEIGHT = 'field_64c234969f44c';
    public const GROUP_WHITEPAPER_ADVANCED__FIELD_FEATURED_IMAGE_WIDTH = 'field_64c234bd9f44d';
    public const GROUP_WHITEPAPER_TAXONOMY__FIELD_TOPIC = 'field_6075818b362e5';
    public const GROUP_WHITEPAPER_TAXONOMY__FIELD_WHITEPAPER_TYPE = 'field_60758285be3fe';
    public const GROUP_META__FIELD_TITLE = 'field_61168e54ff82e';
    public const GROUP_META__FIELD_DESCRIPTION = 'field_61168e6aff82f';
    public const GROUP_META__FIELD_IMAGE = 'field_628b5738d6c15';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const HIDDEN_FROM_FEEDS_POST_STATUS = 'hidden_from_feeds';
    public const VENDOR__CONVERTR = 'convertr';
    public const VENDOR__INTERNAL_WHITEPAPER = 'internal_whitepaper';
    public const GRAPHQL_NAME = 'Whitepaper';
    public const GRAPHQL_PLURAL_NAME = 'Whitepapers';

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
