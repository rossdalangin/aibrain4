<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Repositories;

/**
 * Repository for AI Employee data access.
 */
class EmployeeRepository {

	/**
	 * @var string
	 */
	private $table_name;

	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'ai_employees';
	}

	public function get_all(): array {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$this->table_name} WHERE is_active = 1", ARRAY_A );
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

	public function update( int $id, array $data ): bool {
		global $wpdb;
		return (bool) $wpdb->update( $this->table_name, $data, [ 'id' => $id ] );
	}

	public function delete( int $id ): bool {
		global $wpdb;
		return (bool) $wpdb->update( $this->table_name, [ 'is_active' => 0 ], [ 'id' => $id ] );
	}
}
