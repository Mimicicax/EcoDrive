<?php use function EcoDrive\Routing\route ?>

<h1>Felhasználó-kezelés</h1>

<div class="admin container-header">
    <form action="<?= route("admin") ?>" method="GET">
        <span id="user-search-bar">
            <span class="input-group">
                <label for="user">Felhasználó</label>
                <input type="search" name="query" id="user" placeholder="Felhasználónév vagy email cím">
            </span>
            <button class="button primary">Keresés</button>
        </span>
    </form>
</div>

<?php if (isset($noQuery) || !isset($queriedUser)): ?>
    <div class="empty card-container">
        <span>
        <?php if (isset($noQuery)): ?>
            <h1>Keress felhasználókat</h1>
            <p>Ha felhasználókra keresel rá, azok itt fognak megjelenni</p>
        <?php else: ?>
            <h1>A felhasználó nem található</h1>
            <p>Nincs találat a következő kifejezésre: "<?= $query ?>"</p>
        <?php endif ?>
        </span>
    </div>
<?php else: ?>

    <?php if (isset($query)): ?>
        <p>Találatok megjelenítése a következő kifejezésre: "<?= $query ?>"</p>
    <?php endif ?>

    <div id="user-data-card" class="card">
        <form action="<?= route("admin") ?>" method="POST" onsubmit="return confirm('Biztosan módosítja a felhasználó adatait?')">
            <input type="hidden" name="user" value="<?= $queriedUser->id ?>">
            <span class="input-group">
                <label for="username">Felhasználónév</label>
                <input type="text" name="username" id="username" value="<?= $queriedUser->username ?>">
            </span>
            <span class="input-group">
                <label for="email">Email cím</label>
                <input type="text" name="email" id="email" value="<?= $queriedUser->email ?>">
            </span>
            <span class="input-group">
                <label for="newPass">Új jelszó</label>
                <input type="password" name="newPassword" id="newPass">
            </span>
            <span class="input-group">
                <label for="confirmPass">Jelszó megerősítése</label>
                <input type="password" name="confirmPassword" id="confirmPass">
            </span>
            <input type="submit" class="button primary" value="Mentés">
        </form>
        <form action="<?= route("admin") ?>" method="POST" onsubmit="return confirm('Biztosan törli a felhasználót?')">
            <input type="hidden" name="user" value="<?= $queriedUser->id ?>">
            <input type="hidden" name="action" value="delete">
            <input type="submit" class="button secondary danger" value="Felhasználó törlése">
        </form>
    </div>
<?php endif ?>