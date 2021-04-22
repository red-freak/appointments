<?php

namespace App\Admin;

require_once ('../autoload.php');

use App\Controller\Admin\HomeController;
use App\Controller\AppointmentController;
use App\RequestHandler as BaseRequestHandler;

class RequestHandler extends BaseRequestHandler {
	/**
	 * "Routes" the request by verb.
	 */
	public function handle() {
		switch($this->method) {
			case 'GET':
				http_response_code(200);
				echo HomeController::instance()->index($this, true);
				break;
			default:
				http_response_code(405);
				echo $this->method . ' unkown';
		}
	}
}

$requestHandler = new RequestHandler();
$requestHandler->handle();