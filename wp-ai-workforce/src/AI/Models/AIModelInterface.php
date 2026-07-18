<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\Models;

/**
 * Interface for AI model providers.
 */
interface AIModelInterface {

	/**
	 * Generate a completion from the AI model.
	 *
	 * @param array $messages   Array of message objects (role, content).
	 * @param array $settings   Model-specific settings (temperature, max_tokens, etc.).
	 * @return array            The completion result with content and usage data.
	 */
	public function generate_completion( array $messages, array $settings ): array;

	/**
	 * Generate embeddings for a given text.
	 *
	 * @param string $text The input text.
	 * @return array       The vector embeddings.
	 */
	public function generate_embeddings( string $text ): array;

	/**
	 * Get the identifier of the provider.
	 *
	 * @return string
	 */
	public function get_id(): string;
}
