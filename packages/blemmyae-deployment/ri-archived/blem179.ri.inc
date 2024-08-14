<?php

/**
 * @file Add general HUM CDP settings.
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/BLEM-179
 */

declare(strict_types=1);

/**
 * Add general HUM CDP settings
 */
function blem179_blemmyae_deployment_ri_001(): void
{
    update_option('hum_setting_allow_select_post_types', 1);
    update_option(
        'hum_setting_select_posts_types',
        [
            'post',
            'page',
            'cra_whitepaper',
            'newsml_post',
            'hm-webcast',
            'hm-product-review',
        ]
    );
    update_option('hum_setting_select_posts_scroll', 'article');
    update_option('hum_setting_select_posts_click', '#subscribe-link,.sso-registration');
}
