<?php

if (!isset($content)) $content = '';

if (!function_exists('style')) {
	function style(string $path): string {
		return BASE_URL . 'resources/styles/' . $path;
	}
}

if (!function_exists('url')) {
	function url(string $path): string {
		return BASE_URL . $path;
	}
}

if (!function_exists('selected')) {
	function selected($value1, $value2): string {
		return ($value1 == $value2) ? ' selected="selected"' : '';
	}
}

if (!function_exists('data_get')) {
	function data_get($haystack, $needle, $default = null) {
		$needleParts = array_reverse(explode('.', $needle));
		$currentHaystack = $haystack;

		while (count($needleParts) > 0) {
			$key = array_pop($needleParts);

			if (is_array($currentHaystack)) {
				if (!array_key_exists($key, $currentHaystack)) return $default;

				$currentHaystack = $currentHaystack[$key];
			} elseif (is_object($currentHaystack)) {
				if (!property_exists($currentHaystack, $key)) return $default;

				$currentHaystack = $currentHaystack->$$key;
			} else {
				// Was auch immer das an der Stelle sein soll.
				return $default;
			}
		}

		return $currentHaystack ?: $default;
	}
}

if (!function_exists('dd')) {
	function dd(...$values) {
		foreach ( $values as $item ) {
			print_r($item);
		}

		die();
	}
}

//if (!isset($appointments)) $appointments = json_decode(sendPOST(
//	'https://amor.cms.hu-berlin.de/~georgesv/auc/',
//	['method' => 'GET', 'time' => time()]
//), true);
//registerVariable($appointments);
//
//html('<div style="display: flex; flex-wrap: wrap;">');
//foreach($appointments as $num => $appointmentAvailable) {
//	html('<div style="width: 33%; margin: 6pt 0;">');
//	html('<input type="radio" id="appointment_' .$num . '" name="appointment" value="' . $appointmentAvailable['from'] . '_' . $appointmentAvailable['interviewer_id'] . '">');
//	html('<label for="appointment_' .$num . '">' . date('(D) d.m.Y H:i', $appointmentAvailable['from']) . '</label>');
//	html('</div>');
//}
//html('</div>');
//
//if (!isset($appointment)) $appointment = null;
//registerVariable($appointment);
//
//debug($appointment);
//
//
//
//$appointment = readGET('appointment');
//
//if (!$appointment) {
//	repeatPage('ERROR_AP_NOT_SELECTED');
//} else {
//	$result = json_decode(sendPOST(
//		'https://amor.cms.hu-berlin.de/~georgesv/auc/',
//		[
//			'appointment' => $appointment,
//			'num' => caseNumber(),
//			'token' => caseToken(),
//		]
//	), true);
//
//	if ($result[0] === 201) {
//		html('<div style="background-color: #ebffd5; margin: 10pt; padding: 10pt; font-weight: 700;">Der Termin wurde vorgemerkt.</div>');
//    } else {
//		repeatPage('ERROR_AP_SELECTING');
//	}
//}
//






