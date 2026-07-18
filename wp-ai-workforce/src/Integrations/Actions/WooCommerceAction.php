<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Integrations\Actions;

/**
 * Action to manage WooCommerce products and orders.
 */
class WooCommerceAction extends BaseAction {

	public function get_name(): string {
		return 'manage_woocommerce';
	}

	public function get_description(): string {
		return 'Manage WooCommerce store. Use this to create products, update stock, or check order status.';
	}

	public function get_parameters(): array {
		return [
			'type' => 'object',
			'properties' => [
				'action'     => [ 'type' => 'string', 'enum' => [ 'create_product', 'get_order', 'update_stock', 'revenue_report' ] ],
				'name'       => [ 'type' => 'string', 'description' => 'Product name' ],
				'price'      => [ 'type' => 'string' ],
				'order_id'   => [ 'type' => 'integer' ],
				'days'       => [ 'type' => 'integer', 'description' => 'Number of days for report' ],
			],
			'required' => [ 'action' ],
		];
	}

	public function execute( array $args ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			throw new \Exception( 'WooCommerce is not installed or active.' );
		}

		if ( ! current_user_can( 'view_woocommerce_reports' ) && ! current_user_can( 'manage_woocommerce' ) ) {
			throw new \Exception( 'Insufficient permissions to access WooCommerce data.' );
		}

		switch ( $args['action'] ) {
			case 'create_product':
				$product = new \WC_Product_Simple();
				$product->set_name( sanitize_text_field( $args['name'] ) );
				$product->set_regular_price( $args['price'] );
				$product->set_status( 'publish' );
				$id = $product->save();
				return [ 'success' => true, 'product_id' => $id ];

			case 'get_order':
				$order = wc_get_order( $args['order_id'] );
				return $order ? $order->get_data() : [ 'error' => 'Order not found' ];

			case 'revenue_report':
				$days = $args['days'] ?? 30;
				$reports = \WC_Admin_Reports::get_report_data( [
					'data_labels' => [ 'total_sales' ],
					'query_args'  => [
						'date_range' => 'last_month', // Simplified for MVP
					],
				] );
				return [ 'total_revenue' => $reports->total_sales ?? 'Data unavailable' ];

			default:
				return [ 'error' => 'Unsupported action' ];
		}
	}
}
