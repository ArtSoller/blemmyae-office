<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_60758158cd6c2',
	'title' => 'Whitepaper Taxonomy',
	'fields' => array(
		array(
			'key' => 'field_6075818b362e5',
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
			'min' => '',
			'max' => '',
			'default_value' => array(
			),
			'return_format' => 'object',
			'ui' => 1,
			'allow_null' => 0,
			'placeholder' => '',
			'multiple' => 1,
			'ajax' => 1,
			'save_terms' => 1,
			'load_terms' => 1,
			'populate_parental_terms' => 0,
			'display_hierarchical' => 1,
			'choices' => array(
			),
			'search_placeholder' => '',
			'layout' => '',
			'toggle' => 0,
			'allow_custom' => 0,
			'other_choice' => 0,
		),
		array(
			'key' => 'field_60758285be3fe',
			'label' => 'Whitepaper Type',
			'name' => 'whitepaper_type',
			'aria-label' => '',
			'type' => 'taxonomy',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'taxonomy' => 'whitepaper_type',
			'field_type' => 'select',
			'allow_null' => 0,
			'add_term' => 0,
			'save_terms' => 1,
			'load_terms' => 1,
			'return_format' => 'object',
			'populate_parental_terms' => 0,
			'acfe_bidirectional' => array(
				'acfe_bidirectional_enabled' => '0',
			),
			'multiple' => 0,
			'min' => '',
			'max' => '',
			'bidirectional_target' => array(
			),
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'whitepaper',
			),
		),
	),
	'menu_order' => 1,
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
	'show_in_graphql' => 1,
	'graphql_field_name' => 'whitepaperTaxonomy',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
		0 => 'Whitepaper',
	),
	'modified' => 1713451840,
));

endif;