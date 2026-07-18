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

		$agent_name     = 'AI Employee';
		$agent_position = 'Specialist';

		foreach ( $messages as $msg ) {
			if ( $msg['role'] === 'system' ) {
				$system_content = $msg['content'];
				if ( preg_match( '/# IDENTITY\s+([^\n-]+)\s*-\s*([^\n]+)/i', $system_content, $matches ) ) {
					$agent_name     = trim( $matches[1] );
					$agent_position = trim( $matches[2] );
				}
				break;
			}
		}

		$lower_msg  = strtolower( $user_msg );
		$lower_pos  = strtolower( $agent_position );

		// Detect standard keywords to refine context
		$is_roi       = strpos( $lower_msg, 'roi' ) !== false || strpos( $lower_msg, 'audit' ) !== false || strpos( $lower_msg, 'strategy' ) !== false || strpos( $lower_msg, 'leader' ) !== false;
		$is_marketing = strpos( $lower_msg, 'market' ) !== false || strpos( $lower_msg, 'growth' ) !== false || strpos( $lower_msg, 'ad' ) !== false || strpos( $lower_msg, 'storm' ) !== false;
		$is_system    = strpos( $lower_msg, 'security' ) !== false || strpos( $lower_msg, 'scale' ) !== false || strpos( $lower_msg, 'latency' ) !== false || strpos( $lower_msg, 'technical' ) !== false || strpos( $lower_msg, 'debt' ) !== false || strpos( $lower_msg, 'architecture' ) !== false || strpos( $lower_msg, 'rag' ) !== false;

		if ( strpos( $lower_pos, 'strategy' ) !== false || strpos( $lower_pos, 'executive' ) !== false || strpos( $lower_pos, 'ceo' ) !== false || strpos( $lower_pos, 'cso' ) !== false ) {
			if ( $is_roi ) {
				$reply = "As Chief Strategy Officer, I've reviewed our strategic alignment. To optimize ROI, we must focus on high-leverage assets, streamline our prompt chains, and align our operations with our key performance indicators.";
			} elseif ( $is_marketing ) {
				$reply = "From a strategy perspective, growth is a function of organizational alignment. We should ensure our marketing loops are tightly integrated with our core value proposition to maximize customer lifetime value.";
			} elseif ( $is_system ) {
				$reply = "To scale effectively, we must manage technical debt as a strategic asset. Our plugin architecture must support robust modularity so we don't compromise long-term execution speed.";
			} else {
				$reply = "I have processed your request from a strategic standpoint. Let's establish clear operational deliverables, focus on high-leverage activities, and map our next milestones to maximize ROI.";
			}
		} elseif ( strpos( $lower_pos, 'marketing' ) !== false || strpos( $lower_pos, 'growth' ) !== false || strpos( $lower_pos, 'cmo' ) !== false ) {
			if ( $is_roi ) {
				$reply = "To optimize our marketing ROI, we should conduct a systematic audit of our acquisition channels and double down on those with a ROAS of 4.0 or higher.";
			} elseif ( $is_marketing ) {
				$reply = "To accelerate growth, we should architect data-driven marketing loops, refine our value proposition, and optimize our ROAS threshold.";
			} elseif ( $is_system ) {
				$reply = "Growth and technology go hand-in-hand. We need a highly performant infrastructure to ensure our landing pages and user onboarding flows have minimal latency and high conversion rates.";
			} else {
				$reply = "Let's focus on scaling user acquisition and optimizing our conversion funnels. We should run a series of rapid growth experiments to identify our most profitable marketing loops.";
			}
		} elseif ( strpos( $lower_pos, 'system' ) !== false || strpos( $lower_pos, 'engineer' ) !== false || strpos( $lower_pos, 'developer' ) !== false || strpos( $lower_pos, 'cto' ) !== false || strpos( $lower_pos, 'technical' ) !== false ) {
			if ( $is_roi ) {
				$reply = "From a systems engineering standpoint, maximizing ROI means optimizing resource allocation, reducing API token waste, and building reusable software components.";
			} elseif ( $is_marketing ) {
				$reply = "To support growth, we must ensure our technical stack is resilient. A scalable infrastructure prevents site crashes during traffic surges from successful marketing campaigns.";
			} elseif ( $is_system ) {
				$reply = "The platform system integrity is fully operational. We should prioritize database efficiency, implement zero-trust API boundaries, and optimize our RAG document chunking to eliminate latency.";
			} else {
				$reply = "I am analyzing our system architecture for any potential failure modes. Let's prioritize security, scalability, and code performance to build a bulletproof infrastructure.";
			}
		} else {
			// Default / Generic position handler
			if ( $is_roi ) {
				$reply = "As the {$agent_position}, I believe we can maximize our efficiency by aligning our daily workflows with our core success metrics and eliminating low-leverage tasks.";
			} elseif ( $is_marketing ) {
				$reply = "To accelerate growth within my department, we should focus on optimizing our processes and ensuring our deliverables directly support user acquisition and brand value.";
			} elseif ( $is_system ) {
				$reply = "From my perspective, technical stability and clear system guidelines are essential for us to execute our department goals without friction.";
			} else {
				$reply = "I have processed your request from the standpoint of my role as {$agent_position}. Let's establish clear operational deliverables and actionable milestones.";
			}
		}

		return [
			'content'    => $reply,
			'tool_calls' => [],
			'usage'      => [ 'prompt_tokens' => 120, 'completion_tokens' => 45 ],
		];
	}
}
