<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use EcoDrive\Models\User;
use EcoDrive\Models\Vehicle;

class VehicleTest extends TestCase
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
        // Táblák tisztítása
        mysqli_query(self::$testDbConn, "DELETE FROM sessions");
        mysqli_query(self::$testDbConn, "DELETE FROM vehicles");
        mysqli_query(self::$testDbConn, "DELETE FROM users");
        
        // Test user létrehozása
        require_once __DIR__ . '/../../config.php';
        self::$testUser = User::create('vehicleowner', 'owner@test.com', 'password123');
    }

    public function testVehicleCreation()
    {
        $vehicle = Vehicle::create(
            self::$testUser,
            'Toyota',
            'Corolla',
            'ABC123',
            2020,
            5.5
        );
        
        $this->assertNotNull($vehicle, 'Vehicle létrehozása sikertelen');
        $this->assertGreaterThan(0, $vehicle->id);
        $this->assertEquals('Toyota', $vehicle->brand);
        $this->assertEquals('Corolla', $vehicle->model);
        $this->assertEquals('ABC123', $vehicle->licensePlate);
        $this->assertEquals(2020, $vehicle->year);
        $this->assertEquals(5.5, $vehicle->consumption);
    }

    public function testVehicleLicensePlateUppercase()
    {
        $vehicle = Vehicle::create(
            self::$testUser,
            'Honda',
            'Civic',
            'xyz789',
            2019,
            6.0
        );
        
        // A rendszám nagybetűssé kell konvertálódjon
        $this->assertEquals('XYZ789', $vehicle->licensePlate);
    }

    public function testVehicleFindByLicensePlate()
    {
        Vehicle::create(self::$testUser, 'Ford', 'Focus', 'DEF456', 2018, 7.0);
        
        $found = Vehicle::find('DEF456');
        
        $this->assertNotNull($found);
        $this->assertEquals('Ford', $found->brand);
        $this->assertEquals('Focus', $found->model);
    }

    public function testVehicleFindByLicensePlateCaseInsensitive()
    {
        Vehicle::create(self::$testUser, 'BMW', 'X5', 'GHI789', 2021, 8.5);
        
        // Kisbetűvel keresünk
        $found = Vehicle::find('ghi789');
        
        $this->assertNotNull($found);
        $this->assertEquals('BMW', $found->brand);
    }

    public function testVehicleExists()
    {
        Vehicle::create(self::$testUser, 'Audi', 'A4', 'JKL012', 2022, 6.5);
        
        $exists = Vehicle::exists('JKL012');
        $notExists = Vehicle::exists('NOTEXIST');
        
        $this->assertTrue($exists);
        $this->assertFalse($notExists);
    }

    public function testFindAllVehiclesForUser()
    {
        Vehicle::create(self::$testUser, 'Tesla', 'Model 3', 'EV001', 2023, 0.0);
        Vehicle::create(self::$testUser, 'Tesla', 'Model Y', 'EV002', 2023, 0.0);
        
        $vehicles = Vehicle::findAll(self::$testUser);
        
        $this->assertCount(2, $vehicles);
        $this->assertInstanceOf(Vehicle::class, $vehicles[0]);
        $this->assertInstanceOf(Vehicle::class, $vehicles[1]);
    }

    public function testFindAllVehiclesEmptyForNewUser()
    {
        $newUser = User::create('newowner', 'new@test.com', 'password');
        
        $vehicles = Vehicle::findAll($newUser);
        
        $this->assertIsArray($vehicles);
        $this->assertCount(0, $vehicles);
    }

    public function testVehicleUpdate()
    {
        $vehicle = Vehicle::create(self::$testUser, 'Mazda', 'CX-5', 'MNO345', 2020, 7.5);
        
        $vehicle->brand = 'Mazda Updated';
        $vehicle->model = 'CX-5 Turbo';
        $vehicle->consumption = 8.0;
        
        $result = $vehicle->update();
        
        $this->assertEquals(Vehicle::ERROR_NO_ERROR, $result);
        
        $updated = Vehicle::find('MNO345');
        $this->assertEquals('Mazda Updated', $updated->brand);
        $this->assertEquals('CX-5 Turbo', $updated->model);
        $this->assertEquals(8.0, $updated->consumption);
    }

    public function testVehicleUpdateLicensePlate()
    {
        $vehicle = Vehicle::create(self::$testUser, 'Kia', 'Sportage', 'PQR678', 2021, 6.8);
        
        $vehicle->licensePlate = 'newplate';
        $vehicle->update();
        
        $updated = Vehicle::find('NEWPLATE');
        $this->assertNotNull($updated);
        $this->assertEquals('NEWPLATE', $updated->licensePlate);
        
        // Régi rendszámmal nem található
        $old = Vehicle::find('PQR678');
        $this->assertNull($old);
    }

    public function testVehicleDelete()
    {
        $vehicle = Vehicle::create(self::$testUser, 'Nissan', 'Qashqai', 'STU901', 2019, 7.2);
        
        $result = $vehicle->delete();
        
        $this->assertEquals(Vehicle::ERROR_NO_ERROR, $result);
        
        // Már nem található
        $found = Vehicle::find('STU901');
        $this->assertNull($found);
    }

    public function testDuplicateLicensePlateCreation()
    {
        Vehicle::create(self::$testUser, 'Volvo', 'XC90', 'DUPLICATE', 2020, 8.0);
        
        // Ugyanazzal a rendszámmal próbálunk újat létrehozni
        $duplicate = Vehicle::create(self::$testUser, 'Volvo', 'S60', 'DUPLICATE', 2021, 7.0);
        
        $this->assertNull($duplicate, 'Duplicate license plate létrehozása nem sikertelen');
    }

    public function testVehicleModelEscaped()
    {
        $vehicle = Vehicle::create(
            self::$testUser,
            'Test<script>',
            'Model&quot;',
            'XSS001',
            2020,
            5.0
        );
        
        $escaped = $vehicle->modelEscaped();
        
        $this->assertStringNotContainsString('<script>', $escaped->brand);
        $this->assertStringNotContainsString('&quot;', $escaped->model);
    }
}
