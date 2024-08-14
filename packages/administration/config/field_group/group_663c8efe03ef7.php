<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_663c8efe03ef7',
	'title' => 'Revalidate Page',
	'fields' => array(
		array(
			'key' => 'field_663c8efe1b453',
			'label' => 'Absolute page URL',
			'name' => 'revalidate_page_url',
			'aria-label' => '',
			'type' => 'url',
			'instructions' => 'Enter a valid post URL
Example: https://www.scmagazine.com/news/lorem-ipsum',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'default_value' => '',
			'required_message' => 'The URL field is required',
			'placeholder' => 'Enter the url',
		),
		array(
			'key' => 'field_663c90201b454',
			'label' => 'Revalidate Cache',
			'name' => 'revalidate_page_submit',
			'aria-label' => '',
			'type' => 'acfe_button',
			'instructions' => 'The submit button revalidates the cached post page.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'button_value' => 'Submit',
			'button_type' => 'button',
			'button_class' => 'button button-primary revalidate-page-submit-btn',
			'button_id' => '',
			'button_before' => '',
			'button_after' => '',
			'button_ajax' => 0,
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'options_page',
				'operator' => '==',
				'value' => 'on-demand-revalidation',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'acf_after_title',
	'style' => 'seamless',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 0,
	'acfe_autosync' => array(
		0 => 'php',
		1 => 'json',
	),
	'acfe_form' => 0,
	'acfe_display_title' => '',
	'acfe_meta' => '',
	'acfe_note' => '',
	'show_in_graphql' => 0,
	'graphql_field_name' => 'revalidatePageFields',
	'map_graphql_types_from_location_rules' => 0,
	'graphql_types' => '',
	'modified' => 1715598747,
));

endif;