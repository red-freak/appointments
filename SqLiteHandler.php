<?php

namespace App;

use Cassandra\Date;
use DateTime;
use DateTimeZone;
use SQLite3;

class SqLiteHandler {
	private $db;
	private static $self = null;

	private final function __construct(SQLite3 $db) {
		$this->db = $db;
		$this->db->enableExceptions(true);
		$this->init();
	}

	public function __destruct() {
		$this->db->close();
	}

	private final function init() {
		// Attendents
		$query = "CREATE TABLE IF NOT EXISTS `attendents` (
    		`id` INTEGER PRIMARY KEY UNIQUE NOT NULL,
    		`token` VARCHAR(255) NOT NULL,
    		'number' INTEGER NOT NULL
		);";
		$statement = $this->db->prepare($query);
		$statement->execute();

		// Interviewers
		$query = "CREATE TABLE IF NOT EXISTS `interviewers` (
    		`id` INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE NOT NULL,
    		`name` VARCHAR(255) NOT NULL
		);";
		$statement = $this->db->prepare($query);
		$statement->execute();

		$isDefaultExists = false;
		$query = "PRAGMA table_info(`interviewers`);";
		$statement = $this->db->prepare($query);
		$result = $statement->execute();
		while ($data = $result->fetchArray()) {
			if ($data[1] === 'is_default') {
				$isDefaultExists = true;
				break;
			}
		}

		if (!$isDefaultExists) {
			$query = "ALTER TABLE `interviewers` ADD COLUMN `is_default` SMALLINT DEFAULT 0;";
			$statement = $this->db->prepare($query);
			$statement->execute();
		}


		// Appointments
		$query = "CREATE TABLE IF NOT EXISTS `appointments` (
    		`from` INTEGER NOT NULL,
    		`interviewer_id` INTEGER NOT NULL,
    		`attendent_id` INTEGER,
    		PRIMARY KEY(`from`, `interviewer_id`),
    		FOREIGN KEY(`interviewer_id`) REFERENCES `interviewers`(`id`),
    		FOREIGN KEY(`attendent_id`) REFERENCES `attendents`(`id`)
		);";
		$statement = $this->db->prepare($query);
		$statement->execute();
	}

	private final function seed() {

	}

	/**
	 * Returns the Singleton-Instance of the Handler.
	 *
	 * @param SqLite3 $db
	 *
	 * @return SqLiteHandler
	 */
	public static function instance(SqLite3 $db): SqLiteHandler {
		if(!self::$self) {
			self::$self = new self($db);
		}

		return self::$self;
	}

	public function appointments($admin = false) {
		$now = new DateTime('now' , new DateTimeZone('Europe/Berlin'));
		$now = $now->getTimestamp();

		if ($admin) {
			$query = 'SELECT * FROM `appointments`;';
		} else {
			$query = 'SELECT `from`, `interviewer_id`
					  FROM `appointments`
					  WHERE `attendent_id` IS NULL
						AND `from` > :now
					  ORDER BY `from` ASC;';
		}
		$statement = $this->db->prepare($query);
		$statement->bindParam(':now', $now, SQLITE3_INTEGER);
		$result = $statement->execute();
		$data = [];
		while ($data[] = $result->fetchArray(SQLITE3_ASSOC));
		$data = array_filter($data);

		return $data;
	}

	public function db(): SQLite3 {
		return $this->db;
	}
}