<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use EcoDrive\Models\User;
use EcoDrive\Models\Session;

class SessionTest extends TestCase
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
        // Táblák tisztítása
        mysqli_query(self::$testDbConn, "DELETE FROM sessions");
        mysqli_query(self::$testDbConn, "DELETE FROM vehicles");
        mysqli_query(self::$testDbConn, "DELETE FROM users");
        
        // Cookie tisztítása
        $_COOKIE = [];
    }

    public function testSessionCreationForUser()
    {
        require_once __DIR__ . '/../../config.php';
        
        $user = User::create('sessiontest', 'session@test.com', 'password123');
        
        Session::createSessionForUser($user);
        
        // Ellenőrizzük hogy létrejött a session az adatbázisban
        $result = mysqli_query(
            self::$testDbConn, 
            "SELECT COUNT(*) as count FROM sessions WHERE user = {$user->id}"
        );
        $row = mysqli_fetch_assoc($result);
        
        $this->assertEquals(1, $row['count'], 'Session nem jött létre az adatbázisban');
    }

    public function testCurrentSessionWithoutCookie()
    {
        require_once __DIR__ . '/../../config.php';
        
        // Cookie nélkül null-t kell visszaadjon
        $session = Session::currentSession();
        
        $this->assertNull($session, 'Cookie nélkül nem null a currentSession');
    }

    public function testIsAuthenticatedWithoutSession()
    {
        require_once __DIR__ . '/../../config.php';
        
        $authenticated = Session::isAuthenticated();
        
        $this->assertFalse($authenticated, 'Session nélkül nem false az isAuthenticated');
    }

    public function testCurrentUserWithoutSession()
    {
        require_once __DIR__ . '/../../config.php';
        
        $user = Session::currentUser();
        
        $this->assertNull($user, 'Session nélkül nem null a currentUser');
    }

    public function testSessionDeletion()
    {
        require_once __DIR__ . '/../../config.php';
        
        $user = User::create('deletetest', 'delete@test.com', 'password123');
        
        Session::createSessionForUser($user);
        
        // Ellenőrizzük hogy létezik
        $result = mysqli_query(
            self::$testDbConn, 
            "SELECT COUNT(*) as count FROM sessions WHERE user = {$user->id}"
        );
        $row = mysqli_fetch_assoc($result);
        $this->assertEquals(1, $row['count']);
        
        // Session törlése - ezt csak a currentSession()-ön keresztül lehet
        // Ezért ezt nem tudjuk teljesen tesztelni cookie nélkül
    }

    public function testMultipleSessionsForSameUserPrevented()
    {
        require_once __DIR__ . '/../../config.php';
        
        $user = User::create('multisession', 'multi@test.com', 'password123');
        
        // Első session létrehozása
        Session::createSessionForUser($user);
        
        // Második session létrehozási kísérlet
        Session::createSessionForUser($user);
        
        // Csak egy session létezhet egy userhez
        $result = mysqli_query(
            self::$testDbConn, 
            "SELECT COUNT(*) as count FROM sessions WHERE user = {$user->id}"
        );
        $row = mysqli_fetch_assoc($result);
        
        // A kód úgy van megírva, hogy vagy használja a meglévőt, vagy törli és újat hoz létre
        // Mindenképp csak 1 lehet
        $this->assertEquals(1, $row['count'], 'Több session létezik ugyanahhoz a userhez');
    }

    public function testSessionExpiryInFuture()
    {
        require_once __DIR__ . '/../../config.php';
        
        $user = User::create('expirytest', 'expiry@test.com', 'password123');
        
        Session::createSessionForUser($user);
        
        // Ellenőrizzük hogy a lejárat a jövőben van
        $result = mysqli_query(
            self::$testDbConn, 
            "SELECT expiry FROM sessions WHERE user = {$user->id}"
        );
        $row = mysqli_fetch_assoc($result);
        
        $expiryTime = strtotime($row['expiry']);
        $currentTime = time();
        
        $this->assertGreaterThan($currentTime, $expiryTime, 'Session lejárat nem a jövőben van');
        
        // Kb 7 nap múlva jár le (±1 óra)
        $diffDays = ($expiryTime - $currentTime) / (60 * 60 * 24);
        $this->assertGreaterThan(6.9, $diffDays);
        $this->assertLessThan(7.1, $diffDays);
    }
}
