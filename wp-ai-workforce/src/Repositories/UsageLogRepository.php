<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Repositories;

/**
 * Repository for logging and tracking AI usage.
 */
class UsageLogRepository {

	/**
	 * @var string
	 */
	private $table_name;

	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'ai_usage_logs';
	}

	public function log_usage( array $data ): int {
		global $wpdb;
		$wpdb->insert( $this->table_name, [
			'user_id'           => get_current_user_id(),
			'employee_id'       => $data['employee_id'] ?? 0,
			'model'             => $data['model'] ?? 'unknown',
			'prompt_tokens'     => $data['prompt_tokens'] ?? 0,
			'completion_tokens' => $data['completion_tokens'] ?? 0,
			'cost'              => $data['cost'] ?? 0.0,
		] );
		return (int) $wpdb->insert_id;
	}

	public function get_recent_usage( int $limit = 50 ): array {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT %d", $limit ), ARRAY_A );
	}
}
