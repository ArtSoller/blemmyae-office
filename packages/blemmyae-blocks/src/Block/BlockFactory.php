<?php

/**
 * BlockFactory class. Used to create Block and ContentTeaserBlock
 * instances with needed configs
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Block;

use Cra\BlemmyaeBlocks\BlockImplementations\CtEditorial\RelatedBlock;
use Cra\BlemmyaeBlocks\BlockImplementations\CtLanding\Block;
use Cra\BlemmyaeBlocks\BlockImplementations\CtLanding\ContentTeaserBlock;
use Cra\BlemmyaeBlocks\BlockImplementations\CtLanding\TableWithLinksBlock;
use Cra\BlemmyaeBlocks\Utility;
use Exception;
use Scm\Tools\WpCore;

/**
 * BlockFactory class
 */
class BlockFactory
{
    /**
     * @var array<string, mixed>
     */
    private array $blocksConfig;

    /**
     * List of possible block types
     *
     * @var array<string, string>
     */
    public static array $blockTypes = [
        'collectionWidget' => 'collectionWidget',
        'contentTeaser' => 'contentTeaser',
        'related' => 'related',
    ];

    public function __construct()
    {
        $this->blocksConfig = $this->generateBlocksConfig();
    }

    /**
     * @return array<string, mixed>
     */
    public function getBlocksConfig(): array
    {
        return $this->blocksConfig;
    }

    /**
     * Extract supported post types from acf collection widget config
     *
     * @return array<string, mixed>
     */
    public static function supportedPostTypesFromConfig(): array
    {
        try {
            $jsonContents = file_get_contents(
                WpCore::ABSPATH . 'packages/administration/config/field_group/group_60c1eeb84e108.json'
            );
            if (!$jsonContents) {
                throw new Exception("Unable to load Collection Widget field group config file!");
            }
            $collectionWidgetConfig = json_decode(
                $jsonContents,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Exception $exception) {
            Utility::log("Error occurred while parsing collection widget config - {$exception->getMessage()}");
        }
        $collectionWidgetSubFields = $collectionWidgetConfig['fields'][0]['sub_fields'] ?? [];
        $collectionWidgetLayouts = Utility::findArrayByFieldValue(
            $collectionWidgetSubFields,
            'columns'
        )['sub_fields'][0]['layouts'] ?? [];

        /** @var array<string, mixed> $supportedPostTypes */
        $supportedPostTypes = [];

        foreach ($collectionWidgetLayouts as $layout) {
            $supportedPostTypesField = Utility::findArrayByFieldValue(
                $layout['sub_fields'],
                'post_type'
            )['post_type'] ?? [];
            $supportedPostTypes[$layout['name']] = $supportedPostTypesField;
        }

        return $supportedPostTypes;
    }

    /**
     * List of options that are supported by each type of block.
     * A method instead of property is used to call statically
     * and allow expressions
     *
     * @return array<string, string[]>
     */
    public static function blocksSupportedOptions(): array
    {
        return [
            'list_with_image' => [
                AbstractFeedBlock::$blockOptions['nonDfpNatives'],
                AbstractFeedBlock::$blockOptions['nativeAds'],
                AbstractFeedBlock::$blockOptions['upcomingEvents'],
                AbstractFeedBlock::$blockOptions['onDemandEvents'],
            ],
            'simple_list' => [
                AbstractFeedBlock::$blockOptions['upcomingEvents'],
                AbstractFeedBlock::$blockOptions['onDemandEvents'],
            ],
            'horizontal_list_with_image' => [
                AbstractFeedBlock::$blockOptions['upcomingEvents'],
                AbstractFeedBlock::$blockOptions['onDemandEvents'],
                AbstractFeedBlock::$blockOptions['ongoingEvents'],
            ],
            'simple_list_with_image' => [
                AbstractFeedBlock::$blockOptions['upcomingEvents'],
                AbstractFeedBlock::$blockOptions['onDemandEvents'],
            ],
            'list_of_featured_events' => [
                AbstractFeedBlock::$blockOptions['upcomingEvents'],
                AbstractFeedBlock::$blockOptions['onDemandEvents'],
            ],
            'slideshow_of_events' => [
                AbstractFeedBlock::$blockOptions['upcomingEvents'],
                AbstractFeedBlock::$blockOptions['onDemandEvents'],
            ],
            'featured_list_of_whitepapers' => [
                AbstractFeedBlock::$blockOptions['upcomingEvents'],
                AbstractFeedBlock::$blockOptions['onDemandEvents'],
            ],
            'editorial_related_block' => [
                AbstractFeedBlock::$blockOptions['nonDfpNatives'],
                AbstractFeedBlock::$blockOptions['nativeAds'],
                AbstractFeedBlock::$blockOptions['upcomingEvents'],
                AbstractFeedBlock::$blockOptions['onDemandEvents'],
            ],
        ];
    }

    /**
     * Config for each type of block.
     * blockTypeName is a graphql typename generated by acf.
     * supportedPostTypes - self-explanatory
     * graphqlName - camelCase writing of block type
     * numberOfItems(optional) - used as a fallback when number of posts
     * field does not exist for the block type - featured_post, for example.
     *
     * @return array<string, array<string, mixed>>
     */
    public function generateBlocksConfig(): array
    {
        $parsedBlocksConfig = self::supportedPostTypesFromConfig();

        $defaultSupportedPostTypes = [
            Utility::$postTypesMap['editorial'],
            Utility::$postTypesMap['whitepaper'],
            Utility::$postTypesMap['landing'],
            Utility::$postTypesMap['learning'],
            Utility::$postTypesMap['ppworks_episode'],
            Utility::$postTypesMap['ppworks_segment'],
        ];

        /**
         * Config for each block supported by blemmyae-blocks
         * [
         *   'block_name' => [
         *     'blockTypeName' => GrqphQL typename of a block, the way it is in the schema
         *     'supportedPostType' => Array of post types resolving which this block supports
         *     'graphqlName' => GralhQL name of a block - block_name -> BlockName
         *     'numberOfItems' => Predefined number of items that this block will resolve -
         *       number_of_items value from the block takes precedence, if missing - this
         *       value will be used
         *     'postType' => Array of post types in WordPress format -
         *       post_type from the block takes precedence, if missing - this value will be used
         *   ],
         *   ...,
         * ]
         */
        return [
            'simple_list' => [
                'blockTypeName' =>
                    'Landing_Collectionwidget_layouts_columns_Blocks_SimpleList',
                'supportedPostTypes' =>
                    Utility::postTypesToGraphQlFormat($parsedBlocksConfig['simple_list']),
                'graphQLName' => 'SimpleList',
            ],
            'list_with_image' => [
                'blockTypeName' =>
                    'Landing_Collectionwidget_layouts_columns_Blocks_ListWithImage',
                'supportedPostTypes' => [
                    ...Utility::postTypesToGraphQlFormat($parsedBlocksConfig['list_with_image']),
                    Utility::$postTypesMap['cerberus_dfp_native_ad'],
                ],
                'graphQLName' => 'ListWithImage',
            ],
            'featured_post' => [
                'blockTypeName' =>
                    'Landing_Collectionwidget_layouts_columns_Blocks_FeaturedPost',
                'supportedPostTypes' => $defaultSupportedPostTypes,
                'graphQLName' => 'FeaturedPost',
                'numberOfItems' => 1,
            ],
            'featured_feed_post' => [
                'blockTypeName' =>
                    'Landing_Collectionwidget_layouts_columns_Blocks_FeaturedFeedPost',
                'supportedPostTypes' => $defaultSupportedPostTypes,
                'graphQLName' => 'FeaturedFeedPost',
                'numberOfItems' => 1,
            ],
            'featured_post_with_logo' => [
                'blockTypeName' =>
                    'Landing_Collectionwidget_layouts_columns_Blocks_FeaturedPostWithLogo',
                'supportedPostTypes' => $defaultSupportedPostTypes,
                'graphQLName' => 'FeaturedPostWithLogo',
                'numberOfItems' => 1,
            ],
            'horizontal_list_with_image' => [
                'blockTypeName' =>
                    'Landing_Collectionwidget_layouts_columns_Blocks_HorizontalListWithImage',
                'supportedPostTypes' =>
                    Utility::postTypesToGraphQlFormat($parsedBlocksConfig['horizontal_list_with_image']),
                'graphQLName' => 'HorizontalListWithImage',
            ],
            'list_of_featured_events' => [
                'blockTypeName' =>
                    'Landing_Collectionwidget_layouts_columns_Blocks_ListOfFeaturedEvents',
                'supportedPostTypes' => [Utility::$postTypesMap['learning']],
                'graphQLName' => 'ListOfFeaturedEvents',
            ],
            'featured_list_of_whitepapers' => [
                'blockTypeName' =>
                    'Landing_Collectionwidget_layouts_columns_Blocks_FeaturedListOfWhitepapers',
                'supportedPostTypes' => [Utility::$postTypesMap['whitepaper']],
                'graphQLName' => 'FeaturedListOfWhitepapers',
                'postType' => ['whitepaper'],
            ],
            'editorial_related_block' => [
                'supportedPostTypes' => [
                    ...Utility::postTypesToGraphQlFormat($parsedBlocksConfig['list_with_image']),
                    Utility::$postTypesMap['sc_award_nominee'],
                    Utility::$postTypesMap['cerberus_dfp_native_ad'],
                ],
            ],
            'table_with_links' => [
                'type' => 'term',
                'blockTypeName' =>
                    'Landing_Collectionwidget_layouts_columns_Blocks_TableWithLinks',
                'graphQLName' => 'TableWithLinks',
            ],
        ];
    }

    /**
     * Creates TermBlock instance depending on block name and block type
     *
     * @return AbstractTermBlock
     */
    public static function createTermBlock(): AbstractTermBlock
    {
        return new TableWithLinksBlock([]);
    }

    /**
     * Creates TermBlock instance depending on block name and block type
     *
     * @param string $blockName
     * @param string|null $blockType
     *
     * @return AbstractBlock
     */
    public function createBlock(string $blockName, ?string $blockType = null): AbstractBlock
    {
        $blockConfig = $this->getBlocksConfig()[$blockName];
        switch ($blockConfig['type'] ?? null) {
            default:
            case 'feed':
                return self::createFeedBlock($blockName, $blockType, $blockConfig);
            case 'term':
                return self::createTermBlock();
        }
    }

    /**
     * Creates AbstractBlock instance depending on block name and block type
     *
     * @param string $blockName
     * @param string|null $blockType
     * @param array<string, mixed> $blockConfig
     *
     * @return AbstractFeedBlock
     */
    public static function createFeedBlock(string $blockName, ?string $blockType, array $blockConfig): AbstractFeedBlock
    {
        $blockSupportedOptions = self::blocksSupportedOptions()[$blockName] ?? [];
        $blockType = $blockType ?? self::$blockTypes['collectionWidget'];
        switch ($blockType) {
            case self::$blockTypes['contentTeaser']:
                return new ContentTeaserBlock($blockConfig, $blockSupportedOptions, $blockName);
            case self::$blockTypes['related']:
                return new RelatedBlock($blockConfig, $blockSupportedOptions, $blockName);
            default:
            case self::$blockTypes['collectionWidget']:
                return new Block($blockConfig, $blockSupportedOptions, $blockName);
        }
    }
}
