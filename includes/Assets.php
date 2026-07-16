<?php
/**
 * Shared asset helpers for PNS Blocks.
 *
 * @package PNS_Blocks
 */

namespace PNS\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides shared asset helpers for block registration.
 */
final class Assets {
	/**
	 * Register asset URL filters.
	 *
	 * Local and script-optimizer output can lose the Local port when absolute
	 * plugin URLs are serialized. Root-relative plugin URLs avoid that failure
	 * mode without changing the asset path or version.
	 *
	 * @return void
	 */
	public static function register() {
		add_filter( 'script_loader_src', array( self::class, 'root_relative_src' ), 20, 2 );
		add_filter( 'style_loader_src', array( self::class, 'root_relative_src' ), 20, 2 );
		add_filter( 'js_do_concat', array( self::class, 'skip_concatenation' ), 20, 2 );
		add_filter( 'css_do_concat', array( self::class, 'skip_concatenation' ), 20, 2 );
	}

	/**
	 * Build a plugin-relative asset URL.
	 *
	 * @param string $relative_path Plugin-relative asset path.
	 * @return string
	 */
	public static function url( $relative_path ) {
		return PNS_BLOCKS_PLUGIN_URL . ltrim( $relative_path, '/' );
	}

	/**
	 * Return a cache-busting version for a plugin-relative file.
	 *
	 * @param string $relative_path Plugin-relative file path.
	 * @return string
	 */
	public static function version( $relative_path ) {
		$file = PNS_BLOCKS_PLUGIN_DIR . ltrim( $relative_path, '/' );

		if ( file_exists( $file ) ) {
			return (string) filemtime( $file );
		}

		return PNS_BLOCKS_VERSION;
	}

	/**
	 * Convert this plugin's absolute asset URLs to root-relative URLs.
	 *
	 * @param string $src    Asset URL.
	 * @param string $handle Asset handle.
	 * @return string
	 */
	public static function root_relative_src( $src, $handle ) {
		if ( 0 !== strpos( $handle, 'pns-' ) ) {
			return $src;
		}

		$path = wp_parse_url( $src, PHP_URL_PATH );

		if ( ! is_string( $path ) || false === strpos( $path, '/wp-content/plugins/pns-blocks/' ) ) {
			return $src;
		}

		$query = wp_parse_url( $src, PHP_URL_QUERY );

		if ( is_string( $query ) && '' !== $query ) {
			return $path . '?' . $query;
		}

		return $path;
	}

	/**
	 * Keep project block assets out of script/style concatenators.
	 *
	 * Jetpack Boost's concatenator captures asset paths before WordPress filters
	 * the final loader URL, which can drop Local's port in generated output.
	 * This uses the same handle-based exclusion hook Boost uses for compatibility.
	 *
	 * @param bool   $do_concat Whether the asset should be concatenated.
	 * @param string $handle    Asset handle.
	 * @return bool
	 */
	public static function skip_concatenation( $do_concat, $handle ) {
		if ( 0 === strpos( $handle, 'pns-' ) || 0 === strpos( $handle, 'ran-' ) ) {
			return false;
		}

		return $do_concat;
	}
}
