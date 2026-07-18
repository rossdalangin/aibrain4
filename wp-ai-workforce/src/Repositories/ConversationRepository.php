<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Repositories;

/**
 * Repository for managing chat conversations.
 */
class ConversationRepository {

	/**
	 * @var string
	 */
	private $table_name;

	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'ai_conversations';
	}

	public function get_user_conversations( int $user_id ): array {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE user_id = %d ORDER BY last_message_at DESC", $user_id ), ARRAY_A );
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

	public function update_last_message_at( int $id ): void {
		global $wpdb;
		$wpdb->update( $this->table_name, [ 'last_message_at' => current_time( 'mysql' ) ], [ 'id' => $id ] );
	}
}
