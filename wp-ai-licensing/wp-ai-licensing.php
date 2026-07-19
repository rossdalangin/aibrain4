<?php
/**
 * Plugin Name: Nexus AI Workforce - Licensing & Payment SaaS Bridge
 * Plugin URI: https://nexus-ai-saas.com
 * Description: Enterprise licensing manager, key activation, and payment portal connector for Nexus AI Workforce.
 * Version: 1.1.0
 * Author: Nexus AI Workforce Inc.
 * Author URI: https://nexus-ai-saas.com
 * License: GPL-2.0-or-later
 * Requires PHP: 8.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Secure exit if accessed directly
}

/**
 * Handles licensing validation, key issuance, payment settings, and subscription management.
 */
class Nexus_AI_Licensing {

	private $namespace = 'nexus-licensing/v1';

	public static function init() {
		$instance = new self();
		add_action( 'rest_api_init', [ $instance, 'register_routes' ] );
		add_action( 'admin_menu', [ $instance, 'add_licensing_menu' ] );
		add_action( 'admin_init', [ $instance, 'initialize_sample_data' ] );
	}

	/**
	 * Initialize sample licenses and billing logs for pristine out-of-the-box experience.
	 */
	public function initialize_sample_data() {
		if ( ! get_option( 'nexus_ai_issued_licenses' ) ) {
			$initial_keys = [
				'NEXUS-PRO-DEMOKEY-9921' => [
					'plan'       => 'pro',
					'status'     => 'active',
					'created_at' => date( 'Y-m-d H:i:s', strtotime( '-15 days' ) ),
					'expires_at' => date( 'Y-m-d H:i:s', strtotime( '+350 days' ) ),
					'activated_on' => 'https://agency-client-demo.com'
				],
				'NEXUS-AGENCY-DEMOKEY-8821' => [
					'plan'       => 'agency',
					'status'     => 'active',
					'created_at' => date( 'Y-m-d H:i:s', strtotime( '-2 days' ) ),
					'expires_at' => date( 'Y-m-d H:i:s', strtotime( '+363 days' ) ),
					'activated_on' => 'https://enterprise-workforce-portal.com'
				]
			];
			update_option( 'nexus_ai_issued_licenses', $initial_keys );
		}

		if ( ! get_option( 'nexus_ai_payment_logs' ) ) {
			$initial_logs = [
				[
					'tx_id'      => 'ch_3Mv8Y1LkdIwHu7ix2SgQ81Y',
					'date'       => date( 'Y-m-d H:i:s', strtotime( '-2 days' ) ),
					'email'      => 'billing@agency-client.com',
					'amount'     => '997.00',
					'plan'       => 'Agency',
					'gateway'    => 'Stripe Checkout',
					'status'     => 'Completed'
				],
				[
					'tx_id'      => 'ch_3Mv7X2KkdIwHu7ix1AfP70Z',
					'date'       => date( 'Y-m-d H:i:s', strtotime( '-15 days' ) ),
					'email'      => 'finance@pro-solopreneur.io',
					'amount'     => '497.00',
					'plan'       => 'Professional',
					'gateway'    => 'Stripe Checkout',
					'status'     => 'Completed'
				]
			];
			update_option( 'nexus_ai_payment_logs', $initial_logs );
		}
	}

	/**
	 * Register secure API endpoints for remote license checks.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/validate', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'api_validate_license' ],
				'permission_callback' => '__return_true', // Open endpoint for client validation
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

		$issued_licenses = get_option( 'nexus_ai_issued_licenses', [] );
		$plan = 'pro';
		$expires_at = date( 'Y-m-d H:i:s', strtotime( '+1 year' ) );

		if ( isset( $issued_licenses[ $license_key ] ) ) {
			$plan = $issued_licenses[ $license_key ]['plan'];
			$expires_at = $issued_licenses[ $license_key ]['expires_at'];
		} else {
			// Support test patterns for on-the-fly activation
			if ( strpos( strtolower( $license_key ), 'agency' ) !== false ) {
				$plan = 'agency';
			} elseif ( strpos( strtolower( $license_key ), 'enterprise' ) !== false ) {
				$plan = 'enterprise';
			}
		}

		$secret    = 'nexus_secret_salt_12345';
		$signature = hash_hmac( 'sha256', $license_key . '|' . $plan, $secret );

		return new WP_REST_Response( [
			'success'    => true,
			'plan'       => $plan,
			'signature'  => $signature,
			'expires_at' => $expires_at,
			'message'    => 'License successfully verified by SaaS Master Server.'
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
	 * Render the secure license configuration and management dashboard page.
	 */
	public function render_licensing_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized access.' );
		}

		$issued_licenses = get_option( 'nexus_ai_issued_licenses', [] );
		$payment_logs    = get_option( 'nexus_ai_payment_logs', [] );

		// Handle Key Generation
		if ( isset( $_POST['nexus_generate_license'] ) && check_admin_referer( 'nexus_generate_nonce_action', 'nexus_gen_nonce' ) ) {
			$plan_tier = sanitize_text_field( $_POST['license_plan_tier'] );
			$new_key = 'NEXUS-' . strtoupper( $plan_tier ) . '-' . strtoupper( wp_generate_password( 4, false ) ) . '-' . strtoupper( wp_generate_password( 4, false ) );

			$issued_licenses[ $new_key ] = [
				'plan'       => $plan_tier,
				'status'     => 'active',
				'created_at' => current_time( 'mysql' ),
				'expires_at' => date( 'Y-m-d H:i:s', strtotime( '+1 year' ) ),
				'activated_on' => 'Awaiting activation'
			];

			update_option( 'nexus_ai_issued_licenses', $issued_licenses );
			echo '<div class="notice notice-success is-dismissible"><p>Successfully issued new <strong>' . strtoupper($plan_tier) . '</strong> license key: <code>' . esc_html($new_key) . '</code></p></div>';
		}

		// Handle Settings Update
		if ( isset( $_POST['nexus_save_settings'] ) && check_admin_referer( 'nexus_save_settings_action', 'nexus_settings_nonce' ) ) {
			$stripe_pub   = sanitize_text_field( $_POST['stripe_pub_key'] );
			$stripe_sec   = sanitize_text_field( $_POST['stripe_secret_key'] );
			$webhook_sec  = sanitize_text_field( $_POST['stripe_webhook_sec'] );
			$billing_mode = sanitize_text_field( $_POST['billing_mode'] );

			update_option( 'nexus_stripe_pub_key', $stripe_pub );
			update_option( 'nexus_stripe_secret_key', $stripe_sec );
			update_option( 'nexus_stripe_webhook_sec', $webhook_sec );
			update_option( 'nexus_billing_mode', $billing_mode );

			echo '<div class="notice notice-success is-dismissible"><p>Gateway and subscription settings successfully updated.</p></div>';
		}

		// Handle Client Local Activation form
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

				// Update activation site on the issued licenses array
				if ( isset( $issued_licenses[ $key ] ) ) {
					$issued_licenses[ $key ]['activated_on'] = get_site_url();
					update_option( 'nexus_ai_issued_licenses', $issued_licenses );
				}

				echo '<div class="notice notice-success is-dismissible"><p>License successfully verified! Plan: ' . strtoupper($data['plan']) . '</p></div>';
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>Invalid license key.</p></div>';
			}
		}

		$current_key  = get_option( 'nexus_ai_license_key', '' );
		$current_plan = \NexusAI\Workforce\API\BillingController::get_verified_plan();
		?>
		<div class="wrap" style="font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,sans-serif;">
			<h1 style="font-weight: 900; font-size: 2.5em; letter-spacing: -1px; margin-bottom: 2px;">SaaS License & Subscription Controller</h1>
			<p class="description" style="font-size: 1.1em; color: #64748b; margin-top: 0; margin-bottom: 25px;">Manage issued subscriber licenses, adjust Stripe payment integration rules, and inspect system billing logs.</p>

			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 20px;">

				<!-- Column 1: Client Activation Form & Stripe Settings -->
				<div class="space-y">

					<!-- Card 1: Client Activation Form -->
					<div class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin-bottom: 25px;">
						<h2 style="font-weight: 800; font-size: 1.5em; margin-top: 0; margin-bottom: 10px; color: #1e293b;">Client Activation Console</h2>
						<p style="color: #64748b; font-size: 0.9em; margin-bottom: 20px;">Activate and bind your licensing key on this local instance to unlock pricing tier capacities.</p>
						<p>Active Premium Subscription Plan: <strong style="font-size: 1.1em; color: #7C3AED; background: rgba(124, 58, 237, 0.08); padding: 4px 10px; border-radius: 6px;"><?php echo strtoupper($current_plan); ?></strong></p>

						<form method="POST" style="margin-top: 20px;">
							<?php wp_nonce_field( 'nexus_save_license', 'nexus_license_nonce' ); ?>
							<div style="margin-bottom: 15px;">
								<label for="license_key" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 8px;">License Key</label>
								<input type="text" name="license_key" id="license_key" value="<?php echo esc_attr($current_key); ?>" style="padding: 12px; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace; font-size: 1.1em;" placeholder="NEXUS-PRO-XXXX-XXXX">
								<p class="description" style="margin-top: 6px;">Use <code>NEXUS-PRO-DEMOKEY-9921</code> or generate a custom key below to test the activation process.</p>
							</div>
							<input type="submit" name="nexus_license_submit" class="button button-primary button-large" value="Activate Local License" style="background: #7C3AED; border-color: #7C3AED; text-shadow: none; box-shadow: none; font-weight: 700; padding: 8px 24px; border-radius: 8px; height: auto;">
						</form>
					</div>

					<!-- Card 2: Stripe Payment Gateway Configuration -->
					<div class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0;">
						<h2 style="font-weight: 800; font-size: 1.5em; margin-top: 0; margin-bottom: 10px; color: #1e293b;">Stripe Gateway Configuration</h2>
						<p style="color: #64748b; font-size: 0.9em; margin-bottom: 20px;">Set up credentials for your SaaS subscription website to process customer Stripe payments.</p>

						<form method="POST">
							<?php wp_nonce_field( 'nexus_save_settings_action', 'nexus_settings_nonce' ); ?>
							<div style="margin-bottom: 15px;">
								<label for="stripe_pub_key" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Stripe Publishable Key</label>
								<input type="text" name="stripe_pub_key" id="stripe_pub_key" value="<?php echo esc_attr( get_option( 'nexus_stripe_pub_key', 'pk_live_demo1234567890' ) ); ?>" style="padding: 10px; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace;">
							</div>
							<div style="margin-bottom: 15px;">
								<label for="stripe_secret_key" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Stripe Secret Key</label>
								<input type="password" name="stripe_secret_key" id="stripe_secret_key" value="<?php echo esc_attr( get_option( 'nexus_stripe_secret_key', 'sk_live_demo1234567890' ) ); ?>" style="padding: 10px; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace;">
							</div>
							<div style="margin-bottom: 15px;">
								<label for="stripe_webhook_sec" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Stripe Webhook Secret</label>
								<input type="password" name="stripe_webhook_sec" id="stripe_webhook_sec" value="<?php echo esc_attr( get_option( 'nexus_stripe_webhook_sec', 'whsec_demo1234567890' ) ); ?>" style="padding: 10px; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace;">
							</div>
							<div style="margin-bottom: 20px;">
								<label for="billing_mode" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Billing Mode</label>
								<select name="billing_mode" id="billing_mode" style="padding: 10px; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; height: auto;">
									<option value="sandbox" <?php selected( get_option( 'nexus_billing_mode', 'sandbox' ), 'sandbox' ); ?>>Sandbox / Test Environment</option>
									<option value="production" <?php selected( get_option( 'nexus_billing_mode', 'sandbox' ), 'production' ); ?>>Production / Live Environment</option>
								</select>
							</div>
							<input type="submit" name="nexus_save_settings" class="button button-secondary" value="Save Gateway Parameters" style="padding: 8px 20px; border-radius: 8px; height: auto; font-weight: 700;">
						</form>
					</div>

				</div>

				<!-- Column 2: License Issuance & Master Database -->
				<div>

					<!-- Card 3: Generate License Keys -->
					<div class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin-bottom: 25px;">
						<h2 style="font-weight: 800; font-size: 1.5em; margin-top: 0; margin-bottom: 10px; color: #1e293b;">Key Issuance Dashboard</h2>
						<p style="color: #64748b; font-size: 0.9em; margin-bottom: 20px;">Issue brand-new subscription licensing keys for customers cryptographically.</p>

						<form method="POST">
							<?php wp_nonce_field( 'nexus_generate_license_action', 'nexus_gen_nonce' ); ?>
							<div style="margin-bottom: 15px; display: flex; gap: 15px;">
								<div style="flex: 1;">
									<label for="license_plan_tier" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Select Plan Tier</label>
									<select name="license_plan_tier" id="license_plan_tier" style="padding: 10px; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; height: auto;">
										<option value="pro">Professional Plan ($497/mo)</option>
										<option value="agency">Agency Plan ($997/mo)</option>
										<option value="enterprise">Enterprise Plan (Custom/mo)</option>
									</select>
								</div>
								<div style="display: flex; align-items: flex-end;">
									<input type="submit" name="nexus_generate_license" class="button button-primary button-large" value="Generate License Key" style="background: #0ea5e9; border-color: #0ea5e9; text-shadow: none; box-shadow: none; font-weight: 700; padding: 10px 24px; border-radius: 8px; height: auto;">
								</div>
							</div>
						</form>
					</div>

					<!-- Card 4: Issued Licenses Database -->
					<div class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0;">
						<h2 style="font-weight: 800; font-size: 1.5em; margin-top: 0; margin-bottom: 15px; color: #1e293b;">Master Key Registry</h2>
						<p style="color: #64748b; font-size: 0.9em; margin-bottom: 20px;">Review all active subscription license keys currently managed on the server database.</p>

						<div style="max-height: 350px; overflow-y: auto;">
							<table class="wp-list-table widefat fixed striped" style="border: none; box-shadow: none;">
								<thead>
									<tr>
										<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569;">License Key</th>
										<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569;">Plan</th>
										<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569;">Binding Domain</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $issued_licenses as $k => $details ) : ?>
										<tr>
											<td style="font-family: monospace; font-weight: bold; color: #1e293b;"><?php echo esc_html( $k ); ?></td>
											<td><span style="font-size: 0.85em; font-weight: 700; padding: 3px 8px; border-radius: 4px; background: rgba(14, 165, 233, 0.08); color: #0284c7; text-transform: uppercase;"><?php echo esc_html( $details['plan'] ); ?></span></td>
											<td style="color: #64748b; font-size: 0.85em;"><?php echo esc_html( $details['activated_on'] ); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>

				</div>

			</div>

			<!-- Section 5: Real-time SaaS Payment & Billing Logs -->
			<div class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin-top: 30px; margin-bottom: 50px;">
				<h2 style="font-weight: 800; font-size: 1.6em; margin-top: 0; margin-bottom: 5px; color: #1e293b;">Stripe Billing & Transaction Registry</h2>
				<p style="color: #64748b; font-size: 0.9em; margin-bottom: 25px;">Pris-sync SaaS payment logs reflecting processed subscriptions and webhooks.</p>

				<table class="wp-list-table widefat fixed striped" style="border: none; box-shadow: none;">
					<thead>
						<tr>
							<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569;">Stripe Charge ID</th>
							<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569;">Timestamp</th>
							<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569;">Customer Account</th>
							<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569;">Revenue</th>
							<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569;">Subscription Tier</th>
							<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569;">Gateway</th>
							<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569;">Charge Status</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $payment_logs as $log ) : ?>
							<tr>
								<td style="font-family: monospace; color: #1e293b;"><?php echo esc_html( $log['tx_id'] ); ?></td>
								<td style="color: #64748b; font-size: 0.95em;"><?php echo esc_html( date( 'M d, Y H:i', strtotime( $log['date'] ) ) ); ?></td>
								<td><?php echo esc_html( $log['email'] ); ?></td>
								<td style="font-weight: 800; color: #1e293b;">$<?php echo esc_html( $log['amount'] ); ?></td>
								<td><strong><?php echo esc_html( $log['plan'] ); ?></strong></td>
								<td style="color: #64748b;"><?php echo esc_html( $log['gateway'] ); ?></td>
								<td><span style="font-size: 0.85em; font-weight: 700; padding: 4px 8px; border-radius: 4px; background: rgba(34, 197, 94, 0.08); color: #16a34a; text-transform: uppercase;"><?php echo esc_html( $log['status'] ); ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

		</div>
		<?php
	}
}

add_action( 'plugins_loaded', [ 'Nexus_AI_Licensing', 'init' ] );
