<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use EcoDrive\Models\User;
use EcoDrive\Models\Vehicle;

class VehicleIntegrationTest extends TestCase
{
    private static $testDbConn;
    private static $testUser;

    public static function setUpBeforeClass(): void
    {
        createTestDatabase();
        
        self::$testDbConn = mysqli_connect(
            $_ENV['DB_HOST'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASSWORD'],
            $_ENV['DB_NAME'],
            $_ENV['DB_PORT']
        );
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$testDbConn) {
            mysqli_close(self::$testDbConn);
        }
        dropTestDatabase();
    }

    protected function setUp(): void
    {
        mysqli_query(self::$testDbConn, "DELETE FROM sessions");
        mysqli_query(self::$testDbConn, "DELETE FROM vehicles");
        mysqli_query(self::$testDbConn, "DELETE FROM users");
        
        require_once __DIR__ . '/../../config.php';
        self::$testUser = User::create('vehicleowner', 'owner@test.com', 'password123');
    }

    public function testCompleteVehicleLifecycle()
    {
        // 1. Jármű létrehozása
        $vehicle = Vehicle::create(
            self::$testUser,
            'Toyota',
            'Camry',
            'ABC123',
            2021,
            6.5
        );
        
        $this->assertNotNull($vehicle);
        $this->assertGreaterThan(0, $vehicle->id);
        
        // 2. Jármű megkeresése rendszám alapján
        $found = Vehicle::find('ABC123');
        $this->assertNotNull($found);
        $this->assertEquals('Toyota', $found->brand);
        $this->assertEquals('Camry', $found->model);
        
        // 3. Jármű adatainak módosítása
        $found->brand = 'Toyota Updated';
        $found->consumption = 5.8;
        $result = $found->update();
        $this->assertEquals(Vehicle::ERROR_NO_ERROR, $result);
        
        // 4. Ellenőrzés hogy a módosítás érvénybe lépett
        $updated = Vehicle::find('ABC123');
        $this->assertEquals('Toyota Updated', $updated->brand);
        $this->assertEquals(5.8, $updated->consumption);
        
        // 5. Jármű törlése
        $deleteResult = $updated->delete();
        $this->assertEquals(Vehicle::ERROR_NO_ERROR, $deleteResult);
        
        // 6. Ellenőrzés hogy törlődött
        $deleted = Vehicle::find('ABC123');
        $this->assertNull($deleted);
    }

    public function testMultipleVehiclesPerUser()
    {
        // Több jármű hozzáadása egy userhez
        Vehicle::create(self::$testUser, 'Honda', 'Accord', 'VEH001', 2020, 6.0);
        Vehicle::create(self::$testUser, 'Ford', 'Focus', 'VEH002', 2019, 6.5);
        Vehicle::create(self::$testUser, 'BMW', '320i', 'VEH003', 2022, 7.0);
        
        // Összes jármű lekérése
        $vehicles = Vehicle::findAll(self::$testUser);
        
        $this->assertCount(3, $vehicles);
        
        // Ellenőrizzük hogy mindegyik a megfelelő userhez tartozik
        foreach ($vehicles as $vehicle) {
            $this->assertEquals(self::$testUser->id, $vehicle->user->id);
        }
    }

    public function testVehicleIsolationBetweenUsers()
    {
        // Első user járműve
        Vehicle::create(self::$testUser, 'Tesla', 'Model 3', 'USER1CAR', 2023, 0.0);
        
        // Második user létrehozása és járműve
        $user2 = User::create('secondowner', 'second@test.com', 'password');
        Vehicle::create($user2, 'Audi', 'A4', 'USER2CAR', 2021, 6.8);
        
        // Ellenőrizzük hogy minden user csak a saját járműveit látja
        $user1Vehicles = Vehicle::findAll(self::$testUser);
        $user2Vehicles = Vehicle::findAll($user2);
        
        $this->assertCount(1, $user1Vehicles);
        $this->assertCount(1, $user2Vehicles);
        
        $this->assertEquals('USER1CAR', $user1Vehicles[0]->licensePlate);
        $this->assertEquals('USER2CAR', $user2Vehicles[0]->licensePlate);
    }

    public function testVehicleCascadeDeleteOnUserDelete()
    {
        // Járművek hozzáadása
        Vehicle::create(self::$testUser, 'Mazda', 'CX-5', 'CASCADE1', 2020, 7.0);
        Vehicle::create(self::$testUser, 'Nissan', 'Qashqai', 'CASCADE2', 2019, 7.2);
        
        $vehicles = Vehicle::findAll(self::$testUser);
        $this->assertCount(2, $vehicles);
        
        // User törlése (CASCADE miatt a járművek is törlődnek)
        mysqli_query(self::$testDbConn, "DELETE FROM users WHERE id = " . self::$testUser->id);
        
        // Ellenőrizzük hogy a járművek is törlődtek
        $result = mysqli_query(
            self::$testDbConn,
            "SELECT COUNT(*) as count FROM vehicles WHERE user = " . self::$testUser->id
        );
        $row = mysqli_fetch_assoc($result);
        $this->assertEquals(0, $row['count'], 'Járművek nem törlődtek a user törlésekor');
    }

    public function testLicensePlateUniqueness()
    {
        Vehicle::create(self::$testUser, 'Volvo', 'XC90', 'UNIQUE123', 2020, 8.0);
        
        // Ugyanazzal a rendszámmal próbálunk újat létrehozni
        $duplicate = Vehicle::create(self::$testUser, 'Volvo', 'S60', 'UNIQUE123', 2021, 7.0);
        
        $this->assertNull($duplicate);
        
        // Másik user sem hozhat létre ugyanazzal a rendszámmal
        $user2 = User::create('otherowner', 'other@test.com', 'password');
        $duplicate2 = Vehicle::create($user2, 'Skoda', 'Octavia', 'UNIQUE123', 2019, 6.5);
        
        $this->assertNull($duplicate2);
    }

    public function testVehicleUpdateLicensePlateChange()
    {
        $vehicle = Vehicle::create(self::$testUser, 'Kia', 'Sportage', 'OLD123', 2020, 6.8);
        
        // Rendszám megváltoztatása
        $vehicle->licensePlate = 'NEW123';
        $vehicle->update();
        
        // Új rendszámmal megtalálható
        $found = Vehicle::find('NEW123');
        $this->assertNotNull($found);
        $this->assertEquals('Kia', $found->brand);
        
        // Régi rendszámmal már nem
        $notFound = Vehicle::find('OLD123');
        $this->assertNull($notFound);
    }

    public function testVehicleSearchCaseInsensitivity()
    {
        Vehicle::create(self::$testUser, 'Peugeot', '308', 'ABC123XY', 2021, 6.0);
        
        // Különböző case-ekkel keresünk
        $found1 = Vehicle::find('ABC123XY');
        $found2 = Vehicle::find('abc123xy');
        $found3 = Vehicle::find('AbC123xY');
        
        $this->assertNotNull($found1);
        $this->assertNotNull($found2);
        $this->assertNotNull($found3);
        
        $this->assertEquals($found1->id, $found2->id);
        $this->assertEquals($found2->id, $found3->id);
    }

    public function testVehicleConsumptionCalculation()
    {
        // Ez egy egyszerű teszt a fogyasztási adatok kezelésére
        $vehicle = Vehicle::create(self::$testUser, 'Hyundai', 'i30', 'ECON001', 2020, 5.5);
        
        $this->assertEquals(5.5, $vehicle->consumption);
        $this->assertIsFloat($vehicle->consumption);
        
        // Frissítés
        $vehicle->consumption = 6.2;
        $vehicle->update();
        
        $updated = Vehicle::find('ECON001');
        $this->assertEquals(6.2, $updated->consumption);
    }

    public function testVehicleDataIntegrity()
    {
        $vehicle = Vehicle::create(
            self::$testUser,
            'Mercedes',
            'C-Class',
            'INTEG001',
            2022,
            7.5
        );
        
        // Ellenőrizzük az összes mező értékét
        $this->assertEquals(self::$testUser->id, $vehicle->user->id);
        $this->assertEquals('Mercedes', $vehicle->brand);
        $this->assertEquals('C-Class', $vehicle->model);
        $this->assertEquals('INTEG001', $vehicle->licensePlate);
        $this->assertEquals(2022, $vehicle->year);
        $this->assertIsInt($vehicle->year);
        $this->assertEquals(7.5, $vehicle->consumption);
        $this->assertIsFloat($vehicle->consumption);
    }
}
