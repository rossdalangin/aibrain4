<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Database;

/**
 * Handles database schema creation and updates.
 */
class Migration {

	/**
	 * Run the migrations to create or update tables.
	 */
	public static function run(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$tables = [
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ai_departments (
				id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				name VARCHAR(255) NOT NULL,
				description TEXT,
				parent_id BIGINT(20) DEFAULT 0,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP
			) $charset_collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ai_employees (
				id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				name VARCHAR(255) NOT NULL,
				position VARCHAR(255),
				department_id BIGINT(20) UNSIGNED,
				role_description TEXT,
				responsibilities TEXT,
				skills TEXT,
				goals TEXT,
				kpis TEXT,
				prompt_template LONGTEXT,
				thinking_process TEXT,
				output_format TEXT,
				negative_prompts TEXT,
				examples TEXT,
				model_settings JSON,
				avatar_url VARCHAR(255),
				personality_traits JSON,
				is_active TINYINT(1) DEFAULT 1,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				KEY department_id (department_id)
			) $charset_collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ai_conversations (
				id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				user_id BIGINT(20) UNSIGNED,
				title VARCHAR(255),
				type ENUM('single', 'meeting', 'workflow') DEFAULT 'single',
				status VARCHAR(50),
				last_message_at DATETIME,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				KEY user_id (user_id)
			) $charset_collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ai_messages (
				id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				conversation_id BIGINT(20) UNSIGNED,
				sender_type ENUM('user', 'ai', 'system'),
				sender_id BIGINT(20) UNSIGNED,
				content LONGTEXT,
				context_used JSON,
				token_usage INT,
				metadata JSON,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				KEY conversation_id (conversation_id)
			) $charset_collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ai_knowledge_bases (
				id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				name VARCHAR(255),
				description TEXT,
				owner_type ENUM('global', 'department', 'employee'),
				owner_id BIGINT(20) UNSIGNED,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP
			) $charset_collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ai_knowledge_documents (
				id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				kb_id BIGINT(20) UNSIGNED,
				type VARCHAR(50),
				source_path VARCHAR(512),
				content_hash VARCHAR(64),
				status ENUM('pending', 'processing', 'indexed', 'error'),
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				KEY kb_id (kb_id)
			) $charset_collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ai_knowledge_chunks (
				id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				doc_id BIGINT(20) UNSIGNED,
				content LONGTEXT,
				vector_id VARCHAR(255),
				metadata JSON,
				KEY doc_id (doc_id)
			) $charset_collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ai_workflows (
				id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				name VARCHAR(255),
				definition JSON,
				is_active TINYINT(1) DEFAULT 1,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP
			) $charset_collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ai_usage_logs (
				id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				user_id BIGINT(20) UNSIGNED,
				employee_id BIGINT(20) UNSIGNED,
				model VARCHAR(100),
				prompt_tokens INT,
				completion_tokens INT,
				cost DECIMAL(10, 6),
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				KEY user_id (user_id),
				KEY employee_id (employee_id)
			) $charset_collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ai_plans (
				id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				name VARCHAR(255) NOT NULL,
				price DECIMAL(10, 2),
				agent_limit INT,
				features JSON,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP
			) $charset_collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ai_subscriptions (
				id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				user_id BIGINT(20) UNSIGNED,
				plan_id BIGINT(20) UNSIGNED,
				status VARCHAR(50),
				expires_at DATETIME,
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				KEY user_id (user_id)
			) $charset_collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ai_coupons (
				id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				code VARCHAR(50) UNIQUE,
				discount_percent INT,
				is_active TINYINT(1) DEFAULT 1,
				expires_at DATETIME
			) $charset_collate;",

			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ai_audit_logs (
				id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				user_id BIGINT(20) UNSIGNED,
				action_type VARCHAR(100),
				description TEXT,
				agent_id BIGINT(20) UNSIGNED,
				metadata JSON,
				ip_address VARCHAR(45),
				created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
				KEY user_id (user_id),
				KEY action_type (action_type)
			) $charset_collate;",
		];

		foreach ( $tables as $sql ) {
			dbDelta( $sql );
		}

		// Bulletproof self-healing column check & addition fallback
		$table_name = $wpdb->prefix . 'ai_employees';
		$columns = $wpdb->get_col( "DESCRIBE {$table_name}" );
		if ( ! empty( $columns ) ) {
			$missing_columns = [
				'thinking_process' => 'TEXT',
				'output_format'    => 'TEXT',
				'negative_prompts' => 'TEXT',
				'examples'         => 'TEXT',
			];
			foreach ( $missing_columns as $col => $type ) {
				if ( ! in_array( $col, $columns, true ) ) {
					$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN {$col} {$type}" );
				}
			}
		}
	}
}
