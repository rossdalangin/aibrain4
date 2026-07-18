<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\Models;

/**
 * Basic adapter with common functionality for AI providers.
 */
abstract class BaseAdapter implements AIModelInterface {

	/**
	 * @var string
	 */
	protected $api_key;

	/**
	 * Constructor.
	 *
	 * @param string $api_key Encrypted or raw API key.
	 */
	public function __construct( string $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Log API errors.
	 */
	protected function log_error( string $message, array $context = [] ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( '[Nexus AI] %s', $message ) );
		}
	}

	/**
	 * Get the base URL for the API.
	 */
	abstract protected function get_base_url(): string;

	/**
	 * Perform a remote request to the AI provider.
	 */
	protected function request( string $endpoint, array $payload, array $extra_headers = [] ) {
		$headers = array_merge( [
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $this->api_key,
		], $extra_headers );

		// Filter out empty headers to prevent connection errors on some proxy servers
		$headers = array_filter( $headers, function( $value ) {
			return $value !== '';
		} );

		return wp_remote_post( $this->get_base_url() . $endpoint, [
			'headers' => $headers,
			'body'    => wp_json_encode( $payload ),
			'timeout' => 60,
		] );
	}

	/**
	 * Get a simulated, high-quality response for testing/local environments when no API key is set.
	 */
	protected function get_mock_completion( array $messages ): array {
		$user_msg = "Hello!";
		foreach ( array_reverse( $messages ) as $msg ) {
			if ( $msg['role'] === 'user' ) {
				$user_msg = $msg['content'];
				break;
			}
		}

		$responses = [
			'strategy' => "From a chief strategy perspective, we must optimize organizational alignment and leverage first-principles reasoning to maximize operational ROI.",
			'marketing' => "To accelerate growth, we should architect data-driven marketing loops, refine our value proposition, and optimize our ROAS threshold.",
			'system' => " platform system integrity is fully operational. We should continue prioritizing database efficiency and zero-trust API boundaries.",
			'default' => "I have processed your request from a strategic standpoint. Let's establish clear operational deliverables and actionable milestones."
		];

		$lower_msg = strtolower( $user_msg );
		$reply = $responses['default'];

		if ( strpos( $lower_msg, 'strategy' ) !== false || strpos( $lower_msg, 'leader' ) !== false || strpos( $lower_msg, 'roi' ) !== false ) {
			$reply = $responses['strategy'];
		} elseif ( strpos( $lower_msg, 'market' ) !== false || strpos( $lower_msg, 'growth' ) !== false || strpos( $lower_msg, 'ad' ) !== false ) {
			$reply = $responses['marketing'];
		} elseif ( strpos( $lower_msg, 'security' ) !== false || strpos( $lower_msg, 'scale' ) !== false || strpos( $lower_msg, 'latency' ) !== false ) {
			$reply = $responses['system'];
		}

		return [
			'content'    => "AI Consultant: " . $reply,
			'tool_calls' => [],
			'usage'      => [ 'prompt_tokens' => 120, 'completion_tokens' => 45 ],
		];
	}
}
