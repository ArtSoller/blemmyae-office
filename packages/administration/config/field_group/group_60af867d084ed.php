<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_60af867d084ed',
	'title' => 'Generate Newsletter Options',
	'fields' => array(
		array(
			'key' => 'field_60af869a05635',
			'label' => 'Newsletter Type',
			'name' => 'ctn_generate_newsletter_type',
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
			'acfe_permissions' => '',
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
			'placeholder' => '',
			'multiple' => 0,
			'ajax' => 0,
			'save_terms' => 0,
			'load_terms' => 0,
			'acfe_settings' => '',
			'acfe_validate' => '',
			'choices' => array(
			),
			'layout' => '',
			'toggle' => 0,
			'allow_custom' => 0,
			'other_choice' => 0,
			'search_placeholder' => '',
			'min' => '',
			'max' => '',
		),
		array(
			'key' => 'field_60b072220a084',
			'label' => 'Number of Posts',
			'name' => 'ctn_generate_number_of_posts',
			'aria-label' => '',
			'type' => 'number',
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
			'default_value' => 10,
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'min' => 1,
			'max' => 20,
			'step' => 1,
			'acfe_settings' => '',
			'acfe_validate' => '',
		),
		array(
			'key' => 'field_60b073590a085',
			'label' => 'Publish Status',
			'name' => 'ctn_generate_publish_status',
			'aria-label' => '',
			'type' => 'select',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'acfe_permissions' => '',
			'show_in_graphql' => 0,
			'choices' => array(
				'draft' => 'Draft',
				'publish' => 'Publish',
			),
			'default_value' => 'draft',
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 1,
			'ajax' => 0,
			'return_format' => 'value',
			'allow_custom' => 0,
			'search_placeholder' => '',
			'acfe_settings' => '',
			'acfe_validate' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'min' => '',
			'max' => '',
		),
		array(
			'key' => 'field_63451a60d05ea',
			'label' => 'Subject',
			'name' => 'subject',
			'aria-label' => '',
			'type' => 'text',
			'instructions' => 'Subject to set for the newsletter email in Marketo.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_field' => '',
			'hide_label' => '',
			'hide_instructions' => '',
			'hide_required' => '',
			'show_in_graphql' => 1,
			'default_value' => '',
			'recommended_upper_limit' => 0,
			'recommended_lower_limit' => 0,
			'acfe_settings' => '',
			'acfe_validate' => '',
			'required_message' => '',
			'maxlength' => '',
			'instruction_placement' => '',
			'acfe_permissions' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_63451ae8d05eb',
			'label' => 'Schedule Date',
			'name' => 'schedule_date',
			'aria-label' => '',
			'type' => 'date_picker',
			'instructions' => 'This field is going to be used to only set the names of the associated folder and program in Marketo.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'acfe_permissions' => '',
			'show_in_graphql' => 1,
			'display_format' => 'F j, Y',
			'return_format' => 'd/m/Y',
			'first_day' => 1,
			'acfe_settings' => '',
			'acfe_validate' => '',
			'placeholder' => '',
			'min_date' => '',
			'max_date' => '',
			'no_weekends' => 0,
		),
		array(
			'key' => 'field_6346306abcdb7',
			'label' => 'Available Topics',
			'name' => 'available_topics',
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
			'acfe_permissions' => '',
			'show_in_graphql' => 1,
			'taxonomy' => 'topic',
			'field_type' => 'multi_select',
			'allow_null' => 0,
			'add_term' => 0,
			'save_terms' => 1,
			'load_terms' => 0,
			'return_format' => 'id',
			'populate_parental_terms' => 0,
			'display_hierarchical' => 1,
			'acfe_bidirectional' => array(
				'acfe_bidirectional_enabled' => '0',
			),
			'acfe_settings' => '',
			'acfe_validate' => '',
			'multiple' => 0,
			'min' => '',
			'max' => '',
			'bidirectional_target' => array(
			),
		),
		array(
			'key' => 'field_60af8a8805636',
			'label' => 'Generate',
			'name' => 'ctn_generate_button',
			'aria-label' => '',
			'type' => 'acfe_button',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'acfe_permissions' => '',
			'show_in_graphql' => 1,
			'button_value' => 'Generate',
			'button_type' => 'button',
			'button_class' => 'button button-primary',
			'button_id' => '',
			'button_before' => '',
			'button_after' => '',
			'button_ajax' => 0,
			'acfe_settings' => '',
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'options_page',
				'operator' => '==',
				'value' => 'acf-options-generate-newsletter',
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
	'acfe_form' => 1,
	'acfe_display_title' => '',
	'acfe_permissions' => '',
	'acfe_meta' => '',
	'acfe_note' => '',
	'show_in_graphql' => 0,
	'graphql_field_name' => 'generateNewsletter',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => '',
	'modified' => 1718625277,
));

endif;