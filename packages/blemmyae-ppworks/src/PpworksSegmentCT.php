<?php

namespace Cra\BlemmyaePpworks;

use Scm\Entity\CustomPostType;

/**
 * PpworksSegment class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class PpworksSegmentCT extends CustomPostType
{
    public const POST_TYPE = 'ppworks_segment';
    public const GROUP_APPLICATION__FIELD_APPLICATION = 'field_634fcdce7572f';
    public const GROUP_APPLICATION__FIELD_APPLICATIONS = 'field_646c59e467bbf';
    public const GROUP_APPLICATION__FIELD_SLUG = 'field_6475be6ffc7a9';
    public const GROUP_PPWORKS_PODCAST_BASIC__FIELD_FEATURED_IMAGE = 'field_6201fee736c77';
    public const GROUP_PPWORKS_PODCAST_BASIC__FIELD_YOUTUBE_ID = 'field_61af241e88496';
    public const GROUP_PPWORKS_PODCAST_BASIC__FIELD_LIBSYN_VIDEO_ID = 'field_61af255388498';
    public const GROUP_PPWORKS_PODCAST_BASIC__FIELD_LIBSYN_AUDIO_ID = 'field_61af257f88499';
    public const GROUP_PPWORKS_PODCAST_BASIC__FIELD_S3_VIDEO = 'field_61af25928849a';
    public const GROUP_PPWORKS_PODCAST_BASIC__FIELD_S3_AUDIO = 'field_620622974f469';
    public const GROUP_PPWORKS_PODCAST_BASIC__FIELD_TRANSCRIPTION = 'field_62bd7e2c9a61f';
    public const GROUP_PPWORKS_PODCAST_BASIC__FIELD_RAW_TRANSCRIPTION_FILE = 'field_62bd7fb79a620';
    public const GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_SHOW = 'field_62872e658914a';
    public const GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_EPISODE = 'field_61af44321fad5';
    public const GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_TYPE = 'field_61af46c8d96a9';
    public const GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_POSITION = 'field_6257e579fa3f1';
    public const GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_TOPICS = 'field_61af4677d96a7';
    public const GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_TAGS = 'field_61af46a4d96a8';
    public const GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_HOSTS = 'field_61af4624d96a5';
    public const GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_GUESTS = 'field_61af4650d96a6';
    public const GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_ANNOUNCEMENTS = 'field_620b45d46c5f0';
    public const GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_ARTICLES = 'field_620b45ee6c5f1';
    public const GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_GALLERY_IMAGES = 'field_6283672ebae11';
    public const GROUP_PPWORKS_SEGMENT_ADVANCED__FIELD_SPONSOR_PROGRAMS = 'field_649d8ddb5cb4b';
    public const GROUP_SPONSOR__FIELD_SPONSORS = 'field_613054fb8ff7e';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const TAXONOMY__SHOW = 'ppworks_show';
    public const TAXONOMY__SEGMENT = 'ppworks_segment_type';
    public const TAXONOMY__TAG = 'ppworks_tag';
    public const GRAPHQL_NAME = 'PpworksSegment';
    public const GRAPHQL_PLURAL_NAME = 'PpworksSegments';

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
