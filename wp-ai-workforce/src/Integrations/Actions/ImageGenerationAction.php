<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Integrations\Actions;

/**
 * Action to generate images using AI (DALL-E 3).
 */
class ImageGenerationAction extends BaseAction {

	public function get_name(): string {
		return 'generate_ai_image';
	}

	public function get_description(): string {
		return 'Generate a high-quality image based on a prompt. Use this for blog featured images, social media posts, or ad creative.';
	}

	public function get_parameters(): array {
		return [
			'type' => 'object',
			'properties' => [
				'prompt' => [ 'type' => 'string', 'description' => 'Detailed description of the image to generate' ],
				'size'   => [ 'type' => 'string', 'enum' => [ '1024x1024', '1024x1792', '1792x1024' ], 'default' => '1024x1024' ],
				'style'  => [ 'type' => 'string', 'enum' => [ 'vivid', 'natural' ], 'default' => 'vivid' ],
			],
			'required' => [ 'prompt' ],
		];
	}

	public function execute( array $args ) {
		$settings_repo = new \NexusAI\Workforce\Repositories\SettingsRepository();
		$api_key = $settings_repo->get( 'openai_api_key' );

		if ( ! $api_key ) {
			throw new \Exception( 'OpenAI API key is missing. Image generation requires an active OpenAI connection.' );
		}

		// Decrypt Key
		$encryption = new \NexusAI\Workforce\Utils\Encryption();
		$decrypted_key = $encryption->decrypt( $api_key );

		$response = wp_remote_post( 'https://api.openai.com/v1/images/generations', [
			'headers' => [
				'Authorization' => 'Bearer ' . $decrypted_key,
				'Content-Type'  => 'application/json',
			],
			'body' => wp_json_encode( [
				'model'  => 'dall-e-3',
				'prompt' => sanitize_text_field( $args['prompt'] ),
				'n'      => 1,
				'size'   => $args['size'] ?? '1024x1024',
				'style'  => $args['style'] ?? 'vivid',
			] ),
			'timeout' => 60,
		] );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			throw new \Exception( $body['error']['message'] );
		}

		$image_url = $body['data'][0]['url'] ?? '';

		return [
			'success'   => true,
			'image_url' => $image_url,
			'revised_prompt' => $body['data'][0]['revised_prompt'] ?? '',
		];
	}
}
