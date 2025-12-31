<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use EcoDrive\Models\User;

class UserTest extends TestCase
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
        // Táblák tisztítása minden teszt előtt
        mysqli_query(self::$testDbConn, "DELETE FROM sessions");
        mysqli_query(self::$testDbConn, "DELETE FROM vehicles");
        mysqli_query(self::$testDbConn, "DELETE FROM users");
    }

    public function testUserCreation()
    {
        require_once __DIR__ . '/../../config.php';
        
        $user = User::create('testuser', 'test@example.com', 'password123');
        
        $this->assertNotNull($user, 'User létrehozása sikertelen');
        $this->assertGreaterThan(0, $user->id, 'User ID értéke nem megfelelő');
    }

    public function testUserFindByUsername()
    {
        require_once __DIR__ . '/../../config.php';
        
        User::create('findtest', 'find@test.com', 'password123');
        
        $foundUser = User::find('findtest', User::FIND_BY_USERNAME);
        
        $this->assertNotNull($foundUser, 'User nem található felhasználónév alapján');
        $this->assertEquals('findtest', $foundUser->username);
        $this->assertEquals('find@test.com', $foundUser->email);
    }

    public function testUserFindByEmail()
    {
        require_once __DIR__ . '/../../config.php';
        
        User::create('emailtest', 'email@test.com', 'password123');
        
        $foundUser = User::find('email@test.com', User::FIND_BY_EMAIL);
        
        $this->assertNotNull($foundUser, 'User nem található email alapján');
        $this->assertEquals('emailtest', $foundUser->username);
        $this->assertEquals('email@test.com', $foundUser->email);
    }

    public function testUserExistsByUsername()
    {
        require_once __DIR__ . '/../../config.php';
        
        User::create('existstest', 'exists@test.com', 'password123');
        
        $exists = User::exists('existstest', User::FIND_BY_USERNAME);
        $notExists = User::exists('nonexistent', User::FIND_BY_USERNAME);
        
        $this->assertTrue($exists, 'Létező user-t nem talál');
        $this->assertFalse($notExists, 'Nem létező user-t talál');
    }

    public function testUserExistsByEmail()
    {
        require_once __DIR__ . '/../../config.php';
        
        User::create('emailexists', 'emailexists@test.com', 'password123');
        
        $exists = User::exists('emailexists@test.com', User::FIND_BY_EMAIL);
        $notExists = User::exists('nonexistent@test.com', User::FIND_BY_EMAIL);
        
        $this->assertTrue($exists, 'Létező email-t nem talál');
        $this->assertFalse($notExists, 'Nem létező email-t talál');
    }

    public function testPasswordHashing()
    {
        require_once __DIR__ . '/../../config.php';
        
        $plainPassword = 'mySecurePassword123';
        $user = User::create('pwtest', 'pw@test.com', $plainPassword);
        
        $foundUser = User::find('pwtest', User::FIND_BY_USERNAME);
        
        // A jelszó nem lehet plaintext
        $this->assertNotEquals($plainPassword, $foundUser->password);
        
        // De a passwordEquals működnie kell
        $this->assertTrue($foundUser->passwordEquals($plainPassword));
        $this->assertFalse($foundUser->passwordEquals('wrongpassword'));
    }

    public function testUserUpdate()
    {
        require_once __DIR__ . '/../../config.php';
        
        $user = User::create('updatetest', 'update@test.com', 'password123');
        
        // Username frissítése
        $user->username = 'updateduser';
        $result = $user->update();
        
        $this->assertTrue($result, 'User update sikertelen');
        
        $updated = User::find('updateduser', User::FIND_BY_USERNAME);
        $this->assertNotNull($updated);
        $this->assertEquals('updateduser', $updated->username);
    }

    public function testUserUpdateEmail()
    {
        require_once __DIR__ . '/../../config.php';
        
        $user = User::create('emailupdate', 'old@test.com', 'password123');
        
        $user->email = 'new@test.com';
        $user->update();
        
        $updated = User::find('emailupdate', User::FIND_BY_USERNAME);
        $this->assertEquals('new@test.com', $updated->email);
    }

    public function testUserUpdatePassword()
    {
        require_once __DIR__ . '/../../config.php';
        
        $user = User::create('pwupdate', 'pwupdate@test.com', 'oldpassword');
        
        $user->password = 'newpassword';
        $user->update();
        
        $updated = User::find('pwupdate', User::FIND_BY_USERNAME);
        $this->assertTrue($updated->passwordEquals('newpassword'));
        $this->assertFalse($updated->passwordEquals('oldpassword'));
    }

    public function testDuplicateUsernameCreation()
    {
        require_once __DIR__ . '/../../config.php';
        
        User::create('duplicate', 'first@test.com', 'password123');
        
        // Második létrehozás ugyanazzal a felhasználónévvel
        $duplicate = User::create('duplicate', 'second@test.com', 'password123');
        
        // Ez sikertelen kell legyen
        $this->assertNull($duplicate, 'Duplicate username létrehozása nem sikertelen');
    }

    public function testDuplicateEmailCreation()
    {
        require_once __DIR__ . '/../../config.php';
        
        User::create('user1', 'same@test.com', 'password123');
        
        // Második létrehozás ugyanazzal az email címmel
        $duplicate = User::create('user2', 'same@test.com', 'password123');
        
        // Ez sikertelen kell legyen
        $this->assertNull($duplicate, 'Duplicate email létrehozása nem sikertelen');
    }
}
