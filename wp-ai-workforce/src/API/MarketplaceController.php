<?php
declare(strict_types=1);

namespace NexusAI\Workforce\API;

use WP_REST_Request;
use WP_REST_Response;
use NexusAI\Workforce\Repositories\EmployeeRepository;

/**
 * Controller for Exporting/Importing AI agents.
 */
class MarketplaceController {

	private $repository;

	public function __construct() {
		$this->repository = new EmployeeRepository();
	}

	/**
	 * Export an employee as a JSON Bundle.
	 */
	public function export_item( WP_REST_Request $request ): WP_REST_Response {
		$id = (int) $request['id'];
		$item = $this->repository->get_by_id( $id );

		if ( ! $item ) {
			return new WP_REST_Response( [ 'message' => 'Agent not found' ], 404 );
		}

		// Strip IDs and internal timestamps
		unset( $item['id'], $item['created_at'] );

		$bundle = [
			'version' => '1.0',
			'type'    => 'nexus-ai-bundle',
			'data'    => $item,
		];

		return new WP_REST_Response( $bundle, 200 );
	}

	/**
	 * Import an employee from a JSON Bundle.
	 */
	public function import_item( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_json_params();

		// Handle key-based marketplace simulation
		if ( isset( $params['agent_key'] ) ) {
			$templates = [
				'grant_writer' => [ 'name' => 'Grant Writer Pro', 'position' => 'Grant Writer', 'role_description' => 'I am a visionary grant writer specializing in high-value federal and private grant acquisition.', 'prompt_template' => 'Synthesize organization data into winning grant proposals.', 'model_settings' => wp_json_encode(['model' => 'gpt-4o', 'temperature' => 0.7]) ],
				'lawyer'       => [ 'name' => 'Corporate Counsel', 'position' => 'Legal Advisor', 'role_description' => 'I am a meticulous corporate lawyer specializing in contract law, compliance, and risk mitigation.', 'prompt_template' => 'Review legal documents and ensure full regulatory adherence.', 'model_settings' => wp_json_encode(['model' => 'gpt-4o', 'temperature' => 0.1]) ],
				'doctor'       => [ 'name' => 'Medical Advisor', 'position' => 'Health Consultant', 'role_description' => 'I am a clinical expert focused on wellness, biological data analysis, and preventative health strategy.', 'prompt_template' => 'Analyze health data and provide evidence-based wellness recommendations.', 'model_settings' => wp_json_encode(['model' => 'gpt-4o', 'temperature' => 0.4]) ],
				'finance'      => [ 'name' => 'Wealth Strategist', 'position' => 'Financial Planner', 'role_description' => 'I am a high-level financial strategist focused on capital allocation, tax optimization, and long-term wealth preservation.', 'prompt_template' => 'Develop comprehensive financial growth and risk management strategies.', 'model_settings' => wp_json_encode(['model' => 'gpt-4o', 'temperature' => 0.2]) ],
				'wp_architect' => [ 'name' => 'WP Architect', 'position' => 'Plugin Developer', 'role_description' => 'I am a senior WordPress engineer and systems architect.', 'prompt_template' => 'Build high-performance, secure, and scalable WordPress extensions.', 'model_settings' => wp_json_encode(['model' => 'gpt-4o', 'temperature' => 0.2]) ],
			];

			$data = $templates[ $params['agent_key'] ] ?? null;
			if ( ! $data ) return new WP_REST_Response( [ 'message' => 'Agent key not found' ], 404 );

			$id = $this->repository->create( $data );
			return new WP_REST_Response( [ 'id' => $id, 'success' => true ], 201 );
		}

		$bundle = $params;
		if ( ! isset( $bundle['type'] ) || $bundle['type'] !== 'nexus-ai-bundle' ) {
			return new WP_REST_Response( [ 'message' => 'Invalid bundle format' ], 400 );
		}

		$data = $bundle['data'];
		$id = $this->repository->create( $data );

		return new WP_REST_Response( [ 'id' => $id, 'success' => true ], 201 );
	}
}
