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

    public function init() {

		

	}
}

WPCF_Plugin::getInstance();