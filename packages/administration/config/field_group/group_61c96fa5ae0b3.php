<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_61c96fa5ae0b3',
	'title' => 'Swoogo Speaker Advanced',
	'fields' => array(
		array(
			'key' => 'field_6628c416ffcc1',
			'label' => 'First Name',
			'name' => 'swoogo_first_name',
			'aria-label' => '',
			'type' => 'text',
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
			'recommended_upper_limit' => 0,
			'recommended_lower_limit' => 0,
			'maxlength' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_6628c5708c696',
			'label' => 'Middle Name',
			'name' => 'swoogo_middle_name',
			'aria-label' => '',
			'type' => 'text',
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
			'recommended_upper_limit' => 0,
			'recommended_lower_limit' => 0,
			'maxlength' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_6628c5808c697',
			'label' => 'Last Name',
			'name' => 'swoogo_last_name',
			'aria-label' => '',
			'type' => 'text',
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
			'recommended_upper_limit' => 0,
			'recommended_lower_limit' => 0,
			'maxlength' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_61c971fb8b498',
			'label' => 'Swoogo ID',
			'name' => 'swoogo_id',
			'aria-label' => '',
			'type' => 'text',
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
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_61c971868b496',
			'label' => 'Bio',
			'name' => 'swoogo_bio',
			'aria-label' => '',
			'type' => 'wysiwyg',
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
			'tabs' => 'all',
			'toolbar' => 'basic',
			'media_upload' => 0,
			'delay' => 0,
			'acfe_wysiwyg_auto_init' => 0,
			'acfe_wysiwyg_height' => 300,
			'acfe_wysiwyg_min_height' => 300,
			'acfe_wysiwyg_max_height' => '',
			'acfe_wysiwyg_valid_elements' => '',
			'acfe_wysiwyg_custom_style' => '',
			'acfe_wysiwyg_disable_wp_style' => 0,
			'acfe_wysiwyg_autoresize' => 0,
			'acfe_wysiwyg_disable_resize' => 0,
			'acfe_wysiwyg_remove_path' => 0,
			'acfe_wysiwyg_menubar' => 0,
			'acfe_wysiwyg_transparent' => 0,
			'acfe_wysiwyg_merge_toolbar' => 0,
			'acfe_wysiwyg_custom_toolbar' => 0,
			'acfe_wysiwyg_toolbar_buttons' => array(
			),
		),
		array(
			'key' => 'field_61c9717a8b495',
			'label' => 'Company',
			'name' => 'swoogo_company',
			'aria-label' => '',
			'type' => 'post_object',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'post_type' => array(
				0 => 'company_profile',
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
			'save_post_type' => '',
			'bidirectional_target' => array(
			),
			'acfe_add_post' => 0,
			'acfe_edit_post' => 0,
			'min' => '',
			'max' => '',
		),
		array(
			'key' => 'field_61c970898b494',
			'label' => 'Job Title',
			'name' => 'swoogo_job_title',
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
			'taxonomy' => 'job_title',
			'field_type' => 'select',
			'allow_null' => 0,
			'add_term' => 1,
			'save_terms' => 1,
			'load_terms' => 0,
			'return_format' => 'object',
			'populate_parental_terms' => 0,
			'acfe_bidirectional' => array(
				'acfe_bidirectional_enabled' => '0',
			),
			'multiple' => 0,
			'bidirectional_target' => array(
			),
			'min' => '',
			'max' => '',
		),
		array(
			'key' => 'field_61c972448b499',
			'label' => 'Phone',
			'name' => 'swoogo_phone',
			'aria-label' => '',
			'type' => 'text',
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
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_61c972698b49a',
			'label' => 'Email',
			'name' => 'swoogo_email',
			'aria-label' => '',
			'type' => 'email',
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
			'prepend' => '',
			'append' => '',
		),
		array(
			'key' => 'field_61c971b58b497',
			'label' => 'Twitter',
			'name' => 'swoogo_twitter',
			'aria-label' => '',
			'type' => 'url',
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
		),
		array(
			'key' => 'field_61c972b88b49b',
			'label' => 'Direct Link',
			'name' => 'swoogo_direct_link',
			'aria-label' => '',
			'type' => 'url',
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
		),
		array(
			'key' => 'field_61c9730e8b49c',
			'label' => 'Birth Date',
			'name' => 'swoogo_birth_date',
			'aria-label' => '',
			'type' => 'date_picker',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'display_format' => 'd/m/Y',
			'return_format' => 'd/m/Y',
			'first_day' => 1,
			'placeholder' => '',
			'min_date' => '',
			'max_date' => '',
			'no_weekends' => 0,
		),
		array(
			'key' => 'field_61c9784bec6e4',
			'label' => 'Headshot',
			'name' => 'swoogo_headshot',
			'aria-label' => '',
			'type' => 'image',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'show_in_graphql' => 1,
			'uploader' => '',
			'acfe_thumbnail' => 0,
			'return_format' => 'array',
			'preview_size' => 'thumbnail',
			'min_width' => '',
			'min_height' => '',
			'min_size' => '',
			'max_width' => '',
			'max_height' => '',
			'max_size' => '',
			'mime_types' => '',
			'library' => 'all',
			'upload_folder' => '',
		),
		array(
			'key' => 'field_62ff76184cebc',
			'label' => 'Swoogo Hash',
			'name' => 'swoogo_hash',
			'aria-label' => '',
			'type' => 'text',
			'instructions' => 'Unique string created from First name, Last name and Email. Used as custom ID for mapping.',
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
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array(
			'key' => 'field_62ff74c54f381',
			'label' => 'Regions Collection',
			'name' => 'regions_collection',
			'aria-label' => '',
			'type' => 'repeater',
			'instructions' => '',
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
					'key' => 'field_62ff74e44f382',
					'label' => 'Community Region',
					'name' => 'swoogo_community_region',
					'aria-label' => '',
					'type' => 'taxonomy',
					'instructions' => 'Will also automatically add parental regions.',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'show_in_graphql' => 1,
					'taxonomy' => 'community_region',
					'field_type' => 'multi_select',
					'allow_null' => 0,
					'add_term' => 0,
					'save_terms' => 1,
					'load_terms' => 0,
					'return_format' => 'object',
					'populate_parental_terms' => 1,
					'acfe_bidirectional' => array(
						'acfe_bidirectional_enabled' => '0',
					),
					'multiple' => 0,
					'bidirectional_target' => array(
					),
					'min' => '',
					'max' => '',
					'parent_repeater' => 'field_62ff74c54f381',
				),
				array(
					'key' => 'field_62ff74e74f383',
					'label' => 'Speaker Type',
					'name' => 'swoogo_speaker_type',
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
					'taxonomy' => 'swoogo_speaker_type',
					'add_term' => 1,
					'save_terms' => 1,
					'load_terms' => 0,
					'return_format' => 'object',
					'field_type' => 'multi_select',
					'min' => '',
					'max' => '',
					'allow_null' => 0,
					'populate_parental_terms' => 1,
					'display_hierarchical' => 0,
					'acfe_bidirectional' => array(
						'acfe_bidirectional_enabled' => '0',
					),
					'bidirectional' => 0,
					'multiple' => 0,
					'bidirectional_target' => array(
					),
					'parent_repeater' => 'field_62ff74c54f381',
				),
			),
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
	'menu_order' => 6,
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
	'graphql_field_name' => 'swoogoSpeakerAdvanced',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
		0 => 'Person',
	),
	'modified' => 1713948087,
));

endif;