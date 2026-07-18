<?php
declare(strict_types=1);

namespace NexusAI\Workforce\AI\RAG;

/**
 * Handles text extraction from various document types.
 */
class Parser {

	/**
	 * Parse a file and return its text content.
	 *
	 * @param string $filepath Path to the file.
	 * @param string $type     File type (pdf, docx, txt).
	 * @return string
	 */
	public function parse( string $filepath, string $type ): string {
		if ( ! file_exists( $filepath ) ) {
			return '';
		}

		switch ( strtolower( $type ) ) {
			case 'txt':
			case 'md':
				return file_get_contents( $filepath ) ?: '';

			case 'pdf':
				if ( class_exists( 'Smalot\\PdfParser\\Parser' ) ) {
					$parser = new \Smalot\PdfParser\Parser();
					$pdf = $parser->parseFile( $filepath );
					return $pdf->getText();
				}
				return '';

			case 'docx':
				if ( class_exists( 'PhpOffice\\PhpWord\\IOFactory' ) ) {
					$phpWord = \PhpOffice\PhpWord\IOFactory::load( $filepath );
					$text = '';
					foreach ( $phpWord->getSections() as $section ) {
						foreach ( $section->getElements() as $element ) {
							if ( method_exists( $element, 'getText' ) ) {
								$text .= $element->getText() . "\n";
							}
						}
					}
					return $text;
				}
				return '';

			case 'csv':
				$handle = fopen( $filepath, 'r' );
				$text = '';
				if ( $handle ) {
					while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== false ) {
						$text .= implode( ' | ', $data ) . "\n";
					}
					fclose( $handle );
				}
				return $text;

			default:
				return '';
		}
	}
}
