<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\RAG;

/**
 * Handles semantic chunking of text for embedding.
 */
class Chunker {

	/**
	 * Split text into chunks of specified size with overlap.
	 *
	 * @param string $text       The input text.
	 * @param int    $chunk_size Max characters per chunk.
	 * @param int    $overlap    Overlap size in characters.
	 * @return array             Array of text chunks.
	 */
	public function chunk( string $text, int $chunk_size = 1000, int $overlap = 100 ): array {
		$chunks = [];
		$length = mb_strlen( $text );

		if ( $length <= $chunk_size ) {
			return [ $text ];
		}

		$start = 0;
		while ( $start < $length ) {
			$end = $start + $chunk_size;
			$chunk = mb_substr( $text, $start, $chunk_size );
			$chunks[] = $chunk;
			$start += ( $chunk_size - $overlap );
		}

		return $chunks;
	}
}
