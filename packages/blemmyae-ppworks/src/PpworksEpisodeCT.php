<?php

namespace Cra\BlemmyaePpworks;

use Scm\Entity\CustomPostType;

/**
 * PpworksEpisode class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class PpworksEpisodeCT extends CustomPostType
{
    public const POST_TYPE = 'ppworks_episode';
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
    public const GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_SHOW = 'field_61af3b40ac983';
    public const GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_VAULT = 'field_64739c019dfa6';
    public const GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_NUMBER = 'field_61b8642d316b2';
    public const GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_TOPICS = 'field_61af3b88ac984';
    public const GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_TAGS = 'field_61af3c1dac985';
    public const GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_SEGMENTS = 'field_61af446c0bbc7';
    public const GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_HOSTS = 'field_61af359bac981';
    public const GROUP_PPWORKS_EPISODE_ADVANCED__FIELD_GUESTS = 'field_61af3a32ac982';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const TAXONOMY__SHOW = 'ppworks_show';
    public const TAXONOMY__TAG = 'ppworks_tag';
    public const GRAPHQL_NAME = 'PpworksEpisode';
    public const GRAPHQL_PLURAL_NAME = 'PpworksEpisodes';

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
