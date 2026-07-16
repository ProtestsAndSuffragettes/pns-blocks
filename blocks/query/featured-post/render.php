<?php
/**
 * Render callback for pns/featured-post.
 *
 * @package PNS_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults   = array(
	'postType'      => 'post',
	'heading'       => __( 'Featured News:', 'pns-blocks' ),
	'moreText'      => __( 'Read latest news', 'pns-blocks' ),
	'order'         => 'desc',
	'orderBy'       => 'date',
	'offset'        => 0,
	'layoutVariant' => 'edge-media-left',
	'taxonomies'    => array(),
);
$attributes = wp_parse_args( $attributes, $defaults );

$allowed_post_types = array( 'post', 'herstory', 'page' );
$post_type          = in_array( $attributes['postType'], $allowed_post_types, true ) ? $attributes['postType'] : 'post';

$allowed_orders = array( 'asc', 'desc' );
$order          = strtolower( (string) $attributes['order'] );
$order          = in_array( $order, $allowed_orders, true ) ? $order : 'desc';

$allowed_order_by = array( 'date', 'menu_order', 'title' );
$order_by         = in_array( $attributes['orderBy'], $allowed_order_by, true ) ? $attributes['orderBy'] : 'date';

$allowed_layout_variants = array(
	'media-left',
	'media-right',
	'edge-media-left',
	'edge-media-right',
);
$layout_variant          = in_array( $attributes['layoutVariant'], $allowed_layout_variants, true ) ? $attributes['layoutVariant'] : 'edge-media-left';

$query = new WP_Query(
	array(
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'offset'              => max( 0, absint( $attributes['offset'] ) ),
		'order'               => strtoupper( $order ),
		'orderby'             => $order_by,
		'post_status'         => 'publish',
		'post_type'           => $post_type,
		'posts_per_page'      => 1,
	)
);

if ( ! $query->have_posts() ) {
	return;
}

$render_post_block = static function ( $block_name, $attrs = array() ) {
	return render_block(
		array(
			'blockName'    => $block_name,
			'attrs'        => $attrs,
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		)
	);
};

$taxonomy_defaults = array(
	'post'     => array( 'category', 'post_tag' ),
	'herstory' => array( 'herstory_tag' ),
	'page'     => array(),
);
$taxonomies        = is_array( $attributes['taxonomies'] ) && ! empty( $attributes['taxonomies'] ) ? $attributes['taxonomies'] : $taxonomy_defaults[ $post_type ];
$taxonomies        = array_values(
	array_filter(
		array_map(
			static function ( $taxonomy ) use ( $post_type ) {
				if ( ! is_string( $taxonomy ) || '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
					return '';
				}

				return is_object_in_taxonomy( $post_type, $taxonomy ) ? $taxonomy : '';
			},
			$taxonomies
		)
	)
);

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => implode(
			' ',
			array(
				'pns-featured-post',
				'pns-featured-post--' . sanitize_html_class( $post_type ),
				'pns-section',
				'pns-site-frame-panel',
			)
		),
	)
);

$heading = trim( wp_strip_all_tags( (string) $attributes['heading'] ) );
if ( '' === $heading ) {
	$heading = 'herstory' === $post_type ? __( 'Featured Herstory:', 'pns-blocks' ) : __( 'Featured News:', 'pns-blocks' );
}

$more_text = trim( wp_strip_all_tags( (string) $attributes['moreText'] ) );
if ( '' === $more_text ) {
	$more_text = __( 'Read more', 'pns-blocks' );
}

global $post;

$previous_post = $post;

ob_start();

while ( $query->have_posts() ) {
	$query->the_post();

	$current_post_type = get_post_type();
	$show_post_meta    = 'page' !== $current_post_type;
	?>
	<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributes are escaped by WordPress. ?>>
		<div class="wp-block-pns-split-section pns-section pns-layout pns-split-section is-style-pns-<?php echo esc_attr( $layout_variant ); ?>">
			<div class="wp-block-columns alignfull pns-split-section__columns">
				<div class="wp-block-column pns-split-section__media-column">
					<?php echo $render_post_block( 'core/post-featured-image', array( 'isLink' => true, 'aspectRatio' => '1', 'sizeSlug' => 'square', 'style' => array( 'color' => array( 'duotone' => 'unset' ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered by WordPress blocks. ?>
				</div>

				<div class="wp-block-column pns-split-section__copy-column">
					<div class="wp-block-group pns-split-section__copy">
						<h2 class="wp-block-heading"><?php echo esc_html( $heading ); ?></h2>
						<?php echo $render_post_block( 'core/post-title', array( 'level' => 3, 'isLink' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered by WordPress blocks. ?>

						<?php if ( $show_post_meta ) : ?>
							<div class="wp-block-group pns-post-card__meta pns-featured-post__meta">
								<?php echo $render_post_block( 'core/post-date', array( 'className' => 'pns-post-card__date' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered by WordPress blocks. ?>
								<?php echo $render_post_block( 'core/post-author', array( 'showAvatar' => false, 'isLink' => true, 'className' => 'pns-post-card__author' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered by WordPress blocks. ?>
							</div>
						<?php endif; ?>

						<?php echo $render_post_block( 'core/post-excerpt', array( 'moreText' => $more_text ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered by WordPress blocks. ?>

						<?php if ( ! empty( $taxonomies ) ) : ?>
							<div class="wp-block-group pns-featured-post__footer pns-taxonomy-pills">
								<?php foreach ( $taxonomies as $taxonomy ) : ?>
									<?php echo $render_post_block( 'core/post-terms', array( 'term' => $taxonomy, 'separator' => '', 'className' => 'pns-featured-post__term-list pns-taxonomy-pills__list' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered by WordPress blocks. ?>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</section>
	<?php
}

wp_reset_postdata();
$post = $previous_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restore the post context after a local query render.

echo trim( ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The buffer is assembled from escaped values and rendered WordPress blocks.
