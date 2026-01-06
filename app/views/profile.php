<?php 
use function EcoDrive\Routing\route;
?><h1>Profil</h1>

<div id="profileContainer">

    <div class="card">
        <h2>Email cím és felhasználónév</h2>

        <form>
            <div class="input-group">
                <label for="username">Felhasználónév</label>
                <input type="text" name="username" id="username" placeholder="Felhasználónév" value="<?= $user->username ?>">
            </div>

            <div class="input-group">
                <label for="email">Email cím</label>
                <input type="email" name="email" id="email" placeholder="Email cím" value="<?= $user->email ?>">
            </div>
            <button type="button" class="button primary" onclick="saveProfileData(['username', 'email'], '<?= route("profile") ?>')">
                Mentés
            </button>
        </form>
    </div>

    <div class="card">
        <h2>Jelszó</h2>
        <form action="">
            <div class="input-group">
                <label for="currentPassword">Jelenlegi jelszó</label>
                <input type="password" name="currentPassword" id="currentPassword" placeholder="Jelenlegi jelszó">
            </div>

            <span class="dual-input-group">
                <div class="input-group">
                    <label for="newPassword">Új jelszó</label>
                    <input type="password" name="newPassword" id="newPassword" placeholder="Új jelszó">
                </div>

                <div class="input-group">
                    <label for="confirmPassword">Új jelszó megerősítése</label>
                    <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Új jelszó megerősítése">
                </div>
            </span>
            <button type="button" class="button primary" onclick="saveProfileData(['currentPassword', 'newPassword', 'confirmPassword'], '<?= route("profile") ?>')">
                Új jelszó beállítása
            </button>
        </form>
    </div>
</div>