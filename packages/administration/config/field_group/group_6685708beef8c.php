<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_6685708beef8c',
	'title' => 'Taxonomy Term Settings',
	'fields' => array(
		array(
			'key' => 'field_6685708c5cd0f',
			'label' => 'Remove From Sitemap',
			'name' => 'remove_from_sitemap',
			'aria-label' => '',
			'type' => 'checkbox',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 0,
			'choices' => array(
				'remove' => 'Remove From Sitemap',
			),
			'default_value' => array(
			),
			'return_format' => 'value',
			'min' => '',
			'max' => '',
			'allow_custom' => 0,
			'layout' => 'vertical',
			'toggle' => 0,
			'save_custom' => 0,
			'custom_choice_button_text' => 'Add new choice',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'taxonomy',
				'operator' => '==',
				'value' => 'topic',
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
	'acfe_autosync' => array(
		0 => 'php',
		1 => 'json',
	),
	'acfe_form' => 0,
	'acfe_display_title' => '',
	'acfe_meta' => '',
	'acfe_note' => '',
	'show_in_graphql' => 0,
	'graphql_field_name' => 'taxonomyTermSetting',
	'map_graphql_types_from_location_rules' => 0,
	'graphql_types' => '',
	'modified' => 1720021397,
));

endif;