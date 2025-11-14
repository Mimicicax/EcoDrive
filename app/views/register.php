<?php use function EcoDrive\Routing\route ?>

<h1>Regisztráció</h1>

<form action="<?= route("register") ?>" method="POST">
    <label for="username">Felhasználónév:</label>
    <input type="text" name="username" id="username">
    <br>
    <label for="email">Email:</label>
    <input type="text" name="email" id="email">
    <br>
    <label for="password">Jelszó:</label>
    <input type="password" name="password" id="password">
    <br>
    <label for="confirmPassword">Jelszó:</label>
    <input type="password" name="confirmPassword" id="confirmPassword">
    <br>
    <input type="submit">
</form>

<?= isset($errors) ? var_dump($errors) : "" ?>