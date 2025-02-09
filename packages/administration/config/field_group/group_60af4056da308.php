<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_60af4056da308',
	'title' => 'Newsletter Options',
	'fields' => array(
		array(
			'key' => 'field_60af49717f9e7',
			'label' => 'Allowed Topics by Newsletter Type',
			'name' => 'allowed_topics_by_newsletter_type',
			'aria-label' => '',
			'type' => 'repeater',
			'instructions' => 'If a newsletter type is not specified here then it is assumed that all topics are allowed for it.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'acfe_repeater_stylised_button' => 0,
			'collapsed' => '',
			'min' => 0,
			'max' => 0,
			'layout' => 'table',
			'button_label' => 'Add Row',
			'rows_per_page' => 20,
			'sub_fields' => array(
				array(
					'key' => 'field_60af4b937f9e8',
					'label' => 'Newsletter Type',
					'name' => 'newsletter_type',
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
						0 => 'newsletter_type',
					),
					'allow_terms' => '',
					'allow_level' => '',
					'field_type' => 'select',
					'max' => '',
					'default_value' => array(
					),
					'return_format' => 'object',
					'ui' => 1,
					'allow_null' => 0,
					'search_placeholder' => '',
					'multiple' => 0,
					'ajax' => 0,
					'save_terms' => 0,
					'load_terms' => 0,
					'populate_parental_terms' => 0,
					'display_hierarchical' => 0,
					'required_message' => '',
					'choices' => array(
					),
					'placeholder' => '',
					'layout' => '',
					'toggle' => 0,
					'allow_custom' => 0,
					'other_choice' => 0,
					'min' => '',
					'parent_repeater' => 'field_60af49717f9e7',
				),
				array(
					'key' => 'field_60af4d237f9e9',
					'label' => 'Topics',
					'name' => 'topics',
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
						0 => 'topic',
					),
					'allow_terms' => '',
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
					'ajax' => 0,
					'save_terms' => 0,
					'load_terms' => 0,
					'populate_parental_terms' => 0,
					'display_hierarchical' => 0,
					'required_message' => '',
					'choices' => array(
					),
					'search_placeholder' => '',
					'layout' => '',
					'toggle' => 0,
					'allow_custom' => 0,
					'other_choice' => 0,
					'parent_repeater' => 'field_60af49717f9e7',
				),
			),
		),
		array(
			'key' => 'field_649d48feac699',
			'label' => 'Allowed Applications by Newsletter Type',
			'name' => 'allowed_apps_by_newsletter_type',
			'aria-label' => '',
			'type' => 'repeater',
			'instructions' => 'If a newsletter type is not specified here then it is assumed that all applications are allowed for it.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'acfe_repeater_stylised_button' => 0,
			'layout' => 'table',
			'pagination' => 0,
			'min' => 0,
			'max' => 0,
			'collapsed' => '',
			'button_label' => 'Add Row',
			'rows_per_page' => 20,
			'sub_fields' => array(
				array(
					'key' => 'field_649d496aac69a',
					'label' => 'Newsletter Type',
					'name' => 'newsletter_type',
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
						0 => 'newsletter_type',
					),
					'allow_terms' => '',
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
					'save_terms' => 0,
					'load_terms' => 0,
					'populate_parental_terms' => 0,
					'display_hierarchical' => 0,
					'choices' => array(
					),
					'placeholder' => '',
					'layout' => '',
					'toggle' => 0,
					'allow_custom' => 0,
					'other_choice' => 0,
					'min' => '',
					'max' => '',
					'parent_repeater' => 'field_649d48feac699',
				),
				array(
					'key' => 'field_649d49efac69b',
					'label' => 'Applications',
					'name' => 'apps',
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
						0 => 'applications',
					),
					'allow_terms' => '',
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
					'ajax' => 0,
					'save_terms' => 0,
					'load_terms' => 0,
					'populate_parental_terms' => 0,
					'display_hierarchical' => 0,
					'choices' => array(
					),
					'search_placeholder' => '',
					'layout' => '',
					'toggle' => 0,
					'allow_custom' => 0,
					'other_choice' => 0,
					'parent_repeater' => 'field_649d48feac699',
				),
			),
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'options_page',
				'operator' => '==',
				'value' => 'acf-options-newsletter-options',
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
	'show_in_graphql' => 1,
	'graphql_field_name' => 'newsletterOptions',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
	),
	'modified' => 1713451841,
));

endif;