<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Integrations\Actions;

use NexusAI\Workforce\Repositories\EmployeeRepository;
use NexusAI\Workforce\AI\Agents\Orchestrator;
use NexusAI\Workforce\AI\Factories\ModelFactory;

/**
 * Action that allows an AI agent to delegate a sub-task to another specialized agent.
 */
class DelegateAction extends BaseAction {

	public function get_name(): string {
		return 'delegate_to_specialist';
	}

	public function get_description(): string {
		return 'Delegate a specific sub-task or question to another specialized AI agent in the workforce. Use this when you need expertise outside your primary role.';
	}

	public function get_parameters(): array {
		return [
			'type' => 'object',
			'properties' => [
				'agent_id' => [ 'type' => 'integer', 'description' => 'The ID of the specialist agent to consult' ],
				'task'     => [ 'type' => 'string', 'description' => 'The specific task or question for the specialist' ],
			],
			'required' => [ 'agent_id', 'task' ],
		];
	}

	public function execute( array $args ) {
		$agent_id = (int) $args['agent_id'];
		$task     = sanitize_textarea_field( $args['task'] );

		$repo = new EmployeeRepository();
		$specialist_data = $repo->get_by_id( $agent_id );

		if ( ! $specialist_data ) {
			throw new \Exception( "Specialist agent with ID $agent_id not found." );
		}

		// Instantiate specialist's orchestrator
		$settings_repo = new \NexusAI\Workforce\Repositories\SettingsRepository();
		$default_model = $settings_repo->get( 'default_model', 'gpt-4o' );

		$model_settings = json_decode( $specialist_data['model_settings'] ?? '{}', true );
		$model_name = $specialist_data['model'] ?? $model_settings['model'] ?? $default_model;
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
		$orchestrator = new Orchestrator( $model );

		// Process request through specialist
		$response = $orchestrator->process_request( $task, $specialist_data );

		return [
			'success'    => true,
			'specialist' => $specialist_data['name'],
			'position'   => $specialist_data['position'],
			'response'   => $response,
		];
	}
}
