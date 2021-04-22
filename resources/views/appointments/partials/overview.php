<h2>&Uuml;bersicht</h2>
<table>
	<thead>
		<tr>
			<th>Datum</th>
			<th>Uhrzeit</th>
			<th>Interviewer</th>
			<th>Teilnehmer</th>
		</tr>
	</thead>
	<tbody>
        <tr>
            <td class="headline" colspan="4">Mit Termin</td>
        </tr>
		<?php foreach($appointments as $appointment) { ?>
		<tr>
			<td><?= date("d.m.Y", data_get($appointment, 'from')) ?></td>
			<td><?= date("H:i", data_get($appointment, 'from')) ?> bis <?= date("H:i", data_get($appointment, 'from') + 7200) ?></td>
			<td><?= data_get($appointment, 'name') ?></td>
			<td><?= data_get($appointment, 'number') ?></td>
		</tr>
		<?php } ?>
	</tbody>

    <tbody>
        <tr>
            <td class="headline" colspan="4">Ohne Termin</td>
        </tr>
	<?php foreach($attendantsNoSlot as $attendants) { ?>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td><?= data_get($attendants, 'number') ?></td>
        </tr>
	<?php } ?>
    </tbody>
</table>
<hr>
@include('appointments.partials.calendar')