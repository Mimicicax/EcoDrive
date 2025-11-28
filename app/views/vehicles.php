<?php use function EcoDrive\Routing\route ?>

<div class="vehicle-container-header">
    <p>Találatok száma: <?= empty($vehicleList) ? 0 : count($vehicleList) ?> db</p>
    <button class="button primary" onclick="openModal(document.querySelector('#add-vehicle-popup'))">
        Jármű hozzáadása
    </button>
</div>

<?php if (empty($vehicleList)): ?>
<div class="empty vehicle card-container">
    <span>
        <h1>Még nem mentettél el egy járművet sem</h1>
        <p>Amint hozzáadsz járműveket, azok itt fognak megjelenni.</p>
    </span>
</div>
<?php else: ?>

<dialog id="add-vehicle-popup" 
        class="card" 
        oncancel="closeModal(document.querySelector('#add-vehicle-popup'))">
    <p>Jármű hozzáadása</p>
    <form action="<?= route("vehicles") ?>" method="POST">
        <div class="input-group <?= isset($errors["createError"]) && isset($errors["brandError"]) ? "error" : "" ?>">
            <label for="add-vehicle-brand-field">
                Márka
            </label>
            <input type="text" 
                placeholder="Márka" 
                id="add-vehicle-brand-field" 
                name="brand" 
                required 
                value="<?= isset($errors["createError"]) ? $providedBrand : "" ?>">
        </div>

        <?php if (isset($errors["createError"]) && isset($errors["brandError"])): ?>
            <span class="error">
                <?= $errors["brandError"] ?>
            </span>
        <?php endif ?>

        <div class="input-group <?= isset($errors["createError"]) && isset($errors["modelError"]) ? "error" : "" ?>">
            <label for="add-vehicle-model-field">
                Modell
            </label>
            <input type="text" 
                placeholder="Modell" 
                id="add-vehicle-model-field" 
                name="model"
                value="<?= isset($errors["createError"]) ? $providedModel : "" ?>">
                required>
        </div>

        <?php if (isset($errors["createError"]) && isset($errors["modelError"])): ?>
            <span class="error">
                <?= $errors["modelError"] ?>
            </span>
        <?php endif ?>

        <div class="input-group <?= isset($errors["createError"]) && isset($errors["licensePlateError"]) ? "error" : "" ?>">
            <label for="add-vehicle-plate-field">
                Rendszám
            </label>
            <input type="text" 
                placeholder="Rendszám"
                id="add-vehicle-plate-field" 
                name="licensePlate"
                value="<?= isset($errors["createError"]) ? $providedLicensePlate : "" ?>"
                required>
        </div>

        <?php if (isset($errors["createError"]) && isset($errors["licensePlateError"])): ?>
            <span class="error">
                <?= $errors["licensePlateError"] ?>
            </span>
        <?php endif ?>

        <span class="vehicle-numeric-input-group">
            <div class="input-group <?= isset($errors["createError"]) && isset($errors["yearError"]) ? "error" : "" ?>">
                <label for="add-vehicle-year-field">Évjárat</label>
                <input type="number"
                    placeholder="Évjárat"
                    id="add-vehicle-plate-field"
                    name="year"
                    min="1900"
                    max="<?= getdate()["year"] ?>"
                    value="<?= isset($errors["createError"]) ? $providedYear : "" ?>"
                    required
                    >
            </div>

            <div class="input-group <?= isset($errors["createError"]) && isset($errors["consumptionError"]) ? "error" : "" ?>">
                <label for="add-vehicle-consumption-field">
                    Fogyasztás
                </label>
                <input type="text" 
                    inputmode="decimal"
                    placeholder="Fogyasztás (L/100 km)" 
                    id="add-vehicle-consumption-field" 
                    name="consumption" 
                    value="<?= isset($errors["createError"]) ? $providedConsumption : "" ?>"
                    required
                >
            </div>
        </span>

        <?php if (isset($errors["createError"]) && isset($errors["yearError"])): ?>
            <span class="error">
                <?= $errors["yearError"] ?>
            </span>
        <?php endif ?>
            
        <?php if (isset($errors["createError"]) && isset($errors["consumptionError"])): ?>
            <span class="error">
                <?= $errors["consumptionError"] ?>
            </span>
        <?php endif ?>

        <span class="vehicle-button-grid">
            <button type="button" class="button" onclick="closeModal(document.querySelector('#add-vehicle-popup'))">
                Mégsem
            </button>
            <input type="submit" value="Hozzáadás" class="button primary">
        </span>
    </form>
</dialog>

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
                            Fogyasztás
                        </label>
                        <input type="text" 
                            inputmode="decimal"
                            placeholder="Fogyasztás (L/100 km)" 
                            id="<?= "$idPrefix-consumption" ?>" 
                            name="consumption" 
                            value="<?= $vehicle["consumption"] ?>"
                        >
                    </div>    
                </span>
                <span class="vehicle-button-grid">
                    <button type="button" class="button danger">
                        Jármű törlése
                    </button>
                    <input type="submit" value="Mentés" class="button primary">
                </span>
            </form>
        </div>
    <?php endforeach ?>
</div>
<?php endif ?>