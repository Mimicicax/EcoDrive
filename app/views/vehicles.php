<?php use function EcoDrive\Routing\route ?>

<h1>Járműveim</h1>

<div class="container-header">
    <button class="button primary" onclick="openModal(document.querySelector('#add-vehicle-popup'))">
        Jármű hozzáadása
    </button>
</div>

<dialog id="add-vehicle-popup" 
        oncancel="closeModal(document.querySelector('#add-vehicle-popup'))">
    <div class="card">
        <p class="dialog-title">Jármű hozzáadása</p>
        <form action="<?= route("vehicles") ?>" method="POST">
            <div class="input-group <?= isset($errors["brandError"]) ? "error" : "" ?>">
                <label for="add-vehicle-brand-field">
                    Márka
                </label>
                <input type="text" 
                    placeholder="Márka" 
                    id="add-vehicle-brand-field" 
                    name="brand" 
                    required 
                    value="<?= $providedBrand ?? "" ?>">
            </div>

            <?php if (isset($errors["brandError"])): ?>
                <span class="error">
                    <?= $errors["brandError"] ?>
                </span>
            <?php endif ?>

            <div class="input-group <?= isset($errors["modelError"]) ? "error" : "" ?>">
                <label for="add-vehicle-model-field">
                    Modell
                </label>
                <input type="text" 
                    placeholder="Modell" 
                    id="add-vehicle-model-field" 
                    name="model"
                    value="<?= $providedModel ?? "" ?>"
                    required>
            </div>

            <?php if (isset($errors["modelError"])): ?>
                <span class="error">
                    <?= $errors["modelError"] ?>
                </span>
            <?php endif ?>

            <div class="input-group <?= isset($errors["licensePlateError"]) ? "error" : "" ?>">
                <label for="add-vehicle-plate-field">
                    Rendszám
                </label>
                <input type="text" 
                    placeholder="Rendszám"
                    id="add-vehicle-plate-field" 
                    name="licensePlate"
                    value="<?= $providedLicensePlate ?? "" ?>"
                    required>
            </div>

            <?php if (isset($errors["licensePlateError"])): ?>
                <span class="error">
                    <?= $errors["licensePlateError"] ?>
                </span>
            <?php endif ?>

            <div class="input-group <?= isset($errors["yearError"]) ? "error" : "" ?>">
                <label for="add-vehicle-year-field">Évjárat</label>
                <input type="number"
                    placeholder="Évjárat"
                    id="add-vehicle-year-field"
                    name="year"
                    min="1900"
                    max="<?= getdate()["year"] ?>"
                    value="<?= $providedYear ?? "" ?>"
                    required
                    >
            </div>

            <?php if (isset($errors["yearError"])): ?>
                <span class="error">
                    <?= $errors["yearError"] ?>
                </span>
            <?php endif ?>

            <span class="dual-input-group">
                <div class="input-group <?= isset($errors["consumptionError"]) ? "error" : "" ?>">
                    <label for="add-vehicle-consumption-field">
                        Fogyasztás
                    </label>
                    <input type="text" 
                        inputmode="decimal"
                        placeholder="Fogyasztás (L/100 km)" 
                        id="add-vehicle-consumption-field" 
                        name="consumption" 
                        value="<?= $providedConsumption ?? "" ?>"
                    >
                </div>

                <div class="input-group <?= isset($errors["emissionError"]) ? "error" : "" ?>">
                    <label for="add-vehicle-emission-field">
                        CO2 kibocsátás (g/km)
                    </label>
                    <input type="text" 
                        inputmode="decimal"
                        placeholder="CO2 kibocsátás (g/km)" 
                        id="add-vehicle-emission-field" 
                        name="emission" 
                        value="<?= $providedEmission ?? \EcoDrive\Models\Vehicle::DEFAULT_EMISSION_RATE ?>"
                    >
                </div>
            </span>

            <?php if (isset($errors["consumptionError"])): ?>
                <span class="error">
                    <?= $errors["consumptionError"] ?>
                </span>
            <?php endif ?>

            <span class="dual-input-group">
                <button type="button" class="button" onclick="closeModal(document.querySelector('#add-vehicle-popup'))">
                    Mégsem
                </button>
                <input type="submit" value="Hozzáadás" class="button primary">
            </span>
        </form>
    </div>
</dialog>

<?php if (empty($vehicleList)): ?>
<div class="empty card-container">
    <span>
        <h1>Még nem mentettél el egy járművet sem</h1>
        <p>Amint hozzáadsz járműveket, azok itt fognak megjelenni</p>
    </span>
</div>
<?php else: ?>

<div id="vehicle-container">
    <?php foreach ($vehicleList as $vehicle): ?>
        
        <?php $idPrefix = $vehicle->licensePlate ?>

        <div class="vehicle card" id="<?= "vehicle-$idPrefix" ?>">
            <p>
                <span class="car-license-plate"><?= $vehicle->licensePlate ?></span>
                (<span class="car-brand"><?= $vehicle->brand ?></span>
                <span class="car-model"><?= $vehicle->model ?></span>,
                <span class="car-year"><?= $vehicle->year ?></span>)
            </p>

            <form>
                <input type="hidden" 
                value="<?= $idPrefix ?>" 
                name="vehicleId" 
                value="<?= $vehicle->licensePlate ?>">

                <div class="input-group">
                    <label for="<?= "$idPrefix-brand" ?>">
                        Márka
                    </label>
                    <input type="text" 
                        placeholder="Márka" 
                        id="<?= "$idPrefix-brand" ?>" 
                        name="brand" 
                        value="<?= $vehicle->brand ?>"
                    >
                </div>

                <div class="input-group">
                    <label for="<?= "$idPrefix-model" ?>">
                        Modell
                    </label>
                    <input type="text" 
                        placeholder="Modell" 
                        id="<?= "$idPrefix-model" ?>" 
                        name="model" 
                        value="<?= $vehicle->model ?>"
                    >
                </div>

                <div class="input-group">
                    <label for="<?= "$idPrefix-licensePlate" ?>">
                        Rendszám
                    </label>
                    <input type="text" 
                        placeholder="Rendszám" 
                        id="<?= "$idPrefix-licensePlate" ?>" 
                        name="licensePlate" 
                        value="<?= $vehicle->licensePlate ?>"
                    >
                </div>

                <div class="input-group">
                    <label for="<?= "$idPrefix-year" ?>">Évjárat</label>
                    <input type="number"
                        placeholder="Évjárat"
                        id="<?= "$idPrefix-year" ?>"
                        name="year"
                        value="<?= $vehicle->year ?>"
                        min="1900"
                        max="<?= getdate()["year"] ?>"
                        >
                </div>

                <span class="dual-input-group">
                    <div class="input-group">
                        <label for="<?= "$idPrefix-consumption" ?>">
                            Fogyasztás (L/100 km)
                        </label>
                        <input type="text" 
                            inputmode="decimal"
                            placeholder="Fogyasztás (L/100 km)" 
                            id="<?= "$idPrefix-consumption" ?>" 
                            name="consumption" 
                            value="<?= $vehicle->consumption ?>"
                        >
                    </div>    

                    <div class="input-group">
                        <label for="<?= "$idPrefix-emission" ?>">
                            CO2 kibocsátás (g/km)
                        </label>
                        <input type="text" 
                            inputmode="decimal"
                            placeholder="CO2 kibocsátás (g/km)" 
                            id="<?= "$idPrefix-emission" ?>" 
                            name="emission" 
                            value="<?= $vehicle->co2EmissionRate ?>"
                        >
                    </div>   
                </span>
                <span class="dual-input-group">
                    <button type="button" class="button danger" onclick="if (confirm('Biztosan törli a járművet?')) deleteVehicle('<?= "vehicle-$idPrefix" ?>', '<?= $idPrefix ?>', '<?= route("vehicles") ?>')">
                        Jármű törlése
                    </button>
                    <button type="button" class="button primary" onclick="updateVehicle('<?= "vehicle-$idPrefix" ?>', '<?= $idPrefix ?>', '<?= route("vehicles") ?>')">Mentés</button>
                </span>
            </form>
        </div>
    <?php endforeach ?>
</div>

<?php if (!empty($errors)): ?>
    <script>openModal(document.getElementById("add-vehicle-popup"))</script>
<?php endif ?>

<?php endif ?>