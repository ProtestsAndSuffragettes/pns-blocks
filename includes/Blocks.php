<?php
/**
 * Block registration for PNS Blocks.
 *
 * @package PNS_Blocks
 */

namespace PNS\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers project-owned block types.
 */
final class Blocks {
	/**
	 * Register all compiled block metadata directories.
	 *
	 * @return void
	 */
	public static function register() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		foreach ( self::block_directories() as $block_directory ) {
			register_block_type( $block_directory );
		}
	}

	/**
	 * Find block directories that contain block.json metadata.
	 *
	 * The build tree is the runtime source of truth. The source tree fallback
	 * exists only so early development checkouts fail softly before the first
	 * build has run.
	 *
	 * @return string[]
	 */
	private static function block_directories() {
		$blocks_root = self::blocks_root();

		if ( ! is_dir( $blocks_root ) ) {
			return array();
		}

		$block_directories = array();
		$iterator          = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$blocks_root,
				\FilesystemIterator::SKIP_DOTS
			)
		);

		foreach ( $iterator as $file ) {
			if ( 'block.json' !== $file->getFilename() ) {
				continue;
			}

			$block_directories[] = $file->getPath();
		}

		sort( $block_directories );

		return $block_directories;
	}

	/**
	 * Resolve the preferred block metadata root.
	 *
	 * @return string
	 */
	private static function blocks_root() {
		$build_root = PNS_BLOCKS_PLUGIN_DIR . 'build/blocks';

		if ( is_dir( $build_root ) ) {
			return $build_root;
		}

		return PNS_BLOCKS_PLUGIN_DIR . 'blocks';
	}
}
