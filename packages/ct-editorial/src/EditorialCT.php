<?php

namespace Cra\CtEditorial;

use Scm\Entity\CustomPostType;

/**
 * Editorial class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class EditorialCT extends CustomPostType
{
    public const POST_TYPE = 'editorial';
    public const GROUP_APPLICATION__FIELD_APPLICATION = 'field_634fcdce7572f';
    public const GROUP_APPLICATION__FIELD_APPLICATIONS = 'field_646c59e467bbf';
    public const GROUP_APPLICATION__FIELD_SLUG = 'field_6475be6ffc7a9';
    public const GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR = 'field_608f8eda9ccf7';
    public const GROUP_EDITORIAL_ADVANCED__FIELD_SITE_TITLE = 'field_6065ae2ddc538';
    public const GROUP_EDITORIAL_ADVANCED__FIELD_DECK = 'field_6065ae75dc539';
    public const GROUP_EDITORIAL_ADVANCED__FIELD_FEATURED_IMAGE = 'field_6066d54dac469';
    public const GROUP_EDITORIAL_ADVANCED__FIELD_FEATURED_IMAGE_CAPTION = 'field_61924b6e1912d';
    public const GROUP_EDITORIAL_ADVANCED__FIELD_SHOW_FEATURED_IMAGE = 'field_61a5cb6aa53eb';
    public const GROUP_EDITORIAL_ADVANCED__FIELD_HIDE_AUTHOR = 'field_62835a3a45861';
    public const GROUP_EDITORIAL_ADVANCED__FIELD_RELATED_BLOCK = 'field_625522550cb54';
    public const GROUP_EDITORIAL_ADVANCED__FIELD_RELATED_BLOCK__SUBFIELD_TITLE = 'field_6255232a0cb55';
    public const GROUP_EDITORIAL_ADVANCED__FIELD_RELATED_BLOCK__SUBFIELD_POST_TYPE = 'field_625523690cb56';
    public const GROUP_EDITORIAL_ADVANCED__FIELD_RELATED_BLOCK__SUBFIELD_TAXONOMY_QUERY = 'field_625523ca0cb57';
    public const GROUP_EDITORIAL_ADVANCED__FIELD_RELATED_BLOCK__SUBFIELD_NUMBER_OF_ITEMS = 'field_625526841fb49';
    public const GROUP_EDITORIAL_ADVANCED__FIELD_RELATED_BLOCK__SUBFIELD_COLLECTION = 'field_625526c91fb4a';
    public const GROUP_EDITORIAL_ADVANCED__FIELD_RELATED_BLOCK__SUBFIELD_NATIVE_AD_FREQUENCY = 'field_62552913176e8';
    public const GROUP_EDITORIAL_ADVANCED__FIELD_RELATED_BLOCK__SUBFIELD_OPTIONS = 'field_6255295a176e9';
    public const GROUP_EDITORIAL_TAXONOMY__FIELD_TYPE = 'field_60654eeddc40e';
    public const GROUP_EDITORIAL_TAXONOMY__FIELD_PODCAST_SHOW = 'field_60e6df4192782';
    public const GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC = 'field_6066d31112bc0';
    public const GROUP_EDITORIAL_TAXONOMY__FIELD_PARENT_TOPIC = 'field_6257b772835fd';
    public const GROUP_EDITORIAL_TAXONOMY__FIELD_INDUSTRY = 'field_60655ab51908d';
    public const GROUP_EDITORIAL_TAXONOMY__FIELD_BRAND = 'field_60655b3f1908e';
    public const GROUP_EDITORIAL_TAXONOMY__FIELD_REGION = 'field_6065761244f70';
    public const GROUP_EDITORIAL_TAXONOMY__FIELD_SC_AWARD = 'field_62666ecf9fed4';
    public const GROUP_EDITORIAL_TAXONOMY__FIELD_YEAR = 'field_63999ef9de607';
    public const GROUP_BRIEF_ADVANCED__FIELD_VENDOR = 'field_60d5afe17d694';
    public const GROUP_PRODUCT_TEST_ADVANCED__FIELD_REVIEW = 'field_60d61cc72374f';
    public const GROUP_SPONSOR__FIELD_SPONSORS = 'field_613054fb8ff7e';
    public const GROUP_META__FIELD_TITLE = 'field_61168e54ff82e';
    public const GROUP_META__FIELD_DESCRIPTION = 'field_61168e6aff82f';
    public const GROUP_META__FIELD_IMAGE = 'field_628b5738d6c15';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const EDITORIAL_TYPE_TAXONOMY = 'editorial_type';
    public const GRAPHQL_NAME = 'Editorial';
    public const GRAPHQL_PLURAL_NAME = 'Editorials';

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
