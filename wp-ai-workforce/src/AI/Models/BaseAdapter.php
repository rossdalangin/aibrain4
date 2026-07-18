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

		// Detect if there is a chairman intervention message
		$chairman_intervention = '';
		if ( preg_match( '/CHAIRMAN INTERVENTION:\s*([^\n]+)/i', $user_msg, $ch_matches ) ) {
			$chairman_intervention = trim( $ch_matches[1] );
			// Clean chairman intervention as well
			$chairman_intervention = preg_replace( '/\s*Please think step-by-step before providing your final answer\..*/s', '', $chairman_intervention );
			$chairman_intervention = trim( $chairman_intervention );
		}

		// Extract topic words from clean user message for smart contextual reflection
		$topic_words = [];
		if ( preg_match_all( '/\b[a-zA-Z]{4,15}\b/', $clean_user_msg, $matches ) ) {
			$ignored_words = [ 'with', 'this', 'that', 'your', 'from', 'have', 'would', 'should', 'could', 'about', 'there', 'their', 'them', 'then', 'here', 'some', 'please', 'think', 'step', 'final', 'answer', 'structure', 'output', 'chairman', 'intervention', 'meeting', 'round' ];
			foreach ( $matches[0] as $word ) {
				$l_word = strtolower($word);
				if ( ! in_array( $l_word, $ignored_words ) && strlen($l_word) > 3 ) {
					$topic_words[] = $word;
				}
			}
		}
		$extracted_topic = ! empty( $topic_words ) ? implode( ' and ', array_slice( array_unique( $topic_words ), 0, 2 ) ) : 'our common goals';

		$lower_msg  = strtolower( $clean_user_msg );
		$lower_pos  = strtolower( $agent_position );

		$is_greeting = preg_match( '/\b(hello|hi|hey|greetings|howdy|good morning|good afternoon)\b/i', $lower_msg ) || $lower_msg === 'hello' || $lower_msg === 'hi';
		$is_roi       = strpos( $lower_msg, 'roi' ) !== false || strpos( $lower_msg, 'audit' ) !== false || strpos( $lower_msg, 'cost' ) !== false || strpos( $lower_msg, 'budget' ) !== false || strpos( $lower_msg, 'revenue' ) !== false || strpos( $lower_msg, 'pricing' ) !== false || strpos( $lower_msg, 'sales' ) !== false;
		$is_marketing = strpos( $lower_msg, 'market' ) !== false || strpos( $lower_msg, 'growth' ) !== false || strpos( $lower_msg, 'ad' ) !== false || strpos( $lower_msg, 'storm' ) !== false || strpos( $lower_msg, 'brand' ) !== false || strpos( $lower_msg, 'copy' ) !== false || strpos( $lower_msg, 'headline' ) !== false;
		$is_system    = strpos( $lower_msg, 'security' ) !== false || strpos( $lower_msg, 'scale' ) !== false || strpos( $lower_msg, 'latency' ) !== false || strpos( $lower_msg, 'technical' ) !== false || strpos( $lower_msg, 'debt' ) !== false || strpos( $lower_msg, 'architecture' ) !== false || strpos( $lower_msg, 'rag' ) !== false || strpos( $lower_msg, 'code' ) !== false || strpos( $lower_msg, 'database' ) !== false || strpos( $lower_msg, 'api' ) !== false;

		// Deterministic hash based on agent and request to select unique templates and prevent repetitive output
		$hash = abs(crc32($agent_name . $clean_user_msg));

		$reasoning_steps = [];
		$reply_body = '';

		if ( $is_greeting ) {
			$reasoning_steps = [
				"Greet the user in clear, friendly layman's terms.",
				"Establish professional presence as {$agent_position} without redundant industry jargon.",
				"Invite collaboration on the current business tasks."
			];
			$reply_body = "Hello! I am {$agent_name}, your {$agent_position}. My main focus is to: '{$agent_mission}'. I have a lot of experience in {$agent_skills}, and my ultimate goal is to make sure we hit key targets like {$agent_kpis}. How can I help you or work with you today?";
		} elseif ( $is_roi ) {
			$reasoning_steps = [
				"Analyze the financial question about '{$extracted_topic}' in simple terms.",
				"Determine clear, non-jargony suggestions to optimize efficiency and minimize expenses.",
				"Align our priorities with our success targets: {$agent_kpis}."
			];

			if ( strpos( $lower_pos, 'strategy' ) !== false || strpos( $lower_pos, 'executive' ) !== false || strpos( $lower_pos, 'ceo' ) !== false || strpos( $lower_pos, 'cso' ) !== false ) {
				$templates = [
					"From a planning standpoint, our main focus should be getting all of our teams working toward the same goals. By focusing only on things that actually make us money and keeping our daily tasks super simple, we can make sure every dollar we spend is well worth it.",
					"We need to look closely at where our money is going. To get the best results, we should stop doing tasks that don't add real value, make our tools easier to use, and track our progress using simple numbers that everyone on the team can understand.",
					"Let's keep our business plan clean and straightforward. We should focus our time and resources on our most profitable projects, cut down on unnecessary steps, and make sure we have a clear path to success."
				];
				$reply_body = $templates[ $hash % count($templates) ];
			} elseif ( strpos( $lower_pos, 'marketing' ) !== false || strpos( $lower_pos, 'growth' ) !== false || strpos( $lower_pos, 'cmo' ) !== false ) {
				$templates = [
					"To get the best return on our advertising budget, we must keep a very close eye on what it costs to acquire a new customer. We should stop spending money on ads that do not work and focus entirely on the campaigns that bring in the most buyers.",
					"We can increase our sales and profits by auditing our current marketing activities. Let's make sure our promotional messages are extremely clear, and let's only run advertisements on channels where we are 100% sure we are making a profit.",
					"Let's review our sales numbers and marketing costs carefully. By focusing our efforts on happy, repeat buyers and simplifying our checkout steps, we can significantly boost our revenue without spending more on ads."
				];
				$reply_body = $templates[ $hash % count($templates) ];
			} elseif ( strpos( $lower_pos, 'system' ) !== false || strpos( $lower_pos, 'engineer' ) !== false || strpos( $lower_pos, 'developer' ) !== false || strpos( $lower_pos, 'cto' ) !== false || strpos( $lower_pos, 'technical' ) !== false ) {
				$templates = [
					"From a technical perspective, the best way to save money and boost efficiency is to make our software run as smoothly as possible. This means reducing server resource waste, using smart caching, and avoiding duplicate database searches.",
					"We can lower our technology costs and improve performance by streamlining our backend. Keeping our code simple and avoiding overly complex systems keeps our servers running fast, which means we pay less for hosting.",
					"Let's make sure our systems are highly cost-effective. By optimizing how our servers talk to external databases and cleaning up unnecessary computer scripts, we can save a lot of server power and lower our operating costs."
				];
				$reply_body = $templates[ $hash % count($templates) ];
			} else {
				$templates = [
					"As the {$agent_position}, I believe we can get the best outcomes by keeping our daily routines very simple and focused on the most important targets.",
					"To make our department more cost-effective, we should eliminate tasks that take too much time and do not help us achieve our primary goals.",
					"I suggest we review our team's main objectives. By making our daily workflows straightforward and clear, we can save time and deliver better results."
				];
				$reply_body = $templates[ $hash % count($templates) ];
			}
		} elseif ( $is_marketing ) {
			$reasoning_steps = [
				"Examine the growth and marketing request for '{$extracted_topic}' using clear concepts.",
				"Suggest practical, easy-to-understand customer acquisition ideas.",
				"Focus on making our customer communication clear, readable, and highly engaging."
			];

			if ( strpos( $lower_pos, 'strategy' ) !== false || strpos( $lower_pos, 'executive' ) !== false || strpos( $lower_pos, 'ceo' ) !== false || strpos( $lower_pos, 'cso' ) !== false ) {
				$templates = [
					"If we want to grow quickly, we must ensure our message is incredibly easy for any customer to understand. Let's make our main benefits perfectly clear and align all team efforts to support our marketing message.",
					"Growth happens when we solve real problems for our audience. Let's focus our strategic planning on explaining how our product helps customers simply and clearly, ensuring our message stands out from competitors.",
					"To scale up successfully, we need to make sure every department works together to support our marketing goals. Let's simplify how we talk to customers and ensure our message is consistent everywhere."
				];
				$reply_body = $templates[ $hash % count($templates) ];
			} elseif ( strpos( $lower_pos, 'marketing' ) !== false || strpos( $lower_pos, 'growth' ) !== false || strpos( $lower_pos, 'cmo' ) !== false ) {
				$templates = [
					"To help us grow, we should create simple marketing plans that bring in customers step-by-step, make our product's benefits easy for anyone to understand, and keep our advertising costs lower than the profit we make.",
					"We want more people to find and use our product. We can do this by writing clear messages that explain exactly how we help them, testing different ideas quickly to see what works, and making it super easy for new users to sign up.",
					"Let's share our story in a way that is clear and exciting. We should find out exactly what our customers need, create helpful guides for them, and make sure our marketing budget is spent on ads that actually bring in happy customers."
				];
				$reply_body = $templates[ $hash % count($templates) ];
			} elseif ( strpos( $lower_pos, 'system' ) !== false || strpos( $lower_pos, 'engineer' ) !== false || strpos( $lower_pos, 'developer' ) !== false || strpos( $lower_pos, 'cto' ) !== false || strpos( $lower_pos, 'technical' ) !== false ) {
				$templates = [
					"A fast website is a powerful marketing tool. By ensuring our system loads in less than a second, we make it much more likely that visitors will stay on our site, read our marketing info, and buy our product.",
					"We can support our marketing efforts by keeping our website perfectly stable and extremely fast. When pages load instantly and checkout works smoothly without any errors, customers are much happier and convert faster.",
					"Let's make sure our technical system is highly responsive. Slow loading times turn visitors away, so optimizing our landing pages on a code level is one of the best ways to help our marketing campaigns succeed."
				];
				$reply_body = $templates[ $hash % count($templates) ];
			} else {
				$templates = [
					"To help our company grow, we should ensure our team's work is high-quality and aligns perfectly with our brand's message of simplicity and clarity.",
					"I suggest we make all our customer communications as straightforward as possible, focusing on explaining how we help them in plain, easy-to-read language.",
					"We can support our marketing goals by ensuring our department deliverables are finished on time and represent our core brand guidelines beautifully."
				];
				$reply_body = $templates[ $hash % count($templates) ];
			}
		} elseif ( $is_system ) {
			$reasoning_steps = [
				"Assess technical and systems requirements for '{$extracted_topic}' with clarity.",
				"Formulate safe, scalable, and extremely robust non-jargony solutions.",
				"Ensure the infrastructure remains simple, fast, and secure for everyone."
			];

			if ( strpos( $lower_pos, 'strategy' ) !== false || strpos( $lower_pos, 'executive' ) !== false || strpos( $lower_pos, 'ceo' ) !== false || strpos( $lower_pos, 'cso' ) !== false ) {
				$templates = [
					"To support our company's future growth, our systems must be both reliable and simple. We should ensure our data is completely safe, avoid complex technology that we do not need, and build a strong foundation.",
					"We should treat our technical setup as a core business asset. By keeping our technology simple, robust, and highly secure, we protect our business from interruptions and make it easy to grow in the future.",
					"A great technology strategy is one that focuses on reliability and ease of use. Let's make sure our systems are safe, keep our data organized simply, and ensure our platforms can scale smoothly as we get more users."
				];
				$reply_body = $templates[ $hash % count($templates) ];
			} elseif ( strpos( $lower_pos, 'marketing' ) !== false || strpos( $lower_pos, 'growth' ) !== false || strpos( $lower_pos, 'cmo' ) !== false ) {
				$templates = [
					"A secure and fast platform is essential for customer trust. We should make sure we tell our users how safely we handle their data, as system reliability is a great selling point that makes customers feel safe.",
					"When our systems work flawlessly, our users trust us more. Let's emphasize our high platform speed and safety in our ads to help attract and retain enterprise clients who care about privacy.",
					"Let's ensure our systems are fast and safe, as this directly helps our marketing. Customers are more likely to buy from us when they see our payment portals and pages work instantly and securely."
				];
				$reply_body = $templates[ $hash % count($templates) ];
			} elseif ( strpos( $lower_pos, 'system' ) !== false || strpos( $lower_pos, 'engineer' ) !== false || strpos( $lower_pos, 'developer' ) !== false || strpos( $lower_pos, 'cto' ) !== false || strpos( $lower_pos, 'technical' ) !== false ) {
				$templates = [
					"Our website and software are running perfectly. To keep them fast and secure, we should clean up our database, make sure only authorized people can access our data, and organize our files better so pages load instantly for our users.",
					"I am checking our systems to make sure everything is safe and works without any lag. We should keep our security tight, make our database run smoother, and build our code like building blocks so it is easy to update later.",
					"To make sure our tech can handle thousands of users at once, we need to make our code as efficient as possible. This means keeping our database neat, checking our security locks regularly, and keeping page loading times super short."
				];
				$reply_body = $templates[ $hash % count($templates) ];
			} else {
				$templates = [
					"To ensure our department runs smoothly, we should keep our digital tools simple, well-organized, and secure against common errors.",
					"I suggest we make our technical guidelines extremely clear so that every team member can use our systems easily and without confusion.",
					"Let's focus on keeping our platform stable and safe. When our basic tools are secure and simple, our team can work much more efficiently."
				];
				$reply_body = $templates[ $hash % count($templates) ];
			}
		} else {
			// Default / Generic query analyzer in layman's terms
			$reasoning_steps = [
				"Parse query regarding '{$extracted_topic}' and determine core intent in plain language.",
				"Assess how {$agent_position} can offer clear, practical guidance.",
				"Deliver a straightforward recommendation that is easy to understand and execute."
			];

			$templates = [
				"I have looked at this carefully from the standpoint of my role as {$agent_position}. Let's work together to make our main tasks as clear as possible and focus on simple steps that get us closer to our main goals.",
				"As {$agent_position}, my goal is to make sure we work efficiently. We should make our instructions easy to follow, help each other out on team tasks, and make sure we can measure our daily success clearly.",
				"To get the best results in our department, we should simplify our workflow, focus on the most important goals first, and make sure we communicate our progress clearly and simply."
			];
			$reply_body = $templates[ $hash % count($templates) ];
		}

		// Inject the chairman intervention context dynamically and in layman's terms if present
		if ( ! empty( $chairman_intervention ) ) {
			$openers = [
				"I hear your instruction about '{$chairman_intervention}', and here is how my department can help simply: ",
				"That makes total sense regarding '{$chairman_intervention}'. To make this happen with maximum clarity: ",
				"I completely agree with the focus on '{$chairman_intervention}'. From my perspective: "
			];
			$opener = $openers[ $hash % count($openers) ];
			$reply_body = $opener . lcfirst($reply_body);
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
