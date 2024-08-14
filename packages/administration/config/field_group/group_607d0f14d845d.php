<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_607d0f14d845d',
	'title' => 'Landing Taxonomy',
	'fields' => array(
		array(
			'key' => 'field_607d0fcebd4b3',
			'label' => 'Type',
			'name' => 'type',
			'aria-label' => '',
			'type' => 'acfe_taxonomy_terms',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'taxonomy' => array(
				0 => 'landing_type',
			),
			'allow_terms' => array(
				0 => 'all_landing_type',
			),
			'allow_level' => '',
			'field_type' => 'select',
			'default_value' => array(
			),
			'return_format' => 'object',
			'ui' => 1,
			'allow_null' => 0,
			'search_placeholder' => '',
			'multiple' => 0,
			'ajax' => 0,
			'save_terms' => 1,
			'load_terms' => 1,
			'populate_parental_terms' => 0,
			'choices' => array(
			),
			'placeholder' => '',
			'layout' => '',
			'toggle' => 0,
			'allow_custom' => 0,
			'other_choice' => 0,
			'min' => '',
			'max' => '',
		),
		array(
			'key' => 'field_607d0f183d443',
			'label' => 'Topic',
			'name' => 'topic',
			'aria-label' => '',
			'type' => 'acfe_taxonomy_terms',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'taxonomy' => array(
				0 => 'topic',
			),
			'allow_terms' => array(
				0 => 'all_topic',
			),
			'allow_level' => '',
			'field_type' => 'select',
			'default_value' => array(
			),
			'return_format' => 'object',
			'ui' => 1,
			'allow_null' => 1,
			'placeholder' => '',
			'multiple' => 1,
			'ajax' => 1,
			'save_terms' => 1,
			'load_terms' => 1,
			'populate_parental_terms' => 0,
			'choices' => array(
			),
			'search_placeholder' => '',
			'layout' => '',
			'toggle' => 0,
			'allow_custom' => 0,
			'other_choice' => 0,
			'min' => '',
			'max' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'landing',
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
	'show_in_graphql' => 1,
	'graphql_field_name' => 'landingTaxonomy',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
		0 => 'Landing',
	),
	'modified' => 1713451841,
));

endif;