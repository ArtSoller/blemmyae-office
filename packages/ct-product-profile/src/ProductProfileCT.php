<?php

namespace Cra\CtProductProfile;

use Scm\Entity\CustomPostType;

/**
 * ProductProfile class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class ProductProfileCT extends CustomPostType
{
    public const POST_TYPE = 'product_profile';
    public const GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR = 'field_608f8eda9ccf7';
    public const GROUP_PRODUCT_PROFILE_ADVANCED__FIELD_COMPANY_NAME = 'field_60753c290373a';
    public const GROUP_PRODUCT_PROFILE_ADVANCED__FIELD_PRODUCT_NAME = 'field_60753c920373b';
    public const GROUP_PRODUCT_PROFILE_ADVANCED__FIELD_PRODUCT_DESCRIPTION = 'field_60753caf0373c';
    public const GROUP_PRODUCT_PROFILE_ADVANCED__FIELD_LOGO = 'field_60d62125dd46c';
    public const GROUP_PRODUCT_PROFILE_ADVANCED__FIELD_LICENSING_MODEL = 'field_60753ccb0373d';
    public const GROUP_PRODUCT_PROFILE_ADVANCED__FIELD_PRICING = 'field_60753cef0373e';
    public const GROUP_PRODUCT_PROFILE_ADVANCED__FIELD_PLATFORM_SUPPORT = 'field_60753d010373f';
    public const GROUP_PRODUCT_PROFILE_ADVANCED__FIELD_MGMT_ARCHITECTURE = 'field_60753d1303740';
    public const GROUP_PRODUCT_PROFILE_ADVANCED__FIELD_PERCENT_OF_REVENUE = 'field_60753d1e03741';
    public const GROUP_PRODUCT_PROFILE_ADVANCED__FIELD_PRODUCT_GROWTH = 'field_60753d2b03742';
    public const GROUP_PRODUCT_PROFILE_ADVANCED__FIELD_CUSTOMER_COUNT = 'field_60753d3b03743';
    public const GROUP_PRODUCT_PROFILE_ADVANCED__FIELD_UNIT_COUNT = 'field_60753d4b03744';
    public const GROUP_PRODUCT_PROFILE_ADVANCED__FIELD_GENERAL_AVAILABILITY_DATE = 'field_60753d5303745';
    public const GROUP_PRODUCT_PROFILE_ADVANCED__FIELD_GTM_STRATEGY = 'field_60753d7c03746';
    public const GROUP_PRODUCT_PROFILE_TAXONOMY__FIELD_CATEGORIES = 'field_60753ba5b303b';
    public const GROUP_PRODUCT_PROFILE_TAXONOMY__FIELD_SC_AWARD = 'field_62666ecf9fed7';
    public const GROUP_META__FIELD_TITLE = 'field_61168e54ff82e';
    public const GROUP_META__FIELD_DESCRIPTION = 'field_61168e6aff82f';
    public const GROUP_META__FIELD_IMAGE = 'field_628b5738d6c15';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const GRAPHQL_NAME = 'ProductProfile';
    public const GRAPHQL_PLURAL_NAME = 'ProductProfiles';

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
