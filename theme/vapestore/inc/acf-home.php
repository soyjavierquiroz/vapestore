<?php
/**
 * ACF field definitions for the fixed homepage.
 *
 * @package VapeStore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'acf_add_local_field_group' ) ) {
	acf_add_local_field_group(
		array(
			'key'      => 'group_vapestore_homepage_content',
			'title'    => __( 'Homepage Content', 'vapestore' ),
			'fields'   => array(
				array(
					'key'   => 'field_vapestore_home_hero_eyebrow',
					'label' => __( 'Hero Eyebrow', 'vapestore' ),
					'name'  => 'home_hero_eyebrow',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_vapestore_home_hero_title',
					'label' => __( 'Hero Title', 'vapestore' ),
					'name'  => 'home_hero_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_vapestore_home_hero_text',
					'label' => __( 'Hero Text', 'vapestore' ),
					'name'  => 'home_hero_text',
					'type'  => 'textarea',
				),
				array(
					'key'           => 'field_vapestore_home_hero_image',
					'label'         => __( 'Hero Image', 'vapestore' ),
					'name'          => 'home_hero_image',
					'type'          => 'image',
					'return_format' => 'id',
				),
				array(
					'key'   => 'field_vapestore_home_hero_primary_label',
					'label' => __( 'Hero Primary Button Label', 'vapestore' ),
					'name'  => 'home_hero_primary_label',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_vapestore_home_hero_secondary_label',
					'label' => __( 'Hero Secondary Button Label', 'vapestore' ),
					'name'  => 'home_hero_secondary_label',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_vapestore_home_benefit_1_title',
					'label' => __( 'Benefit 1 Title', 'vapestore' ),
					'name'  => 'home_benefit_1_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_vapestore_home_benefit_1_text',
					'label' => __( 'Benefit 1 Text', 'vapestore' ),
					'name'  => 'home_benefit_1_text',
					'type'  => 'textarea',
				),
				array(
					'key'   => 'field_vapestore_home_benefit_2_title',
					'label' => __( 'Benefit 2 Title', 'vapestore' ),
					'name'  => 'home_benefit_2_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_vapestore_home_benefit_2_text',
					'label' => __( 'Benefit 2 Text', 'vapestore' ),
					'name'  => 'home_benefit_2_text',
					'type'  => 'textarea',
				),
				array(
					'key'   => 'field_vapestore_home_benefit_3_title',
					'label' => __( 'Benefit 3 Title', 'vapestore' ),
					'name'  => 'home_benefit_3_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_vapestore_home_benefit_3_text',
					'label' => __( 'Benefit 3 Text', 'vapestore' ),
					'name'  => 'home_benefit_3_text',
					'type'  => 'textarea',
				),
				array(
					'key'   => 'field_vapestore_home_benefit_4_title',
					'label' => __( 'Benefit 4 Title', 'vapestore' ),
					'name'  => 'home_benefit_4_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_vapestore_home_benefit_4_text',
					'label' => __( 'Benefit 4 Text', 'vapestore' ),
					'name'  => 'home_benefit_4_text',
					'type'  => 'textarea',
				),
				array(
					'key'   => 'field_vapestore_home_about_eyebrow',
					'label' => __( 'About Eyebrow', 'vapestore' ),
					'name'  => 'home_about_eyebrow',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_vapestore_home_about_title',
					'label' => __( 'About Title', 'vapestore' ),
					'name'  => 'home_about_title',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_vapestore_home_about_text',
					'label' => __( 'About Text', 'vapestore' ),
					'name'  => 'home_about_text',
					'type'  => 'textarea',
				),
				array(
					'key'           => 'field_vapestore_home_about_image',
					'label'         => __( 'About Image', 'vapestore' ),
					'name'          => 'home_about_image',
					'type'          => 'image',
					'return_format' => 'id',
				),
				array(
					'key'   => 'field_vapestore_home_about_button_label',
					'label' => __( 'About Button Label', 'vapestore' ),
					'name'  => 'home_about_button_label',
					'type'  => 'text',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_type',
						'operator' => '==',
						'value'    => 'front_page',
					),
				),
			),
		)
	);
}
