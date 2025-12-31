# EcoDrive - Gyors Útmutató Teszteléshez és CI/CD-hez

## 🚀 Gyors Start (5 perc)

### Windows Környezetben

1. **Előfeltételek ellenőrzése**
   ```bash
   php -v        # PHP 8.0 vagy újabb kell
   composer -v   # Composer telepítve kell legyen
   mysql -V      # MySQL telepítve kell legyen
   ```

2. **Test környezet telepítése**
   ```bash
   # Projekt mappában
   cd c:\Users\dani\Documents\EcoDrive
   
   # Függőségek telepítése
   composer install
   
   # Test adatbázis létrehozása (futtatás előtt állítsd be a MySQL root jelszót!)
   cd tests
   setup-test-db.bat
   ```

3. **Tesztek futtatása**
   ```bash
   # Vissza a projekt mappába
   cd ..
   
   # Összes teszt
   composer test
   
   # Csak unit tesztek
   composer test:unit
   
   # Csak integration tesztek
   composer test:integration
   ```

### Linux/Mac Környezetben

```bash
# 1. Előfeltételek ugyanazok mint Windows-nál

# 2. Test környezet telepítése
cd /path/to/EcoDrive
composer install

# 3. Test adatbázis létrehozása
cd tests
chmod +x setup-test-db.sh
./setup-test-db.sh

# 4. Tesztek futtatása
cd ..
composer test
```

---

## 📊 Mi található a tests mappában?

```
tests/
├── bootstrap.php                    # PHPUnit inicializáció
├── README.md                        # Részletes dokumentáció
├── MANUAL_TEST_CASES.md            # Manuális teszt esetek
├── setup-test-db.sh                # Linux/Mac setup script
├── setup-test-db.bat               # Windows setup script
├── Unit/                           # Unit tesztek
│   ├── UserTest.php                # User model tesztek
│   ├── VehicleTest.php             # Vehicle model tesztek
│   └── SessionTest.php             # Session tesztek
└── Integration/                    # Integration tesztek
    ├── AuthenticationIntegrationTest.php
    └── VehicleIntegrationTest.php
```

---

## ✅ CI/CD Pipeline - Mit csinál?

A GitHub-ra push-oláskor automatikusan:

### 1. **Testing** (3-5 perc)
   - ✓ Unit tesztek futnak
   - ✓ Integration tesztek futnak
   - ✓ Test coverage riport készül

### 2. **Code Quality** (1-2 perc)
   - ✓ PHP szintaxis ellenőrzés
   - ✓ Kódstatisztikák

### 3. **Security** (2-3 perc)
   - ✓ Biztonsági rések ellenőrzése
   - ✓ Függőségek auditálása

### 4. **Build** (2-3 perc)
   - ✓ Production build készítése
   - ✓ Artifact csomagolása

### 5. **Deploy** (csak main branch)
   - ✓ Deployment szimuláció
   - ✓ Status értesítés

**Hol nézhetem meg?** GitHub repository → Actions tab

---

## 🧪 Teszt Típusok Magyarázat

### Unit Teszt - Mi ez?
Egy-egy kis darabot tesztel (pl. egy függvényt):
```php
// Példa: User létrehozás tesztelése
public function testUserCreation() {
    $user = User::create('test', 'test@test.com', 'pass123');
    $this->assertNotNull($user);  // Ellenőrzés: létrejött-e?
}
```

**Mikor használd?**
- Új modell metódus írásakor
- Bug fix után
- Refactoring után

### Integration Teszt - Mi ez?
Több komponens együttműködését teszteli:
```php
// Példa: Teljes bejelentkezési folyamat
public function testCompleteLoginFlow() {
    // 1. User létrehozása
    $user = User::create('login', 'login@test.com', 'pass');
    
    // 2. Bejelentkezés
    $found = User::find('login', User::FIND_BY_USERNAME);
    
    // 3. Session létrehozása
    Session::createSessionForUser($found);
    
    // 4. Ellenőrzés
    $this->assertTrue(Session::isAuthenticated());
}
```

**Mikor használd?**
- Új feature befejezésekor
- Authentication módosításkor
- Adatbázis séma változáskor

### Manuális Teszt - Mi ez?
Te magad kattintgatod végig a weboldalon és ellenőrzöd.

**Mikor használd?**
- UI változáskor
- Új funkció kipróbálásakor
- User tapasztalat ellenőrzéséhez

---

## 🐛 Hibaelhárítás - Leggyakoribb Problémák

### ❌ "MySQL connection failed"
**Probléma:** Test adatbázis nem elérhető

**Megoldás:**
```bash
# Windows:
# 1. MySQL service fut-e?
# Szolgáltatások → MySQL → Indítás

# 2. Futtasd újra a setup scriptet
cd tests
setup-test-db.bat
```

### ❌ "Class not found"
**Probléma:** Autoload nem frissült

**Megoldás:**
```bash
composer dump-autoload
```

### ❌ "vendor/bin/phpunit not found"
**Probléma:** Függőségek nincsenek telepítve

**Megoldás:**
```bash
composer install
```

### ❌ "Permission denied" (Linux/Mac)
**Probléma:** Setup script nem futtatható

**Megoldás:**
```bash
chmod +x tests/setup-test-db.sh
```

---

## 📝 Új Teszt Írása - Lépésről Lépésre

### 1. Hol hozzam létre?
- Unit teszt → `tests/Unit/` mappába
- Integration teszt → `tests/Integration/` mappába

### 2. Fájlnév konvenció
- `NevTest.php` (pl. `UserTest.php`)
- Névben szerepeljen a "Test" szó

### 3. Teszt osztály struktúra
```php
<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class UjFunkcioTest extends TestCase
{
    // Előkészítés (minden teszt előtt fut)
    protected function setUp(): void
    {
        // Test adatok készítése
    }
    
    // Egy teszt eset
    public function testValami()
    {
        // 1. Arrange - előkészítés
        $adat = "teszt";
        
        // 2. Act - végrehajtás
        $eredmeny = valamilyenFuggveny($adat);
        
        // 3. Assert - ellenőrzés
        $this->assertEquals("várt", $eredmeny);
    }
    
    // Takarítás (minden teszt után fut)
    protected function tearDown(): void
    {
        // Cleanup
    }
}
```

### 4. Hasznos Assert-ek
```php
// Egyenlőség
$this->assertEquals($vart, $kapott);

// Null ellenőrzés
$this->assertNull($valtozo);
$this->assertNotNull($valtozo);

// Boolean
$this->assertTrue($feltetel);
$this->assertFalse($feltetel);

// Szám összehasonlítás
$this->assertGreaterThan(5, $szam);
$this->assertLessThan(10, $szam);

// String tartalmaz
$this->assertStringContainsString("keresett", $szoveg);
```

---

## 🎯 Commit Előtti Checklist

Mindig push előtt:

- [ ] `composer test` zöld? ✅
- [ ] Új funkcióhoz írtam tesztet? ✅
- [ ] Értelmesen neveztem el a commit-ot? ✅
  - ✓ `feat: új jármű törlés funkció`
  - ✓ `fix: bejelentkezési hiba javítása`
  - ✗ `változtatások` ❌
  - ✗ `update` ❌

---

## 🔄 CI/CD Workflow - Gyakorlatban

### Fejlesztési Folyamat

1. **Feature branch létrehozása**
   ```bash
   git checkout -b feature/uj-funkció
   ```

2. **Kód írása + Teszt írása**
   ```bash
   # Kód módosítása...
   # Teszt írása...
   
   # Lokális teszt
   composer test
   ```

3. **Commit és Push**
   ```bash
   git add .
   git commit -m "feat: új funkció hozzáadása"
   git push origin feature/uj-funkció
   ```

4. **Pull Request létrehozása**
   - GitHub-on → New Pull Request
   - CI automatikusan fut ✅

5. **CI zöld? → Merge**
   - Ha minden teszt zöld → Merge into main
   - Main branch → Automatikus deployment

### Mi történik GitHub-on?

```
Your Push
    ↓
┌───────────────────────────┐
│  GitHub Actions Elindul   │
└───────────┬───────────────┘
            ↓
    ┌───────────────┐
    │  Test Job     │ → Fail ❌ → PR rejected
    └───────┬───────┘
            ↓ Pass ✅
    ┌───────────────┐
    │  Code Quality │ → Fail ❌ → PR rejected
    └───────┬───────┘
            ↓ Pass ✅
    ┌───────────────┐
    │  Security     │ → Fail ❌ → PR rejected
    └───────┬───────┘
            ↓ Pass ✅
    ┌───────────────┐
    │  Build        │ → Success ✅
    └───────┬───────┘
            ↓
    ┌───────────────┐
    │  All Green ✅ │ → Merge OK!
    └───────────────┘
```

---

## 💡 Tips Junior Fejlesztőknek

### DO ✅
- Írj tesztet MINDEN új funkcióhoz
- Futtasd a teszteket commit előtt
- Nézd meg a CI eredményeket GitHub-on
- Kérdezz, ha nem érted a teszt hibát
- Használd a manuális tesztet is (kattintgatás)

### DON'T ❌
- Ne commit-olj failing tesztekkel
- Ne skip-eld a teszteket (`@skip` annotation)
- Ne töröld a teszteket csak azért mert failelnek
- Ne push-olj teszt nélküli új funkciókat

---

## 📚 További Segítség

- **Részletes docs:** `tests/README.md`
- **Manuális tesztek:** `tests/MANUAL_TEST_CASES.md`
- **CI/CD config:** `.github/workflows/test.yaml`
- **PHPUnit docs:** https://phpunit.de/

---

**Kérdés van?** Nézd meg a `tests/README.md` fájlt vagy kérdezz! 🚀
