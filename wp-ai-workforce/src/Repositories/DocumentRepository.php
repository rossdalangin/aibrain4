<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Repositories;

/**
 * Repository for Knowledge Documents data access.
 */
class DocumentRepository {

	/**
	 * @var string
	 */
	private $table_name;

	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'ai_knowledge_documents';
	}

	public function get_by_kb( int $kb_id ): array {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE kb_id = %d", $kb_id ), ARRAY_A );
	}

	public function create( array $data ): int {
		global $wpdb;
		$wpdb->insert( $this->table_name, $data );
		return (int) $wpdb->insert_id;
	}

	public function update_status( int $id, string $status ): bool {
		global $wpdb;
		return (bool) $wpdb->update( $this->table_name, [ 'status' => $status ], [ 'id' => $id ] );
	}
}
