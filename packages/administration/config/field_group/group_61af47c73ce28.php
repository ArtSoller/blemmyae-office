<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_61af47c73ce28',
	'title' => 'Publishing Options',
	'fields' => array(
		array(
			'key' => 'field_61af47d3a014d',
			'label' => 'Unpublish Date',
			'name' => 'unpublish_date',
			'aria-label' => '',
			'type' => 'date_time_picker',
			'instructions' => 'Set if the post should be unpublished after a certain date.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 0,
			'display_format' => 'F j, Y g:i a',
			'return_format' => 'c',
			'first_day' => 1,
			'placeholder' => '',
			'min_date' => '',
			'max_date' => '',
			'min_time' => '',
			'max_time' => '',
			'no_weekends' => '',
			'min_hour' => '',
			'max_hour' => '',
			'min_min' => '',
			'max_min' => '',
			'min_sec' => '',
			'max_sec' => '',
		),
	),
	'location' => array(
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
				'value' => 'whitepaper',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'newsletter',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'side',
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
	'graphql_field_name' => 'publishingOptions',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
	),
	'modified' => 1713451845,
));

endif;