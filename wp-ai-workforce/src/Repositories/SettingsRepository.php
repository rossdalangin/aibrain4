<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Repositories;

/**
 * Repository for managing plugin settings.
 */
class SettingsRepository {

	/**
	 * @var string
	 */
	private $option_name = 'nexus_ai_workforce_settings';

	/**
	 * Get all settings.
	 *
	 * @return array
	 */
	public function get_all(): array {
		return get_option( $this->option_name, [] );
	}

	/**
	 * Get a specific setting.
	 *
	 * @param string $key     The setting key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get( string $key, $default = null ) {
		$settings = $this->get_all();
		return $settings[ $key ] ?? $default;
	}

	/**
	 * Update settings.
	 *
	 * @param array $data New settings data.
	 * @return bool
	 */
	public function update( array $data ): bool {
		$current = $this->get_all();
		$new = array_merge( $current, $data );
		return update_option( $this->option_name, $new );
	}
}
