<?php $days = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So']; ?>

<div class="c-calendar-selector">
    <div class="c-calendar-selector__group">
        <div class="c-calendar-selector__item">
            <a href="<?= url('admin/appointments', ['mode' => $mode, 'from' => $from - 604800 * 2, 'grid' => 'w']) ?>">
                KW <?= date('W', $from - 604800 * 2); ?> (<?= date('d.m', $from - 604800 * 2); ?>)
            </a>
        </div>
        <div class="c-calendar-selector__item">
            <a href="<?= url('admin/appointments', ['mode' => $mode, 'from' => $from - 604800, 'grid' => 'w']) ?>">
                KW <?= date('W', $from - 604800); ?> (<?= date('d.m', $from - 604800); ?>)
            </a>
        </div>
        <div class="c-calendar-selector__item">
            KW <?= date('W', $from); ?> (<?= date('d.m', $from); ?>)
        </div>
        <div class="c-calendar-selector__item">
            <a href="<?= url('admin/appointments', ['mode' => $mode, 'from' => $from + 604800, 'grid' => 'w']) ?>">
                KW <?= date('W', $from + 604800); ?> (<?= date('d.m', $from + 604800); ?>)
            </a>
        </div>
        <div class="c-calendar-selector__item">
            <a href="<?= url('admin/appointments', ['mode' => $mode, 'from' => $from + 604800 * 2, 'grid' => 'w']) ?>">
                KW <?= date('W', $from + 604800 * 2); ?> (<?= date('d.m', $from + 604800 * 2); ?>)
            </a>
        </div>
    </div>
    <div class="c-calendar-selector__group">
        <?php if ($grid === 'w') { ?>
            <div class="c-calendar-selector__item">
                <a href="<?= url('admin/appointments', ['mode' => $mode, 'from' => $from, 'grid' => 'm']) ?>">
                    Monatsansicht
                </a>
            </div>
        <?php } else { ?>
            <div class="c-calendar-selector__item">
                <a href="<?= url('admin/appointments', ['mode' => $mode, 'from' => $from, 'grid' => 'w']) ?>">
                    Wochenansicht
                </a>
            </div>
        <?php } ?>
    </div>
</div>

<?php $ignoreTd = [0, 0, 0, 0, 0, 0, 0]; ?>

<table class="c-calendar is-week">
    <thead>
        <tr>
            <th></th>
            <?php for($j = 0; $j < 7; ++$j) { ?>
                <th><?= $days[$j] ?> (<?= date('d.m', $from + 86400 * $j); ?>)</th>
            <?php } ?>
        </tr>
    </thead>
    <tbody>
        <?php for($i = 0; $i < 96; ++$i) { ?>
            <tr class="c-calendar-day is-column">
                <?php if ($i % 4 === 0) { ?><td class="is-item" rowspan="4"><?= date('H:i', mktime(0,0,0, 1, 1, 1970) + $i * 900); ?></td><?php } ?>
	            <?php for($j = 0; $j < 7; ++$j) {
	                if ($ignoreTd[$j] <= 0) {
		                $appointment = data_get($appointments, ($from + $j * 86400 + $i * 900));
		                $attendant_number = data_get($appointment, 'number');
		                $interviewer_name = data_get($appointment, 'name');
		                $appointmentStr = trim($attendant_number ? $attendant_number . ' (' . $interviewer_name . ')' : $interviewer_name);
		                if (!empty($appointmentStr)) $appointmentStr = '<div class="appointment-control"><a href="'
                                                                       . url('admin/appointments', ['_method' => 'delete', 'from' => data_get($appointment, 'from'), 'i_id' => data_get($appointment, 'interviewer_id')])
                                                                       . '" title="Löschen">X</a></div>'
                                                                       . $appointmentStr;

		                if (!empty($appointmentStr)) $ignoreTd[$j] = 8;

                        ?>
                            <td class="is-item <?= ($attendant_number ? 'is-booked' : ($interviewer_name ? 'is-slot' : '')) ?> <?=
                                ($i % 4 === 0) ? 'is-full' : ''
                            ?>"
                                <?= (!empty($appointmentStr)) ? 'rowspan="8"' : '' ?>
                            ><?= $appointmentStr ?></td>
                        <?php
	                    }
		                if ($ignoreTd[$j] > 0) --$ignoreTd[$j];
	                }
	            ?>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php //dd($appointments); ?>