<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_608f8e9a4fe63',
	'title' => 'Author Collection',
	'fields' => array(
		array(
			'key' => 'field_608f8eda9ccf7',
			'label' => 'Author(s)',
			'name' => 'author',
			'aria-label' => '',
			'type' => 'relationship',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'acfe_permissions' => '',
			'show_in_graphql' => 1,
			'post_type' => array(
				0 => 'people',
			),
			'taxonomy' => array(
				0 => 'people_type:author',
			),
			'filters' => array(
				0 => 'search',
			),
			'elements' => '',
			'min' => 1,
			'max' => '',
			'return_format' => 'object',
			'acfe_bidirectional' => array(
				'acfe_bidirectional_enabled' => true,
				'acfe_bidirectional_related' => array(
					0 => 'field_608fcb58aa715',
				),
			),
			'acfe_settings' => array(
				'608fcaf120a3a' => array(
					'acfe_settings_location' => '',
					'acfe_settings_settings' => array(
						'608fcaf720a3b' => array(
							'acfe_settings_setting_type' => 'default_value',
							'acfe_settings_setting_operator' => '=',
							'acfe_settings_setting_value' => '196439',
						),
					),
				),
			),
			'acfe_validate' => '',
			'bidirectional_target' => array(
			),
			'acfe_add_post' => 0,
			'acfe_edit_post' => 0,
			'default_value' => '196439',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'company_profile',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'editorial',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'landing',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'learning',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'newsletter',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'product_profile',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'whitepaper',
			),
		),
	),
	'menu_order' => -1,
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
	'acfe_permissions' => '',
	'acfe_form' => 1,
	'acfe_meta' => '',
	'acfe_note' => '',
	'show_in_graphql' => 1,
	'graphql_field_name' => 'authorCollection',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
		0 => 'CompanyProfile',
		1 => 'Editorial',
		2 => 'Landing',
		3 => 'Learning',
		4 => 'Newsletter',
		5 => 'ProductProfile',
		6 => 'Whitepaper',
	),
	'modified' => 1713451841,
));

endif;