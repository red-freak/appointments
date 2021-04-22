<?php
    require_once BASE_DIR . DIRECTORY_SEPARATOR. 'resources' . DIRECTORY_SEPARATOR
                 . 'views' . DIRECTORY_SEPARATOR . 'helpers.php';
?>

<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="UTF-8">
	<title>RedFreak_ Appointment-Admin - 0.0.1</title>
    <link rel="stylesheet" href="<?= style('app.css') ?>">
</head>
<body>
    <div class="side-wrapper">
        @include('partials.header')
        <div class="content-wrapper">
            <aside>
                @include('partials.sidebar')
            </aside>
            <main>
                <?= $content ?>
            </main>
        </div>
        @include('partials.footer')
    </div>
</body>
</html>