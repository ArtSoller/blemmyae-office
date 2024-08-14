<?php

/**
 * Newsletter class, defines Newsletter post type
 *
 * @package   Cra\CtNewsletter
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtNewsletter;

use Scm\Entity\CustomPostType;

/**
 * @deprecated
 * Newsletter class.
 *
 * @psalm-suppress UndefinedClass
 */
class Newsletter extends CustomPostType
{
    public const POST_TYPE = 'newsletter';

    public const GENERATE_NEWSLETTER_POST_ID = 'options';

    public const FIELD_POSTS = 'field_606edc8077a94';

    public const FIELD_SUBJECT = 'field_6156a99a2f396';

    public const FIELD_SCHEDULE_DATE = 'field_6156aa142f397';

    public const FIELD_NEWSLETTER_TYPE = 'field_606edbd026195';

    public const FIELD_REPEATER_POST = 'field_606edda277a9a';

    public const FIELD_APP = 'field_634fcdce7572f';
}
