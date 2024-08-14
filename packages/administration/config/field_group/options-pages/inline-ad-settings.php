<?php 

acfe_register_options_page(array(
    'menu_slug' => 'inline-ad-settings',
    'page_title' => 'Inline Ad Settings',
    'active' => true,
    'menu_title' => 'Inline Ad Settings',
    'capability' => 'edit_posts',
    'parent_slug' => null,
    'position' => 15,
    'icon_url' => 'dashicons-media-code',
    'redirect' => true,
    'post_id' => 'inline_ad_settings',
    'autoload' => null,
    'update_button' => 'Update',
    'updated_message' => 'Options Updated',
    'acfe_autosync' => array(
        'php',
        'json',
    ),
    'modified' => 1715610061,
));

