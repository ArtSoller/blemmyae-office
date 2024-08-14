<?php

namespace Cra\CtCompanyProfile;

use Scm\Entity\CustomPostType;

/**
 * CompanyProfile class.
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
final class CompanyProfileCT extends CustomPostType
{
    public const POST_TYPE = 'company_profile';
    public const GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR = 'field_608f8eda9ccf7';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_COMPANY_NAME = 'field_60752a19018b6';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_ABOUT = 'field_60752cb0018ba';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_PARENT_COMPANY = 'field_60752d60018bf';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_CHILD_COMPANIES = 'field_60752db7018c0';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_PRODUCTS = 'field_60753dce0feb6';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_LOGO = 'field_60753a8f1daea';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_WEBSITE_URL = 'field_60753aaf1daeb';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_EMAIL = 'field_6490324dfcec1';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_PHONE = 'field_64903272fcec2';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_LEGAL_NAME = 'field_60752a3b018b7';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_HEADQUARTERS = 'field_60752a49018b8';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_CEO = 'field_6075317b1dadf';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_CMO = 'field_607532351dae0';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_DATE_FOUNDED = 'field_60752c64018b9';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_ACQUIRED_DATE = 'field_60752e95018c1';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_STATE = 'field_60752cdc018bb';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_REASON_CLOSED = 'field_60752d23018bc';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_EMPLOYEE_COUNT = 'field_60752d36018bd';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_SUBSIDIARY_STATUS = 'field_60752d47018be';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_PURE_PLAY = 'field_607532601dae1';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_INCORPORATED_LOCATION = 'field_6075327a1dae2';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_PUBLIC_FILING_DATE = 'field_6075329b1dae3';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_PUBLIC_MARKET = 'field_607532c71dae4';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_PUBLIC_SYMBOL = 'field_607532d31dae5';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_FUNDING_ROUND = 'field_607532de1dae6';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_TOTAL_FUNDING = 'field_607533081dae7';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_REVENUE = 'field_60753a371dae8';
    public const GROUP_COMPANY_PROFILE_ADVANCED__FIELD_REVENUE_GROWTH = 'field_60753a561dae9';
    public const GROUP_COMPANY_PROFILE_TAXONOMY__FIELD_TYPE = 'field_6075281b01146';
    public const GROUP_COMPANY_PROFILE_TAXONOMY__FIELD_SC_AWARD = 'field_62666ecf9fed6';
    public const GROUP_PPWORKS_COMPANY_ADVANCED__FIELD_PPWORKS_ABOUT = 'field_61bac987b6bb7';
    public const GROUP_PPWORKS_COMPANY_ADVANCED__FIELD_PPWORKS_LOGO = 'field_61bac9a5b6bb8';
    public const GROUP_PPWORKS_COMPANY_ADVANCED__FIELD_PPWORKS_URL = 'field_61bac9d0b6bb9';
    public const GROUP_SWOOGO_COMPANY_ADVANCED__FIELD_SWOOGO_NAME = 'field_6628c52740f52';
    public const GROUP_SWOOGO_COMPANY_ADVANCED__FIELD_SWOOGO_ID = 'field_61caadb77a2be';
    public const GROUP_SWOOGO_COMPANY_ADVANCED__FIELD_SWOOGO_ABOUT = 'field_61caad387a2bd';
    public const GROUP_SWOOGO_COMPANY_ADVANCED__FIELD_SWOOGO_URL = 'field_61caadd57a2bf';
    public const GROUP_SWOOGO_COMPANY_ADVANCED__FIELD_SWOOGO_DIRECT_LINK = 'field_61caae057a2c0';
    public const GROUP_SWOOGO_COMPANY_ADVANCED__FIELD_SWOOGO_LOGO = 'field_61caae787a2c1';
    public const GROUP_SWOOGO_COMPANY_ADVANCED__FIELD_SWOOGO_WHITEPAPER = 'field_626b81f572a9b';
    public const GROUP_META__FIELD_TITLE = 'field_61168e54ff82e';
    public const GROUP_META__FIELD_DESCRIPTION = 'field_61168e6aff82f';
    public const GROUP_META__FIELD_IMAGE = 'field_628b5738d6c15';
    public const GROUP_FLAGS__FIELD_FLAGS = 'field_625e3ee9b4a20';
    public const GROUP_WEBHOOK_SYNC__FIELD_SOURCE_OF_SYNC = 'field_65f31352dcfc5';
    public const TAXONOMY__COMPANY_PROFILE_TYPE = 'company_profile_type';
    public const GRAPHQL_NAME = 'CompanyProfile';
    public const GRAPHQL_PLURAL_NAME = 'CompanyProfiles';

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
