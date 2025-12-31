# EcoDrive - Tesztelési és CI/CD Dokumentáció

## Áttekintés

Ez a projekt teljes CI/CD (Continuous Integration/Continuous Deployment) pipeline-nal rendelkezik, amely automatizált tesztelést, kódminőség-ellenőrzést és deployment folyamatot biztosít.

## Tartalomjegyzék

1. [Tesztelési Stratégia](#tesztelési-stratégia)
2. [Tesztek Futtatása Lokálisan](#tesztek-futtatása-lokálisan)
3. [CI/CD Pipeline](#cicd-pipeline)
4. [Deployment Folyamat](#deployment-folyamat)
5. [Hibaelhárítás](#hibaelhárítás)

---

## Tesztelési Stratégia

### Teszt Típusok

#### 1. Unit Tesztek (`tests/Unit/`)
- **Cél**: Egyedi komponensek (modellek, segédfüggvények) tesztelése izolált környezetben
- **Lefedettség**: User, Vehicle, Session modellek
- **Fájlok**:
  - `UserTest.php` - User model CRUD műveletek
  - `VehicleTest.php` - Vehicle model műveletek
  - `SessionTest.php` - Session kezelés

#### 2. Integration Tesztek (`tests/Integration/`)
- **Cél**: Több komponens együttműködésének tesztelése
- **Lefedettség**: Authentication flow, Vehicle management, Profile updates
- **Fájlok**:
  - `AuthenticationIntegrationTest.php` - Teljes auth folyamat
  - `VehicleIntegrationTest.php` - Járműkezelési folyamatok

#### 3. Manuális Tesztek (`tests/MANUAL_TEST_CASES.md`)
- **Cél**: UI/UX tesztelés, exploratory testing
- **Lefedettség**: Összes felhasználói interakció
- **Végrehajtás**: Tesztelő által manuálisan

---

## Tesztek Futtatása Lokálisan

### Előfeltételek

1. **PHP 8.0 vagy újabb telepítése**
   ```bash
   php -v
   ```

2. **Composer telepítése**
   ```bash
   composer --version
   ```

3. **MySQL adatbázis**
   - MySQL 8.0 vagy MariaDB 10.5+
   - Test user és adatbázis létrehozása:
   ```sql
   CREATE USER 'ecodrive_test'@'localhost' IDENTIFIED BY 'ecodrive_test';
   CREATE DATABASE ecodrive_test;
   GRANT ALL PRIVILEGES ON ecodrive_test.* TO 'ecodrive_test'@'localhost';
   FLUSH PRIVILEGES;
   ```

### Telepítési Lépések

1. **Függőségek telepítése**
   ```bash
   cd c:\Users\dani\Documents\EcoDrive
   composer install
   ```

2. **Test környezet beállítása**
   
   Győződj meg róla, hogy létezik a `.env.test` fájl:
   ```bash
   cat .env.test
   ```

### Tesztek Futtatása

#### Összes teszt futtatása
```bash
composer test
# vagy
vendor/bin/phpunit
```

#### Csak Unit tesztek
```bash
composer test:unit
# vagy
vendor/bin/phpunit --testsuite unit
```

#### Csak Integration tesztek
```bash
composer test:integration
# vagy
vendor/bin/phpunit --testsuite integration
```

#### Specifikus teszt fájl futtatása
```bash
vendor/bin/phpunit tests/Unit/UserTest.php
```

#### Specifikus teszt eset futtatása
```bash
vendor/bin/phpunit --filter testUserCreation
```

#### Coverage riport generálása
```bash
vendor/bin/phpunit --coverage-html coverage
```
A riport megnyitása: `coverage/index.html`

---

## CI/CD Pipeline

### Pipeline Áttekintés

A GitHub Actions alapú CI/CD pipeline a következő szakaszokból áll:

```
┌─────────────────┐
│  Code Push      │
│  (Git commit)   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  CI - Testing   │◄─── Automatikus tesztek futtatása
│  - Unit Tests   │
│  - Integration  │
│  - Coverage     │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Code Quality    │◄─── Kódminőség ellenőrzés
│  - Syntax check │
│  - Statistics   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Security Scan   │◄─── Biztonsági ellenőrzés
│  - Vuln. check  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Build           │◄─── Build artifact készítése
│  - Optimize     │
│  - Package      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Deploy          │◄─── Deployment (csak main branch)
│  - Production   │
└─────────────────┘
```

### Pipeline Trigger-ek

A pipeline automatikusan fut:
- **Push event**: `main` vagy `develop` branch-re
- **Pull Request**: `main` vagy `develop` branch-be

### Pipeline Jobs

#### 1. Test Job
- **Futási idő**: ~3-5 perc
- **Lépések**:
  1. Kód checkout
  2. PHP 8.0 telepítése
  3. MySQL service indítása
  4. Composer függőségek telepítése
  5. Test adatbázis setup
  6. Unit tesztek futtatása
  7. Integration tesztek futtatása
  8. Coverage riport generálása

#### 2. Code Quality Job
- **Futási idő**: ~1-2 perc
- **Lépések**:
  1. PHP szintaxis ellenőrzés
  2. Kódstatisztikák gyűjtése

#### 3. Security Job
- **Futási idő**: ~2-3 perc
- **Lépések**:
  1. Ismert biztonsági rések ellenőrzése
  2. Függőségek security audit

#### 4. Build Job
- **Futási idő**: ~2-3 perc
- **Függőségek**: Test és Code Quality job-ok sikeres lefutása
- **Lépések**:
  1. Production dependencies telepítése
  2. Build artifact készítése (tar.gz)
  3. Artifact feltöltése (7 napig megőrzött)

#### 5. Deploy Job
- **Futási idő**: ~1-2 perc
- **Futási feltétel**: Csak `main` branch push esetén
- **Függőségek**: Minden előző job sikeres lefutása
- **Lépések**:
  1. Build artifact letöltése
  2. Deployment végrehajtása (jelenleg szimuláció)

### Artifacts

A pipeline a következő artifact-okat készíti:

1. **coverage-report** (coverage.xml)
   - Test coverage XML formátumban
   - Megtekinthető: Actions → Run → Artifacts

2. **ecodrive-build** (tar.gz)
   - Production-ready build package
   - 7 napig megőrzött
   - Letölthető deployment-hez

### Környezeti Változók

A tesztekhez szükséges környezeti változók:

```yaml
DB_HOST: 127.0.0.1
DB_USER: ecodrive_test
DB_PASSWORD: ecodrive_test
DB_NAME: ecodrive_test
DB_PORT: 3306
```

---

## Deployment Folyamat

### Development → Staging → Production

```
Feature Branch → develop → main → Production
     ↓             ↓         ↓          ↓
  Local Tests   CI Tests  Full CI   Deploy
```

### Deployment Checklist (Junior szinten)

#### Commit előtt
- [ ] Lokális tesztek futtatása: `composer test`
- [ ] Kód formázás ellenőrzése
- [ ] Új funkcióhoz teszt írása

#### Pull Request előtt
- [ ] Branch naprakész a target branch-csel
- [ ] Minden teszt zöld lokálisan
- [ ] Értelmes commit üzenetek

#### Merge előtt
- [ ] CI pipeline zöld
- [ ] Code review elkészült (ha van reviewer)
- [ ] Nincs conflict

#### Production deployment előtt
- [ ] Backup készítése
- [ ] Rollback terv megléte
- [ ] Health check endpoint működik

### Rollback Folyamat

Ha probléma merül fel production-ben:

1. **Gyors rollback**:
   ```bash
   # Előző verzió artifact letöltése
   # Deployment visszavonása
   ```

2. **Fix és újra deploy**:
   ```bash
   git revert <commit-hash>
   git push origin main
   ```

---

## Hibaelhárítás

### Gyakori Problémák

#### 1. "MySQL connection failed" hiba teszteknél

**Probléma**: A teszt adatbázis nem elérhető

**Megoldás**:
```bash
# Ellenőrizd a MySQL service-t
sudo systemctl status mysql

# Ellenőrizd a test user jogosultságait
mysql -u ecodrive_test -p
```

#### 2. "Composer dependencies not found"

**Probléma**: Vendor könyvtár hiányzik

**Megoldás**:
```bash
composer install
```

#### 3. PHPUnit "Class not found" hiba

**Probléma**: Autoload nem megfelelő

**Megoldás**:
```bash
composer dump-autoload
```

#### 4. CI Pipeline timeout

**Probléma**: MySQL service nem indul el időben

**Megoldás**: A `.github/workflows/test.yaml`-ban növeld a timeout értéket

#### 5. Coverage riport nem generálódik

**Probléma**: Xdebug nincs telepítve

**Megoldás**:
```bash
# Ellenőrizd
php -m | grep xdebug

# Ha nincs, telepítsd
pecl install xdebug
```

### Debug Módok

#### Verbose teszt futtatás
```bash
vendor/bin/phpunit --verbose
```

#### Teszt stack trace
```bash
vendor/bin/phpunit --debug
```

#### Csak az első hiba megállítása
```bash
vendor/bin/phpunit --stop-on-failure
```

---

## Best Practices (Junior szinten)

### Teszt Írás

1. **AAA Pattern használata**:
   ```php
   public function testExample() {
       // Arrange - előkészítés
       $user = User::create('test', 'test@test.com', 'pass');
       
       // Act - végrehajtás
       $found = User::find('test', User::FIND_BY_USERNAME);
       
       // Assert - ellenőrzés
       $this->assertNotNull($found);
   }
   ```

2. **Értelmes teszt nevek**:
   - ✅ `testUserCreationWithValidData()`
   - ❌ `test1()`

3. **Egy dolog tesztelése tesztenként**:
   - Egy teszt = egy funkcionalitás

4. **Test isolation**:
   - Minden teszt független
   - `setUp()` és `tearDown()` használata

### Commit Messages

```
feat: új jármű hozzáadása funkció
fix: bejelentkezési bug javítása
test: user model tesztek bővítése
docs: README frissítése
```

### Code Review Checklist

- [ ] Érthető kód
- [ ] Van teszt az új funkcióhoz
- [ ] Dokumentáció frissítve (ha kell)
- [ ] Nincs felesleges console.log vagy debug kód

---

## További Források

- [PHPUnit Dokumentáció](https://phpunit.de/documentation.html)
- [GitHub Actions Dokumentáció](https://docs.github.com/en/actions)
- [CI/CD Best Practices - Atlassian](https://www.atlassian.com/continuous-delivery)
- [CI/CD Best Practices - IBM](https://www.ibm.com/think/topics/ci-cd)

---

**Utolsó frissítés**: 2025-12-04
**Verzió**: 1.0.0
