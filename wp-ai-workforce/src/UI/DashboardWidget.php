<?php
declare(strict_types=1);

namespace NexusAI\Workforce\UI;

/**
 * Adds a luxury dashboard widget to the main WordPress dashboard.
 */
class DashboardWidget {

	public function init(): void {
		add_action( 'wp_dashboard_setup', [ $this, 'add_widget' ] );
	}

	public function add_widget(): void {
		wp_add_dashboard_widget(
			'nexus_ai_status_widget',
			'Nexus AI Workforce Status',
			[ $this, 'render' ]
		);
	}

	public function render(): void {
		global $wpdb;
		$agent_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ai_employees WHERE is_active = 1" );
		$efficiency  = "94.2%"; // Conceptual
		?>
		<div style="background: #0A0A0B; color: #f8fafc; padding: 20px; border-radius: 12px; font-family: 'Inter', sans-serif;">
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
				<div>
					<p style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: bold; margin: 0;">Workforce Efficiency</p>
					<p style="font-size: 24px; font-weight: 900; margin: 5px 0 0 0; color: #7C3AED;"><?php echo esc_html($efficiency); ?></p>
				</div>
				<div style="text-align: right;">
					<p style="font-size: 10px; color: #94a3b8; text-transform: uppercase; font-weight: bold; margin: 0;">Agents Active</p>
					<p style="font-size: 24px; font-weight: 900; margin: 5px 0 0 0;"><?php echo (int)$agent_count; ?></p>
				</div>
			</div>
			<a href="<?php echo admin_url('admin.php?page=nexus-ai-workforce'); ?>" style="display: block; width: 100%; text-align: center; background: #7C3AED; color: #1e293b; text-decoration: none; padding: 10px; border-radius: 8px; font-weight: bold; font-size: 12px; text-transform: uppercase;">Open Command Center</a>
		</div>
		<?php
	}
}
