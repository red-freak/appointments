<nav>
	<strong>Termine</strong>
	<ul>
        <li><a href="<?= url('admin/appointments', ['mode' => 'o']) ?>">&Uuml;bersicht</a></li>
        <li><a href="<?= url('admin/appointments', ['mode' => 'c']) ?>">verwalten</a></li>
	</ul>

	<strong>Interviewer</strong>
	<ul>
        <li><a href="<?= url('admin') ?>">verwalten</a></li>
	</ul>

	<strong>Teilnehmer</strong>
	<ul>
        <li><a href="<?= url('admin') ?>">verwalten</a></li>
	</ul>
</nav>