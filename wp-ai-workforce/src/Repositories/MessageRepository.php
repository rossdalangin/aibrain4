<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Repositories;

/**
 * Repository for individual chat messages.
 */
class MessageRepository {

	/**
	 * @var string
	 */
	private $table_name;

	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'ai_messages';
	}

	public function get_by_conversation( int $conversation_id ): array {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE conversation_id = %d ORDER BY created_at ASC", $conversation_id ), ARRAY_A );
	}

	public function get_conversation_messages( int $conversation_id ): array {
		return $this->get_by_conversation( $conversation_id );
	}

	public function create( array $data ): int {
		global $wpdb;
		$wpdb->insert( $this->table_name, $data );
		return (int) $wpdb->insert_id;
	}
}
