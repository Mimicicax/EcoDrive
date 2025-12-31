<?php use function EcoDrive\Environment\appConfig ?>
<?php require_once appConfig()->APP_ROOT . "/helpers/DateTimeToLocal.php" ?>
<?php use function EcoDrive\Routing\route ?>
<?php use function EcoDrive\Helpers\dateTimeToLocalString ?>

<h1>Utazási napló</h1>

<div class="container-header">
    <button class="button primary" onclick="openModal(document.querySelector('#add-journal-entry-popup'))">
        Bejegyzés hozzáadása
    </button>

    <form action="<?= route("journal") ?>" method="GET" class="horizontal">
        <span class="input-group">
            <label for="filterVehicle">Jármű</label>

            <select id="filterVehicle" name="filterVehicle">
                <?php foreach ($userVehicles as $vehicle): ?>
                    <option value="<?= $vehicle->licensePlate ?>" <?= isset($filterVehicle) && $filterVehicle == $vehicle->licensePlate ? "selected=true" : "" ?> >
                        <?php $name = $vehicle->licensePlate  . " (" . $vehicle->brand . " " . $vehicle->model . ")" ?>
                        <?= $name ?>
                    </option>
                <?php endforeach ?>
            </select>
        </span>

        <span class="input-group">
            <label for="filterYear">Év</label>

            <select name="filterYear" id="filterYear">
                <?php foreach ($filterYearList as $year): ?>
                    <option value="<?= $year ?>" <?= isset($filterYear) && $filterYear == $year ? "selected=true" : "" ?> >
                        <?= $year ?>
                    </option>
                <?php endforeach ?>
            </select>
        </span>

        <button class="button secondary">Szűrés</button>
    </form>
</div>

<dialog id="add-journal-entry-popup" 
        oncancel="closeModal(document.querySelector('#add-journal-entry-popup'))">
    <div class="card">
        <p class="dialog-title">Bejegyzés hozzáadása</p>

        <form action="<?= route("journal") ?>" method="POST">

            <span class="input-group">
                <label for="vehicle">Jármű</label>

                <select name="vehicle" id="vehicle">
                    <?php foreach ($userVehicles as $vehicle): ?>
                        <option value="<?= $vehicle->licensePlate ?>" <?= isset($providedVehicle) && $providedVehicle == $vehicle->licensePlate ? "selected=true" : "" ?> >
                            <?= $vehicle->name() ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </span>

            <?php if (isset($errors["plateError"])): ?>
                <span class="error">
                    <?= $errors["plateError"] ?>
                </span>
            <?php endif ?>

            <span <?= "class=\"input-group" . (isset($errors["travelStartError"]) ? " error" : "") . "\"" ?> >
                <label for="travel_start">Indulás időpontja</label>
                <input type="datetime-local" name="travel_start" id="travel_start" <?= isset($providedTravelStart) ? "value=\"$providedTravelStart\"" : "" ?>>
            </span>

            <?php if (isset($errors["travelStartError"])): ?>
                <span class="error">
                    <?= $errors["travelStartError"] ?>
                </span>
            <?php endif ?>

            <span <?= "class=\"input-group" . (isset($errors["distanceError"]) ? " error" : "") . "\"" ?> >
                <label for="distance">Távolság (km)</label>
                <input type="text" inputmode="decimal" name="distance" id="distance" <?= isset($providedDistance) ? "value=\"$providedDistance\"" : "" ?>>
            </span>

            <?php if (isset($errors["distanceError"])): ?>
                <span class="error">
                    <?= $errors["distanceError"] ?>
                </span>
            <?php endif ?>

            <p>Indulási hely</p>
            <span class="dual-input-group">
                <span <?= "class=\"input-group" . (isset($errors["fromZipError"]) ? " error" : "") . "\"" ?> >
                    <label for="from_zip">Irányítószám</label>
                    <input type="number" name="from_zip" id="from_zip" min="0" max="9999" <?= isset($providedFromZip) ? "value=\"$providedFromZip\"" : "" ?>>
                </span>

                <span class="input-group">
                    <label for="from_city">Város</label>
                    <input type="text" name="from_city" id="from_city" <?= isset($providedFromCity) ? "value=\"$providedFromCity\"" : "" ?>>
                </span>
            </span>

            <?php if (isset($errors["fromZipError"])): ?>
                <span class="error">
                    <?= $errors["fromZipError"] ?>
                </span>
            <?php endif ?>

            <span class="input-group">
                <label for="from_street">Utca</label>
                <input type="text" name="from_street" id="from_street" <?= isset($providedFromStreet) ? "value=\"$providedFromStreet\"" : "" ?>>
            </span>

            <p>Érkezési hely</p>
            <span class="dual-input-group">
                <span <?= "class=\"input-group" . (isset($errors["toZipError"]) ? " error" : "") . "\"" ?> >
                    <label for="to_zip">Irányítószám</label>
                    <input type="number" name="to_zip" id="to_zip" min="0" max="9999" <?= isset($providedToZip) ? "value=\"$providedToZip\"" : "" ?>>
                </span>
                
                <span class="input-group">
                    <label for="to_city">Város</label>
                    <input type="text" name="to_city" id="to_city" <?= isset($providedToCity) ? "value=\"$providedToCity\"" : "" ?>>
                </span>
            </span>

            <?php if (isset($errors["toZipError"])): ?>
                <span class="error">
                    <?= $errors["toZipError"] ?>
                </span>
            <?php endif ?>

            <span class="input-group">
                <label for="to_street">Utca</label>
                <input type="text" name="to_street" id="to_street" <?= isset($providedToStreet) ? "value=\"$providedToStreet\"" : "" ?>>
            </span>

            <input type="submit" value="Hozzáadás" class="button primary add-top-margin">
        </form>
    </div>
</dialog>

<?php if (empty($routeList)): ?>

<div class="empty card-container">
    <span>
        <h1>A napló üres</h1>
        <p>Adj hozzá útvonalakat és azok itt fognak megjelenni</p>
        <p>Az is előfordulhat, hogy nincs a szűrési feltételeknek megfelelő elmentett útvonalad</p>
    </span>
</div>

<?php else: ?>

<h1><?= $filterYear ?></h1>

<div class="card-container journal">
    <?php $month = \EcoDrive\Helpers\monthName($routeList[0]->travelStart) ?>

    <h2><?= ucfirst($month) ?> </h2>

    <?php foreach ($routeList as $route): ?>

        <?php 
            $fromAddress = $route->fromStreet;
            $toAddress = $route->toStreet;

            if ($route->fromCity != "")
                $fromAddress .= ($fromAddress == "" ? $route->fromCity : ", " . $route->fromCity);

            if ($route->fromZip != 0)
                $fromAddress .= ($fromAddress == "" ? $route->fromZip : ", " . $route->fromZip);

            if ($route->toCity != "")
                $toAddress .= ($toAddress == "" ? $route->toCity : ", " . $route->toCity);

            if ($route->toZip != 0)
                $toAddress .= ($toAddress == "" ? $route->toZip : ", " . $route->toZip);

            $m = \EcoDrive\Helpers\monthName($route->travelStart);
        ?>

        <?php if ($month !== $m): ?>
            <?php $month = $m ?>
            <h2><?= ucfirst($month) ?> </h2>
        <?php endif ?>

        <div class="card journal-entry">
            <div>
                <p><b>Innen:</b> <?= $fromAddress ?></p>
                <p><b>Ide:</b> <?= $toAddress ?></p>
                <p><b>Indulás:</b> <?= dateTimeToLocalString($route->travelStart) ?></p>
            </div>
            <div>
                <p><b>Jármű:</b> <?= $route->vehicle->name() ?> </p>
                <p><b>Távolság:</b> <?= $route->distance ?> km </p>
                <p><b>Becsült CO2-kibocsátás:</b> <?= $route->emission ?> g</p>
            </div>
            <form action="<?= route("journal/delete") ?>" method="POST" onsubmit="return confirm('Biztosan törli a bejegyzést?')">
                <input type="hidden" name="route" value="<?= $route->id ?>">
                <button class="button secondary danger">Bejegyzés törlése</button>
            </form>
        </div>
    <?php endforeach ?>
    
</div>
<?php endif ?>

<?php if (!empty($errors)): ?>
    <script>openModal(document.getElementById("add-journal-entry-popup"))</script>
<?php endif ?>
