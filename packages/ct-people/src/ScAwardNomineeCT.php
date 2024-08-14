<?php

namespace Cra\CtPeople;

use Scm\Entity\CustomPostType;

/**
 * ScAwardNominee class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class ScAwardNomineeCT extends CustomPostType
{
    public const POST_TYPE = 'sc_award_nominee';
    public const GROUP_APPLICATION__FIELD_APPLICATION = 'field_634fcdce7572f';
    public const GROUP_APPLICATION__FIELD_APPLICATIONS = 'field_646c59e467bbf';
    public const GROUP_APPLICATION__FIELD_SLUG = 'field_6475be6ffc7a9';
    public const GROUP_SC_AWARD_FINALIST_ADVANCED__FIELD_CATEGORY = 'field_627a26d4f3ce1';
    public const GROUP_SC_AWARD_FINALIST_ADVANCED__FIELD_PARENT_CATEGORY = 'field_627a2c77aab74';
    public const GROUP_SC_AWARD_FINALIST_ADVANCED__FIELD_NOMINEE = 'field_627a271df3ce2';
    public const GROUP_SC_AWARD_FINALIST_ADVANCED__FIELD_FEATURED_IMAGE = 'field_627a2501f3cdd';
    public const GROUP_SC_AWARD_FINALIST_ADVANCED__FIELD_FEATURED_IMAGE_CAPTION = 'field_627a265ff3cde';
    public const GROUP_SC_AWARD_FINALIST_ADVANCED__FIELD_SUMMARY = 'field_627a26a5f3ce0';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const GRAPHQL_NAME = 'ScAwardNominee';
    public const GRAPHQL_PLURAL_NAME = 'ScAwardNominees';

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
