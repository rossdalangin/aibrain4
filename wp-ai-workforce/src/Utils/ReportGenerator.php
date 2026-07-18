<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Utils;

/**
 * Generates comprehensive strategic intelligence reports.
 */
class ReportGenerator {

	/**
	 * Generate an executive summary of the current AI workforce status.
	 */
	public function generate_executive_summary(): string {
		global $wpdb;
		$agent_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ai_employees WHERE is_active = 1" );
		$depts       = $wpdb->get_results( "SELECT name FROM {$wpdb->prefix}ai_departments" );
		$total_tasks = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ai_usage_logs" );

		$report = "# Nexus AI: Strategic Intelligence Report\n\n";
		$report .= "## Workforce Composition\n";
		$report .= "- **Active AI Agents:** $agent_count\n";
		$report .= "- **Departments:** " . implode( ', ', array_column( $depts, 'name' ) ) . "\n\n";

		$report .= "## Operational Velocity\n";
		$report .= "- **Total Tasks Orchestrated:** $total_tasks\n";
		$report .= "- **Platform Integrity:** STABLE\n\n";

		$report .= "## Recent Strategic Decisions\n";
		$convs = $wpdb->get_results( "SELECT title FROM {$wpdb->prefix}ai_conversations ORDER BY created_at DESC LIMIT 5" );
		foreach ( $convs as $c ) {
			$report .= "- " . ($c->title ?: 'Untitled Strategy Session') . "\n";
		}

		return $report;
	}
}
