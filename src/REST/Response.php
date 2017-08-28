<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\WPSR7\REST;

use GuzzleHttp\Psr7\Response as PSR7Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

use function GuzzleHttp\Psr7\stream_for;

/**
 * PSR-7-compliant WordPress REST response implementation.
 *
 * @package Inpsyde\WPSR7\REST
 * @since   1.0.0
 */
final class Response extends \WP_REST_Response implements ResponseInterface {

	/**
	 * @var ResponseInterface
	 */
	private $http_message;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed    $data    Optional. Response data. Defaults to null.
	 * @param int      $status  Optional. HTTP status code. Defaults to 200.
	 * @param string[] $headers Optional. HTTP headers, keys to string values. Defaults to empty array.
	 */
	public function __construct( $data = null, int $status = 200, array $headers = [] ) {

		$this->http_message = new PSR7Response();

		parent::__construct( $data, $status, $headers );

		// This is necessary because the parent constructor doesn't use set_data() but directly accesses $this->data.
		$this->set_data( $data );
	}

	/**
	 * Returns an instance based on the given WordPress REST response object.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Response $response WordPress REST response object.
	 *
	 * @return Response
	 */
	public static function from_wp_rest_response( \WP_REST_Response $response ): Response {

		if ( $response instanceof self ) {
			return $response;
		}

		return new self(
			$response->get_data(),
			(int) $response->get_status(),
			(array) ( $response->get_headers() ?? [] )
		);
	}

	/**
	 * Returns the HTTP protocol version.
	 *
	 * @since 1.0.0
	 *
	 * @return string HTTP protocol version.
	 */
	public function getProtocolVersion() {

		return $this->http_message->getProtocolVersion();
	}

	/**
	 * Returns an instance with the specified HTTP protocol version.
	 *
	 * @since 1.0.0
	 *
	 * @param string $version HTTP protocol version.
	 *
	 * @return static
	 */
	public function withProtocolVersion( $version ) {

		$version = (string) $version;
		if ( $this->http_message->getProtocolVersion() === $version ) {
			return $this;
		}

		$clone = clone $this;

		$clone->http_message = $this->http_message->withProtocolVersion( $version );

		return $clone;
	}

	/**
	 * Returns all message header values.
	 *
	 * @since 1.0.0
	 *
	 * @return string[][] Associative array with header names as keys, and arrays of header values as values.
	 */
	public function getHeaders() {

		return $this->http_message->getHeaders();
	}

	/**
	 * Checks if a header exists by the given case-insensitive name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Case-insensitive header field name.
	 *
	 * @return bool Whether or not any header names match the given one using case-insensitive string comparison.
	 */
	public function hasHeader( $name ) {

		return $this->http_message->hasHeader( $name );
	}

	/**
	 * Returns a message header value by the given case-insensitive name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Case-insensitive header field name.
	 *
	 * @return string[] An array of string values as provided for the given header.
	 */
	public function getHeader( $name ) {

		return $this->http_message->getHeader( $name );
	}

	/**
	 * Returns a comma-separated string of the values for a single header.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Case-insensitive header field name.
	 *
	 * @return string A string of values as provided for the given header concatenated together using a comma.
	 */
	public function getHeaderLine( $name ) {

		return $this->http_message->getHeaderLine( $name );
	}

	/**
	 * Returns an instance with the provided value replacing the specified header.
	 *
	 * @since 1.0.0
	 *
	 * @param string          $name  Case-insensitive header field name.
	 * @param string|string[] $value Header value(s).
	 *
	 * @return static
	 */
	public function withHeader( $name, $value ) {

		$clone = clone $this;

		$clone->http_message = $this->http_message->withHeader( $name, $value );

		$clone->set_wp_headers_from_http_message();

		return $clone;
	}

	/**
	 * Returns an instance with the specified header appended with the given value.
	 *
	 * @since 1.0.0
	 *
	 * @param string          $name  Case-insensitive header field name to add.
	 * @param string|string[] $value Header value(s).
	 *
	 * @return static
	 */
	public function withAddedHeader( $name, $value ) {

		$clone = clone $this;

		$clone->http_message = $this->http_message->withAddedHeader( $name, $value );

		$clone->set_wp_headers_from_http_message();

		return $clone;
	}

	/**
	 * Returns an instance without the specified header.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Case-insensitive header field name to remove.
	 *
	 * @return static
	 */
	public function withoutHeader( $name ) {

		$clone = clone $this;

		$clone->http_message = $this->http_message->withoutHeader( $name );

		$clone->set_wp_headers_from_http_message();

		return $clone;
	}

	/**
	 * Returns the body of the message.
	 *
	 * @since 1.0.0
	 *
	 * @return StreamInterface The body as a stream.
	 */
	public function getBody() {

		return $this->http_message->getBody();
	}

	/**
	 * Returns an instance with the specified message body.
	 *
	 * @since 1.0.0
	 *
	 * @param StreamInterface $body Body.
	 *
	 * @return static
	 */
	public function withBody( StreamInterface $body ) {

		if ( $this->http_message->getBody() === $body ) {
			return $this;
		}

		$clone = clone $this;

		$clone->http_message = $this->http_message->withBody( $body );

		$clone->set_wp_data_from_http_message();

		return $clone;
	}

	/**
	 * Returns the response status code.
	 *
	 * @since 1.0.0
	 *
	 * @return int Status code.
	 */
	public function getStatusCode() {

		return $this->http_message->getStatusCode();
	}

	/**
	 * Returns an instance with the specified status code and, optionally, reason phrase.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $status        HTTP status code.
	 * @param string $reason_phrase Optional. Reason phrase. Defaults based on status code.
	 *
	 * @return static
	 */
	public function withStatus( $status, $reason_phrase = '' ) {

		$clone = clone $this;

		$clone->http_message = $this->http_message->withStatus( $status, $reason_phrase );

		$clone->set_wp_status_from_http_message();

		return $clone;
	}

	/**
	 * Returns the response reason phrase associated with the status code.
	 *
	 * @since 1.0.0
	 *
	 * @return string Reason phrase.
	 */
	public function getReasonPhrase() {

		return $this->http_message->getReasonPhrase();
	}

	/**
	 * Sets the given headers.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $headers HTTP headers, keys to string values.
	 *
	 * @return void
	 */
	public function set_headers( $headers ) {

		parent::set_headers( $headers );

		$this->set_http_message_with_wp_headers();
	}

	/**
	 * Sets a single HTTP header.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name    Header name.
	 * @param string $value   Header value.
	 * @param bool   $replace Optional. Whether to replace an existing header of the same name. Defaults to true.
	 *
	 * @return void
	 */
	public function header( $name, $value, $replace = true ) {

		parent::header( $name, $value, $replace );

		$this->set_http_message_with_wp_headers();
	}

	/**
	 * Sets the HTTP status code.
	 *
	 * @since 1.0.0
	 *
	 * @param int $status HTTP status code.
	 *
	 * @return void
	 */
	public function set_status( $status ) {

		parent::set_status( $status );

		$this->http_message = $this->http_message->withStatus( $this->get_status() );
	}

	/**
	 * Sets the response data.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $data Response data.
	 *
	 * @return void
	 */
	public function set_data( $data ) {

		parent::set_data( $data );

		$data = $this->get_data();
		if ( \is_array( $data ) ) {
			$data = \json_encode( $data ) ?: '';
		}

		$this->http_message = $this->http_message->withBody( stream_for( $data ) );
	}

	/**
	 * Sets the PSR-7-compliant HTTP message with the WordPress-specific HTTP headers.
	 *
	 * @return void
	 */
	private function set_http_message_with_wp_headers() {

		$http_message = $this->http_message;

		$headers = \array_map( function ( $values ) {

			return \is_array( $values ) ? \array_map( '\strval', $values ) : \explode( ',', $values );
		}, (array) $this->get_headers() );

		// For whatever reason, there is no withHeaders() in PSR-7 HTTP messages, so we (have to) create a new one.
		$this->http_message = new PSR7Response(
			$http_message->getStatusCode(),
			$headers,
			$http_message->getBody(),
			$http_message->getProtocolVersion(),
			$http_message->getReasonPhrase()
		);
	}

	/**
	 * Sets the WordPress-specific HTTP message data according to the actual data of the PSR-7-compliant HTTP message.
	 *
	 * @return void
	 */
	private function set_wp_data_from_http_message() {

		$data = \json_decode( (string) $this->http_message->getBody() );

		parent::set_data( \is_scalar( $data ) ? $data : (array) $data );
	}

	/**
	 * Sets the WordPress-specific HTTP headers according to the actual data of the PSR-7-compliant HTTP message.
	 *
	 * @return void
	 */
	private function set_wp_headers_from_http_message() {

		$headers = \array_map( function ( array $values ) {

			return \implode( ', ', $values );
		}, $this->http_message->getHeaders() );

		parent::set_headers( $headers );
	}

	/**
	 * Sets the WordPress-specific HTTP status code according to the actual data of the PSR-7-compliant HTTP message.
	 *
	 * @return void
	 */
	private function set_wp_status_from_http_message() {

		parent::set_status( $this->http_message->getStatusCode() );
	}
}
