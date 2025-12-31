<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use EcoDrive\Models\User;

class AuthenticationIntegrationTest extends TestCase
{
    private static $testDbConn;

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
        
        $_POST = [];
        $_COOKIE = [];
    }

    public function testCompleteRegistrationFlow()
    {
        require_once __DIR__ . '/../../config.php';
        
        // 1. User regisztráció
        $username = 'integrationuser';
        $email = 'integration@test.com';
        $password = 'SecurePass123';
        
        $user = User::create($username, $email, $password);
        
        $this->assertNotNull($user, 'Regisztráció sikertelen');
        $this->assertGreaterThan(0, $user->id);
        
        // 2. Ellenőrizzük hogy létezik az adatbázisban
        $foundUser = User::find($username, User::FIND_BY_USERNAME);
        $this->assertNotNull($foundUser);
        $this->assertEquals($email, $foundUser->email);
        
        // 3. Jelszó ellenőrzése
        $this->assertTrue($foundUser->passwordEquals($password));
        
        // 4. Helytelen jelszó nem működik
        $this->assertFalse($foundUser->passwordEquals('WrongPassword'));
    }

    public function testCompleteLoginFlow()
    {
        require_once __DIR__ . '/../../config.php';
        require_once __DIR__ . '/../../app/models/Session.php';
        
        // 1. User létrehozása
        $username = 'loginuser';
        $password = 'LoginPass123';
        $user = User::create($username, 'login@test.com', $password);
        
        // 2. Bejelentkezés username-mel
        $foundUser = User::find($username, User::FIND_BY_USERNAME);
        $this->assertNotNull($foundUser);
        $this->assertTrue($foundUser->passwordEquals($password));
        
        // 3. Session létrehozása
        \EcoDrive\Models\Session::createSessionForUser($foundUser);
        
        // 4. Ellenőrizzük hogy létrejött a session
        $result = mysqli_query(
            self::$testDbConn,
            "SELECT * FROM sessions WHERE user = {$foundUser->id}"
        );
        $sessionData = mysqli_fetch_assoc($result);
        
        $this->assertNotNull($sessionData);
        $this->assertNotEmpty($sessionData['session_id']);
    }

    public function testLoginWithEmail()
    {
        require_once __DIR__ . '/../../config.php';
        
        // User létrehozása
        $email = 'emaillogin@test.com';
        $password = 'EmailPass123';
        User::create('emailuser', $email, $password);
        
        // Bejelentkezés email címmel
        $foundUser = User::find($email, User::FIND_BY_EMAIL);
        
        $this->assertNotNull($foundUser);
        $this->assertTrue($foundUser->passwordEquals($password));
    }

    public function testInvalidLoginAttempt()
    {
        require_once __DIR__ . '/../../config.php';
        
        User::create('validuser', 'valid@test.com', 'correctpassword');
        
        // Helytelen felhasználónév
        $invalidUser = User::find('invaliduser', User::FIND_BY_USERNAME);
        $this->assertNull($invalidUser);
        
        // Helyes user, helytelen jelszó
        $validUser = User::find('validuser', User::FIND_BY_USERNAME);
        $this->assertFalse($validUser->passwordEquals('wrongpassword'));
    }

    public function testUserDataValidation()
    {
        require_once __DIR__ . '/../../config.php';
        require_once __DIR__ . '/../../app/helpers/UserDataValidator.php';
        
        // Érvényes adatok
        $this->assertNull(\EcoDrive\Helpers\validateUsername('validuser'));
        $this->assertNull(\EcoDrive\Helpers\validateEmail('valid@example.com'));
        $this->assertNull(\EcoDrive\Helpers\validatePassword('Pass123', 'Pass123'));
        
        // Érvénytelen felhasználónév (túl rövid)
        $this->assertNotNull(\EcoDrive\Helpers\validateUsername('ab'));
        
        // Érvénytelen email
        $this->assertNotNull(\EcoDrive\Helpers\validateEmail('notanemail'));
        
        // Jelszavak nem egyeznek
        $this->assertNotNull(\EcoDrive\Helpers\validatePassword('Pass123', 'Pass456'));
    }

    public function testDuplicateRegistrationPrevention()
    {
        require_once __DIR__ . '/../../config.php';
        
        // Első regisztráció
        $user1 = User::create('duplicate', 'first@test.com', 'password');
        $this->assertNotNull($user1);
        
        // Második kísérlet ugyanazzal a username-mel
        $user2 = User::create('duplicate', 'second@test.com', 'password');
        $this->assertNull($user2, 'Duplicate username regisztráció nem blokkolva');
        
        // Második kísérlet ugyanazzal az email-lel
        $user3 = User::create('different', 'first@test.com', 'password');
        $this->assertNull($user3, 'Duplicate email regisztráció nem blokkolva');
    }

    public function testProfileUpdateFlow()
    {
        require_once __DIR__ . '/../../config.php';
        
        // User létrehozása
        $user = User::create('profileuser', 'profile@test.com', 'OldPass123');
        
        // Username frissítése
        $user->username = 'updatedprofile';
        $result = $user->update();
        $this->assertTrue($result);
        
        // Ellenőrzés
        $updated = User::find('updatedprofile', User::FIND_BY_USERNAME);
        $this->assertNotNull($updated);
        $this->assertEquals('updatedprofile', $updated->username);
        
        // Email frissítése
        $user->email = 'newemail@test.com';
        $user->update();
        
        $updated = User::find('updatedprofile', User::FIND_BY_USERNAME);
        $this->assertEquals('newemail@test.com', $updated->email);
        
        // Jelszó frissítése
        $user->password = 'NewPass456';
        $user->update();
        
        $updated = User::find('updatedprofile', User::FIND_BY_USERNAME);
        $this->assertTrue($updated->passwordEquals('NewPass456'));
    }

    public function testLogoutFlow()
    {
        require_once __DIR__ . '/../../config.php';
        require_once __DIR__ . '/../../app/models/Session.php';
        
        // User és session létrehozása
        $user = User::create('logoutuser', 'logout@test.com', 'password');
        \EcoDrive\Models\Session::createSessionForUser($user);
        
        // Ellenőrzés hogy létezik
        $result = mysqli_query(
            self::$testDbConn,
            "SELECT COUNT(*) as count FROM sessions WHERE user = {$user->id}"
        );
        $row = mysqli_fetch_assoc($result);
        $this->assertEquals(1, $row['count']);
        
        // Session törlése szimuláció (közvetlen adatbázis művelet)
        mysqli_query(self::$testDbConn, "DELETE FROM sessions WHERE user = {$user->id}");
        
        // Ellenőrzés hogy törlődött
        $result = mysqli_query(
            self::$testDbConn,
            "SELECT COUNT(*) as count FROM sessions WHERE user = {$user->id}"
        );
        $row = mysqli_fetch_assoc($result);
        $this->assertEquals(0, $row['count']);
    }
}
