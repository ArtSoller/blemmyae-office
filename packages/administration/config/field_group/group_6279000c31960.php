<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_6279000c31960',
	'title' => 'PPWorks Announcement Advanced',
	'fields' => array(
		array(
			'key' => 'field_627900213071e',
			'label' => 'Featured Image',
			'name' => 'featured_image',
			'aria-label' => '',
			'type' => 'image',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'uploader' => '',
			'acfe_thumbnail' => 0,
			'return_format' => 'array',
			'preview_size' => 'medium',
			'min_width' => '',
			'min_height' => '',
			'min_size' => '',
			'max_width' => '',
			'max_height' => '',
			'max_size' => '',
			'mime_types' => 'png,jpg,jpeg,webp,avif,svg',
			'library' => 'all',
			'upload_folder' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'ppworks_announcement',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 0,
	'acfe_display_title' => '',
	'acfe_autosync' => array(
		0 => 'php',
		1 => 'json',
	),
	'acfe_form' => 0,
	'acfe_meta' => '',
	'acfe_note' => '',
	'show_in_graphql' => 0,
	'graphql_field_name' => 'ppworksAnnouncementAdvanced',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
	),
	'modified' => 1713451847,
));

endif;