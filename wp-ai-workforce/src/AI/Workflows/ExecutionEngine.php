<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\Workflows;

use NexusAI\Workforce\AI\Agents\Orchestrator;
use NexusAI\Workforce\Repositories\EmployeeRepository;
use NexusAI\Workforce\AI\Factories\ModelFactory;

/**
 * Executes multi-agent sequential workflows with persistent state.
 */
class ExecutionEngine {

	private $employee_repository;

	public function __construct() {
		$this->employee_repository = new EmployeeRepository();
	}

	/**
	 * Run a defined workflow.
	 *
	 * @param array $workflow_definition JSON-decoded definition (steps, agents).
	 * @param string $input              The initial trigger input.
	 * @return array                     The final state and results of each step.
	 */
	public function run( array $workflow_definition, string $input ): array {
		$state = [ 'initial_trigger' => $input ];
		$results = [];

		foreach ( $workflow_definition['steps'] as $index => $step ) {
			$agent_id = (int) $step['agent_id'];
			$task     = $step['task_description'];

			$agent = $this->employee_repository->get_by_id( $agent_id );
			if ( ! $agent ) {
				$results[] = [ 'step' => "Step $index", 'error' => "Agent $agent_id not found." ];
				continue;
			}

			$orchestrator = $this->get_orchestrator( $agent );

			// Build comprehensive context from all previous steps
			$context = "WORKFLOW STATE:\n";
			foreach ( $state as $key => $val ) {
				$context .= "[$key]: " . ( is_string($val) ? $val : json_encode($val) ) . "\n";
			}

			$prompt = "You are participating in a multi-agent workflow.
			Current Task: $task

			$context

			Produce your output based on your specific role and the current workflow state.";

			$output = $orchestrator->process_request( $prompt, $agent );

			$step_name = $step['name'] ?? "Step_" . ($index + 1);
			$results[] = [
				'step'   => $step_name,
				'agent'  => $agent['name'],
				'output' => $output,
			];

			$state[ $step_name ] = $output;
		}

		return [
			'status'  => 'completed',
			'final_state' => $state,
			'results' => $results,
		];
	}

	private function get_orchestrator( array $agent_data ): Orchestrator {
		$settings_repo = new \NexusAI\Workforce\Repositories\SettingsRepository();
		$default_model = $settings_repo->get( 'default_model', 'gpt-4o' );

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
