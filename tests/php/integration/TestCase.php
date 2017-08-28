<?php # -*- coding: utf-8 -*-

namespace Inpsyde\WPSR7\Tests\Integration;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Abstract base class for all integration test case implementations.
 *
 * @package Inpsyde\WPSR7\Tests\Integration
 * @since   1.0.0
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase {

	use MockeryPHPUnitIntegration;

	/**
	 * Prepares the test environment before each test.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setUp() {

		parent::setUp();
		Monkey\setUp();

		// Define a noop function mock to be used in tests.
		Monkey\Functions\when( 'noop' )->justReturn();
	}

	/**
	 * Cleans up the test environment after each test.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function tearDown() {

		Monkey\tearDown();
		parent::tearDown();
	}
}
