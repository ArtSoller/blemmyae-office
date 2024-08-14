<?php

/**
 * Learning class, adds Learning post type
 *
 * @package   Cra\CtLearning
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtLearning;

use Scm\Entity\CustomPostType;

/**
 * @deprecated
 * Learning class.
 * @psalm-suppress UndefinedClass
 */
class Learning extends CustomPostType
{
    public const POST_TYPE = 'learning';

    public const FIELD__STATUS = 'field_6148137c2ff22';

    public const FIELD__FEATURED_IMAGE = 'field_60742831f1de9';

    public const FIELD__DATE = 'field_60d2b952511e8';

    public const FIELD__SPEAKERS = 'field_61480b5e11631';

    public const FIELD__SPEAKERS_SPEAKER = 'field_61480b8811632';

    public const FIELD__SPONSORS = 'field_6075619144708';

    public const FIELD__AGENDA = 'field_61480a1222b73';

    public const FIELD__REGISTRATION_FIELDS = 'field_609a58d96794d';

    public const FIELD__VENDOR = 'field_609a5a0f6794e';

    public const VENDOR__SWOOGO = 'swoogo';

    public const VENDOR__GOTOWEBINAR = 'gotowebinar';

    public const VENDOR__EXTERNAL_EVENT = 'external_event';

    public const FIELD__VENDOR_TYPE = 'field_61e93cf932771';

    public const TAXONOMY__VENDOR_TYPE = 'learning_vendor_type';

    public const FIELD__COMMUNITY_REGION = 'field_6273c7ae04af1';

    public const TAXONOMY__COMMUNITY_REGION = 'community_region';

    public const FIELD__LOCATION = 'field_61480ce82ff13';

    public const FIELD__ORGANIZER_DETAILS = 'field_65aa7d830f8ce';

    public const VENDOR_TYPE__SWOOGO = 'Swoogo';

    public const VENDOR_TYPE__MSSP = 'Mssp';

    public const VENDOR_TYPE__CE2E = 'Ce2e';

    public const VENDOR_TYPE_GO_TO_WEBINAR = 'GoToWebinar';

    public const FIELD__LEARNING_TYPE = 'field_607414edd514e';

    public const TAXONOMY__LEARNING_TYPE = 'learning_type';

    public const FIELD__TOPIC = 'field_607415300ff19';

    public const TAXONOMY__TOPIC = 'topic';

    // 'Uncategorized' term name is common for 'Community Region' and 'Topic' taxonomies.
    public const TERM_UNCATEGORIZED = 'Uncategorized';

    public const FIELD__BRAND = 'field_60e7d2c7c2ab5';

    public const TAXONOMY__BRAND = 'brand';

    public const BRAND__TERM__CSF = 'Cybersecurity Collaboration Forum';
    public const FIELD__META_TITLE = 'field_61168e54ff82e';
    public const FIELD__META_DESCRIPTION = 'field_61168e6aff82f';
}
