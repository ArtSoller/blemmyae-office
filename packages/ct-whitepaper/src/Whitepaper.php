<?php

/**
 * Whitepaper class, defines Whitepaper post
 *
 * @package   Cra\CtWhitepaper
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtWhitepaper;

use Scm\Entity\CustomPostType;

/**
 * @deprecated
 * Whitepaper class.
 *
 * @psalm-suppress UndefinedClass
 */
class Whitepaper extends CustomPostType
{
    public const POST_TYPE = 'whitepaper';

    public const HIDDEN_FROM_FEEDS_POST_STATUS = 'hidden_from_feeds';

    public const FIELD_FEATURED_IMAGE = 'field_60758315798bf';

    public const FIELD_VENDOR = 'field_60a21f7043b2f';

    public const FIELD_CONVERTR_CAMPAIGN_ID = 'field_60a2200743b30';

    public const FIELD_CONVERTR_CAMPAIGN_API_KEY = 'field_60a22ca543b31';

    public const FIELD_CONVERTR_LINK_ID = 'field_60a22cf343b33';

    public const FIELD_CONVERTR_DOWNLOAD_LINK = 'field_60a231df74408';

    public const FIELD_CONVERTR_WHITEPAPER_ID = 'field_60a22cfa43b34';

    public const FIELD_CONVERTR_FORM_ID = 'field_60a22cd143b32';

    public const FIELD_CONVERTR_FORM_FIELDS = 'field_60a22d3443b36';

    public const OPTIONS_CONVERTR_SYNC = 'field_60a21c4395303';

    public const OPTIONS_CONVERTR_FORCE_UPDATE = 'field_60a4b96d84981';

    public const OPTIONS_CONVERTR_CAMPAIGN = 'field_60a2100ec3258';

    public const OPTIONS_CONVERTR_CAMPAIGN_NAME = 'field_60a210e9c3259';

    public const OPTIONS_CONVERTR_CAMPAIGN_ID = 'field_60a2114dc325a';

    public const OPTIONS_CONVERTR_CAMPAIGN_API_KEY = 'field_60a21188c325b';

    public const VENDOR__CONVERTR = 'convertr';

    public const VENDOR__INTERNAL_WHITEPAPER = 'internal_whitepaper';

    public const FIELD_WHITEPAPER_FILE_ATTACHMENT = 'field_61f79a5851b08';

    // @todo: move to a better suiting file, it is a field in a non whitepaper-specific field group
    public const FIELD_POST_STATUS = 'field_6474c00a815ec';
}
