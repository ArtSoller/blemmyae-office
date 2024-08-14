<?php

namespace Cra\BlemmyaePpworks;

use Scm\Entity\CustomPostType;

/**
 * PpworksSponsorProgram class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class PpworksSponsorProgramCT extends CustomPostType
{
    public const POST_TYPE = 'ppworks_sponsor_prog';
    public const GROUP_APPLICATION__FIELD_APPLICATION = 'field_634fcdce7572f';
    public const GROUP_APPLICATION__FIELD_APPLICATIONS = 'field_646c59e467bbf';
    public const GROUP_APPLICATION__FIELD_SLUG = 'field_6475be6ffc7a9';
    public const GROUP_PPWORKS_SPONSOR_PROGRAM_ADVANCED__FIELD_SPONSOR = 'field_649d8bfdcb0f5';
    public const GROUP_PPWORKS_SPONSOR_PROGRAM_ADVANCED__FIELD_LANDING_PAGE_URL = 'field_649d8d51cb0f6';
    public const GROUP_PPWORKS_SPONSOR_PROGRAM_ADVANCED__FIELD_IS_ACTIVE = 'field_6579b460be3a5';
    public const GROUP_PPWORKS_SPONSOR_PROGRAM_ADVANCED__FIELD_TIER = 'field_6579b509be3a6';
    public const GROUP_PPWORKS_SPONSOR_PROGRAM_ADVANCED__FIELD_BRAND = 'field_659e9156ef3f1';
    public const GROUP_PPWORKS_SPONSOR_PROGRAM_ADVANCED__FIELD_BRAND_ORDER = 'field_65a13ce183b0d';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const GRAPHQL_NAME = 'PPWorksSponsorProgram';
    public const GRAPHQL_PLURAL_NAME = 'PPWorksSponsorPrograms';

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
