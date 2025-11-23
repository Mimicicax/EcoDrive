<?php use function EcoDrive\Routing\route ?>

<div class="login card">
    <h1>Bejelentkezés</h1>

    <form action="<?= route("login") ?>" method="POST">
        <?php if (isset($errors["loginError"])): ?>
            <div class="input-group error">
        <?php else: ?>
            <div class="input-group">
        <?php endif ?>
            <label for="username">Felhasználónév</label>
            <input type="text" name="username" id="username" placeholder="Felhasználónév vagy email cím">
        </div>

        <?php if (isset($errors["loginError"])): ?>
            <div class="input-group error">
        <?php else: ?>
            <div class="input-group">
        <?php endif ?>
            <label for="password">Jelszó</label>
            <input type="password" name="password" id="password" placeholder="Jelszó">
        </div>

        <?php if (isset($errors["loginError"])): ?>
            <span class="error">
                <?= $errors["loginError"] ?>
            </span>
        <?php endif ?>

        <a id="forgotten-password" href="">Elfelejtett jelszó</a>

        <span>
            <a href="<?= route("register") ?>" class="button">Még nincs fiókom</a>
            <input type="submit" class="button primary" value="Bejelentkezés">
        </span>
    </form>
</div>