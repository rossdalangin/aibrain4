<?php
declare(strict_types=1);

namespace NexusAI\Workforce\API;

use WP_REST_Request;
use WP_REST_Response;
use NexusAI\Workforce\Repositories\DepartmentRepository;

/**
 * Controller for Company Departments.
 */
class DepartmentController {

	private $repository;

	public function __construct() {
		$this->repository = new DepartmentRepository();
	}

	public function get_items( WP_REST_Request $request ): WP_REST_Response {
		return new WP_REST_Response( $this->repository->get_all(), 200 );
	}

	public function create_item( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_params();
		$id = $this->repository->create( [
			'name'        => sanitize_text_field( $params['name'] ),
			'description' => sanitize_textarea_field( $params['description'] ),
		] );
		return new WP_REST_Response( [ 'id' => $id ], 201 );
	}
}
