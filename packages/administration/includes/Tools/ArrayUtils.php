<?php

/**
 * ArrayUtils
 *
 * @author Nikita Sokolskii (n_sokolskiy@dotwrk.com)
 */

namespace Scm\Tools;

/**
 * `Array` class name is reserved, thus the name
 */
class ArrayUtils
{
    /**
     * Reorder elements in array
     *
     * $config structure:
     * [
     *   'itemToPrependTo' => 'itemToMove',
     * ]
     *
     * @param array $config
     * @param array $array
     * @return array
     */
    public static function arrayReorderElements(array $config, array $array): array
    {
        $array = array_diff($array, array_values($config));

        return self::arrayInsertElements($config, $array);
    }

    /**
     * Inserts elements before items in array. If item to prepend to
     * is not found, inserts at the end of the array.
     *
     * $config structure if ordinary array:
     * [
     *   'itemToPrependTo' => 'itemToInsert',
     *   ...
     * ]
     *
     * $config structure if associative array:
     * [
     *   'itemToPrependToKey' => [
     *     'itemToInsertKey' => 'ItemToInsertValue'
     *   ]
     * ]
     *
     * @param array $config
     * @param array $array
     * @param bool $isAssociativeArray
     * @return array
     */
    public static function arrayInsertElements(array $config, array $array, bool $isAssociativeArray = false): array
    {
        foreach ($config as $itemToPrependTo => $itemToInsert) {
            $itemToPrependToIndex = array_search(
                $itemToPrependTo,
                $isAssociativeArray ? array_keys($array) : $array,
                true
            );

            if (!$itemToPrependToIndex) {
                $array [] = $itemToInsert;
                continue;
            }

            $array = array_merge(
                array_slice($array, 0, $itemToPrependToIndex),
                $isAssociativeArray ? $itemToInsert : [$itemToInsert],
                array_slice($array, $itemToPrependToIndex)
            );
        }

        return $array;
    }
}
