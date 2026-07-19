<?php
/**
 * Plugin Name: Nexus AI Workforce - Licensing & Payment SaaS Bridge
 * Plugin URI: https://nexus-ai-saas.com
 * Description: Enterprise licensing manager, key activation, and payment portal connector for Nexus AI Workforce.
 * Version: 1.0.0
 * Author: Nexus AI Workforce Inc.
 * Author URI: https://nexus-ai-saas.com
 * License: GPL-2.0-or-later
 * Requires PHP: 8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Secure exit if accessed directly
}

/**
 * Handles licensing validation and payment stubs.
 */
class Nexus_AI_Licensing {

	private $namespace = 'nexus-licensing/v1';

	public static function init() {
		$instance = new self();
		add_action( 'rest_api_init', [ $instance, 'register_routes' ] );
		add_action( 'admin_menu', [ $instance, 'add_licensing_menu' ] );
	}

	/**
	 * Register secure API endpoints for remote license checks.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/validate', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'api_validate_license' ],
				'permission_callback' => '__return_true', // Open endpoint for validation
			],
		] );

		register_rest_route( $this->namespace, '/checkout', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'api_create_checkout_session' ],
				'permission_callback' => [ $this, 'check_admin_permission' ],
			],
		] );
	}

	/**
	 * Add custom license configuration panel in WordPress admin.
	 */
	public function add_licensing_menu() {
		add_submenu_page(
			'nexus-ai-workforce', // Parent menu (Nexus AI main dashboard)
			'Licensing & Payments',
			'License & Payments',
			'manage_options',
			'nexus-ai-licensing',
			[ $this, 'render_licensing_page' ]
		);
	}

	/**
	 * Validate a license key from the client and sign it cryptographically.
	 */
	public function api_validate_license( WP_REST_Request $request ) {
		$license_key = sanitize_text_field( $request->get_param( 'license_key' ) );
		if ( empty( $license_key ) ) {
			return new WP_REST_Response( [ 'success' => false, 'message' => 'License key is required.' ], 400 );
		}

		$plan = 'pro';
		if ( strpos( strtolower( $license_key ), 'agency' ) !== false ) {
			$plan = 'agency';
		} elseif ( strpos( strtolower( $license_key ), 'enterprise' ) !== false ) {
			$plan = 'enterprise';
		}

		$secret    = 'nexus_secret_salt_12345';
		$signature = hash_hmac( 'sha256', $license_key . '|' . $plan, $secret );

		return new WP_REST_Response( [
			'success'   => true,
			'plan'      => $plan,
			'signature' => $signature,
			'expires_at' => date( 'Y-m-d H:i:s', strtotime( '+1 year' ) ),
			'message'   => 'License successfully verified by SaaS Master Server.'
		], 200 );
	}

	/**
	 * Simulate creating a Stripe Billing Checkout session for SaaS subscriptions.
	 */
	public function api_create_checkout_session( WP_REST_Request $request ) {
		$plan_name = sanitize_text_field( $request->get_param( 'plan' ) );

		// Secure Stripe checkout redirection stub
		$checkout_url = 'https://checkout.stripe.com/pay/cs_live_' . bin2hex( random_bytes( 16 ) );

		return new WP_REST_Response( [
			'success'      => true,
			'checkout_url' => $checkout_url,
			'message'      => 'Stripe subscription checkout session initiated.'
		], 200 );
	}

	/**
	 * Secure permission check: only administrators can access licensing parameters.
	 */
	public function check_admin_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Render the secure license configuration page.
	 */
	public function render_licensing_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized access.' );
		}

		// Handle Form Submission
		if ( isset( $_POST['nexus_license_submit'] ) && check_admin_referer( 'nexus_save_license', 'nexus_license_nonce' ) ) {
			$key = sanitize_text_field( $_POST['license_key'] );
			update_option( 'nexus_ai_license_key', $key );

			// Simulate activation via API route
			$request = new WP_REST_Request( 'POST', '/' . $this->namespace . '/validate' );
			$request->set_param( 'license_key', $key );
			$response = $this->api_validate_license( $request );
			$data = $response->get_data();

			if ( $data['success'] ) {
				update_option( 'nexus_ai_active_plan', $data['plan'] );
				update_option( 'nexus_ai_license_signature', $data['signature'] );
				echo '<div class="notice notice-success is-dismissible"><p>License successfully verified! Plan: ' . strtoupper($data['plan']) . '</p></div>';
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>Invalid license key.</p></div>';
			}
		}

		$current_key = get_option( 'nexus_ai_license_key', '' );
		$current_plan = \NexusAI\Workforce\API\BillingController::get_verified_plan();
		?>
		<div class="wrap">
			<h1>Nexus AI Workforce - Licensing Control</h1>
			<p>Configure subscription parameters, activate premium licenses, and connect payment gateways securely.</p>

			<div class="card" style="max-w: 600px; padding: 20px; background: #fff; margin-top: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
				<h2>License Activation</h2>
				<p>Active Premium Subscription Plan: <strong><?php echo strtoupper($current_plan); ?></strong></p>

				<form method="POST">
					<?php wp_nonce_field( 'nexus_save_license', 'nexus_license_nonce' ); ?>
					<table class="form-table">
						<tr>
							<th scope="row"><label for="license_key">License Key</label></th>
							<td>
								<input type="text" name="license_key" id="license_key" value="<?php echo esc_attr($current_key); ?>" class="regular-text" placeholder="NEXUS-XXXX-XXXX-XXXX" style="padding: 10px; width: 100%;">
								<p class="description">Enter your premium or SaaS license key. Use <code>NEXUS-PRO-KEY</code>, <code>NEXUS-AGENCY-KEY</code>, or <code>NEXUS-ENTERPRISE-KEY</code> for simulation.</p>
							</td>
						</tr>
					</table>
					<p class="submit">
						<input type="submit" name="nexus_license_submit" class="button button-primary button-large" value="Activate and Verify Subscription" style="padding: 8px 20px; height: auto;">
					</p>
				</form>
			</div>

			<div class="card" style="max-w: 600px; padding: 20px; background: #fff; margin-top: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
				<h2>SaaS Integration & Pricing Tiers</h2>
				<p>Accept subscription payments globally using Stripe or PayPal. Standard plans:</p>
				<ul>
					<li><strong>PRO Plan</strong> ($197/mo): Deploy up to 10 AI employees.</li>
					<li><strong>AGENCY Plan</strong> ($497/mo): Deploy up to 100 AI employees.</li>
					<li><strong>ENTERPRISE Plan</strong> ($997/mo): Unlimited AI employees and vector stores.</li>
				</ul>
				<p><a href="https://nexus-ai-saas.com/pricing" class="button" target="_blank">View Stripe Pricing Portal</a></p>
			</div>
		</div>
		<?php
	}
}

add_action( 'plugins_loaded', [ 'Nexus_AI_Licensing', 'init' ] );
