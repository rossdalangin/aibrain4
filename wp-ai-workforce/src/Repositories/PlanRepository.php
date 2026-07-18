<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Repositories;

/**
 * Repository for managing SaaS plans.
 */
class PlanRepository {

	private $table_name;

	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'ai_plans';
	}

	public function get_all(): array {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$this->table_name}", ARRAY_A );
	}

	public function create( array $data ): int {
		global $wpdb;
		$wpdb->insert( $this->table_name, $data );
		return (int) $wpdb->insert_id;
	}
}
