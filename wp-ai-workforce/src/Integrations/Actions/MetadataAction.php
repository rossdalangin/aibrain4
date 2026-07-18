<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Integrations\Actions;

/**
 * Action to manage WordPress Metadata and ACF fields.
 */
class MetadataAction extends BaseAction {

	public function get_name(): string {
		return 'manage_metadata_acf';
	}

	public function get_description(): string {
		return 'Read or update WordPress post metadata and ACF (Advanced Custom Fields). Use this to manage custom structured data.';
	}

	public function get_parameters(): array {
		return [
			'type' => 'object',
			'properties' => [
				'post_id' => [ 'type' => 'integer', 'description' => 'Target post/page/product ID' ],
				'action'  => [ 'type' => 'string', 'enum' => [ 'get', 'update' ], 'default' => 'get' ],
				'key'     => [ 'type' => 'string', 'description' => 'Meta key or ACF field name' ],
				'value'   => [ 'type' => 'string', 'description' => 'Value to set (only for update)' ],
			],
			'required' => [ 'post_id', 'action', 'key' ],
		];
	}

	public function execute( array $args ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			throw new \Exception( 'Insufficient permissions to manage metadata.' );
		}

		$post_id = (int) $args['post_id'];
		$action  = $args['action'];
		$key     = sanitize_text_field( $args['key'] );

		if ( $action === 'get' ) {
			// Check ACF first
			if ( function_exists( 'get_field' ) ) {
				$val = get_field( $key, $post_id );
				if ( $val !== null && $val !== false ) {
					return [ 'success' => true, 'value' => $val, 'source' => 'acf' ];
				}
			}
			return [ 'success' => true, 'value' => get_post_meta( $post_id, $key, true ), 'source' => 'wp_meta' ];
		}

		$value = sanitize_textarea_field( $args['value'] ?? '' );

		if ( function_exists( 'update_field' ) ) {
			update_field( $key, $value, $post_id );
			return [ 'success' => true, 'updated' => $key, 'source' => 'acf' ];
		}

		update_post_meta( $post_id, $key, $value );
		return [ 'success' => true, 'updated' => $key, 'source' => 'wp_meta' ];
	}
}
