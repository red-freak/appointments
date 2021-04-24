<?php
    $today = new DateTime('now', new DateTimeZone('Europe/Berlin'));
    if (!isset($grid)) $grid = 'w';
    if (!isset($from)) $from = $today->getTimestamp();
?>

@include('appointments.partials.calendar.week')