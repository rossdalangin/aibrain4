<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\Models;

/**
 * Adapter for OpenRouter API (supports Llama 3, Grok, Mistral, etc.)
 */
class OpenRouterAdapter extends BaseAdapter {

	public function get_id(): string {
		return 'openrouter';
	}

	public function generate_embeddings( string $text ): array {
		return [];
	}

	protected function get_base_url(): string {
		return 'https://openrouter.ai/api/v1/';
	}

	public function generate_completion( array $messages, array $settings = [] ): array {
		if ( empty( $this->api_key ) ) {
			return $this->get_mock_completion( $messages );
		}

		$payload = [
			'model'       => $settings['model'] ?? 'meta-llama/llama-3.1-405b-instruct',
			'messages'    => $messages,
			'temperature' => (float) ( $settings['temperature'] ?? 0.7 ),
			'max_tokens'  => (int) ( $settings['max_tokens'] ?? 2000 ),
		];

		if ( ! empty( $settings['tools'] ) ) {
			$payload['tools'] = $settings['tools'];
		}

		$response = $this->request( 'chat/completions', $payload, [
			'HTTP_Referer' => get_site_url(),
			'X-Title'      => 'Nexus AI Workforce',
		] );

		if ( is_wp_error( $response ) ) {
			return [ 'content' => 'Error: ' . $response->get_error_message() ];
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$choice = $body['choices'][0] ?? [];

		return [
			'content'    => $choice['message']['content'] ?? '',
			'tool_calls' => $choice['message']['tool_calls'] ?? [],
		];
	}
}
