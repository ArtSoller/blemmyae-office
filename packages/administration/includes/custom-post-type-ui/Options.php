<?php

/**
 * Options.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

namespace Scm\Custom_Post_Type_UI;

use Scm\Tools\ArrayUtils;
use Scm\Tools\WpCore;
use WP_Post;
use WP_Query;

/**
 * @todo: Update description.
 *
 * Class Options
 * @package Scm\Custom_Post_Type_UI
 */
class Options
{
    public const CUSTOM_COLUMN_SLUG__LEARNING_START_DATE = 'learning_start_date';
    public const CUSTOM_COLUMN_SLUG__EDITORIAL_AUTHOR = 'editorial_author';
    public const CUSTOM_COLUMN_META_FIELD__LEARNING_START_DATE = 'date_0_start_date';

    /**
     * Initialize hooks.
     */
    public function __construct()
    {
        $this->initCustomDashboardColumns();
        add_action('restrict_manage_posts', [$this, 'filterContentByTaxonomies'], 10, 2);
    }

    /**
     * Triggers hooks that add custom column in dashboard
     *
     * @return void
     */
    public function initCustomDashboardColumns(): void
    {
        add_filter('query_vars', [$this, 'addCustomFilterVariable']);
        add_filter('manage_learning_posts_columns', [$this, 'learningPostsColumns']);
        add_action('manage_editorial_posts_columns', [$this, 'editorialPostsColumns'], 10, 2);
        add_action('manage_learning_posts_custom_column', [$this, 'customColumnValue'], 10, 2);
        add_action('manage_editorial_posts_custom_column', [$this, 'customColumnValue'], 10, 2);
        add_filter('manage_edit-learning_sortable_columns', [$this, 'learningStartDateSortableColumn']);
        add_action('pre_get_posts', [$this, 'learningStartDateColumnSort'], 10, 1);
        add_action('pre_get_posts', [$this, 'processCustomFilter'], 10, 1);
    }

    /**
     * Add custom_filter variable to query
     *
     * @param string[] $vars
     *
     * @return string[]
     */
    public function addCustomFilterVariable(array $vars): array
    {
        $vars[] = "custom_filter";
        return $vars;
    }

    /**
     * Updates editorial columns displayed in cms
     *
     * @param array $columns
     * @return array
     * @phpstan-ignore-next-line
     */
    public function editorialPostsColumns(array $columns): array
    {
        // Columns that should not be rendered specified by their machine name
        $columnsToOmit = ['taxonomy-podcast_show', 'taxonomy-flag'];

        /**
         * Columns that should be renamed. Structure:
         * [
         *   'columnMachineName' => 'Column New Human Name'
         * ]
         */
        $columnsToRename = [
            'author' => 'Post Creator',
        ];

        /**
         * Columns to insert. Structure:
         * [
         *   'columnToPrependToMachineName' => [
         *     'columnMachineName' => 'Column Human Name'
         *   ]
         * ]
         */
        $columnsToInsert = [
            'author' => [
                self::CUSTOM_COLUMN_SLUG__EDITORIAL_AUTHOR => 'Author',
            ],
        ];

        $columns = array_filter(
            $columns,
            static function (string $columnKey) use ($columnsToOmit): bool {
                return !in_array(
                    $columnKey,
                    $columnsToOmit,
                    true
                );
            },
            ARRAY_FILTER_USE_KEY
        );

        $columns = [...$columns, ...$columnsToRename];

        return ArrayUtils::arrayInsertElements($columnsToInsert, $columns, true);
    }

    /**
     * Adds label to learning start date column
     *
     * @param array $columns
     *
     * @return array
     * @phpstan-ignore-next-line
     */
    public function learningPostsColumns(array $columns): array
    {
        return array_merge(
            $columns,
            [
                self::CUSTOM_COLUMN_SLUG__LEARNING_START_DATE => __('Start Date'),
            ]
        );
    }

    /**
     * Outputs formatted meta value to learning start date column
     *
     * @param string $column
     * @param int $postId
     *
     * @return void
     */
    public function customColumnValue(string $column, int $postId): void
    {
        switch ($column) {
            case self::CUSTOM_COLUMN_SLUG__LEARNING_START_DATE:
                echo esc_html(
                    date(
                    // Example of formatting - 2022/11/03 at 3:10 am
                        'Y/m/d \a\t\ h:i a',
                        strtotime(
                            get_post_meta(
                                $postId,
                                self::CUSTOM_COLUMN_META_FIELD__LEARNING_START_DATE
                            )[0] ?? null
                        )
                    )
                );
                break;
            case self::CUSTOM_COLUMN_SLUG__EDITORIAL_AUTHOR:
                $authorIds = get_post_meta($postId, 'author')[0] ?? null;
                if ($authorIds) {
                    $authorNames = [];
                    foreach ($authorIds as $authorId) {
                        $post = get_post($authorId);
                        if ($post instanceof WP_Post) {
                            $authorNames[] = $post->post_title;
                        }
                    }
                    // WordPress phpcs rules require escape of each echo
                    echo esc_html(implode(', ', $authorNames));
                }
                break;
        }
    }

    /**
     * Adds slug to allow sorting by learning start date column
     *
     * @param array $columns
     *
     * @return array
     * @phpstan-ignore-next-line
     */
    public function learningStartDateSortableColumn(array $columns): array
    {
        return array_merge(
            $columns,
            [
                self::CUSTOM_COLUMN_SLUG__LEARNING_START_DATE => self::CUSTOM_COLUMN_SLUG__LEARNING_START_DATE,
            ]
        );
    }

    /**
     * Process custom_filter value and update WP_Query args
     *
     * @param WP_Query $query
     *
     * @return void
     */
    public function processCustomFilter(WP_Query $query): void
    {
        $customFilter = $query->get('custom_filter');
        if (!$customFilter) {
            return;
        }

        $customFilter = stripcslashes($customFilter);
        $customFilter = @unserialize($customFilter) ?: $customFilter;

        if (is_array($customFilter)) {
            $metaFieldName = array_keys($customFilter)[0];
            $metaQuery = [
                'relation' => 'OR',
                ...array_map(
                    fn($metaFieldValue) => [
                        'key' => $metaFieldName,
                        'value' => sprintf(':"%d";', (string)$metaFieldValue),
                        'compare' => 'LIKE',
                    ],
                    $customFilter[$metaFieldName]
                ),
            ];

            $query->set('meta_query', $metaQuery);
            $query->set('orderby', 'meta_value');
        }
    }

    /**
     * Adds meta query and order by to query to allow sorting
     * by start date column
     *
     * @param WP_Query $query
     *
     * @return void
     */
    public function learningStartDateColumnSort(WP_Query $query): void
    {
        $orderBy = $query->get('orderby');

        if ($orderBy === self::CUSTOM_COLUMN_SLUG__LEARNING_START_DATE) {
            $metaQuery = [
                'relation' => 'OR',
                [
                    'key' => self::CUSTOM_COLUMN_META_FIELD__LEARNING_START_DATE,
                    'type' => 'DATE',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => self::CUSTOM_COLUMN_META_FIELD__LEARNING_START_DATE,
                    'type' => 'DATE',
                ],
            ];

            $query->set('meta_query', $metaQuery);
            $query->set('orderby', 'meta_value');
        }
    }

    /**
     * @throws \Exception
     */
    public function filterContentByTaxonomies(string $postType, string $which): void
    {
        // A list of taxonomy slugs to filter by - @todo: move out as a ct-plugin(s) constants.
        $customFilters = [];
        // Apply this only on a specific post type
        switch ($postType) {
            case 'company_profile':
                $taxonomies = [
                    'company_profile_type',
                    'topic',
                ];
                break;
            case 'editorial':
                $taxonomies = [
                    'applications',
                    'editorial_type',
                    'topic',
                    'brand',
                    'industry',
                ];
                $customFilters = [
                    'author',
                ];
                break;
            case 'landing':
                $taxonomies = [
                    'applications',
                    'landing_type',
                    'topic',
                ];
                break;
            case 'learning':
                $taxonomies = [
                    'applications',
                    'learning_type',
                    'topic',
                    'learning_vendor_type',
                ];
                break;
            case 'newsletter':
                $taxonomies = [
                    'applications',
                    'newsletter_type',
                    'topic',
                ];
                break;
            case 'people':
                $taxonomies = [
                    'people_type',
                    'topic',
                ];
                break;
            case 'product_profile':
                $taxonomies = [
                    'topic',
                ];
                break;
            case 'whitepaper':
                $taxonomies = [
                    'applications',
                    'whitepaper_type',
                    'topic',
                ];
                break;
            case 'ppworks_episode':
                $taxonomies = [
                    'applications',
                    'topic',
                    'ppworks_show',
                ];
                break;
            case 'ppworks_segment':
                $taxonomies = [
                    'applications',
                    'topic',
                    'ppworks_segment_type',
                    'ppworks_show',
                ];
                break;
            default:
                return;
        }

        foreach ($taxonomies as $taxonomySlug) {
            // Retrieve taxonomy data.
            $taxonomyObject = get_taxonomy($taxonomySlug);
            if (!$taxonomyObject) {
                continue;
            }
            $taxonomyName = $taxonomyObject->labels->name;

            // Retrieve taxonomy terms.
            $terms = WpCore::getTerms($taxonomySlug);

            // Display filter HTML.
            echo "<select name='$taxonomySlug' id='$taxonomySlug' class='postform'>";
            echo '<option value="">' . sprintf(
                esc_html__('All %s', 'text_domain'),
                $taxonomyName
            ) . '</option>';
            $options = [
                'terms' => $terms,
                'taxonomySlug' => $taxonomySlug,
                'postType' => $postType,
            ];
            echo $this->termsHierarchical($options);
            echo '</select>';
        }

        foreach ($customFilters as $customFilter) {
            if ($customFilter === 'author') {
                $wpQuery = new WP_Query([
                    'post_type' => 'people',
                    'posts_per_page' => -1,
                    'orderby' => 'post_title',
                ]);

                // Display filter HTML.
                echo '<select name="custom_filter" id="custom_filter" class="postform">';
                echo '<option value="">' . esc_html__('All authors') . '</option>';
                /** @var WP_Post $personPost */
                foreach ($wpQuery->posts as $personPost) {
                    $value = serialize([
                        'author' => [$personPost->ID],
                    ]);
                    $selected = ((isset($_GET['custom_filter']) && (stripslashes(
                        $_GET['custom_filter']
                    ) === $value)) ? ' selected="selected"' : '');

                    echo "<option value='$value' $selected>" . $personPost->post_title . '</option>';
                }
                echo '</select>';
            }
        }
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return string
     */
    public function termsHierarchical(array $options): string
    {
        $terms = $options['terms'] ?? [];
        $taxonomySlug = $options['taxonomySlug'] ?? '';
        $postType = $options['postType'] ?? '';
        $output = $options['output'] ?? '';
        $parentId = $options['parentId'] ?? 0;
        $level = $options['level'] ?? 0;
        foreach ($terms as $term) {
            if ($parentId === $term->parent && $level < 3) {
                $itemOutput = printf(
                    '<option value="%1$s" %2$s>%3$s%4$s (%5$s)</option>',
                    $term->slug,
                    // phpcs:ignore
                    ((isset($_GET[$taxonomySlug]) && ($_GET[$taxonomySlug] === $term->slug)) ? ' selected="selected"' : ''),
                    str_repeat('- ', $level),
                    $term->name,
                    $this->taxCount($taxonomySlug, $term->slug, $postType)
                );

                $output .= $itemOutput;
                $options['output'] = $output;
                $options['parentId'] = $term->term_id;
                $options['level'] = $level + 1;
                $output = $this->termsHierarchical($options);
            }
        }
        return $output;
    }

    /**
     * @param string $taxonomy
     * @param string $termSlug
     * @param string $postType
     *
     * @return int
     */
    public function taxCount(string $taxonomy, string $termSlug, string $postType): int
    {
        $group = 'administration-tax-count';
        $key = $taxonomy . $termSlug . $postType;
        $taxCount = wp_cache_get($key, $group);
        if ($taxCount === false) {
            // Find the number of items in custom post type that use the term in a taxonomy.
            $args = [
                'post_type' => $postType,
                'posts_per_page' => -1,
                'tax_query' => [
                    [
                        'taxonomy' => $taxonomy,
                        'field' => 'slug',
                        'terms' => $termSlug,
                    ],
                ],
                'cache_results' => true,
                'fields' => 'ids',
            ];
            $taxCount = (new WP_Query($args))->found_posts;
            // @phpstan-ignore-next-line Cannot find MINUTE_IN_SECONDS.
            wp_cache_set($key, $taxCount, $group, 5 * MINUTE_IN_SECONDS);
        }

        return (int)$taxCount;
    }
}
