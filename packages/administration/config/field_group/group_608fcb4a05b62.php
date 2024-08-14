<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_608fcb4a05b62',
	'title' => 'Post Collection',
	'fields' => array(
		array(
			'key' => 'field_608fcb58aa715',
			'label' => 'Post(s)',
			'name' => 'post',
			'aria-label' => '',
			'type' => 'relationship',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'post_type' => array(
				0 => 'company_profile',
				1 => 'editorial',
				2 => 'landing',
				3 => 'learning',
				4 => 'newsletter',
				5 => 'whitepaper',
				6 => 'people',
				7 => 'product_profile',
			),
			'taxonomy' => '',
			'filters' => array(
				0 => 'search',
				1 => 'post_type',
			),
			'elements' => '',
			'min' => '',
			'max' => '',
			'return_format' => 'id',
			'acfe_bidirectional' => array(
				'acfe_bidirectional_enabled' => true,
				'acfe_bidirectional_related' => array(
					0 => 'field_608f8eda9ccf7',
				),
			),
			'bidirectional_target' => array(
			),
			'acfe_add_post' => 0,
			'acfe_edit_post' => 0,
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'people',
			),
			array(
				'param' => 'post_taxonomy',
				'operator' => '==',
				'value' => 'people_type:author',
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
	'show_in_rest' => false,
	'acfe_display_title' => '',
	'acfe_autosync' => array(
		0 => 'php',
		1 => 'json',
	),
	'acfe_form' => 0,
	'acfe_meta' => '',
	'acfe_note' => '',
	'show_in_graphql' => 1,
	'graphql_field_name' => 'postCollection',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
		0 => 'Person',
	),
	'modified' => 1713451841,
));

endif;