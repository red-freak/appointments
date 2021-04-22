<?php

namespace App\Controller\Admin;

use App\RequestHandler;

class HomeController {
	private static $self = null;

	private final function __construct() {
		// nil
	}

	/**
	 * Returns the Singleton-Instance of the Controller.
	 *
	 * @return self
	 */
	public static function instance(): self {
		if(!self::$self) {
			self::$self = new self();
		}

		return self::$self;
	}

	public function index(RequestHandler $caller, bool $admin = false): string {
		return $caller->view('home')->render();
	}

	public function create(RequestHandler $caller) {
		return '';
	}
}