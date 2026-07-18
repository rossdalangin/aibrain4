<?php
declare(strict_types=1);

namespace NexusAI\Workforce\API;

use WP_REST_Server;

/**
 * Handles registration of Nexus AI REST API endpoints.
 */
class RestHandler {

	/**
	 * @var string
	 */
	private $namespace = 'nexus-ai/v1';

	/**
	 * Initialize the REST API.
	 */
	public function init(): void {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register all routes.
	 */
	public function register_routes(): void {
		$employee_controller = new EmployeeController();
		$chat_controller = new ChatController();
		$settings_controller = new SettingsController();
		$status_controller = new StatusController();
		$marketplace_controller = new MarketplaceController();
		$kb_controller = new KBController();
		$billing_controller = new BillingController();
		$dept_controller = new DepartmentController();

		register_rest_route( $this->namespace, '/settings', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $settings_controller, 'get_items' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $settings_controller, 'update_item' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/billing/upgrade', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $billing_controller, 'upgrade_plan' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/kb/direct', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $kb_controller, 'ingest_direct_text' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/system/seed', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => function() {
					$engine = new \NexusAI\Workforce\Utils\SampleDataEngine();
					return new \WP_REST_Response( $engine->seed(), 200 );
				},
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/system/purge-all', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => function() {
					$engine = new \NexusAI\Workforce\Utils\SampleDataEngine();
					$engine->clear();
					return new \WP_REST_Response( [ 'success' => true ], 200 );
				},
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/departments/(?P<id>\d+)', [
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => function( \WP_REST_Request $request ) {
					$id = (int) $request['id'];
					global $wpdb;
					$wpdb->delete( $wpdb->prefix . 'ai_departments', [ 'id' => $id ] );
					return new \WP_REST_Response( [ 'success' => true ], 200 );
				},
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/conversations/(?P<id>\d+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => function( \WP_REST_Request $request ) {
					$id = (int) $request['id'];
					$repo = new \NexusAI\Workforce\Repositories\MessageRepository();
					return new \WP_REST_Response( $repo->get_conversation_messages( $id ), 200 );
				},
				'permission_callback' => [ $this, 'check_permission' ],
			],
			[
				'methods'             => [ WP_REST_Server::EDITABLE, WP_REST_Server::CREATABLE ],
				'callback'            => function( \WP_REST_Request $request ) {
					$id = (int) $request['id'];
					$title = sanitize_text_field( $request['title'] );
					global $wpdb;
					$wpdb->update( $wpdb->prefix . 'ai_conversations', [ 'title' => $title ], [ 'id' => $id ] );
					return new \WP_REST_Response( [ 'success' => true ], 200 );
				},
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/billing/cancel', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => function() {
					update_option( 'nexus_ai_cancel_pending', true );
					return new \WP_REST_Response( [ 'success' => true ], 200 );
				},
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/chat/meeting', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $chat_controller, 'run_meeting_step' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/status/purge', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => function() {
					global $wpdb;
					$wpdb->query( "DELETE FROM {$wpdb->prefix}ai_usage_logs" );
					$wpdb->query( "DELETE FROM {$wpdb->prefix}ai_audit_logs" );
					return new \WP_REST_Response( [ 'success' => true ], 200 );
				},
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/kb/wipe', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => function() {
					global $wpdb;
					$wpdb->query( "DELETE FROM {$wpdb->prefix}ai_knowledge_chunks" );
					$wpdb->query( "DELETE FROM {$wpdb->prefix}ai_knowledge_documents" );
					return new \WP_REST_Response( [ 'success' => true ], 200 );
				},
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/kb/upload', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $kb_controller, 'upload_item' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/billing/plans', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $billing_controller, 'get_plans' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/billing/coupon', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $billing_controller, 'apply_coupon' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/kb/ingest', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $kb_controller, 'ingest_item' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/marketplace/export/(?P<id>\d+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $marketplace_controller, 'export_item' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/marketplace/import', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $marketplace_controller, 'import_item' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/status', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $status_controller, 'get_status' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/conversations', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $chat_controller, 'get_conversations' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $chat_controller, 'send_message' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/employees', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $employee_controller, 'get_items' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $employee_controller, 'create_item' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/workflows', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => function( \WP_REST_Request $request ) {
					$repo = new \NexusAI\Workforce\Repositories\WorkflowRepository();
					return new \WP_REST_Response( $repo->get_all(), 200 );
				},
				'permission_callback' => [ $this, 'check_permission' ],
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => function( \WP_REST_Request $request ) {
					$params = $request->get_params();
					$repo = new \NexusAI\Workforce\Repositories\WorkflowRepository();
					$id = $repo->create( [
						'name'       => sanitize_text_field( $params['name'] ),
						'definition' => wp_json_encode( $params['steps'] ),
						'is_active'  => 1
					] );
					return new \WP_REST_Response( [ 'id' => $id ], 201 );
				},
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/workflows/(?P<id>\d+)', [
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => function( \WP_REST_Request $request ) {
					$id = (int) $request['id'];
					global $wpdb;
					$wpdb->update( $wpdb->prefix . 'ai_workflows', [ 'is_active' => 0 ], [ 'id' => $id ] );
					return new \WP_REST_Response( [ 'success' => true ], 200 );
				},
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/workflows/run/(?P<id>\d+)', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => function( \WP_REST_Request $request ) {
					$id = (int) $request['id'];
					$params = $request->get_params();
					$repo = new \NexusAI\Workforce\Repositories\WorkflowRepository();
					$workflow = $repo->get_by_id( $id );

					if ( ! $workflow ) return new \WP_REST_Response( [ 'message' => 'Workflow not found' ], 404 );

					$engine = new \NexusAI\Workforce\AI\Workflows\ExecutionEngine();
					$result = $engine->run( [ 'steps' => json_decode( $workflow['definition'], true ) ], $params['input'] ?? '' );

					return new \WP_REST_Response( $result, 200 );
				},
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/departments', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $dept_controller, 'get_items' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $dept_controller, 'create_item' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );

		register_rest_route( $this->namespace, '/public/chat', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $chat_controller, 'send_public_message' ],
				'permission_callback' => '__return_true',
			],
		] );

		register_rest_route( $this->namespace, '/tutorials/complete', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => function( \WP_REST_Request $request ) {
					$params = $request->get_params();
					$lesson_id = sanitize_text_field( $params['lesson_id'] );
					$user_id = get_current_user_id();
					$completed = get_user_meta( $user_id, 'nexus_ai_completed_lessons', true ) ?: [];
					if ( ! in_array( $lesson_id, $completed ) ) {
						$completed[] = $lesson_id;
						update_user_meta( $user_id, 'nexus_ai_completed_lessons', $completed );
					}
					return new \WP_REST_Response( [ 'success' => true, 'completed' => $completed ], 200 );
				},
				'permission_callback' => function() { return is_user_logged_in(); },
			],
		] );

		register_rest_route( $this->namespace, '/employees/(?P<id>\d+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $employee_controller, 'get_item' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ $employee_controller, 'update_item' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $employee_controller, 'delete_item' ],
				'permission_callback' => [ $this, 'check_permission' ],
			],
		] );
	}

	/**
	 * Check if the user has permission to access the API.
	 */
	public function check_permission( \WP_REST_Request $request ): bool {
		// Simple Rate Limiting scoped to our plugin namespace
		if ( ! current_user_can('manage_options') ) {
			$transient_key = 'nexus_ai_rate_limit_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
			$calls = (int) get_transient( $transient_key );
			if ( $calls > 50 ) {
				return false;
			}
			set_transient( $transient_key, $calls + 1, HOUR_IN_SECONDS );
		}

		return current_user_can( 'manage_options' );
	}
}
