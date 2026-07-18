<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Integrations\Actions;

/**
 * Action to manage and reply to WordPress comments.
 */
class CommentManagementAction extends BaseAction {

	public function get_name(): string {
		return 'manage_wordpress_comments';
	}

	public function get_description(): string {
		return 'Reply to, approve, or moderate comments on WordPress posts. Use this for community engagement or spam control.';
	}

	public function get_parameters(): array {
		return [
			'type' => 'object',
			'properties' => [
				'comment_id' => [ 'type' => 'integer', 'description' => 'The ID of the comment to act upon' ],
				'action'     => [ 'type' => 'string', 'enum' => [ 'reply', 'approve', 'spam', 'trash' ], 'default' => 'reply' ],
				'content'    => [ 'type' => 'string', 'description' => 'The content of the reply (only for reply action)' ],
			],
			'required' => [ 'comment_id', 'action' ],
		];
	}

	public function execute( array $args ) {
		if ( ! current_user_can( 'moderate_comments' ) ) {
			throw new \Exception( 'Insufficient permissions to manage comments.' );
		}

		$comment_id = (int) $args['comment_id'];
		$action     = $args['action'];

		switch ( $action ) {
			case 'reply':
				$comment = get_comment( $comment_id );
				if ( ! $comment ) throw new \Exception( 'Comment not found.' );

				$reply_id = wp_insert_comment( [
					'comment_post_ID'      => $comment->comment_post_ID,
					'comment_content'      => wp_kses_post( $args['content'] ),
					'comment_parent'       => $comment_id,
					'user_id'              => get_current_user_id(),
					'comment_approved'     => 1,
				] );
				return [ 'success' => true, 'reply_id' => $reply_id ];

			case 'approve':
				wp_set_comment_status( $comment_id, 'approve' );
				break;
			case 'spam':
				wp_set_comment_status( $comment_id, 'spam' );
				break;
			case 'trash':
				wp_delete_comment( $comment_id );
				break;
		}

		return [ 'success' => true, 'action_performed' => $action ];
	}
}
