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
$layout_variant          = $attributes['layoutVariant'] ?? 'media-right';

if ( ! in_array( $layout_variant, $allowed_layout_variants, true ) ) {
	$layout_variant = 'media-right';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode(
			' ',
			array(
				'pns-section',
				'pns-layout',
				'pns-split-section',
				'pns-site-frame-panel',
				'is-style-pns-' . $layout_variant,
			)
		),
	)
);

echo '<div ' . $wrapper_attributes . '>' . $content . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Inner block content is rendered by WordPress blocks.
