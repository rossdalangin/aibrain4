<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Integrations\Actions;

/**
 * Action to send notifications to Discord.
 */
class DiscordAction extends BaseAction {

	public function get_name(): string {
		return 'discord_notify';
	}

	public function get_description(): string {
		return 'Send a notification message to a specific Discord channel via webhook.';
	}

	public function get_parameters(): array {
		return [
			'type'       => 'object',
			'properties' => [
				'content' => [
					'type'        => 'string',
					'description' => 'The message content to send.',
				],
				'username' => [
					'type'        => 'string',
					'description' => 'Optional override for the bot username.',
				],
			],
			'required'   => [ 'content' ],
		];
	}

	public function execute( array $args ) {
		$webhook_url = get_option( 'nexus_ai_discord_webhook' );
		if ( ! $webhook_url ) {
			return [ 'success' => false, 'message' => 'Discord Webhook URL not configured in settings.' ];
		}

		$payload = [ 'content' => $args['content'] ];
		if ( ! empty( $args['username'] ) ) {
			$payload['username'] = $args['username'];
		}

		$response = wp_remote_post( $webhook_url, [
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => wp_json_encode( $payload ),
		] );

		if ( is_wp_error( $response ) ) {
			return [ 'success' => false, 'message' => $response->get_error_message() ];
		}

		return [ 'success' => true, 'message' => 'Discord notification sent.' ];
	}
}
