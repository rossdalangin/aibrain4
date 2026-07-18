<?php
/**
 * Plugin Name: Nexus AI Workforce
 * Plugin URI:  https://nexusai.io
 * Description: An enterprise-grade AI Workforce Platform for WordPress. Hire specialized AI employees, build an AI brain for your company, and automate complex workflows.
 * Version:     1.0.0
 * Author:      Nexus AI Team
 * Author URI:  https://nexusai.io
 * License:     GPL2
 * Text Domain: nexus-ai-workforce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Register Autoloader (PSR-4)
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
} else {
	// Fallback autoloader for basic development without composer
	spl_autoload_register( function ( $class ) {
		$prefix = 'NexusAI\\Workforce\\';
		$base_dir = __DIR__ . '/src/';

		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require $file;
		}
	} );
}

/**
 * Initialize the plugin
 */
function nexus_ai_workforce_init() {
	// Initialize Core Plugin Class
	if ( class_exists( 'NexusAI\\Workforce\\Core\\Plugin' ) ) {
		\NexusAI\Workforce\Core\Plugin::instance()->init();
	}
}
add_action( 'plugins_loaded', 'nexus_ai_workforce_init' );

/**
 * Activation Hook
 */
register_activation_hook( __FILE__, function() {
	if ( class_exists( 'NexusAI\\Workforce\\Database\\Migration' ) ) {
		\NexusAI\Workforce\Database\Migration::run();
	}
	flush_rewrite_rules();
} );

/**
 * Deactivation Hook
 */
register_deactivation_hook( __FILE__, function() {
	flush_rewrite_rules();
} );
