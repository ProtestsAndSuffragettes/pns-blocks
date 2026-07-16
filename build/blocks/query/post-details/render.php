<?php
/**
 * Render callback for pns/post-details.
 *
 * @package PNS_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$context = $block->context;

if ( empty( $context['postId'] ) ) {
	$context['postId'] = get_the_ID();
}

if ( empty( $context['postType'] ) && ! empty( $context['postId'] ) ) {
	$context['postType'] = get_post_type( $context['postId'] );
}

$post_id = absint( $context['postId'] ?? 0 );

if ( ! $post_id || ! get_post( $post_id ) ) {
	return;
}

$metadata_block = new WP_Block(
	array(
		'blockName'    => 'pns/post-metadata',
		'attrs'        => array(),
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	),
	$context
);
$metadata_markup = $metadata_block->render();

if ( '' === $metadata_markup ) {
	return;
}

$post_type = $context['postType'] ?? get_post_type( $post_id );
$taxonomies = 'post' === $post_type ? array( 'category', 'post_tag' ) : array();
$terms      = array();

foreach ( $taxonomies as $taxonomy ) {
	$term_list = get_the_term_list( $post_id, $taxonomy, '', ' ', '' );

	if ( is_wp_error( $term_list ) || '' === $term_list ) {
		continue;
	}

	$terms[] = sprintf(
		'<div class="wp-block-post-terms pns-single-terms__list pns-taxonomy-pills__list taxonomy-%1$s">%2$s</div>',
		esc_attr( $taxonomy ),
		wp_kses_post( $term_list )
	);
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'pns-post-details',
	)
);

echo sprintf(
	'<div %1$s>%2$s%3$s</div>',
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributes are escaped by WordPress.
	$metadata_markup, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered by pns/post-metadata with the same block context.
	! empty( $terms ) ? '<div class="pns-single-terms pns-taxonomy-pills">' . implode( '', $terms ) . '</div>' : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Term markup is escaped by WordPress.
);
