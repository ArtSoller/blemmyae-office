<?php

/**
 * CompanyProfile class, defines Company Profile post type
 *
 * @package   Cra\CtCompanyProfile
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtCompanyProfile;

use Scm\Entity\CustomPostType;

/**
 * @deprecated
 * CompanyProfile class.
 * @psalm-suppress UndefinedClass
 */
class CompanyProfile extends CustomPostType
{
    public const POST_TYPE = 'company_profile';

    public const FIELD__COMPANY_NAME = 'field_60752a19018b6';

    public const FIELD__AUTHOR = 'field_608f8eda9ccf7';

    public const FIELD__ABOUT = 'field_60752cb0018ba';

    public const FIELD__LOGO = 'field_60753a8f1daea';

    public const FIELD__PHONE = 'field_64903272fcec2';

    public const FIELD__EMAIL = 'field_6490324dfcec1';

    public const FIELD__WEBSITE_URL = 'field_60753aaf1daeb';

    public const FIELD__TYPE = 'field_6075281b01146';

    public const TAXONOMY__COMPANY_PROFILE_TYPE = 'company_profile_type';

    public const FIELD__SWOOGO_ID = 'field_61caadb77a2be';

    public const FIELD__SWOOGO_ABOUT = 'field_61caad387a2bd';

    public const FIELD__SWOOGO_URL = 'field_61caadd57a2bf';

    public const FIELD__SWOOGO_DIRECT_LINK = 'field_61caae057a2c0';

    public const FIELD__SWOOGO_LOGO = 'field_61caae787a2c1';

    public const FIELD__SWOOGO_WHITEPAPER = 'field_626b81f572a9b';

    public const SPONSOR__FIELD__SPONSORS = 'field_613054fb8ff7e';
}
