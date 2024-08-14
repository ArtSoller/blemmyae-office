<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Tests\Utility;

class BlockMocksFactory
{
    public static function createMockBlockData(string $type = 'contentTeaser'): array
    {
        switch ($type) {
            case 'contentTeaser':
                return [
                    'emptyBlock' =>
                        [
                            'name' => 'list_with_image',
                        ],
                    'blockWithEmptyTaxonomyQuery' =>
                        [
                            'number_of_items' => 10,
                            'name' => 'list_with_image',
                            'post_type' => ['editorial'],
                            'taxonomy_query' => null,
                            'page' => 1,
                        ],
                    'blockWithData' =>
                        [
                            'name' => 'list_with_image',
                            'post_type' => ['editorial'],
                            'options' => ['nonDfpNatives'],
                            'native_ad_frequency' => 4,
                            'native_ad_topics' => ['ransomware'],
                            'number_of_items' => 10,
                            'taxonomy_query' => [
                                'taxArray' => [
                                    [
                                        'field' => 'term_taxonomy_id',
                                        'operator' => 'IN',
                                        'terms' => [100],
                                    ],
                                    [
                                        'field' => 'term_taxonomy_id',
                                        'operator' => 'NOT IN',
                                        'terms' => [200],
                                    ],
                                ],
                                'relation' => 'AND',
                            ],
                            'page' => 3,
                            'exclude' => [],
                        ],
                    'blockWithDataAcfTaxonomyQuery' =>
                        [
                            'name' => 'list_with_image',
                            'post_type' => ['editorial'],
                            'options' => ['nonDfpNatives'],
                            'native_ad_frequency' => 4,
                            'native_ad_topics' => ['ransomware'],
                            'number_of_items' => 10,
                            'taxonomy_query' => [
                                'relation' => 'AND',
                                'query_array' =>
                                    [
                                        [
                                            'terms' =>
                                                [
                                                    (object) [
                                                        'term_id' => 100,
                                                    ],
                                                ],
                                            'operator' => 'IN',
                                        ],
                                        [
                                            'terms' =>
                                                [
                                                    (object) [
                                                        'term_id' => 200,
                                                    ],
                                                ],
                                            'operator' => 'NOT IN',
                                        ],
                                    ],
                            ],
                            'page' => 3,
                            'exclude' => [],
                        ],
                    'blockWithLearningOption' =>
                        [
                            'name' => 'list_with_image',
                            'post_type' => ['editorial'],
                            'options' => ['upcomingEvents'],
                            'number_of_items' => 10,
                            'taxonomy_query' => [
                                'taxArray' => [
                                    [
                                        'field' => 'SLUG',
                                        'operator' => 'IN',
                                        'terms' => ["cloud"],
                                        'taxonomy' => 'TOPIC',
                                    ],
                                ],
                                'relation' => 'AND',
                            ],
                            'page' => 3,
                            'exclude' => [],
                        ],
                    // @todo: Sponsors' 'nonDfpNatives' option currently is not injected during test.
                    // The main native ads logic needs to implement that while testing meta_query for
                    // sponsors. Currently, only author is being tested.
                    'blockWithAuthorMetaQuery' =>
                        [
                            'name' => 'list_with_image',
                            'post_type' => ['editorial'],
                            'author' => (object) ['ID' => 1],
                            'taxonomy_query' => [
                                'taxArray' => [
                                    [
                                        'field' => 'term_taxonomy_id',
                                        'operator' => 'IN',
                                        'terms' => [100],
                                    ],
                                ],
                            ],
                            'page' => 3,
                            'exclude' => [],
                        ],
                ];
            default:
                return [];
        }
    }
}
