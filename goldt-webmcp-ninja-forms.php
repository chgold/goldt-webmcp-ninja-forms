<?php
/**
 * Plugin Name: Goldnat for Ninja Forms
 * Plugin URI: https://goldnat.ai/plugins/goldt-webmcp-ninja-forms
 * Description: Ninja Forms integration for Goldnat — exposes forms, fields, actions and submissions as AI tools via the Goldnat WebMCP Bridge.
 * Version: 1.1.0
 * Author: chgold
 * Author URI: https://goldnat.ai/
 * License: Proprietary
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: goldt-webmcp-bridge, ninja-forms
 * Text Domain: goldt-webmcp-ninja-forms
 *
 * @package GoldtWebMCP\NinjaForms
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GOLDTWMCP_NF_VERSION', '1.1.0' );
define( 'GOLDTWMCP_NF_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Verify the free Bridge core + Ninja Forms are present.
 *
 * @return bool
 */
function goldtwmcp_nf_check_dependencies() {
	if ( ! defined( 'GOLDTWMCP_VERSION' ) ) {
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-error"><p><strong>Goldnat for Ninja Forms:</strong> Requires the free Goldnat WebMCP Bridge plugin.</p></div>';
			}
		);
		return false;
	}
	if ( ! function_exists( 'Ninja_Forms' ) ) {
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-error"><p><strong>Goldnat for Ninja Forms:</strong> Requires the Ninja Forms plugin.</p></div>';
			}
		);
		return false;
	}
	return true;
}

add_action(
	'plugins_loaded',
	function () {
		if ( ! goldtwmcp_nf_check_dependencies() ) {
			return;
		}
		add_action( 'goldtwmcp_register_modules', 'goldtwmcp_nf_register_modules' );
	},
	30
);

/**
 * Instantiate + register the Ninja Forms module.
 *
 * @param \GoldtWebMCP\Core\GoldtWebMCP $goldtwmcp Bridge instance.
 * @return void
 */
function goldtwmcp_nf_register_modules( $goldtwmcp ) {
	require_once GOLDTWMCP_NF_PATH . 'includes/modules/class-ninjaforms-module.php';
	$manifest = $goldtwmcp->get_manifest_instance();
	$module   = new \GoldtWebMCP\Modules\NinjaForms_Module( $manifest );
	$goldtwmcp->register_external_module( 'ninjaforms', $module );
}
