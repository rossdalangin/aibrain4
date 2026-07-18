<?php
declare(strict_types=1);

namespace NexusAI\Workforce\API;

use WP_REST_Request;
use WP_REST_Response;
use NexusAI\Workforce\Repositories\ConversationRepository;
use NexusAI\Workforce\Repositories\MessageRepository;
use NexusAI\Workforce\Repositories\EmployeeRepository;
use NexusAI\Workforce\AI\Agents\Orchestrator;
use NexusAI\Workforce\AI\Factories\ModelFactory;
use NexusAI\Workforce\Utils\Encryption;
use NexusAI\Workforce\Repositories\SettingsRepository;
use NexusAI\Workforce\Repositories\UsageLogRepository;
use NexusAI\Workforce\Utils\AuditLogger;
use NexusAI\Workforce\Utils\CostCalculator;

/**
 * Controller for Chat and Multi-Agent interactions.
 */
class ChatController {

	private $conversations;
	private $messages;
	private $employees;
	private $settings;
	private $usage_logs;

	public function __construct() {
		$this->conversations = new ConversationRepository();
		$this->messages      = new MessageRepository();
		$this->employees     = new EmployeeRepository();
		$this->settings      = new SettingsRepository();
		$this->usage_logs    = new UsageLogRepository();
	}

	public function get_conversations( WP_REST_Request $request ): WP_REST_Response {
		$user_id = get_current_user_id();
		$items = $this->conversations->get_user_conversations( $user_id );
		return new WP_REST_Response( $items, 200 );
	}

	/**
	 * Single step in a multi-agent meeting.
	 */
	public function run_meeting_step( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_params();
		$agent_id = (int) $params['agent_id'];
		$agenda   = sanitize_textarea_field( $params['agenda'] );
		$round    = (int) $params['round'];

		$agent = $this->employees->get_by_id( $agent_id );
		if ( ! $agent ) {
			return new WP_REST_Response( [ 'error' => 'Agent not found' ], 404 );
		}

		$orchestrator = $this->get_orchestrator( $agent );

		// Specialized Meeting Prompt
		$prompt = "We are in a strategic meeting. ROUND: $round. AGENDA: $agenda.
		As the {$agent['position']}, give your expert opinion or contribution to the goal.
		If you have a clear recommendation, format it as 'DECISION: [your decision]' or 'ACTION: [specific action]'.
		Keep it professional, concise, and focused on your specific role KPIs.";

		$result = $orchestrator->process_request( $prompt, $agent );

		return new WP_REST_Response( [
			'agent_name' => $agent['name'],
			'position'   => $agent['position'],
			'content'    => $result['content']
		], 200 );
	}

	/**
	 * Send a message from the public frontend widget.
	 */
	public function send_public_message( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_params();
		$message = sanitize_textarea_field( $params['message'] ?? '' );
		$session_id = sanitize_text_field( $params['session_id'] ?? 'anonymous' );

		if ( ! $this->settings->get( 'widget_enabled', false ) ) {
			return new WP_REST_Response( [ 'error' => 'Widget disabled' ], 403 );
		}

		$agent_id = (int) $this->settings->get( 'public_agent_id', 0 );
		$agent    = $this->employees->get_by_id( $agent_id );

		if ( ! $agent ) {
			return new WP_REST_Response( [ 'error' => 'Agent not configured' ], 500 );
		}

		$orchestrator = $this->get_orchestrator( $agent );
		$result = $orchestrator->process_request( $message, $agent );

		// Log usage for the public agent
		$this->usage_logs->log_usage( [
			'employee_id'       => $agent_id,
			'model'             => $agent['model'] ?? 'gpt-4o',
			'prompt_tokens'     => $result['usage']['prompt_tokens'],
			'completion_tokens' => $result['usage']['completion_tokens'],
			'cost'              => ( new CostCalculator() )->calculate( $agent['model'] ?? 'gpt-4o', $result['usage']['prompt_tokens'], $result['usage']['completion_tokens'] ),
		] );

		return new WP_REST_Response( [ 'response' => $result['content'] ], 200 );
	}

	public function send_message( WP_REST_Request $request ): WP_REST_Response {
		$user_id = get_current_user_id();
		$params  = $request->get_params();

		$conversation_id = (int) ( $params['conversation_id'] ?? 0 );
		$employee_id     = (int) ( $params['employee_id'] ?? 0 );
		$content         = sanitize_textarea_field( $params['message'] ?? '' );

		if ( ! $conversation_id ) {
			$conversation_id = $this->conversations->create( [
				'user_id' => $user_id,
				'title'   => mb_substr( $content, 0, 50 ),
				'status'  => 'active',
			] );
		} else {
			// Security: Verify conversation ownership
			$conv = $this->conversations->get_by_id( $conversation_id );
			if ( ! $conv || (int) $conv['user_id'] !== $user_id ) {
				return new WP_REST_Response( [ 'error' => 'Unauthorized conversation access' ], 403 );
			}
		}

		// Store User Message
		$this->messages->create( [
			'conversation_id' => $conversation_id,
			'sender_type'     => 'user',
			'sender_id'       => $user_id,
			'content'         => $content,
		] );

		// Process with AI
		$employee = $this->employees->get_by_id( $employee_id ) ?: [];
		$orchestrator = $this->get_orchestrator( $employee );

		$result = $orchestrator->process_request( $content, $employee );

		// Store AI Message
		$this->messages->create( [
			'conversation_id' => $conversation_id,
			'sender_type'     => 'ai',
			'sender_id'       => $employee_id,
			'content'         => $result['content'],
		] );

		// Log Usage with real Cost Calculation
		$prompt_tokens = $result['usage']['prompt_tokens'];
		$comp_tokens   = $result['usage']['completion_tokens'];
		$model         = $employee['model'] ?? 'gpt-4o';
		$cost          = ( new CostCalculator() )->calculate( $model, $prompt_tokens, $comp_tokens );

		$this->usage_logs->log_usage( [
			'employee_id'       => $employee_id,
			'model'             => $model,
			'prompt_tokens'     => $prompt_tokens,
			'completion_tokens' => $comp_tokens,
			'cost'              => $cost,
		] );

		$this->conversations->update_last_message_at( $conversation_id );

		( new AuditLogger() )->log( 'ai_chat', "Agent interaction complete.", $employee_id, [ 'conversation_id' => $conversation_id ] );

		return new WP_REST_Response( [ 'response' => $result['content'], 'conversation_id' => $conversation_id ], 200 );
	}

	private function get_orchestrator( array $agent_data ): Orchestrator {
		$default_model = $this->settings->get( 'default_model', 'gpt-4o' );
		$model_settings = json_decode( $agent_data['model_settings'] ?? '{}', true );
		$model_name = $agent_data['model'] ?? $model_settings['model'] ?? $default_model;
		if ( empty( $model_name ) ) {
			$model_name = $default_model;
		}

		// Dynamically determine the provider based on the chosen model name
		$provider = 'openai';
		if ( strpos( $model_name, 'claude' ) !== false ) {
			$provider = 'claude';
		} elseif ( strpos( $model_name, 'gemini' ) !== false ) {
			$provider = 'gemini';
		} elseif ( strpos( $model_name, 'llama' ) !== false || strpos( $model_name, 'openrouter' ) !== false ) {
			$provider = 'openrouter';
		} elseif ( strpos( $model_name, 'local' ) !== false ) {
			$provider = 'ollama';
		}

		$model = ModelFactory::create( $provider );
		return new Orchestrator( $model );
	}
}
