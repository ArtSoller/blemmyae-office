<?php

namespace Cra\CtPeople;

use Scm\Entity\CustomPostType;

/**
 * People class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class PeopleCT extends CustomPostType
{
    public const POST_TYPE = 'people';
    public const GROUP_PEOPLE_ADVANCED__FIELD_FIRST_NAME = 'field_60758478bfc12';
    public const GROUP_PEOPLE_ADVANCED__FIELD_MIDDLE_NAME = 'field_60758497bfc13';
    public const GROUP_PEOPLE_ADVANCED__FIELD_LAST_NAME = 'field_607584a5bfc14';
    public const GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES = 'field_607584c9bfc16';
    public const GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES__SUBFIELD_COMPANY = 'field_607584e8bfc17';
    public const GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES__SUBFIELD_JOB_TITLE = 'field_6075851cbfc18';
    public const GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES__SUBFIELD_JOB_TITLE_TAXONOMY = 'field_61e91c1a07b91';
    public const GROUP_PEOPLE_ADVANCED__FIELD_HEADSHOT = 'field_60758552bfc19';
    public const GROUP_PEOPLE_ADVANCED__FIELD_BIO = 'field_60758589bfc1a';
    public const GROUP_PEOPLE_ADVANCED__FIELD_PHONE = 'field_607585a4bfc1b';
    public const GROUP_PEOPLE_ADVANCED__FIELD_EMAIL = 'field_607585c0bfc1c';
    public const GROUP_PEOPLE_ADVANCED__FIELD_WEBSITE = 'field_65eefef66b2af';
    public const GROUP_PEOPLE_ADVANCED__FIELD_TWITTER = 'field_60758692bfc21';
    public const GROUP_PEOPLE_ADVANCED__FIELD_DISCORD = 'field_65eeff206b2b0';
    public const GROUP_PEOPLE_ADVANCED__FIELD_LINKEDIN = 'field_6075866dbfc20';
    public const GROUP_PEOPLE_ADVANCED__FIELD_INSTAGRAM = 'field_65eeff3b6b2b1';
    public const GROUP_PEOPLE_ADVANCED__FIELD_FACEBOOK = 'field_607586a4bfc22';
    public const GROUP_PEOPLE_ADVANCED__FIELD_MASTODON = 'field_65eeff526b2b2';
    public const GROUP_PEOPLE_ADVANCED__FIELD_BLUESKY = 'field_65eeff5c6b2b3';
    public const GROUP_PEOPLE_ADVANCED__FIELD_THREADS = 'field_65eeff696b2b4';
    public const GROUP_PEOPLE_ADVANCED__FIELD_GITHUB = 'field_65eeff726b2b5';
    public const GROUP_PEOPLE_TAXONOMY__FIELD_TOPIC = 'field_607583ae4f5f5';
    public const GROUP_PEOPLE_TAXONOMY__FIELD_TYPE = 'field_607583f54f5f6';
    public const GROUP_PEOPLE_TAXONOMY__FIELD_SC_AWARD = 'field_62666ecf9fed5';
    public const GROUP_PEOPLE_REFERENCES__FIELD_APPEARANCES_AS_HOST = 'field_61b07b73ad8e1';
    public const GROUP_PEOPLE_REFERENCES__FIELD_APPEARANCES_AS_GUEST = 'field_61b07d0668b19';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_FIRST_NAME = 'field_6570528beb135';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_MIDDLE_NAME = 'field_662a39b3fb8ad';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_LAST_NAME = 'field_657053d0eb136';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_HEADSHOT = 'field_61bac88aa5a9d';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_JOB_TITLE = 'field_61bac78ba5a98';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_COMPANY = 'field_61bac812a5a99';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_BIO = 'field_61bac82ba5a9a';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_SKYPE = 'field_6278ee3297e31';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_WEBSITE = 'field_6278ee1597e30';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_TWITTER = 'field_61bac860a5a9b';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_DISCORD = 'field_6278ee7c97e32';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_LINKEDIN = 'field_61bac870a5a9c';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_INSTAGRAM = 'field_6278eeea97e33';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_FACEBOOK = 'field_65eefe0abf1de';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_MASTODON = 'field_65eefe70bf1df';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_BLUESKY = 'field_65eefe8ebf1e0';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_THREADS = 'field_65eefea8bf1e1';
    public const GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_GITHUB = 'field_6278ef5a97e34';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_FIRST_NAME = 'field_6628c416ffcc1';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_MIDDLE_NAME = 'field_6628c5708c696';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_LAST_NAME = 'field_6628c5808c697';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_ID = 'field_61c971fb8b498';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_BIO = 'field_61c971868b496';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_COMPANY = 'field_61c9717a8b495';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_JOB_TITLE = 'field_61c970898b494';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_PHONE = 'field_61c972448b499';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_EMAIL = 'field_61c972698b49a';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_TWITTER = 'field_61c971b58b497';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_DIRECT_LINK = 'field_61c972b88b49b';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_BIRTH_DATE = 'field_61c9730e8b49c';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_HEADSHOT = 'field_61c9784bec6e4';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_HASH = 'field_62ff76184cebc';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_REGIONS_COLLECTION = 'field_62ff74c54f381';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_REGIONS_COLLECTION__SUBFIELD_SWOOGO_COMMUNITY_REGION = 'field_62ff74e44f382';
    public const GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_REGIONS_COLLECTION__SUBFIELD_SWOOGO_SPEAKER_TYPE = 'field_62ff74e74f383';
    public const GROUP_CSC_PEOPLE_ADVANCED__FIELD_CSC_HUBSPOT_ID = 'field_6319abb088d52';
    public const GROUP_CSC_PEOPLE_ADVANCED__FIELD_CSC_COMPANY = 'field_6319ad4888d54';
    public const GROUP_CSC_PEOPLE_ADVANCED__FIELD_CSC_JOB_TITLE = 'field_6319ac7388d53';
    public const GROUP_CSC_PEOPLE_ADVANCED__FIELD_CSC_HEADSHOT = 'field_6319ad8588d55';
    public const GROUP_CSC_PEOPLE_ADVANCED__FIELD_CSC_PEOPLE_TYPE = 'field_6319ee7d94180';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const GROUP_WEBHOOK_SYNC__FIELD_SOURCE_OF_SYNC = 'field_65f31352dcfc5';
    public const TAXONOMY__JOB_TITLE = 'job_title';
    public const TAXONOMY__PEOPLE_TYPE = 'people_type';
    public const TERM__INDUSTRY_FIGURE__ID = 72347;
    public const TERM__SPEAKER__ID = 72346;
    public const TAXONOMY__COMMUNITY_REGION = 'community_region';
    public const COMMUNITY_REGION__TERM__UNCATEGORIZED = 'Uncategorized';
    public const TAXONOMY__SWOOGO_SPEAKER_TYPE = 'swoogo_speaker_type';
    public const GRAPHQL_NAME = 'Person';
    public const GRAPHQL_PLURAL_NAME = 'People';

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
