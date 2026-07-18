<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\Factories;

use NexusAI\Workforce\AI\Models\AIModelInterface;
use NexusAI\Workforce\AI\Models\OpenAIAdapter;
use NexusAI\Workforce\AI\Models\ClaudeAdapter;
use NexusAI\Workforce\AI\Models\GeminiAdapter;
use NexusAI\Workforce\AI\Models\OpenRouterAdapter;
use NexusAI\Workforce\AI\Models\OllamaAdapter;
use NexusAI\Workforce\Utils\Encryption;
use NexusAI\Workforce\Repositories\SettingsRepository;

/**
 * Factory for creating AI model instances based on provider type.
 */
class ModelFactory {

	public static function create( string $provider, bool $with_fallback = true ): AIModelInterface {
		$settings = new SettingsRepository();
		$encryption = new Encryption();

		switch ( $provider ) {
			case 'claude':
				$key = $encryption->decrypt( $settings->get( 'claude_api_key', '' ) );
				return new ClaudeAdapter( $key );
			case 'gemini':
				$key = $encryption->decrypt( $settings->get( 'gemini_api_key', '' ) );
				return new GeminiAdapter( $key );
			case 'openrouter':
				$key = $encryption->decrypt( $settings->get( 'openrouter_api_key', '' ) );
				return new OpenRouterAdapter( $key );
			case 'ollama':
				return new OllamaAdapter( '' ); // Ollama typically doesn't need an API key locally
			case 'openai':
			default:
				$key = $encryption->decrypt( $settings->get( 'openai_api_key', '' ) );
				return new OpenAIAdapter( $key );
		}
	}
}
