<?php
/*
 * Plugin Name: WP Custom Fonts
 * Author: Damiano Giacomazzi
 * Author URI: https://www.damianogiacomazzi.com/
 * Version: 1.0.0
 * Text Domain: dgzz
 * Description: Upload your custom fonts.
 */


if ( ! defined( "ABSPATH" ) ) {
	die( "You shouldnt be here" );
}

define( 'WPCF_PLUGIN_PLUGIN_FILE', __FILE__);
define( 'WPCF_PBNAME', plugin_basename(WPCF_PLUGIN_PLUGIN_FILE) );
define( 'WPCF_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define( 'WPCF_PLUGIN_URL', plugins_url("/", __FILE__ ));

class WPCF_Plugin {

    /**
	 * Plugin Version
	 * @var string The plugin version.
	 */
	const VERSION = '1.0.0';

	/**
	 * Minimum PHP Version
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.0';

    /**
     * @var WPCF_Plugin
     */
    private static $instance;

	public function __construct() {

        add_action( 'init', [$this, 'init'] );
        add_action( 'init', [$this, 'dgzz_fonts_cpt'] );
  
    }

    public function init() {

		add_action( 'admin_enqueue_scripts', [$this, 'dgzz_admin_scripts'] );
        add_filter( 'upload_mimes', [$this,'add_custom_upload_mimes'] );
		add_filter( 'wp_check_filetype_and_ext', array( $this, 'update_mime_types' ), 10, 3 );
		add_action( 'admin_menu', [$this, 'dgzz_admin_add_menu_page'] );
		add_action( 'admin_menu', [$this, 'dgzz_remove_menu_items'] );
		add_filter( 'csf_field_typography_customwebfonts', [$this, 'dgzz_add_fonts_to_lists'] );
		add_action( 'add_meta_boxes', [$this, 'dgzz_fonts_cpt_metaboxes'] );
		add_action( 'save_post', [$this, 'dgzz_fonts_cpt_metaboxes_save'], 1, 2);
		add_action( 'wp_head', [$this, 'dgzz_add_fonts_style'] );

		/**
		 * Add Font Group
		 */
		add_filter( 'elementor/fonts/groups', function( $font_groups ) {
			$font_groups['custom_fonts'] = __( 'Custom Fonts' );
			return $font_groups;
		} );

		/**
		 * Add Group Fonts
		 */
		add_filter( 'elementor/fonts/additional_fonts', function( $additional_fonts ) {
			// Key/value
			//Font name/font group
			$customwebfonts = $this->dgzz_add_fonts_to_lists();

			foreach($customwebfonts as $font) {
				$additional_fonts[$font] = 'custom_fonts';
			}
			return $additional_fonts;
		} );

	}

	public function add_custom_upload_mimes($existing_mimes) {
		$existing_mimes['otf'] = 'font/otf';
		$existing_mimes['woff'] = 'application/x-font-woff';
		$existing_mimes['ttf'] = 'application/x-font-ttf';
		$existing_mimes['svg'] = 'image/svg+xml';
		$existing_mimes['eot'] = 'application/vnd.ms-fontobject';
		return $existing_mimes;
	}

	public function update_mime_types( $defaults, $file, $filename ) {
		if ( 'ttf' === pathinfo( $filename, PATHINFO_EXTENSION ) ) {
			$defaults['type'] = 'application/x-font-ttf';
			$defaults['ext']  = 'ttf';
		}

		if ( 'otf' === pathinfo( $filename, PATHINFO_EXTENSION ) ) {
			$defaults['type'] = 'application/x-font-otf';
			$defaults['ext']  = 'otf';
		}

		return $defaults;
	}

	public function dgzz_admin_scripts() {
		wp_enqueue_style( 'plugin-css', WPCF_PLUGIN_URL . 'inc/style.css' );
		wp_enqueue_script( 'main-js', WPCF_PLUGIN_URL . 'inc/main.js', ['jquery'], '1.0.0', true );
	}



	/*
	 * ADD FONTS STYLE
	 */

	public function dgzz_add_fonts_to_lists() {
		global $wpdb;
		$customwebfonts = array();

		$customwebfonts = $wpdb->get_col(
			"SELECT post_title
			FROM $wpdb->posts
			WHERE post_type = 'dgzz_wp_custom_fonts'
			AND post_status IN ('publish')"
		);

		return $customwebfonts;
	}



	/*
	 * ADD FONTS STYLE
	 */

	public function dgzz_add_fonts_style() {
		$args = [
			'post_type' => 'dgzz_wp_custom_fonts'
		];

		$fonts_ext = [
			'woff2', 'woff', 'ttf', 'eot', 'svg', 'otf'
		];

		$the_query = new WP_Query( $args );
		
		if ( $the_query->have_posts() ) {
			echo '<style id="wp-custom-fonts">';
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$post_meta = get_post_meta(get_the_ID());
				echo "
				@font-face { 
					font-family: " . get_the_title() . "; 
					src:";
				foreach ($fonts_ext as $font_ext) {
					if ( isset ($post_meta['dgzz-cf-'.$font_ext][0]) && $post_meta['dgzz-cf-'.$font_ext][0] != "" )
					echo "url(" . $post_meta['dgzz-cf-'.$font_ext][0] . "); \n";
				}
				echo "}";
			}
			echo '</style>';
		} else {
			return;
		}

		wp_reset_postdata();
	}
	


	/* 
     * REGISTER FONTS CUSTOM POST TYPE
     */

	public function dgzz_fonts_cpt() {
		$labels = array(
			'name' => __( 'Custom Fonts', 'kinsta' ),
			'singular_name' => __( 'Custom Font', 'kinsta' ),
			'add_new' => __( 'New Custom Font', 'kinsta' ),
			'add_new_item' => __( 'Add New Custom Font', 'kinsta' ),
			'edit_item' => __( 'Edit Custom Font', 'kinsta' ),
			'new_item' => __( 'New Custom Font', 'kinsta' ),
			'view_item' => __( 'View Custom Fonts', 'kinsta' ),
			'search_items' => __( 'Search Custom Fonts', 'kinsta' ),
			'not_found' =>  __( 'No Custom Fonts Found', 'kinsta' ),
			'not_found_in_trash' => __( 'No Custom Fonts found in Trash', 'kinsta' ),
		);

		$args = array(
			'labels' => $labels,
			'has_archive' => false,
			'public' => true,
			'hierarchical' => false,
			'supports' => array(
				'title',
			),
			'show_in_rest' => true
		);

		register_post_type( 'dgzz_wp_custom_fonts', $args );

	}

	public function dgzz_remove_menu_items() {
		//remove_menu_page( 'edit.php?post_type=dgzz_wp_custom_fonts' );
	}



	/* 
     * CF METABOXES
     */

	public function dgzz_fonts_cpt_metaboxes() {
		$fonts_ext = [
			'woff2', 'woff', 'ttf', 'eot', 'svg', 'otf'
		];
		
		foreach ($fonts_ext as $font_ext) {
			add_meta_box(
				'dgzz_cf_'.$font_ext,
				'Font .'.$font_ext,
				[$this, 'dgzz_add_font_field_'.$font_ext],
				'dgzz_wp_custom_fonts'
			);
		}
	}

	public function dgzz_add_font_field_woff2() {
		
		wp_reset_postdata();

		wp_nonce_field( basename( __FILE__ ), 'dgzz_cf_fields' );
		$name = get_post_meta( get_the_ID(), 'dgzz-cf-woff2', true );
		echo '<input type="text" name="dgzz-cf-woff2" value="' . esc_textarea( $name )  . '" class="widefat">';
	}

	public function dgzz_add_font_field_woff() {
		global $post;

		wp_nonce_field( basename( __FILE__ ), 'dgzz_cf_fields' );
		$name = get_post_meta( $post->ID, 'dgzz-cf-woff', true );
		echo '<input type="text" name="dgzz-cf-woff" value="' . esc_textarea( $name )  . '" class="widefat">';
	}

	public function dgzz_add_font_field_ttf() {
		global $post;

		wp_nonce_field( basename( __FILE__ ), 'dgzz_cf_fields' );
		$name = get_post_meta( $post->ID, 'dgzz-cf-ttf', true );
		echo '<input type="text" name="dgzz-cf-ttf" value="' . esc_textarea( $name )  . '" class="widefat">';
	}

	public function dgzz_add_font_field_eot() {
		global $post;

		wp_nonce_field( basename( __FILE__ ), 'dgzz_cf_fields' );
		$name = get_post_meta( $post->ID, 'dgzz-cf-eot', true );
		echo '<input type="text" name="dgzz-cf-eot" value="' . esc_textarea( $name )  . '" class="widefat">';
	}

	public function dgzz_add_font_field_svg() {
		global $post;

		wp_nonce_field( basename( __FILE__ ), 'dgzz_cf_fields' );
		$name = get_post_meta( $post->ID, 'dgzz-cf-svg', true );
		echo '<input type="text" name="dgzz-cf-svg" value="' . esc_textarea( $name )  . '" class="widefat">';
	}

	public function dgzz_add_font_field_otf() {
		global $post;

		wp_nonce_field( basename( __FILE__ ), 'dgzz_cf_fields' );
		$name = get_post_meta( $post->ID, 'dgzz-cf-otf', true );
		echo '<input type="text" name="dgzz-cf-otf" value="' . esc_textarea( $name )  . '" class="widefat">';
	}
	

	public function dgzz_fonts_cpt_metaboxes_save( $post_id, $post ) {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		$fonts_ext = [
			'woff2', 'woff', 'ttf', 'eot', 'svg', 'otf'
		];

		foreach ($fonts_ext as $font_ext) {
			if (isset($_POST['dgzz-cf-'.$font_ext])) {
				$fonts_meta['dgzz-cf-'.$font_ext] = esc_textarea( $_POST['dgzz-cf-'.$font_ext] );
			}
		}

		if (isset($fonts_meta)) {
			foreach ( $fonts_meta as $key => $value ) :
				if ( get_post_meta( $post_id, $key, false ) ) {
					update_post_meta( $post_id, $key, $value );
				} else {
					add_post_meta( $post_id, $key, $value);
				}
				if ( ! $value ) {
					delete_post_meta( $post_id, $key );
				}
			endforeach;
		}
	}



	/* 
     * ADD ADMIN MENU PAGE 
     */

	public function dgzz_admin_add_menu_page() {
		add_menu_page(
			__( 'WP Custom Fonts', 'dgzz' ),
			__( 'WP Custom Fonts', 'dgzz' ),
			'manage_options',
			'wpcf-admin',
			[$this, 'dgzz_panel_main_tab']
		);
	}

    public function dgzz_panel_main_tab() {
        if ( ! current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'dgzz' ) );
        }
        require_once WPCF_PLUGIN_PATH . '/inc/screens/base.php';
    }



	/* 
     * GET PLUGIN CLASS INSTANCE
     */
	public static function getInstance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPCF_Plugin ) ) {
			self::$instance = new WPCF_Plugin();
		}
		return self::$instance;
    }

}

WPCF_Plugin::getInstance();