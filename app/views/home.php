<?php use EcoDrive\Models\Session; ?>

<h1>Főoldal</h1>
<p>Felhasználó: <?= Session::currentUser()->username ?> </p>