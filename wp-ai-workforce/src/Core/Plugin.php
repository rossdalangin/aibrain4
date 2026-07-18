<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Core;

class Plugin {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init() {
		$this->register_hooks();
		$this->init_components();
		// Ensure database columns exist (self-healing migration)
		if ( is_admin() && ! get_option( 'nexus_ai_workforce_db_migrated_v2' ) ) {
			if ( class_exists( 'NexusAI\\Workforce\\Database\\Migration' ) ) {
				\NexusAI\Workforce\Database\Migration::run();
				update_option( 'nexus_ai_workforce_db_migrated_v2', true );
			}
		}
	}

	private function register_hooks() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'admin_head', [ $this, 'inject_custom_agency_css' ] );
		add_shortcode( 'nexus_ai_portal', [ $this, 'render_client_portal_shortcode' ] );
	}

	public function render_client_portal_shortcode(): string {
		if ( ! is_user_logged_in() ) {
			return '<p class="nexus-error">Authentication Required to Access AI Workforce.</p>';
		}

		$this->enqueue_admin_assets( 'nexus-ai-workforce-portal' );

		ob_start();
		if ( class_exists( 'NexusAI\\Workforce\\UI\\AdminRenderer' ) ) {
			( new \NexusAI\Workforce\UI\AdminRenderer() )->render_overview_page();
		}
		return ob_get_clean();
	}

	public function inject_custom_agency_css(): void {
		$settings = new \NexusAI\Workforce\Repositories\SettingsRepository();
		$custom_css = $settings->get( 'custom_agency_css', '' );
		if ( ! empty( $custom_css ) ) {
			echo '<style id="nexus-agency-overrides">' . wp_strip_all_tags( $custom_css ) . '</style>';
		}
	}

	private function init_components() {
		// Initialize REST API
		if ( class_exists( 'NexusAI\\Workforce\\API\\RestHandler' ) ) {
			( new \NexusAI\Workforce\API\RestHandler() )->init();
		}

		// Initialize Frontend Widget
		if ( class_exists( 'NexusAI\\Workforce\\UI\\ChatWidget' ) ) {
			( new \NexusAI\Workforce\UI\ChatWidget() )->init();
		}

		// Initialize Dashboard Widget
		if ( class_exists( 'NexusAI\\Workforce\\UI\\DashboardWidget' ) ) {
			( new \NexusAI\Workforce\UI\DashboardWidget() )->init();
		}
	}

	public function add_admin_menu() {
		$settings_repo = new \NexusAI\Workforce\Repositories\SettingsRepository();
		$agency_mode   = (bool) $settings_repo->get( 'agency_mode', false );
		$display_title = $settings_repo->get( 'platform_title', 'Nexus AI' );

		if ( empty( $display_title ) ) {
			$display_title = 'Nexus AI';
		}

		add_menu_page(
			$display_title,
			$display_title,
			'manage_options',
			'nexus-ai-workforce',
			[ $this, 'render_overview_page' ],
			'dashicons-superhero',
			2
		);

		add_submenu_page(
			'nexus-ai-workforce',
			__( 'Collaboration', 'nexus-ai-workforce' ),
			'Collaboration Hub',
			'manage_options',
			'nexus-ai-workforce-meetings',
			[ $this, 'render_meetings_page' ]
		);

		add_submenu_page(
			'nexus-ai-workforce',
			__( 'Archive', 'nexus-ai-workforce' ),
			'Strategic Archive',
			'manage_options',
			'nexus-ai-workforce-archive',
			[ $this, 'render_archive_page' ]
		);

		add_submenu_page(
			'nexus-ai-workforce',
			__( 'Workforce', 'nexus-ai-workforce' ),
			'Hire AI Agents',
			'manage_options',
			'nexus-ai-workforce-employees',
			[ $this, 'render_workforce_page' ]
		);

		add_submenu_page(
			'nexus-ai-workforce',
			__( 'Departments', 'nexus-ai-workforce' ),
			'Org Structure',
			'manage_options',
			'nexus-ai-workforce-departments',
			[ $this, 'render_departments_page' ]
		);

		add_submenu_page(
			'nexus-ai-workforce',
			__( 'Knowledge Base', 'nexus-ai-workforce' ),
			'Company Brain',
			'manage_options',
			'nexus-ai-workforce-kb',
			[ $this, 'render_kb_page' ]
		);

		add_submenu_page(
			'nexus-ai-workforce',
			__( 'Workflows', 'nexus-ai-workforce' ),
			'Automations',
			'manage_options',
			'nexus-ai-workforce-workflows',
			[ $this, 'render_workflows_page' ]
		);

		add_submenu_page(
			'nexus-ai-workforce',
			__( 'Tutorials', 'nexus-ai-workforce' ),
			'Learning Center',
			'read',
			'nexus-ai-workforce-tutorials',
			[ $this, 'render_admin_tutorials_page' ]
		);

		add_submenu_page(
			'nexus-ai-workforce',
			__( 'Audit', 'nexus-ai-workforce' ),
			'Audit Trail',
			'manage_options',
			'nexus-ai-workforce-audit',
			[ $this, 'render_audit_page' ]
		);

		add_submenu_page(
			'nexus-ai-workforce',
			__( 'Billing', 'nexus-ai-workforce' ),
			'Plans & Billing',
			'manage_options',
			'nexus-ai-workforce-billing',
			[ $this, 'render_billing_page' ]
		);

		add_submenu_page(
			'nexus-ai-workforce',
			__( 'Settings', 'nexus-ai-workforce' ),
			'System Config',
			'manage_options',
			'nexus-ai-workforce-settings',
			[ $this, 'render_settings_page' ]
		);

		add_submenu_page(
			'nexus-ai-workforce',
			__( 'Playground', 'nexus-ai-workforce' ),
			'Agent Playground',
			'manage_options',
			'nexus-ai-workforce-playground',
			[ $this, 'render_playground_page' ]
		);
	}

	public function render_admin_page() {
		echo '<div id="nexus-ai-admin-root"></div>';
	}

	public function render_workforce_page() {
		if ( class_exists( 'NexusAI\\Workforce\\UI\\AdminRenderer' ) ) {
			( new \NexusAI\Workforce\UI\AdminRenderer() )->render_workforce_page();
		}
	}

	public function render_departments_page() {
		if ( class_exists( 'NexusAI\\Workforce\\UI\\AdminRenderer' ) ) {
			( new \NexusAI\Workforce\UI\AdminRenderer() )->render_departments_page();
		}
	}

	public function render_settings_page() {
		if ( class_exists( 'NexusAI\\Workforce\\UI\\AdminRenderer' ) ) {
			( new \NexusAI\Workforce\UI\AdminRenderer() )->render_settings_page();
		}
	}

	public function render_playground_page() {
		if ( class_exists( 'NexusAI\\Workforce\\UI\\AdminRenderer' ) ) {
			( new \NexusAI\Workforce\UI\AdminRenderer() )->render_playground_page();
		}
	}

	public function render_kb_page() {
		if ( class_exists( 'NexusAI\\Workforce\\UI\\AdminRenderer' ) ) {
			( new \NexusAI\Workforce\UI\AdminRenderer() )->render_kb_page();
		}
	}

	public function render_overview_page() {
		if ( class_exists( 'NexusAI\\Workforce\\UI\\AdminRenderer' ) ) {
			( new \NexusAI\Workforce\UI\AdminRenderer() )->render_overview_page();
		}
	}

	public function render_workflows_page() {
		if ( class_exists( 'NexusAI\\Workforce\\UI\\AdminRenderer' ) ) {
			( new \NexusAI\Workforce\UI\AdminRenderer() )->render_workflows_page();
		}
	}

	public function render_meetings_page() {
		if ( class_exists( 'NexusAI\\Workforce\\UI\\AdminRenderer' ) ) {
			( new \NexusAI\Workforce\UI\AdminRenderer() )->render_meetings_page();
		}
	}

	public function render_archive_page() {
		if ( class_exists( 'NexusAI\\Workforce\\UI\\AdminRenderer' ) ) {
			( new \NexusAI\Workforce\UI\AdminRenderer() )->render_archive_page();
		}
	}

	public function render_admin_tutorials_page() {
		if ( class_exists( 'NexusAI\\Workforce\\UI\\AdminRenderer' ) ) {
			( new \NexusAI\Workforce\UI\AdminRenderer() )->render_tutorials_page();
		}
	}

	public function render_audit_page() {
		if ( class_exists( 'NexusAI\\Workforce\\UI\\AdminRenderer' ) ) {
			( new \NexusAI\Workforce\UI\AdminRenderer() )->render_audit_page();
		}
	}

	public function render_billing_page() {
		if ( class_exists( 'NexusAI\\Workforce\\UI\\AdminRenderer' ) ) {
			( new \NexusAI\Workforce\UI\AdminRenderer() )->render_billing_page();
		}
	}

	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'nexus-ai-workforce' ) === false && $hook !== 'nexus-ai-workforce-portal' ) {
			return;
		}

		// Enqueue Tailwind and Chart.js CDNs
		wp_enqueue_script( 'nexus-ai-tailwind', 'https://cdn.tailwindcss.com', [], '3.3.0' );
		wp_enqueue_script( 'nexus-ai-chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.4.0' );

		$main_file = dirname( __FILE__, 3 ) . '/wp-ai-workforce.php';
		wp_enqueue_style( 'nexus-ai-premium', plugins_url( 'assets/css/nexus-ui.css', $main_file ), [], '1.1.0' );

		// Enqueue React build (assuming webpack output)
		if ( file_exists( dirname( __FILE__, 3 ) . '/assets/js/admin.js' ) ) {
			wp_enqueue_script( 'nexus-ai-admin', plugins_url( 'assets/js/admin.js', $main_file ), [ 'wp-element', 'wp-api-fetch' ], '1.1.0', true );
		}

		wp_enqueue_script( 'nexus-ai-bridge', plugins_url( 'assets/js/admin-bridge.js', $main_file ), [], '1.1.0', true );
		wp_localize_script( 'nexus-ai-bridge', 'nexus_ai_data', [
			'rest_url' => esc_url_raw( rest_url() ),
			'nonce'    => wp_create_nonce( 'wp_rest' ),
		] );
	}
}
