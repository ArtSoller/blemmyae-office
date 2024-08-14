<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Tests\Unit;

use Cra\BlemmyaeBlocks\Block\AbstractFeedBlock;
use Cra\BlemmyaeBlocks\Block\BlockFactory;
use Cra\BlemmyaeBlocks\Tests\Utility\BlockMocksFactory;
use Cra\BlemmyaeBlocks\WpQuery\BlockWpQueryManager;
use PHPUnit\Framework\TestCase;

class WpQueryManagerTest extends TestCase
{
    public function blockParametersProvider(): array
    {
        $blockMockData = BlockMocksFactory::createMockBlockData();

        return [
            /*
             * Verify that wpGraphQl variant of wp query args
             * is processed correctly
             */
            'blockWithData' =>
                [
                    [
                        'blockParams' => $blockMockData['blockWithData'],
                    ],
                    [
                        'wpQueryArgs' => [
                            'post_type' => ['editorial'],
                            'post_status' => 'publish',
                            'tax_query' =>
                                [
                                    'relation' => 'AND',
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
                            'meta_query' => [],
                            'offset' => 15,
                            'paged' => 1,
                            'posts_per_page' => 8,
                            'fields' => 'ids',
                            'post__not_in' => [],
                        ],
                    ],
                ],
            /*
             * Verify that wpGraphQl variant of wp query args
             * is processed correctly
             */
            'blockWithDataAcfTaxonomyQuery' =>
                [
                    [
                        'blockParams' => $blockMockData['blockWithDataAcfTaxonomyQuery'],
                    ],
                    [
                        'wpQueryArgs' => [
                            'post_type' => ['editorial'],
                            'post_status' => 'publish',
                            'tax_query' =>
                                [
                                    'relation' => 'AND',
                                    [
                                        'field' => 'term_taxonomy_id',
                                        'terms' => [100],
                                        'operator' => 'IN',
                                    ],
                                    [
                                        'field' => 'term_taxonomy_id',
                                        'terms' => [200],
                                        'operator' => 'NOT IN',
                                    ],

                                ],
                            'meta_query' => [],
                            'offset' => 15,
                            'paged' => 1,
                            'posts_per_page' => 8,
                            'fields' => 'ids',
                            'post__not_in' => [],
                        ],
                    ],
                ],
            /*
             * Verify that for block with empty taxonomy query
             * correct wp query args are generated
             */
            'blockWithEmptyTaxonomyQuery' =>
                [
                    [
                        'blockParams' => $blockMockData['blockWithEmptyTaxonomyQuery'],
                    ],
                    [
                        'wpQueryArgs' => [
                            'post_type' => ['editorial'],
                            'post_status' => 'publish',
                            'tax_query' => [],
                            'meta_query' => [],
                            'offset' => 0,
                            'paged' => 1,
                            'posts_per_page' => 10,
                            'fields' => 'ids',
                            'post__not_in' => [],
                        ],
                    ],
                ],
            /*
             * Will always fail because of the hardcoded timestamp. TBD
             */
            'blockWithLearningOption' =>
                [
                    [
                        'blockParams' => $blockMockData['blockWithLearningOption'],
                    ],
                    [
                        'wpQueryArgs' => [
                            'post_type' => 'learning',
                            'post_status' => 'publish',
                            'posts_per_page' => 10,
                            'paged' => 1,
                            'tax_query' =>
                                [
                                    [
                                        'field' => 'SLUG',
                                        'operator' => 'IN',
                                        'terms' => ['cloud',],
                                        'taxonomy' => 'TOPIC',
                                    ],
                                ],
                            'fields' => 'ids',
                            'meta_query' =>
                                [
                                    [
                                        'key' => 'date_0_start_date',
                                        'value' => '2022-08-19 02:09:19',
                                        'compare' => '>=',
                                        'type' => 'DATE',
                                    ],
                                ],
                            'meta_key' => 'date_0_start_date',
                            'orderby' => 'meta_value',
                            'order' => 'ASC',
                            'post__not_in' => [],
                        ],
                    ],
                ],
            /*
             * Verify that for block with meta_query correct wp query args are generated.
             */
            'blockWithAuthorMetaQuery' =>
                [
                    [
                        'blockParams' => $blockMockData['blockWithAuthorMetaQuery'],
                    ],
                    [
                        'wpQueryArgs' => [
                            'post_type' => ['editorial'],
                            'post_status' => 'publish',
                            'tax_query' => [
                                [
                                    'field' => 'term_taxonomy_id',
                                    'operator' => 'IN',
                                    'terms' => [100],
                                ],
                            ],
                            'meta_query' => [
                                [
                                    'key' => 'author',
                                    'value' => ':"1";',
                                    'compare' => 'LIKE',
                                ],
                            ],
                            'offset' => 20,
                            'paged' => 1,
                            'posts_per_page' => 10,
                            'fields' => 'ids',
                            'post__not_in' => [],
                        ],
                    ],
                ],
        ];
    }

    /**
     * Assertions verify that data maps correctly and that fallbacks are correct
     *
     * @dataProvider blockParametersProvider
     */
    public function testMapping(array $mock, array $expectedMapping): void
    {
        $blockFactory = new BlockFactory();
        $block = $blockFactory->createBlock($mock['blockParams']['name'], BlockFactory::$blockTypes['contentTeaser']);
        $block->init($mock['blockParams']);

        if ($block instanceof AbstractFeedBlock) {
            $wpQueryManager = new BlockWpQueryManager($block);
            $wpQueryArgs = $wpQueryManager->prepareWpQueryArgs();

            $this->assertSame(
                $expectedMapping['wpQueryArgs'],
                $wpQueryArgs,
                'Test that WP Query args are generated correctly'
            );
        }
    }
}
