<?php

/**
 * AbstractLandingByQueryResolver class.
 *
 * Holds protected methods for LandingBySlug class.
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Endpoints\CtLanding;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\WP_GraphQL\Options;
use Cra\BlemmyaeBlocks\Block\BlockQueue;
use Cra\BlemmyaeBlocks\Endpoints\AbstractEndpoint;
use Cra\CtLanding\LandingCT;
use Exception;
use WP_Query;
use WPGraphQL\AppContext;

/**
 * AbstractLandingByQueryResolver class.
 */
abstract class AbstractLandingByQueryResolver extends AbstractEndpoint
{
    private const COLLECTION_WIDGET_FIELD = 'field_60c1fd708e2d6';

    protected BlockQueue $blockQueue;

    protected string $postType = LandingCT::POST_TYPE;

    /**
     * General function to resolve landing by query.
     *
     * @param array<string, mixed> $args
     *  Args for WP_Query.
     * @param AppContext $context
     *  Context
     *
     * @return object|null
     * @throws Exception
     */
    protected function landingByQueryResolver(array $args, AppContext $context): ?object
    {
        // Load posts via wordpress query.
        $wpQuery = new WP_Query($args);

        // If we have more than 1 post, we will not load any posts.
        if ($wpQuery->post_count !== 1) {
            return null;
        }

        // Get first landing post.
        /** @var int $landingPost */
        $landingPost = $wpQuery->posts[0];

        // Use landing loader.
        $landingLoader = $context->get_loader('post')->load($landingPost);

        // Work with collections widget fields.
        $collectionWidgetField = get_field(self::COLLECTION_WIDGET_FIELD, $landingPost) ?: [];
        foreach ($collectionWidgetField as $collectionWidgetLayoutIndex => $collectionWidgetLayout) {
            $columns = $collectionWidgetLayout['columns'];
            foreach ($columns as $columnIndex => $column) {
                $blocks = $column['blocks'];
                foreach ($blocks as $blockIndex => $block) {
                    if (!array_key_exists($block['acf_fc_layout'], $this->blockFactory->getBlocksConfig())) {
                        continue;
                    }

                    // Save path to the blocks, but without parent.
                    $path = [
                        'layouts',
                        $collectionWidgetLayoutIndex,
                        'columns',
                        $columnIndex,
                        'blocks',
                        $blockIndex,
                    ];

                    // Get apps.
                    $app = BlemmyaeApplications::getAppIdByPostId($landingPost);

                    // Save blocks into queue.
                    $blockObject = $this->blockFactory->createBlock($block['acf_fc_layout']);
                    $blockObject->init($block, $path, $this->blockQueue, $app);
                    $this->blockQueue->addBlock($blockObject);
                }
            }
        }

        $this->blockQueue->resolveQueue();

        return $landingLoader;
    }

    /**
     * Resolve landing by DATABASE_ID.
     *
     * @param array<string, mixed> $args
     * @param AppContext $context
     *
     * @return object|null
     * @throws Exception
     */
    protected function landingPreviewResolver(array $args, AppContext $context): ?object
    {
        // If we do not have ID or previewId => nothing to do.
        if (empty($args['id']) && empty($args['previewId'])) {
            return null;
        }

        $isPreview = !empty($args['previewId']);

        // If we have preview => we need to load revision instead of landing.
        $wpQueryArgs = [
            'p' => $isPreview ? $args['previewId'] : $args['id'],
            'post_type' => $isPreview ? 'revision' : LandingCT::POST_TYPE,
            // Post status `any` does not include `draft` and `auto-draft`.
            'post_status' => ['any', 'draft', 'auto-draft'],
            'sort_column' => 'ID',
            'fields' => 'ids',
            'sort_order' => 'desc',
            'posts_per_page' => 1,
        ];

        return $this->landingByQueryResolver($wpQueryArgs, $context);
    }

    /**
     * Function generates blocks queue and initiates queue resolving.
     *
     * @param array<string, mixed> $args
     * @param AppContext $context
     *
     * @return object|null
     * @throws Exception
     */
    protected function landingBySlugResolver(array $args, AppContext $context): ?object
    {
        return $this->landingByQueryResolver(
            Options::graphqlEntityQueryArgs($args, $this->postType) ?? [],
            $context
        );
    }
}
