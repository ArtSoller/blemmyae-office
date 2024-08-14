<?php

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\CerberusApps;
use Cra\BlemmyaeApplications\Entity\Permalink;
use ReleaseInstructions\Command\CoreCommand as RI;

function port2347_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port2347_blemmyae_deployment_ri_002', true);
}

/**
 * Migrate redirects to custom redirects table.
 */
function port2347_blemmyae_deployment_ri_002(): void
{
    global $wpdb;

    $date_query = [
        'date_query' => [
            'after' => [
                'year' => 2023,
                'month' => 8,
                'day' => 16,
            ],
        ],
    ];
    $tax_query = [
        'tax_query' => [
            [
                'taxonomy' => 'applications',
                'field' => 'slug',
                'terms' => ['mssp', 'ce2e'],
                'operator' => 'IN',
            ]
        ]
    ];

    $post_args = [
        'post_type' => 'editorial',
        'posts_per_page' => -1,
        'odrder' => 'ASC',
        'orderby' => 'date',
    ];

    $query_args = array_merge($post_args, $date_query, $tax_query);


    $posts = [];
    $posts[] = [
        'id',
        'date',
        'topics',
        'title',
        'application',
        'link',
        'authors',
        'description'
    ];
    $query = new WP_Query($query_args);
    while ($query->have_posts()) :
        $query->the_post();
        $id = get_the_ID();
        $source = get_permalink($id);
        // Remove base url.
        $source = wp_make_link_relative($source);
        $app = BlemmyaeApplications::getAppIdByPostId($id);
        $postSlug = get_field(CerberusApps::APPLICATION_SLUG_FIELD, $id, false);

        // todo cross-app support
        if (is_array($postSlug)) {
            $postSlug = reset($postSlug);
        }

        $row = [
            $id,
            get_the_date('M j, Y'),
            // Topics
            implode(', ', array_column(get_field('field_6066d31112bc0'), 'name')),
            get_the_title(),
            // Applications
            implode(', ', array_column(get_field('field_646c59e467bbf'), 'name')),
            // Link
            Permalink::buildPermalinkByApp($source, $app, $postSlug),
            // Authors
            implode(', ', array_column(get_field('field_608f8eda9ccf7'), 'post_title')),
            // Get first 3 sentences. Or either just get_the_excerpt(),
            preg_replace('/\s+/', ' ', strip_tags(get_field('field_6065ae75dc539') ?: '')) ?: implode(
                    '. ',
                    array_slice(
                        explode(
                            '.',
                            preg_replace('/\s+/', ' ', strip_tags(get_the_content()))
                        ),
                        0,
                        3
                    )
                ) . 'ri',
        ];
        $posts[] = $row;
        //var_dump($row);
    endwhile;

    $fp = fopen('./a9s-posts-since-august-' . date('M-j-Y') . '.csv', 'w');
    foreach ($posts as $fields) {
        fputcsv($fp, $fields, ';');
    }
    fclose($fp);
}
