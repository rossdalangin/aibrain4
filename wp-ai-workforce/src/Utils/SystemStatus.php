<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Utils;

/**
 * Performs health and environment checks for the system.
 */
class SystemStatus {

	/**
	 * Get comprehensive system health report.
	 */
	public function get_report(): array {
		return [
			'php_version'    => PHP_VERSION,
			'openssl'        => extension_loaded( 'openssl' ),
			'sqlite'         => extension_loaded( 'sqlite3' ),
			'db_tables'      => $this->check_tables(),
			'api_connection' => $this->check_api_connectivity(),
		];
	}

	private function check_tables(): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'ai_employees';
		return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) === $table;
	}

	private function check_api_connectivity(): bool {
		$response = wp_remote_get( 'https://api.openai.com/v1/models', [ 'timeout' => 5 ] );
		return ! is_wp_error( $response );
	}
}
