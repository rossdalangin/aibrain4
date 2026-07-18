<?php
declare(strict_types=1);

namespace NexusAI\Workforce\Utils;

/**
 * Handles encryption and decryption of sensitive data.
 */
class Encryption {

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var string
	 */
	private $method = 'AES-256-CTR';

	public function __construct() {
		// Use the WordPress AUTH_KEY for encryption
		if ( defined( 'AUTH_KEY' ) ) {
			$this->key = AUTH_KEY;
		} else {
			$this->key = 'nexus-default-key'; // Fallback for testing
		}
	}

	/**
	 * Encrypt a value.
	 *
	 * @param string $value The raw value.
	 * @return string       The encrypted value (base64).
	 */
	public function encrypt( string $value ): string {
		$iv_length = openssl_cipher_iv_length( $this->method );
		$iv = openssl_random_pseudo_bytes( $iv_length );
		$encrypted = openssl_encrypt( $value, $this->method, $this->key, 0, $iv );
		return base64_encode( $iv . $encrypted );
	}

	/**
	 * Decrypt a value.
	 *
	 * @param string $value The base64 encrypted value.
	 * @return string       The decrypted value.
	 */
	public function decrypt( string $value ): string {
		$data = base64_decode( $value );
		$iv_length = openssl_cipher_iv_length( $this->method );
		$iv = substr( $data, 0, $iv_length );
		$encrypted = substr( $data, $iv_length );
		return openssl_decrypt( $encrypted, $this->method, $this->key, 0, $iv ) ?: '';
	}
}
