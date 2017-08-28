<?php # -*- coding: utf-8 -*-

declare( strict_types = 1 );

namespace Inpsyde\WPSR7\REST;

use GuzzleHttp\Psr7\ServerRequest as PSR7Request;
use GuzzleHttp\Psr7\UploadedFile as PSR7UploadedFile;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

use function GuzzleHttp\Psr7\stream_for;

/**
 * PSR-7-compliant WordPress REST request implementation.
 *
 * @package Inpsyde\WPSR7\REST
 * @since   1.0.0
 */
final class Request extends \WP_REST_Request implements ServerRequestInterface {

	/**
	 * @var ServerRequestInterface
	 */
	private $http_message;

	/**
	 * Constructor. Sets up the properties.
	 *
	 * @since  1.0.0
	 *
	 * @param string $method     Optional. HTTP method. Defaults to empty string.
	 * @param string $route      Optional. Request route. Defaults to empty string.
	 * @param array  $attributes Optional. Request attributes. Defaults to empty array.
	 */
	public function __construct( string $method = '', string $route = '', array $attributes = [] ) {

		$this->http_message = new PSR7Request( $method, '' );

		parent::__construct( $method, $route, $attributes );
	}

	/**
	 * Returns an instance based on the given WordPress REST request object.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request WordPress REST request object.
	 *
	 * @return Request
	 */
	public static function from_wp_rest_request( \WP_REST_Request $request ): Request {

		if ( $request instanceof self ) {
			return $request;
		}

		return new self(
			(string) $request->get_method(),
			(string) $request->get_route(),
			(array) ( $request->get_attributes() ?? [] )
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
	 * Returns the message's request target.
	 *
	 * @since 1.0.0
	 *
	 * @return string Request target.
	 */
	public function getRequestTarget() {

		return $this->http_message->getRequestTarget();
	}

	/**
	 * Returns an instance with the specified request target.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $request_target Request target.
	 *
	 * @return static
	 */
	public function withRequestTarget( $request_target ) {

		if ( $this->http_message->getRequestTarget() === $request_target ) {
			return $this;
		}

		$clone = clone $this;

		$clone->http_message = $this->http_message->withRequestTarget( $request_target );

		return $clone;
	}

	/**
	 * Returns the HTTP method of the request.
	 *
	 * @since 1.0.0
	 *
	 * @return string HTTP method.
	 */
	public function getMethod() {

		return $this->http_message->getMethod();
	}

	/**
	 * Returns an instance with the provided HTTP method.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method Case-sensitive method.
	 *
	 * @return static
	 * @throws \InvalidArgumentException for invalid HTTP methods.
	 */
	public function withMethod( $method ) {

		if ( $this->http_message->getMethod() === $method ) {
			return $this;
		}

		$clone = clone $this;

		$clone->http_message = $this->http_message->withMethod( $method );

		$clone->set_wp_method_from_http_message();

		return $clone;
	}

	/**
	 * Returns the request URI.
	 *
	 * @since 1.0.0
	 *
	 * @return UriInterface Request URI object.
	 */
	public function getUri() {

		return $this->http_message->getUri();
	}

	/**
	 * Returns an instance with the provided URI.
	 *
	 * @since 1.0.0
	 *
	 * @param UriInterface $uri           New request URI to use.
	 * @param bool         $preserve_host Preserve the original state of the Host header.
	 *
	 * @return static
	 */
	public function withUri( UriInterface $uri, $preserve_host = false ) {

		if ( $this->http_message->getUri() === $uri ) {
			return $this;
		}

		$clone = clone $this;

		$clone->http_message = $this->http_message->withUri( $uri, $preserve_host );

		if ( ! $preserve_host && $uri->getHost() !== '' ) {
			$clone->set_wp_headers_from_http_message();
		}

		return $clone;
	}

	/**
	 * Returns the server parameters.
	 *
	 * @since 1.0.0
	 *
	 * @return array Server parameters.
	 */
	public function getServerParams() {

		return $this->http_message->getServerParams();
	}

	/**
	 * Returns the cookies sent by the client to the server.
	 *
	 * @since 1.0.0
	 *
	 * @return array Cookie parameters.
	 */
	public function getCookieParams() {

		return $this->http_message->getCookieParams();
	}

	/**
	 * Returns an instance with the specified cookies.
	 *
	 * @since 1.0.0
	 *
	 * @param array $cookies Array of key/value pairs representing cookies.
	 *
	 * @return static
	 */
	public function withCookieParams( array $cookies ) {

		$clone = clone $this;

		$clone->http_message = $this->http_message->withCookieParams( $cookies );

		return $clone;
	}

	/**
	 * Returns the query string arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return array Query string arguments.
	 */
	public function getQueryParams() {

		return $this->http_message->getQueryParams();
	}

	/**
	 * Returns an instance with the specified query string arguments.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query Array of query string arguments.
	 *
	 * @return static
	 */
	public function withQueryParams( array $query ) {

		$clone = clone $this;

		$clone->http_message = $this->http_message->withQueryParams( $query );

		$clone->set_wp_query_params_from_http_message();

		return $clone;
	}

	/**
	 * Returns normalized file upload data.
	 *
	 * @since 1.0.0
	 *
	 * @return UploadedFileInterface[] An array of uploaded file objects.
	 */
	public function getUploadedFiles() {

		return $this->http_message->getUploadedFiles();
	}

	/**
	 * Returns an instance with the specified uploaded files.
	 *
	 * @since 1.0.0
	 *
	 * @param array $uploaded_files An array tree of UploadedFileInterface instances.
	 *
	 * @return static
	 */
	public function withUploadedFiles( array $uploaded_files ) {

		$clone = clone $this;

		$clone->http_message = $this->http_message->withUploadedFiles( $uploaded_files );

		$clone->set_wp_file_params_from_http_message();

		return $clone;
	}

	/**
	 * Returns any parameters provided in the request body.
	 *
	 * @since 1.0.0
	 *
	 * @return array|object|null Body parameters.
	 */
	public function getParsedBody() {

		return $this->http_message->getParsedBody();
	}

	/**
	 * Returns an instance with the specified body parameters.
	 *
	 * @since 1.0.0
	 *
	 * @param array|object|null $body Body parameters.
	 *
	 * @return static
	 */
	public function withParsedBody( $body ) {

		$clone = clone $this;

		$clone->http_message = $this->http_message->withParsedBody( $body );

		$clone->set_wp_body_params_from_http_message();

		return $clone;
	}

	/**
	 * Returns attributes derived from the request.
	 *
	 * @since 1.0.0
	 *
	 * @return array Attributes derived from the request.
	 */
	public function getAttributes() {

		return $this->http_message->getAttributes();
	}

	/**
	 * Returns a single derived request attribute, if set, and the default value as provided otherwise.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name    Attribute name.
	 * @param mixed  $default Optional. Default value to return if the attribute does not exist. Defaults to null.
	 *
	 * @return mixed Attribute value, or default value as provided.
	 */
	public function getAttribute( $name, $default = null ) {

		$defaults = $this->get_default_params();

		$default = $defaults[ $name ] ?? $default;

		return $this->http_message->getAttribute( $name, $default );
	}

	/**
	 * Returns an instance with the specified derived request attribute.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name  The attribute name.
	 * @param mixed  $value The value of the attribute.
	 *
	 * @return static
	 */
	public function withAttribute( $name, $value ) {

		$clone = clone $this;

		$clone->http_message = $this->http_message->withAttribute( $name, $value );

		$clone->set_wp_attributes_from_http_message();

		return $clone;
	}

	/**
	 * Returns an instance without the specified derived request attribute.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The attribute name.
	 *
	 * @return static
	 */
	public function withoutAttribute( $name ) {

		$clone = clone $this;

		$clone->http_message = $this->http_message->withoutAttribute( $name );

		$clone->set_wp_attributes_from_http_message();

		return $clone;
	}

	/**
	 * Sets the HTTP method for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method HTTP method.
	 *
	 * @return void
	 */
	public function set_method( $method ) {

		parent::set_method( $method );

		$this->http_message = $this->http_message->withMethod( $this->get_method() );
	}

	/**
	 * Sets a single HTTP header.
	 *
	 * @since 1.0.0
	 *
	 * @param string          $name  Header name.
	 * @param string[]|string $value Header value, or list of values.
	 *
	 * @return void
	 */
	public function set_header( $name, $value ) {

		parent::set_header( $name, $value );

		$this->set_http_message_with_wp_headers();
	}

	/**
	 * Appends a header value for the given header.
	 *
	 * @since 1.0.0
	 *
	 * @param string          $name  Header name.
	 * @param string[]|string $value Header value, or list of values.
	 *
	 * @return void
	 */
	public function add_header( $name, $value ) {

		parent::add_header( $name, $value );

		$this->set_http_message_with_wp_headers();
	}

	/**
	 * Removes all values for a header.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Header name.
	 *
	 * @return void
	 */
	public function remove_header( $name ) {

		parent::remove_header( $name );

		$this->set_http_message_with_wp_headers();
	}

	/**
	 * Sets a parameter on the request.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name  Parameter name.
	 * @param mixed  $value Parameter value.
	 *
	 * @return void
	 */
	public function set_param( $name, $value ) {

		parent::set_param( $name, $value );

		$this->http_message = \strtoupper( $this->get_method() ) === 'POST'
			? $this->http_message->withParsedBody( $this->get_body_params() )
			: $this->http_message->withQueryParams( $this->get_query_params() );
	}

	/**
	 * Sets parameters from the query string.
	 *
	 * @since 1.0.0
	 *
	 * @param array $params Parameter map of key to value.
	 *
	 * @return void
	 */
	public function set_query_params( $params ) {

		parent::set_query_params( $params );

		$this->set_http_message_with_wp_query_params();
	}

	/**
	 * Sets parameters from the body.
	 *
	 * @since 1.0.0
	 *
	 * @param array $params Parameter map of key to value.
	 *
	 * @return void
	 */
	public function set_body_params( $params ) {

		parent::set_body_params( $params );

		$this->http_message = $this->http_message->withParsedBody( $this->get_body_params() );
	}

	/**
	 * Sets multipart file parameters from the body.
	 *
	 * @since 1.0.0
	 *
	 * @param array $params Parameter map of key to value.
	 *
	 * @return void
	 */
	public function set_file_params( $params ) {

		parent::set_file_params( $params );

		$this->set_http_message_with_wp_file_params();
	}

	/**
	 * Sets the request body data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $body Binary data from the request body.
	 *
	 * @return void
	 */
	public function set_body( $body ) {

		parent::set_body( $body );

		$this->http_message = $this->http_message
			->withBody( stream_for( $this->get_body() ) )
			->withParsedBody( $this->get_body_params() );
	}

	/**
	 * Sets the attributes for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes Attributes for the request.
	 *
	 * @return void
	 */
	public function set_attributes( $attributes ) {

		parent::set_attributes( $attributes );

		$this->set_http_message_with_wp_attributes();
	}

	/**
	 * Sanitizes (where possible) the params on the request.
	 *
	 * @since 1.0.0
	 *
	 * @return true|\WP_Error True if parameters were sanitized, WP_Error if an error occurred during sanitization.
	 */
	public function sanitize_params() {

		$result = parent::sanitize_params();

		$this->set_http_message_with_wp_query_params();
		$this->set_http_message_with_wp_body_params();
		$this->set_http_message_with_wp_file_params();

		return $result;
	}

	/**
	 * Removes a parameter from the request.
	 *
	 * @since 1.0.0
	 *
	 * @param string $offset Parameter name.
	 *
	 * @return void
	 */
	public function offsetUnset( $offset ) {

		parent::offsetUnset( $offset );

		$this->set_http_message_with_wp_query_params();
		$this->set_http_message_with_wp_body_params();
		$this->set_http_message_with_wp_file_params();
	}

	/**
	 * Sets the PSR-7-compliant HTTP message with the WordPress-specific HTTP headers.
	 *
	 * @return void
	 */
	private function set_http_message_with_wp_attributes() {

		$http_message = $this->http_message;

		$wp_attributes = $this->get_attributes();

		// Remove obsolete attributes that won't be overwritten.
		foreach ( \array_keys( \array_diff_key( $http_message->getAttributes(), $wp_attributes ) ) as $name ) {
			$http_message = $http_message->withoutAttribute( $name );
		}

		// Set new attributes.
		foreach ( $wp_attributes as $name => $value ) {
			$http_message = $http_message->withAttribute( $name, $value );
		}

		$this->http_message = $http_message;
	}

	/**
	 * Sets the PSR-7-compliant HTTP message with the WordPress-specific HTTP headers.
	 *
	 * @return void
	 */
	private function set_http_message_with_wp_headers() {

		$http_message = $this->http_message;

		// For whatever reason, there is no withHeaders() in PSR-7 HTTP messages, so we (have to) create a new one.
		$this->http_message = new PSR7Request(
			$http_message->getMethod(),
			$http_message->getUri(),
			(array) ( $this->get_headers() ?? [] ),
			$http_message->getBody(),
			$http_message->getProtocolVersion(),
			(array) ( $http_message->getServerParams() ?? [] )
		);
	}

	/**
	 * Sets the PSR-7-compliant HTTP message with the WordPress-specific body parameters.
	 *
	 * @return void
	 */
	private function set_http_message_with_wp_body_params() {

		$this->http_message = $this->http_message->withParsedBody( $this->get_body_params() );
	}

	/**
	 * Sets the PSR-7-compliant HTTP message with the WordPress-specific file parameters.
	 *
	 * @return void
	 */
	private function set_http_message_with_wp_file_params() {

		$files = (array) ( $this->get_file_params() ?: [] );
		if ( $files ) {
			$files = \array_filter( $files, 'is_array' );
		}

		if ( $files ) {
			$files = \array_map( function ( array $file ) {

				return new PSR7UploadedFile(
					(string) ( $file['tmp_name'] ?? $file['name'] ?? '' ),
					(int) ( $file['size'] ?? 0 ),
					(int) ( $file['error'] ?? 0 ),
					$file['name'] ? (string) $file['name'] : null,
					$file['type'] ? (string) $file['type'] : null
				);
			}, $files );
		}

		$this->http_message = $this->http_message->withUploadedFiles( $files );
	}

	/**
	 * Sets the PSR-7-compliant HTTP message with the WordPress-specific query parameters.
	 *
	 * @return void
	 */
	private function set_http_message_with_wp_query_params() {

		$this->http_message = $this->http_message->withQueryParams( $this->get_query_params() );
	}

	/**
	 * Sets the WordPress-specific attributes according to the actual ones of the PSR-7-compliant HTTP message.
	 *
	 * @return void
	 */
	private function set_wp_attributes_from_http_message() {

		parent::set_attributes( $this->http_message->getAttributes() );
	}

	/**
	 * Sets the WordPress-specific body parameters according to the actual ones of the PSR-7-compliant HTTP message.
	 *
	 * @return void
	 */
	private function set_wp_body_params_from_http_message() {

		parent::set_body_params( (array) ( $this->http_message->getParsedBody() ?: [] ) );
	}

	/**
	 * Sets the WordPress-specific HTTP message data according to the actual data of the PSR-7-compliant HTTP message.
	 *
	 * @return void
	 */
	private function set_wp_data_from_http_message() {

		parent::set_body( (string) $this->http_message->getBody() );
	}

	/**
	 * Sets the WordPress-specific file parameters according to the actual ones of the PSR-7-compliant HTTP message.
	 *
	 * @return void
	 */
	private function set_wp_file_params_from_http_message() {

		$files = $this->http_message->getUploadedFiles();
		if ( $files ) {
			$files = \array_map( function ( UploadedFileInterface $file ) {

				return [
					'name'     => $file->getClientFilename(),
					'type'     => $file->getClientMediaType(),
					'size'     => $file->getSize(),
					'tmp_name' => $file->getClientFilename(),
					'error'    => $file->getError(),
				];
			}, $files );
		}

		parent::set_file_params( $files );
	}

	/**
	 * Sets the WordPress-specific HTTP headers according to the actual data of the PSR-7-compliant HTTP message.
	 *
	 * @return void
	 */
	private function set_wp_headers_from_http_message() {

		parent::set_headers( $this->http_message->getHeaders() );
	}

	/**
	 * Sets the WordPress-specific HTTP method according to the actual method of the PSR-7-compliant HTTP message.
	 *
	 * @return void
	 */
	private function set_wp_method_from_http_message() {

		parent::set_method( $this->http_message->getMethod() );
	}

	/**
	 * Sets the WordPress-specific query parameters according to the actual ones of the PSR-7-compliant HTTP message.
	 *
	 * @return void
	 */
	private function set_wp_query_params_from_http_message() {

		parent::set_query_params( $this->http_message->getQueryParams() );
	}
}
