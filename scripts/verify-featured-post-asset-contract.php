<?php
/**
 * Verify the Featured Post block's shared Split Section asset contract.
 *
 * Run from the project root:
 *
 * wp eval-file app/public/wp-content/plugins/pns-blocks/scripts/verify-featured-post-asset-contract.php
 *
 * This is intentionally read-only. It checks that source and generated
 * metadata agree, then verifies that WordPress exposes the expected frontend
 * and editor style handles on the registered Featured Post block.
 *
 * @package PNS_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$expected_assets = array(
	'editorStyle' => 'pns-split-section-editor-style',
	'style'       => 'pns-split-section-style',
);
$metadata_files = array(
	'source' => PNS_BLOCKS_PLUGIN_DIR . 'blocks/query/featured-post/block.json',
	'build'  => PNS_BLOCKS_PLUGIN_DIR . 'build/blocks/query/featured-post/block.json',
);
$errors = array();

foreach ( $metadata_files as $label => $metadata_file ) {
	$metadata = json_decode( (string) file_get_contents( $metadata_file ), true );

	if ( ! is_array( $metadata ) ) {
		$errors[] = sprintf( '%s metadata is not valid JSON: %s', $label, $metadata_file );
		continue;
	}

	foreach ( $expected_assets as $field => $expected_handle ) {
		if ( ( $metadata[ $field ] ?? null ) !== $expected_handle ) {
			$errors[] = sprintf(
				'%1$s metadata must declare %2$s as %3$s.',
				$label,
				$field,
				$expected_handle
			);
		}
	}

	if ( false !== ( $metadata['supports']['inserter'] ?? null ) ) {
		$errors[] = sprintf( '%s metadata must hide PNS Featured Post from the inserter.', $label );
	}
}

$registry      = WP_Block_Type_Registry::get_instance();
$featured_post = $registry->get_registered( 'pns/featured-post' );

if ( ! $featured_post ) {
	$errors[] = 'pns/featured-post is not registered.';
} else {
	if ( ! in_array( $expected_assets['style'], $featured_post->style_handles, true ) ) {
		$errors[] = 'pns/featured-post does not expose the Split Section frontend style handle.';
	}

	if ( ! in_array( $expected_assets['editorStyle'], $featured_post->editor_style_handles, true ) ) {
		$errors[] = 'pns/featured-post does not expose the Split Section editor style handle.';
	}

	if ( false !== ( $featured_post->supports['inserter'] ?? null ) ) {
		$errors[] = 'pns/featured-post must be hidden from the inserter.';
	}
}

foreach ( $expected_assets as $handle ) {
	if ( ! wp_style_is( $handle, 'registered' ) ) {
		$errors[] = sprintf( 'Expected shared style handle is not registered: %s.', $handle );
	}
}

if ( ! empty( $errors ) ) {
	$message = implode( "\n", $errors );

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::error( $message );
	}

	throw new RuntimeException( $message );
}

$message = 'Featured Post contract passed: source/build metadata, template-only inserter policy, and registered frontend/editor Split Section styles agree.';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::success( $message );
	return;
}

echo $message . PHP_EOL;
