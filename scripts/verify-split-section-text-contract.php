<?php
/**
 * Verify the Split Section Text | Text runtime contract.
 *
 * @package PNS_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_split_text_block = WP_Block_Type_Registry::get_instance()->get_registered( 'pns/split-section' );

if ( ! $pns_split_text_block instanceof WP_Block_Type ) {
	WP_CLI::error( 'pns/split-section is not registered.' );
}

$pns_split_text_fixture = '<!-- wp:pns/split-section {"mediaType":"text","layoutVariant":"edge-media-right","textVerticalAlignment":"top","secondaryTextVerticalAlignment":"bottom","align":"full"} -->'
	. '<!-- wp:columns {"align":"full","className":"pns-split-section__columns"} -->'
	. '<div class="wp-block-columns alignfull pns-split-section__columns">'
	. '<!-- wp:column {"backgroundColor":"brand-purple","textColor":"neutral-0","className":"pns-split-section__copy-column pns-split-section__text-column"} -->'
	. '<div class="wp-block-column pns-split-section__copy-column pns-split-section__text-column has-neutral-0-color has-brand-purple-background-color has-text-color has-background">'
	. '<!-- wp:group {"className":"pns-split-section__copy"} --><div class="wp-block-group pns-split-section__copy"><!-- wp:paragraph --><p>First panel</p><!-- /wp:paragraph --></div><!-- /wp:group -->'
	. '</div><!-- /wp:column -->'
	. '<!-- wp:column {"backgroundColor":"heritage-green","textColor":"neutral-0","className":"pns-split-section__copy-column pns-split-section__text-column"} -->'
	. '<div class="wp-block-column pns-split-section__copy-column pns-split-section__text-column has-neutral-0-color has-heritage-green-background-color has-text-color has-background">'
	. '<!-- wp:group {"className":"pns-split-section__copy"} --><div class="wp-block-group pns-split-section__copy"><!-- wp:paragraph --><p>Second panel</p><!-- /wp:paragraph --></div><!-- /wp:group -->'
	. '</div><!-- /wp:column -->'
	. '</div><!-- /wp:columns -->'
	. '<!-- /wp:pns/split-section -->';

$pns_split_text_rendered = do_blocks( $pns_split_text_fixture );

foreach ( array( 'pns-site-frame-panel', 'is-pns-text-text', 'is-style-pns-edge-media-right', 'is-pns-primary-text-align-top', 'is-pns-secondary-text-align-bottom', 'First panel', 'Second panel' ) as $pns_split_text_required ) {
	if ( ! str_contains( $pns_split_text_rendered, $pns_split_text_required ) ) {
		WP_CLI::error( sprintf( 'Rendered Text | Text fixture is missing "%s".', $pns_split_text_required ) );
	}
}

foreach ( array( 'is-layout-flex', 'is-vertical' ) as $pns_split_text_stack_marker ) {
	$pns_split_text_copy_stack_pattern = '/class="(?=[^"]*pns-split-section__copy)(?=[^"]*' . preg_quote( $pns_split_text_stack_marker, '/' ) . ')[^"]*"/';

	if ( preg_match( $pns_split_text_copy_stack_pattern, $pns_split_text_rendered ) ) {
		WP_CLI::error( sprintf( 'Rendered Text | Text copy Groups still expose the retired Stack marker "%s".', $pns_split_text_stack_marker ) );
	}
}

$pns_split_media_fixture = '<!-- wp:pns/split-section {"mediaType":"video","layoutVariant":"edge-media-right","textVerticalAlignment":"top","secondaryTextVerticalAlignment":"bottom","align":"full"} -->'
	. '<!-- wp:columns {"align":"full","className":"pns-split-section__columns"} --><div class="wp-block-columns alignfull pns-split-section__columns">'
	. '<!-- wp:column {"className":"pns-split-section__copy-column"} --><div class="wp-block-column pns-split-section__copy-column">'
	. '<!-- wp:group {"className":"pns-split-section__copy"} --><div class="wp-block-group pns-split-section__copy"><!-- wp:paragraph --><p>Video copy</p><!-- /wp:paragraph --></div><!-- /wp:group -->'
	. '</div><!-- /wp:column -->'
	. '<!-- wp:column {"className":"pns-split-section__media-column pns-split-section__media-column--video"} --><div class="wp-block-column pns-split-section__media-column pns-split-section__media-column--video"></div><!-- /wp:column -->'
	. '</div><!-- /wp:columns -->'
	. '<!-- /wp:pns/split-section -->';
$pns_split_media_rendered = do_blocks( $pns_split_media_fixture );

if ( ! str_contains( $pns_split_media_rendered, 'is-pns-primary-text-align-top' ) ) {
	WP_CLI::error( 'Rendered media fixture does not expose the text-side alignment class.' );
}

if ( str_contains( $pns_split_media_rendered, 'is-pns-secondary-text-align-' ) ) {
	WP_CLI::error( 'Rendered media fixture exposes a secondary alignment class on a media variation.' );
}

$pns_split_text_constrained_rendered = do_blocks(
	str_replace(
		'"layoutVariant":"edge-media-right"',
		'"layoutVariant":"media-right"',
		$pns_split_text_fixture
	)
);

if ( ! str_contains( $pns_split_text_constrained_rendered, 'is-style-pns-media-right' ) ) {
	WP_CLI::error( 'Rendered constrained Text | Text fixture is missing its width contract.' );
}

$pns_split_text_reversed_rendered = do_blocks(
	str_replace(
		'"layoutVariant":"edge-media-right"',
		'"layoutVariant":"edge-media-left"',
		$pns_split_text_fixture
	)
);

if ( ! str_contains( $pns_split_text_reversed_rendered, 'is-style-pns-edge-media-left' ) ) {
	WP_CLI::error( 'Rendered reversed Text | Text fixture is missing its visual-order contract.' );
}

$pns_split_text_files = array(
	'editor source' => dirname( __DIR__ ) . '/blocks/layout/split-section/editor.css',
	'frontend source' => dirname( __DIR__ ) . '/blocks/layout/split-section/style.css',
	'built editor' => dirname( __DIR__ ) . '/build/blocks/layout/split-section/index.css',
	'built frontend' => dirname( __DIR__ ) . '/build/blocks/layout/split-section/style-index.css',
);

foreach ( $pns_split_text_files as $pns_split_text_label => $pns_split_text_path ) {
	$pns_split_text_contents = file_get_contents( $pns_split_text_path );

	if (
		false === $pns_split_text_contents
		|| ! str_contains( $pns_split_text_contents, 'is-pns-text-text' )
		|| ! str_contains( $pns_split_text_contents, 'is-style-pns-edge-media-left' )
		|| ! str_contains( $pns_split_text_contents, 'is-style-pns-media-right' )
	) {
		WP_CLI::error( sprintf( '%s does not contain the Text | Text layout contract.', $pns_split_text_label ) );
	}
}

WP_CLI::success( 'Split Section Text | Text runtime contract verified.' );
