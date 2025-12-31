# 🚗 EcoDrive

Járműkezelő és fogyasztás-követő webalkalmazás PHP-ban.

## 📋 Projekt Áttekintés

Az EcoDrive egy egyszerű, de teljes körű webalkalmazás, amely lehetővé teszi a felhasználók számára járműveik nyilvántartását és fogyasztásuk követését.

### Főbb Funkciók

- 👤 **User Authentication** - Regisztráció, bejelentkezés, kijelentkezés
- 🚙 **Járműkezelés** - Járművek hozzáadása, módosítása, törlése
- 📊 **Profil kezelés** - Felhasználói adatok módosítása
- 🔒 **Biztonság** - Session alapú authentikáció, XSS védelem, SQL injection védelem

## 🛠️ Technológiai Stack

- **Backend:** PHP 8.0+
- **Adatbázis:** MySQL 8.0 / MariaDB 10.5+
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Testing:** PHPUnit 9.5
- **CI/CD:** GitHub Actions

## 🚀 Gyors Kezdés

### Előfeltételek

- PHP 8.0 vagy újabb
- MySQL 8.0 vagy MariaDB 10.5+
- Composer
- Web szerver (Apache/Nginx) vagy `php -S`

### Telepítés

1. **Repository klónozása**
   ```bash
   git clone https://github.com/Mimicicax/EcoDrive.git
   cd EcoDrive
   ```

2. **Függőségek telepítése**
   ```bash
   composer install
   ```

3. **Adatbázis beállítása**
   ```bash
   # Importáld a sémát
   mysql -u root -p < app/database/database.sql
   ```

4. **Környezeti változók beállítása**
   
   Másold át és szerkeszd a `.env` fájlt:
   ```bash
   cp .env.example .env
   # Szerkeszd az adatbázis credentials-t
   ```

5. **Alkalmazás indítása**
   ```bash
   # Beépített PHP szerverrel
   php -S localhost:8000
   
   # Vagy konfiguráld a web szerveredet a projekt könyvtárra
   ```

6. **Böngészőben megnyitás**
   ```
   http://localhost:8000
   ```

## 🧪 Tesztelés

A projekt teljes tesztelési infrastruktúrával rendelkezik.

### Tesztek Futtatása

```bash
# Összes teszt
composer test

# Csak unit tesztek
composer test:unit

# Csak integration tesztek
composer test:integration
```

### Test Environment Setup

```bash
# Windows
cd tests
setup-test-db.bat

# Linux/Mac
cd tests
chmod +x setup-test-db.sh
./setup-test-db.sh
```

### Teszt Lefedettség

- ✅ **47 automated test** (30 unit + 17 integration)
- ✅ **26 manuális teszt eset** részletes dokumentációval
- ✅ Model layer tesztelés (~90% coverage)
- ✅ Authentication flow tesztelés
- ✅ Vehicle management tesztelés

📖 **Részletes dokumentáció:** [tests/README.md](tests/README.md)  
🚀 **Gyors útmutató:** [tests/QUICK_START.md](tests/QUICK_START.md)

## 🔄 CI/CD Pipeline

A projekt GitHub Actions alapú CI/CD pipeline-nal rendelkezik.

### Pipeline Stages

```
Push/PR → Testing → Code Quality → Security → Build → Deploy
```

1. **Testing** - Automatizált tesztek futtatása
2. **Code Quality** - Szintaxis ellenőrzés, statisztikák
3. **Security** - Biztonsági scan, vulnerability check
4. **Build** - Production build artifact készítése
5. **Deploy** - Deployment szimuláció (csak main branch)

### Pipeline Státusz

![CI/CD Pipeline](https://github.com/Mimicicax/EcoDrive/workflows/EcoDrive%20CI/CD%20Pipeline/badge.svg)

**Konfiguráció:** [.github/workflows/test.yaml](.github/workflows/test.yaml)

## 📁 Projekt Struktúra

```
EcoDrive/
├── app/                      # Alkalmazás logika
│   ├── database/            # Adatbázis séma
│   ├── endpoints/           # Endpoint handler osztályok
│   ├── helpers/             # Helper funkciók
│   ├── models/              # Model osztályok (User, Vehicle, Session)
│   ├── views/               # View template-ek
│   └── routing.php          # Route definíciók
├── assets/                   # Frontend assets
│   ├── css/                 # Stylesheet-ek
│   ├── script.js            # JavaScript
│   └── style.css            # Fő CSS
├── tests/                    # Tesztek és teszt dokumentáció
│   ├── Unit/                # Unit tesztek
│   ├── Integration/         # Integration tesztek
│   ├── README.md            # Teszt dokumentáció
│   ├── QUICK_START.md       # Gyors útmutató
│   └── MANUAL_TEST_CASES.md # Manuális teszt esetek
├── .github/                  # GitHub Actions workflows
│   └── workflows/
│       └── test.yaml        # CI/CD pipeline
├── boot.php                  # Alkalmazás bootstrap
├── config.php                # Konfiguráció
├── index.php                 # Belépési pont
├── composer.json             # PHP dependencies
├── phpunit.xml               # PHPUnit konfiguráció
└── README.md                 # Ez a fájl
```

## 🔐 Biztonság

Az alkalmazás a következő biztonsági mechanizmusokat implementálja:

- ✅ **Password Hashing** - Argon2i algoritmus
- ✅ **SQL Injection védelem** - Prepared statements
- ✅ **XSS védelem** - Output escaping
- ✅ **Session Security** - HTTPOnly cookie-k, secure session kezelés
- ✅ **CSRF védelem** - Token alapú védelem (jövőbeli fejlesztés)

## 📝 API Dokumentáció

### Authentication Endpoints

- `GET /login` - Bejelentkezési oldal
- `POST /login` - Bejelentkezés végrehajtása
- `GET /register` - Regisztrációs oldal
- `POST /register` - Regisztráció végrehajtása
- `POST /logout` - Kijelentkezés

### Profile Endpoints

- `GET /profile` - Profil oldal
- `PUT /profile` - Profil adatok frissítése

### Vehicle Endpoints

- `GET /vehicles` - Járművek listázása
- `POST /vehicles` - Új jármű hozzáadása
- `PUT /vehicles/{id}` - Jármű módosítása
- `DELETE /vehicles/{id}` - Jármű törlése

## 🤝 Hozzájárulás

A projekt nyitott a hozzájárulásokra!

### Hozzájárulási Folyamat

1. Fork-old a repository-t
2. Hozz létre egy feature branch-et (`git checkout -b feature/UjFunkció`)
3. Commitold a változtatásokat (`git commit -m 'feat: új funkció hozzáadása'`)
4. Push-old a branch-et (`git push origin feature/UjFunkció`)
5. Nyiss egy Pull Request-et

### Commit Konvenciók

```
feat: új funkció
fix: bug javítás
docs: dokumentáció frissítés
test: teszt hozzáadása/módosítása
refactor: kód refactoring
style: kód formázás
```

## 📖 Dokumentáció

- **Teszt dokumentáció:** [tests/README.md](tests/README.md)
- **Gyors útmutató:** [tests/QUICK_START.md](tests/QUICK_START.md)
- **Manuális tesztek:** [tests/MANUAL_TEST_CASES.md](tests/MANUAL_TEST_CASES.md)
- **Implementation summary:** [tests/IMPLEMENTATION_SUMMARY.md](tests/IMPLEMENTATION_SUMMARY.md)

## 🐛 Hibabejelentés

Hibát találtál? [Nyiss egy issue-t](https://github.com/Mimicicax/EcoDrive/issues/new)!

## 📄 Licenc

Ez a projekt oktatási célú projekt. Nincs konkrét licenc meghatározva.

## 👥 Szerzők

- **Mimicicax** - Initial work - [GitHub](https://github.com/Mimicicax)

## 🙏 Köszönetnyilvánítás

- CI/CD best practices: [Atlassian](https://www.atlassian.com/continuous-delivery)
- CI/CD best practices: [IBM](https://www.ibm.com/think/topics/ci-cd)
- PHPUnit dokumentáció
- GitHub Actions dokumentáció

---

**Verzió:** 1.0.0  
**Utolsó frissítés:** 2025-12-04  
**Státusz:** ✅ Production Ready (with testing infrastructure)
