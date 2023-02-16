<?php

namespace ShopShape\Traits;

/* It's a security measure to prevent direct access to the file. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Singleton {


	/**
	 * A variable that is used to store the instance of the class.
	 *
	 * @var mixed
	 */
	protected static $instance;

	/**
	 * The function is private, so it can't be called from outside the class. It has no parameters, and it doesn't return
	 * anything
	 */
	protected function __construct() {
	}

	/**
	 * If the instance is null, create a new instance of the class and return it. Otherwise, return the existing instance
	 *
	 * @return self The instance of the class.
	 */
	final public static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new self();
		}

		return static::$instance;
	}

	/**
	 * If you try to clone this class, you'll get an error.
	 */
	private function __clone() {
	}

	/**
	 * If you try to unserialize this class, it will throw an exception.
	 */
	private function __wakeup() {
	}
}
