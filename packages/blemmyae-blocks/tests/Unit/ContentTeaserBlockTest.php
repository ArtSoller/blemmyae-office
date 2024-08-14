<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Tests\Unit;

use Cra\BlemmyaeBlocks\Block\BlockFactory;
use Cra\BlemmyaeBlocks\BlockImplementations\CtLanding\ContentTeaserBlock;
use Cra\BlemmyaeBlocks\Tests\Utility\BlockMocksFactory;
use PHPUnit\Framework\TestCase;

class ContentTeaserBlockTest extends TestCase
{
    public function blockParametersProvider(): array
    {
        $blockMockData = BlockMocksFactory::createMockBlockData();

        return [
            /*
             * Verify that init data is calculated correctly
             */
            'emptyBlock' =>
                [
                    [
                        'blockParams' => $blockMockData['emptyBlock']
                    ],
                    [
                        'pageOffset' => 0,
                        'nativePageOffset' => 0,
                        'nativeAdTopics' => [],
                        'postType' => [],
                        'initialNumberOfItems' => 10,
                        'taxonomyQuery' => [],
                    ],
                ],
            /*
             * Verify that init data, if present, is mapped correctly
             */
            'blockWithData' =>
                [
                    [
                        'blockParams' => $blockMockData['blockWithData']
                    ],
                    [
                        'pageOffset' => 15,
                        'nativePageOffset' => 5,
                        'nativeAdTopics' => [
                            (object) [
                                'slug' => 'ransomware',
                            ],
                        ],
                        'postType' => ['editorial'],
                        'initialNumberOfItems' => 10,
                        'taxonomyQuery' => [
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

        if ($block instanceof ContentTeaserBlock) {
            $this->assertSame(
                $block->pageOffset,
                $expectedMapping['pageOffset'],
                'Test that pageOffset is calculated properly'
            );
            $this->assertSame(
                $block->nativePageOffset,
                $expectedMapping['nativePageOffset'],
                'Test that nativePageOffset is calculated properly'
            );
            $this->assertSame(
                $block->nativePageOffset + $block->pageOffset,
                $block->initialNumberOfItems * ($block->page - 1),
                'Test that values calculated for offset based pagination are correct for page value'
            );
            $this->assertEqualsCanonicalizing(
                $block->nativeAdTopics,
                $expectedMapping['nativeAdTopics'],
                'Test that nativeAdTopics is mapped properly'
            );
            $this->assertSame(
                $block->postType,
                $expectedMapping['postType'],
                'Test that postType is mapped properly'
            );
            $this->assertSame(
                $block->initialNumberOfItems,
                $expectedMapping['initialNumberOfItems'],
                'Test that initialNumberOfItems is preserved'
            );
            $this->assertSame(
                $block->taxonomyQuery,
                $expectedMapping['taxonomyQuery'],
                'Test that taxonomyQuery is mapped properly'
            );
        }
    }
}
