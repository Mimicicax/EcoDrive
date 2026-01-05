<?php
    use function EcoDrive\Environment\appConfig;
    use function EcoDrive\Routing\route;

    require_once appConfig()->APP_ROOT . "/models/Session.php";

    use \EcoDrive\Models\Session;
?>


<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href=<?= asset("style.css") ?> rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
    <script src="https://kit.fontawesome.com/b71a3cf50f.js" crossorigin="anonymous"></script>
    <script src="<?= asset("script.js") ?>"></script>
    <title>
        <?= isset($title) ? $title . " | EcoDrive" : "EcoDrive" ?>
    </title>
</head>
<body>
    <?php 
        /* 
            Ellenőrizni kell a loadFailed()-et, mert ez a fájl akkor is betöltődik ha pl. nem tudtunk csatlakozni az adatbázishoz (hogy az 500-as oldalt visszaadjuk). 
            Ilyenkor értelemszerűen nem hívhatjuk a Session::isAuthenticated()-et 
        */
     ?>
    <?php if (!appConfig()->loadFailed() && Session::isAuthenticated() && isset($activeNavLink)): ?>
    <header>
        <input type="checkbox" id="hamburger-toggle">
        <label id="hamburger-bars" for="hamburger-toggle">
            <div></div>
            <div></div>
            <div></div>
        </label>
        <nav>
            <ul>
                <li>
                    <a href="<?= route("profile") ?>" <?= $activeNavLink == route("profile") ? "class=\"active\"" : ""?>>
                        <i class="fa-solid fa-user fa-fw"></i>
                        &nbsp;
                        Profil
                    </a>
                </li>
                <li>
                    <a href="<?= route("vehicles") ?>" <?= $activeNavLink == route("vehicles") ? "class=\"active\"" : ""?>>
                        <i class="fa-solid fa-car fa-fw"></i>
                        &nbsp;
                        Járműveim
                    </a>
                </li>
                <li>
                    <a href="<?= route("journal") ?>" <?= $activeNavLink == route("journal") ? "class=\"active\"" : ""?>>
                        <i class="fa-solid fa-book fa-fw"></i>
                        &nbsp;
                        Utazási napló
                    </a>
                </li>
                <li>
                    <a href="<?= route("statistics") ?>" <?= $activeNavLink == route("statistics") ? "class=\"active\"" : ""?>>
                        <i class="fa-solid fa-chart-pie"></i>
                        &nbsp;
                        Statisztika
                    </a>
                </li>
            </ul>
        </nav>
        <div id="header-bottom-section">
            <a href="<?= route("logout") ?>" class="button" id="logoutButton">
                <i class="fa-solid fa-right-from-bracket fa-flip-horizontal"></i>
                &nbsp;
                Kijelentkezés
            </a>
        </div>
    </header>
    <?php endif ?>

    <main>
        <?php include $_pageContent ?>
    </main>
</body>
</html>

<?php return ?>