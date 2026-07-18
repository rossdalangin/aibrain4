<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\Models;

/**
 * Adapter for Ollama API (Local AI)
 */
class OllamaAdapter extends BaseAdapter {

	public function get_id(): string {
		return 'ollama';
	}

	public function generate_embeddings( string $text ): array {
		return [];
	}

	protected function get_base_url(): string {
		// Default Ollama local URL, can be overridden in settings
		return 'http://localhost:11434/api/';
	}

	public function generate_completion( array $messages, array $settings = [] ): array {
		$payload = [
			'model'    => $settings['model'] ?? 'llama3',
			'messages' => $messages,
			'stream'   => false,
			'options'  => [
				'temperature' => (float) ( $settings['temperature'] ?? 0.7 ),
			],
		];

		// Note: Ollama handles tools differently (functions). This is a basic chat implementation.
		$response = $this->request( 'chat', $payload );

		if ( is_wp_error( $response ) ) {
			return [ 'content' => 'Error: ' . $response->get_error_message() ];
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return [
			'content'    => $body['message']['content'] ?? '',
			'tool_calls' => [], // Local models might need specific fine-tuning for tool support
		];
	}
}
