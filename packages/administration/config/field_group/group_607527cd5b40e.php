<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_607527cd5b40e',
	'title' => 'Company Profile Taxonomy',
	'fields' => array(
		array(
			'key' => 'field_6075281b01146',
			'label' => 'Type',
			'name' => 'type',
			'aria-label' => '',
			'type' => 'taxonomy',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'taxonomy' => 'company_profile_type',
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
		array(
			'key' => 'field_62666ecf9fed6',
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
				'value' => 'company_profile',
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
	'graphql_field_name' => 'companyProfileTaxonomy',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
		0 => 'CompanyProfile',
	),
	'modified' => 1713451840,
));

endif;