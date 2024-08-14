<?php

/**
 * @file Add general HUM CDP settings.
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/BLEM-279
 */

declare(strict_types=1);

/**
 * Add general HUM CDP settings
 */
function blem279_blemmyae_deployment_ri_001(): void
{
    update_option('hum_setting_allow_select_post_types', 1);
    update_option(
        'hum_setting_select_posts_types',
        [
            'editorial',
            'landing',
            'learning',
            'whitepaper',
        ]
    );
    update_option('hum_setting_select_posts_scroll', '.teaser,.content-column');
    update_option('hum_setting_select_posts_click', '.btn');
}
