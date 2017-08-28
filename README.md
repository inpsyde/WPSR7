# WPSR7

[![Version](https://img.shields.io/packagist/v/inpsyde/wpsr7.svg)](https://packagist.org/packages/inpsyde/wpsr7)
[![Status](https://img.shields.io/badge/status-active-brightgreen.svg)](https://github.com/inpsyde/WPSR7)
[![Build](https://img.shields.io/travis/inpsyde/WPSR7.svg)](http://travis-ci.org/inpsyde/WPSR7)
[![Downloads](https://img.shields.io/packagist/dt/inpsyde/wpsr7.svg)](https://packagist.org/packages/inpsyde/wpsr7)
[![License](https://img.shields.io/packagist/l/inpsyde/wpsr7.svg)](https://packagist.org/packages/inpsyde/wpsr7)

> PSR-7-compliant HTTP messages for WordPress.

## Introduction

In the PHP world in general, there is a standard (recommendation) when it comes to HTTP messages: PSR-7.
Despite things like Calypso, Gutenberg and the growing JavaScript codebase in general, WordPress is written in PHP.
Thus, wouldn’t it be nice to do what the rest of the PHP world is doing?
Isn’t there some way to leverage all the existing PSR-7 middleware and incorporate them into your (RESTful) WordPress projects?

Well, here it is.

## Table of Contents

* [Installation](#installation)
  * [Requirements](#requirements)
* [Usage](#usage)
  * [Creating a PSR-7-compliant WordPress REST Request](#creating-a-psr-7-compliant-wordpress-rest-request)
  * [Creating a PSR-7-compliant WordPress REST Response](#creating-a-psr-7-compliant-wordpress-rest-response)
  * [Using the PSR-7-compliant WordPress HTTP Messages](#using-the-psr-7-compliant-wordpress-http-messages)

## Installation

Install with [Composer](https://getcomposer.org):

```sh
$ composer require inpsyde/wpsr7
```

Run the tests:

```sh
$ vendor/bin/phpunit
```

### Requirements

This package requires PHP 7 or higher.

## Usage

The following sections will help you get started with using the classes included in this package to integrate existing PSR-7 middleware into your (RESTful) WordPress projects.

### Creating a PSR-7-compliant WordPress REST Request

If you are interested in a PSR-7-compliant WordPress REST request object, you can, of course, create a new instance yourself.
You can do this like so, with all arguments being optional:

```php
use Inpsyde\WPSR7\REST\Request;

$request = new Request(
	$method,
	$route,
	$attributes
);
```

However, it is rather unlikely, because you usually do not want to define any request-based data on your own, ... since it is already included in the current request. :)
More likely is that you want to make an existing WordPress REST request object PSR-7-compliant, like so:

```php
use Inpsyde\WPSR7\REST\Request;

// ...

$request = Request::from_wp_rest_request( $request );
```

### Creating a PSR-7-compliant WordPress REST Response

As for requests, you can also create a new response object yourself.
Again, all arguments are optional.

```php
use Inpsyde\WPSR7\REST\Response;

$response = new Response(
	$data,
	$status,
	$headers
);
```

While this might make somewhat more sense compared to requests, the usual case would be to make an existing WordPress REST response object PSR-7-compliant, which can be done like this:

```php
use Inpsyde\WPSR7\REST\Response;

// ...

$response = Response::from_wp_rest_response( $response );
```

### Using the PSR-7-compliant WordPress HTTP Messages

Once you made a WordPress HTTP message PSR-7-compliant, you can just pass it on to PSR-7 middleware.
Since you can do almost anything, it doesn't make too much sense to provide any examples here.
But if you think you really have a good one, we're happy to accept pull requests for the readme file. :)

## License

Copyright (c) 2017 Thorsten Frommen, Inpsyde GmbH

This code is licensed under the [MIT License](LICENSE).
