<?php

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

function port2342_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port2342_blemmyae_deployment_ri_002', true);
}

/**
 * Migrate redirects to custom redirects table.
 */
function port2342_blemmyae_deployment_ri_002(): void
{
    $post_args = [
        'post_type' => 'people',
        'posts_per_page' => -1,
    ];

    $counter = 1;

    $cacheExpirationTime = 5 * (defined('MINUTE_IN_SECONDS') ? MINUTE_IN_SECONDS : 60);

    $query = new WP_Query($post_args);
    $postsNumber = $query->post_count;
    while ($query->have_posts()) :
        $query->the_post();
        $id = get_the_ID();
        $key = 'person_posts_' . $id;
        set_transient($key, false, $cacheExpirationTime);

        $msgPrefix = '[' . $counter . '/' . $postsNumber . ']';
        Logger::log("$msgPrefix Updating transient for post id $id", 'info');
        $counter++;
    endwhile;
}
