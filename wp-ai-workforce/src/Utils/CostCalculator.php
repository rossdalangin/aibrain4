<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Utils;

/**
 * Calculates estimated costs for AI usage.
 */
class CostCalculator {

	/**
	 * Pricing per 1k tokens (USD).
	 */
	private const PRICING = [
		'gpt-4o'              => [ 'input' => 0.005, 'output' => 0.015 ],
		'gpt-4o-mini'         => [ 'input' => 0.00015, 'output' => 0.0006 ],
		'claude-3-5-sonnet'   => [ 'input' => 0.003, 'output' => 0.015 ],
		'gemini-1.5-pro'      => [ 'input' => 0.0035, 'output' => 0.0105 ],
		'llama-3.1-405b'      => [ 'input' => 0.005, 'output' => 0.015 ],
	];

	/**
	 * Calculate total cost of an interaction.
	 */
	public function calculate( string $model, int $prompt_tokens, int $completion_tokens ): float {
		$pricing = self::PRICING[ $model ] ?? self::PRICING['gpt-4o']; // Fallback to standard

		$input_cost  = ( $prompt_tokens / 1000 ) * $pricing['input'];
		$output_cost = ( $completion_tokens / 1000 ) * $pricing['output'];

		return (float) ( $input_cost + $output_cost );
	}
}
