<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaePpworks;

use Scm\Entity\CustomPostType;

/**
 * Class which handles registration of custom entities.
 *
 * @psalm-suppress UndefinedClass
 */
class Ppworks extends CustomPostType
{
    public const POST_STATUS__UNFINISHED = 'unfinished';

    public const POST_STATUS__TO_BE_PUBLISHED = 'to_be_published';

    public const TAXONOMY__SHOW = 'ppworks_show';

    public const TAXONOMY__SHOW__SHORT_NAME = 'field_61b0443919050';

    public const TAXONOMY__SHOW__DESCRIPTION = 'field_61b097bdfe637';

    public const TAXONOMY__SHOW__DEFAULT_IMAGE = 'field_62ac2ce6c6122';

    public const TAXONOMY__SHOW__AUDIO_ONLY = 'field_6329a5b453428';

    public const TAXONOMY__SHOW__CUSTOM_SERIES = 'field_6303357d0d03f';

    public const TAXONOMY__SHOW__SUBSCRIBE_VIDEO = 'field_62fdf3580aff0';

    public const TAXONOMY__SHOW__SUBSCRIBE_VIDEO_APPLE = 'field_62fdf4000aff2';

    public const TAXONOMY__SHOW__SUBSCRIBE_VIDEO_GOOGLE = 'field_62fdf42e0aff3';

    public const TAXONOMY__SHOW__SUBSCRIBE_VIDEO_RSS = 'field_62fdf44e0aff4';

    public const TAXONOMY__SHOW__SUBSCRIBE_AUDIO = 'field_62fdf4750aff5';

    public const TAXONOMY__SHOW__SUBSCRIBE_AUDIO_APPLE = 'field_62fdf4750aff6';

    public const TAXONOMY__SHOW__SUBSCRIBE_AUDIO_SPOTIFY = 'field_65f9734a8a332';

    public const TAXONOMY__SHOW__SUBSCRIBE_AUDIO_AMAZON = 'field_66758a7211aa6';

    public const TAXONOMY__SHOW__SUBSCRIBE_AUDIO_PANDORA = 'field_66758a9011aa7';

    public const TAXONOMY__SHOW__SUBSCRIBE_AUDIO_GOOGLE = 'field_62fdf4750aff7';

    public const TAXONOMY__SHOW__SUBSCRIBE_AUDIO_RSS = 'field_62fdf4750aff8';

    /**
     * Returns post types.
     *
     * @return string[]
     */
    public static function postTypes(): array
    {
        return [
            PpworksAnnouncementCT::POST_TYPE,
            PpworksArticleCT::POST_TYPE,
            PpworksEpisodeCT::POST_TYPE,
            PpworksSegmentCT::POST_TYPE,
            PpworksSponsorProgramCT::POST_TYPE,
        ];
    }
}
