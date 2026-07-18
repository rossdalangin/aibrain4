<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\Agents;

use NexusAI\Workforce\AI\Models\AIModelInterface;
use NexusAI\Workforce\Integrations\ActionRegistry;
use NexusAI\Workforce\AI\Prompting\PromptBuilder;
use NexusAI\Workforce\AI\RAG\Searcher;

/**
 * Orchestrates multi-agent interactions and task delegation.
 */
class Orchestrator {

	/**
	 * @var AIModelInterface
	 */
	private $model;

	/**
	 * @var ActionRegistry
	 */
	private $action_registry;

	/**
	 * @var PromptBuilder
	 */
	private $prompt_builder;

	/**
	 * @var Searcher
	 */
	private $searcher;

	public function __construct( AIModelInterface $model ) {
		$this->model = $model;
		$this->action_registry = new ActionRegistry();
		$this->prompt_builder = new PromptBuilder();
		$this->searcher = new Searcher();
	}

	/**
	 * Facilitate a multi-agent meeting.
	 *
	 * @param string $topic  The discussion topic.
	 * @param array  $agents Array of agent data.
	 * @return array         Transcript of the meeting.
	 */
	public function facilitate_meeting( string $topic, array $agents ): array {
		$transcript = [];
		$context = "Meeting Topic: $topic\n\nParticipants:\n";

		foreach ( $agents as $agent ) {
			$context .= "- " . $agent['name'] . " (" . $agent['position'] . ")\n";
		}

		// Round-robin debate with explicit Consensus detection
		foreach ( $agents as $agent ) {
			$prompt = $context . "\nTranscript so far:\n";
			foreach ( $transcript as $entry ) {
				$prompt .= $entry['agent'] . ": " . $entry['content'] . "\n";
			}
			$prompt .= "\n" . $agent['name'] . ", based on the goals and KPIs of your position, what is your opinion on this? If a decision is already clear, explicitly state 'I AGREE' or 'I DISAGREE' and explain why.";

			$result = $this->process_request( $prompt, $agent );
			$transcript[] = [
				'agent'   => $agent['name'],
				'content' => $result['content'],
			];
		}

		return $transcript;
	}

	/**
	 * Process a complex request by delegating to specialized agents.
	 *
	 * @param string $request   The user request.
	 * @param array  $agent_data The primary agent data (employee).
	 * @param string $company_context General company context.
	 * @return string           Final response.
	 */
	public function process_request( string $request, array $agent_data, string $company_context = '', string $department_context = '' ): array {
		// 1. Resolve Global Company Context if not provided
		if ( empty( $company_context ) ) {
			$settings = new \NexusAI\Workforce\Repositories\SettingsRepository();
			$mission  = $settings->get( 'company_mission', '' );
			$values   = $settings->get( 'company_values', '' );
			$audience = $settings->get( 'company_audience', '' );

			$company_context = "COMPANY VISION: $mission\nCORE VALUES: $values\nTARGET AUDIENCE: $audience";
		}

		// 2. Perform RAG search
		$kb_context = $this->searcher->search( $request, [ 'agent_id' => $agent_data['id'] ?? 0 ] );

		// 3. Build system prompt with context layers
		$system_prompt = $this->prompt_builder->build( array_merge( $agent_data, [
			'company_context'    => $company_context,
			'department_context' => $department_context,
			'kb_context'         => $kb_context
		] ) );

		// 4. Chain of Thought (CoT) injection for complex reasoning
		$request .= "\n\nPlease think step-by-step before providing your final answer. Structure your output by first describing your reasoning process in a 'Reasoning' section, followed by your 'Final Response'.";

		$messages = [
			[ 'role' => 'system', 'content' => $system_prompt ],
			[ 'role' => 'user', 'content' => $request ],
		];

		// Hallucination Guard: Ground response in KB if request seems factual
		if ( strlen($kb_context) > 100 ) {
			$messages[0]['content'] .= "\n\nHALLUCINATION GUARD: Your response must be strictly grounded in the 'Relevant information from Knowledge Base' provided. If the information is not present, explicitly state that you do not know.";
		}

		$settings = $this->parse_settings( $agent_data['model_settings'] ?? '{}' );
		if ( empty( $settings['model'] ) ) {
			$settings_repo = new \NexusAI\Workforce\Repositories\SettingsRepository();
			$settings['model'] = $agent_data['model'] ?? $settings_repo->get( 'default_model', 'gpt-4o' );
		}
		if ( empty( $settings['model'] ) ) {
			$settings['model'] = 'gpt-4o';
		}
		$settings['tools'] = $this->action_registry->get_tools_definition();

		$usage = [ 'prompt_tokens' => 0, 'completion_tokens' => 0 ];

		$result = [ 'content' => '', 'tool_calls' => [], 'usage' => [] ];
		try {
			$result = $this->model->generate_completion( $messages, $settings );
			$usage['prompt_tokens']     += $result['usage']['prompt_tokens'] ?? 0;
			$usage['completion_tokens'] += $result['usage']['completion_tokens'] ?? 0;
		} catch ( \Exception $e ) {
			// Failover to secondary provider if primary fails
			try {
				$settings_repo = new \NexusAI\Workforce\Repositories\SettingsRepository();
				$fallback_provider = $settings_repo->get( 'fallback_provider', 'claude' );

				$fallback_model = \NexusAI\Workforce\AI\Factories\ModelFactory::create($fallback_provider);
				$result = $fallback_model->generate_completion( $messages, $settings );

				$usage['prompt_tokens']     += $result['usage']['prompt_tokens'] ?? 0;
				$usage['completion_tokens'] += $result['usage']['completion_tokens'] ?? 0;
			} catch ( \Exception $fallback_e ) {
				$result = [
					'content'    => "AI model interaction failed. Connection Error: " . $fallback_e->getMessage(),
					'tool_calls' => [],
					'usage'      => []
				];
			}
		}

		// 4. Handle tool calls (Recursive loop for multi-turn multi-tool execution)
		$max_iterations = 5;
		$iteration = 0;

		while ( ! empty( $result['tool_calls'] ) && $iteration < $max_iterations ) {
			$iteration++;
			$messages[] = [
				'role'       => 'assistant',
				'content'    => $result['content'] ?? '',
				'tool_calls' => $result['tool_calls']
			];

			foreach ( $result['tool_calls'] as $tool_call ) {
				$name = $tool_call['function']['name'];
				$args = json_decode( $tool_call['function']['arguments'], true ) ?: [];

				try {
					$tool_result = $this->action_registry->execute( $name, $args );
					$messages[] = [
						'role'         => 'tool',
						'tool_call_id' => $tool_call['id'] ?? 'call_' . uniqid(),
						'name'         => $name,
						'content'      => wp_json_encode( $tool_result )
					];
				} catch ( \Exception $e ) {
					$messages[] = [
						'role'         => 'tool',
						'tool_call_id' => $tool_call['id'] ?? 'call_' . uniqid(),
						'name'         => $name,
						'content'      => "Error: " . $e->getMessage()
					];
				}
			}

			$result = $this->model->generate_completion( $messages, $settings );
			$usage['prompt_tokens']     += $result['usage']['prompt_tokens'] ?? 0;
			$usage['completion_tokens'] += $result['usage']['completion_tokens'] ?? 0;
		}

		return [
			'content' => $result['content'] ?? 'Action completed.',
			'usage'   => $usage,
		];
	}

	/**
	 * Parse model settings from JSON.
	 */
	private function parse_settings( $settings ): array {
		if ( is_string( $settings ) ) {
			return json_decode( $settings, true ) ?: [];
		}
		return (array) $settings;
	}
}
