<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_61bac418b3ff3',
	'title' => 'PPWorks Article Advanced',
	'fields' => array(
		array(
			'key' => 'field_6278fc7792bc8',
			'label' => 'Host',
			'name' => 'host',
			'aria-label' => '',
			'type' => 'post_object',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'post_type' => array(
				0 => 'people',
			),
			'taxonomy' => '',
			'allow_null' => 0,
			'multiple' => 0,
			'return_format' => 'object',
			'save_custom' => 0,
			'save_post_status' => 'publish',
			'acfe_bidirectional' => array(
				'acfe_bidirectional_enabled' => '0',
			),
			'ui' => 1,
			'bidirectional_target' => array(
			),
			'save_post_type' => '',
			'acfe_add_post' => 0,
			'acfe_edit_post' => 0,
			'min' => '',
			'max' => '',
		),
		array(
			'key' => 'field_6257e5d51449c',
			'label' => 'Position',
			'name' => 'position',
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
			'show_in_graphql' => 1,
			'default_value' => 1,
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'min' => '',
			'max' => '',
			'step' => 1,
		),
		array(
			'key' => 'field_61bac42e5ed53',
			'label' => 'Source Link',
			'name' => 'source_link',
			'aria-label' => '',
			'type' => 'url',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'default_value' => '',
			'placeholder' => '',
		),
		array(
			'key' => 'field_621729c619b7f',
			'label' => 'Description',
			'name' => 'description',
			'aria-label' => '',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'default_value' => '',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => '',
			'new_lines' => '',
			'acfe_textarea_code' => 0,
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'ppworks_article',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'hide_on_screen' => array(
		0 => 'block_editor',
		1 => 'the_content',
		2 => 'excerpt',
	),
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
	'graphql_field_name' => 'ppworksArticleAdvanced',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
		0 => 'PPWorksArticle',
	),
	'modified' => 1713451846,
));

endif;