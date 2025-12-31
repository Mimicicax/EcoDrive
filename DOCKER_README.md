# EcoDrive Docker Setup

Ez az útmutató segít beállítani az EcoDrive projektet Docker konténerekben fejlesztési és tesztelési célokra.

## Előfeltételek

- Docker és Docker Compose telepítve a gépeden
- Legalább 2GB szabad RAM (MySQL és PHP számára)

## Gyors indítás

1. **Klónozd vagy másold a projektet egy mappába**

2. **Indítsd el a szolgáltatásokat:**
   ```bash
   docker-compose up --build
   ```

3. **Nyisd meg a böngészőben:**
   - Alkalmazás: http://localhost:8080
   - phpMyAdmin: http://localhost:8081 (felhasználó: ecodrive, jelszó: ecodrive2026)

4. **Tesztek futtatása:**
   ```bash
   docker-compose run --rm test
   ```

## Szolgáltatások

- **app**: PHP 8.1 + Apache web szerver
- **db**: MySQL 8.0 adatbázis
- **phpmyadmin**: Adatbázis kezelő felület
- **test**: PHPUnit tesztek futtatása külön adatbázissal

## Környezeti változók

A fejlesztési környezet alapértelmezett beállításai:
- Adatbázis: ecodrive / ecodrive2026
- Debug mód: bekapcsolva
- Session cookie: ECODRIVE_SESSION

## Biztonsági megjegyzések

⚠️ **Ez a setup fejlesztési célokra készült! Production környezetben:**

- Használj erősebb jelszavakat
- Kapcsold ki a DEBUG_MODE-ot
- Használj HTTPS-t
- Állíts be megfelelő tűzfal szabályokat
- Használj environment specifikus .env fájlokat

## Parancsok

```bash
# Szolgáltatások indítása
docker-compose up -d

# Logok megtekintése
docker-compose logs -f app

# Konténerek leállítása
docker-compose down

# Adatok törlése (adatbázis)
docker-compose down -v
```