<?php use function EcoDrive\Routing\route ?>

<h1>Felhasználó-kezelés</h1>

<div class="container-header">
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