<?php

namespace App\Controller;

use App\RequestHandler;
use Throwable;

class AppointmentController {
	private static $self = null;

	private final function __construct() {
		// nil
	}

	/**
	 * Returns the Singleton-Instance of the Controller.
	 *
	 * @return AppointmentController
	 */
	public static function instance(): AppointmentController {
		if(!self::$self) {
			self::$self = new self();
		}

		return self::$self;
	}

	public function index(RequestHandler $caller, bool $admin = false): array {
		return $caller->db()->appointments($admin);
	}

	public function store(RequestHandler $request) {
		$isDummy = false;
		// Prüfe die Anfrage
		$appointment = explode('_', $request->get('appointment', '_'));
		if (!is_array($appointment) || count($appointment) !== 2 || empty($appointment[0]) || empty($appointment[1]))
			return [401, 'UNAUTHORIZED_1'];
		if ($appointment[0] == '-1' && $appointment[1] == '-1') $isDummy = true;
		$num = $request->get('num');
		if(!$num) return [401, 'UNAUTHORIZED_2'];

		$token = $request->get('token');
		if(!$token) return [401, 'UNAUTHORIZED_3'];

		if(!$isDummy) {
			$statement = $request->db()->db()->prepare(
				'SELECT COUNT(*) FROM `appointments` WHERE `from` = :from AND `interviewer_id` = :i_id AND attendent_id IS NULL;'
			);
			$statement->bindParam(':from', $appointment[0], SQLITE3_INTEGER);
			$statement->bindParam(':i_id', $appointment[1], SQLITE3_INTEGER);
			$result = $statement->execute();
			$data = $result->fetchArray();
			$statement->close();
			if ($data[0] != 1) return [400, 'BAD REQUEST'];
		}

		// Füge den Teilnehmer ein
		$tryAgain = true;
		$emeregencyCounter = 0;
		while($tryAgain) {
			try {
				++$emeregencyCounter;
				$tryAgain = false;
				$id = random_int(0, PHP_INT_MAX);
				$statement = $request->db()->db()->prepare(
					'INSERT INTO `attendents` (`id`, `token`, `number`) VALUES (:id, :token, :number);'
				);
				$statement->bindParam(':id', $id, SQLITE3_INTEGER);
				$statement->bindParam(':token', $token, SQLITE3_TEXT);
				$statement->bindParam(':number', $num, SQLITE3_INTEGER);

				$result = $statement->execute();
			} catch (Throwable $exception) {
				if ($emeregencyCounter < 1000) {
					$tryAgain = true;
				}
			}
		}

		if ($isDummy) return [201, 'DUMMY CREATED'];

		// Aktualisiere den Termin
		$statement = $request->db()->db()->prepare(
			'UPDATE `appointments` SET `attendent_id` = :a_id WHERE `from` = :from AND `interviewer_id` = :i_id;'
		);
		$statement->bindParam(':a_id', $id);
		$statement->bindParam(':from', $appointment[0], SQLITE3_INTEGER);
		$statement->bindParam(':i_id', $appointment[1], SQLITE3_INTEGER);
		$statement->execute();

		return [201, 'CREATED'];
	}
}