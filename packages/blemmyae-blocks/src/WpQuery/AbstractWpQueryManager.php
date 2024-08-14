<?php

/**
 * AbstractWpQueryManager class. Used to create WP_Query
 * instances for use-case when block is an AbstractBlock instance
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\WpQuery;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use WP_Query;

/**
 * AbstractWpQueryManager class
 */
abstract class AbstractWpQueryManager
{
    protected int $numberOfItems;

    /**
     * @var int[]
     */
    protected array $exclude;

    protected int $page = 1;

    /**
     * @var array<string|int, mixed>
     */
    protected array $taxonomyQuery;

    /**
     * @var string[]
     */
    protected array $postType;

    /**
     * Flag used to extend taxonomyQuery with requirement that posts
     * should have native editorial type
     *
     * @var bool
     */
    protected bool $nonDfpNatives = false;

    protected bool $isLearningQuery;

    protected bool $isUpcomingEvents;

    protected bool $isOngoingEvents;

    protected int $numberOfNatives;

    protected int $pageOffset = 0;

    protected int $nativePageOffset = 0;

    /**
     * @var array<int, object{slug: string}>
     */
    protected array $nativeAdTopics = [];

    /**
     * @var null|object{ID: int}
     */
    protected ?object $nativeAdSponsor = null;

    /**
     * @var null|object{ID: int}
     */
    protected ?object $author = null;

    public int $totalPostCount = 0;

    /**
     * @var string[]
     */
    public array $applications = [];

    /**
     * Returns WP_Query instantiated with correct arguments. If block has
     * upcomingEvents or onDemandEvents options set, specific WP_Query
     * args should be used
     *
     * @return WP_Query
     */
    public function wpQuery(): WP_Query
    {
        $wpQuery = new WP_Query(
            $this->prepareWpQueryArgs()
        );
        $this->totalPostCount = $wpQuery->found_posts;

        return $wpQuery;
    }

    /**
     * @return array<string, mixed>
     */
    public function prepareWpQueryArgs(): array
    {
        $queryArgs = $this->isLearningQuery ?
            $this->learningOptionsWpQueryArgs() :
            $this->constructWpQueryArgs();

        // Add application to tax query, if it exists.
        if (!empty($this->applications)) {
            $queryArgs['tax_query'] = $this->addApplicationsToTaxQuery($queryArgs['tax_query'], $this->applications);
        }

        return $queryArgs;
    }

    /**
     * @return void
     */
    public function nativesModeEnable(): void
    {
        $this->nonDfpNatives = true;
    }

    /**
     * Generates WP_Query args that contain meta_query to order posts by
     * event start date.
     *
     * @return array<string, mixed>
     */
    protected function learningOptionsWpQueryArgs(): array
    {
        $currentDate = gmdate('Y-m-d h:i:s');
        $metaQueryCompareOperator = $this->isUpcomingEvents ? '>=' : '<';
        $order = $this->isUpcomingEvents ? 'ASC' : 'DESC';

        // Default query.
        $query = [
            'meta_query' => [
                [
                    'key' => 'date_0_start_date',
                    'value' => $currentDate,
                    'compare' => $metaQueryCompareOperator,
                    'type' => 'DATE',
                ],
            ],
            'meta_key' => 'date_0_start_date',
        ];

        // Query for ongoing events.
        if ($this->isOngoingEvents) {
            $query = [
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => 'date_0_start_date',
                        'value' => $currentDate,
                        'compare' => '<=',
                        'type' => 'DATE',
                    ],
                    [
                        'key' => 'date_0_end_date',
                        'value' => $currentDate,
                        'compare' => '>=',
                        'type' => 'DATE',
                    ],
                ],
            ];
        }

        return array_merge([
            'post_type' => 'learning',
            'post_status' => 'publish',
            'posts_per_page' => $this->numberOfItems,
            'paged' => $this->page,
            'tax_query' => $this->taxonomyQuery ? $this->constructTaxQuery() : [],
            'fields' => 'ids',
            'orderby' => 'meta_value',
            'order' => $order,
            'post__not_in' => $this->exclude,
        ], $query);
    }

    /**
     * @return array<string, mixed>
     */
    protected function constructWpQueryArgs(): array
    {
        $taxonomyQuery = $this->taxonomyQuery ? $this->constructTaxQuery() : [];

        return [
            'post_type' => $this->postType,
            'post_status' => 'publish',
            'tax_query' => $taxonomyQuery,
            'meta_query' => $this->buildMetaQuery(),
            'offset' => $this->nonDfpNatives ? $this->nativePageOffset : $this->pageOffset,
            'paged' => $this->page,
            'posts_per_page' =>
                $this->nonDfpNatives ? $this->numberOfNatives : $this->numberOfItems,
            'fields' => 'ids',
            'post__not_in' => $this->exclude,
        ];
    }

    /**
     * Prepares meta_query array to filter posts based on fields values.
     *
     * @return array<int, array{'key': string, 'value': string|int, 'compare': string}> The constructed meta query.
     */
    protected function buildMetaQuery(): array
    {
        $metaQuery = [];

        $fields = [
            [
                'condition' => $this->nonDfpNatives && $this->nativeAdSponsor,
                'key' => 'sponsors',
                'value' => $this->nativeAdSponsor ? sprintf(':"%d";', $this->nativeAdSponsor->ID) : 0,
                'compare' => 'LIKE',
            ],
            [
                'condition' => $this->author,
                'key' => 'author',
                'value' => $this->author ? sprintf(':"%d";', $this->author->ID) : 0,
                'compare' => 'LIKE',
            ],
        ];

        foreach ($fields as $field) {
            if ($field['condition']) {
                $metaQuery[] = [
                    'key' => $field['key'],
                    'value' => $field['value'],
                    'compare' => $field['compare'],
                ];
            }
        }

        return $metaQuery ?: [];
    }


    /**
     * Builds taxonomy query for WP_Query both from graphql format and from acf
     * wp format
     *
     * @return array<string|int, mixed>
     */
    protected function constructTaxQuery(): array
    {
        $nativeTaxQuery = false;
        if (!empty($this->taxonomyQuery)) {
            if (!empty($this->taxonomyQuery['taxArray']) && is_array($this->taxonomyQuery['taxArray'])) {
                if (2 > count($this->taxonomyQuery['taxArray'])) {
                    unset($this->taxonomyQuery['relation']);
                }

                foreach ($this->taxonomyQuery['taxArray'] as $value) {
                    // Made this way to eliminate non-necessary nesting
                    $this->taxonomyQuery[] = $value;
                }

                unset($this->taxonomyQuery['taxArray']);
                $nativeTaxQuery = true;
            }
        }

        $baseTaxQuery = $nativeTaxQuery ? $this->taxonomyQuery : [
            'relation' => $this->taxonomyQuery['relation'],
            ...array_map(static fn($queryElement) => [
                'field' => 'term_taxonomy_id',
                'terms' =>
                    array_map(static fn($termNode) => $termNode->term_taxonomy_id, $queryElement['terms'] ?? []),
                'operator' => $queryElement['operator'],
            ], $this->taxonomyQuery['query_array'] ?? []),
        ];

        /**
         * Constructed this way to satisfy docs in collection widget:
         * "If multiple topics selected then native ads from any of the topics will be shown."
         */
        if ($this->nonDfpNatives) {
            return [
                'relation' => 'OR',
                ...(array_map(static fn($term) => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'topic',
                        'field' => 'slug',
                        'terms' => [$term->slug],
                        'operator' => 'IN',
                    ],
                    [
                        'taxonomy' => 'editorial_type',
                        'field' => 'slug',
                        'terms' => ['native'],
                        'operator' => 'IN',
                    ],
                ], $this->nativeAdTopics)),
            ];
        }

        return $baseTaxQuery;
    }

    /**
     * Add application tax query to block wp query based on block settings.
     *
     * @param array<string|int, mixed> $taxQuery
     * @param string[] $applications
     *
     * @return array<string|int, mixed>
     */
    public function addApplicationsToTaxQuery(array $taxQuery, array $applications): array
    {
        return [
            'relation' => 'AND',
            [
                ...$taxQuery,
            ],
            [
                'field' => 'slug',
                'taxonomy' => BlemmyaeApplications::TAXONOMY,
                'terms' => $applications,
                'operator' => 'IN',
            ],
        ];
    }
}
