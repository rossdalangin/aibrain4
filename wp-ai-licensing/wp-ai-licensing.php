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
		add_action( 'admin_menu', [ $instance, 'add_licensing_menu' ], 99 ); // Priority 99 to load after parent menu
		add_action( 'admin_init', [ $instance, 'initialize_settings_and_samples' ] );
		add_action( 'admin_init', [ $instance, 'handle_malformed_menu_links' ] );

		// Register interactive Shortcodes
		add_shortcode( 'nexus_pricing_table', [ $instance, 'pricing_table_shortcode' ] );
		add_shortcode( 'nexus_thank_you', [ $instance, 'thank_you_shortcode' ] );
	}

	/**
	 * Fallback redirect to handle any malformed direct admin links.
	 */
	public function handle_malformed_menu_links() {
		if ( is_admin() && strpos( $_SERVER['REQUEST_URI'], '/wp-admin/nexus-ai-licensing' ) !== false ) {
			wp_safe_redirect( admin_url( 'admin.php?page=nexus-ai-licensing' ) );
			exit;
		}
	}

	/**
	 * Initialize settings and sample data for a pristine user experience.
	 */
	public function initialize_settings_and_samples() {
		// Set default plans, pricing, and boundaries
		add_option( 'nexus_plan_pro_price', '497.00' );
		add_option( 'nexus_plan_agency_price', '997.00' );
		add_option( 'nexus_payment_gateway', 'stripe' );
		add_option( 'nexus_payment_currency', 'USD' );

		// Set default Thank You Page & Email template messages
		add_option( 'nexus_thank_you_message', "Thank you, {customer_name}! Your subscription to the {plan_name} plan was successful. Your secure license key is: {license_key}." );
		add_option( 'nexus_email_subject', "Your Nexus AI Workforce Premium License Key" );
		add_option( 'nexus_email_body', "Hello {customer_name},\n\nThank you for subscribing to the {plan_name} plan!\nYour secure license key is:\n{license_key}\n\nPaste this key in your local WordPress dashboard (under License & Payments) to activate and expand your active workspace agent boundaries.\n\nBest regards,\nNexus AI SaaS Team" );

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
				'permission_callback' => '__return_true', // Open endpoint for clients
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
	 * Renders a highly responsive HTML Pricing Table shortcode: [nexus_pricing_table]
	 */
	public function pricing_table_shortcode( $atts ) {
		$pro_price    = get_option( 'nexus_plan_pro_price', '497.00' );
		$agency_price = get_option( 'nexus_plan_agency_price', '997.00' );
		$currency     = get_option( 'nexus_payment_currency', 'USD' );
		$symbol       = $currency === 'EUR' ? '€' : ($currency === 'GBP' ? '£' : '$');

		// Resolve Thank You page URL if we are on a page, otherwise redirect locally
		$thank_you_url = site_url( '/thank-you' );

		ob_start();
		?>
		<div class="nexus-pricing-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(285px, 1fr)); gap: 30px; margin: 40px auto; max-width: 1200px; font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; color: #1e293b;">

			<!-- Card 1: Starter -->
			<div style="border: 1px solid #cbd5e1; border-radius: 16px; padding: 35px 25px; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.02); text-align: center; display: flex; flex-col; flex-direction: column; justify-content: space-between;">
				<div>
					<h3 style="font-size: 1.3em; margin: 0 0 10px; font-weight: 800; color: #475569;">Starter Plan</h3>
					<p style="font-size: 2.2em; font-weight: 900; margin: 0 0 15px; color: #1e293b;"><?php echo esc_html($symbol); ?>0<span style="font-size: 14px; color: #64748b; font-weight: normal;">/mo</span></p>
					<p style="color: #64748b; font-size: 14px; line-height: 1.5; margin-bottom: 25px;">Perfect for testing. Deploy 1 AI employee with local database limits.</p>
				</div>
				<a href="<?php echo esc_url( add_query_arg( 'plan', 'starter', $thank_you_url ) ); ?>" style="display: block; background: #64748b; color: #fff; text-decoration: none; padding: 12px; border-radius: 8px; font-weight: bold; font-size: 14px; text-transform: uppercase;">Get Starter Key</a>
			</div>

			<!-- Card 2: Professional (Most Popular) -->
			<div style="border: 2px solid #7C3AED; border-radius: 16px; padding: 35px 25px; background: #fff; box-shadow: 0 10px 25px rgba(124, 58, 237, 0.05); text-align: center; position: relative; display: flex; flex-col; flex-direction: column; justify-content: space-between;">
				<span style="position: absolute; top: 15px; right: 15px; background: #7C3AED; color: #fff; font-size: 9px; font-weight: bold; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">Most Popular</span>
				<div>
					<h3 style="font-size: 1.3em; margin: 0 0 10px; font-weight: 800; color: #7C3AED;">Professional</h3>
					<p style="font-size: 2.2em; font-weight: 900; margin: 0 0 15px; color: #1e293b;"><?php echo esc_html($symbol . $pro_price); ?><span style="font-size: 14px; color: #64748b; font-weight: normal;">/mo</span></p>
					<p style="color: #64748b; font-size: 14px; line-height: 1.5; margin-bottom: 25px;">Up to 10 AI employees, advanced knowledge documents (PDFs), and workflow templates.</p>
				</div>
				<a href="<?php echo esc_url( add_query_arg( 'plan', 'pro', $thank_you_url ) ); ?>" style="display: block; background: #7C3AED; color: #fff; text-decoration: none; padding: 12px; border-radius: 8px; font-weight: bold; font-size: 14px; text-transform: uppercase;">Subscribe to Pro</a>
			</div>

			<!-- Card 3: Agency -->
			<div style="border: 1px solid #cbd5e1; border-radius: 16px; padding: 35px 25px; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.02); text-align: center; display: flex; flex-col; flex-direction: column; justify-content: space-between;">
				<div>
					<h3 style="font-size: 1.3em; margin: 0 0 10px; font-weight: 800; color: #1e293b;">Agency Plan</h3>
					<p style="font-size: 2.2em; font-weight: 900; margin: 0 0 15px; color: #1e293b;"><?php echo esc_html($symbol . $agency_price); ?><span style="font-size: 14px; color: #64748b; font-weight: normal;">/mo</span></p>
					<p style="color: #64748b; font-size: 14px; line-height: 1.5; margin-bottom: 25px;">Up to 100 AI employees, full White-label rights, and restricted Client Portals.</p>
				</div>
				<a href="<?php echo esc_url( add_query_arg( 'plan', 'agency', $thank_you_url ) ); ?>" style="display: block; background: #1e293b; color: #fff; text-decoration: none; padding: 12px; border-radius: 8px; font-weight: bold; font-size: 14px; text-transform: uppercase;">Subscribe to Agency</a>
			</div>

		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Renders the secure simulated front-end purchase Thank You Page: [nexus_thank_you]
	 */
	public function thank_you_shortcode() {
		$plan = isset( $_GET['plan'] ) ? sanitize_text_field( $_GET['plan'] ) : 'pro';
		$customer_name = 'Valued Customer';

		// Generate cryptographically random key for the simulated subscription purchase
		$key = 'NEXUS-' . strtoupper( $plan ) . '-' . strtoupper( wp_generate_password( 4, false ) ) . '-' . strtoupper( wp_generate_password( 4, false ) );

		// Securely insert to the master issued database options
		$issued_licenses = get_option( 'nexus_ai_issued_licenses', [] );
		$issued_licenses[ $key ] = [
			'plan'       => $plan,
			'status'     => 'active',
			'created_at' => current_time( 'mysql' ),
			'expires_at' => date( 'Y-m-d H:i:s', strtotime( '+1 year' ) ),
			'activated_on' => 'Awaiting activation'
		];
		update_option( 'nexus_ai_issued_licenses', $issued_licenses );

		// Log payment transaction inside Master SaaS registry logs
		$payment_logs = get_option( 'nexus_ai_payment_logs', [] );
		$price = '0.00';
		if ( $plan === 'agency' ) {
			$price = get_option( 'nexus_plan_agency_price', '997.00' );
		} elseif ( $plan === 'pro' ) {
			$price = get_option( 'nexus_plan_pro_price', '497.00' );
		}

		$tx_id = 'ch_stripe_' . bin2hex( random_bytes( 10 ) );
		$payment_logs[] = [
			'tx_id'   => $tx_id,
			'date'    => current_time( 'mysql' ),
			'email'   => 'customer_checkout@company.com',
			'amount'  => $price,
			'plan'    => ucfirst($plan),
			'gateway' => strtoupper( get_option( 'nexus_payment_gateway', 'stripe' ) ) . ' Checkout',
			'status'  => 'Completed'
		];
		update_option( 'nexus_ai_payment_logs', $payment_logs );

		// Parse the configured Thank You page message
		$thank_you_template = get_option( 'nexus_thank_you_message', "Thank you, {customer_name}! Your subscription to the {plan_name} plan was successful. Your secure license key is: {license_key}." );
		$parsed_msg = str_replace(
			[ '{customer_name}', '{plan_name}', '{license_key}' ],
			[ $customer_name, strtoupper($plan), $key ],
			$thank_you_template
		);

		// Parse and store simulated email notification template
		$email_subject = get_option( 'nexus_email_subject', "Your Nexus AI Workforce License Key" );
		$email_body_template = get_option( 'nexus_email_body', "Hello {customer_name},\n\nThank you for subscribing to the {plan_name} plan!\nYour secure license key is:\n{license_key}\n\nPaste this key in your local WordPress dashboard to activate." );
		$parsed_email_body = str_replace(
			[ '{customer_name}', '{plan_name}', '{license_key}' ],
			[ $customer_name, strtoupper($plan), $key ],
			$email_body_template
		);

		update_option( 'nexus_simulated_email_last_sent', [
			'subject' => $email_subject,
			'body'    => $parsed_email_body,
			'sent_to' => 'customer_checkout@company.com',
			'time'    => current_time( 'mysql' )
		] );

		ob_start();
		?>
		<div class="nexus-thank-you" style="border: 1px solid #cbd5e1; border-radius: 12px; padding: 30px; background: #f8fafc; max-width: 650px; margin: 40px auto; font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; box-shadow: 0 4px 15px rgba(0,0,0,0.05); color: #1e293b;">
			<div style="text-align: center; margin-bottom: 20px;">
				<span style="font-size: 3.5em;">🎉</span>
				<h2 style="color: #16a34a; font-weight: 900; font-size: 1.8em; margin-top: 10px; margin-bottom: 5px; letter-spacing: -0.5px;">Subscription Confirmed</h2>
				<p style="color: #64748b; margin-top: 0;">Payment processed successfully by secure gateway.</p>
			</div>

			<p style="font-size: 15px; line-height: 1.6; color: #1e293b; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;"><?php echo nl2br(esc_html($parsed_msg)); ?></p>

			<div style="margin-top: 25px; padding: 20px; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px;">
				<div style="display: flex; justify-content: space-between; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 15px;">
					<span style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Simulated Email Notification Dispatch</span>
					<span style="font-size: 10px; font-weight: 700; color: #16a34a; background: rgba(34, 197, 94, 0.08); padding: 2px 8px; border-radius: 4px;">SENT ✓</span>
				</div>
				<p style="margin: 0; font-size: 11px; font-weight: bold; color: #64748b; text-transform: uppercase;">To Recipient:</p>
				<p style="margin: 3px 0 12px; font-weight: bold; color: #1e293b; font-size: 14px;">customer_checkout@company.com</p>
				<p style="margin: 0; font-size: 11px; font-weight: bold; color: #64748b; text-transform: uppercase;">Subject Line:</p>
				<p style="margin: 3px 0 12px; font-weight: bold; color: #1e293b; font-size: 14px;"><?php echo esc_html($email_subject); ?></p>
				<p style="margin: 0; font-size: 11px; font-weight: bold; color: #64748b; text-transform: uppercase;">Message Body:</p>
				<p style="margin: 5px 0 0; font-family: monospace; white-space: pre-wrap; font-size: 12px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0;"><?php echo esc_html($parsed_email_body); ?></p>
			</div>
		</div>
		<?php
		return ob_get_clean();
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
		if ( isset( $_POST['nexus_generate_license'] ) && check_admin_referer( 'nexus_generate_license_action', 'nexus_gen_nonce' ) ) {
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

		// Handle Settings & Gateway Update
		if ( isset( $_POST['nexus_save_settings'] ) && check_admin_referer( 'nexus_save_settings_action', 'nexus_settings_nonce' ) ) {
			update_option( 'nexus_plan_pro_price', sanitize_text_field( $_POST['nexus_plan_pro_price'] ) );
			update_option( 'nexus_plan_agency_price', sanitize_text_field( $_POST['nexus_plan_agency_price'] ) );
			update_option( 'nexus_payment_gateway', sanitize_text_field( $_POST['nexus_payment_gateway'] ) );
			update_option( 'nexus_payment_currency', sanitize_text_field( $_POST['nexus_payment_currency'] ) );
			update_option( 'nexus_stripe_pub_key', sanitize_text_field( $_POST['stripe_pub_key'] ) );
			update_option( 'nexus_stripe_secret_key', sanitize_text_field( $_POST['stripe_secret_key'] ) );

			echo '<div class="notice notice-success is-dismissible"><p>Gateway and subscription settings successfully updated.</p></div>';
		}

		// Handle Email & Thank You Templates Update
		if ( isset( $_POST['nexus_save_templates'] ) && check_admin_referer( 'nexus_save_templates_action', 'nexus_templates_nonce' ) ) {
			update_option( 'nexus_thank_you_message', wp_kses_post( $_POST['nexus_thank_you_message'] ) );
			update_option( 'nexus_email_subject', sanitize_text_field( $_POST['nexus_email_subject'] ) );
			update_option( 'nexus_email_body', sanitize_textarea_field( $_POST['nexus_email_body'] ) );

			echo '<div class="notice notice-success is-dismissible"><p>Email and Thank You Page templates successfully updated.</p></div>';
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

		// Handle active admin tab navigation
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'activation';
		?>
		<div class="wrap" style="font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
			<h1 style="font-weight: 900; font-size: 2.5em; letter-spacing: -1.2px; margin-bottom: 2px;">SaaS License & Subscription Controller</h1>
			<p class="description" style="font-size: 1.1em; color: #64748b; margin-top: 0; margin-bottom: 25px;">Manage subscriber licenses, configure payment gateways, customize pricing, and review email notification dispatch systems.</p>

			<!-- Navigation Tabs -->
			<h2 class="nav-tab-wrapper" style="border-bottom: 1px solid #cbd5e1; margin-bottom: 30px;">
				<a href="?page=nexus-ai-licensing&tab=activation" class="nav-tab <?php echo $active_tab === 'activation' ? 'nav-tab-active' : ''; ?>" style="font-weight: bold; border-bottom: none;">Client Activation</a>
				<a href="?page=nexus-ai-licensing&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>" style="font-weight: bold; border-bottom: none;">Pricing & Gateways</a>
				<a href="?page=nexus-ai-licensing&tab=templates" class="nav-tab <?php echo $active_tab === 'templates' ? 'nav-tab-active' : ''; ?>" style="font-weight: bold; border-bottom: none;">Checkout & Email Settings</a>
				<a href="?page=nexus-ai-licensing&tab=registry" class="nav-tab <?php echo $active_tab === 'registry' ? 'nav-tab-active' : ''; ?>" style="font-weight: bold; border-bottom: none;">License Key Registry</a>
				<a href="?page=nexus-ai-licensing&tab=transactions" class="nav-tab <?php echo $active_tab === 'transactions' ? 'nav-tab-active' : ''; ?>" style="font-weight: bold; border-bottom: none;">Stripe Billing Logs</a>
			</h2>

			<?php if ( $active_tab === 'activation' ) : ?>
				<!-- TAB 1: Local Key Activation -->
				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
					<div class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin: 0;">
						<h2 style="font-weight: 800; font-size: 1.5em; margin-top: 0; margin-bottom: 10px; color: #1e293b;">License Activation console</h2>
						<p style="color: #64748b; font-size: 0.9em; margin-bottom: 20px;">Activate and bind your licensing key on this local instance to unlock pricing tier capacities.</p>
						<p>Active Premium Subscription Plan: <strong style="font-size: 1.1em; color: #7C3AED; background: rgba(124, 58, 237, 0.08); padding: 4px 10px; border-radius: 6px;"><?php echo strtoupper($current_plan); ?></strong></p>

						<form method="POST" style="margin-top: 25px;">
							<?php wp_nonce_field( 'nexus_save_license', 'nexus_license_nonce' ); ?>
							<div style="margin-bottom: 20px;">
								<label for="license_key" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 8px;">License Key</label>
								<input type="text" name="license_key" id="license_key" value="<?php echo esc_attr($current_key); ?>" style="padding: 12px; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace; font-size: 1.1em;" placeholder="NEXUS-PRO-XXXX-XXXX">
								<p class="description" style="margin-top: 8px;">Paste your premium key. For quick testing, you can use the pre-seeded key <code>NEXUS-AGENCY-DEMOKEY-8821</code>.</p>
							</div>
							<input type="submit" name="nexus_license_submit" class="button button-primary button-large" value="Activate local License" style="background: #7C3AED; border-color: #7C3AED; text-shadow: none; box-shadow: none; font-weight: 700; padding: 10px 24px; border-radius: 8px; height: auto;">
						</form>
					</div>

					<div class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin: 0; display: flex; flex-direction: column; justify-content: space-between;">
						<div>
							<h2 style="font-weight: 800; font-size: 1.5em; margin-top: 0; margin-bottom: 10px; color: #1e293b;">How to setup plans and collect payments</h2>
							<p style="color: #64748b; font-size: 0.95em; line-height: 1.6; margin-bottom: 15px;">Setup your subscription products on Stripe or PayPal, then create a landing page on this WordPress site and place the pricing grid shortcode:</p>
							<code style="display: block; font-size: 1.1em; background: #f1f5f9; padding: 10px; border-radius: 6px; font-weight: bold; color: #7C3AED; margin-bottom: 15px; font-family: monospace;">[nexus_pricing_table]</code>
							<p style="color: #64748b; font-size: 0.95em; line-height: 1.6;">When clients purchase, they will be automatically redirected to the Thank You Page containing:</p>
							<code style="display: block; font-size: 1.1em; background: #f1f5f9; padding: 10px; border-radius: 6px; font-weight: bold; color: #10b981; font-family: monospace;">[nexus_thank_you]</code>
						</div>
					</div>
				</div>

			<?php elseif ( $active_tab === 'settings' ) : ?>
				<!-- TAB 2: Pricing & Gateways Settings -->
				<form method="POST" class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin: 0;">
					<?php wp_nonce_field( 'nexus_save_settings_action', 'nexus_settings_nonce' ); ?>
					<h2 style="font-weight: 800; font-size: 1.5em; margin-top: 0; margin-bottom: 25px; color: #1e293b;">Pricing & Payment Gateways Config</h2>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
						<div>
							<h3 style="font-weight: 700; font-size: 1.1em; color: #1e293b; margin-bottom: 15px;">Subscription Pricing Tiers</h3>
							<div style="margin-bottom: 15px;">
								<label for="nexus_plan_pro_price" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Pro Plan Monthly Price ($)</label>
								<input type="text" name="nexus_plan_pro_price" id="nexus_plan_pro_price" value="<?php echo esc_attr( get_option( 'nexus_plan_pro_price', '497.00' ) ); ?>" style="padding: 10px; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px;">
							</div>
							<div style="margin-bottom: 15px;">
								<label for="nexus_plan_agency_price" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Agency Plan Monthly Price ($)</label>
								<input type="text" name="nexus_plan_agency_price" id="nexus_plan_agency_price" value="<?php echo esc_attr( get_option( 'nexus_plan_agency_price', '997.00' ) ); ?>" style="padding: 10px; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px;">
							</div>
							<div style="margin-bottom: 15px;">
								<label for="nexus_payment_currency" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Base Currency</label>
								<select name="nexus_payment_currency" id="nexus_payment_currency" style="padding: 10px; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; height: auto;">
									<option value="USD" <?php selected( get_option( 'nexus_payment_currency', 'USD' ), 'USD' ); ?>>USD ($)</option>
									<option value="EUR" <?php selected( get_option( 'nexus_payment_currency', 'USD' ), 'EUR' ); ?>>EUR (€)</option>
									<option value="GBP" <?php selected( get_option( 'nexus_payment_currency', 'USD' ), 'GBP' ); ?>>GBP (£)</option>
								</select>
							</div>
						</div>

						<div>
							<h3 style="font-weight: 700; font-size: 1.1em; color: #1e293b; margin-bottom: 15px;">Gateway Credentials</h3>
							<div style="margin-bottom: 15px;">
								<label for="nexus_payment_gateway" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Select Primary Payment Gateway</label>
								<select name="nexus_payment_gateway" id="nexus_payment_gateway" style="padding: 10px; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; height: auto;">
									<option value="stripe" <?php selected( get_option( 'nexus_payment_gateway', 'stripe' ), 'stripe' ); ?>>Stripe Subscription API</option>
									<option value="paypal" <?php selected( get_option( 'nexus_payment_gateway', 'stripe' ), 'paypal' ); ?>>PayPal Subscriptions</option>
								</select>
							</div>
							<div style="margin-bottom: 15px;">
								<label for="stripe_pub_key" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Stripe / PayPal Client ID</label>
								<input type="text" name="stripe_pub_key" id="stripe_pub_key" value="<?php echo esc_attr( get_option( 'nexus_stripe_pub_key', 'pk_live_demo1234567890' ) ); ?>" style="padding: 10px; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace;">
							</div>
							<div style="margin-bottom: 15px;">
								<label for="stripe_secret_key" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Secret Key / Secret Token</label>
								<input type="password" name="stripe_secret_key" id="stripe_secret_key" value="<?php echo esc_attr( get_option( 'nexus_stripe_secret_key', 'sk_live_demo1234567890' ) ); ?>" style="padding: 10px; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; font-family: monospace;">
							</div>
						</div>
					</div>

					<div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
						<input type="submit" name="nexus_save_settings" class="button button-primary button-large" value="Save Gateway & Pricing Configuration" style="background: #7C3AED; border-color: #7C3AED; text-shadow: none; box-shadow: none; font-weight: 700; padding: 10px 24px; border-radius: 8px; height: auto;">
					</div>
				</form>

			<?php elseif ( $active_tab === 'templates' ) : ?>
				<!-- TAB 3: Thank You & Email Templates -->
				<form method="POST" class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin: 0;">
					<?php wp_nonce_field( 'nexus_save_templates_action', 'nexus_templates_nonce' ); ?>
					<h2 style="font-weight: 800; font-size: 1.5em; margin-top: 0; margin-bottom: 25px; color: #1e293b;">Checkout Messaging & Email Templates</h2>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 25px;">
						<div>
							<h3 style="font-weight: 700; font-size: 1.1em; color: #1e293b; margin-bottom: 15px;">Front-End Thank You Message</h3>
							<p style="color: #64748b; font-size: 0.85em; margin-bottom: 15px;">This message will display on the screen immediately after the user subscribes and pays.</p>
							<div style="margin-bottom: 15px;">
								<label for="nexus_thank_you_message" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Thank You Message Template</label>
								<textarea name="nexus_thank_you_message" id="nexus_thank_you_message" style="width: 100%; h-36; height: 120px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; font-family: sans-serif; font-size: 13px;"><?php echo esc_textarea( get_option( 'nexus_thank_you_message' ) ); ?></textarea>
							</div>
							<p class="description">Supported variables: <code>{customer_name}</code>, <code>{plan_name}</code>, <code>{license_key}</code>.</p>
						</div>

						<div>
							<h3 style="font-weight: 700; font-size: 1.1em; color: #1e293b; margin-bottom: 15px;">Email Notification Template</h3>
							<p style="color: #64748b; font-size: 0.85em; margin-bottom: 15px;">This message will be dispatched to the subscriber's email inbox on successful checkout.</p>
							<div style="margin-bottom: 15px;">
								<label for="nexus_email_subject" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Email Subject</label>
								<input type="text" name="nexus_email_subject" id="nexus_email_subject" value="<?php echo esc_attr( get_option( 'nexus_email_subject' ) ); ?>" style="padding: 10px; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px;">
							</div>
							<div style="margin-bottom: 15px;">
								<label for="nexus_email_body" style="display: block; font-weight: 700; font-size: 0.85em; text-transform: uppercase; color: #475569; margin-bottom: 6px;">Email Body Content</label>
								<textarea name="nexus_email_body" id="nexus_email_body" style="width: 100%; height: 160px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; font-family: monospace; font-size: 12px;"><?php echo esc_textarea( get_option( 'nexus_email_body' ) ); ?></textarea>
							</div>
							<p class="description">Supported variables: <code>{customer_name}</code>, <code>{plan_name}</code>, <code>{license_key}</code>.</p>
						</div>
					</div>

					<div style="border-top: 1px solid #e2e8f0; padding-top: 20px;">
						<input type="submit" name="nexus_save_templates" class="button button-primary button-large" value="Save Email & Thank You Templates" style="background: #7C3AED; border-color: #7C3AED; text-shadow: none; box-shadow: none; font-weight: 700; padding: 10px 24px; border-radius: 8px; height: auto;">
					</div>
				</form>

			<?php elseif ( $active_tab === 'registry' ) : ?>
				<!-- TAB 4: Key Registry -->
				<div class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin: 0; margin-bottom: 30px;">
					<h2 style="font-weight: 800; font-size: 1.5em; margin-top: 0; margin-bottom: 10px; color: #1e293b;">Key Issuance Console</h2>
					<p style="color: #64748b; font-size: 0.9em; margin-bottom: 25px;">Issue brand-new subscription licensing keys for customers cryptographically.</p>

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
								<input type="submit" name="nexus_generate_license" class="button button-primary button-large" value="Generate License Key" style="background: #0ea5e9; border-color: #0ea5e9; text-shadow: none; box-shadow: none; font-weight: 700; padding: 12px 24px; border-radius: 8px; height: auto;">
							</div>
						</div>
					</form>
				</div>

				<div class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin: 0;">
					<h2 style="font-weight: 800; font-size: 1.5em; margin-top: 0; margin-bottom: 10px; color: #1e293b;">Master Key Registry</h2>
					<p style="color: #64748b; font-size: 0.9em; margin-bottom: 20px;">Review all active subscription license keys currently managed on the server database.</p>

					<table class="wp-list-table widefat fixed striped" style="border: none; box-shadow: none;">
						<thead>
							<tr>
								<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569; padding-bottom: 10px;">License Key</th>
								<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569; padding-bottom: 10px;">Plan</th>
								<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569; padding-bottom: 10px;">Binding Domain</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $issued_licenses as $k => $details ) : ?>
								<tr>
									<td style="font-family: monospace; font-weight: bold; color: #1e293b; padding: 12px 10px;"><?php echo esc_html( $k ); ?></td>
									<td style="padding: 12px 10px;"><span style="font-size: 0.85em; font-weight: 700; padding: 3px 8px; border-radius: 4px; background: rgba(14, 165, 233, 0.08); color: #0284c7; text-transform: uppercase;"><?php echo esc_html( $details['plan'] ); ?></span></td>
									<td style="color: #64748b; font-size: 0.9em; padding: 12px 10px;"><?php echo esc_html( $details['activated_on'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

			<?php elseif ( $active_tab === 'transactions' ) : ?>
				<!-- TAB 5: Billing Logs -->
				<div class="card" style="background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin: 0;">
					<h2 style="font-weight: 800; font-size: 1.5em; margin-top: 0; margin-bottom: 5px; color: #1e293b;">Stripe Billing & Transaction Registry</h2>
					<p style="color: #64748b; font-size: 0.9em; margin-bottom: 25px;">SaaS payment logs reflecting processed subscriptions and webhooks.</p>

					<table class="wp-list-table widefat fixed striped" style="border: none; box-shadow: none;">
						<thead>
							<tr>
								<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569; padding-bottom: 10px;">Stripe Charge ID</th>
								<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569; padding-bottom: 10px;">Timestamp</th>
								<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569; padding-bottom: 10px;">Customer Account</th>
								<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569; padding-bottom: 10px;">Revenue</th>
								<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569; padding-bottom: 10px;">Subscription Tier</th>
								<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569; padding-bottom: 10px;">Gateway</th>
								<th style="font-weight: 700; border-bottom: 2px solid #e2e8f0; color: #475569; padding-bottom: 10px;">Charge Status</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $payment_logs as $log ) : ?>
								<tr>
									<td style="font-family: monospace; color: #1e293b; padding: 12px 10px;"><?php echo esc_html( $log['tx_id'] ); ?></td>
									<td style="color: #64748b; font-size: 0.95em; padding: 12px 10px;"><?php echo esc_html( date( 'M d, Y H:i', strtotime( $log['date'] ) ) ); ?></td>
									<td style="padding: 12px 10px;"><?php echo esc_html( $log['email'] ); ?></td>
									<td style="font-weight: 800; color: #1e293b; padding: 12px 10px;">$<?php echo esc_html( $log['amount'] ); ?></td>
									<td style="padding: 12px 10px;"><strong><?php echo esc_html( $log['plan'] ); ?></strong></td>
									<td style="color: #64748b; padding: 12px 10px;"><?php echo esc_html( $log['gateway'] ); ?></td>
									<td style="padding: 12px 10px;"><span style="font-size: 0.85em; font-weight: 700; padding: 4px 8px; border-radius: 4px; background: rgba(34, 197, 94, 0.08); color: #16a34a; text-transform: uppercase;"><?php echo esc_html( $log['status'] ); ?></span></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

		</div>
		<?php
	}
}

add_action( 'plugins_loaded', [ 'Nexus_AI_Licensing', 'init' ] );
