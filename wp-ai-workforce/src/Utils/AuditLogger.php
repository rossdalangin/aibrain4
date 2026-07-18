<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Utils;

/**
 * Handles security and system audit logging.
 */
class AuditLogger {

	/**
	 * Log a security or system action.
	 */
	public function log( string $action, string $description, int $agent_id = 0, array $metadata = [] ): void {
		global $wpdb;

		$wpdb->insert( $wpdb->prefix . 'ai_audit_logs', [
			'user_id'     => get_current_user_id(),
			'action_type' => $action,
			'description' => $description,
			'agent_id'    => $agent_id,
			'metadata'    => wp_json_encode( $metadata ),
			'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '',
			'created_at'  => current_time( 'mysql' ),
		] );
	}
}
