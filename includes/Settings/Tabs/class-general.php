<?php
/**
 * General Settings
 *
 * @package RSFV
 */

namespace RSFV\Settings;

use RSFV\Options;
use RSFV\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * General.
 */
class General extends Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'rsfv' );

		parent::__construct();
	}

	/**
	 * Gets available post types.
	 *
	 * @return mixed|array
	 */
	public function get_available_post_types() {
		$post_types                        = array();
		$all_post_types                    = \get_post_types();
		$post_types_with_thumbnail_support = \get_post_types_by_support( 'thumbnail' );

		// Just in case.
		if ( ! is_array( $post_types_with_thumbnail_support ) ) {
			$post_types_with_thumbnail_support = array();
		}

		foreach ( $all_post_types as $post_type ) {
			if ( ! isset( $post_types[ $post_type ] ) && in_array( $post_type, $post_types_with_thumbnail_support, true ) ) {
				$post_types[ $post_type ] = get_post_type_object( $post_type )->labels->name;
			}
		}

		return apply_filters(
			'rsfv_post_types_support',
			$post_types
		);
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section ID.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {

		$post_types = $this->get_available_post_types();

		$compatibility_engines = Plugin::get_instance()->theme_provider->get_selectable_engine_options();

		$current_engine = Options::get_instance()->get( 'active-theme-engine' );

		$settings = array(
			array(
				'title' => esc_html_x( 'Theme Compatibility Engine', 'settings title', 'rsfv' ),
				'desc'  => __( 'If featured videos aren\'t working as expected in your theme, you may need to set this from the list of supported theme engines. (Default engine follows standard WordPress rules, and may not work for all themes)', 'rsfv' ),
				'class' => 'rsfv-theme-compatibility-engine',
				'type'  => 'content',
				'id'    => 'rsfv-theme-compatibility',
			),
			array(
				'type' => 'title',
				'id'   => 'rsfv_theme_support_title',
			),
			array(
				'title'   => __( 'Status', 'rsfv' ),
				'desc'    => '',
				'id'      => 'theme-engine-status',
				'default' => __( 'Auto', 'rsfv' ),
				'class'   => 'disabled' !== $current_engine ? 'engine-active' : 'engine-inactive',
				'type'    => 'status',
				'current' => $compatibility_engines[ $current_engine ] ?? $current_engine,
			),
			array(
				'title'   => __( 'Set engine', 'rsfv' ),
				'desc'    => '',
				'id'      => 'theme-compatibility-engine',
				'default' => 'auto',
				'type'    => 'select',
				'options' => $compatibility_engines,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'rsfv_theme_support_title',
			),
			array(
				'title' => esc_html_x( 'Enable Post Types Support', 'settings title', 'rsfv' ),
				'desc'  => __( 'Please select the post types you wish to enable featured video support at.', 'rsfv' ),
				'class' => 'rsfv-enable-post-types',
				'type'  => 'content',
				'id'    => 'rsfv-enable-post-types',
			),
			array(
				'type' => 'title',
				'id'   => 'rsfv_post_types_title',
			),
			array(
				'title'   => '',
				'id'      => 'post_types',
				'default' => array(
					'post' => true,
				),
				'type'    => 'multi-checkbox',
				'options' => $post_types,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'rsfv_post_types_title',
			),
		);

		if ( ! Plugin::get_instance()->has_pro_active() ) {
			$settings = array_merge(
				$settings,
				array(
					array(
						'title' => esc_html_x( 'Global Aspect Ratio', 'settings title', 'rsfv' ),
						'desc'  => __( 'Set aspect ratio for featured videos shown sitewide.', 'rsfv' ),
						'class' => 'promo-aspect-ratios',
						'type'  => 'promo-content',
						'id'    => 'promo-aspect-ratios',
					),
					array(
						'type' => 'title',
						'id'   => 'rsfv_pro_aspect_ratio_title',
					),
					array(
						'title'   => __( 'Video Aspect Ratio', 'rsfv' ),
						'desc'    => __( 'Available in the Pro version.', 'rsfv' ),
						'id'      => 'promo-global-aspect-ratio',
						'default' => 'sixteen-nine',
						'type'    => 'promo-select',
						'options' => array(
							'sixteen-nine' => '16:9 (Default)',
							'one-one'      => '1:1',
							'three-two'    => '3:2',
							'four-three'   => '4:3',
						),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'rsfv_pro_aspect_ratio_title',
					),
				)
			);
		}

		$settings = apply_filters(
			'rsfv_general_settings',
			$settings
		);

		return apply_filters( 'rsfv_get_settings_' . $this->id, $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );

		Admin_Settings::save_fields( $settings );
		if ( $current_section ) {
			do_action( 'rsfv_update_options_' . $this->id . '_' . $current_section );
		}
	}
}

return new General();
