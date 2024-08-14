<?php 

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_634fcdce7160c',
	'title' => 'Application',
	'fields' => array(
		array(
			'key' => 'field_634fcdce7572f',
			'label' => 'Application',
			'name' => 'application',
			'aria-label' => '',
			'type' => 'acfe_taxonomy_terms',
			'instructions' => 'In this field, you need to specify the front-end application or site for your content. For example: scm (sc magazine), csc (cyberleadersunite), ciso (cybersecuritycollaboration), e.t.c. </br> </br><b>NOTE</b> Right now, we are using the application field to support multiple applications for Landings, Events and Sessions. For all other content, you can use SCM as the default application.',
			'required' => 1,
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
			'max' => '',
			'default_value' => array(
			),
			'return_format' => 'object',
			'ui' => 1,
			'allow_null' => 0,
			'search_placeholder' => '',
			'multiple' => 0,
			'ajax' => 1,
			'save_terms' => 1,
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
		),
		array(
			'key' => 'field_646c59e467bbf',
			'label' => 'Application - Multiple value (Read-Only)',
			'name' => 'applications',
			'aria-label' => '',
			'type' => 'acfe_taxonomy_terms',
			'instructions' => 'Read-Only application field. </br> This field clone data from the application field. We need this field for future migration',
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
			'ajax' => 1,
			'save_terms' => 1,
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
		),
		array(
			'key' => 'field_6475be6ffc7a9',
			'label' => 'Application Slug',
			'name' => 'slug',
			'aria-label' => '',
			'type' => 'acfe_slug',
			'instructions' => 'Applications slugs. If you have an empty application slug field and save the post, then the slug will be generated based on the post title.',
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
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'editorial',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'landing',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'learning',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'newsletter',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'whitepaper',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'ppworks_announcement',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'ppworks_article',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'ppworks_episode',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'ppworks_segment',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'ppworks_sponsor_prog',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'sc_award_nominee',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'session',
			),
		),
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'testimonial',
			),
		),
	),
	'menu_order' => -10,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 1,
	'acfe_autosync' => array(
		0 => 'php',
		1 => 'json',
	),
	'acfe_form' => 0,
	'acfe_display_title' => '',
	'acfe_meta' => '',
	'acfe_note' => '',
	'show_in_graphql' => 1,
	'graphql_field_name' => 'applicationCollection',
	'map_graphql_types_from_location_rules' => 1,
	'graphql_types' => array(
		0 => 'ContentNode',
		1 => 'Editorial',
		2 => 'Landing',
		3 => 'Learning',
		4 => 'Newsletter',
		5 => 'PPWorksSponsorProgram',
		6 => 'PpworksEpisode',
		7 => 'PpworksSegment',
		8 => 'ScAwardNominee',
		9 => 'Session',
		10 => 'Whitepaper',
	),
	'modified' => 1716976530,
));

endif;