<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Integrations;

use NexusAI\Workforce\Integrations\Actions\BaseAction;
use NexusAI\Workforce\Integrations\Actions\CreatePostAction;
use NexusAI\Workforce\Integrations\Actions\ManageUserAction;
use NexusAI\Workforce\Integrations\Actions\AnalyticsReportAction;
use NexusAI\Workforce\Integrations\Actions\WooCommerceAction;
use NexusAI\Workforce\Integrations\Actions\WebhookAction;
use NexusAI\Workforce\Integrations\Actions\SlackAction;
use NexusAI\Workforce\Integrations\Actions\DiscordAction;
use NexusAI\Workforce\Integrations\Actions\ImageGenerationAction;
use NexusAI\Workforce\Integrations\Actions\CommentManagementAction;
use NexusAI\Workforce\Integrations\Actions\MetadataAction;
use NexusAI\Workforce\Integrations\Actions\FormsAction;
use NexusAI\Workforce\Integrations\Actions\DelegateAction;

/**
 * Registry for all AI-executable actions.
 */
class ActionRegistry {

	/**
	 * @var BaseAction[]
	 */
	private $actions = [];

	public function __construct() {
		// Register core actions
		$this->register( new CreatePostAction() );
		$this->register( new ManageUserAction() );
		$this->register( new AnalyticsReportAction() );
		$this->register( new WooCommerceAction() );
		$this->register( new WebhookAction() );
		$this->register( new SlackAction() );
		$this->register( new DiscordAction() );
		$this->register( new ImageGenerationAction() );
		$this->register( new CommentManagementAction() );
		$this->register( new MetadataAction() );
		$this->register( new FormsAction() );
		$this->register( new DelegateAction() );
	}

	/**
	 * Register a new action.
	 */
	public function register( BaseAction $action ): void {
		$this->actions[ $action->get_name() ] = $action;
	}

	/**
	 * Get all registered actions formatted for the AI (OpenAI Tools format).
	 */
	public function get_tools_definition(): array {
		$tools = [];
		foreach ( $this->actions as $action ) {
			$tools[] = [
				'type' => 'function',
				'function' => [
					'name'        => $action->get_name(),
					'description' => $action->get_description(),
					'parameters'  => $action->get_parameters(),
				],
			];
		}
		return $tools;
	}

	/**
	 * Execute an action by name.
	 */
	public function execute( string $name, array $args ) {
		if ( ! isset( $this->actions[ $name ] ) ) {
			throw new \Exception( "Action '$name' not found." );
		}
		return $this->actions[ $name ]->execute( $args );
	}
}
