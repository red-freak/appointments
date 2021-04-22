<?php

namespace App;

require_once './autoload.php';

use App\Controller\AppointmentController;

class AppointmentRequestHandler extends RequestHandler {
	public function __construct() {
		parent::__construct();
	}

	/**
	 * "Routes" the request by verb.
	 */
	public function handle() {
		switch($this->method) {
			case 'GET':
				http_response_code(200);
				echo json_encode(AppointmentController::instance()->index($this));
				break;
			case 'POST':
				http_response_code(201);
				echo json_encode(AppointmentController::instance()->store($this));
				break;
			default:
				http_response_code(405);
				echo $this->method . ' unkown';
		}
	}
}

$requestHandler = new AppointmentRequestHandler();
$requestHandler->handle();