<?php
/**
 * Render callback for pns/split-section.
 *
 * @package PNS_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$allowed_layout_variants = array(
	'media-left',
	'media-right',
	'edge-media-left',
	'edge-media-right',
);
$legacy_text_layouts     = array(
	'text-text'                      => 'edge-media-right',
	'text-text-reversed'             => 'edge-media-left',
	'text-text-constrained'          => 'media-right',
	'text-text-constrained-reversed' => 'media-left',
);
$saved_layout_variant    = $attributes['layoutVariant'] ?? 'media-right';
$is_text_text            = 'text' === ( $attributes['mediaType'] ?? '' ) || array_key_exists( $saved_layout_variant, $legacy_text_layouts );
$layout_variant          = $legacy_text_layouts[ $saved_layout_variant ] ?? $saved_layout_variant;

if ( ! in_array( $layout_variant, $allowed_layout_variants, true ) ) {
	$layout_variant = 'media-right';
}

$wrapper_classes = array(
	'pns-section',
	'pns-layout',
	'pns-split-section',
	'pns-site-frame-panel',
	'is-style-pns-' . $layout_variant,
);

if ( $is_text_text ) {
	$wrapper_classes[] = 'is-pns-text-text';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode( ' ', $wrapper_classes ),
	)
);

echo '<div ' . $wrapper_attributes . '>' . $content . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Inner block content is rendered by WordPress blocks.
