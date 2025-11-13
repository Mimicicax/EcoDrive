<?php use function EcoDrive\Routing\route ?>

<h1>Bejelentkezés</h1>

<form action="<?= route("login") ?>" method="POST">
    <input type="text" name="username">
    <input type="password" name="password">
    <input type="submit">
</form>

<?php if (isset($loginError)): ?>
    Bejelentkezési hiba
<?php endif ?>