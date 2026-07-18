<?php
declare(strict_types=1);

namespace NexusAI\Workforce\API;

use WP_REST_Request;
use WP_REST_Response;
use NexusAI\Workforce\Repositories\PlanRepository;
use NexusAI\Workforce\Repositories\SubscriptionRepository;
use NexusAI\Workforce\Repositories\CouponRepository;

/**
 * Controller for SaaS billing, plans, and coupons.
 */
class BillingController {

	private $plans;
	private $subscriptions;
	private $coupons;

	public function __construct() {
		$this->plans         = new PlanRepository();
		$this->subscriptions = new SubscriptionRepository();
		$this->coupons       = new CouponRepository();
	}

	public function get_plans( WP_REST_Request $request ): WP_REST_Response {
		return new WP_REST_Response( $this->plans->get_all(), 200 );
	}

	public function apply_coupon( WP_REST_Request $request ): WP_REST_Response {
		$code = sanitize_text_field( $request->get_param( 'code' ) );
		$coupon = $this->coupons->get_by_code( $code );

		if ( ! $coupon ) {
			return new WP_REST_Response( [ 'message' => 'Invalid or expired coupon' ], 404 );
		}

		return new WP_REST_Response( $coupon, 200 );
	}

	public function get_my_subscription( WP_REST_Request $request ): WP_REST_Response {
		$user_id = get_current_user_id();
		$sub = $this->subscriptions->get_by_user( $user_id );
		return new WP_REST_Response( $sub, 200 );
	}

	public function upgrade_plan( WP_REST_Request $request ): WP_REST_Response {
		$plan = sanitize_text_field( $request->get_param( 'plan' ) );
		update_option( 'nexus_ai_active_plan', $plan );
		return new WP_REST_Response( [ 'success' => true, 'plan' => $plan ], 200 );
	}
}
