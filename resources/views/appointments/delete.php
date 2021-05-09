@extends('layouts.base')
<div class="alert alert-box alert-warn">
    <div>
        <strong>
            Bist du sich das du das Appointment "<?= data_get($appointment, 'from') ?>@<?= data_get($appointment, 'interviewer_id') ?>" löschen willst?
        </strong>
    </div>
    <div>
        <div><strong>Datum:</strong> <?= date('d.m.Y H:i:s', data_get($appointment, 'from')) ?></div>
        <div><strong>Interviewer:</strong> <?= data_get($appointment, 'name') ?></div>
        <div><strong>Vor-Umfrage:</strong> <?= data_get($appointment, 'number') ?></div>
    </div>

    <form method="get" action="<?= url('admin/appointments') ?>">
        <input type="hidden" name="mode" value="c" />
        <button type="submit">Abbrechen</button>
    </form>
    <form method="post" action="<?= url('admin/appointments') ?>">
        <input type="hidden" name="_method" value="delete" />
        <input type="hidden" name="confirmed" value="1" />
        <input type="hidden" name="from" value="<?= data_get($appointment, 'from') ?>" />
        <input type="hidden" name="i_id" value="<?= data_get($appointment, 'interviewer_id') ?>" />
        <button type="submit" class="alert-error">Ja!</button>
    </form>


</div>