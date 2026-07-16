<?php
/**
 * Migrate legacy core/group split-section wrappers to pns/split-section.
 *
 * Run with:
 * wp eval-file app/public/wp-content/plugins/pns-blocks/scripts/migrate-split-section-blocks.php
 * wp eval-file app/public/wp-content/plugins/pns-blocks/scripts/migrate-split-section-blocks.php apply
 * wp eval-file app/public/wp-content/plugins/pns-blocks/scripts/migrate-split-section-blocks.php post-id=5654 apply
 *
 * @package PNS_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_split_section_args = pns_blocks_split_section_migration_parse_args();
$pns_split_section_run  = pns_blocks_split_section_migration_run( $pns_split_section_args );

pns_blocks_split_section_migration_print_report( $pns_split_section_run );

/**
 * Parse CLI arguments passed after `--`.
 *
 * @return array<string,mixed>
 */
function pns_blocks_split_section_migration_parse_args() {
	$argv = $_SERVER['argv'] ?? array();
	$args = array(
		'apply'           => false,
		'include_trash'   => false,
		'include_revision' => false,
		'post_id'         => 0,
	);

	foreach ( $argv as $arg ) {
		if ( 'apply' === $arg || '--apply' === $arg ) {
			$args['apply'] = true;
			continue;
		}

		if ( 'include-trash' === $arg || '--include-trash' === $arg ) {
			$args['include_trash'] = true;
			continue;
		}

		if ( 'include-revisions' === $arg || '--include-revisions' === $arg ) {
			$args['include_revision'] = true;
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
 * Run the migration in dry-run or apply mode.
 *
 * @param array<string,mixed> $args Parsed arguments.
 * @return array<string,mixed>
 */
function pns_blocks_split_section_migration_run( $args ) {
	$posts = pns_blocks_split_section_migration_get_posts( $args );
	$run   = array(
		'apply'       => (bool) $args['apply'],
		'backup_file' => '',
		'checked'     => count( $posts ),
		'changed'     => 0,
		'blocks'      => 0,
		'posts'       => array(),
		'errors'      => array(),
	);
	$backup = array();
	$updates = array();

	foreach ( $posts as $post ) {
		$stats   = array(
			'blocks' => 0,
		);
		$blocks  = parse_blocks( $post->post_content );
		$updated = pns_blocks_split_section_migration_transform_blocks( $blocks, $stats );

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

		$backup[] = array(
			'ID'           => (int) $post->ID,
			'post_type'    => $post->post_type,
			'post_status'  => $post->post_status,
			'post_title'   => $post->post_title,
			'post_content' => $post->post_content,
		);

		$updates[] = array(
			'ID'           => (int) $post->ID,
			'post_content' => $new_content,
		);
	}

	if ( $args['apply'] && ! empty( $backup ) ) {
		$run['backup_file'] = pns_blocks_split_section_migration_write_backup( $backup );

		foreach ( $updates as $update ) {
			$result = wp_update_post( wp_slash( $update ), true );

			if ( is_wp_error( $result ) ) {
				$run['errors'][] = sprintf(
					'%d: %s',
					$update['ID'],
					$result->get_error_message()
				);
			}
		}
	}

	return $run;
}

/**
 * Get candidate posts.
 *
 * @param array<string,mixed> $args Parsed arguments.
 * @return WP_Post[]
 */
function pns_blocks_split_section_migration_get_posts( $args ) {
	global $wpdb;

	$where = array(
		'post_content LIKE %s',
	);
	$query_args = array(
		'%pns-split-section%',
	);

	if ( ! empty( $args['post_id'] ) ) {
		$where[]      = 'ID = %d';
		$query_args[] = (int) $args['post_id'];
	}

	if ( empty( $args['include_revision'] ) ) {
		$where[] = "post_type <> 'revision'";
	}

	if ( empty( $args['include_trash'] ) ) {
		$where[] = "post_status <> 'trash'";
	}

	$sql = $wpdb->prepare(
		"SELECT * FROM {$wpdb->posts} WHERE " . implode( ' AND ', $where ) . ' ORDER BY ID ASC',
		$query_args
	);

	return $wpdb->get_results( $sql );
}

/**
 * Transform nested blocks.
 *
 * @param array<int,array<string,mixed>> $blocks Parsed blocks.
 * @param array<string,int>             $stats Migration stats.
 * @return array<int,array<string,mixed>>
 */
function pns_blocks_split_section_migration_transform_blocks( $blocks, &$stats ) {
	foreach ( $blocks as &$block ) {
		if ( ! empty( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = pns_blocks_split_section_migration_transform_blocks( $block['innerBlocks'], $stats );
		}

		if ( ! pns_blocks_split_section_migration_is_legacy_split_section( $block ) ) {
			continue;
		}

		$attrs          = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
		$class_name     = (string) ( $attrs['className'] ?? '' );
		$layout_variant = pns_blocks_split_section_migration_get_layout_variant( $class_name );

		unset( $attrs['className'] );

		$attrs['layoutVariant'] = $layout_variant;

		if ( empty( $attrs['align'] ) ) {
			$attrs['align'] = 'full';
		}

		$block['blockName']    = 'pns/split-section';
		$block['attrs']        = $attrs;
		$block['innerContent'] = array_fill( 0, count( $block['innerBlocks'] ?? array() ), null );
		$block['innerHTML']    = '';

		$stats['blocks']++;
	}

	return $blocks;
}

/**
 * Determine whether a parsed block is a legacy split-section wrapper.
 *
 * @param array<string,mixed> $block Parsed block.
 * @return bool
 */
function pns_blocks_split_section_migration_is_legacy_split_section( $block ) {
	if ( 'core/group' !== ( $block['blockName'] ?? '' ) ) {
		return false;
	}

	$class_name = $block['attrs']['className'] ?? '';

	return is_string( $class_name ) && preg_match( '/\bpns-split-section\b/', $class_name );
}

/**
 * Get the custom block layout variant from legacy classes.
 *
 * @param string $class_name Legacy class attribute.
 * @return string
 */
function pns_blocks_split_section_migration_get_layout_variant( $class_name ) {
	$class_map = array(
		'is-style-pns-media-left'       => 'media-left',
		'is-style-pns-media-right'      => 'media-right',
		'is-style-pns-edge-media-left'  => 'edge-media-left',
		'is-style-pns-edge-media-right' => 'edge-media-right',
	);

	foreach ( $class_map as $class => $variant ) {
		if ( preg_match( '/\b' . preg_quote( $class, '/' ) . '\b/', $class_name ) ) {
			return $variant;
		}
	}

	return 'edge-media-right';
}

/**
 * Write a rollback backup.
 *
 * @param array<int,array<string,mixed>> $backup Backup records.
 * @return string
 */
function pns_blocks_split_section_migration_write_backup( $backup ) {
	$backup_dir = dirname( PNS_BLOCKS_PLUGIN_DIR, 5 ) . '/docs/jobs/live-adoption-db-backups';

	if ( ! is_dir( $backup_dir ) ) {
		wp_mkdir_p( $backup_dir );
	}

	$file = $backup_dir . '/' . gmdate( 'Y-m-d-His' ) . '-split-section-blocks-before.json';

	file_put_contents(
		$file,
		wp_json_encode( $backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES )
	);

	return $file;
}

/**
 * Print a migration report.
 *
 * @param array<string,mixed> $run Migration run details.
 * @return void
 */
function pns_blocks_split_section_migration_print_report( $run ) {
	$lines = array(
		'mode: ' . ( $run['apply'] ? 'apply' : 'dry-run' ),
		'checked: ' . $run['checked'],
		'changed_posts: ' . $run['changed'],
		'changed_blocks: ' . $run['blocks'],
	);

	if ( ! empty( $run['backup_file'] ) ) {
		$lines[] = 'backup_file: ' . $run['backup_file'];
	}

	foreach ( $run['errors'] as $error ) {
		$lines[] = 'error: ' . $error;
	}

	foreach ( $run['posts'] as $post ) {
		$lines[] = sprintf(
			'- %d %s/%s blocks=%d %s',
			$post['ID'],
			$post['post_type'],
			$post['post_status'],
			$post['blocks'],
			$post['post_title']
		);
	}

	echo implode( PHP_EOL, $lines ) . PHP_EOL;
}
