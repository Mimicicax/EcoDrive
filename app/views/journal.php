<?php use function EcoDrive\Routing\route ?>

<h1>Utazási napló</h1>

<div class="vehicle-container-header">
    <button class="button primary" onclick="openModal(document.querySelector('#add-journal-entry-popup'))">
        Bejegyzés hozzáadása
    </button>
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
                        <option value="<?= $vehicle->id ?>">
                            <?php $name = $vehicle->licensePlate  . " (" . $vehicle->brand . " " . $vehicle->model . ")" ?>
                            <?= $name ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </span>

            <span class="input-group">
                <label for="travel_start">Indulás időpontja</label>
                <input type="datetime-local" name="travel_start" id="travel_start">
            </span>

            <span class="input-group">
                <label for="distance">Távolság (km)</label>
                <input type="text" name="distance" id="distance">
            </span>

            <p>Indulási hely</p>
            <span class="dual-input-group">
                <span class="input-group">
                    <label for="from_zip">Irányítószám</label>
                    <input type="number" name="from_zip" id="from_zip" min="0" max="9999">
                </span>

                <span class="input-group">
                    <label for="from_city">Város</label>
                    <input type="text" name="from_city" id="from_city">
                </span>
            </span>

            <span class="input-group">
                <label for="from_street">Utca</label>
                <input type="text" name="from_street" id="from_street">
            </span>

            <p>Érkezési hely</p>
            <span class="dual-input-group">
                <span class="input-group">
                    <label for="to_zip">Irányítószám</label>
                    <input type="number" name="to_zip" id="to_zip" min="0" max="9999">
                </span>
                
                <span class="input-group">
                    <label for="to_city">Város</label>
                    <input type="text" name="to_city" id="to_city">
                </span>
            </span>

            <span class="input-group">
                <label for="to_street">Utca</label>
                <input type="text" name="to_street" id="to_street">
            </span>

            <input type="submit" value="Hozzáadás" class="button primary add-top-margin">
        </form>
    </div>
</dialog>

<?php if (!empty($errors)): ?>
    <script>openModal(document.getElementById("add-journal-entry-popup"))</script>
<?php endif ?>
