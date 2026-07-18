<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Integrations\Actions;

/**
 * Base class for all AI-executable WordPress actions.
 */
abstract class BaseAction {

	/**
	 * Get the name of the action (slug).
	 */
	abstract public function get_name(): string;

	/**
	 * Get the description for the AI.
	 */
	abstract public function get_description(): string;

	/**
	 * Get the parameter schema (JSON Schema format).
	 */
	abstract public function get_parameters(): array;

	/**
	 * Execute the action.
	 *
	 * @param array $args The parameters provided by the AI.
	 * @return mixed       Result of the action.
	 */
	abstract public function execute( array $args );
}
