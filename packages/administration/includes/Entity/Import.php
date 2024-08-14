<?php

/**
 * Import.
 *
 * @author Alexander Kucherov <avdkucherov@gmail.com>
 */

namespace Scm\Entity;

/**
 * @todo: Update description.
 *
 * Interface Import
 * @package Scm\Entity
 */
interface Import
{
    /**
     * @param array $data
     * @return bool
     * @todo: Update description.
     *
     */
    public static function import(array $data): bool;
}
