<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-631
 */

declare(strict_types=1);

/**
 * Migrate CSC content from WYSIWYG to Grid - Card List block.
 */
function blem631_blemmyae_deployment_ri_002(): void
{
    // Update layouts setting for specific layouts on CSC homepage.
    foreach (['7', '8', '10', '13'] as $layout_id) {
        update_post_meta('443916', 'layouts_' . $layout_id . '_type', 'content__1_column');
    }

    update_post_meta('443916', 'layouts_0_background_image_position', 'bottom');
    update_post_meta('443916', 'layouts_2_design_theme', 'light');
    update_post_meta('443916', 'layouts_2_background_image', '449744');
    update_post_meta('443916', 'layouts_2_background_image_position', 'top');
    // phpcs:ignore
    update_post_meta('443916', 'layouts_2_columns_0_blocks_0_body', '<div class="fs-3 pb-4 pt-5 mt-5 text-white text-center">MEMBER ORGANIZATIONS ACROSS INDUSTRIES &amp; SECTORS INCLUDE</div>');

    $updated_content = [
        // /membership-enterprise content.
        '443923' => [
            [
                'layouts' => '1',
                'columns' => '1',
                'id' => '0',
                'block' => '0',
                'amount' => '1',
                'number_of_columns' => '1',
                'fields' => [
                    'template' => 'tile',
                    'image_style' => 'rectangle',
                    'background_image' => '455515',
                    // phpcs:ignore
                    'body' => '\"We all face similar problems securing our companies from an evolving and growing cyber-threat landscape. Learning from peers across industries - problem-solving with them - yield better decisions and faster results\"

<em>- Sheldon Cuffie, CISO, American Family Insurance</em>',
                ]
            ],
            [
                'layouts' => '2',
                'columns' => '0',
                'id' => '0',
                'block' => '0',
                'amount' => '1',
                'number_of_columns' => '1',
                'fields' => [
                    'template' => 'tile',
                    'image_style' => 'rectangle',
                    'background_image' => '455516',
                    // phpcs:ignore
                    'body' => '\"The Morning Security Reports give me and my team actionable intelligence every day, and the opportunity to share insights and validate strategies with peers via working groups and private boardrooms is invaluable.”
 
<em>- Matt Stiak, Director, Cyber Risk Management, Delta Dental of California</em>',
                ]
            ],
        ],
        // /membership-smb content.
        '449408' => [
            [
                'layouts' => '1',
                'columns' => '1',
                'id' => '0',
                'block' => '0',
                'amount' => '1',
                'number_of_columns' => '1',
                'fields' => [
                    'template' => 'tile',
                    'image_style' => 'rectangle',
                    'background_image' => '455515',
                    // phpcs:ignore
                    'body' => '\"The Cybersecurity Collaborative listened to my organizational needs and developed a plan consisting of tools, policies, and professional development training which I implemented immediately.  They saved me time and consulting fees and one of my most valued partners."

<em>- Michael Barnes, IT Director, OrthoTennessee</em>',
                ]
            ],
            [
                'layouts' => '2',
                'columns' => '0',
                'id' => '0',
                'block' => '0',
                'amount' => '1',
                'number_of_columns' => '1',
                'fields' => [
                    'template' => 'tile',
                    'image_style' => 'rectangle',
                    'background_image' => '455515',
                    // phpcs:ignore
                    'body' => '\"We all face similar problems securing our companies from an evolving and growing cyber-threat landscape. Learning from peers across industries - problem-solving with them - yields better decisions and faster results." 

<em>- Sheldon Cuffie, CISO, American Family Insurance</em>',
                ]
            ],
        ],
        // Homepage content.
        '443916' => [
            [
                'layouts' => '8',
                'columns' => '0',
                'id' => '0',
                'block' => '0',
                'amount' => '4',
                'number_of_columns' => '2',
                'fields' => [
                    'template' => 'grid',
                    'image_style' => 'rectangle',
                    'background_image' => '449743',
                    'title' => 'No Vendor or Analyst Influence',
                    'body' => 'Only peer-to-peer knowledge sharing in a trusted environment.',
                ]
            ],
            [
                'layouts' => '8',
                'columns' => '0',
                'id' => '1',
                'block' => '0',
                'amount' => '4',
                'number_of_columns' => '2',
                'fields' => [
                    'template' => 'grid',
                    'image_style' => 'rectangle',
                    'background_image' => '449743',
                    'title' => 'CISO Developed Tools',
                    // phpcs:ignore
                    'body' => 'Curated library of implementable solutions developed based on member needs and requests.',
                ]
            ],
            [
                'layouts' => '8',
                'columns' => '0',
                'id' => '2',
                'block' => '0',
                'amount' => '4',
                'number_of_columns' => '2',
                'fields' => [
                    'template' => 'grid',
                    'image_style' => 'rectangle',
                    'background_image' => '449743',
                    'title' => 'On-Demand Collaboration',
                    'body' => 'Engage peers for real-time solutions to your top challenges.',
                ]
            ],
            [
                'layouts' => '8',
                'columns' => '0',
                'id' => '3',
                'block' => '0',
                'amount' => '4',
                'number_of_columns' => '2',
                'fields' => [
                    'template' => 'grid',
                    'image_style' => 'rectangle',
                    'background_image' => '449743',
                    'title' => 'By and for the Member',
                    'body' => 'Continuous monitoring of member needs drive all content and educational initiatives.',
                ]
            ],
            [
                'layouts' => '10',
                'columns' => '0',
                'id' => '0',
                'block' => '0',
                'amount' => '2',
                'number_of_columns' => '2',
                'fields' => [
                    'template' => 'grid',
                    'image_style' => 'rectangle',
                    'image' => '450118',
                    'title' => 'Small Security Teams',
                    'body' => 'For organizations with small or no dedicated cybersecurity teams',
                    'cta_link' => ['title' => 'Learn more', 'url' => '/membership-smb', 'target' => ''],
                ]
            ],
            [
                'layouts' => '10',
                'columns' => '0',
                'id' => '1',
                'block' => '0',
                'amount' => '2',
                'number_of_columns' => '2',
                'fields' => [
                    'template' => 'grid',
                    'image_style' => 'rectangle',
                    'image' => '450119',
                    'title' => 'Enterprise Security Teams',
                    'body' => 'For organizations with established, dedicated security teams',
                    'cta_link' => ['title' => 'Learn more', 'url' => '/membership-smb', 'target' => ''],
                ]
            ],
            [
                'layouts' => '13',
                'columns' => '0',
                'id' => '0',
                'block' => '0',
                'amount' => '2',
                'number_of_columns' => '2',
                'fields' => [
                    'template' => 'grid',
                    'image_style' => 'rectangle',
                    'image' => '449740',
                    'background_image' => '449743',
                    'title' => 'Insights From Our Community',
                    'body' => 'Our CISOs share their journey to becoming trusted industry leaders.

<em>Developed and published in partnership with SC Media.</em>',
                ]
            ],
            [
                'layouts' => '13',
                'columns' => '0',
                'id' => '1',
                'block' => '0',
                'amount' => '2',
                'number_of_columns' => '2',
                'fields' => [
                    'template' => 'grid',
                    'image_style' => 'rectangle',
                    'image' => '449741',
                    'background_image' => '449743',
                    'title' => 'A New Podcast Series',
                    'body' => 'Our CISOs share their journey to becoming trusted industry leaders.

<em>Produced in conjunction with Security Weekly.</em>',
                ]
            ],
        ],
    ];

    foreach ($updated_content as $post_id => $cards) {
        foreach ($cards as $card) {
            $block_name = 'layouts_' . $card['layouts'] . '_columns_' . $card['columns'] . '_blocks';
            $block_id = $block_name . '_' . $card['block'];
            $card_machine_name = $block_id . '_grid_card_' . $card['id'];

            update_post_meta($post_id, $block_name, ['grid_card_list']);
            update_post_meta($post_id, $block_id . '_number_of_columns', $card['number_of_columns']);

            // WP structure requires setting the elements amount for repeater field type.
            update_post_meta($post_id, $block_id . '_grid_card', $card['amount']);

            foreach ($card['fields'] as $field_name => $field_value) {
                update_post_meta($post_id, $card_machine_name . '_' . $field_name, $field_value);
            }
        }
    }
}
