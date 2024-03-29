<?php

namespace App\Controller\Admin;

use App\RequestHandler;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use SQLite3Stmt;

class AppointmentController {
	const SLOT_REQUEST = 'SELECT COUNT(*) as `count`
						   FROM `appointments`
						   WHERE 
				           `from` > (:from - 7200) AND `from` < (:from + 7200);';

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

	public function index(RequestHandler $request, $errors = []) {
		$from = $request->get('from', mktime(0, 0, 0, date("n"), date("j") - date("N") + 1));

		$appointments_tmp = $this->appointments($request, $from);
		$appointments = [];
		foreach ($appointments_tmp as $appointment) {
			$appointments[$appointment['from']] = $appointment;
		}

		return $request->view('appointments.index')
		               ->with('appointments', $appointments)
		               ->with('attendantsNoSlot', $this->attendantsNoSlot($request))
		               ->with('from', $from)
		               ->render();
	}

	public function create(RequestHandler $request, $errors = []) {
		$from = $request->get('from', mktime(0, 0, 0, date("n"), date("j") - date("N") + 1));
		$slotsToChoose = $request->get('slotsToChoose', 5);
		if($request->get('submit') === '+') $slotsToChoose += 5;

		$appointments_tmp = $this->appointments($request);
		$appointments = [];
		foreach ($appointments_tmp as $appointment) {
			$appointments[$appointment['from']] = $appointment;
		}

		return $request->view('appointments.create')
		               ->with('slotsAvailable', $this->getFreeAppointments($request))
		               ->with('appointments', $appointments)
		               ->with('attendantsNoSlot', $this->attendantsNoSlot($request))
		               ->with('slots', $request->get('slots'))
		               ->with('slotsToChoose', $slotsToChoose)
		               ->with('errors', $errors)
					   ->with('from', $from)
		               ->render();
	}

	public function store(RequestHandler $request) {
		$slots = $request->get('slots');
		$timezone = new DateTimeZone('Europe/Berlin');
		[ $errors, $slotsToSave ] = $this->create_checkInput( $slots, $timezone, $request );

		// Wenn wir bis hier Fehler hatte, breche ab!
		if (count($errors) > 0) return $this->create($request, $errors);

		// Füge den Datensatz ein
		$statement = $request->db()->db()->prepare('SELECT * FROM `interviewers` WHERE `is_default` != 0  LIMIT 0,1;');
		$result = $statement->execute();
		$interviewer = $result->fetchArray();

		$statement = $request->db()->db()->prepare(
			'INSERT INTO `appointments`
    				(`from`, `interviewer_id`)
    				VALUES (:from, :i_id);'
		);
		$statement->bindParam(':i_id', $interviewer['id'], SQLITE3_INTEGER);

		/** @var DateTime $slot */
		foreach($slotsToSave as $slot) {
			$timestamp = $slot->getTimestamp();
			$statement->bindParam(':from', $timestamp, SQLITE3_INTEGER);
			$statement->execute();
		}

		$appointments_tmp = $this->appointments($request);
		$appointments = [];
		foreach ($appointments_tmp as $appointment) {
			$appointments[$appointment['from']] = $appointment;
		}

		// Rückgabe
		$slotsToChoose = $request->get('slotsToChoose', 5);
		if($request->get('submit') === '+') $slotsToChoose += 5;

		return $request->view('appointments.create')
		               ->with('slotsAvailable', $this->getFreeAppointments($request))
		               ->with('appointments', $appointments)
		               ->with('attendantsNoSlot', $this->attendantsNoSlot($request))
		               ->with('slotsToChoose', $slotsToChoose)
		               ->with('success', true)
			           ->with('from', $request->get('from', mktime(0, 0, 0, date("n"), date("j") - date("N") + 1)))
		               ->render();
	}

	public function delete(RequestHandler $request) {
		if (!$request->get('confirmed'))
			return $this->requestDelete($request);

		$from = $request->get('from');
		$i_id = $request->get('i_id');
		// Füge den Datensatz ein
		$query = 'DELETE
		           FROM `appointments`
				   WHERE `appointments`.`from` = :from AND `appointments`.`interviewer_id` = :i_id;';
		$statement = $request->db()->db()->prepare($query);
		$statement->bindParam(':from', $from);
		$statement->bindParam(':i_id', $i_id);
		$statement->execute();

		$request->redirect(url('admin/appointments', ['mode' => 'o']));
	}

	public function requestDelete(RequestHandler $request) {
		$from = $request->get('from');
		$i_id = $request->get('i_id');
		// Füge den Datensatz ein
		$query = 'SELECT *
		           FROM `appointments`
				       LEFT JOIN `attendents` on attendents.id = appointments.attendent_id
				       LEFT JOIN `interviewers` on appointments.interviewer_id = interviewers.id
				   WHERE `appointments`.`from` = :from AND `appointments`.`interviewer_id` = :i_id;';
		$statement = $request->db()->db()->prepare($query);
		$statement->bindParam(':from', $from);
		$statement->bindParam(':i_id', $i_id);
		$result = $statement->execute();
		$appointment = $result->fetchArray();

		return $request->view('appointments.delete')
		               ->with('appointment', $appointment)
		               ->render();
	}

	/**
	 * @param RequestHandler $request
	 *
	 * @throws Exception
	 */
	private function getFreeAppointments(RequestHandler $request): array {
		/** @var SQLite3Stmt $slotRequest */
		$slotRequest = $request->db()->db()->prepare(self::SLOT_REQUEST);
		$slotRequest->bindParam(':from', $from, SQLITE3_INTEGER);

		$slots = [];
		// bekomme die 2 h slots
		$slot = new DateTime($request->get('time', 'now'));
		$slot->setTimezone(new DateTimeZone('Europe/Berlin'));
		$adjusted = $slot->getTimestamp() + (7200 - $slot->getTimestamp() % 7200);
		$slot->setTimestamp($adjusted);
		$interval2Hours = DateInterval::createFromDateString('+ 15 minutes');
		for (; $slot->getTimestamp() <= $adjusted + 30 * 24 * 60 * 60; $slot->add($interval2Hours)) {
			$from = $slot->getTimestamp();
			$result = $slotRequest->execute();
			$data = $result->fetchArray();
			if ($data[0] == 0) {
				$date = $slot->format('Y-m-d (D.)');
				if (!array_key_exists($date, $slots)) $slots[$date] = [];
				$slots[$date][] = $slot->format('H:i');
			}
		}

		$slotRequest->close();

		return $slots;
	}

	/**
	 * @param RequestHandler $request
	 *
	 * @return array
	 *
	 * @throws Exception
	 */
	private function appointments(RequestHandler $request, ?int $from = null): array {
		// Erstelle die Query
		$query = 'SELECT *
		           FROM `appointments`
				       LEFT JOIN `attendents` on attendents.id = appointments.attendent_id
				       LEFT JOIN `interviewers` on appointments.interviewer_id = interviewers.id';
		if ($from) $query .= "\n" . 'WHERE `from` >= :from AND `from` <= (:from + 604800)';
		$query .= "\n" . 'ORDER BY `from`;';

		/** @var SQLite3Stmt $appointmentRequest */
		$appointmentRequest = $request->db()->db()->prepare($query);
		$appointmentRequest->bindParam(':from', $from, SQLITE3_INTEGER);
		$result = $appointmentRequest->execute();
		$data = [];
		while ($data[] = $result->fetchArray(SQLITE3_ASSOC));

		$appointmentRequest->close();

		return array_filter($data);
	}

	/**
	 * @param RequestHandler $request
	 *
	 * @throws Exception
	 */
	private function attendantsNoSlot(RequestHandler $request): array {
		/** @var SQLite3Stmt $appointmentRequest */
		$appointmentRequest = $request->db()->db()->prepare(
			'SELECT *
				   FROM `attendents`
				   LEFT OUTER JOIN appointments a on attendents.id = a.attendent_id
				   WHERE `from` IS NULL
				   ORDER BY number;'
		);
		$result = $appointmentRequest->execute();
		$data = [];
		while ($data[] = $result->fetchArray(SQLITE3_ASSOC));

		$appointmentRequest->close();

		return array_filter($data);
	}

	private function addError(array &$error, int $line, string $message) {
		if(!array_key_exists($line, $error)) {
			$error[$line] = [];
		}

		$error[$line][] = $message;
	}

	/**
	 * @param              $slots
	 * @param DateTimeZone $timezone
	 *
	 * @return array
	 */
	public function create_checkInput( $slots, DateTimeZone $timezone, RequestHandler $request ): array {
		// Ermittel welche Slots wir potentiell speichern müssten
		$errors      = [];
		$slotsToSave = [];

		foreach ( $slots as $num => $slot ) {
			$day = data_get( $slot, 'day', -1 );
			if ( $day != -1 ) {
				$time = data_get( $slot, 'time' );

				if ( ! $time || $time == - 1 ) {
					$this->addError( $errors, $num, 'Termin ' . $num . ' hat keine Zeitangabe.' );
				} else {
					$date          = DateTime::createFromFormat( 'Y-m-d (D.)H:i', $day . $time, $timezone );
					$slotsToSave[] = $date;
				}
			}
		}

		// Sortiere die Slotas
		usort( $slotsToSave, function ( $a, $b ) {
			if ( $a == $b ) {
				return 0;
			}

			return ( $a < $b ) ? - 1 : 1;
		} );


		// prüfe auf Widersprüche
		for ( $i = 0; $i < count( $slotsToSave ); ++ $i ) {
			for ( $j = $i; $j < count( $slotsToSave ); ++ $j ) {
				if ( $i == $j ) {
					continue;
				}

				$diff = abs( $slotsToSave[ $i ]->getTimestamp() - $slotsToSave[ $j ]->getTimestamp() );
				if ( $diff < 7200 ) {
					$this->addError( $errors, $i, 'Termin ' . $i . ' wiederspricht Termin ' . $j . '.');
					$this->addError( $errors, $j, 'Termin ' . $j . ' wiederspricht Termin ' . $i . '.');
				}
			}
		}

		// prüfe ob die Slots bereits existieren
		$statement = $request->db()->db()->prepare(self::SLOT_REQUEST);
		foreach($slotsToSave as $num => $slot) {
			$timestamp = $slot->getTimestamp();
			$statement->bindParam(':from', $timestamp, SQLITE3_INTEGER);
			$result = $statement->execute();
			$data = $result->fetchArray();
			if ($data[0] != 0) {
				$this->addError( $errors, $num, 'Der Termin ist bereits geblockt.');
			}
		}
		$statement->close();

		return [ $errors, $slotsToSave ];
	}
}

