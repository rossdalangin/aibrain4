<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\Models;

/**
 * Adapter for Google Gemini API.
 */
class GeminiAdapter extends BaseAdapter {

	public function get_id(): string {
		return 'gemini';
	}

	protected function get_base_url(): string {
		return 'https://generativelanguage.googleapis.com/v1beta/models/';
	}

	public function generate_completion( array $messages, array $settings ): array {
		if ( empty( $this->api_key ) ) {
			return $this->get_mock_completion( $messages );
		}

		$model = $settings['model'] ?? 'gemini-1.5-pro';
		$api_key = $this->api_key;

		// Extract system prompt if present in messages
		$system_instruction = null;
		$filtered_messages = [];
		foreach ( $messages as $msg ) {
			if ( $msg['role'] === 'system' ) {
				$system_instruction = [
					'parts' => [ [ 'text' => $msg['content'] ] ]
				];
			} else {
				$filtered_messages[] = $msg;
			}
		}

		// Convert OpenAI format to Gemini format
		$contents = [];
		foreach ( $filtered_messages as $msg ) {
			$role = ( $msg['role'] === 'user' ) ? 'user' : 'model';
			$contents[] = [
				'role'  => $role,
				'parts' => [ [ 'text' => $msg['content'] ] ],
			];
		}

		$endpoint = $model . ':generateContent?key=' . $api_key;

		$payload = [
			'contents' => $contents,
			'generationConfig' => [
				'temperature' => (float) ( $settings['temperature'] ?? 0.7 ),
				'maxOutputTokens' => (int) ( $settings['max_tokens'] ?? 2048 ),
			],
		];

		if ( $system_instruction ) {
			$payload['systemInstruction'] = $system_instruction;
		}

		$response = $this->request( $endpoint, $payload, [ 'Authorization' => '' ] ); // Key is in query string

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			throw new \Exception( $body['error']['message'] );
		}

		return [
			'content' => $body['candidates'][0]['content']['parts'][0]['text'] ?? '',
			'usage'   => $body['usageMetadata'] ?? [],
		];
	}

	public function generate_embeddings( string $text ): array {
		$model = 'text-embedding-004';
		$endpoint = $model . ':embedContent?key=' . $this->api_key;

		$response = $this->request( $endpoint, [
			'model'   => 'models/' . $model,
			'content' => [ 'parts' => [ [ 'text' => $text ] ] ],
		], [ 'Authorization' => '' ] );

		if ( is_wp_error( $response ) ) {
			return [];
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return $body['embedding']['values'] ?? [];
	}
}
