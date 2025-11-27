<?php use function EcoDrive\Routing\route ?>

<div class="vehicle-container-header">
    Itt majd fasza lesz
    <p>Találatok száma: <?= empty($vehicleList) ? 0 : count($vehicleList) ?> db</p>
</div>

<?php if (empty($vehicleList)): ?>
<div class="empty vehicle card-container">
    <span>
        <h1>Még nem mentettél el egy járművet sem</h1>
        <p>Amint hozzáadsz járműveket, azok itt fognak megjelenni.</p>
    </span>
</div>
<?php else: ?>
<h1>Járműveim</h1>
<div class="vehicle card-container">
    <?php foreach ($vehicleList as $vehicle): ?>
        <?php $idPrefix = $vehicle["license_plate"] ?>

        <div class="vehicle card">
            <p>
                <span class="license-plate">
                    <?= $vehicle["license_plate"] ?>
                </span>
                (<?= "$vehicle[brand]" ?> <?= "$vehicle[model]" ?>, <?= "$vehicle[year]" ?>)
            </p>

            <form>
                <input type="hidden" 
                value="<?= $idPrefix ?>" 
                name="vehicleId" 
                value="<?= $vehicle["license_plate"] ?>">

                <div class="input-group">
                    <label for="<?= "$idPrefix-brand" ?>">
                        Márka
                    </label>
                    <input type="text" 
                        placeholder="Márka" 
                        id="<?= "$idPrefix-brand" ?>" 
                        name="brand" 
                        value="<?= $vehicle["brand"] ?>"
                    >
                </div>

                <div class="input-group">
                    <label for="<?= "$idPrefix-model" ?>">
                        Modell
                    </label>
                    <input type="text" 
                        placeholder="Márka" 
                        id="<?= "$idPrefix-model" ?>" 
                        name="model" 
                        value="<?= $vehicle["model"] ?>"
                    >
                </div>

                <div class="input-group">
                    <label for="<?= "$idPrefix-plate" ?>">
                        Rendszám
                    </label>
                    <input type="text" 
                        placeholder="Rendszám" 
                        id="<?= "$idPrefix-plate" ?>" 
                        name="licensePlate" 
                        value="<?= $vehicle["license_plate"] ?>"
                    >
                </div>

                <span class="vehicle-numeric-input-group">
                    <div class="input-group">
                        <label for="<?= "$idPrefix-year" ?>">Évjárat</label>
                        <input type="number"
                            placeholder="Évjárat"
                            id="<?= "$idPrefix-year" ?>"
                            name="year"
                            value="<?= $vehicle["year"] ?>"
                            min="1900"
                            max="<?= getdate()["year"] ?>"
                            >
                    </div>

                    <div class="input-group">
                        <label for="<?= "$idPrefix-consumption" ?>">
                            Fogyasztás (L/100 km)
                        </label>
                        <input type="text" 
                            inputmode="decimal"
                            placeholder="Fogyasztás" 
                            id="<?= "$idPrefix-consumption" ?>" 
                            name="consumption" 
                            value="<?= $vehicle["consumption"] ?>"
                        >
                    </div>    
                </span>
                <span class="vehicle-button-grid">
                    <button class="button danger">
                        Jármű törlése
                    </button>
                    <input type="submit" value="Változtatások mentése" class="button primary">
                </span>
            </form>
        </div>
    <?php endforeach ?>
</div>
<?php endif ?>