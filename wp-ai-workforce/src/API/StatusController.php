<?php
declare(strict_types=1);

namespace NexusAI\Workforce\API;

use WP_REST_Request;
use WP_REST_Response;
use NexusAI\Workforce\Utils\SystemStatus;

/**
 * Controller for exposing system health and status.
 */
class StatusController {

	public function get_status( WP_REST_Request $request ): WP_REST_Response {
		$status = ( new SystemStatus() )->get_report();
		return new WP_REST_Response( $status, 200 );
	}
}
