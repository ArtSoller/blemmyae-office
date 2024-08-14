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
 * Event Session class.
 * @psalm-suppress UndefinedClass
 */
class Session extends CustomPostType
{
    public const POST_TYPE = 'session';

    public const VENDOR__SWOOGO = 'swoogo';

    public const FIELD__EVENT = 'field_626a5cc09f734';

    public const FIELD__DATETIME = 'field_626a63a708763';

    public const FIELD__ABSTRACT = 'field_626a6cd11bb10';

    public const FIELD__SPEAKERS = 'field_626ba066b59f0';

    public const FIELD__VENDOR = 'field_626a692001155';

    public const FIELD__VENDOR_TYPE = 'field_626a6a1c0115a';

    public const FIELD__LOCATION = 'field_61480ce82ff13';

    public const TAXONOMY__VENDOR_TYPE = 'learning_vendor_type';

    public const VENDOR_TYPE__SWOOGO = 'Swoogo';
}
