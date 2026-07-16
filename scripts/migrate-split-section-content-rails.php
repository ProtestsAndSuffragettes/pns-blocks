<?php
/**
 * Remove saved horizontal padding from Split Section copy wrappers.
 *
 * The block stylesheet now owns the content rail. This migration removes
 * inline left/right padding from existing copy groups so saved markup cannot
 * override the configurable theme rail. It preserves top/bottom padding and
 * every other block attribute.
 *
 * Run with:
 * wp eval-file app/public/wp-content/plugins/pns-blocks/scripts/migrate-split-section-content-rails.php
 * wp eval-file app/public/wp-content/plugins/pns-blocks/scripts/migrate-split-section-content-rails.php apply
 * wp eval-file app/public/wp-content/plugins/pns-blocks/scripts/migrate-split-section-content-rails.php post-id=1797 apply
 *
 * @package PNS_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_content_rail_migration_args = pns_content_rail_migration_parse_args();
$pns_content_rail_migration_run  = pns_content_rail_migration_run( $pns_content_rail_migration_args );

pns_content_rail_migration_print_report( $pns_content_rail_migration_run );

/**
 * @return array{apply:bool,post_id:int}
 */
function pns_content_rail_migration_parse_args() {
	$argv = $_SERVER['argv'] ?? array();
	$args = array(
		'apply'   => false,
		'post_id' => 0,
	);

	foreach ( $argv as $arg ) {
		if ( 'apply' === $arg || '--apply' === $arg ) {
			$args['apply'] = true;
			continue;
		}

		if ( 0 === strpos( $arg, '--post-id=' ) ) {
			$args['post_id'] = absint( substr( $arg, strlen( '--post-id=' ) ) );
			continue;
		}

		if ( 0 === strpos( $arg, 'post-id=' ) ) {
			$args['post_id'] = absint( substr( $arg, strlen( 'post-id=' ) ) );
		}
	}

	return $args;
}

/**
 * @param array{apply:bool,post_id:int} $args Parsed CLI arguments.
 * @return array<string,mixed>
 */
function pns_content_rail_migration_run( $args ) {
	$posts = pns_content_rail_migration_get_posts( $args );
	$run   = array(
		'apply'       => (bool) $args['apply'],
		'backup_file' => '',
		'checked'     => count( $posts ),
		'changed'     => 0,
		'blocks'      => 0,
		'posts'       => array(),
		'errors'      => array(),
	);
	$backup  = array();
	$updates = array();

	foreach ( $posts as $post ) {
		$stats   = array( 'blocks' => 0 );
		$blocks  = parse_blocks( $post->post_content );
		$updated = pns_content_rail_migration_transform_blocks( $blocks, $stats );

		if ( 0 === $stats['blocks'] ) {
			continue;
		}

		$new_content = serialize_blocks( $updated );

		if ( $new_content === $post->post_content ) {
			continue;
		}

		$run['changed']++;
		$run['blocks'] += $stats['blocks'];
		$run['posts'][] = array(
			'ID'          => (int) $post->ID,
			'post_type'   => $post->post_type,
			'post_status' => $post->post_status,
			'post_title'  => $post->post_title,
			'blocks'      => $stats['blocks'],
		);
		$backup[]      = array(
			'ID'           => (int) $post->ID,
			'post_type'    => $post->post_type,
			'post_status'  => $post->post_status,
			'post_title'   => $post->post_title,
			'post_content' => $post->post_content,
		);
		$updates[]     = array(
			'ID'           => (int) $post->ID,
			'post_content' => $new_content,
		);
	}

	if ( $args['apply'] && ! empty( $backup ) ) {
		$run['backup_file'] = pns_content_rail_migration_write_backup( $backup );

		foreach ( $updates as $update ) {
			$result = wp_update_post( wp_slash( $update ), true );

			if ( is_wp_error( $result ) ) {
				$run['errors'][] = sprintf( '%d: %s', $update['ID'], $result->get_error_message() );
				continue;
			}

			clean_post_cache( $update['ID'] );
		}
	}

	return $run;
}

/**
 * @param array{apply:bool,post_id:int} $args Parsed CLI arguments.
 * @return WP_Post[]
 */
function pns_content_rail_migration_get_posts( $args ) {
	global $wpdb;

	$where      = array(
		'post_content LIKE %s',
		"post_type <> 'revision'",
		"post_status <> 'trash'",
	);
	$query_args = array( '%pns-split-section__copy%' );

	if ( ! empty( $args['post_id'] ) ) {
		$where[]      = 'ID = %d';
		$query_args[] = (int) $args['post_id'];
	}

	$sql = $wpdb->prepare(
		"SELECT * FROM {$wpdb->posts} WHERE " . implode( ' AND ', $where ) . ' ORDER BY ID ASC',
		$query_args
	);

	return $wpdb->get_results( $sql );
}

/**
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @param array{blocks:int}              $stats Migration counters.
 * @return array<int,array<string,mixed>>
 */
function pns_content_rail_migration_transform_blocks( $blocks, &$stats ) {
	foreach ( $blocks as &$block ) {
		if ( ! empty( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = pns_content_rail_migration_transform_blocks( $block['innerBlocks'], $stats );
		}

		if ( ! pns_content_rail_migration_is_copy_group( $block ) ) {
			continue;
		}

		$changed = pns_content_rail_migration_remove_padding_attributes( $block );

		if ( pns_content_rail_migration_remove_padding_markup( $block ) ) {
			$changed = true;
		}

		if ( $changed ) {
			$stats['blocks']++;
		}
	}

	return $blocks;
}

/**
 * @param array<string,mixed> $block Parsed block.
 * @return bool
 */
function pns_content_rail_migration_is_copy_group( $block ) {
	if ( 'core/group' !== ( $block['blockName'] ?? '' ) ) {
		return false;
	}

	$class_name = $block['attrs']['className'] ?? '';

	return is_string( $class_name ) && false !== strpos( $class_name, 'pns-split-section__copy' );
}

/**
 * @param array<string,mixed> $block Parsed block.
 * @return bool
 */
function pns_content_rail_migration_remove_padding_attributes( &$block ) {
	$attrs   = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
	$padding = $attrs['style']['spacing']['padding'] ?? null;

	if ( ! is_array( $padding ) ) {
		return false;
	}

	$changed = false;

	foreach ( array( 'left', 'right' ) as $side ) {
		if ( array_key_exists( $side, $padding ) ) {
			unset( $padding[ $side ] );
			$changed = true;
		}
	}

	if ( ! $changed ) {
		return false;
	}

	if ( empty( $padding ) ) {
		unset( $attrs['style']['spacing']['padding'] );
	} else {
		$attrs['style']['spacing']['padding'] = $padding;
	}

	if ( empty( $attrs['style']['spacing'] ) ) {
		unset( $attrs['style']['spacing'] );
	}

	if ( empty( $attrs['style'] ) ) {
		unset( $attrs['style'] );
	}

	$block['attrs'] = $attrs;

	return true;
}

/**
 * @param array<string,mixed> $block Parsed block.
 * @return bool
 */
function pns_content_rail_migration_remove_padding_markup( &$block ) {
	$changed = false;

	if ( isset( $block['innerHTML'] ) && is_string( $block['innerHTML'] ) ) {
		$updated = pns_content_rail_migration_strip_horizontal_padding( $block['innerHTML'] );

		if ( $updated !== $block['innerHTML'] ) {
			$block['innerHTML'] = $updated;
			$changed            = true;
		}
	}

	if ( empty( $block['innerContent'] ) || ! is_array( $block['innerContent'] ) ) {
		return $changed;
	}

	foreach ( $block['innerContent'] as $index => $fragment ) {
		if ( ! is_string( $fragment ) ) {
			continue;
		}

		$updated = pns_content_rail_migration_strip_horizontal_padding( $fragment );

		if ( $updated !== $fragment ) {
			$block['innerContent'][ $index ] = $updated;
			$changed                         = true;
		}
	}

	return $changed;
}

/**
 * @param string $markup Static block wrapper markup.
 * @return string
 */
function pns_content_rail_migration_strip_horizontal_padding( $markup ) {
	$updated = preg_replace(
		'/\s*padding-(?:left|right)\s*:\s*[^;"\']*;?/',
		'',
		$markup
	);

	if ( null === $updated ) {
		return $markup;
	}

	return str_replace( ' style=""', '', $updated );
}

/**
 * @param array<int,array<string,mixed>> $backup Original records.
 * @return string
 */
function pns_content_rail_migration_write_backup( $backup ) {
	$backup_dir = dirname( PNS_BLOCKS_PLUGIN_DIR, 5 ) . '/docs/jobs/content-rail-db-backups';

	if ( ! is_dir( $backup_dir ) && ! wp_mkdir_p( $backup_dir ) ) {
		WP_CLI::error( sprintf( 'Could not create backup directory: %s', $backup_dir ) );
	}

	$file = $backup_dir . '/' . gmdate( 'Ymd-His' ) . '-split-section-copy-padding-before.json';

	file_put_contents(
		$file,
		wp_json_encode( $backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
	);

	return $file;
}

/**
 * @param array<string,mixed> $run Migration report.
 * @return void
 */
function pns_content_rail_migration_print_report( $run ) {
	$lines = array(
		'mode: ' . ( $run['apply'] ? 'apply' : 'dry-run' ),
		'checked: ' . $run['checked'],
		'changed_posts: ' . $run['changed'],
		'changed_blocks: ' . $run['blocks'],
	);

	if ( ! empty( $run['backup_file'] ) ) {
		$lines[] = 'backup_file: ' . $run['backup_file'];
	}

	foreach ( $run['posts'] as $post ) {
		$lines[] = sprintf(
			'changed: %s #%d (%s) — %d copy wrapper(s)',
			$post['post_type'],
			$post['ID'],
			$post['post_title'],
			$post['blocks']
		);
	}

	foreach ( $run['errors'] as $error ) {
		$lines[] = 'error: ' . $error;
	}

	WP_CLI::log( implode( "\n", $lines ) );

	if ( empty( $run['errors'] ) ) {
		WP_CLI::success( 'Split Section content-rail migration completed.' );
		return;
	}

	WP_CLI::warning( 'Split Section content-rail migration completed with errors.' );
}
