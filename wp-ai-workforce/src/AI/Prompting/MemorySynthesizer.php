<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\Prompting;

use NexusAI\Workforce\AI\Models\AIModelInterface;

/**
 * Synthesizes and compresses long conversation histories into strategic summaries.
 */
class MemorySynthesizer {

	/**
	 * @var AIModelInterface
	 */
	private $model;

	public function __construct( AIModelInterface $model ) {
		$this->model = $model;
	}

	/**
	 * Summarize a conversation history.
	 *
	 * @param array $messages The full message history.
	 * @return string         A concise summary of the key points and decisions.
	 */
	public function synthesize( array $messages ): string {
		if ( count( $messages ) < 6 ) {
			return ""; // Only synthesize if the history is long
		}

		$history_text = "";
		foreach ( $messages as $msg ) {
			if ( $msg['role'] === 'system' ) continue;
			$history_text .= strtoupper($msg['role']) . ": " . ($msg['content'] ?? '') . "\n";
		}

		$prompt = [
			[
				'role' => 'system',
				'content' => 'You are a Strategic Memory Assistant. Your job is to summarize long conversations into a concise "Context Summary". Focus on: Key Decisions made, Strategic Insights gained, and Pending Actions. Keep it under 250 words.'
			],
			[
				'role' => 'user',
				'content' => "Please synthesize this conversation history:\n\n" . $history_text
			]
		];

		try {
			$result = $this->model->generate_completion( $prompt, [ 'temperature' => 0.3 ] );
			return $result['content'] ?? "";
		} catch ( \Exception $e ) {
			return "";
		}
	}
}
