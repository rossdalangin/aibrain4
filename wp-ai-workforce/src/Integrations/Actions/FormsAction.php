<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Integrations\Actions;

/**
 * Action to retrieve entries from common WordPress form plugins.
 */
class FormsAction extends BaseAction {

	public function get_name(): string {
		return 'get_form_entries';
	}

	public function get_description(): string {
		return 'Retrieve submissions from Gravity Forms, Fluent Forms, or WPForms. Use this to analyze leads or customer inquiries.';
	}

	public function get_parameters(): array {
		return [
			'type' => 'object',
			'properties' => [
				'plugin'  => [ 'type' => 'string', 'enum' => [ 'gravity', 'fluent', 'wpforms' ] ],
				'form_id' => [ 'type' => 'integer' ],
				'limit'   => [ 'type' => 'integer', 'default' => 5 ],
			],
			'required' => [ 'plugin', 'form_id' ],
		];
	}

	public function execute( array $args ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			throw new \Exception( 'Insufficient permissions to read form entries.' );
		}

		$plugin  = $args['plugin'];
		$form_id = (int) $args['form_id'];
		$limit   = (int) ( $args['limit'] ?? 5 );

		switch ( $plugin ) {
			case 'gravity':
				if ( ! class_exists( 'GFAPI' ) ) throw new \Exception('Gravity Forms not active.');
				return \GFAPI::get_entries( $form_id, [], null, [ 'page_size' => $limit ] );

			case 'fluent':
				global $wpdb;
				return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fluentform_submissions WHERE form_id = %d LIMIT %d", $form_id, $limit ) );

			case 'wpforms':
				if ( ! function_exists( 'wpforms' ) ) throw new \Exception('WPForms not active.');
				return wpforms()->entry->get_entries( [ 'form_id' => $form_id, 'number' => $limit ] );

			default:
				return [ 'error' => 'Unsupported form plugin' ];
		}
	}
}
