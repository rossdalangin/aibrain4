<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Integrations\Actions;

/**
 * Action to manage WordPress users.
 */
class ManageUserAction extends BaseAction {

	public function get_name(): string {
		return 'manage_wordpress_user';
	}

	public function get_description(): string {
		return 'Create or update WordPress users. Use this when requested to manage team access.';
	}

	public function get_parameters(): array {
		return [
			'type' => 'object',
			'properties' => [
				'username' => [ 'type' => 'string' ],
				'email'    => [ 'type' => 'string' ],
				'role'     => [ 'type' => 'string', 'enum' => [ 'subscriber', 'contributor', 'author', 'editor' ] ],
			],
			'required' => [ 'username', 'email' ],
		];
	}

	public function execute( array $args ) {
		if ( ! current_user_can( 'create_users' ) ) {
			throw new \Exception( 'Insufficient permissions to manage users.' );
		}

		// Security: Prevent AI from creating administrators via prompt injection
		$requested_role = $args['role'] ?? 'subscriber';
		if ( $requested_role === 'administrator' ) {
			throw new \Exception( 'AI is not permitted to create administrator accounts.' );
		}

		$user_id = wp_create_user( $args['username'], wp_generate_password(), $args['email'] );

		if ( is_wp_error( $user_id ) ) {
			throw new \Exception( $user_id->get_error_message() );
		}

		if ( ! empty( $requested_role ) ) {
			$user = new \WP_User( $user_id );
			$user->set_role( $requested_role );
		}

		return [ 'success' => true, 'user_id' => $user_id ];
	}
}
