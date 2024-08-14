<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_65f3135147629',
	'title' => 'Webhook Sync',
	'fields' => array(
		array(
			'key' => 'field_65f31352dcfc5',
			'label' => 'Source of Sync',
			'name' => 'source_of_sync',
			'aria-label' => '',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'choices' => array(
				'multiple' => 'Multiple / CMS',
				'cerberus' => 'Cerberus',
				'convertr' => 'Convertr',
				'ppworks' => 'PPWorks',
				'swoogo' => 'Swoogo',
			),
			'default_value' => false,
			'return_format' => 'value',
			'multiple' => 0,
			'max' => '',
			'prepend' => '',
			'append' => '',
			'allow_null' => 1,
			'ui' => 1,
			'ajax' => 0,
			'placeholder' => '',
			'allow_custom' => 0,
			'search_placeholder' => '',
			'min' => '',
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
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'company_profile',
			),
		),
	),
	'menu_order' => 200,
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
	'graphql_field_name' => 'webhookSync',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
	),
	'modified' => 1713451847,
));

endif;