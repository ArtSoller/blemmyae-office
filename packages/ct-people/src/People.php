<?php

/**
 * People class, defines People post type
 *
 * @package   Cra\CtPeople
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtPeople;

use Scm\Entity\CustomPostType;
use WP_Term;

/**
 * @deprecated
 * People class.
 * @psalm-suppress UndefinedClass
 */
class People extends CustomPostType
{
    public const POST_TYPE = 'people';

    public const FIELD__FIRST_NAME = 'field_60758478bfc12';

    public const FIELD__MIDDLE_NAME = 'field_60758497bfc13';

    public const FIELD__LAST_NAME = 'field_607584a5bfc14';

    public const FIELD__COMPANIES = 'field_607584c9bfc16';

    public const FIELD__COMPANIES__COMPANY = 'field_607584e8bfc17';

    public const FIELD__COMPANIES__JOB_TITLE = 'field_6075851cbfc18';

    public const FIELD__COMPANIES__JOB_TITLE_TAXONOMY = 'field_61e91c1a07b91';

    public const FIELD__HEADSHOT = 'field_60758552bfc19';

    public const FIELD__BIO = 'field_60758589bfc1a';

    public const FIELD__PHONE = 'field_607585a4bfc1b';

    public const FIELD__EMAIL = 'field_607585c0bfc1c';
    public const FIELD__FACEBOOK = 'field_607586a4bfc22';

    public const FIELD__LINKEDIN = 'field_6075866dbfc20';

    public const FIELD__PEOPLE_TYPE = 'field_607583f54f5f6';

    public const FIELD__TWITTER = 'field_60758692bfc21';

    public const FIELD__SWOOGO_ID = 'field_61c971fb8b498';

    public const FIELD__SWOOGO_BIO = 'field_61c971868b496';

    public const FIELD__SWOOGO_COMPANY = 'field_61c9717a8b495';

    public const FIELD__SWOOGO_JOB_TITLE = 'field_61c970898b494';

    public const FIELD__SWOOGO_PHONE = 'field_61c972448b499';

    public const FIELD__SWOOGO_EMAIL = 'field_61c972698b49a';

    public const FIELD__SWOOGO_TWITTER = 'field_61c971b58b497';

    public const FIELD__SWOOGO_DIRECT_LINK = 'field_61c972b88b49b';

    public const FIELD__SWOOGO_BIRTH_DATE = 'field_61c9730e8b49c';

    public const FIELD__SWOOGO_HEADSHOT = 'field_61c9784bec6e4';

    public const FIELD__SWOOGO_SPEAKER_TYPE = 'field_620cd91283cc1';

    public const FIELD__SWOOGO_COMMUNITY_REGION = 'field_620cb9cf34525';

    public const FIELD__SWOOGO_HASH = 'field_62ff76184cebc';

    public const FIELD__SWOOGO_REGIONS_COLLECTION = 'field_62ff74c54f381';

    public const FIELD__SWOOGO_REGIONS_COLLECTION__REGION = 'field_62ff74e44f382';

    public const FIELD__SWOOGO_REGIONS_COLLECTION__SPEAKER_TYPE = 'field_62ff74e74f383';

    public const FIELD__CSC_HUBSPOT_ID = 'field_6319abb088d52';

    public const FIELD__CSC_COMPANY = 'field_6319ad4888d54';

    public const FIELD__CSC_JOB_TITLE = 'field_6319ac7388d53';

    public const FIELD__CSC_HEADSHOT = 'field_6319ad8588d55';

    public const FIELD__CSC_PEOPLE_TYPE = 'field_6319ee7d94180';

    public const FIELD__SPEAKER_COLLECTION = 'field_6148196c8ccce';

    public const TAXONOMY__JOB_TITLE = 'job_title';

    public const TAXONOMY__PEOPLE_TYPE = 'people_type';

    public const PEOPLE_TYPE__TERM__INDUSTRY_FIGURE__ID = 72347;

    public const PEOPLE_TYPE__TERM__SPEAKER__ID = 72346;

    public const TAXONOMY__COMMUNITY_REGION = 'community_region';

    public const COMMUNITY_REGION__TERM__UNCATEGORIZED = 'Uncategorized';

    public const TAXONOMY__SWOOGO_SPEAKER_TYPE = 'swoogo_speaker_type';
}
