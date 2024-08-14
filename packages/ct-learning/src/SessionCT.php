<?php

namespace Cra\CtLearning;

use Scm\Entity\CustomPostType;

/**
 * Session class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class SessionCT extends CustomPostType
{
    public const POST_TYPE = 'session';
    public const GROUP_APPLICATION__FIELD_APPLICATION = 'field_634fcdce7572f';
    public const GROUP_APPLICATION__FIELD_APPLICATIONS = 'field_646c59e467bbf';
    public const GROUP_APPLICATION__FIELD_SLUG = 'field_6475be6ffc7a9';
    public const GROUP_SESSION_ADVANCED__FIELD_EVENT = 'field_626a5cc09f734';
    public const GROUP_SESSION_ADVANCED__FIELD_DATE_TIME = 'field_626a63a708763';
    public const GROUP_SESSION_ADVANCED__FIELD_DATE_TIME__SUBFIELD_START_DATE_TIME = 'field_626b9f69b59ee';
    public const GROUP_SESSION_ADVANCED__FIELD_DATE_TIME__SUBFIELD_END_DATE_TIME = 'field_626b9f8db59ef';
    public const GROUP_SESSION_ADVANCED__FIELD_DATE_TIME__SUBFIELD_CUSTOM_DATE_TIME = 'field_62836c51fa6ff';
    public const GROUP_SESSION_ADVANCED__FIELD_ABSTRACT = 'field_626a6cd11bb10';
    public const GROUP_SESSION_ADVANCED__FIELD_SPEAKERS = 'field_626ba066b59f0';
    public const GROUP_SESSION_ADVANCED__FIELD_VENDOR = 'field_626a692001155';
    public const GROUP_SESSION_ADVANCED__FIELD_VENDOR_TYPE = 'field_626a6a1c0115a';
    public const GROUP_LEARNING_LOCATION__FIELD_LOCATION = 'field_61480ce82ff13';
    public const GROUP_LEARNING_LOCATION__FIELD_LOCATION__SUBFIELD_URL = 'field_61480edf2ff19';
    public const GROUP_LEARNING_LOCATION__FIELD_LOCATION__SUBFIELD_PHONE = 'field_52au45bl2iv3d';
    public const GROUP_LEARNING_LOCATION__FIELD_LOCATION__SUBFIELD_ADDRESS = 'field_61480eef2ff1a';
    public const GROUP_LEARNING_LOCATION__FIELD_LOCATION__SUBFIELD_MAP = 'field_614813512ff21';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const VENDOR__SWOOGO = 'swoogo';
    public const VENDOR_TYPE__SWOOGO = 'Swoogo';
    public const TAXONOMY__VENDOR_TYPE = 'learning_vendor_type';
    public const GRAPHQL_NAME = 'Session';
    public const GRAPHQL_PLURAL_NAME = 'Sessions';

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
