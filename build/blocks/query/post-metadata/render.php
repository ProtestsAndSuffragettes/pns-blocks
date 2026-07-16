<?php
/**
 * Render callback for pns/post-metadata.
 *
 * @package PNS_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = isset( $block->context['postId'] ) ? absint( $block->context['postId'] ) : get_the_ID();

if ( ! $post_id || ! get_post( $post_id ) ) {
	return;
}

$post_type = isset( $block->context['postType'] ) ? $block->context['postType'] : get_post_type( $post_id );

if ( ! is_string( $post_type ) || ! post_type_supports( $post_type, 'author' ) ) {
	return;
}

$post_date = get_post_field( 'post_date', $post_id );
$author_id = (int) get_post_field( 'post_author', $post_id );

if ( ! is_string( $post_date ) || '' === $post_date || ! $author_id ) {
	return;
}

$post_timestamp = strtotime( $post_date );

if ( false === $post_timestamp ) {
	return;
}

$date_markup = sprintf(
	'<div class="wp-block-post-date"><time datetime="%1$s">%2$s</time></div>',
	esc_attr( $post_date ),
	esc_html( wp_date( get_option( 'date_format' ), $post_timestamp ) )
);

$author_markup = sprintf(
	'<div class="wp-block-post-author"><div class="wp-block-post-author__content"><p class="wp-block-post-author__name"><a href="%1$s">%2$s</a></p></div></div>',
	esc_url( get_author_posts_url( $author_id ) ),
	esc_html( get_the_author_meta( 'display_name', $author_id ) )
);

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'pns-post-metadata',
	)
);

echo sprintf(
	'<div %1$s><div class="pns-post-meta">%2$s%3$s</div></div>',
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributes are escaped by WordPress.
	$date_markup, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup is assembled from escaped WordPress values.
	$author_markup // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup is assembled from escaped WordPress values.
);
