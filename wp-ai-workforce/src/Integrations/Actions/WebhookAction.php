<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Integrations\Actions;

/**
 * Action to trigger external webhooks (Zapier, Make, n8n).
 */
class WebhookAction extends BaseAction {

	public function get_name(): string {
		return 'trigger_external_webhook';
	}

	public function get_description(): string {
		return 'Send data to an external automation platform like Zapier or Make. Use this to notify external systems.';
	}

	public function get_parameters(): array {
		return [
			'type' => 'object',
			'properties' => [
				'webhook_url' => [ 'type' => 'string' ],
				'payload'     => [ 'type' => 'object' ],
			],
			'required' => [ 'webhook_url', 'payload' ],
		];
	}

	public function execute( array $args ) {
		$response = wp_remote_post( esc_url_raw( $args['webhook_url'] ), [
			'body'    => wp_json_encode( $args['payload'] ),
			'headers' => [ 'Content-Type' => 'application/json' ],
			'timeout' => 30,
		] );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}

		return [ 'success' => true, 'status' => wp_remote_retrieve_response_code( $response ) ];
	}
}
