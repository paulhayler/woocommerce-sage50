<?php
/**
 * Plugin Name: Woocommerce Export Orders to Sage
 * Plugin URI: http://www.freshrecipe.co.uk/
 * Description: Export orders from Woocommerce to Sage Line 50
 * Author: Paul Hayler
 * Author URI: http://www.freshrecipe.co.uk/
 * Version: 1.0.0
 * Text Domain: woocommerce-sage-export
 * Domain Path: /i18n/languages/

 *
 * @package     woocommerce-sage-export
 * @author      Paul Hayler
 * @Category    Plugin
 * @copyright   Copyright (c) 2015 Fresh Recipe Ltd
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */
if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

// Check if WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	// do 2nd check for Multisite !
	include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	if ( ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
		return;
	}
}


include 'classes/class-wc-order-export-admin.php';
include 'classes/class-wc-order-export-engine.php';
include 'classes/class-wc-order-export-data-extractor.php';

$wc_order_export = new WC_Order_Export_Admin();
register_activation_hook( __FILE__, array($wc_order_export,'install') );
register_deactivation_hook( __FILE__, array($wc_order_export,'uninstall') );