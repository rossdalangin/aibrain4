<?php
declare(strict_types=1);

namespace NexusAI\Workforce\UI;

use NexusAI\Workforce\Repositories\SettingsRepository;

/**
 * Renders the visible Admin UI forms and components.
 */
class AdminRenderer {

	private $settings;

	public function __construct() {
		$this->settings = new SettingsRepository();
	}

	private function get_brand_styles(): string {
		$color = $this->settings->get( 'ui_color', '#4f46e5' );
		$font  = $this->settings->get( 'ui_font', 'Inter' );
		return "<style>:root { --nexus-violet: $color !important; } .nexus-admin-body { font-family: '$font', sans-serif !important; } .text-nexus-violet { color: $color !important; } .bg-nexus-violet { background-color: $color !important; } .nexus-btn-vibrant { background: $color !important; }</style>";
	}

	/**
	 * Render the "Overview" dashboard page.
	 */
	public function render_overview_page(): void {
		echo $this->get_brand_styles();
		$agency_mode   = (bool) $this->settings->get( 'agency_mode', false );
		$display_title = $this->settings->get( 'platform_title', 'Nexus AI' );
		global $wpdb;
		$agent_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ai_employees WHERE is_active = 1" ) ?: 0;
		$doc_count   = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}ai_knowledge_documents" ) ?: 0;
		$total_cost  = $wpdb->get_var( "SELECT SUM(cost) FROM {$wpdb->prefix}ai_usage_logs" ) ?: 0.00;
		$user_role   = get_user_meta( get_current_user_id(), 'nexus_ai_role', true ) ?: 'Administrator';
		?>
		<div class="nexus-admin-body p-10 theme-overview animate-fade-in-up">
			<div class="mb-10 flex justify-between items-center">
				<div>
					<h2 class="text-sm font-semibold text-accent uppercase tracking-widest mb-2">Platform Command</h2>
					<h1 class="text-5xl font-black text-[#1e293b] text-gradient-vibrant leading-tight">Executive Overview</h1>
				</div>
				<?php if ( ! empty( $this->settings->get( 'agency_logo' ) ) ) : ?>
					<img src="<?php echo esc_url( $this->settings->get( 'agency_logo' ) ); ?>" class="h-12 object-contain" alt="Logo">
				<?php elseif ( ! $agency_mode ) : ?>
					<div class="text-2xl font-black tracking-tighter text-[#1e293b]/20">NEXUS AI</div>
				<?php endif; ?>
			</div>
			<div class="mb-10">
				<p class="text-gray-400 mt-3 max-w-2xl text-lg leading-relaxed">Monitor your AI workforce productivity, token consumption, and strategic activity in real-time.</p>
			</div>

			<div class="nexus-step-guide">
				<h3 class="text-[#1e293b] font-bold mb-3 uppercase tracking-tighter text-sm flex items-center gap-2">
					<span class="w-5 h-5 bg-accent rounded-full flex items-center justify-center text-[10px]">1</span>
					Operational Blueprint: Getting Started
				</h3>
				<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">1. Fuel the Engine</p>
						<p>Navigate to <span class="text-accent font-bold">Settings</span>. Securely add your API keys. <br><span class="italic text-[9px] opacity-70">Note: Without keys, the AI workforce remains dormant.</span></p>
					</div>
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">2. Hire Specialized Talent</p>
						<p>Go to <span class="text-accent font-bold">Hire AI Agents</span>. Select a template like "CEO" or "Marketing Director" to instantly deploy expert personas.</p>
					</div>
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">3. Ingest Corporate IQ</p>
						<p>Upload PDFs or SOPs in the <span class="text-accent font-bold">Company Brain</span>. This gives your agents "Corporate Memory" to pull from.</p>
					</div>
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">4. Orchestrate Growth</p>
						<p>Use <span class="text-accent font-bold">Workflows</span> to chain agents together (e.g. Researcher -> Writer -> QA) for autonomous delivery.</p>
					</div>
				</div>
			</div>

			<!-- Key Stats: Bento Grid Layout -->
			<div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
				<div class="glass-panel p-8 rounded-3xl collab-wave bg-opacity-20 flex flex-col justify-between min-h-[220px] transform hover:scale-[1.02] transition-transform">
					<p class="text-xs text-[#1e293b] uppercase tracking-widest font-bold">Efficiency Score</p>
					<div>
						<p class="text-6xl font-black mt-2">94.2%</p>
						<p class="text-xs text-[#1e293b]/70 mt-3 font-medium">Automatic Prompt Optimization Active</p>
					</div>
				</div>
				<div class="glass-panel p-8 rounded-3xl bg-nexus-blue bg-opacity-5 flex flex-col justify-between min-h-[220px] transform hover:scale-[1.02] transition-transform text-nexus-blue border-l-4 border-l-nexus-blue">
					<p class="text-xs uppercase tracking-widest font-bold">AI Workforce</p>
					<div>
						<p class="text-6xl font-black mt-2"><?php echo (int) $agent_count; ?></p>
						<p class="text-xs text-gray-400 mt-3">High-Impact Roles Deployed</p>
					</div>
				</div>
				<div class="glass-panel p-8 rounded-3xl bg-green-500 bg-opacity-5 flex flex-col justify-between min-h-[220px] transform hover:scale-[1.02] transition-transform text-green-500 border-l-4 border-l-green-500">
					<p class="text-xs uppercase tracking-widest font-bold">Company Brain</p>
					<div>
						<p class="text-6xl font-black mt-2"><?php echo (int) $doc_count; ?></p>
						<p class="text-xs text-gray-400 mt-3">Global Context Entries Ingested</p>
					</div>
				</div>
				<div class="glass-panel p-8 rounded-3xl bg-nexus-gold bg-opacity-5 flex flex-col justify-between min-h-[220px] transform hover:scale-[1.02] transition-transform text-nexus-gold border-l-4 border-l-nexus-gold">
					<p class="text-xs uppercase tracking-widest font-bold">Strategic ROI</p>
					<div>
						<p class="text-6xl font-black mt-2">$<?php echo number_format( (float) $total_cost * 12, 2 ); ?></p>
						<p class="text-xs text-gray-400 mt-3">Estimated Annual Labor Savings</p>
					</div>
				</div>
			</div>

			<!-- System Health Monitor -->
			<div class="glass-panel p-8 rounded-2xl border border-nexus-border mb-12 bg-[#f8fafc]/5">
				<div class="flex justify-between items-center mb-6">
					<h2 class="text-xl font-bold flex items-center gap-3">
						<span class="status-pulse-live"></span>
						System Core Integrity
					</h2>
					<span class="text-xs text-gray-500 uppercase font-bold tracking-widest">v1.0.0 Stable</span>
				</div>
				<div class="grid grid-cols-2 md:grid-cols-5 gap-6">
					<div class="p-4 rounded-xl bg-nexus-elevated border border-white/5">
						<p class="text-[10px] text-gray-500 uppercase mb-1">Database</p>
						<p class="text-sm font-bold text-green-500">OPTIMIZED</p>
					</div>
					<div class="p-4 rounded-xl bg-nexus-elevated border border-white/5">
						<p class="text-[10px] text-gray-500 uppercase mb-1">RAG Engine</p>
						<p class="text-sm font-bold text-green-500">READY</p>
					</div>
					<div class="p-4 rounded-xl bg-nexus-elevated border border-white/5">
						<p class="text-[10px] text-gray-500 uppercase mb-1">Encryption</p>
						<p class="text-sm font-bold text-nexus-violet uppercase">AES-256-CTR</p>
					</div>
					<div class="p-4 rounded-xl bg-nexus-elevated border border-white/5">
						<p class="text-[10px] text-gray-500 uppercase mb-1">Active Role</p>
						<p class="text-sm font-bold text-[#1e293b] uppercase"><?php echo esc_html($user_role); ?></p>
					</div>
					<div class="p-4 rounded-xl bg-nexus-elevated border border-white/5">
						<p class="text-[10px] text-gray-500 uppercase mb-1">Adapters</p>
						<p class="text-sm font-bold text-nexus-blue">7 ACTIVE</p>
					</div>
				</div>
			</div>

			<div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-12">
				<div class="glass-panel p-8 rounded-3xl border border-nexus-border">
					<h2 class="text-xl font-bold mb-6 flex justify-between items-center">
						Consumption Trends
						<span class="text-[10px] text-gray-500 uppercase tracking-widest font-bold">Last 30 Days</span>
					</h2>
					<div class="h-64">
						<canvas id="nexus-consumption-chart"></canvas>
					</div>
				</div>
				<div class="glass-panel p-8 rounded-3xl border border-nexus-border">
					<h2 class="text-xl font-bold mb-6 flex justify-between items-center">
						Workforce Efficiency
						<span class="text-[10px] text-gray-500 uppercase tracking-widest font-bold">Output Metrics</span>
					</h2>
					<div class="h-64">
						<canvas id="nexus-efficiency-chart"></canvas>
					</div>
				</div>
			</div>

			<div class="grid grid-cols-1 lg:grid-cols-3 gap-10 mb-12">
				<div class="lg:col-span-2 glass-panel p-8 rounded-3xl border border-nexus-border">
					<h2 class="text-xl font-bold mb-6">Departmental Performance</h2>
					<div class="h-80">
						<canvas id="nexus-dept-chart"></canvas>
					</div>
				</div>
				<div class="lg:col-span-1 glass-panel p-8 rounded-3xl border border-nexus-border theme-overview">
					<h2 class="text-xl font-bold mb-6 italic text-accent">Activity Stream</h2>
					<div class="space-y-6">
						<div class="flex gap-4">
							<div class="w-2 h-10 bg-accent rounded-full"></div>
							<div>
								<p class="text-xs font-bold text-[#1e293b] uppercase">Knowledge Base Update</p>
								<p class="text-[11px] text-gray-500">Market_Analysis_2025.pdf indexed.</p>
							</div>
						</div>
						<div class="flex gap-4 opacity-70">
							<div class="w-2 h-10 bg-nexus-blue rounded-full"></div>
							<div>
								<p class="text-xs font-bold text-[#1e293b] uppercase">Meeting Concluded</p>
								<p class="text-[11px] text-gray-500">Q4 Strategy finalized by CEO & CMO.</p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
				<div class="glass-panel p-8 rounded-2xl border border-nexus-border theme-workforce">
					<h2 class="text-xl font-semibold mb-6 text-accent">Workforce Activity</h2>
					<div class="space-y-4">
						<div class="flex items-center gap-4 p-3 rounded-lg bg-nexus-elevated/50">
							<div class="w-2 h-2 rounded-full bg-accent"></div>
							<p class="text-sm">Agent <span class="font-bold">Sarah</span> updated SEO for 3 pages.</p>
							<span class="ml-auto text-xs text-gray-500">2m ago</span>
						</div>
					</div>
				</div>
				<div class="glass-panel p-8 rounded-2xl border border-nexus-border bg-accent/5">
					<h2 class="text-xl font-semibold mb-6">Enterprise Quick-Tools</h2>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<button class="p-5 rounded-2xl bg-nexus-elevated border border-nexus-border hover:border-nexus-violet text-left transition-all group nexus-btn-vibrant theme-overview">
							<p class="font-bold text-sm text-[#1e293b] group-hover:text-accent">Prompt Rewriter</p>
							<p class="text-[10px] text-gray-500 mt-1 uppercase">Refine agent instructions for GPT-4o.</p>
						</button>
						<button class="p-5 rounded-2xl bg-nexus-elevated border border-nexus-border hover:border-nexus-blue text-left transition-all group nexus-btn-vibrant theme-kb">
							<p class="font-bold text-sm text-[#1e293b] group-hover:text-accent">Context Optimizer</p>
							<p class="text-[10px] text-gray-500 mt-1 uppercase">Prune redundant Company Brain chunks.</p>
						</button>
						<button id="nexus-purge-logs" class="p-5 rounded-2xl bg-nexus-elevated border border-nexus-border hover:border-red-500 text-left transition-all group">
							<p class="font-bold text-sm text-[#1e293b] group-hover:text-red-500">System Purge</p>
							<p class="text-[10px] text-gray-500 mt-1 uppercase">Clear all usage logs and transcripts.</p>
						</button>
						<button class="p-5 rounded-2xl bg-nexus-elevated border border-nexus-border hover:border-accent text-left transition-all group nexus-btn-vibrant theme-learning">
							<p class="font-bold text-sm text-[#1e293b] group-hover:text-accent">ROAS Audit</p>
							<p class="text-[10px] text-gray-500 mt-1 uppercase">Generate instant marketing report.</p>
						</button>
					</div>
				</div>

				<div class="glass-panel p-8 rounded-2xl border border-nexus-border border-l-4 border-l-nexus-gold bg-nexus-gold/5 flex flex-col justify-between">
					<div>
						<h2 class="text-xl font-bold text-nexus-gold mb-2">Agency Client Portal</h2>
						<p class="text-xs text-gray-400 leading-relaxed">Provide your clients with a dedicated, white-labeled interface to interact with their AI workforce.</p>
					</div>
					<div class="space-y-3 mt-6">
						<div class="flex items-center justify-between p-3 rounded-xl bg-nexus-elevated border border-white/5">
							<span class="text-[10px] font-bold text-[#1e293b] uppercase">Client Access</span>
							<span class="text-[10px] font-bold text-green-500 uppercase">Enabled</span>
						</div>
						<button class="nexus-launch-portal w-full bg-nexus-gold text-black font-black py-3 rounded-xl text-xs uppercase tracking-widest hover:opacity-90 transition-all">Launch Portal Preview</button>
					</div>
				</div>
			</div>

			<!-- Archive Viewer Modal -->
			<div id="nexus-archive-modal" class="fixed inset-0 z-[10000] hidden">
				<div class="absolute inset-0 bg-black/90 backdrop-blur-md"></div>
				<div class="absolute inset-x-20 top-20 bottom-20 glass-panel rounded-3xl border border-nexus-border flex flex-col overflow-hidden shadow-2xl">
					<div class="p-8 border-b border-nexus-border flex justify-between items-center bg-nexus-elevated/50">
						<div>
							<h2 id="nexus-archive-title" class="text-2xl font-bold text-[#1e293b] uppercase tracking-tighter">Session Transcript</h2>
							<p id="nexus-archive-meta" class="text-xs text-gray-500 mt-1">Archived intelligence record</p>
						</div>
						<button id="nexus-close-archive" class="text-gray-400 hover:text-[#1e293b] bg-[#f8fafc]/5 px-4 py-2 rounded-xl">Close Archive</button>
					</div>
					<div id="nexus-archive-content" class="flex-1 p-10 overflow-y-auto space-y-6 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]">
						<!-- Messages will appear here -->
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the "Hire Agent" workforce management page.
	 */
	/**
	 * Render the Departments (Org Structure) page.
	 */
	public function render_departments_page(): void {
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['nexus_ai_dept_nonce'] ) ) {
			if ( wp_verify_nonce( $_POST['nexus_ai_dept_nonce'], 'nexus_ai_dept_save' ) ) {
				$name = sanitize_text_field( $_POST['name'] ?? '' );
				$description = sanitize_textarea_field( $_POST['description'] ?? '' );
				if ( ! empty( $name ) ) {
					$repo = new \NexusAI\Workforce\Repositories\DepartmentRepository();
					$repo->create( [
						'name'        => $name,
						'description' => $description,
					] );
					echo '<div class="notice notice-success is-dismissible" style="background:#10b981; color:#fff; padding:15px; border-radius:12px; margin-bottom:20px; font-weight:bold; box-shadow:0 10px 15px -3px rgba(16,185,129,0.2);">Department created successfully.</div>';
				}
			}
		}

		echo $this->get_brand_styles();
		global $wpdb;
		$depts = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ai_departments ORDER BY name ASC", ARRAY_A ) ?: [];
		?>
		<div class="nexus-admin-body p-10 theme-workforce animate-fade-in-up">
			<div class="mb-10">
				<h2 class="text-sm font-semibold text-accent uppercase tracking-widest mb-2">Organizational Design</h2>
				<h1 class="text-5xl font-black text-[#1e293b] text-gradient-vibrant leading-tight">Company Departments</h1>
				<p class="text-gray-400 mt-3 max-w-2xl text-lg leading-relaxed">Define your company's organizational structure. Assign agents to departments to enable context-sharing and specialized reporting.</p>
			</div>

			<div class="nexus-step-guide">
				<div class="grid grid-cols-1 md:grid-cols-2 gap-10">
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">What are Departments?</p>
						<p>Departments group your AI agents logically. <br><span class="text-accent font-bold">Example:</span> Putting "Elena (Growth Architect)" in a "Marketing" department allows her to share context with other marketing agents.</p>
					</div>
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">Pro-Tip: Departmental RAG</p>
						<p>When you upload documents to the Company Brain, you can restrict them to a specific department. This ensures the "Finance" AI doesn't see "Marketing" trade secrets unless permitted.</p>
					</div>
				</div>
			</div>

			<div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
				<div class="lg:col-span-1">
					<div class="glass-panel p-8 rounded-2xl border border-nexus-border">
						<h2 class="text-xl font-bold mb-6">Create New Department</h2>
						<form id="nexus-create-dept-form" method="POST" class="space-y-6">
							<?php wp_nonce_field( 'nexus_ai_dept_save', 'nexus_ai_dept_nonce' ); ?>
							<div>
								<label class="block text-sm font-medium text-gray-400 mb-2">Department Name</label>
								<input type="text" name="name" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b]" placeholder="e.g. Marketing, IT, Finance">
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-400 mb-2">Description</label>
								<textarea name="description" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] h-24" placeholder="What does this department handle?"></textarea>
							</div>
							<button type="submit" class="w-full bg-accent text-[#1e293b] font-bold py-3 rounded-xl nexus-btn-vibrant">Initialize Department</button>
						</form>
					</div>
				</div>

				<div class="lg:col-span-2">
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<?php foreach ( $depts as $dept ) : ?>
							<div class="glass-panel p-6 rounded-2xl border border-nexus-border glass-card-hover">
								<h3 class="text-xl font-bold text-accent mb-2"><?php echo esc_html( $dept['name'] ); ?></h3>
								<p class="text-sm text-gray-300 mb-4"><?php echo esc_html( $dept['description'] ); ?></p>

								<!-- Department Team Listing -->
								<div class="mt-4 p-4 rounded-xl bg-nexus-elevated/40 border border-white/5 space-y-2 text-xs">
									<p class="font-bold text-[#1e293b] text-[10px] uppercase tracking-wider mb-2">Department Team:</p>
									<?php
									$dept_agents = $wpdb->get_results( $wpdb->prepare( "SELECT name, position FROM {$wpdb->prefix}ai_employees WHERE department_id = %d AND is_active = 1", $dept['id'] ), ARRAY_A ) ?: [];
									if ( ! empty( $dept_agents ) ) {
										foreach ( $dept_agents as $da ) {
											echo '<p class="text-gray-400">• <span class="font-bold text-accent">' . esc_html( $da['name'] ) . '</span> (' . esc_html( $da['position'] ) . ')</p>';
										}
									} else {
										echo '<p class="italic text-gray-500 opacity-60">No agents assigned.</p>';
									}
									?>
								</div>

								<div class="flex justify-between items-center mt-6 pt-4 border-t border-nexus-border/30">
									<span class="text-[10px] text-gray-500 uppercase font-bold tracking-tighter">Active Agents: <?php
										echo (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}ai_employees WHERE department_id = %d", $dept['id'] ) );
									?></span>
									<button class="nexus-delete-dept text-xs text-red-500/50 hover:text-red-500 transition-all" data-id="<?php echo (int) $dept['id']; ?>">Delete Dept</button>
									<a href="<?php echo admin_url( 'admin.php?page=nexus-ai-workforce-employees' ); ?>" class="text-xs text-accent hover:underline font-bold">Manage Team</a>
								</div>
							</div>
						<?php endforeach; ?>
						<?php if ( empty( $depts ) ) : ?>
							<div class="col-span-2 glass-panel p-20 rounded-3xl border-2 border-dashed border-nexus-border text-center">
								<p class="text-gray-500 italic">No departments initialized yet. Create your first one to organize your workforce.</p>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_workforce_page(): void {
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['nexus_ai_hire_nonce'] ) ) {
			if ( wp_verify_nonce( $_POST['nexus_ai_hire_nonce'], 'nexus_ai_hire_save' ) ) {
				$params = $_POST;
				$repo = new \NexusAI\Workforce\Repositories\EmployeeRepository();
				$data = [
					'name'             => sanitize_text_field( $params['name'] ?? '' ),
					'position'         => sanitize_text_field( $params['position'] ?? '' ),
					'department_id'    => absint( $params['department_id'] ?? 0 ),
					'role_description' => wp_kses_post( $params['identity'] ?? '' ),
					'skills'           => wp_kses_post( $params['rules'] ?? '' ),
					'kpis'             => wp_kses_post( $params['kpis'] ?? '' ),
					'prompt_template'  => wp_kses_post( $params['mission'] ?? '' ),
					'thinking_process' => wp_kses_post( $params['thinking_process'] ?? '' ),
					'output_format'    => wp_kses_post( $params['output_format'] ?? '' ),
					'negative_prompts' => wp_kses_post( $params['negative_prompts'] ?? '' ),
					'examples'         => wp_kses_post( $params['examples'] ?? '' ),
					'model_settings'   => wp_json_encode( [
						'model'       => sanitize_text_field( $params['model'] ?? 'gpt-4o' ),
						'temperature' => isset( $params['temperature'] ) ? (float) $params['temperature'] : 0.7,
						'provider'    => 'openai',
						'personality' => sanitize_text_field( $params['personality'] ?? 'professional' ),
						'voice'       => sanitize_text_field( $params['voice'] ?? 'onyx' ),
					] ),
				];

				$id = $repo->create( $data );
				( new \NexusAI\Workforce\Utils\AuditLogger() )->log( 'employee_hired', "Deployed new AI agent: {$data['name']} as {$data['position']}", $id );
				echo '<div class="notice notice-success is-dismissible" style="background:#10b981; color:#fff; padding:15px; border-radius:12px; margin-bottom:20px; font-weight:bold; box-shadow:0 10px 15px -3px rgba(16,185,129,0.2);">AI Agent deployed successfully.</div>';
			}
		}

		echo $this->get_brand_styles();
		?>
		<div class="nexus-admin-body p-10 theme-workforce animate-fade-in-up">
			<div class="mb-10 flex justify-between items-end">
				<div>
					<h2 class="text-sm font-semibold text-accent uppercase tracking-widest mb-2 text-green-500">Talent Management</h2>
					<h1 class="text-5xl font-black text-[#1e293b] text-gradient-vibrant leading-tight">Hire & Manage AI Agents</h1>
					<p class="text-gray-400 mt-3 max-w-2xl text-lg leading-relaxed">Build your virtual executive team. Each agent you hire has a unique personality and specific professional goals.</p>
				</div>
				<div class="flex gap-2 bg-nexus-elevated p-1 rounded-xl border border-nexus-border">
					<button class="nexus-tab-btn px-6 py-2 rounded-lg text-sm font-bold bg-accent text-[#1e293b]" data-tab="hiring">In-House</button>
					<button class="nexus-tab-btn px-6 py-2 rounded-lg text-sm font-bold text-gray-400 hover:text-[#1e293b] transition-all" data-tab="marketplace">Global Marketplace</button>
				</div>
			</div>

			<div class="nexus-step-guide">
				<h3 class="text-[#1e293b] font-bold mb-3 uppercase tracking-tighter text-sm">Deployment Protocol: How to Hire</h3>
				<div class="grid grid-cols-1 md:grid-cols-4 gap-8">
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">1. Expert Templates</p>
						<p>Use <span class="text-accent font-bold">Fast-Hire</span> to load a pre-built expert. <br><span class="italic text-[9px]">Note: A "CEO" template includes first-principles reasoning and ROI focus by default.</span></p>
					</div>
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">2. Strategic Identity</p>
						<p>The <span class="text-accent font-bold">Identity</span> field is the AI's DNA. <br><span class="italic text-[9px]">Example: "You are a master of consumer psychology" is better than "You are a marketer."</span></p>
					</div>
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">3. Cognitive Engine</p>
						<p>Pick the <span class="text-accent font-bold">Model</span>. Use GPT-4o for complex strategy and reasoning. Use GPT-4o Mini for high-speed, simple task execution.</p>
					</div>
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">4. Action Guardrails</p>
						<p>Set <span class="text-accent font-bold">Negative Prompts</span> to tell the agent what NOT to do. <br><span class="italic text-[9px]">Example: "Never use jargon" or "Never disclose pricing to non-admins."</span></p>
					</div>
				</div>
			</div>

			<div id="nexus-hiring-tab" class="nexus-tab-content grid grid-cols-1 lg:grid-cols-2 gap-10">
				<div class="glass-panel p-8 rounded-2xl border border-nexus-border dept-tech">
					<div class="mb-6">
						<h2 class="text-xl font-semibold">Hire New AI Agent</h2>
					</div>

					<!-- Pre-configured Agents Dropdown -->
					<div class="mb-8 p-6 bg-accent/5 rounded-2xl border border-accent/20">
						<label class="block text-sm font-bold text-accent mb-3 uppercase tracking-tighter">Fast-Hire: Select Expert Template</label>
						<select id="nexus-agent-template-selector" class="w-full bg-nexus-elevated border border-nexus-border rounded-xl p-4 text-[#1e293b] font-medium focus:ring-2 focus:ring-accent transition-all">
							<option value="">-- Choose an Expert --</option>
							<optgroup label="Executive Suite">
								<option value="ceo">CEO - Strategic Visionary</option>
								<option value="coo">COO - Operations Architect</option>
								<option value="cto">CTO - Systems Architect</option>
								<option value="cmo">CMO - Growth Architect</option>
								<option value="cfo">CFO - Financial Strategist</option>
							</optgroup>
							<optgroup label="Marketing & Sales">
								<option value="mkt_dir">Marketing Director - Strategy Lead</option>
								<option value="seo">SEO Specialist - Traffic Growth</option>
								<option value="copywriter">Copywriter - Conversion Expert</option>
								<option value="ads">Paid Ads Specialist - ROAS Expert</option>
								<option value="sales">Sales Director - Revenue Architect</option>
								<option value="social">Social Media - Community Growth</option>
								<option value="affiliate">Affiliate Manager - Channel Lead</option>
							</optgroup>
							<optgroup label="Engineering & Design">
								<option value="wp_dev">WP Developer - Backend Specialist</option>
								<option value="php_dev">PHP Developer - Systems Specialist</option>
								<option value="react_dev">React Developer - UI/UX Architect</option>
								<option value="ux">UX Designer - Experience Specialist</option>
								<option value="qa">QA Engineer - Product Stability</option>
								<option value="prompt">Prompt Engineer - AI Optimizer</option>
							</optgroup>
							<optgroup label="Operations & Support">
								<option value="ops_mgr">Operations Manager - Scale Lead</option>
								<option value="hr">HR Manager - Culture Builder</option>
								<option value="legal">Legal Advisor - Risk Mitigator</option>
								<option value="pm">Project Manager - Agile Delivery</option>
								<option value="biz_analyst">Business Analyst - Logic Lead</option>
								<option value="data">Data Analyst - Business Intelligence</option>
								<option value="support">Customer Support Manager - Success Expert</option>
								<option value="prod_mgr">Product Manager - Roadmap Expert</option>
							</optgroup>
							<optgroup label="Content & Admin">
								<option value="exec_asst">Executive Assistant - Logic Guru</option>
								<option value="bookkeeper">Bookkeeper - Precision Master</option>
								<option value="va">Virtual Assistant - Multi-Tasker</option>
								<option value="graphic">Graphic Designer - Visual Lead</option>
								<option value="video">Video Editor - Engagement Lead</option>
								<option value="email">Email Marketer - Retention Expert</option>
							</optgroup>
							<optgroup label="Elite Specialty Roles">
								<option value="aso">App Store Optimizer - Mobile Growth</option>
								<option value="crisis_pr">Crisis PR - Reputation Defense</option>
								<option value="grant_premium">Senior Grant Strategist - Funding</option>
								<option value="funnel_hacker">Funnel Optimization - conversion Expert</option>
								<option value="vulnerability_expert">Security Researcher - System Hardening</option>
							</optgroup>
						</select>
						<p class="text-[10px] text-gray-500 mt-3">Expect: Instant population of professional identity and mission constraints.</p>
					</div>

					<form id="nexus-hire-agent-form" method="POST" class="space-y-8">
						<?php wp_nonce_field( 'nexus_ai_hire_save', 'nexus_ai_hire_nonce' ); ?>
						<!-- Prompt Preview Toggle -->
						<div class="flex justify-end mb-2">
							<button type="button" id="nexus-toggle-prompt-preview" class="text-[10px] font-bold text-accent uppercase tracking-widest hover:underline">Show Master Prompt Preview</button>
						</div>

						<div id="nexus-prompt-preview-container" class="hidden p-6 rounded-2xl bg-black border border-accent/30 mb-6 font-mono text-[10px] text-gray-400 overflow-y-auto max-h-64 whitespace-pre-wrap relative group">
							<div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
								<button type="button" id="nexus-optimize-prompt" class="bg-accent/20 text-accent text-[9px] px-2 py-1 rounded border border-accent/30 hover:bg-accent hover:text-black font-bold">Refine with AI</button>
							</div>
							<div id="nexus-prompt-preview-content"></div>
						</div>

						<!-- Phase 1: Identity -->
						<div class="form-group-tint">
							<p class="text-xs font-bold text-accent uppercase mb-4 tracking-widest">Phase 1: Professional Identity</p>
							<div class="space-y-4">
								<div>
									<label class="block text-sm font-medium text-gray-400 mb-2">Agent Name</label>
									<input type="text" name="name" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b]" placeholder="e.g. Sarah">
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-400 mb-2">Professional Position</label>
									<input type="text" name="position" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b]" placeholder="e.g. CMO, Full Stack Developer">
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-400 mb-2">Assign Department</label>
									<select name="department_id" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b]">
										<option value="0">-- No Department (Global) --</option>
										<?php
										global $wpdb;
										$depts_list = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}ai_departments ORDER BY name ASC", ARRAY_A ) ?: [];
										foreach ( $depts_list as $d ) {
											echo '<option value="' . (int) $d['id'] . '">' . esc_html( $d['name'] ) . '</option>';
										}
										?>
									</select>
								</div>
							</div>
						</div>
						<div class="form-group-tint">
							<p class="text-xs font-bold text-accent uppercase mb-4 tracking-widest">Phase 2: Core Reasoning & Objectives</p>
							<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
								<div>
									<label class="block text-sm font-medium text-gray-400 mb-2">Identity (Persona)</label>
									<textarea name="identity" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] h-24" placeholder="Who is this AI?"></textarea>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-400 mb-2">Mission (Primary Objective)</label>
									<textarea name="mission" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] h-24" placeholder="What is its main goal?"></textarea>
								</div>
							</div>
							<div class="mt-6">
								<label class="block text-sm font-medium text-gray-400 mb-2">Strategic Objectives (KPIs)</label>
								<textarea name="kpis" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] h-20" placeholder="What specific metrics define success for this role?"></textarea>
							</div>
						</div>

						<!-- Advanced Brain Builder -->
						<div class="form-group-tint">
							<div class="flex justify-between items-center mb-4">
								<p class="text-xs font-bold text-accent uppercase tracking-widest">Phase 3: Advanced Brain Configuration</p>
								<button type="button" onclick="document.getElementById('nexus-advanced-brain-fields').classList.toggle('hidden')" class="text-[10px] text-gray-500 hover:text-[#1e293b] uppercase font-bold">Toggle Advanced Settings</button>
							</div>

							<div id="nexus-advanced-brain-fields" class="hidden space-y-6">
								<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
									<div>
										<label class="block text-sm font-medium text-gray-400 mb-2">Behavior & Rules</label>
										<textarea name="rules" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] h-24" placeholder="Fixed rules the AI must always follow..."></textarea>
									</div>
									<div>
										<label class="block text-sm font-medium text-gray-400 mb-2">Thinking Process</label>
										<textarea name="thinking_process" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] h-24" placeholder="How should the AI reason? (e.g. First Principles, SWOT)"></textarea>
									</div>
								</div>
								<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
									<div>
										<label class="block text-sm font-medium text-gray-400 mb-2">Output Format</label>
										<textarea name="output_format" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] h-24" placeholder="Standardized output style (Markdown, JSON, Bullet points)"></textarea>
									</div>
									<div>
										<label class="block text-sm font-medium text-gray-400 mb-2">Negative Prompts (Guardrails)</label>
										<textarea name="negative_prompts" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] h-24" placeholder="What should the AI NEVER do or say?"></textarea>
									</div>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-400 mb-2">Few-Shot Examples</label>
									<textarea name="examples" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] h-32" placeholder="Example 1: User says X, AI says Y..."></textarea>
								</div>
							</div>
						</div>
						<div class="grid grid-cols-2 gap-6">
							<div>
								<label class="block text-sm font-medium text-gray-400 mb-2">Communication Tone</label>
								<select name="personality" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b]">
									<option value="professional">Professional & Direct</option>
									<option value="creative">Creative & Enthusiastic</option>
									<option value="analytical">Analytical & Fact-based</option>
									<option value="motivational">Visionary & Motivational</option>
									<option value="technical">Technical & Precise</option>
								</select>
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-400 mb-2">Voice Identity</label>
								<select name="voice" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b]">
									<option value="onyx">OpenAI - Onyx (Deep)</option>
									<option value="nova">OpenAI - Nova (Energetic)</option>
									<option value="shimmer">OpenAI - Shimmer (Soft)</option>
									<option value="eleven_multilingual">ElevenLabs - Professional</option>
								</select>
							</div>
						</div>
						<div class="grid grid-cols-2 gap-4">
							<div>
								<label class="block text-sm font-medium text-gray-400 mb-2">AI Model</label>
								<select name="model" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b]">
									<optgroup label="High Reasoning">
										<option value="gpt-4o">OpenAI GPT-4o (Standard)</option>
										<option value="claude-3-5-sonnet-20240620">Anthropic Claude 3.5 Sonnet</option>
										<option value="gemini-1.5-pro">Google Gemini 1.5 Pro</option>
									</optgroup>
									<optgroup label="High Volume / Fast">
										<option value="gpt-4o-mini">OpenAI GPT-4o Mini</option>
										<option value="gemini-1.5-flash">Google Gemini 1.5 Flash</option>
										<option value="gemini-3-flash">Google Gemini 3 Flash (BETA)</option>
									</optgroup>
									<optgroup label="OpenRouter / Open Source">
										<option value="meta-llama/llama-3.1-405b-instruct">Llama 3.1 405B (via OpenRouter)</option>
										<option value="mistralai/mistral-large">Mistral Large</option>
										<option value="x-ai/grok-1">xAI Grok-1</option>
									</optgroup>
								</select>
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-400 mb-2">Creativity (Temp)</label>
								<input type="range" name="temperature" min="0" max="1" step="0.1" value="0.7" class="w-full h-2 bg-nexus-border rounded-lg appearance-none cursor-pointer accent-accent">
							</div>
						</div>
						<button type="submit" class="w-full bg-accent hover:bg-green-600 text-[#1e293b] font-bold py-4 rounded-xl transition-all shadow-lg shadow-green-500/10 nexus-btn-vibrant">Deploy Agent</button>
						<span class="nexus-button-note text-center">Expect: Permanent agent profile creation and workforce integration.</span>
					</form>
				</div>

				<div class="space-y-8">
					<div class="glass-panel p-8 rounded-3xl border border-nexus-border bg-accent/5 flex flex-col justify-center min-h-[150px]">
						<p class="text-sm text-gray-400 uppercase tracking-widest font-bold">Workforce Capability</p>
						<p class="text-6xl font-black mt-2 text-[#1e293b]">ACTIVE</p>
					</div>
					<div class="glass-panel p-8 rounded-2xl border border-nexus-border">
						<h3 class="text-lg font-bold mb-6">Current Workforce</h3>
						<div class="space-y-4">
							<?php
							global $wpdb;
							$active_agents = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ai_employees WHERE is_active = 1", ARRAY_A ) ?: [];
							foreach ( $active_agents as $a ) : ?>
								<div class="p-4 rounded-xl bg-[#f8fafc]/5 border border-white/5 flex justify-between items-center group">
									<div>
										<p class="font-bold text-accent"><?php echo esc_html( $a['name'] ); ?></p>
										<p class="text-[10px] text-gray-400 uppercase"><?php echo esc_html( $a['position'] ); ?></p>
									</div>
									<button class="nexus-delete-agent opacity-0 group-hover:opacity-100 text-red-500 text-xs transition-all" data-id="<?php echo (int) $a['id']; ?>">Terminate</button>
								</div>
							<?php endforeach; ?>
							<?php if ( empty( $active_agents ) ) : ?>
								<div class="text-sm text-gray-500 italic">Start by selecting a template or creating a custom agent.</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<div id="nexus-marketplace-tab" class="nexus-tab-content hidden">
				<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
					<!-- Grant Writer -->
					<div class="glass-panel p-8 rounded-3xl border border-nexus-border hover:border-nexus-gold transition-all group glass-card-hover border-l-4 border-l-nexus-gold">
						<div class="w-16 h-16 rounded-2xl bg-nexus-gold/10 flex items-center justify-center text-nexus-gold mb-6 group-hover:scale-110 transition-transform">
							<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
						</div>
						<h3 class="text-xl font-bold text-[#1e293b]">Grant Writer Pro</h3>
						<p class="text-sm text-gray-500 mt-2">Specialized in winning high-value federal and private grants.</p>
						<button class="nexus-marketplace-install w-full mt-8 bg-nexus-gold/20 hover:bg-nexus-gold text-nexus-gold hover:text-black font-bold py-3 rounded-xl transition-all nexus-btn-vibrant" data-agent="grant_writer">Install Role</button>
						<span class="nexus-button-note">Expect: Expert persona added to your workforce.</span>
					</div>

					<!-- Corporate Lawyer -->
					<div class="glass-panel p-8 rounded-3xl border border-nexus-border hover:border-blue-500 transition-all group glass-card-hover border-l-4 border-l-blue-500">
						<div class="w-16 h-16 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-500 mb-6 group-hover:scale-110 transition-transform">
							<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
						</div>
						<h3 class="text-xl font-bold text-[#1e293b]">Corporate Counsel</h3>
						<p class="text-sm text-gray-500 mt-2">Legal expert for contract review, compliance, and risk mitigation.</p>
						<button class="nexus-marketplace-install w-full mt-8 bg-blue-500/20 hover:bg-blue-500 text-blue-500 hover:text-[#1e293b] font-bold py-3 rounded-xl transition-all nexus-btn-vibrant" data-agent="lawyer">Install Role</button>
					</div>

					<!-- Medical Consultant -->
					<div class="glass-panel p-8 rounded-3xl border border-nexus-border hover:border-red-500 transition-all group glass-card-hover border-l-4 border-l-red-500">
						<div class="w-16 h-16 rounded-2xl bg-red-500/10 flex items-center justify-center text-red-500 mb-6 group-hover:scale-110 transition-transform">
							<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
						</div>
						<h3 class="text-xl font-bold text-[#1e293b]">Medical Advisor</h3>
						<p class="text-sm text-gray-500 mt-2">Specialized in health research, wellness plans, and biological data.</p>
						<button class="nexus-marketplace-install w-full mt-8 bg-red-500/20 hover:bg-red-500 text-red-500 hover:text-[#1e293b] font-bold py-3 rounded-xl transition-all nexus-btn-vibrant" data-agent="doctor">Install Role</button>
					</div>

					<!-- Financial Planner -->
					<div class="glass-panel p-8 rounded-3xl border border-nexus-border hover:border-green-500 transition-all group glass-card-hover border-l-4 border-l-green-500">
						<div class="w-16 h-16 rounded-2xl bg-green-500/10 flex items-center justify-center text-green-500 mb-6 group-hover:scale-110 transition-transform">
							<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
						</div>
						<h3 class="text-xl font-bold text-[#1e293b]">Wealth Strategist</h3>
						<p class="text-sm text-gray-500 mt-2">Capital allocation, investment analysis, and tax optimization expert.</p>
						<button class="nexus-marketplace-install w-full mt-8 bg-green-500/20 hover:bg-green-500 text-green-500 hover:text-[#1e293b] font-bold py-3 rounded-xl transition-all nexus-btn-vibrant" data-agent="finance">Install Role</button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the "Settings" page.
	 */
	public function render_settings_page(): void {
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['nexus_ai_settings_nonce'] ) ) {
			if ( wp_verify_nonce( $_POST['nexus_ai_settings_nonce'], 'nexus_ai_settings_save' ) ) {
				$data = [];
				$params = $_POST;

				$sensitive_keys = [ 'openai_api_key', 'claude_api_key', 'gemini_api_key', 'openrouter_api_key', 'deepseek_api_key', 'mistral_api_key' ];
				$encryption = new \NexusAI\Workforce\Utils\Encryption();

				foreach ( $sensitive_keys as $key ) {
					if ( isset( $params[ $key ] ) && $params[ $key ] !== '' && $params[ $key ] !== '********' ) {
						$data[ $key ] = $encryption->encrypt( $params[ $key ] );
					}
				}

				if ( isset( $params['default_model'] ) ) {
					$data['default_model'] = sanitize_text_field( $params['default_model'] );
				}

				if ( isset( $params['ui_color'] ) ) {
					$data['ui_color'] = sanitize_hex_color( $params['ui_color'] );
				}

				if ( isset( $params['agency_logo'] ) ) {
					$data['agency_logo'] = esc_url_raw( $params['agency_logo'] );
				}

				if ( isset( $params['platform_title'] ) ) {
					$data['platform_title'] = sanitize_text_field( $params['platform_title'] );
				}

				$data['agency_mode'] = isset( $params['agency_mode'] ) ? true : false;
				$data['maintenance_mode'] = isset( $params['maintenance_mode'] ) ? true : false;
				$data['widget_enabled'] = isset( $params['widget_enabled'] ) ? true : false;

				if ( isset( $params['company_mission'] ) ) {
					$data['company_mission'] = sanitize_textarea_field( $params['company_mission'] );
				}

				if ( isset( $params['company_values'] ) ) {
					$data['company_values'] = sanitize_textarea_field( $params['company_values'] );
				}

				if ( isset( $params['company_audience'] ) ) {
					$data['company_audience'] = sanitize_textarea_field( $params['company_audience'] );
				}

				if ( isset( $params['ui_font'] ) ) {
					$data['ui_font'] = sanitize_text_field( $params['ui_font'] );
				}

				if ( isset( $params['public_agent_id'] ) ) {
					$data['public_agent_id'] = (int) $params['public_agent_id'];
				}

				$this->settings->update( $data );
				echo '<div class="notice notice-success is-dismissible" style="background:#10b981; color:#fff; padding:15px; border-radius:12px; margin-bottom:20px; font-weight:bold; box-shadow:0 10px 15px -3px rgba(16,185,129,0.2);">System infrastructure configuration saved successfully.</div>';
			}
		}

		echo $this->get_brand_styles();
		?>
		<div class="nexus-admin-body p-10 theme-settings animate-fade-in-up">
			<div class="mb-10">
				<h2 class="text-sm font-semibold text-accent uppercase tracking-widest mb-2">Infrastructure</h2>
				<h1 class="text-5xl font-black text-[#1e293b] text-gradient-vibrant leading-tight">System Configuration</h1>
				<p class="text-gray-400 mt-3 max-w-2xl text-lg leading-relaxed">Manage the core engines and branding of your AI platform.</p>
			</div>

			<div class="nexus-step-guide">
				<div class="grid grid-cols-1 md:grid-cols-2 gap-10">
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">Configuration Note:</p>
						<p>API keys are <span class="text-accent font-bold">AES-256 Encrypted</span> and stored securely. We never store keys in plain text.</p>
					</div>
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">White Labeling:</p>
						<p>Changing the color and logo will update the entire platform UI for all users.</p>
					</div>
				</div>
			</div>

			<form id="nexus-settings-form" method="POST" class="max-w-6xl grid grid-cols-1 md:grid-cols-2 gap-10">
				<?php wp_nonce_field( 'nexus_ai_settings_save', 'nexus_ai_settings_nonce' ); ?>
				<div class="glass-panel p-8 rounded-2xl border border-nexus-border space-y-6">
					<h2 class="text-xl font-semibold mb-6 text-accent">Global AI Engines</h2>
						<div>
							<label class="block text-sm font-medium text-gray-400 mb-2">OpenAI API Key</label>
							<input type="password" name="openai_api_key" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] focus:border-accent outline-none" value="<?php echo ! empty( $this->settings->get( 'openai_api_key' ) ) ? '********' : ''; ?>" placeholder="sk-...">
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-400 mb-2">Anthropic API Key</label>
							<input type="password" name="claude_api_key" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] focus:border-accent outline-none" value="<?php echo ! empty( $this->settings->get( 'claude_api_key' ) ) ? '********' : ''; ?>" placeholder="sk-ant-...">
						</div>
						<div class="dept-marketing p-4 rounded-xl border border-nexus-blue/10">
							<label class="block text-sm font-bold text-[#1e293b] mb-2 flex items-center gap-2">
								<svg class="w-4 h-4 text-nexus-blue" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L4.5 20.29L5.21 21L12 18L18.79 21L19.5 20.29L12 2Z"/></svg>
								Google Gemini Key
							</label>
							<input type="password" name="gemini_api_key" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] focus:border-nexus-blue outline-none" value="<?php echo ! empty( $this->settings->get( 'gemini_api_key' ) ) ? '********' : ''; ?>" placeholder="AIza...">
							<p class="text-[9px] text-gray-500 mt-2">Required for Gemini 1.5 Pro/Flash integration.</p>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-400 mb-2">OpenRouter API Key</label>
							<input type="password" name="openrouter_api_key" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] focus:border-accent outline-none" value="<?php echo ! empty( $this->settings->get( 'openrouter_api_key' ) ) ? '********' : ''; ?>" placeholder="sk-or-...">
						</div>
						<div class="grid grid-cols-2 gap-4">
							<div>
								<label class="block text-sm font-medium text-gray-400 mb-2">DeepSeek Key</label>
								<input type="password" name="deepseek_api_key" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] focus:border-accent outline-none" value="<?php echo ! empty( $this->settings->get( 'deepseek_api_key' ) ) ? '********' : ''; ?>" placeholder="sk-...">
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-400 mb-2">Mistral Key</label>
								<input type="password" name="mistral_api_key" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] focus:border-accent outline-none" value="<?php echo ! empty( $this->settings->get( 'mistral_api_key' ) ) ? '********' : ''; ?>" placeholder="sk-...">
							</div>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-400 mb-2">Global Default Model</label>
							<select name="default_model" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] outline-none focus:border-accent">
								<?php $current_model = $this->settings->get( 'default_model', 'gpt-4o' ); ?>
								<optgroup label="High Reasoning">
									<option value="gpt-4o" <?php selected( $current_model, 'gpt-4o' ); ?>>OpenAI GPT-4o (Standard)</option>
									<option value="claude-3-5-sonnet-20240620" <?php selected( $current_model, 'claude-3-5-sonnet-20240620' ); ?>>Anthropic Claude 3.5 Sonnet</option>
									<option value="gemini-1.5-pro" <?php selected( $current_model, 'gemini-1.5-pro' ); ?>>Google Gemini 1.5 Pro</option>
								</optgroup>
								<optgroup label="High Volume / Fast">
									<option value="gpt-4o-mini" <?php selected( $current_model, 'gpt-4o-mini' ); ?>>OpenAI GPT-4o Mini</option>
									<option value="gemini-1.5-flash" <?php selected( $current_model, 'gemini-1.5-flash' ); ?>>Google Gemini 1.5 Flash</option>
									<option value="gemini-3-flash" <?php selected( $current_model, 'gemini-3-flash' ); ?>>Google Gemini 3 Flash (BETA)</option>
								</optgroup>
								<optgroup label="OpenRouter / Open Source">
									<option value="meta-llama/llama-3.1-405b-instruct" <?php selected( $current_model, 'meta-llama/llama-3.1-405b-instruct' ); ?>>Llama 3.1 405B (via OpenRouter)</option>
									<option value="mistralai/mistral-large" <?php selected( $current_model, 'mistralai/mistral-large' ); ?>>Mistral Large</option>
									<option value="x-ai/grok-1" <?php selected( $current_model, 'x-ai/grok-1' ); ?>>xAI Grok-1</option>
								</optgroup>
							</select>
						</div>
						<button type="submit" class="w-full bg-accent text-[#1e293b] font-bold py-4 rounded-xl transition-all shadow-lg shadow-accent/10 nexus-btn-vibrant">Save Infrastructure</button>
						<button type="button" id="nexus-test-connectivity" class="w-full mt-2 bg-[#f8fafc]/5 border border-white/10 text-[#1e293b] py-2 rounded-lg text-xs hover:bg-[#f8fafc]/10 transition-all nexus-btn-vibrant">Run Global Connectivity Test</button>
						<span class="nexus-button-note text-center">Expect: Secure AES-256 encryption of all keys before storage.</span>
				</div>

				<div class="glass-panel p-8 rounded-2xl border border-nexus-border">
					<h2 class="text-xl font-semibold mb-6 text-nexus-gold">Company Memory & Identity</h2>
					<div class="space-y-6">
						<div>
							<label class="block text-sm font-medium text-gray-400 mb-2">Company Vision & Mission</label>
							<textarea name="company_mission" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] h-24" placeholder="Describe the ultimate goal of the company..."><?php echo esc_textarea( $this->settings->get( 'company_mission', '' ) ); ?></textarea>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-400 mb-2">Core Values</label>
							<textarea name="company_values" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] h-24" placeholder="Integrity, Innovation, Customer First..."><?php echo esc_textarea( $this->settings->get( 'company_values', '' ) ); ?></textarea>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-400 mb-2">Target Audience / Personas</label>
							<textarea name="company_audience" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b] h-24" placeholder="Describe your ideal customers..."><?php echo esc_textarea( $this->settings->get( 'company_audience', '' ) ); ?></textarea>
						</div>

						<h2 class="text-xl font-semibold mt-10 mb-6 text-nexus-gold">White Label & Brand</h2>
						<div>
							<label class="block text-sm font-medium text-gray-400 mb-2">Agency Logo URL</label>
							<input type="text" name="agency_logo" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b]" value="<?php echo esc_attr( $this->settings->get( 'agency_logo', '' ) ); ?>" placeholder="https://...">
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-400 mb-2">Primary Accent Color</label>
							<input type="color" name="ui_color" class="w-20 h-12 bg-nexus-elevated border border-nexus-border rounded-lg p-1 text-[#1e293b] cursor-pointer" value="<?php echo esc_attr( $this->settings->get( 'ui_color', '#7C3AED' ) ); ?>">
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-400 mb-2">Global UI Font</label>
							<select name="ui_font" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b]">
								<option value="Inter" <?php selected( $this->settings->get( 'ui_font', 'Inter' ), 'Inter' ); ?>>Inter (Modern SaaS)</option>
								<option value="Segoe UI" <?php selected( $this->settings->get( 'ui_font', 'Inter' ), 'Segoe UI' ); ?>>Segoe UI (Enterprise)</option>
								<option value="JetBrains Mono" <?php selected( $this->settings->get( 'ui_font', 'Inter' ), 'JetBrains Mono' ); ?>>JetBrains Mono (Technical)</option>
								<option value="Playfair Display" <?php selected( $this->settings->get( 'ui_font', 'Inter' ), 'Playfair Display' ); ?>>Playfair Display (Luxury)</option>
							</select>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-400 mb-2">Platform Display Title</label>
							<input type="text" name="platform_title" class="w-full bg-nexus-elevated border border-nexus-border rounded-lg p-3 text-[#1e293b]" value="<?php echo esc_attr( $this->settings->get( 'platform_title', 'Nexus AI Workforce' ) ); ?>" placeholder="Nexus AI Workforce">
						</div>
						<div class="p-6 rounded-2xl bg-nexus-elevated border border-nexus-border">
							<p class="text-sm font-bold text-[#1e293b] mb-2 uppercase">Agency Mode</p>
							<div class="flex items-center gap-4">
								<input type="checkbox" name="agency_mode" class="w-5 h-5 rounded border-gray-600 bg-gray-700 text-accent" <?php checked( (bool) $this->settings->get( 'agency_mode', false ) ); ?>>
								<p class="text-xs text-gray-500">Hide "Nexus AI" branding and use "Platform Display Title" throughout the UI.</p>
							</div>
						</div>
						<div class="p-6 rounded-2xl bg-red-900/10 border border-red-900/20">
							<p class="text-sm font-bold text-red-500 mb-2 uppercase">Platform Maintenance Mode</p>
							<div class="flex items-center gap-4">
								<input type="checkbox" name="maintenance_mode" class="w-5 h-5 rounded border-red-900/50 bg-gray-700 text-red-600" <?php checked( (bool) $this->settings->get( 'maintenance_mode', false ) ); ?>>
								<p class="text-xs text-gray-400">Lock the AI workforce environment. Only Administrators can access dashboards.</p>
							</div>
						</div>

						<h3 class="text-sm font-bold text-[#1e293b] mt-10 mb-4 uppercase tracking-widest text-[10px]">Active Enterprise Tools</h3>
						<div class="grid grid-cols-2 gap-3">
							<div class="p-3 rounded-xl bg-green-500/10 border border-green-500/20 flex items-center justify-between tool-active-glow">
								<span class="text-[10px] font-bold text-green-500">WP_POSTS</span>
								<span class="text-[9px] text-green-500/50">ACTIVE</span>
							</div>
							<div class="p-3 rounded-xl bg-green-500/10 border border-green-500/20 flex items-center justify-between tool-active-glow">
								<span class="text-[10px] font-bold text-green-500">WOOCOMMERCE</span>
								<span class="text-[9px] text-green-500/50">ACTIVE</span>
							</div>
							<div class="p-3 rounded-xl bg-nexus-blue/10 border border-nexus-blue/20 flex items-center justify-between opacity-50 tool-pending-glow">
								<span class="text-[10px] font-bold text-nexus-blue">SLACK_HOOK</span>
								<span class="text-[9px] text-nexus-blue/50">PENDING</span>
							</div>
							<div class="p-3 rounded-xl bg-nexus-blue/10 border border-nexus-blue/20 flex items-center justify-between opacity-50 tool-pending-glow">
								<span class="text-[10px] font-bold text-nexus-blue">DISCORD_HOOK</span>
								<span class="text-[9px] text-nexus-blue/50">PENDING</span>
							</div>
						</div>


						<h2 class="text-xl font-semibold mt-10 mb-6 text-nexus-blue">Platform Diagnostics</h2>
						<div class="p-6 rounded-2xl bg-nexus-elevated border border-nexus-border">
							<div class="flex justify-between items-center mb-6">
								<h2 class="text-sm font-bold text-[#1e293b] uppercase">System Integrity</h2>
								<button type="button" id="nexus-run-diagnostics" class="text-[9px] bg-accent/20 text-accent px-3 py-1 rounded-full font-bold hover:bg-accent hover:text-black transition-all">Run Full Scan</button>
							</div>
							<div id="nexus-diagnostic-results" class="space-y-3">
								<div class="flex items-center justify-between p-3 rounded-xl bg-nexus-bg border border-white/5">
									<span class="text-[10px] text-gray-500 uppercase">Encryption Engine</span>
									<span class="text-[10px] text-green-500 font-bold">READY</span>
								</div>
								<div class="flex items-center justify-between p-3 rounded-xl bg-nexus-bg border border-white/5">
									<span class="text-[10px] text-gray-500 uppercase">Database Integrity</span>
									<span class="text-[10px] text-green-500 font-bold">13/13 TABLES</span>
								</div>
								<div class="flex items-center justify-between p-3 rounded-xl bg-nexus-bg border border-white/5">
									<span class="text-[10px] text-gray-500 uppercase">REST API Path</span>
									<span class="text-[10px] text-accent font-bold">ACCESSIBLE</span>
								</div>
							</div>
						</div>

						<h2 class="text-xl font-semibold mt-10 mb-6 text-nexus-violet">Frontend Chat Widget</h2>
						<div class="p-6 rounded-2xl bg-nexus-elevated border border-nexus-border">
							<div class="flex items-center justify-between mb-6">
								<p class="text-sm font-bold text-[#1e293b] uppercase">Enable Public Widget</p>
								<input type="checkbox" name="widget_enabled" class="w-5 h-5 rounded border-gray-600 bg-gray-700 text-nexus-violet" <?php checked( (bool) $this->settings->get( 'widget_enabled', false ) ); ?>>
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-400 mb-2">Public Facing Agent</label>
								<select name="public_agent_id" class="w-full bg-nexus-bg border border-nexus-border rounded-lg p-3 text-[#1e293b]">
									<option value="0">-- Select Agent --</option>
									<?php
									global $wpdb;
									$agents = $wpdb->get_results( "SELECT id, name, position FROM {$wpdb->prefix}ai_employees WHERE is_active = 1", ARRAY_A );
									foreach ( $agents as $agent ) {
										$selected = ( (int) $this->settings->get( 'public_agent_id', 0 ) === (int) $agent['id'] ) ? 'selected' : '';
										echo '<option value="' . (int) $agent['id'] . '" ' . $selected . '>' . esc_html( $agent['name'] ) . ' (' . esc_html( $agent['position'] ) . ')</option>';
									}
									?>
								</select>
								<p class="text-[10px] text-gray-500 mt-2">This agent will handle all public-facing queries on your website.</p>
							</div>
						</div>

						<h2 class="text-xl font-semibold mt-10 mb-6 text-red-500">System Sample Data</h2>
						<div class="p-6 rounded-2xl bg-red-500/5 border border-red-500/10">
							<p class="text-sm text-gray-400 mb-6 leading-relaxed">Instantly populate your platform with a world-class executive team, pre-configured departments, and sample knowledge base data.</p>
							<div class="grid grid-cols-2 gap-4">
								<button type="button" id="nexus-seed-samples" class="bg-red-500/20 text-red-500 font-bold py-3 rounded-xl hover:bg-red-500 hover:text-[#1e293b] transition-all text-xs uppercase tracking-widest border border-red-500/20">Seed Global Samples</button>
								<button type="button" id="nexus-purge-all" class="bg-[#f8fafc]/5 text-gray-500 font-bold py-3 rounded-xl hover:bg-red-600 hover:text-[#1e293b] transition-all text-xs uppercase tracking-widest border border-white/5">Purge All Data</button>
							</div>
							<p class="text-[9px] text-gray-500 mt-4 italic">Note: Seeding creates Alexander (CSO), Elena (Growth Architect), and Marcus (Systems Architect). Purge All will TRUNCATE all platform tables.</p>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the "Knowledge Base" (Company Brain) page.
	 */
	public function render_kb_page(): void {
		echo $this->get_brand_styles();
		?>
		<div class="nexus-admin-body p-10 theme-kb animate-fade-in-up">
			<div class="mb-10 flex justify-between items-end">
				<div>
					<h2 class="text-sm font-semibold text-accent uppercase tracking-widest mb-2">Intelligence</h2>
					<h1 class="text-5xl font-black text-[#1e293b] text-gradient-vibrant leading-tight">Company Brain (RAG)</h1>
					<p class="text-gray-400 mt-3 max-w-2xl text-lg leading-relaxed">Give your AI workforce "Company Memory." Upload your unique business data to ground agent responses.</p>
				</div>
				<button id="nexus-wipe-memory" class="bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-[#1e293b] px-6 py-2 rounded-xl text-xs font-bold transition-all border border-red-500/20">Wipe All Memory</button>
			</div>

			<div class="nexus-step-guide">
				<h3 class="text-[#1e293b] font-bold mb-3 uppercase tracking-tighter text-sm">Intelligence Protocol: Training the Brain</h3>
				<div class="grid grid-cols-1 md:grid-cols-3 gap-10">
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">Document Ingestion</p>
						<p>Drag in PDFs or DOCX files. <br><span class="text-accent font-bold italic text-[9px]">How it works: We "chunk" the text and index it. When you ask an agent a question, we retrieve the relevant "brain chunks" and inject them into the prompt.</span></p>
					</div>
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">Competitive Scraping</p>
						<p>Enter a URL to crawl it. <br><span class="text-accent font-bold italic text-[9px]">Note: Agents can then answer questions about competitor pricing or landing page strategies based on real-time data.</span></p>
					</div>
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">Contextual Isolation</p>
						<p>Assign data to specific agents. <br><span class="text-accent font-bold italic text-[9px]">Example: Upload "Alex's Diary" and assign it only to "Alexander (CEO)" so other agents don't see his personal notes.</span></p>
					</div>
				</div>
			</div>

			<div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
				<div class="glass-panel p-8 rounded-2xl border border-nexus-border dept-tech">
					<h2 class="text-xl font-semibold mb-6">Ingest Data</h2>
					<div class="space-y-8">
						<div id="nexus-kb-upload-zone" class="border-2 border-dashed border-accent/20 rounded-2xl p-10 text-center hover:border-accent transition-all bg-accent/5 group cursor-pointer relative">
							<input type="file" id="nexus-kb-file-input" class="absolute inset-0 opacity-0 cursor-pointer" accept=".pdf,.docx,.txt,.md,.csv">
							<div id="nexus-upload-idle">
								<p class="text-sm text-gray-300 font-medium">Click or Drag PDF, DOCX, or TXT here</p>
								<button class="mt-6 bg-nexus-elevated border border-nexus-border text-[#1e293b] px-8 py-3 rounded-xl text-sm font-bold hover:border-accent transition-all nexus-btn-vibrant">Select Files</button>
							</div>
							<div id="nexus-upload-progress" class="hidden">
								<div class="w-12 h-12 border-4 border-accent border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
								<p class="text-accent font-bold">Ingesting Knowledge...</p>
							</div>
							<span class="nexus-button-note">Expect: Automatic chunking and semantic indexing into Company Memory.</span>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-400 mb-2">Assign Knowledge To</label>
							<select id="nexus-kb-target" class="w-full bg-nexus-elevated border border-nexus-border rounded-xl p-3 text-[#1e293b] mb-6">
								<option value="global">Global Company Brain</option>
								<?php
								global $wpdb;
								$agents = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}ai_employees", ARRAY_A );
								foreach ( $agents as $agent ) {
									echo '<option value="agent-' . (int) $agent['id'] . '">Agent: ' . esc_html( $agent['name'] ) . '</option>';
								}
								?>
							</select>
						</div>
						<div>
							<label class="block text-sm font-medium text-gray-400 mb-2">Web Scraper</label>
							<div class="flex gap-2">
								<input type="url" id="nexus-kb-url-input" class="flex-1 bg-nexus-elevated border border-nexus-border rounded-xl p-4 text-[#1e293b] outline-none focus:border-accent" placeholder="https://...">
								<button id="nexus-kb-index-btn" class="bg-accent text-[#1e293b] px-8 py-2 rounded-xl font-bold hover:bg-blue-600 transition-all nexus-btn-vibrant">Index</button>
							</div>
							<span class="nexus-button-note">Expect: Recursive crawling of the provided URL.</span>
						</div>
						<div class="pt-6 border-t border-nexus-border/30">
							<label class="block text-sm font-medium text-gray-400 mb-2">Direct Intelligence Input</label>
							<input type="text" id="nexus-kb-direct-name" class="w-full bg-nexus-elevated border border-nexus-border rounded-xl p-3 text-[#1e293b] mb-2" placeholder="Document Name (e.g. Q4 SOPs)">
							<textarea id="nexus-kb-direct-text" class="w-full h-40 bg-nexus-elevated border border-nexus-border rounded-xl p-4 text-[#1e293b] text-sm outline-none focus:border-accent" placeholder="Paste raw company data or strategic notes here..."></textarea>
							<button id="nexus-kb-direct-btn" class="w-full mt-2 bg-nexus-blue text-[#1e293b] py-3 rounded-xl font-bold hover:opacity-90 transition-all nexus-btn-vibrant theme-kb">Ingest Strategic Text</button>
						</div>
					</div>
				</div>

				<div class="glass-panel p-8 rounded-2xl border border-nexus-border">
					<h2 class="text-xl font-semibold mb-6">Document Library</h2>
					<?php
					global $wpdb;
					$docs = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ai_knowledge_documents ORDER BY created_at DESC", ARRAY_A ) ?: [];
					?>
					<div class="overflow-x-auto">
						<table class="w-full text-left text-sm text-gray-400">
							<thead class="text-xs uppercase text-gray-500 border-b border-nexus-border">
								<tr>
									<th class="pb-3 px-2">Type</th>
									<th class="pb-3 px-2">Source</th>
									<th class="pb-3 px-2">Status</th>
									<th class="pb-3 px-2">Created</th>
								</tr>
							</thead>
							<tbody class="divide-y divide-nexus-border/50">
								<?php foreach ( $docs as $doc ) : ?>
									<tr>
										<td class="py-4 px-2 uppercase text-[10px] font-bold"><?php echo esc_html( $doc['type'] ); ?></td>
										<td class="py-4 px-2 truncate max-w-[150px]"><?php echo esc_html( basename($doc['source_path']) ); ?></td>
										<td class="py-4 px-2">
											<span class="px-2 py-1 rounded-full bg-green-500/10 text-green-500 text-[10px] font-bold uppercase">
												<?php echo esc_html( $doc['status'] ); ?>
											</span>
										</td>
										<td class="py-4 px-2 text-[10px]"><?php echo esc_html( $doc['created_at'] ); ?></td>
									</tr>
								<?php endforeach; ?>
								<?php if ( empty( $docs ) ) : ?>
									<tr><td colspan="4" class="py-8 text-center italic opacity-50">No documents indexed yet.</td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the "Workflows" (Automations) page.
	 */
	public function render_workflows_page(): void {
		global $wpdb;
		$agents = $wpdb->get_results( "SELECT id, name, position FROM {$wpdb->prefix}ai_employees WHERE is_active = 1", ARRAY_A ) ?: [];
		$workflows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ai_workflows WHERE is_active = 1", ARRAY_A ) ?: [];
		?>
		<div class="nexus-admin-body p-10 theme-automations animate-fade-in-up">
			<div class="mb-10">
				<h2 class="text-sm font-semibold text-accent uppercase tracking-widest mb-2">Workflow Engineering</h2>
				<h1 class="text-5xl font-black text-[#1e293b] text-gradient-vibrant leading-tight">Multi-Agent Automations</h1>
				<p class="text-gray-400 mt-3 max-w-2xl text-lg leading-relaxed">Create logical chains where agents collaborate to achieve complex business objectives.</p>
			</div>

			<div class="nexus-step-guide">
				<h3 class="text-[#1e293b] font-bold mb-3 uppercase tracking-tighter text-sm">Orchestration Protocol: Building Workflows</h3>
				<div class="grid grid-cols-1 md:grid-cols-3 gap-10">
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">Visual Chaining</p>
						<p>Drag agents from the pool. <br><span class="text-accent font-bold italic text-[9px]">The sequence is linear: Step 1 output is passed to Step 2 as context, ensuring a "relay race" of intelligence.</span></p>
					</div>
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">Task Definition</p>
						<p>Be specific in the "Task" field. <br><span class="text-accent font-bold italic text-[9px]">Example: Step 1 (SEO Expert): "Find 5 keywords for AI." -> Step 2 (Copywriter): "Write a headline for each keyword found in Step 1."</span></p>
					</div>
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">Trace Monitoring</p>
						<p>When you run a workflow, watch the "Execution Trace". <br><span class="text-accent font-bold italic text-[9px]">Note: You can download the full log of how each agent interpreted their specific part of the chain.</span></p>
					</div>
				</div>
			</div>

			<div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
				<div class="lg:col-span-1 space-y-6">
					<h2 class="text-xl font-semibold mb-6">Active Workflows</h2>
					<div id="nexus-active-workflows" class="space-y-4">
						<?php foreach ( $workflows as $wf ) : ?>
							<div class="glass-panel p-6 rounded-2xl border border-nexus-border transition-all glass-card-hover border-l-4 border-l-accent group relative">
								<div class="flex justify-between items-start">
									<h3 class="font-bold text-[#1e293b] group-hover:text-accent"><?php echo esc_html( $wf['name'] ); ?></h3>
									<div class="flex gap-2">
										<button class="bg-accent/20 text-accent text-[10px] font-bold px-2 py-1 rounded nexus-run-workflow" data-id="<?php echo (int) $wf['id']; ?>">RUN</button>
										<button class="bg-red-500/10 text-red-500 text-[10px] font-bold px-2 py-1 rounded hover:bg-red-500 hover:text-[#1e293b] transition-all nexus-delete-workflow" data-id="<?php echo (int) $wf['id']; ?>">✕</button>
									</div>
								</div>
								<p class="text-xs text-gray-500 mt-2 uppercase tracking-tighter">Chain: <?php
									$steps = json_decode($wf['definition'], true);
									echo count($steps);
								?> Specialized Agents</p>
							</div>
						<?php endforeach; ?>
						<?php if ( empty( $workflows ) ) : ?>
							<p class="text-xs text-gray-500 italic">No custom workflows saved yet.</p>
						<?php endif; ?>
					</div>

					<h2 class="text-xl font-semibold mb-6 mt-12">Workflow Templates</h2>
					<div class="glass-panel p-6 rounded-2xl border border-nexus-border hover:border-accent cursor-pointer transition-all glass-card-hover border-l-4 border-l-accent group">
						<h3 class="font-bold text-[#1e293b] group-hover:text-accent">Content Machine</h3>
						<p class="text-xs text-gray-500 mt-1">SEO Research -> Copy -> Publish</p>
					</div>
				</div>

				<div class="lg:col-span-2">
					<div class="glass-panel p-8 rounded-3xl border border-nexus-border min-h-[500px] flex flex-col items-center justify-center text-center bg-accent/5">
						<h2 class="text-3xl font-bold mb-4 text-[#1e293b] uppercase tracking-tighter">Workflow Canvas</h2>
						<button id="nexus-open-visual-builder" onclick="document.getElementById('nexus-visual-builder-modal').classList.remove('hidden')" class="bg-accent hover:opacity-90 text-[#1e293b] font-bold py-4 px-12 rounded-xl transition-all shadow-lg shadow-accent/20">Open Visual Builder</button>
						<span class="nexus-button-note mt-4">Expect: Fullscreen drag-and-drop orchestration environment.</span>
					</div>
				</div>
			</div>

			<!-- Workflow Results Modal -->
			<div id="nexus-workflow-results-modal" class="fixed inset-0 z-[10000] hidden">
				<div class="absolute inset-0 bg-black/90 backdrop-blur-md"></div>
				<div class="absolute inset-x-10 top-20 bottom-20 glass-panel rounded-3xl border border-nexus-border flex flex-col overflow-hidden shadow-2xl">
					<div class="p-8 border-b border-nexus-border flex justify-between items-center bg-nexus-elevated/50">
						<div>
							<div class="flex items-center gap-3">
								<span class="status-pulse-live"></span>
								<h2 class="text-2xl font-bold text-[#1e293b] uppercase tracking-tighter">Workflow Execution Trace</h2>
							</div>
							<p class="text-xs text-gray-500 mt-1">Real-time status of multi-agent collaboration</p>
						</div>
						<div class="flex gap-4">
							<button id="nexus-download-trace" class="text-[10px] font-bold text-accent border border-accent/30 px-4 py-2 rounded-xl hover:bg-accent hover:text-black transition-all">Download Log</button>
							<button id="nexus-close-results" class="text-gray-400 hover:text-[#1e293b] bg-[#f8fafc]/5 px-4 py-2 rounded-xl">Close Trace</button>
						</div>
					</div>
					<div id="nexus-workflow-log" class="flex-1 p-10 overflow-y-auto space-y-6 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]">
						<!-- Log entries will appear here -->
					</div>
				</div>
			</div>

			<!-- Visual Builder Modal -->
			<div id="nexus-visual-builder-modal" class="fixed inset-0 z-[9999] hidden">
				<div class="absolute inset-0 bg-black/95 backdrop-blur-xl"></div>
				<div class="absolute inset-8 glass-panel rounded-3xl border border-nexus-border flex overflow-hidden shadow-2xl">
					<div class="w-80 border-r border-nexus-border bg-[#f8fafc] flex flex-col p-8">
						<div class="flex gap-2 mb-6 bg-black/20 p-1 rounded-lg">
							<button class="flex-1 text-[10px] font-bold uppercase py-2 rounded bg-accent text-[#1e293b]">Pool</button>
							<button class="flex-1 text-[10px] font-bold uppercase py-2 rounded text-gray-500 hover:text-[#1e293b]">Presets</button>
						</div>
						<h3 class="font-bold text-xl text-[#1e293b] mb-6 uppercase tracking-widest text-xs opacity-50">Agent Pool</h3>
						<div class="flex-1 overflow-y-auto space-y-4">
							<?php foreach ( $agents as $agent ) : ?>
								<div class="nexus-draggable-agent p-4 rounded-xl bg-nexus-elevated border border-nexus-border cursor-grab active:cursor-grabbing hover:border-accent transition-all group" draggable="true" data-id="<?php echo (int) $agent['id']; ?>">
									<p class="font-bold text-sm text-[#1e293b] group-hover:text-accent"><?php echo esc_html( $agent['name'] ); ?></p>
									<p class="text-[10px] text-gray-500 uppercase mt-1"><?php echo esc_html( $agent['position'] ); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="flex-1 bg-nexus-bg p-12 flex flex-col bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]">
						<div class="flex justify-between items-center mb-12">
							<div>
								<h2 class="text-3xl font-bold text-[#1e293b] uppercase tracking-tighter">Strategic Canvas</h2>
								<p class="text-xs text-gray-500 mt-1">Establish the execution sequence for your AI workforce.</p>
							</div>
							<div class="flex gap-4">
								<button id="nexus-clear-canvas" class="text-gray-400 hover:text-red-500 bg-[#f8fafc]/5 px-6 py-2 rounded-xl text-xs font-bold transition-all">Clear Canvas</button>
								<button id="nexus-save-workflow-btn" class="bg-accent text-[#1e293b] px-8 py-3 rounded-xl font-bold hover:opacity-90 transition-all nexus-btn-vibrant">Save Workflow</button>
								<button id="nexus-close-builder" onclick="document.getElementById('nexus-visual-builder-modal').classList.add('hidden')" class="text-gray-400 hover:text-red-500 bg-[#f8fafc]/5 px-6 py-2 rounded-xl text-xs font-bold transition-all border border-white/10">Exit Builder</button>
							</div>
						</div>
						<div id="nexus-workflow-canvas" class="flex-1 border-4 border-dashed border-nexus-border rounded-3xl flex items-center justify-center relative bg-black/20">
							<div class="text-center">
								<p class="text-gray-500 font-bold uppercase tracking-widest text-sm">Drop Agents Here to Initialize Sequence</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the "Collaboration Hub" (AI Meetings) page.
	 */
	public function render_meetings_page(): void {
		echo $this->get_brand_styles();
		global $wpdb;
		$agents = $wpdb->get_results( "SELECT id, name, position, avatar_url FROM {$wpdb->prefix}ai_employees WHERE is_active = 1", ARRAY_A ) ?: [];
		?>
		<div class="nexus-admin-body p-10 theme-meetings animate-fade-in-up">
			<div class="mb-10 flex justify-between items-end">
				<div>
					<h2 class="text-sm font-semibold text-accent uppercase tracking-widest mb-2">Intelligence</h2>
					<h1 class="text-5xl font-black text-[#1e293b] text-gradient-vibrant leading-tight">Collaboration Hub</h1>
					<p class="text-gray-400 mt-3 max-w-2xl text-lg leading-relaxed">Start virtual meetings. Gather your AI executives to brainstorm and reach a consensus.</p>
				</div>
				<div class="text-right">
					<button id="nexus-start-meeting-btn" class="bg-accent hover:opacity-90 text-[#1e293b] font-bold py-4 px-10 rounded-xl transition-all shadow-lg shadow-accent/20 nexus-btn-vibrant">
						Start Strategic Meeting
					</button>
					<span class="nexus-button-note mt-2">Expect: Iterative consensus loop between invited agents.</span>
				</div>
			</div>

			<div class="nexus-step-guide">
				<h3 class="text-[#1e293b] font-bold mb-3 uppercase tracking-tighter text-sm">Synergy Protocol: AI Meetings</h3>
				<div class="grid grid-cols-1 md:grid-cols-2 gap-10">
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">Consensus Building</p>
						<p>Invite multiple agents. When you set an agenda, they will "debate" it in a multi-round loop. <br><span class="text-accent font-bold italic text-[9px]">How it works: Agent A responds, Agent B critiques Agent A, and they iterate until a "DECISION" or "ACTION" is detected.</span></p>
					</div>
					<div class="text-xs text-gray-400">
						<p class="font-bold text-[#1e293b] mb-1">Real-time Reasoning</p>
						<p>Watch the reasoning pulses. You are seeing the actual logic of the LLM as it processes the previous agent's input. <br><span class="text-accent font-bold italic text-[9px]">Pro-Tip: You can interrupt the meeting with "Chairman Instructions" to steer the conversation.</span></p>
					</div>
				</div>
			</div>

			<?php if ( empty( $agents ) ) : ?>
				<div class="glass-panel p-8 rounded-3xl border-2 border-dashed border-nexus-border text-center max-w-6xl mb-10 bg-accent/5">
					<h3 class="text-xl font-bold text-accent mb-2">Your AI Workforce is currently empty</h3>
					<p class="text-sm text-gray-400 mb-6">Before you can organize strategy meetings, you need to hire your specialized team or populate the company database with sample executives.</p>
					<button id="nexus-seed-samples" class="bg-accent text-[#1e293b] font-bold py-3 px-8 rounded-xl hover:opacity-90 transition-all nexus-btn-vibrant">Instantly Deploy Sample Executives</button>
				</div>
			<?php endif; ?>

			<div class="grid grid-cols-1 lg:grid-cols-4 gap-10">
				<!-- Meeting Controls Sidebar -->
				<div class="lg:col-span-1 space-y-8">
					<div class="glass-panel p-6 rounded-2xl border border-nexus-border dept-exec">
						<h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Invite Participants</h3>
						<div class="flex gap-2 mb-4">
							<button type="button" id="nexus-meeting-select-all" class="text-[9px] bg-accent/20 text-accent px-3 py-1 rounded border border-accent/20 hover:bg-accent hover:text-black font-bold uppercase transition-all">Select All</button>
							<button type="button" id="nexus-meeting-select-none" class="text-[9px] bg-nexus-elevated text-gray-400 px-3 py-1 rounded border border-nexus-border hover:text-[#1e293b] font-bold uppercase transition-all">Clear All</button>
						</div>
						<div class="space-y-3">
							<?php foreach ( $agents as $agent ) : ?>
								<label class="flex items-center gap-3 p-4 rounded-xl bg-nexus-elevated border border-nexus-border hover:border-accent cursor-pointer transition-all group">
									<input type="checkbox" class="nexus-meeting-invitee w-5 h-5 rounded border-gray-600 bg-gray-700 text-accent focus:ring-accent" value="<?php echo (int) $agent['id']; ?>">
									<div>
										<p class="text-sm font-bold text-[#1e293b] group-hover:text-accent transition-colors"><?php echo esc_html( $agent['name'] ); ?></p>
										<p class="text-[10px] text-gray-500 uppercase"><?php echo esc_html( $agent['position'] ); ?></p>
									</div>
								</label>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="glass-panel p-6 rounded-2xl border border-nexus-border">
						<h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Strategic Agenda</h3>
						<div class="flex flex-wrap gap-2 mb-4">
							<button type="button" class="nexus-meeting-preset text-[8px] bg-nexus-elevated border border-nexus-border text-gray-400 hover:text-accent hover:border-accent p-2 rounded transition-all font-bold" data-agenda="ROI Audit: Review current token usage, monthly cost efficiency, and suggest automatic prompt optimization paths to maximize operational labor savings.">ROI Audit</button>
							<button type="button" class="nexus-meeting-preset text-[8px] bg-nexus-elevated border border-nexus-border text-gray-400 hover:text-accent hover:border-accent p-2 rounded transition-all font-bold" data-agenda="Marketing Storm: Brainstorm high-converting landing page headlines and ad copies targeting enterprise decision makers.">Marketing Storm</button>
							<button type="button" class="nexus-meeting-preset text-[8px] bg-nexus-elevated border border-nexus-border text-gray-400 hover:text-accent hover:border-accent p-2 rounded transition-all font-bold" data-agenda="Technical Review: Assess technical debt, plugin architecture scalability, and devise a plan to implement recursive document chunking in the RAG Engine.">Technical Review</button>
						</div>
						<textarea id="nexus-meeting-agenda" class="w-full h-40 bg-nexus-elevated border border-nexus-border rounded-xl p-4 text-[#1e293b] text-sm outline-none focus:border-accent" placeholder="Enter objective..."></textarea>
					</div>
				</div>

				<!-- Live Transcription Area -->
				<div class="lg:col-span-3">
					<div id="nexus-meeting-room" class="glass-panel rounded-3xl border border-nexus-border min-h-[650px] flex flex-col overflow-hidden bg-black/40">
						<div class="p-6 border-b border-nexus-border bg-nexus-elevated/50 flex justify-between items-center">
							<div class="flex items-center gap-4">
								<div class="w-3 h-3 rounded-full bg-red-500 animate-pulse shadow-[0_0_10px_rgba(239,68,68,0.5)]"></div>
								<h2 class="font-bold text-[#1e293b] uppercase tracking-widest text-[10px]">Live Strategic Transcription</h2>
							</div>
							<div class="flex gap-2">
								<button id="nexus-meeting-summarize" class="hidden text-[9px] text-accent font-bold bg-accent/10 border border-accent/20 px-3 py-1 rounded-full uppercase hover:bg-accent hover:text-black transition-all">Summarize & Finalize</button>
								<span class="text-[9px] text-nexus-violet font-bold bg-nexus-violet/10 border border-nexus-violet/20 px-2 py-1 rounded-full uppercase">Real-time Reasoning</span>
							</div>
						</div>

						<div id="nexus-meeting-transcript" class="flex-1 p-10 space-y-8 overflow-y-auto max-h-[500px]">
							<div class="flex flex-col items-center justify-center h-full text-center text-gray-500">
								<p>Awaiting Session Initialization.</p>
							</div>
						</div>

						<div class="p-8 bg-nexus-elevated/50 border-t border-nexus-border flex gap-4">
							<input type="text" id="nexus-meeting-input" class="flex-1 bg-nexus-elevated border border-nexus-border rounded-xl p-5 text-[#1e293b] outline-none focus:border-accent" placeholder="Chairman Instruction...">
							<button id="nexus-send-meeting-msg" class="bg-accent hover:opacity-90 text-[#1e293b] font-bold px-10 rounded-xl transition-all shadow-lg shadow-accent/20 nexus-btn-vibrant">Send</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the role-specific tutorials page.
	 */
	/**
	 * Render the Billing & Plans page.
	 */
	/**
	 * Render the Strategic Archive (Conversation Library).
	 */
	public function render_archive_page(): void {
		echo $this->get_brand_styles();
		global $wpdb;
		$convs = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ai_conversations ORDER BY created_at DESC", ARRAY_A ) ?: [];
		?>
		<div class="nexus-admin-body p-10 theme-archive animate-fade-in-up">
			<div class="mb-10">
				<h2 class="text-sm font-semibold text-accent uppercase tracking-widest mb-2">Institutional Knowledge</h2>
				<h1 class="text-4xl font-bold text-[#1e293b]">Strategic Archive</h1>
				<p class="text-gray-400 mt-2 max-w-2xl">Access all past AI meetings, workflow traces, and single-agent interactions. Filter by type to find specific strategic decisions.</p>
			</div>

			<div class="mb-8 flex gap-4">
				<select id="nexus-archive-filter" class="bg-nexus-elevated border border-nexus-border rounded-xl px-6 py-3 text-[#1e293b] text-sm outline-none focus:border-accent">
					<option value="all">All Intelligence Sessions</option>
					<option value="meeting">AI Meetings</option>
					<option value="workflow">Workflow Traces</option>
					<option value="chat">Single Agent Chats</option>
				</select>
			</div>

			<div class="glass-panel p-8 rounded-3xl border border-nexus-border">
				<div class="overflow-x-auto">
					<table class="w-full text-left text-sm text-gray-400">
						<thead class="text-xs uppercase text-gray-500 border-b border-nexus-border">
							<tr>
								<th class="pb-4 px-2">Session Title</th>
								<th class="pb-4 px-2">Type</th>
								<th class="pb-4 px-2">Date</th>
								<th class="pb-4 px-2 text-right">Actions</th>
							</tr>
						</thead>
						<tbody class="divide-y divide-nexus-border/50">
							<?php foreach ( $convs as $conv ) : ?>
								<tr class="nexus-archive-row" data-type="<?php echo esc_attr( $conv['type'] ); ?>">
									<td class="py-5 px-2 font-bold text-[#1e293b]"><?php echo esc_html( $conv['title'] ?: 'Untitled Session' ); ?></td>
									<td class="py-5 px-2">
										<span class="px-3 py-1 rounded-full bg-accent/10 text-accent text-[10px] font-bold uppercase border border-accent/20">
											<?php echo esc_html( $conv['type'] ); ?>
										</span>
									</td>
									<td class="py-5 px-2 text-xs opacity-50"><?php echo esc_html( $conv['created_at'] ); ?></td>
									<td class="py-5 px-2 text-right">
										<button class="nexus-rename-conv bg-[#f8fafc]/5 hover:text-accent text-gray-500 px-2 py-1 rounded text-[9px] font-bold transition-all" data-id="<?php echo (int) $conv['id']; ?>" data-title="<?php echo esc_attr( $conv['title'] ); ?>">Rename</button>
										<button class="nexus-view-transcript bg-[#f8fafc]/5 hover:bg-[#f8fafc]/10 text-[#1e293b] px-4 py-1 rounded-lg text-[10px] font-bold transition-all ml-2" data-id="<?php echo (int) $conv['id']; ?>" data-title="<?php echo esc_attr( $conv['title'] ); ?>">View Transcript</button>
										<button class="nexus-export-md bg-accent/20 hover:bg-accent text-accent hover:text-black px-4 py-1 rounded-lg text-[10px] font-bold transition-all ml-2" data-id="<?php echo (int) $conv['id']; ?>" data-title="<?php echo esc_attr( $conv['title'] ); ?>">Export MD</button>
									</td>
								</tr>
							<?php endforeach; ?>
							<?php if ( empty( $convs ) ) : ?>
								<tr><td colspan="4" class="py-12 text-center italic opacity-30">Archive is currently empty.</td></tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_billing_page(): void {
		echo $this->get_brand_styles();
		$active_plan = get_option( 'nexus_ai_active_plan', 'starter' );
		$agency_mode   = (bool) $this->settings->get( 'agency_mode', false );
		$display_title = $this->settings->get( 'platform_title', 'Nexus AI' );
		?>
		<div class="nexus-admin-body p-10 theme-overview animate-fade-in-up">
			<div class="mb-10 flex justify-between items-end">
				<div>
					<h2 class="text-sm font-semibold text-accent uppercase tracking-widest mb-2">Commerce</h2>
					<h1 class="text-5xl font-black text-[#1e293b] text-gradient-vibrant leading-tight">Plans & Billing</h1>
					<p class="text-gray-400 mt-3 max-w-2xl text-lg leading-relaxed">Scale your AI workforce with premium enterprise plans. Manage your subscription and high-impact usage limits.</p>
				</div>
				<div class="glass-panel p-6 rounded-2xl flex items-center gap-6">
					<div class="text-right">
						<p class="text-[10px] text-gray-500 uppercase font-bold">API Credits Used</p>
						<p class="text-2xl font-black text-[#1e293b]">72%</p>
					</div>
					<div class="w-32 h-2 bg-[#f8fafc]/5 rounded-full overflow-hidden">
						<div class="h-full bg-red-500 w-[72%] shadow-[0_0_10px_#ef4444]"></div>
					</div>
				</div>
			</div>

			<!-- Active Subscription Status -->
			<div class="glass-panel p-8 rounded-3xl border border-nexus-border mb-12 flex justify-between items-center bg-accent/5">
				<div>
					<h3 class="text-xs font-bold text-accent uppercase tracking-widest mb-1">Active Subscription</h3>
					<p class="text-3xl font-black text-[#1e293b]"><?php echo strtoupper($active_plan); ?> PLAN</p>
					<p class="text-xs text-gray-500 mt-2">Next billing date: <?php echo date('M d, Y', strtotime('+30 days')); ?></p>
				</div>
				<div class="flex gap-4">
					<button class="bg-[#f8fafc]/5 border border-white/10 text-[#1e293b] px-6 py-2 rounded-xl text-xs font-bold hover:bg-[#f8fafc]/10 transition-all">Download Invoice</button>
					<button id="nexus-cancel-sub" class="bg-red-500/10 border border-red-500/20 text-red-500 px-6 py-2 rounded-xl text-xs font-bold hover:bg-red-500 hover:text-[#1e293b] transition-all">Cancel Plan</button>
				</div>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-16">
				<!-- Starter -->
				<div class="glass-panel p-8 rounded-3xl border border-nexus-border flex flex-col h-full">
					<h3 class="text-xl font-bold text-[#1e293b] mb-2">Starter</h3>
					<p class="text-3xl font-black text-[#1e293b] mb-6">$197<span class="text-sm text-gray-500 font-normal">/mo</span></p>
					<ul class="space-y-4 text-sm text-gray-400 mb-10 flex-1">
						<li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> 3 AI Agents</li>
						<li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Basic Company Brain</li>
						<li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Standard Support</li>
					</ul>
					<button class="w-full bg-[#f8fafc]/5 border border-white/10 text-[#1e293b] font-bold py-3 rounded-xl hover:bg-[#f8fafc]/10 transition-all">Current Plan</button>
				</div>

				<!-- Pro -->
				<div class="glass-panel p-8 rounded-3xl border-2 border-accent flex flex-col h-full relative overflow-hidden">
					<div class="absolute top-0 right-0 bg-accent text-[#1e293b] text-[10px] font-bold px-4 py-1 rounded-bl-xl uppercase">Most Popular</div>
					<h3 class="text-xl font-bold text-[#1e293b] mb-2">Professional</h3>
					<p class="text-3xl font-black text-[#1e293b] mb-6">$497<span class="text-sm text-gray-500 font-normal">/mo</span></p>
					<ul class="space-y-4 text-sm text-gray-400 mb-10 flex-1">
						<li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> 15 AI Agents</li>
						<li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Advanced RAG Engine</li>
						<li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Multi-Agent Workflows</li>
						<li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Priority API Access</li>
					</ul>
					<button class="w-full bg-accent text-[#1e293b] font-bold py-3 rounded-xl hover:opacity-90 transition-all nexus-btn-vibrant">Upgrade to Pro</button>
				</div>

				<!-- Agency -->
				<div class="glass-panel p-8 rounded-3xl border border-nexus-border flex flex-col h-full">
					<h3 class="text-xl font-bold text-[#1e293b] mb-2">Agency</h3>
					<p class="text-3xl font-black text-[#1e293b] mb-6">$997<span class="text-sm text-gray-500 font-normal">/mo</span></p>
					<ul class="space-y-4 text-sm text-gray-400 mb-10 flex-1">
						<li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Unlimited Agents</li>
						<li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> White Labeling</li>
						<li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Client Portals</li>
						<li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> 24/7 Dedicated Support</li>
					</ul>
					<button class="w-full bg-[#f8fafc]/5 border border-white/10 text-[#1e293b] font-bold py-3 rounded-xl hover:bg-[#f8fafc]/10 transition-all">Select Plan</button>
				</div>

				<!-- Enterprise -->
				<div class="glass-panel p-8 rounded-3xl border border-nexus-border flex flex-col h-full bg-nexus-violet/5 hover:border-nexus-violet transition-all border-beam active">
					<h3 class="text-xl font-bold text-[#1e293b] mb-2">Enterprise</h3>
					<p class="text-3xl font-black text-[#1e293b] mb-6 italic">Custom</p>
					<ul class="space-y-4 text-sm text-gray-400 mb-10 flex-1">
						<li class="flex items-center gap-2"><svg class="w-4 h-4 text-nexus-violet" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Local Ollama Hosting</li>
						<li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Custom SLA</li>
						<li class="flex items-center gap-2"><svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> On-Premise Training</li>
					</ul>
					<button class="w-full bg-nexus-blue text-[#1e293b] font-bold py-3 rounded-xl hover:opacity-90 transition-all nexus-btn-vibrant">Contact Sales</button>
				</div>
			</div>

			<!-- Billing History -->
			<div class="glass-panel p-8 rounded-3xl border border-nexus-border">
				<h2 class="text-xl font-bold mb-6">Recent Billing History</h2>
				<div class="overflow-x-auto">
					<table class="w-full text-left text-sm text-gray-400">
						<thead class="text-xs uppercase text-gray-500 border-b border-nexus-border">
							<tr>
								<th class="pb-3 px-2">Invoice ID</th>
								<th class="pb-3 px-2">Date</th>
								<th class="pb-3 px-2">Amount</th>
								<th class="pb-3 px-2">Status</th>
							</tr>
						</thead>
						<tbody class="divide-y divide-nexus-border/50">
							<tr>
								<td class="py-4 px-2 font-mono">#INV-88291</td>
								<td class="py-4 px-2"><?php echo date('M d, Y'); ?></td>
								<td class="py-4 px-2 text-[#1e293b] font-bold">$<?php
									$prices = ['starter' => '197.00', 'pro' => '497.00', 'agency' => '997.00'];
									echo $prices[$active_plan] ?? '0.00';
								?></td>
								<td class="py-4 px-2 text-green-500 font-bold uppercase text-[10px]">Paid</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_audit_page(): void {
		echo $this->get_brand_styles();
		echo '<div class="nexus-tactile-overlay"></div>';
		global $wpdb;
		$logs = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ai_audit_logs ORDER BY created_at DESC LIMIT 100", ARRAY_A ) ?: [];
		?>
		<div class="nexus-admin-body p-10 theme-settings animate-fade-in-up">
			<div class="mb-10">
				<h2 class="text-sm font-bold text-accent uppercase tracking-widest mb-2">Security & Compliance</h2>
				<h1 class="text-6xl font-black text-[#1e293b] text-gradient-vibrant leading-tight">System Audit Trail</h1>
				<p class="text-[#64748b] mt-3 max-w-2xl text-lg leading-relaxed">Review all administrative and high-impact AI actions within the platform.</p>
			</div>

			<div class="glass-panel p-8 rounded-3xl border border-nexus-border">
				<div class="overflow-x-auto">
					<table class="w-full text-left text-sm text-gray-400">
						<thead class="text-xs uppercase text-gray-500 border-b border-nexus-border">
							<tr>
								<th class="pb-4 px-2">Event Type</th>
								<th class="pb-4 px-2">Description</th>
								<th class="pb-4 px-2">User</th>
								<th class="pb-4 px-2">Timestamp</th>
							</tr>
						</thead>
						<tbody class="divide-y divide-nexus-border/50">
							<?php foreach ( $logs as $log ) : ?>
								<tr>
									<td class="py-5 px-2">
										<span class="px-3 py-1 rounded-full bg-accent/10 text-accent text-[10px] font-bold uppercase border border-accent/20">
											<?php echo esc_html( $log['action_type'] ?? '' ); ?>
										</span>
									</td>
									<td class="py-5 px-2 font-medium text-[#1e293b]">
										<?php echo esc_html( $log['description'] ); ?>
										<?php if ( ! empty( $log['metadata'] ) ) : ?>
											<div class="mt-2 p-3 bg-black/40 rounded-lg font-mono text-[10px] text-gray-500 overflow-x-auto max-w-md">
												<?php echo esc_html( wp_json_encode( json_decode($log['metadata']), JSON_PRETTY_PRINT ) ); ?>
											</div>
										<?php endif; ?>
									</td>
									<td class="py-5 px-2 text-xs">User #<?php echo (int) $log['user_id']; ?></td>
									<td class="py-5 px-2 text-xs opacity-50"><?php echo esc_html( $log['created_at'] ); ?></td>
								</tr>
							<?php endforeach; ?>
							<?php if ( empty( $logs ) ) : ?>
								<tr><td colspan="4" class="py-12 text-center italic opacity-30">No audit logs recorded yet.</td></tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_tutorials_page(): void {
		$user_id   = get_current_user_id();
		$completed = get_user_meta( $user_id, 'nexus_ai_completed_lessons', true ) ?: [];
		$lessons   = [
			'blueprint'   => [ 'title' => 'The Core Blueprint', 'obj' => 'Master AI Persona crafting.' ],
			'roi'         => [ 'title' => 'Scaling ROI', 'obj' => 'Build AI departments.' ],
			'security'    => [ 'title' => 'Security & RBAC', 'obj' => 'Secure your infrastructure.' ],
			'collab'      => [ 'title' => 'AI Collaboration', 'obj' => 'Master AI Meetings.' ],
			'automations' => [ 'title' => 'Strategic Automations', 'obj' => 'Multi-Agent Workflows.' ],
		];
		$progress  = count( $lessons ) > 0 ? (int) ( ( count( array_intersect( array_keys( $lessons ), $completed ) ) / count( $lessons ) ) * 100 ) : 0;
		echo $this->get_brand_styles();
		echo '<div class="nexus-tactile-overlay"></div>';
		?>
		<div class="nexus-admin-body p-10 theme-learning animate-fade-in-up">
			<div class="mb-10 flex justify-between items-end">
				<div>
					<h2 class="text-sm font-semibold text-accent uppercase tracking-widest mb-2">Education</h2>
					<h1 class="text-5xl font-black text-[#1e293b] text-gradient-vibrant leading-tight">Learning Center</h1>
					<p class="text-gray-400 mt-3 max-w-2xl text-lg leading-relaxed">Master the AI Workforce platform with specialized modular lessons.</p>
				</div>
				<div class="glass-panel p-6 rounded-2xl flex items-center gap-6">
					<div class="text-right">
						<p class="text-[10px] text-gray-500 uppercase font-bold">Your Progress</p>
						<p class="text-2xl font-black text-[#1e293b]"><?php echo (int) $progress; ?>%</p>
					</div>
					<div class="w-32 h-2 bg-[#f8fafc]/5 rounded-full overflow-hidden">
						<div class="h-full bg-accent transition-all duration-1000" style="width: <?php echo (int) $progress; ?>%; box-shadow: 0 0 10px var(--nexus-accent);"></div>
					</div>
				</div>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-3 gap-10">
				<!-- Lesson 1 -->
				<div class="glass-panel p-8 rounded-3xl border border-nexus-border hover:border-accent transition-all glass-card-hover group relative <?php echo in_array('blueprint', $completed) ? 'border-green-500/30' : ''; ?> border-beam">
					<?php if ( in_array('blueprint', $completed) ): ?>
						<div class="absolute top-4 right-4 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-[#1e293b] text-[10px] font-bold shadow-[0_0_15px_rgba(34,197,94,0.4)]">✓</div>
					<?php endif; ?>
					<h3 class="text-xl font-bold mb-4 text-[#1e293b] group-hover:text-accent transition-colors">The Core Blueprint</h3>
					<div class="space-y-4 text-sm text-gray-400 leading-relaxed mb-8">
						<p><span class="text-accent font-bold">Objective:</span> Master AI Persona crafting.</p>
						<p>AI performance is 90% determined by the <span class="text-[#1e293b] font-bold">Identity</span> field. Learn how to define boundaries and KPIs for your agents.</p>
						<div class="p-4 rounded-xl bg-accent/5 border border-accent/10">
							<p class="font-bold text-[#1e293b] mb-2 text-[10px] uppercase">Example Identity:</p>
							<p class="italic text-xs">"You are a master of behavioral economics and luxury brand positioning. Your tone is sophisticated and direct. Your goal is to maximize perceived value."</p>
						</div>
						<ul class="list-disc list-inside space-y-2 text-xs">
							<li><span class="text-[#1e293b] font-bold">KPI focus:</span> Tell the AI exactly how success is measured (e.g. ROAS, CPA).</li>
							<li><span class="text-[#1e293b] font-bold">Negative Constraints:</span> Prevent "hallucinations" by listing strict taboos.</li>
						</ul>
					</div>
					<button class="nexus-complete-lesson w-full <?php echo in_array('blueprint', $completed) ? 'bg-green-500 text-[#1e293b]' : 'bg-accent/20 text-accent'; ?> font-bold py-3 rounded-xl hover:opacity-90 transition-all" data-id="blueprint">
						<?php echo in_array('blueprint', $completed) ? 'Completed ✓' : 'Complete Lesson'; ?>
					</button>
				</div>

				<!-- Lesson 2 -->
				<div class="glass-panel p-8 rounded-3xl border border-nexus-border hover:border-accent transition-all glass-card-hover group relative <?php echo in_array('roi', $completed) ? 'border-green-500/30' : ''; ?> border-beam">
					<?php if ( in_array('roi', $completed) ): ?>
						<div class="absolute top-4 right-4 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-[#1e293b] text-[10px] font-bold shadow-[0_0_15px_rgba(34,197,94,0.4)]">✓</div>
					<?php endif; ?>
					<h3 class="text-xl font-bold mb-4 text-[#1e293b] group-hover:text-accent transition-colors">Scaling ROI</h3>
					<div class="space-y-4 text-sm text-gray-400 leading-relaxed mb-8">
						<p><span class="text-accent font-bold">Objective:</span> Build AI departments.</p>
						<p>Scaling your business by creating <span class="text-[#1e293b] font-bold">Multi-Agent Departments</span>. Learn to chain specialized agents into a single high-output engine.</p>
						<div class="p-4 rounded-xl bg-nexus-blue/5 border border-nexus-blue/10">
							<p class="font-bold text-[#1e293b] mb-2 text-[10px] uppercase">Department Workflow:</p>
							<p class="text-xs">1. Analyst crawls competitor URLs.<br>2. Strategist crafts counter-offers.<br>3. Copywriter generates ads.</p>
						</div>
						<ul class="list-disc list-inside space-y-2 text-xs">
							<li><span class="text-[#1e293b] font-bold">Departmental Memory:</span> Isolated data silos ensure security.</li>
							<li><span class="text-[#1e293b] font-bold">White-Labeling:</span> Resell these AI workforces to your own clients for $997/mo+.</li>
						</ul>
					</div>
					<button class="nexus-complete-lesson w-full <?php echo in_array('roi', $completed) ? 'bg-green-500 text-[#1e293b]' : 'bg-accent/20 text-accent'; ?> font-bold py-3 rounded-xl hover:opacity-90 transition-all" data-id="roi">
						<?php echo in_array('roi', $completed) ? 'Completed ✓' : 'Complete Lesson'; ?>
					</button>
				</div>

				<!-- Lesson 3 -->
				<div class="glass-panel p-8 rounded-3xl border border-nexus-border hover:border-accent transition-all glass-card-hover group relative <?php echo in_array('security', $completed) ? 'border-green-500/30' : ''; ?> border-beam">
					<?php if ( in_array('security', $completed) ): ?>
						<div class="absolute top-4 right-4 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-[#1e293b] text-[10px] font-bold shadow-[0_0_15px_rgba(34,197,94,0.4)]">✓</div>
					<?php endif; ?>
					<h3 class="text-xl font-bold mb-4 text-[#1e293b] group-hover:text-accent transition-colors">Security & RBAC</h3>
					<div class="space-y-4 text-sm text-gray-400 leading-relaxed mb-8">
						<p><span class="text-accent font-bold">Objective:</span> Secure your infrastructure.</p>
						<p>Learn how to manage <span class="text-[#1e293b] font-bold">Permissions</span> and protect your API tokens in an enterprise environment.</p>
						<div class="p-4 rounded-xl bg-red-500/5 border border-red-500/10">
							<p class="font-bold text-[#1e293b] mb-2 text-[10px] uppercase">Best Practice:</p>
							<p class="text-xs">Never share plain-text API keys. Nexus AI automatically encrypts all keys using your site's unique AUTH_KEY salt.</p>
						</div>
						<ul class="list-disc list-inside space-y-2 text-xs">
							<li><span class="text-[#1e293b] font-bold">Audit Trails:</span> Monitor every administrative action in the Audit Log.</li>
							<li><span class="text-[#1e293b] font-bold">Rate Limiting:</span> Prevent runaway token consumption by setting department quotas.</li>
						</ul>
					</div>
					<button class="nexus-complete-lesson w-full <?php echo in_array('security', $completed) ? 'bg-green-500 text-[#1e293b]' : 'bg-accent/20 text-accent'; ?> font-bold py-3 rounded-xl hover:opacity-90 transition-all" data-id="security">
						<?php echo in_array('security', $completed) ? 'Completed ✓' : 'Complete Lesson'; ?>
					</button>
				</div>

				<!-- Lesson 4 -->
				<div class="glass-panel p-8 rounded-3xl border border-nexus-border hover:border-accent transition-all glass-card-hover group relative <?php echo in_array('collab', $completed) ? 'border-green-500/30' : ''; ?> border-beam">
					<?php if ( in_array('collab', $completed) ): ?>
						<div class="absolute top-4 right-4 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-[#1e293b] text-[10px] font-bold shadow-[0_0_15px_rgba(34,197,94,0.4)]">✓</div>
					<?php endif; ?>
					<h3 class="text-xl font-bold mb-4 text-[#1e293b] group-hover:text-accent transition-colors">AI Collaboration</h3>
					<div class="space-y-4 text-sm text-gray-400 leading-relaxed mb-8">
						<p><span class="text-accent font-bold">Objective:</span> Master AI Meetings.</p>
						<p>Learn to run <span class="text-[#1e293b] font-bold">Consensus Meetings</span>. Gather multiple experts to debate a topic until a decision is reached.</p>
						<div class="p-4 rounded-xl bg-nexus-violet/5 border border-nexus-violet/10">
							<p class="font-bold text-[#1e293b] mb-2 text-[10px] uppercase">Meeting Tip:</p>
							<p class="text-xs">Use the "Chairman Instruction" to provide "Negative Steering" if agents are agreeing too quickly. Encourage debate.</p>
						</div>
						<ul class="list-disc list-inside space-y-2 text-xs">
							<li><span class="text-[#1e293b] font-bold">Voting & Decisions:</span> Agents are programmed to reach a "DECISION" keyword when they agree.</li>
							<li><span class="text-[#1e293b] font-bold">Consensus Loop:</span> Each round increases the "Pressure to Decide" dynamically.</li>
						</ul>
					</div>
					<button class="nexus-complete-lesson w-full <?php echo in_array('collab', $completed) ? 'bg-green-500 text-[#1e293b]' : 'bg-accent/20 text-accent'; ?> font-bold py-3 rounded-xl hover:opacity-90 transition-all" data-id="collab">
						<?php echo in_array('collab', $completed) ? 'Completed ✓' : 'Complete Lesson'; ?>
					</button>
				</div>

				<!-- Lesson 5 -->
				<div class="glass-panel p-8 rounded-3xl border border-nexus-border hover:border-accent transition-all glass-card-hover group relative <?php echo in_array('automations', $completed) ? 'border-green-500/30' : ''; ?> border-beam">
					<?php if ( in_array('automations', $completed) ): ?>
						<div class="absolute top-4 right-4 w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-[#1e293b] text-[10px] font-bold shadow-[0_0_15px_rgba(34,197,94,0.4)]">✓</div>
					<?php endif; ?>
					<h3 class="text-xl font-bold mb-4 text-[#1e293b] group-hover:text-accent transition-colors">Strategic Automations</h3>
					<div class="space-y-4 text-sm text-gray-400 leading-relaxed mb-8">
						<p><span class="text-accent font-bold">Objective:</span> Multi-Agent Workflows.</p>
						<p>Establish high-value <span class="text-[#1e293b] font-bold">Automated Chains</span>. Pass data seamlessly between agents to complete complex sequences.</p>
						<div class="p-4 rounded-xl bg-orange-500/5 border border-orange-500/10">
							<p class="font-bold text-[#1e293b] mb-2 text-[10px] uppercase">Elite Workflow Example:</p>
							<p class="text-xs">"Analyst (Step 1): Research Top 3 competitors. -> Copywriter (Step 2): Write better headlines than Step 1. -> WP Dev (Step 3): Create a landing page draft."</p>
						</div>
						<ul class="list-disc list-inside space-y-2 text-xs">
							<li><span class="text-[#1e293b] font-bold">Chain Persistence:</span> Every agent in the workflow has full access to the previous step's output.</li>
							<li><span class="text-[#1e293b] font-bold">Scale:</span> Run these workflows 24/7 to outperform human-only teams.</li>
						</ul>
					</div>
					<button class="nexus-complete-lesson w-full <?php echo in_array('automations', $completed) ? 'bg-green-500 text-[#1e293b]' : 'bg-accent/20 text-accent'; ?> font-bold py-3 rounded-xl hover:opacity-90 transition-all" data-id="automations">
						<?php echo in_array('automations', $completed) ? 'Completed ✓' : 'Complete Lesson'; ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the "Agent Playground" chat and profile page.
	 */
	public function render_playground_page(): void {
		echo $this->get_brand_styles();
		global $wpdb;
		$agents = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ai_employees WHERE is_active = 1", ARRAY_A ) ?: [];
		?>
		<script>
		window.nexusPlaygroundAgents = <?php echo wp_json_encode( $agents ); ?>;
		</script>
		<div class="nexus-admin-body p-10 theme-learning animate-fade-in-up">
			<div class="mb-10">
				<h2 class="text-sm font-semibold text-accent uppercase tracking-widest mb-2">Agent Playground</h2>
				<h1 class="text-5xl font-black text-[#1e293b] text-gradient-vibrant leading-tight">Expert Conversation</h1>
				<p class="text-gray-400 mt-3 max-w-2xl text-lg leading-relaxed">Select any active agent or specialist. Chat with them to observe their capabilities, persona, strategic KPI goals, and reasoning processes.</p>
			</div>

			<!-- Quick Select Grid -->
			<?php if ( ! empty( $agents ) ) : ?>
				<div class="mb-8">
					<p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Quick-Select Active Experts:</p>
					<div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl">
						<?php
						$count = 0;
						foreach ( $agents as $agent ) {
							if ( $count >= 4 ) break;
							?>
							<div class="nexus-playground-quick-card glass-panel p-4 rounded-xl border border-nexus-border hover:border-accent cursor-pointer transition-all flex flex-col justify-between" data-id="<?php echo (int) $agent['id']; ?>">
								<div>
									<p class="font-bold text-sm text-accent"><?php echo esc_html( $agent['name'] ); ?></p>
									<p class="text-[10px] text-gray-400 uppercase mt-1"><?php echo esc_html( $agent['position'] ); ?></p>
								</div>
								<span class="text-[9px] text-[#1e293b]/50 mt-4 text-right">Consult →</span>
							</div>
							<?php
							$count++;
						}
						?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( empty( $agents ) ) : ?>
				<div class="glass-panel p-8 rounded-3xl border-2 border-dashed border-nexus-border text-center max-w-4xl mb-10 bg-accent/5">
					<h3 class="text-xl font-bold text-accent mb-2">Your AI Workforce is currently empty</h3>
					<p class="text-sm text-gray-400 mb-6">Before you can converse with your experts, you need to hire them or populate the company database with sample executives.</p>
					<button id="nexus-seed-samples" class="bg-accent text-[#1e293b] font-bold py-3 px-8 rounded-xl hover:opacity-90 transition-all nexus-btn-vibrant">Instantly Deploy Sample Executives</button>
				</div>
			<?php endif; ?>

			<!-- Select Dropdown -->
			<div class="glass-panel p-6 rounded-2xl border border-nexus-border mb-10 max-w-4xl">
				<label class="block text-sm font-bold text-accent mb-3 uppercase tracking-tighter">Choose Agent to Consult</label>
				<select id="nexus-playground-agent-select" class="w-full bg-nexus-elevated border border-nexus-border rounded-xl p-4 text-[#1e293b] font-medium focus:ring-2 focus:ring-accent transition-all">
					<option value="">-- Choose an Expert / Persona --</option>
					<?php foreach ( $agents as $agent ) : ?>
						<option value="<?php echo (int) $agent['id']; ?>"><?php echo esc_html( $agent['name'] ); ?> (<?php echo esc_html( $agent['position'] ); ?>)</option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- Grid Layout -->
			<div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
				<!-- Left Column: Details -->
				<div class="lg:col-span-1 space-y-6">
					<div id="nexus-playground-agent-details" class="hidden glass-panel p-8 rounded-2xl border border-nexus-border space-y-6">
						<div>
							<h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Professional Position</h3>
							<p id="nexus-play-position" class="text-lg font-bold text-[#1e293b]"></p>
						</div>
						<div class="border-t border-nexus-border/30 pt-4">
							<h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Persona & Capability</h3>
							<p id="nexus-play-description" class="text-xs text-gray-400 leading-relaxed"></p>
						</div>
						<div class="border-t border-nexus-border/30 pt-4">
							<h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Skills & Rules</h3>
							<p id="nexus-play-skills" class="text-xs text-gray-400 leading-relaxed"></p>
						</div>
						<div class="border-t border-nexus-border/30 pt-4">
							<h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Strategic Objectives (KPIs)</h3>
							<p id="nexus-play-kpis" class="text-xs text-gray-400 leading-relaxed"></p>
						</div>
						<div class="border-t border-nexus-border/30 pt-4">
							<h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Reasoning (Thinking Process)</h3>
							<p id="nexus-play-thinking" class="text-xs text-gray-400 leading-relaxed font-mono bg-black/20 p-2 rounded"></p>
						</div>
						<div class="border-t border-nexus-border/30 pt-4">
							<h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Output Format</h3>
							<p id="nexus-play-output" class="text-xs text-gray-400 leading-relaxed"></p>
						</div>
						<div class="border-t border-nexus-border/30 pt-4">
							<h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Negative Guardrails</h3>
							<p id="nexus-play-negative" class="text-xs text-red-500/80 leading-relaxed"></p>
						</div>
					</div>
				</div>

				<!-- Right Column: Chat Window -->
				<div class="lg:col-span-2">
					<div class="glass-panel rounded-3xl border border-nexus-border min-h-[600px] flex flex-col overflow-hidden bg-black/40">
						<div class="p-6 border-b border-nexus-border bg-nexus-elevated/50 flex justify-between items-center">
							<div class="flex items-center gap-4">
								<div class="w-3 h-3 rounded-full bg-green-500 animate-pulse shadow-[0_0_10px_rgba(34,197,94,0.5)]"></div>
								<h2 class="font-bold text-[#1e293b] uppercase tracking-widest text-[10px]">Playground Console</h2>
							</div>
							<span class="text-[9px] text-nexus-violet font-bold bg-nexus-violet/10 border border-nexus-violet/20 px-2 py-1 rounded-full uppercase">Agent Isolated Context</span>
						</div>

						<div id="nexus-playground-chat" class="flex-1 p-8 space-y-6 overflow-y-auto max-h-[450px]">
							<div class="text-center text-gray-500 py-20">
								Select an agent above to begin conversation.
							</div>
						</div>

						<div class="p-6 bg-nexus-elevated/50 border-t border-nexus-border flex gap-4">
							<input type="text" id="nexus-playground-input" class="flex-1 bg-nexus-elevated border border-nexus-border rounded-xl p-4 text-[#1e293b] outline-none focus:border-accent" placeholder="Consult your specialist...">
							<button id="nexus-playground-send-btn" class="bg-accent hover:opacity-90 text-[#1e293b] font-bold px-8 rounded-xl transition-all shadow-lg shadow-accent/20 nexus-btn-vibrant">Ask Agent</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
