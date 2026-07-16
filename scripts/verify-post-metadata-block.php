<?php
/**
 * Verify the PNS Post Metadata and Post Details block render contracts.
 *
 * Run from the project root:
 *
 * wp eval-file app/public/wp-content/plugins/pns-blocks/scripts/verify-post-metadata-block.php
 *
 * The script is read-only. It renders blocks against explicit post context so
 * Query Loop and single-post use do not depend on ambient global state.
 *
 * @package PNS_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$errors   = array();
$registry = WP_Block_Type_Registry::get_instance();

foreach ( array( 'pns/post-metadata', 'pns/post-details' ) as $block_name ) {
	$block_type = $registry->get_registered( $block_name );

	if ( ! $block_type ) {
		$errors[] = sprintf( '%s is not registered.', $block_name );
		continue;
	}

	if ( ! empty( $block_type->supports['inserter'] ) ) {
		$errors[] = sprintf( '%s must be hidden from the inserter.', $block_name );
	}

	foreach ( array( 'postId', 'postType' ) as $context_key ) {
		if ( ! in_array( $context_key, $block_type->uses_context, true ) ) {
			$errors[] = sprintf( '%1$s must use %2$s block context.', $block_name, $context_key );
		}
	}
}

$posts = get_posts(
	array(
		'post_status'    => 'publish',
		'post_type'      => 'post',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
	)
);

$pages = get_posts(
	array(
		'post_status'    => 'publish',
		'post_type'      => 'page',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
	)
);

if ( empty( $posts ) ) {
	$errors[] = 'A published post is required to verify PNS post metadata rendering.';
}

if ( empty( $pages ) ) {
	$errors[] = 'A published page is required to verify empty post-detail taxonomy output.';
}

$render = static function ( $block_name, $post ) {
	$parsed_block = array(
		'blockName'    => $block_name,
		'attrs'        => array(),
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	);
	$block        = new WP_Block(
		$parsed_block,
		array(
			'postId'   => $post->ID,
			'postType' => $post->post_type,
		)
	);

	return $block->render();
};

if ( empty( $errors ) ) {
	$metadata_markup = $render( 'pns/post-metadata', $posts[0] );

	foreach ( array( 'pns-post-metadata', 'pns-post-meta', 'wp-block-post-date', 'wp-block-post-author' ) as $expected_class ) {
		if ( ! str_contains( $metadata_markup, $expected_class ) ) {
			$errors[] = sprintf( 'Post Metadata is missing %s.', $expected_class );
		}
	}

	if ( str_contains( $metadata_markup, 'pns-single-terms' ) ) {
		$errors[] = 'Post Metadata must never render taxonomy pills.';
	}

	$details_markup = $render( 'pns/post-details', $posts[0] );

	foreach ( array( 'pns-post-details', 'pns-post-metadata', 'pns-single-terms', 'pns-taxonomy-pills' ) as $expected_class ) {
		if ( ! str_contains( $details_markup, $expected_class ) ) {
			$errors[] = sprintf( 'Post Details is missing %s for a published post.', $expected_class );
		}
	}

	$empty_details_markup = $render( 'pns/post-details', $pages[0] );

	if ( ! str_contains( $empty_details_markup, 'pns-post-metadata' ) ) {
		$errors[] = 'Post Details must reuse PNS Post Metadata output.';
	}

	if ( str_contains( $empty_details_markup, 'pns-single-terms' ) ) {
		$errors[] = 'Post Details must suppress the taxonomy wrapper when the current post type has no supported taxonomies.';
	}
}

if ( ! empty( $errors ) ) {
	$message = implode( "\n", $errors );

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::error( $message );
	}

	throw new RuntimeException( $message );
}

$message = 'PNS Post Metadata and Post Details contracts passed: registration, explicit post context, metadata-only cards, hero details with taxonomy pills, and empty-taxonomy output are correct.';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::success( $message );
	return;
}

echo $message . PHP_EOL;
