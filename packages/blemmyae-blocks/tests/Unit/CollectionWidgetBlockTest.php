<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Tests\Unit;

use Cra\BlemmyaeBlocks\Block\BlockFactory;
use Cra\BlemmyaeBlocks\Block\BlockQueue;
use Cra\BlemmyaeBlocks\BlockImplementations\CtLanding\Block;
use PHPUnit\Framework\TestCase;

class CollectionWidgetBlockTest extends TestCase
{
    public function blockParametersProvider(): array
    {
        return [
            /*
             * Verify that if query_array is empty in block config,
             * taxonomyQuery field of Block instance is an empty array
             */
            'emptyTaxArray' =>
                [
                    [
                        'blockParams' => [
                            'number_of_items' => 10,
                            'name' => 'list_with_image',
                            'post_type' => ['editorial'],
                            'taxonomy_query' => [
                                'query_array' => null,
                                'relation' => 'AND',
                            ],
                        ],
                        'path' => [
                            'layouts',
                            '0',
                            'columns',
                            '0',
                            'blocks',
                            '0',
                        ],
                    ],
                    [
                        'taxonomyQuery' => [],
                        'numberOfItems' => 10,
                    ],
                ],
            /*
             * Verify that taxonomy query is mapped correctly
             */
            'nonEmptyTaxArray' =>
                [
                    [
                        'blockParams' => [
                            'number_of_items' => 10,
                            'name' => 'list_with_image',
                            'post_type' => ['editorial'],
                            'taxonomy_query' => [
                                'query_array' => [
                                    [
                                        'terms' => [
                                            'taxonomy' => 'topic',
                                            'slug' => 'device-security',
                                        ],
                                        'operator' => 'IN',
                                    ],
                                ],
                                'relation' => 'AND',
                            ],
                        ],
                        'path' => [
                            'layouts',
                            '0',
                            'columns',
                            '0',
                            'blocks',
                            '1',
                        ],
                    ],
                    [
                        'taxonomyQuery' => [
                            'query_array' => [
                                [
                                    'terms' => [
                                        'taxonomy' => 'topic',
                                        'slug' => 'device-security',
                                    ],
                                    'operator' => 'IN',
                                ],
                            ],
                            'relation' => 'AND',
                        ],
                        'numberOfItems' => 10,
                    ],
                ],
            /*
             * Verify that featured post block, that is configured
             * to have only one post in resolvedPostCollection, will
             * have numberOfItems == 1
             */
            'featuredPostWithoutCollection' =>
                [
                    [
                        'blockParams' => [
                            'name' => 'featured_post',
                            'post_type' => ['editorial'],
                            'taxonomy_query' => [
                                'query_array' => null,
                                'relation' => 'AND',
                            ],
                        ],
                        'path' => [
                            'layouts',
                            '0',
                            'columns',
                            '0',
                            'blocks',
                            '2',
                        ],
                    ],
                    [
                        'taxonomyQuery' => [],
                        'numberOfItems' => 1,
                    ],
                ],
            /*
             * Verify that featured post block, that is configured
             * to have only one post in resolvedPostCollection, will
             * have numberOfItems == 0
             */
            'featuredPostWithCollection' =>
                [
                    [
                        'blockParams' => [
                            'name' => 'featured_post',
                            'post_type' => ['editorial'],
                            'taxonomy_query' => [
                                'query_array' => null,
                                'relation' => 'AND',
                            ],
                            'post' => 342432,
                        ],
                        'path' => [
                            'layouts',
                            '0',
                            'columns',
                            '0',
                            'blocks',
                            '3',
                        ],
                    ],
                    [
                        'taxonomyQuery' => [],
                        'numberOfItems' => 0,
                    ],
                ],
            /*
             * Verify that numberOfItems is calculated correctly depending
             * on the nativeAdFrequency
             */
            'nativeAdsOption1' =>
                [
                    [
                        'blockParams' => [
                            'number_of_items' => 10,
                            'name' => 'list_with_image',
                            'post_type' => ['editorial'],
                            'taxonomy_query' => [
                                'query_array' => null,
                                'relation' => 'AND',
                            ],
                            'options' => ['nativeAds'],
                            'native_ad_frequency' => 4,
                        ],
                        'path' => [
                            'layouts',
                            '0',
                            'columns',
                            '0',
                            'blocks',
                            '4',
                        ],
                    ],
                    [
                        'taxonomyQuery' => [],
                        'numberOfItems' => 8,
                    ],
                ],
            'nativeAdsOption2' =>
                [
                    [
                        'blockParams' => [
                            'number_of_items' => 10,
                            'name' => 'list_with_image',
                            'post_type' => ['editorial'],
                            'taxonomy_query' => [
                                'query_array' => null,
                                'relation' => 'AND',
                            ],
                            'options' => ['nativeAds'],
                            'native_ad_frequency' => 3,
                        ],
                        'path' => [
                            'layouts',
                            '0',
                            'columns',
                            '0',
                            'blocks',
                            '5',
                        ],
                    ],
                    [
                        'taxonomyQuery' => [],
                        'numberOfItems' => 7,
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
        // Empty blockQueue, since collectionWidgetBlock instance requires one for global excludes
        $blockQueue = new BlockQueue();
        $blockFactory = new BlockFactory();
        $block = $blockFactory->createBlock($mock['blockParams']['name'], BlockFactory::$blockTypes['collectionWidget']);
        $block->init($mock['blockParams'], $mock['path'], $blockQueue);
        if ($block instanceof Block) {
            $this->assertSame(
                $block->taxonomyQuery,
                $expectedMapping['taxonomyQuery'],
                'Test that taxonomyQuery is mapped properly'
            );
            $this->assertSame(
                $block->numberOfItems,
                $expectedMapping['numberOfItems'],
                'Test that number of items to resolve corresponds to block params and options'
            );
        }
    }
}
