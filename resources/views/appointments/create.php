<?php
 if (!isset($slots)) $slots = [];
?>

<h2>Termine anlegen</h2>

@extends('layouts.base')
<script type="application/javascript">
    window.timeslots = JSON.parse('<?= json_encode($slotsAvailable) ?>');

    function addTimeslots(i) {
        const eleSelectDay = document.getElementById('slots_' + i + '_day');
        const eleSelectTime = document.getElementById('slots_' + i + '_time');

        const options = window.timeslots[eleSelectDay.value];

        // clear old options
        const length = eleSelectTime.options.length;
        for (i = length-1; i >= 1; --i) {
            eleSelectTime.options[i] = null;
        }

        for(option of options) {
            const eleOption = document.createElement("option");
            eleOption.text = option;
            eleOption.value = option;
            eleSelectTime.add(eleOption);
        }
    }
</script>

<?php if(isset($success) && $success) { ?>
    <div class="alert alert-success">Erfolgreich gespeichert!</div>
<?php } ?>

<?php if(isset($errors) && count($errors) > 0) { ?>
    <div class="alert alert-error">Es sind Fehler aufgetreten!</div>
<?php } ?>

<form method="get" action="<?= url('admin/appointments') ?>" class="form">
    <input type="hidden" name="method" value="POST" />
    <input type="hidden" name="slotsToChoose" value="<?= $slotsToChoose ?>" />
    <?php for ($i = 0; $i < $slotsToChoose; ++$i) { ?>
        <div class="form_row">
            <?php if(isset($errors) && count(data_get($errors, $i, [])) > 0) { ?>
                <div class="alert alert-error alert-small">
                    <?php foreach(data_get($errors, $i, []) as $error) { ?>
                        <?= $error ?>
                    <?php } ?>
                </div>
            </div>
            <div class="form_row">
            <?php } ?>


            <div class="form_cell form_cell--day">Tag:
                <select name="slots[<?= $i ?>][day]" id="slots_<?= $i ?>_day" onchange="addTimeslots(<?= $i ?>)">
                    <option value="-1" <?= selected(-1, data_get($slots, $i . '.day', '-1')) ?>>---</option>
                    <?php foreach (array_keys($slotsAvailable) as $day) { ?>
                        <option value="<?= $day ?>" <?= selected($day, data_get($slots, $i . '.day', '-1')) ?>><?= $day ?></option>
	                <?php } ?>
                </select>
            </div>

            <div class="form_cell form_cell--time">Zeit:
                <select name="slots[<?= $i ?>][time]" id="slots_<?= $i ?>_time" >
                    <option value="-1" <?= selected(-1, data_get($slots, $i . '.time', '-1')) ?>>---</option>
                    <?php
                        $day = data_get($slots, $i . '.day');

                        $timeslots = [];
                        if (array_key_exists($day, $slotsAvailable)) {
	                        $timeslots = $slotsAvailable[$day];
                        }

                        if ($day != 0) {
                            foreach ($timeslots as $timeslot) { ?>
                                <option value="<?= $timeslot ?>"<?= selected($timeslot, data_get($slots, $i . '.time', '-1')) ?>><?= $timeslot ?></option>
                            <?php }
                        }
                    ?>
                </select>
            </div>
        </div>
    <?php } ?>
    <button type="submit" name="submit" value="+">+</button><br /><br />
    <button type="submit" name="submit" value="save">Speichern</button>
</form>

<hr />
@include('appointments.partials.overview")