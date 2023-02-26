<?php

/**
 * ShopShape: Enhancing Your Shopping Experience
 *
 * @wordpress-plugin
 * Plugin Name:       ShopShape
 * Plugin URI:        https://devkabir.shop/plugin/shopshape/
 * Description:       Enhancing Your Shopping Experience By Customizing Woocommerce Pages
 * Version:           1.0.2
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Dev Kabir
 * Author URI:        https://devkabir.shop/
 * Text Domain:       shopshape
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

/**
 * A security measure to prevent direct access to the plugin file.
 */

use ShopShape\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loading the autoloader file which is responsible for loading all the classes.
 */
require __DIR__ . '/vendor/autoload.php';


/**
 * A WordPress hook that is called when the plugin is activated.
 *
 * Just clearing cache so that user can see update in the front at ease.
 */
register_activation_hook(
	__FILE__,
	function () {
		wp_cache_flush();
	}
);

/**
 * A WordPress hook that is called when the plugin is deactivated.
 *
 * Just clearing cache so that user can see update in the front at ease.
 */
register_deactivation_hook(
	__FILE__,
	function () {
		wp_cache_flush();
	}
);

/**
 * Load functionality by the current request is for which screen
 */
if ( wp_using_themes() ) {
	ShopShape\Cart\Page::get_instance();
}
if ( is_admin() ) {
	Settings::get_instance()->init();
}