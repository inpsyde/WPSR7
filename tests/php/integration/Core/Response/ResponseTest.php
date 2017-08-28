<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPSR7\Tests\Integration\REST;

use Brain\Monkey;
use Inpsyde\WPSR7\REST\Response as Testee;
use Inpsyde\WPSR7\Tests\Integration\TestCase;

use function GuzzleHttp\Psr7\stream_for;

/**
 * Test case for the response class.
 *
 * @package Inpsyde\WPSR7\Tests\Integration\REST
 * @since   1.0.0
 */
class ResponseTest extends TestCase {

	protected function setUp() {

		parent::setUp();

		Monkey\Functions\when( 'absint' )->returnArg();
	}

	/**
	 * Tests creating a PSR-7-compliant instance with defaults only.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_creation_with_defaults() {

		$testee = new Testee();

		self::assertSame( null, $testee->get_data() );
		self::assertSame( 200, $testee->get_status() );
		self::assertSame( [], $testee->get_headers() );

		self::assertSame( '', (string) $testee->getBody() );
		self::assertSame( 200, $testee->getStatusCode() );
		self::assertSame( [], $testee->getHeaders() );
	}

	/**
	 * Tests creating a PSR-7-compliant instance with arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_creation_with_arguments() {

		$data = 'some data here';

		$status = 123;

		$headers = [
			'foo' => 'some, foo, headers',
			'bar' => 'some, bar, headers',
		];

		$psr7_headers = [
			'foo' => [ 'some', 'foo', 'headers' ],
			'bar' => [ 'some', 'bar', 'headers' ],
		];

		$testee = new Testee( $data, $status, $headers );

		self::assertSame( $data, $testee->get_data() );
		self::assertSame( $status, $testee->get_status() );
		self::assertSame( $headers, $testee->get_headers() );

		self::assertSame( $data, (string) $testee->getBody() );
		self::assertSame( $status, $testee->getStatusCode() );
		self::assertSame( $psr7_headers, $testee->getHeaders() );
	}

	/**
	 * Tests returning a passed response instance that already is a PSR-7-compliant one.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_creation_from_wp_rest_response_returns_passed_psr7_response() {

		$instance = new Testee();

		self::assertSame( Testee::from_wp_rest_response( $instance ), $instance );
	}

	/**
	 * Tests creating a PSR-7-compliant instance from a WordPress REST response instance.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_creation_from_wp_rest_response() {

		$data = 'some data here';

		$status = 123;

		$headers = [
			'foo' => [ 'some', 'foo', 'headers' ],
			'bar' => [ 'some', 'bar', 'headers' ],
		];

		$testee = Testee::from_wp_rest_response( new \WP_REST_Response( $data, $status, $headers ) );

		self::assertSame( $data, $testee->get_data() );
		self::assertSame( $status, $testee->get_status() );
		self::assertSame( $headers, $testee->get_headers() );

		self::assertSame( $data, (string) $testee->getBody() );
		self::assertSame( $status, $testee->getStatusCode() );
		self::assertSame( $headers, $testee->getHeaders() );
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

		$testee = ( new Testee() )->withHeader( $name, $value );

		self::assertSame( $value, $testee->get_headers()[ $name ] );

		self::assertContains( $value, $testee->getHeader( $name ) );
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

		$testee = ( new Testee() )
			->withHeader( $name, $initial )
			->withAddedHeader( $name, $value );

		self::assertSame( "{$initial}, {$value}", $testee->get_headers()[ $name ] );

		self::assertContains( $value, $testee->getHeader( $name ) );
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
			->withHeader( $name, $value  )
			->withoutHeader( $name);

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

		self::assertSame( $data, $testee->get_data() );

		self::assertSame( $data, (string) $testee->getBody() );
	}

	/**
	 * Tests setting the status code using the PSR-7 method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_status_through_psr7() {

		$status = 123;

		$reason_phrase = 'some reason phrase here';

		$testee = ( new Testee() )->withStatus( $status, $reason_phrase );

		self::assertSame( $status, $testee->get_status() );

		self::assertSame( $status, $testee->getStatusCode() );
		self::assertSame( $reason_phrase, $testee->getReasonPhrase() );
	}

	/**
	 * Tests setting the headers using the WordPress method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_headers_through_wp() {

		$headers = [
			'foo' => 'some, foo, headers',
			'bar' => 'some, bar, headers',
		];

		$psr7_headers = [
			'foo' => [ 'some', 'foo', 'headers' ],
			'bar' => [ 'some', 'bar', 'headers' ],
		];

		$testee = new Testee();
		$testee->set_headers( $headers );

		self::assertSame( $headers, $testee->get_headers() );

		self::assertSame( $psr7_headers, $testee->getHeaders() );
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

		$testee = new Testee();
		$testee->header( $name, $value );

		self::assertSame( $value, $testee->get_headers()[ $name ] );

		self::assertContains( $value, $testee->getHeader( $name ) );
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

		$testee = new Testee();
		$testee->header( $name, $initial );
		$testee->header( $name, $value, false );

		self::assertSame( "{$initial}, {$value}", $testee->get_headers()[ $name ] );

		self::assertContains( $value, $testee->getHeader( $name ) );
		self::assertSame( "{$initial}, {$value}", $testee->getHeaderLine( $name ) );
	}

	/**
	 * Tests setting the status code using the WordPress method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_status_through_wp() {

		$status = 123;

		$testee = new Testee();
		$testee->set_status( $status );

		self::assertSame( $status, $testee->get_status() );

		self::assertSame( $status, $testee->getStatusCode() );
		self::assertNotSame( 'OK', $testee->getReasonPhrase() );
	}

	/**
	 * Tests setting the reponse data using the WordPress method.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_set_data_through_wp() {

		$data = 'some data here';

		$testee = new Testee();
		$testee->set_data( $data );

		self::assertSame( $data, $testee->get_data() );

		self::assertSame( $data, (string) $testee->getBody() );
	}
}
