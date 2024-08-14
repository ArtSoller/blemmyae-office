<?php 

acfe_register_options_page(array(
    'menu_slug' => 'on-demand-revalidation',
    'page_title' => 'Revalidation Page',
    'active' => true,
    'menu_title' => 'Revalidate Page',
    'capability' => 'edit_posts',
    'parent_slug' => 'tools.php',
    'position' => null,
    'icon_url' => null,
    'redirect' => true,
    'post_id' => 'options',
    'autoload' => null,
    'update_button' => 'Update',
    'updated_message' => 'The URL was submitted',
    'acfe_autosync' => array(
        'php',
        'json',
    ),
    'modified' => 1715610037,
));

