<?php

namespace App\Admin;

require_once ('../../autoload.php');

use App\Controller\Admin\AppointmentController;
use App\RequestHandler as BaseRequestHandler;

class RequestHandler extends BaseRequestHandler {
	/**
	 * "Routes" the request by verb.
	 */
	public function handle() {
		switch($this->method) {
			case 'GET':
				http_response_code(200);
				if ($this->get('mode') === 'o') {
					echo AppointmentController::instance()->index($this);
				} else {
					echo AppointmentController::instance()->create($this);
				}

				break;
			case 'POST':
				if ($this->get('submit') === '+') {
					http_response_code(200);
					echo AppointmentController::instance()->create($this);
				} else {
					http_response_code(201);
					echo AppointmentController::instance()->store($this);
				}
				break;
			case 'DELETE':
				http_response_code(200);
				echo AppointmentController::instance()->delete($this);
				break;
			default:
				http_response_code(405);
				echo $this->method . ' unkown';
		}
	}
}

$requestHandler = new RequestHandler();
$requestHandler->handle();