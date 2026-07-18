<?php
declare(strict_types=1);

namespace NexusAI\Workforce\UI;

use NexusAI\Workforce\Repositories\SettingsRepository;
use NexusAI\Workforce\Repositories\EmployeeRepository;

/**
 * Handles the frontend chat widget rendering and assets.
 */
class ChatWidget {

	private $settings;
	private $employees;

	public function __construct() {
		$this->settings  = new SettingsRepository();
		$this->employees = new EmployeeRepository();
	}

	public function init(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function enqueue_assets(): void {
		if ( ! $this->settings->get( 'widget_enabled', false ) ) {
			return;
		}

		wp_enqueue_style( 'nexus-chat-widget', plugins_url( 'assets/css/nexus-widget.css', dirname( __FILE__, 2 ) ), [], '1.0.0' );
		wp_enqueue_script( 'nexus-chat-widget', plugins_url( 'assets/js/nexus-widget.js', dirname( __FILE__, 2 ) ), [], '1.0.0', true );

		$agent_id = (int) $this->settings->get( 'public_agent_id', 0 );
		$agent    = $this->employees->get_by_id( $agent_id );

		wp_localize_script( 'nexus-chat-widget', 'nexus_chat_config', [
			'rest_url'       => esc_url_raw( rest_url() ),
			'agent_name'     => $agent ? $agent['name'] : 'Sarah',
			'agent_position' => $agent ? $agent['position'] : 'Support Lead',
		] );
	}
}
