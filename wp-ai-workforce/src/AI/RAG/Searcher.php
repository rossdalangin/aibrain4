<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\RAG;

/**
 * Handles retrieval of relevant context from the vector database.
 */
class Searcher {

	/**
	 * Search for context chunks relevant to a query.
	 *
	 * @param string $query    The user question.
	 * @param array  $filters  Search filters (agent_id, dept_id).
	 * @return string          Concatenated context.
	 */
	public function search( string $query, array $filters = [] ): string {
		global $wpdb;

		$chunks_table = $wpdb->prefix . 'ai_knowledge_chunks';
		$docs_table   = $wpdb->prefix . 'ai_knowledge_documents';
		$kb_table     = $wpdb->prefix . 'ai_knowledge_bases';

		// 1. Build secure query with departmental filtering
		$sql = "SELECT c.content FROM $chunks_table c
				JOIN $docs_table d ON c.doc_id = d.id
				JOIN $kb_table k ON d.kb_id = k.id
				WHERE 1=1";

		// 2. Multi-term weighting (Simulated semantic search)
		$terms = explode(' ', $query);
		$term_conditions = [];
		$params = [];

		// Prioritize exact match if multiple words
		if (count($terms) > 1) {
			$term_conditions[] = "c.content LIKE %s";
			$params[] = '%' . $wpdb->esc_like($query) . '%';
		}

		foreach ($terms as $term) {
			if (strlen($term) < 3) continue;
			$term_conditions[] = "c.content LIKE %s";
			$params[] = '%' . $wpdb->esc_like($term) . '%';
		}

		if (!empty($term_conditions)) {
			$sql .= " AND (" . implode(' OR ', $term_conditions) . ")";
		} else {
			$sql .= " AND c.content LIKE %s";
			$params[] = '%' . $wpdb->esc_like($query) . '%';
		}

		if ( ! empty( $filters['dept_id'] ) ) {
			$sql .= " AND (k.owner_type = 'department' AND k.owner_id = %d)";
			$params[] = $filters['dept_id'];
		} elseif ( ! empty( $filters['agent_id'] ) ) {
			$sql .= " AND (k.owner_type = 'employee' AND k.owner_id = %d)";
			$params[] = $filters['agent_id'];
		} else {
			$sql .= " AND k.owner_type = 'global'";
		}

		// 3. Advanced Simulated Vector Scoring (BM25-inspired)
		// We weigh whole query matches significantly higher than individual term matches.
		$score_case = "CASE WHEN c.content LIKE %s THEN 100 ELSE 0 END";
		$params[] = '%' . $wpdb->esc_like($query) . '%';

		// Priority for matches in document titles if we had them,
		// but let's simulate density by checking term presence in multiple positions
		foreach ($terms as $term) {
			if (strlen($term) < 3) continue;
			// Term match in first 100 chars (header-weighted)
			$score_case .= " + (CASE WHEN LEFT(c.content, 100) LIKE %s THEN 15 ELSE 0 END)";
			$params[] = '%' . $wpdb->esc_like($term) . '%';

			// General occurrence
			$score_case .= " + (CASE WHEN c.content LIKE %s THEN 5 ELSE 0 END)";
			$params[] = '%' . $wpdb->esc_like($term) . '%';
		}

		$sql .= " ORDER BY ($score_case) DESC LIMIT 15";

		$results = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		if ( empty( $results ) ) {
			return "";
		}

		$context = "Relevant information from Knowledge Base:\n";
		foreach ( $results as $row ) {
			$context .= "- " . $row['content'] . "\n";
		}

		return $context;
	}
}
