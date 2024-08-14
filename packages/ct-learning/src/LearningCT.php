<?php

namespace Cra\CtLearning;

use Scm\Entity\CustomPostType;

/**
 * Learning class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class LearningCT extends CustomPostType
{
    public const POST_TYPE = 'learning';
    public const GROUP_APPLICATION__FIELD_APPLICATION = 'field_634fcdce7572f';
    public const GROUP_APPLICATION__FIELD_APPLICATIONS = 'field_646c59e467bbf';
    public const GROUP_APPLICATION__FIELD_SLUG = 'field_6475be6ffc7a9';
    public const GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR = 'field_608f8eda9ccf7';
    public const GROUP_LEARNING_ADVANCED__FIELD_STATUS = 'field_6148137c2ff22';
    public const GROUP_LEARNING_ADVANCED__FIELD_FEATURED_IMAGE = 'field_60742831f1de9';
    public const GROUP_LEARNING_ADVANCED__FIELD_ABSTRACT = 'field_626a6781b5729';
    public const GROUP_LEARNING_ADVANCED__FIELD_DATE = 'field_60d2b952511e8';
    public const GROUP_LEARNING_ADVANCED__FIELD_DATE__SUBFIELD_START_DATE = 'field_60d2b963511e9';
    public const GROUP_LEARNING_ADVANCED__FIELD_DATE__SUBFIELD_END_DATE = 'field_60d2b9d4511ea';
    public const GROUP_LEARNING_ADVANCED__FIELD_SPEAKERS = 'field_61480b5e11631';
    public const GROUP_LEARNING_ADVANCED__FIELD_SPEAKERS__SUBFIELD_SPEAKER = 'field_61480b8811632';
    public const GROUP_LEARNING_ADVANCED__FIELD_SPEAKERS__SUBFIELD_NAME = 'field_61480bcd11633';
    public const GROUP_LEARNING_ADVANCED__FIELD_SPEAKERS__SUBFIELD_JOB_TITLE = 'field_61480bf511634';
    public const GROUP_LEARNING_ADVANCED__FIELD_SPEAKERS__SUBFIELD_COMPANY = 'field_61480c0211635';
    public const GROUP_LEARNING_ADVANCED__FIELD_SPEAKERS__SUBFIELD_BIO = 'field_61480c3011636';
    public const GROUP_LEARNING_ADVANCED__FIELD_SPEAKERS__SUBFIELD_HEADSHOT = 'field_61480c3d11637';
    public const GROUP_LEARNING_ADVANCED__FIELD_SPEAKERS__SUBFIELD_LINK = 'field_61480c6011638';
    public const GROUP_LEARNING_ADVANCED__FIELD_COMPANY = 'field_6075619144708';
    public const GROUP_LEARNING_ADVANCED__FIELD_AGENDA = 'field_61480a1222b73';
    public const GROUP_LEARNING_ADVANCED__FIELD_REGISTRATION_FIELDS = 'field_609a58d96794d';
    public const GROUP_LEARNING_ADVANCED__FIELD_VENDOR = 'field_609a5a0f6794e';
    public const GROUP_LEARNING_ADVANCED__FIELD_VENDOR_TYPE = 'field_61e93cf932771';
    public const GROUP_LEARNING_ADVANCED__FIELD_CISO_COMMUNITY_REGION = 'field_6273c7ae04af1';
    public const GROUP_LEARNING_ADVANCED__FIELD_CSC_EVENT_FIELDS = 'field_6319b0346c5b1';
    public const GROUP_LEARNING_ADVANCED__FIELD_CSC_EVENT_FIELDS__SUBFIELD_EXTERNAL_ID = 'field_6319b7dd0dd84';
    public const GROUP_LEARNING_ADVANCED__FIELD_CSC_EVENT_FIELDS__SUBFIELD_IS_PAST = 'field_6319b1e76c5b5';
    public const GROUP_LEARNING_ADVANCED__FIELD_CSC_EVENT_FIELDS__SUBFIELD_REGISTRATION_URL = 'field_6322d3e445054';
    public const GROUP_LEARNING_ADVANCED__FIELD_CSC_EVENT_FIELDS__SUBFIELD_LEAD_BY = 'field_6319b1316c5b3';
    public const GROUP_LEARNING_ADVANCED__FIELD_CSC_EVENT_FIELDS__SUBFIELD_QUARTER = 'field_6319b18c6c5b4';
    public const GROUP_LEARNING_ADVANCED__FIELD_CSC_EVENT_FIELDS__SUBFIELD_SPEAKER_HUBSPOT_IDS = 'field_6322d57b45055';
    public const GROUP_LEARNING_ADVANCED__FIELD_ASK_A_QUESTION_FORM_LINK = 'field_641ae597edf70';
    public const GROUP_LEARNING_ADVANCED__FIELD_ORGANIZER_DETAILS = 'field_65aa7d830f8ce';
    public const GROUP_LEARNING_ADVANCED__FIELD_ORGANIZER_DETAILS__SUBFIELD_NAME = 'field_65aa7d990f8cf';
    public const GROUP_LEARNING_ADVANCED__FIELD_ORGANIZER_DETAILS__SUBFIELD_PHONE = 'field_65aa7dbd0f8d0';
    public const GROUP_LEARNING_ADVANCED__FIELD_ORGANIZER_DETAILS__SUBFIELD_EMAIL = 'field_65aa7dc70f8d1';
    public const GROUP_LEARNING_ADVANCED__FIELD_ORGANIZER_DETAILS__SUBFIELD_WEBSITE = 'field_65aa7dcd0f8d2';
    public const GROUP_PUBLISHING_OPTIONS__FIELD_UNPUBLISH_DATE = 'field_61af47d3a014d';
    public const GROUP_LEARNING_LOCATION__FIELD_LOCATION = 'field_61480ce82ff13';
    public const GROUP_LEARNING_LOCATION__FIELD_LOCATION__SUBFIELD_URL = 'field_61480edf2ff19';
    public const GROUP_LEARNING_LOCATION__FIELD_LOCATION__SUBFIELD_PHONE = 'field_52au45bl2iv3d';
    public const GROUP_LEARNING_LOCATION__FIELD_LOCATION__SUBFIELD_ADDRESS = 'field_61480eef2ff1a';
    public const GROUP_LEARNING_LOCATION__FIELD_LOCATION__SUBFIELD_MAP = 'field_614813512ff21';
    public const GROUP_LEARNING_PEOPLE__FIELD_PERSON = 'field_60742986cfe2a';
    public const GROUP_LEARNING_PEOPLE__FIELD_PERSON__SUBFIELD_FULL_NAME = 'field_607429b5cfe2b';
    public const GROUP_LEARNING_PEOPLE__FIELD_PERSON__SUBFIELD_TITLE = 'field_607429c0cfe2c';
    public const GROUP_LEARNING_PEOPLE__FIELD_PERSON__SUBFIELD_BIO = 'field_607429dccfe2d';
    public const GROUP_LEARNING_PEOPLE__FIELD_PERSON__SUBFIELD_HEADSHOT = 'field_60742a04cfe2e';
    public const GROUP_LEARNING_TAXONOMY__FIELD_LEARNING_TYPE = 'field_607414edd514e';
    public const GROUP_LEARNING_TAXONOMY__FIELD_TOPIC = 'field_607415300ff19';
    public const GROUP_LEARNING_TAXONOMY__FIELD_BRAND = 'field_60e7d2c7c2ab5';
    public const GROUP_META__FIELD_TITLE = 'field_61168e54ff82e';
    public const GROUP_META__FIELD_DESCRIPTION = 'field_61168e6aff82f';
    public const GROUP_META__FIELD_IMAGE = 'field_628b5738d6c15';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const VENDOR__SWOOGO = 'swoogo';
    public const VENDOR__GOTOWEBINAR = 'gotowebinar';
    public const VENDOR_TYPE__SWOOGO = 'Swoogo';
    public const VENDOR_TYPE__MSSP = 'Mssp';
    public const VENDOR_TYPE__CE2E = 'Ce2e';
    public const VENDOR_TYPE_GO_TO_WEBINAR = 'GoToWebinar';
    public const TERM_UNCATEGORIZED = 'Uncategorized';
    public const BRAND__TERM__CSF = 'Cybersecurity Collaboration Forum';
    public const TAXONOMY__COMMUNITY_REGION = 'community_region';
    public const TAXONOMY__TOPIC = 'topic';
    public const TAXONOMY__LEARNING_TYPE = 'learning_type';
    public const TAXONOMY__VENDOR_TYPE = 'learning_vendor_type';
    public const TAXONOMY__BRAND = 'brand';
    public const VENDOR__EXTERNAL_EVENT = 'external_event';
    public const GRAPHQL_NAME = 'Learning';
    public const GRAPHQL_PLURAL_NAME = 'Learnings';

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
