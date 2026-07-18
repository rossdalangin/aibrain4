<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\Models;

/**
 * Basic adapter with common functionality for AI providers.
 */
abstract class BaseAdapter implements AIModelInterface {

	/**
	 * @var string
	 */
	protected $api_key;

	/**
	 * Constructor.
	 *
	 * @param string $api_key Encrypted or raw API key.
	 */
	public function __construct( string $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Log API errors.
	 */
	protected function log_error( string $message, array $context = [] ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( '[Nexus AI] %s', $message ) );
		}
	}

	/**
	 * Get the base URL for the API.
	 */
	abstract protected function get_base_url(): string;

	/**
	 * Perform a remote request to the AI provider.
	 */
	protected function request( string $endpoint, array $payload, array $extra_headers = [] ) {
		$headers = array_merge( [
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $this->api_key,
		], $extra_headers );

		// Filter out empty headers to prevent connection errors on some proxy servers
		$headers = array_filter( $headers, function( $value ) {
			return $value !== '';
		} );

		return wp_remote_post( $this->get_base_url() . $endpoint, [
			'headers' => $headers,
			'body'    => wp_json_encode( $payload ),
			'timeout' => 60,
		] );
	}

	/**
	 * Get a simulated, high-quality response for testing/local environments when no API key is set.
	 */
	protected function get_mock_completion( array $messages ): array {
		$user_msg = "Hello!";
		foreach ( array_reverse( $messages ) as $msg ) {
			if ( $msg['role'] === 'user' ) {
				$user_msg = $msg['content'];
				break;
			}
		}

		$agent_name     = 'AI Employee';
		$agent_position = 'Specialist';
		$agent_mission  = 'Execute tasks efficiently.';
		$agent_skills   = 'General Analysis, Problem Solving';
		$agent_kpis     = 'Execution Speed, Strategy Delivery';

		foreach ( $messages as $msg ) {
			if ( $msg['role'] === 'system' ) {
				$system_content = $msg['content'];
				if ( preg_match( '/# IDENTITY\s+([^\n-]+)\s*-\s*([^\n]+)/i', $system_content, $matches ) ) {
					$agent_name     = trim( $matches[1] );
					$agent_position = trim( $matches[2] );
				}
				if ( preg_match( '/# MISSION\s+([^\n]+)/i', $system_content, $m_matches ) ) {
					$agent_mission = trim( $m_matches[1] );
				}
				if ( preg_match( '/# SKILLS & EXPERTISE\s+([^\n]+)/i', $system_content, $s_matches ) ) {
					$agent_skills = trim( $s_matches[1] );
				}
				if ( preg_match( '/# SUCCESS KPIS\s+Your performance is measured by:\s*([^\n]+)/i', $system_content, $k_matches ) ) {
					$agent_kpis = trim( $k_matches[1] );
				}
				break;
			}
		}

		// Clean user message by stripping chain-of-thought (CoT) system prompt instructions
		$clean_user_msg = preg_replace( '/\s*Please think step-by-step before providing your final answer\..*/s', '', $user_msg );
		$clean_user_msg = trim( $clean_user_msg );

		// Extract topic words from clean user message for smart contextual reflection
		$topic_words = [];
		if ( preg_match_all( '/\b[a-zA-Z]{4,15}\b/', $clean_user_msg, $matches ) ) {
			$ignored_words = [ 'with', 'this', 'that', 'your', 'from', 'have', 'would', 'should', 'could', 'about', 'there', 'their', 'them', 'then', 'here', 'some', 'please', 'think', 'step', 'final', 'answer', 'structure', 'output' ];
			foreach ( $matches[0] as $word ) {
				$l_word = strtolower($word);
				if ( ! in_array( $l_word, $ignored_words ) && strlen($l_word) > 3 ) {
					$topic_words[] = $word;
				}
			}
		}
		$extracted_topic = ! empty( $topic_words ) ? implode( ' and ', array_slice( array_unique( $topic_words ), 0, 2 ) ) : 'your objective';

		$lower_msg  = strtolower( $clean_user_msg );
		$lower_pos  = strtolower( $agent_position );

		$is_greeting = preg_match( '/\b(hello|hi|hey|greetings|howdy|good morning|good afternoon)\b/i', $lower_msg ) || $lower_msg === 'hello' || $lower_msg === 'hi';
		$is_roi       = strpos( $lower_msg, 'roi' ) !== false || strpos( $lower_msg, 'audit' ) !== false || strpos( $lower_msg, 'cost' ) !== false || strpos( $lower_msg, 'budget' ) !== false || strpos( $lower_msg, 'revenue' ) !== false || strpos( $lower_msg, 'pricing' ) !== false || strpos( $lower_msg, 'sales' ) !== false;
		$is_marketing = strpos( $lower_msg, 'market' ) !== false || strpos( $lower_msg, 'growth' ) !== false || strpos( $lower_msg, 'ad' ) !== false || strpos( $lower_msg, 'storm' ) !== false || strpos( $lower_msg, 'brand' ) !== false || strpos( $lower_msg, 'copy' ) !== false || strpos( $lower_msg, 'headline' ) !== false;
		$is_system    = strpos( $lower_msg, 'security' ) !== false || strpos( $lower_msg, 'scale' ) !== false || strpos( $lower_msg, 'latency' ) !== false || strpos( $lower_msg, 'technical' ) !== false || strpos( $lower_msg, 'debt' ) !== false || strpos( $lower_msg, 'architecture' ) !== false || strpos( $lower_msg, 'rag' ) !== false || strpos( $lower_msg, 'code' ) !== false || strpos( $lower_msg, 'database' ) !== false || strpos( $lower_msg, 'api' ) !== false;

		$reasoning_steps = [];
		$reply_body = '';

		if ( $is_greeting ) {
			$reasoning_steps = [
				"Identify greeting intent and establish professional identity as {$agent_position}.",
				"Align tone with the agent profile: Name: {$agent_name}, Position: {$agent_position}.",
				"Prepare to accept operational instructions, strategic tasks, or general inquiries."
			];
			$reply_body = "Hello! I am {$agent_name}, your {$agent_position}. My core mission is to: '{$agent_mission}'. I specialize in {$agent_skills} and focus closely on achieving success metrics like {$agent_kpis}. How can I assist you or collaborate with you today?";
		} elseif ( $is_roi ) {
			$reasoning_steps = [
				"Analyze financial/strategic query regarding '{$extracted_topic}'.",
				"Evaluate cost-to-benefit ratios and operational ROI relevant to the {$agent_position} role.",
				"Reference success metrics ({$agent_kpis}) to ground the recommendations in data-driven metrics."
			];
			if ( strpos( $lower_pos, 'strategy' ) !== false || strpos( $lower_pos, 'executive' ) !== false || strpos( $lower_pos, 'ceo' ) !== false || strpos( $lower_pos, 'cso' ) !== false ) {
				$reply_body = "As Chief Strategy Officer, I've conducted a rigorous audit on '{$extracted_topic}'. To optimize long-term operational ROI, we must eliminate redundant prompt loops, streamline our micro-workflows, and ensure our token expenses map directly to tangible business outcomes. Let's establish immediate KPIs to monitor this execution.";
			} elseif ( strpos( $lower_pos, 'marketing' ) !== false || strpos( $lower_pos, 'growth' ) !== false || strpos( $lower_pos, 'cmo' ) !== false ) {
				$reply_body = "To maximize our marketing ROI, we must optimize our customer acquisition cost (CAC) and scale channels that generate a ROAS of 4.0 or greater. Regarding '{$extracted_topic}', we should audit our landing page performance and ad placement parameters to ensure we aren't wasting budget on low-converting demographics.";
			} elseif ( strpos( $lower_pos, 'system' ) !== false || strpos( $lower_pos, 'engineer' ) !== false || strpos( $lower_pos, 'developer' ) !== false || strpos( $lower_pos, 'cto' ) !== false || strpos( $lower_pos, 'technical' ) !== false ) {
				$reply_body = "From a systems engineering standpoint, optimizing the ROI for '{$extracted_topic}' involves refining database queries, utilizing server-side caching, and caching recurring API embeddings. This reduces cloud computing latency and significantly lowers API token overhead, translating to direct infrastructure savings.";
			} else {
				$reply_body = "As {$agent_position}, I believe managing '{$extracted_topic}' requires clear operational guidelines. By aligning our daily tasks with our core KPI parameters ({$agent_kpis}), we can eliminate low-leverage activities and maximize overall department efficiency.";
			}
		} elseif ( $is_marketing ) {
			$reasoning_steps = [
				"Analyze growth and marketing request concerning '{$extracted_topic}'.",
				"Leverage behavioral psychology and viral loop strategies from {$agent_position} perspective.",
				"Design rapid, measurable growth experiments to optimize customer conversion."
			];
			if ( strpos( $lower_pos, 'strategy' ) !== false || strpos( $lower_pos, 'executive' ) !== false || strpos( $lower_pos, 'ceo' ) !== false || strpos( $lower_pos, 'cso' ) !== false ) {
				$reply_body = "From a strategy perspective, growth is a direct function of organizational alignment. Regarding '{$extracted_topic}', we must refine our value proposition and ensure our multi-agent marketing campaigns speak directly to our target enterprise persona to scale long-term customer lifetime value.";
			} elseif ( strpos( $lower_pos, 'marketing' ) !== false || strpos( $lower_pos, 'growth' ) !== false || strpos( $lower_pos, 'cmo' ) !== false ) {
				$reply_body = "To accelerate growth and optimize '{$extracted_topic}', we should architect highly viral marketing loops, run multi-variate tests on our primary headlines, and optimize our paid media budgets around a robust ROAS threshold of 4.0.";
			} elseif ( strpos( $lower_pos, 'system' ) !== false || strpos( $lower_pos, 'engineer' ) !== false || strpos( $lower_pos, 'developer' ) !== false || strpos( $lower_pos, 'cto' ) !== false || strpos( $lower_pos, 'technical' ) !== false ) {
				$reply_body = "Technology and growth are deeply intertwined. For '{$extracted_topic}', my focus is ensuring that our user-facing pages and checkout paths are highly optimized. Reducing server latency directly improves conversion rates and supports viral campaign spikes without system downtime.";
			} else {
				$reply_body = "Regarding '{$extracted_topic}', as {$agent_position}, we should ensure that our deliverables directly enhance customer value. I suggest aligning our outputs to support our primary acquisition funnels and brand guidelines.";
			}
		} elseif ( $is_system ) {
			$reasoning_steps = [
				"Review architectural requirements for '{$extracted_topic}'.",
				"Apply fail-safe security principles and performance optimization frameworks.",
				"Design highly scalable, low-latency micro-services or API connections."
			];
			if ( strpos( $lower_pos, 'strategy' ) !== false || strpos( $lower_pos, 'executive' ) !== false || strpos( $lower_pos, 'ceo' ) !== false || strpos( $lower_pos, 'cso' ) !== false ) {
				$reply_body = "To scale effectively, we must manage technical debt as a strategic balance sheet item. For '{$extracted_topic}', our architecture must remain modular and secure. This ensures we can deploy future features rapidly without risking regression or platform instability.";
			} elseif ( strpos( $lower_pos, 'marketing' ) !== false || strpos( $lower_pos, 'growth' ) !== false || strpos( $lower_pos, 'cmo' ) !== false ) {
				$reply_body = "From a growth perspective, '{$extracted_topic}' is critical for user trust. We should communicate our robust system security and speed as a key selling point in our brand messaging to convert privacy-conscious enterprise buyers.";
			} elseif ( strpos( $lower_pos, 'system' ) !== false || strpos( $lower_pos, 'engineer' ) !== false || strpos( $lower_pos, 'developer' ) !== false || strpos( $lower_pos, 'cto' ) !== false || strpos( $lower_pos, 'technical' ) !== false ) {
				$reply_body = "System integrity and performance are fully operational. For '{$extracted_topic}', I recommend establishing strict zero-trust API boundaries, implementing recursive chunking in our RAG indexing engine, and sharding high-volume tables to keep server latency under 200ms.";
			} else {
				$reply_body = "Technical stability and clear guidelines for '{$extracted_topic}' are highly important. As {$agent_position}, I will work to integrate these safety and performance boundaries into our team's standard workflows.";
			}
		} else {
			// Default / Generic query analyzer
			$reasoning_steps = [
				"Parse query regarding '{$extracted_topic}' and determine core intent.",
				"Assess how {$agent_position} skills ({$agent_skills}) can solve the prompt.",
				"Formulate professional advice aligned with the mission: '{$agent_mission}'."
			];
			$reply_body = "I have processed your request regarding '{$extracted_topic}' from the standpoint of my role as {$agent_position}. To achieve our strategic deliverables, we should prioritize high-leverage activities, focus on {$agent_skills}, and map out actionable milestones to ensure our long-term KPIs ({$agent_kpis}) are fully met.";
		}

		// Format output with Reasoning and Final Response
		$content = "Reasoning:\n";
		foreach ( $reasoning_steps as $i => $step ) {
			$content .= ($i + 1) . ". " . $step . "\n";
		}
		$content .= "\nFinal Response:\n" . $reply_body;

		return [
			'content'    => $content,
			'tool_calls' => [],
			'usage'      => [ 'prompt_tokens' => 120, 'completion_tokens' => 45 ],
		];
	}
}
