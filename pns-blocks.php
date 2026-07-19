<?php
/*
 * x-release-please-start-version
 */
/**
 * Plugin Name: PNS Blocks
 * Description: Project-owned portable blocks for Protests and Suffragettes.
 * Version: 0.2.0
 * Author: Protests and Suffragettes
 * Text Domain: pns-blocks
 * Requires at least: 6.5
 * Requires PHP: 8.0
 *
 * @package PNS_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PNS_BLOCKS_VERSION', '0.2.0' );
/*
 * x-release-please-end
 */
define( 'PNS_BLOCKS_PLUGIN_FILE', __FILE__ );
define( 'PNS_BLOCKS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PNS_BLOCKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once PNS_BLOCKS_PLUGIN_DIR . 'includes/Assets.php';
require_once PNS_BLOCKS_PLUGIN_DIR . 'includes/Blocks.php';

add_action( 'init', array( \PNS\Blocks\Blocks::class, 'register' ) );
add_action( 'init', array( \PNS\Blocks\Assets::class, 'register' ) );
