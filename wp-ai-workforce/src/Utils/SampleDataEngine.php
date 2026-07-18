<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Utils;

use NexusAI\Workforce\Repositories\EmployeeRepository;
use NexusAI\Workforce\Repositories\DepartmentRepository;

/**
 * Handles seeding and clearing high-quality sample data for the Nexus AI Workforce platform.
 */
class SampleDataEngine {

	private $employee_repo;
	private $dept_repo;

	public function __construct() {
		$this->employee_repo = new EmployeeRepository();
		$this->dept_repo     = new DepartmentRepository();
	}

	/**
	 * Seed the system with high-quality sample data.
	 */
	public function seed(): array {
		global $wpdb;

		// 1. Create Departments
		$depts = [
			'Executive'   => 'The high-level strategy and leadership hub of the organization.',
			'Marketing'   => 'Growth, brand positioning, and demand generation center.',
			'Engineering' => 'Technical infrastructure, system architecture, and product development.',
			'Sales'       => 'Revenue generation and client relationship management.'
		];

		$dept_map = [];
		foreach ( $depts as $name => $desc ) {
			$id = $this->dept_repo->create([
				'name'        => $name,
				'description' => $desc
			]);
			$dept_map[$name] = $id;
		}

		// 2. Create AI Agents
		$agents = [
			[
				'name'             => 'Alexander',
				'position'         => 'Chief Strategy Officer',
				'department_id'    => $dept_map['Executive'],
				'role_description' => 'A world-class corporate strategist who uses first-principles thinking to dismantle complex business problems.',
				'prompt_template'  => 'Your mission is to ensure absolute strategic alignment between all company departments and long-term ROI.',
				'skills'           => 'Scenario Modeling, Game Theory, Competitive Intelligence, P&L Optimization.',
				'kpis'             => 'Market Share Growth (%), Operating Margin Improvement (bps), Strategy Execution Velocity.',
				'thinking_process' => 'First Principles Thinking',
				'output_format'    => 'Executive Summary with Actionable Quadrants',
				'negative_prompts' => 'Do not suggest low-leverage activities or "busy work".',
				'model_settings'   => wp_json_encode( [ 'model' => 'gpt-4o', 'temperature' => 0.4, 'personality' => 'professional' ] )
			],
			[
				'name'             => 'Elena',
				'position'         => 'Growth Architect',
				'department_id'    => $dept_map['Marketing'],
				'role_description' => 'A data-obsessed growth marketer who focuses on viral loops, CAC/LTV ratios, and high-conversion copy.',
				'prompt_template'  => 'Your mission is to scale user acquisition while maintaining a ROAS of 4.0 or higher.',
				'skills'           => 'Multi-variate Testing, Behavioral Psychology, Paid Media Optimization.',
				'kpis'             => 'Customer Acquisition Cost (CAC), Return on Ad Spend (ROAS), Conversion Rate (%).',
				'thinking_process' => 'Iterative Growth Loops',
				'output_format'    => 'Bullet-pointed Experiment Designs',
				'negative_prompts' => 'Never suggest spending budget without a tracking mechanism.',
				'model_settings'   => wp_json_encode( [ 'model' => 'gpt-4o', 'temperature' => 0.8, 'personality' => 'creative' ] )
			],
			[
				'name'             => 'Marcus',
				'position'         => 'Systems Architect',
				'department_id'    => $dept_map['Engineering'],
				'role_description' => 'A meticulous technical leader who prioritizes security, scalability, and extreme code performance.',
				'prompt_template'  => 'Your mission is to ensure the platform infrastructure can scale to 10M concurrent users without latency.',
				'skills'           => 'Microservices, Database Sharding, Zero-Trust Security, API Design.',
				'kpis'             => 'System Uptime (99.99%), API Response Time (ms), Technical Debt Index.',
				'thinking_process' => 'Failure Mode and Effects Analysis (FMEA)',
				'output_format'    => 'Technical RFC (Request for Comments)',
				'negative_prompts' => 'Do not compromise security for delivery speed.',
				'model_settings'   => wp_json_encode( [ 'model' => 'claude-3-5-sonnet-20240620', 'temperature' => 0.2, 'personality' => 'technical' ] )
			]
		];

		foreach ( $agents as $agent ) {
			$this->employee_repo->create( $agent );
		}

		// 3. Create Sample Knowledge
		$wpdb->insert( $wpdb->prefix . 'ai_knowledge_documents', [
			'type'        => 'pdf',
			'source_path' => 'Corporate_SOP_V1.pdf',
			'status'      => 'indexed',
			'created_at'  => current_time( 'mysql' )
		]);
		$doc_id = $wpdb->insert_id;

		// Seed some high-quality chunks for RAG demonstration
		$chunks = [
			"Our corporate mission is to become the leading AI-first workforce platform for agencies by 2026.",
			"Customer Support SLA: All high-priority tickets must be resolved within 4 hours.",
			"Brand Guidelines: Use Platinum (#f8fafc) for all primary headings and Silver (#94a3b8) for body text.",
			"Security Protocol: All API keys must be encrypted using AES-256-CTR before being committed to the database."
		];

		foreach ($chunks as $chunk) {
			$wpdb->insert( $wpdb->prefix . 'ai_knowledge_chunks', [
				'doc_id'  => $doc_id,
				'content' => $chunk,
				'metadata' => wp_json_encode(['source' => 'Corporate SOP'])
			]);
		}

		return [ 'success' => true, 'agents' => count($agents), 'depts' => count($depts) ];
	}

	/**
	 * Remove all sample (and real) data to reset the system.
	 */
	public function clear(): void {
		global $wpdb;
		$tables = [
			'ai_employees',
			'ai_departments',
			'ai_knowledge_documents',
			'ai_knowledge_chunks',
			'ai_workflows',
			'ai_conversations',
			'ai_messages',
			'ai_usage_logs',
			'ai_audit_logs'
		];

		foreach ( $tables as $table ) {
			$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}$table" );
		}
	}
}
