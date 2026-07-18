<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Integrations\Actions;

/**
 * Action to send notifications to Slack.
 */
class SlackAction extends BaseAction {

	public function get_name(): string {
		return 'slack_notify';
	}

	public function get_description(): string {
		return 'Send a notification message to a specific Slack channel via webhook.';
	}

	public function get_parameters(): array {
		return [
			'type'       => 'object',
			'properties' => [
				'message' => [
					'type'        => 'string',
					'description' => 'The message content to send.',
				],
				'channel' => [
					'type'        => 'string',
					'description' => 'Optional Slack channel override.',
				],
			],
			'required'   => [ 'message' ],
		];
	}

	public function execute( array $args ) {
		$webhook_url = get_option( 'nexus_ai_slack_webhook' );
		if ( ! $webhook_url ) {
			return [ 'success' => false, 'message' => 'Slack Webhook URL not configured in settings.' ];
		}

		$response = wp_remote_post( $webhook_url, [
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => wp_json_encode( [ 'text' => $args['message'] ] ),
		] );

		if ( is_wp_error( $response ) ) {
			return [ 'success' => false, 'message' => $response->get_error_message() ];
		}

		return [ 'success' => true, 'message' => 'Slack notification sent.' ];
	}
}
