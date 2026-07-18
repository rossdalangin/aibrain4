<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Repositories;

/**
 * Repository for managing company departments.
 */
class DepartmentRepository {

	/**
	 * @var string
	 */
	private $table_name;

	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'ai_departments';
	}

	public function get_all(): array {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$this->table_name}", ARRAY_A );
	}

	public function get_by_id( int $id ): ?array {
		global $wpdb;
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", $id ), ARRAY_A );
		return $result ?: null;
	}

	public function create( array $data ): int {
		global $wpdb;
		$wpdb->insert( $this->table_name, $data );
		return (int) $wpdb->insert_id;
	}
}
