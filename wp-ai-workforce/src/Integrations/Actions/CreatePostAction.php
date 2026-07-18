<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Integrations\Actions;

/**
 * Action to create a new WordPress post.
 */
class CreatePostAction extends BaseAction {

	public function get_name(): string {
		return 'create_wordpress_post';
	}

	public function get_description(): string {
		return 'Create a new post or page in WordPress. Use this when the user asks to publish or draft content.';
	}

	public function get_parameters(): array {
		return [
			'type' => 'object',
			'properties' => [
				'title'   => [ 'type' => 'string', 'description' => 'The title of the post' ],
				'content' => [ 'type' => 'string', 'description' => 'The main body content (HTML or Markdown)' ],
				'status'  => [ 'type' => 'string', 'enum' => [ 'draft', 'publish' ], 'default' => 'draft' ],
				'type'    => [ 'type' => 'string', 'enum' => [ 'post', 'page' ], 'default' => 'post' ],
			],
			'required' => [ 'title', 'content' ],
		];
	}

	public function execute( array $args ) {
		if ( ! current_user_can( 'publish_posts' ) ) {
			throw new \Exception( 'Insufficient permissions to create posts.' );
		}

		$post_id = wp_insert_post( [
			'post_title'   => sanitize_text_field( $args['title'] ),
			'post_content' => wp_kses_post( $args['content'] ),
			'post_status'  => sanitize_text_field( $args['status'] ?? 'draft' ),
			'post_type'    => sanitize_text_field( $args['type'] ?? 'post' ),
		] );

		if ( is_wp_error( $post_id ) ) {
			throw new \Exception( $post_id->get_error_message() );
		}

		return [
			'success' => true,
			'post_id' => $post_id,
			'url'     => get_permalink( $post_id ),
		];
	}
}
