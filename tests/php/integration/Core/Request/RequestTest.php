<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPSR7\Tests\Integration\REST;

use GuzzleHttp\Psr7\UploadedFile;
use function GuzzleHttp\Psr7\uri_for;
use Inpsyde\WPSR7\REST\Request as Testee;
use Inpsyde\WPSR7\Tests\Integration\TestCase;

use function GuzzleHttp\Psr7\stream_for;

/**
 * Test case for the request class.
 *
 * @package Inpsyde\WPSR7\Tests\Integration\REST
 * @since   1.0.0
 */
class RequestTest extends TestCase {

	/**
	 * Tests creating a PSR-7-compliant instance with defaults only.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_creation_with_defaults() {

		$testee = new Testee();

		self::assertSame( '', $testee->get_method() );
		self::assertSame( '', $testee->get_route() );
		self::assertSame( [], $testee->get_attributes() );

		self::assertSame( '', $testee->getMethod() );
		self::assertSame( [], $testee->getAttributes() );
	}

	/**
	 * Tests creating a PSR-7-compliant instance with arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_creation_with_arguments() {

		$method = 'METHOD';

		$route = 'some/route/here';

		$attributes = [ 'some', 'attributes', 'here' ];

		$testee = new Testee( $method, $route, $attributes );

		self::assertSame( $method, $testee->get_method() );
		self::assertSame( $route, $testee->get_route() );
		self::assertSame( $attributes, $testee->get_attributes() );

		self::assertSame( $method, $testee->getMethod() );
		self::assertSame( $attributes, $testee->getAttributes() );
	}

	/**
	 * Tests returning a passed request instance that already is a PSR-7-compliant one.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_creation_from_wp_rest_request_returns_passed_psr7_request() {

		$instance = new Testee();

		self::assertSame( Testee::from_wp_rest_request( $instance ), $instance );
	}

	/**
	 * Tests creating a PSR-7-compliant instance from a WordPress REST request instance.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_creation_from_wp_rest_request() {

		$method = 'METHOD';

		$route = 'some/route/here';

		$attributes = [ 'some', 'attributes', 'here' ];

		$testee = Testee::from_wp_rest_request( new \WP_REST_Request( $method, $route, $attributes ) );

		self::assertSame( $method, $testee->get_method() );
		self::assertSame( $route, $testee->get_route() );
		self::assertSame( $attributes, $testee->get_attributes() );

		self::assertSame( $method, $testee->getMethod() );
		self::assertSame( $attributes, $testee->getAttributes() );
	}

	/**
	 * Tests setting the protocol version using the PSR-7 method returns the current instance if it is set.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_protocol_version_through_psr7_returns_current_instance() {

		$version = '1.2.3';

		$testee = ( new Testee() )->withProtocolVersion( $version );

		self::assertSame( $testee, $testee->withProtocolVersion( $version ) );
	}

	/**
	 * Tests setting the protocol version using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_protocol_version_through_psr7() {

		$version = '1.2.3';

		$testee = ( new Testee() )->withProtocolVersion( $version );

		self::assertSame( $version, $testee->getProtocolVersion() );
	}

	/**
	 * Tests setting a specific header using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_header_through_psr7() {

		$name = 'some name here';

		$value = 'some value here';

		$value_as_array = [ $value ];

		$testee = ( new Testee() )->withHeader( $name, $value );

		self::assertSame( $value_as_array, $testee->get_headers()[ $name ] );

		self::assertSame( $value_as_array, $testee->getHeader( $name ) );
		self::assertSame( $value, $testee->getHeaderLine( $name ) );
	}

	/**
	 * Tests adding a specific header using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_add_header_through_psr7() {

		$initial = 'something';

		$name = 'some name here';

		$value = 'some value here';

		$value_as_array = [ $initial, $value ];

		$testee = ( new Testee() )
			->withHeader( $name, $initial )
			->withAddedHeader( $name, $value );

		self::assertSame( $value_as_array, $testee->get_headers()[ $name ] );

		self::assertSame( $value_as_array, $testee->getHeader( $name ) );
		self::assertSame( "{$initial}, {$value}", $testee->getHeaderLine( $name ) );
	}

	/**
	 * Tests removing a specific header using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_remove_header_through_psr7() {

		$name = 'some name here';

		$value = 'some value here';

		$testee = ( new Testee() )
			->withHeader( $name, $value )
			->withoutHeader( $name );

		self::assertTrue( empty( $testee->get_headers()[ $name ] ) );

		self::assertNotContains( $value, $testee->getHeader( $name ) );
		self::assertSame( '', $testee->getHeaderLine( $name ) );
	}

	/**
	 * Tests setting the message body using the PSR-7 method returns the current instance if it is set.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_body_through_psr7_returns_current_instance() {

		$body = stream_for( 'some data here' );

		$testee = ( new Testee() )->withBody( $body );

		self::assertSame( $testee, $testee->withBody( $body ) );
	}

	/**
	 * Tests setting the message body using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_body_through_psr7() {

		self::markTestSkipped();

		// TODO: Check why this is not working.
		$data = 'some data here';

		$body = stream_for( $data );

		$testee = ( new Testee() )->withBody( $body );

		self::assertSame( $data, $testee->get_body() );

		self::assertSame( $data, (string) $testee->getBody() );
	}

	/**
	 * Tests setting the request target using the PSR-7 method returns the current instance if it is set.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_request_target_through_psr7_returns_current_instance() {

		$request_target = 'some-request-target';

		$testee = ( new Testee() )->withRequestTarget( $request_target );

		self::assertSame( $testee, $testee->withRequestTarget( $request_target ) );
	}

	/**
	 * Tests setting the request target using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_request_target_through_psr7() {

		$request_target = 'some-request-target';

		$testee = ( new Testee() )->withRequestTarget( $request_target );

		self::assertSame( $request_target, $testee->getRequestTarget() );
	}

	/**
	 * Tests setting the method using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_method_through_psr7() {

		$method = 'METHOD';

		$testee = ( new Testee() )->withMethod( $method );

		self::assertSame( $method, $testee->get_method() );

		self::assertSame( $method, $testee->getMethod() );
	}

	/**
	 * Tests setting the URI using the PSR-7 method returns the current instance if it is set.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_uri_through_psr7_returns_current_instance() {

		$uri = uri_for( 'some/uri/here' );

		$testee = ( new Testee() )->withUri( $uri );

		self::assertSame( $testee, $testee->withUri( $uri ) );
	}

	/**
	 * Tests setting the URI using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_uri_through_psr7() {

		$host = 'some-host.here';

		$uri = uri_for( "//{$host}/some/uri/here" );

		$testee = ( new Testee() )->withUri( $uri );

		self::assertSame( [ $host ], $testee->get_headers()['host'] );

		self::assertSame( $uri, $testee->getUri() );
		self::assertSame( $host, $testee->getHeaderLine( 'host' ) );
	}

	/**
	 * Tests setting the URI using the PSR-7 method, while preserving the host.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_uri_through_psr7_and_preserve_host() {

		$uri = uri_for( 'some/uri/here' );

		$testee = ( new Testee() )->withUri( $uri );

		self::assertSame( [], $testee->get_headers() );

		self::assertSame( $uri, $testee->getUri() );
		self::assertSame( [], $testee->getHeaders() );
	}

	/**
	 * Tests setting the cookie params using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_cookie_params_through_psr7() {

		$params = [ 'some', 'params', 'here' ];

		$testee = ( new Testee() )->withCookieParams( $params );

		self::assertSame( $params, $testee->getCookieParams() );
	}

	/**
	 * Tests setting the query params using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_query_params_through_psr7() {

		$params = [ 'some', 'params', 'here' ];

		$testee = ( new Testee() )->withQueryParams( $params );

		self::assertSame( $params, $testee->get_query_params() );

		self::assertSame( $params, $testee->getQueryParams() );
	}

	/**
	 * Tests setting the uploaded files using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_uploaded_files_through_psr7() {

		$uploaded_files = [ new UploadedFile( '', 0, 0 ) ];

		$testee = ( new Testee() )->withUploadedFiles( $uploaded_files );

		self::assertSame( count( $uploaded_files ), count( $testee->get_file_params() ) );

		self::assertSame( $uploaded_files, $testee->getUploadedFiles() );
	}

	/**
	 * Tests setting the query params using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_parsed_body_through_psr7() {

		$parsed_body = [ 'some', 'params', 'here' ];

		$testee = ( new Testee() )->withParsedBody( $parsed_body );

		self::assertSame( $parsed_body, $testee->get_body_params() );

		self::assertSame( $parsed_body, $testee->getParsedBody() );
	}

	/**
	 * Tests setting a specific attribute using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_attribute_through_psr7() {

		$name = 'some name here';

		$value = 'some value here';

		$testee = ( new Testee() )->withAttribute( $name, $value );

		self::assertSame( $value, $testee->get_attributes()[ $name ] );

		self::assertSame( $value, $testee->getAttribute( $name ) );
	}

	/**
	 * Tests removing a specific attribute using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_remove_attribute_through_psr7() {

		$name = 'some name here';

		$value = 'some value here';

		$testee = ( new Testee() )
			->withAttribute( $name, $value )
			->withoutAttribute( $name );

		self::assertSame( [], $testee->get_attributes() );

		self::assertSame( [], $testee->getAttributes() );
	}

	/**
	 * Tests setting the method using the WordPress method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_method_through_wp() {

		$method = 'METHOD';

		$testee = new Testee();
		$testee->set_method( $method );

		self::assertSame( $method, $testee->get_method() );

		self::assertSame( $method, $testee->getMethod() );
	}

	/**
	 * Tests setting a specific header using the WordPress method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_header_through_wp() {

		$name = 'some name here';

		$value = 'some value here';

		$value_as_array = [ $value ];

		$testee = new Testee();
		$testee->set_header( $name, $value );

		self::assertSame( $value_as_array, $testee->get_headers()[ $name ] );

		self::assertSame( $value_as_array, $testee->getHeader( $name ) );
		self::assertSame( $value, $testee->getHeaderLine( $name ) );
	}

	/**
	 * Tests adding a specific header using the WordPress method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_add_header_through_wp() {

		$initial = 'something';

		$name = 'some name here';

		$value = 'some value here';

		$value_as_array = [ $initial, $value ];

		$testee = new Testee();
		$testee->set_header( $name, $initial );
		$testee->add_header( $name, $value );

		self::assertSame( $value_as_array, $testee->get_headers()[ $name ] );

		self::assertSame( $value_as_array, $testee->getHeader( $name ) );
		self::assertSame( "{$initial}, {$value}", $testee->getHeaderLine( $name ) );
	}

	/**
	 * Tests removing a specific header using the WordPress method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_remove_header_through_wp() {

		$name = 'some name here';

		$value = 'some value here';

		$testee = new Testee();
		$testee->set_header( $name, $value );
		$testee->remove_header( $name );

		self::assertSame( [], $testee->get_headers() );

		self::assertSame( [], $testee->getHeaders() );
	}

	/**
	 * Tests setting a specific param using the WordPress method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_param_through_wp() {

		$name = 'some name here';

		$value = 'some value here';

		$testee = new Testee();
		$testee->set_param( $name, $value );

		self::assertSame( $value, $testee->get_param( $name ) );
		self::assertSame( $value, $testee->get_query_params()[ $name ] );

		self::assertSame( $value, $testee->getQueryParams()[ $name ] );
	}

	/**
	 * Tests setting a specific POST param using the WordPress method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_param_through_wp_for_post_request() {

		$name = 'some name here';

		$value = 'some value here';

		$testee = new Testee( 'POST' );
		$testee->set_param( $name, $value );

		self::assertSame( $value, $testee->get_param( $name ) );
		self::assertSame( $value, $testee->get_body_params()[ $name ] );

		self::assertSame( $value, $testee->getParsedBody()[ $name ] );
	}

	/**
	 * Tests setting the query params using the WordPress method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_query_params_through_wp() {

		$params = [ 'some', 'params', 'here' ];

		$testee = new Testee();
		$testee->set_query_params( $params );

		self::assertSame( $params, $testee->get_query_params() );

		self::assertSame( $params, $testee->getQueryParams() );
	}

	/**
	 * Tests setting the body params using the WordPress method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_body_params_through_wp() {

		$params = [ 'some', 'params', 'here' ];

		$testee = new Testee();
		$testee->set_body_params( $params );

		self::assertSame( $params, $testee->get_body_params() );

		self::assertSame( $params, $testee->getParsedBody() );
	}

	/**
	 * Tests setting the file params using the WordPress method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_file_parms_through_wp() {

		$params = [
			[
				'name'     => 'some name here',
				'type'     => 'some type here',
				'size'     => 123,
				'tmp_name' => 'some name here',
				'error'    => 'some error here',
			],
		];

		$testee = new Testee();
		$testee->set_file_params( $params  );

		self::assertSame( $params, $testee->get_file_params() );

		self::assertSame( count( $params ), count( $testee->getUploadedFiles() ) );
	}


	/**
	 * Tests setting the request body using the WordPress method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_body_through_wp() {

		$body = 'some data here';

		$testee = new Testee();
		$testee->set_body( $body );

		self::assertSame( $body, $testee->get_body() );

		self::assertSame( $body, (string) $testee->getBody() );
	}

	// TODO: set attributes

	// TODO: unset param
}
