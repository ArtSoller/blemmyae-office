<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-607
 */

declare(strict_types=1);

/**
 * Set subscribe links.
 */
function blem607_blemmyae_deployment_ri_001(): void
{
    $ppworkShows = [
        '72836' => [
            'audio' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/pauls-security-weekly-podcast-only/id1149992167?mt=2',
                'google' => 'https://podcasts.google.com/feed/aHR0cHM6Ly9wYXVsZG90Y29tLmxpYnN5bi5jb20v',
                'rss' => 'https://pswonly.libsyn.com/rss',
            ],
            'video' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/pauls-security-weekly-tv/id121896233?mt=2',
                'google' => 'https://www.youtube.com/playlist?list=PLlPkFwQHxYE4atQRxwAsTux2PmOuWGgAA',
                'rss' => 'https://pswvideoonly.libsyn.com/rss',
            ],
        ],
        '72838' => [
            'audio' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/enterprise-security-weekly/id1112175960?mt=2',
                'google' => 'https://podcasts.google.com/feed/aHR0cDovL2Vzd2F1ZGlvLnN3c2d0di5saWJzeW5wcm8uY29tL3Jzcw',
                'rss' => 'https://eswaudio.libsyn.com/rss',
            ],
            'video' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/enterprise-security-weekly/id1111782605?mt=2',
                'google' => 'https://www.youtube.com/playlist?list=PLlPkFwQHxYE4j9Nxn8LO2vc2D89ZjEqmA',
                'rss' => 'https://eswvideo.libsyn.com/rss',
            ],
        ],
        '72841' => [
            'audio' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/application-security-weekly-audio/id1338907745?mt=2',
                'google' => 'https://podcasts.google.com/feed/aHR0cDovL2Fzd2F1ZGlvLnN3c2d0di5saWJzeW5wcm8uY29tL3Jzcw',
                'rss' => 'https://aswaudio.libsyn.com/rss',
            ],
            'video' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/application-security-weekly-video/id1338908396?mt=2',
                'google' => 'https://www.youtube.com/playlist?list=PLlPkFwQHxYE4Rzf18_huyTbphNxqu4gbZ',
                'rss' => 'https://aswvideo.libsyn.com/rss',
            ],
        ],
        '72839' => [
            'audio' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/startup-security-weekly-audio/id1139240260?mt=2',
                'google' => 'https://podcasts.google.com/feed/aHR0cDovL3Nzd2F1ZGlvLnN3c2d0di5saWJzeW5wcm8uY29tL3Jzcw',
                'rss' => 'https://sswaudio.libsyn.com/rss',
            ],
            'video' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/startup-security-weekly-video/id1139240239?mt=2',
                'google' => 'https://www.youtube.com/watch?v=4tg31DJvTLk&list=PLlPkFwQHxYE4KNGXjbiHUtrx7-wQa-SXi',
                'rss' => 'https://sswvideo.libsyn.com/rss',
            ],
        ],
        '72845' => [
            'audio' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/security-and-compliance-weekly-audio/id1487829881',
                'google' => 'https://podcasts.google.com/feed/aHR0cHM6Ly9zY3dhdWRpby5saWJzeW4uY29tL3Jzcw',
                'rss' => 'https://scwaudio.libsyn.com/rss',
            ],
            'video' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/security-and-compliance-weekly-video/id1582881526',
                'google' => 'https://www.youtube.com/playlist?list=PLlPkFwQHxYE43HJDJsb_1-NJbDVWudpeg',
                'rss' => 'https://scwvideo.libsyn.com/rss',
            ],
        ],
        '72847' => [
            'audio' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/hack-naked-tv-audio/id1111792719?mt=2',
                'google' => 'https://podcasts.google.com/feed/aHR0cDovL2hudHZhdWRpby5zd3NndHYubGlic3lucHJvLmNvbS9yc3M',
                'rss' => 'https://hntvaudio.libsyn.com/rss',
            ],
            'video' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/hack-naked-tv-video/id1111787861?mt=2',
                'google' => 'https://www.youtube.com/playlist?list=PLlPkFwQHxYE4y0GomF9gD0Dm_YgCwFDPo',
                'rss' => 'https://hntvvideo.libsyn.com/rss',
            ],
        ],
        '72843' => [
            'audio' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/tradecraft-security-weekly-audio/id1244867530',
                'google' => 'https://podcasts.google.com/feed/aHR0cDovL3Rzd2F1ZGlvLnN3c2d0di5saWJzeW5wcm8uY29tL3Jzcw',
                'rss' => 'https://tswaudio.libsyn.com/rss',
            ],
            'video' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/tradecraft-security-weekly-video/id1244871892',
                'google' => 'https://www.youtube.com/playlist?list=PLlPkFwQHxYE7Yi5jtcSyCCr8pXxP1OEkZ',
                'rss' => 'https://tswvideo.libsyn.com/rss',
            ],
        ],
        '72840' => [
            'audio' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/secure-digital-life-audio/id1214392250?mt=2',
                'google' =>
                    'https://podcasts.google.com/feed/aHR0cHM6Ly9zZWN1cmVkaWdpdGFsbGlmZS5jb20vaW5kZXgucGhwL2ZlZWQv',
                'rss' => 'https://sdlaudio.libsyn.com/rss',
            ],
            'video' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/secure-digital-life-video/id1214394375',
                'google' => 'https://www.youtube.com/playlist?list=PLlPkFwQHxYE6Fkc0B1nEt0rykFpLDitMP',
                'rss' => 'https://sdlvideo.libsyn.com/rss',
            ],
        ],
        '73941' => [
            'audio' => [
                'apple' => 'https://podcasts.apple.com/us/podcast/ciso-stories-podcast/id1553494094',
                'google' => 'https://podcasts.google.com/feed/aHR0cDovL2NzcGF1ZGlvLnN3c2d0di5saWJzeW5wcm8uY29tL3Jzcw',
                'rss' => 'https://cspaudio.libsyn.com/rss',
            ],
        ],
    ];

    foreach ($ppworkShows as $ppworkShowId => $ppworksShow) {
        foreach ($ppworksShow as $groupName => $groupValue) {
            foreach ($groupValue as $type => $link) {
                update_term_meta($ppworkShowId, $groupName . '_' . $type, $link);
            }
        }
    }

    wp_cache_flush();
}
