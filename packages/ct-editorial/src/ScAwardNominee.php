<?php

/**
 * Editorial class, customizes default Post Type
 *
 * @package   Cra\CtEditorial
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtEditorial;

use Scm\Entity\CustomPostType;

/**
 * Editorial class.
 * @psalm-suppress UndefinedClass
 */
class ScAwardNominee extends CustomPostType
{
    public const POST_TYPE = 'sc_award_nominee';
    public const CATEGORY_FIELD = 'field_627a26d4f3ce1';
    public const PARENT_CATEGORY_FIELD = 'field_627a2c77aab74';
}
