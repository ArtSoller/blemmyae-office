<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_61b07a0ea187b',
	'title' => 'People References',
	'fields' => array(
		array(
			'key' => 'field_61b07b73ad8e1',
			'label' => 'Appearances as Host',
			'name' => 'appearances_as_host',
			'aria-label' => '',
			'type' => 'post_object',
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
				0 => 'ppworks_episode',
				1 => 'ppworks_segment',
			),
			'taxonomy' => '',
			'allow_null' => 0,
			'multiple' => 1,
			'return_format' => 'object',
			'save_custom' => 0,
			'save_post_status' => 'publish',
			'acfe_bidirectional' => array(
				'acfe_bidirectional_enabled' => true,
				'acfe_bidirectional_related' => array(
					0 => 'field_61af359bac981',
					1 => 'field_61af4624d96a5',
				),
			),
			'ui' => 1,
			'bidirectional_target' => array(
			),
			'save_post_type' => '',
			'acfe_add_post' => 0,
			'acfe_edit_post' => 0,
			'min' => '',
			'max' => '',
		),
		array(
			'key' => 'field_61b07d0668b19',
			'label' => 'Appearances as Guest',
			'name' => 'appearances_as_guest',
			'aria-label' => '',
			'type' => 'post_object',
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
				0 => 'ppworks_episode',
				1 => 'ppworks_segment',
			),
			'taxonomy' => '',
			'allow_null' => 0,
			'multiple' => 1,
			'return_format' => 'object',
			'save_custom' => 0,
			'save_post_status' => 'publish',
			'acfe_bidirectional' => array(
				'acfe_bidirectional_enabled' => true,
				'acfe_bidirectional_related' => array(
					0 => 'field_61af3a32ac982',
					1 => 'field_61af4650d96a6',
				),
			),
			'ui' => 1,
			'bidirectional_target' => array(
			),
			'save_post_type' => '',
			'acfe_add_post' => 0,
			'acfe_edit_post' => 0,
			'min' => '',
			'max' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'people',
			),
		),
	),
	'menu_order' => 3,
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
	'show_in_graphql' => 1,
	'graphql_field_name' => 'peopleReferences',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
		0 => 'Person',
	),
	'modified' => 1713451846,
));

endif;