<?php

namespace Cra\BlemmyaePpworks;

use Scm\Entity\CustomPostType;

/**
 * PpworksAnnouncement class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class PpworksAnnouncementCT extends CustomPostType
{
    public const POST_TYPE = 'ppworks_announcement';
    public const GROUP_APPLICATION__FIELD_APPLICATION = 'field_634fcdce7572f';
    public const GROUP_APPLICATION__FIELD_APPLICATIONS = 'field_646c59e467bbf';
    public const GROUP_APPLICATION__FIELD_SLUG = 'field_6475be6ffc7a9';
    public const GROUP_PPWORKS_ANNOUNCEMENT_ADVANCED__FIELD_FEATURED_IMAGE = 'field_627900213071e';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const GRAPHQL_NAME = 'PpworksAnnouncement';
    public const GRAPHQL_PLURAL_NAME = 'PpworksAnnouncements';

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
