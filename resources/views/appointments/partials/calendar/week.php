<?php $days = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So']; ?>

<table class="c-calendar is-week">
    <thead>
        <tr>
            <?php for($i = 0; $i < 7; ++$i) { ?>
                <th><?= $days[$i] ?></th>
            <?php } ?>
        </tr>
    </thead>
<!--    -->
<!--        <tr class="c-calendar-day is-column">-->
<!--            <div class="c-calendar-slot is-row">--><?//= $days[$i] ?><!--</div>-->
<!--            --><?php //for($j = 0; $j < 96; ++$j) { ?>
<!--                <div class="c-calendar-slot is-row"></div>-->
<!--            --><?php //} ?>
<!--        </tr>-->
<!--    --><?php //} ?>
</table>