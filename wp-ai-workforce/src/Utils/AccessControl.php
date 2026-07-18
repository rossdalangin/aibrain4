<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Utils;

/**
 * Handles Role-Based Access Control (RBAC) for the platform.
 */
class AccessControl {

	/**
	 * Check if a user has access to a specific capability.
	 *
	 * @param string $capability Capabilities like 'hire_agent', 'view_analytics', 'manage_billing'.
	 * @return bool
	 */
	public static function can( string $capability ): bool {
		if ( current_user_can( 'manage_options' ) ) {
			return true; // Super admin
		}

		// Implement custom role mapping based on user level (Admin, Agency, Employee, Guest)
		$user_id = get_current_user_id();
		$role = get_user_meta( $user_id, 'nexus_ai_role', true );

		$matrix = [
			'agency' => [ 'hire_agent', 'view_analytics', 'manage_billing', 'white_label' ],
			'employee' => [ 'chat_with_agent', 'view_docs' ],
			'guest' => [ 'view_docs' ],
		];

		return in_array( $capability, $matrix[ $role ] ?? [] );
	}

	/**
	 * Get the tutorial link based on user level.
	 */
	public static function get_tutorial_link(): string {
		$role = get_user_meta( get_current_user_id(), 'nexus_ai_role', true ) ?: 'employee';
		return admin_url( 'admin.php?page=nexus-ai-workforce-tutorials&level=' . $role );
	}
}
