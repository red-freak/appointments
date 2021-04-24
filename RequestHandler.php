<?php

namespace App;

use SQLite3;

abstract class RequestHandler {
	const DB_FILE_NAME = '/db/appointments.sqlite';

	/** @var SqLiteHandler $dbHandler */
	private $dbHandler;

	protected $method;

	public static $currentRequest;

	/**
	 * RequestHandler constructor.
	 */
	public function __construct() {
		self::$currentRequest = $this;

		$this->method = strtoupper($this->get('method', $_SERVER['REQUEST_METHOD']));
		$this->dbHandler = $this->initDb();

		return $this;
	}

	/**
	 * "Routes" the request by verb.
	 */
	public abstract function handle();

	private function initDb(): SqLiteHandler {
		$dbConnection = new SQLite3(BASE_DIR . self::DB_FILE_NAME);
		return SqLiteHandler::instance($dbConnection);
	}

	public function db() {
		return $this->dbHandler;
	}

	/**
	 * @param string $name
	 *
	 * @return ViewHandler
	 */
	public function view(string $name) {
		return new ViewHandler($name);
	}

	/**
	 * @param string $name
	 * @param null   $default
	 *
	 * @return mixed|null
	 */
	public function get(string $name, $default = null) {
		if (array_key_exists($name, $_GET)) $value = $_GET[$name];
		if (array_key_exists($name, $_POST)) $value = $_POST[$name];

		return isset($value) && $value ? $value : $default;
	}

	/**
	 * @return array
	 */
	public function all(): array {
		$pairs = [];
		foreach ($_GET as $key => $value) $pairs[$key] = $value;
		foreach ($_POST as $key => $value) $pairs[$key] = $value;

		return $pairs;
	}
}