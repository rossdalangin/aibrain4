<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\Models;

/**
 * Adapter for Anthropic Claude API.
 */
class ClaudeAdapter extends BaseAdapter {

	public function get_id(): string {
		return 'claude';
	}

	protected function get_base_url(): string {
		return 'https://api.anthropic.com/v1/';
	}

	public function generate_completion( array $messages, array $settings ): array {
		if ( empty( $this->api_key ) ) {
			return $this->get_mock_completion( $messages );
		}

		$model = $settings['model'] ?? 'claude-3-5-sonnet-20240620';

		// Extract system prompt if present in messages
		$system_prompt = $settings['system_prompt'] ?? '';
		$filtered_messages = [];
		foreach ( $messages as $msg ) {
			if ( $msg['role'] === 'system' ) {
				$system_prompt = $msg['content'];
			} else {
				$filtered_messages[] = $msg;
			}
		}

		$payload = [
			'model'      => $model,
			'messages'   => $filtered_messages,
			'max_tokens' => (int) ( $settings['max_tokens'] ?? 2048 ),
		];

		if ( ! empty( $system_prompt ) ) {
			$payload['system'] = $system_prompt;
		}

		$response = $this->request( 'messages', $payload, [
			'x-api-key'         => $this->api_key,
			'anthropic-version' => '2023-06-01',
		] );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			throw new \Exception( $body['error']['message'] );
		}

		return [
			'content' => $body['content'][0]['text'] ?? '',
			'usage'   => $body['usage'] ?? [],
		];
	}

	public function generate_embeddings( string $text ): array {
		// Anthropic does not have a native embeddings API yet
		return [];
	}
}
