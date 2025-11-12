<?php use function EcoDrive\Routing\route ?>

<h1>Bejelentkezés</h1>

<form action="<?= route("login") ?>" method="POST">
    <input type="text" name="username">
    <input type="password" name="username">
    <input type="submit">
</form>
