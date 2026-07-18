<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\Models;

/**
 * Adapter for OpenAI API.
 */
class OpenAIAdapter extends BaseAdapter {

	public function get_id(): string {
		return 'openai';
	}

	protected function get_base_url(): string {
		return 'https://api.openai.com/v1/';
	}

	public function generate_completion( array $messages, array $settings ): array {
		if ( empty( $this->api_key ) ) {
			return $this->get_mock_completion( $messages );
		}

		$model = $settings['model'] ?? 'gpt-4o';

		$body_params = [
			'model'       => $model,
			'messages'    => $messages,
			'temperature' => (float) ( $settings['temperature'] ?? 0.7 ),
			'max_tokens'  => (int) ( $settings['max_tokens'] ?? 2000 ),
		];

		if ( ! empty( $settings['tools'] ) ) {
			$body_params['tools'] = $settings['tools'];
		}

		$response = $this->request( 'chat/completions', $body_params );

		if ( is_wp_error( $response ) ) {
			$this->log_error( $response->get_error_message() );
			throw new \Exception( $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			$this->log_error( $body['error']['message'] );
			throw new \Exception( $body['error']['message'] );
		}

		return [
			'content'    => $body['choices'][0]['message']['content'] ?? '',
			'tool_calls' => $body['choices'][0]['message']['tool_calls'] ?? [],
			'usage'      => $body['usage'] ?? [],
		];
	}

	public function generate_embeddings( string $text ): array {
		$response = $this->request( 'embeddings', [
			'model' => 'text-embedding-3-small',
			'input' => $text,
		] );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return $body['data'][0]['embedding'] ?? [];
	}
}
