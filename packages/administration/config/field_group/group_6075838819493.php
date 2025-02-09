<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_6075838819493',
	'title' => 'People Taxonomy',
	'fields' => array(
		array(
			'key' => 'field_607583ae4f5f5',
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
			'allow_null' => 0,
			'placeholder' => '',
			'multiple' => 1,
			'ajax' => 0,
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
		array(
			'key' => 'field_607583f54f5f6',
			'label' => 'Type',
			'name' => 'type',
			'aria-label' => '',
			'type' => 'acfe_taxonomy_terms',
			'instructions' => 'First type is a main type.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'taxonomy' => array(
				0 => 'people_type',
			),
			'allow_terms' => '',
			'allow_level' => '',
			'field_type' => 'select',
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
		array(
			'key' => 'field_62666ecf9fed5',
			'label' => 'SC Award',
			'name' => 'sc_award',
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
				0 => 'sc_award',
			),
			'allow_terms' => '',
			'allow_level' => '',
			'field_type' => 'select',
			'default_value' => array(
			),
			'return_format' => 'object',
			'populate_parental_terms' => 1,
			'ui' => 1,
			'allow_null' => 1,
			'placeholder' => '',
			'multiple' => 1,
			'ajax' => 0,
			'save_terms' => 1,
			'load_terms' => 0,
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
				'value' => 'people',
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
	'acfe_display_title' => '',
	'acfe_autosync' => array(
		0 => 'php',
		1 => 'json',
	),
	'acfe_form' => 0,
	'acfe_meta' => '',
	'acfe_note' => '',
	'show_in_graphql' => 1,
	'graphql_field_name' => 'peopleTaxonomy',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
		0 => 'Person',
	),
	'modified' => 1713451840,
));

endif;