<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Repositories;

/**
 * Repository for managing coupons and discounts.
 */
class CouponRepository {

	private $table_name;

	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'ai_coupons';
	}

	public function get_by_code( string $code ): ?array {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE code = %s AND is_active = 1", $code ), ARRAY_A );
	}
}
