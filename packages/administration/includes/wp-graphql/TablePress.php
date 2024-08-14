<?php

declare(strict_types=1);

namespace Scm\WP_GraphQL;

/**
 * Class Redirects.
 *
 * Adds "redirects" GraphQL field.
 */
class TablePress extends AbstractExtension
{
    public const GRAPHQL_NAME = 'TablepressTable';

    /**
     * Restructure JSON from
     * [["Col1","Col2","Col3"],["Val11","Val12","Val13"],["Val21","Val22","Val23"]]
     * to
     * [
     *  {
     *    "Col1": "Val11",
     *    "Col2": "Val12",
     *    "Col3": "Val13"
     *  },
     *  {
     *    "Col1": "Val21",
     *    "Col2": "Val22",
     *    "Col3": "Val23"
     *  }
     * ]
     *
     * @param array $table
     * @return array
     */
    protected static function restructureTable(array $table): array
    {
        $columnNames = $table[0] ?? null;

        if (!$columnNames) {
            return [];
        }

        // Actual table content - omit first row because it is column names
        $tableContent = array_slice($table, 1);

        return array_map(
            static function (mixed $itemField) use ($columnNames): array {
                $mappedField = [];

                for ($i = 0; $i < count($columnNames); $i++) {
                    /**
                     * Fields that have <objectName>__<objectPropertyName1>,
                     * <objectName>__<objectPropertyName2> titles
                     * are a flattened version of
                     * [
                     *   'objectName' => [
                     *     'objectPropertyName1' => 'value1',
                     *     'objectPropertyName2' => 'value2'
                     *   ]
                     * ] field, the following "unflattens" the object by constructing it
                     */
                    if (str_contains($columnNames[$i], '__')) {
                        [$objectName, $objectPropertyName] = explode('__', $columnNames[$i]);
                        $objectPropertyValue = $itemField[$i];

                        if (!array_key_exists($objectName, $mappedField)) {
                            $mappedField[$objectName] = [];
                        }

                        $mappedField[$objectName][$objectPropertyName] = $objectPropertyValue;

                        continue;
                    }

                    $mappedField[$columnNames[$i]] = json_decode($itemField[$i]) ?? $itemField[$i];
                }

                return $mappedField;
            },
            $tableContent,
        );
    }

    /**
     * @inerhitDoc
     */
    protected function registerFields(): void
    {
        $config = [
            'type' => 'string',
            'description' => 'TablePress raw content resolver',
            'resolve' =>
                static function ($source) {
                    $postContent = json_decode(get_post($source->ID)->post_content);
                    $postContentJsonRestructured = self::restructureTable($postContent);

                    return json_encode($postContentJsonRestructured);
                },
        ];

        register_graphql_field(self::GRAPHQL_NAME, 'rawContent', $config);

        register_graphql_object_type('TablePressPagination', [
            'description' => __("TablePress pagination endpoint", 'administration'),
            'fields' => [
                'hasNextPage' => [
                    'type' => 'boolean',
                    'description' => __('Whether there is a next page', 'administration'),
                ],
                'rows' => [
                    'type' => 'string',
                    'description' => __('Encoded json rows', 'administration'),
                ],
            ],
        ]);

        $config = [
            'type' => 'TablePressPagination',
            'description' => 'TablePress raw content resolver',
            'args' => [
                'databaseId' => [
                    'type' => 'ID',
                    'description' => __('Database Id', 'administration'),
                ],
                'page' => [
                    'type' => 'Integer',
                    'description' => __('Page number', 'administration'),
                ],
                'rowsPerPage' => [
                    'type' => 'Integer',
                    'description' => __('Page size', 'administration'),
                ],
            ],
            'resolve' =>
                static function ($source, $args) {
                    [
                        'page' => $page,
                        'rowsPerPage' => $rowsPerPage,
                        'databaseId' => $databaseId
                    ] = $args;

                    if ($page <= 0 || $rowsPerPage <= 0) {
                        return null;
                    }

                    $postContent = json_decode(get_post($databaseId)->post_content);
                    if (!$postContent) {
                        return null;
                    }
                    $postContentJsonRestructured = self::restructureTable($postContent);

                    $offset = ($page - 1) * $rowsPerPage;

                    $rows = array_slice($postContentJsonRestructured, $offset, $rowsPerPage);

                    return [
                        'rows' => json_encode($rows),
                        'hasNextPage' =>
                            $offset + $rowsPerPage < count($postContentJsonRestructured),
                    ];
                },
        ];

        register_graphql_field('RootQuery', 'tablePressPagination', $config);
    }

    /**
     * @inheritDoc
     */
    protected function registerTypes(): void
    {
        // Intentionally empty - interface requires this method
        // but there are no types to be registered
    }
}
