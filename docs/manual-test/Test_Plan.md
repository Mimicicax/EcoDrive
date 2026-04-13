# EcoDrive – Manuális teszt terv (Test Plan)

## 1. Cél

A tesztelés célja, hogy igazolja: az EcoDrive webalkalmazás jelenleg implementált funkciói (bejelentkezés, regisztráció, profil, járművek, utazási napló, statisztika, admin) üzletileg helyesen, stabilan és biztonságosan működnek, valamint hogy a validációk és hibakezelések konzisztensen viselkednek.

## 2. Tesztelt rendszer (SUT)

- PHP 8.3 + Apache (Docker)
- MySQL adatbázis (Docker)
- UI: szerveroldali renderelt nézetek + JS `fetch()` kérések (PUT/PATCH/DELETE)

## 3. Hatókör (Scope)

### 3.1 In-scope (a kódban ténylegesen elérhető)

- Autentikáció
  - Regisztráció
  - Bejelentkezés (felhasználónév *vagy* email)
  - Kijelentkezés
  - Session cookie működés
- Profil
  - Felhasználónév/email módosítás
  - Jelszó módosítás (jelenlegi jelszó ellenőrzéssel)
- Járművek
  - Jármű hozzáadás (modal)
  - Jármű adatainak módosítása (AJAX PUT)
  - Jármű törlése (AJAX DELETE)
- Utazási napló
  - Bejegyzés hozzáadás (modal)
  - Szűrés (év + jármű)
  - Bejegyzés törlés (AJAX DELETE)
- Statisztika
  - Havi/éves összesítések
  - Diagramok (Chart.js)
  - Extrapoláció (legkisebb négyzetek)
- Admin (csak admin szerepkör)
  - Felhasználó keresés (username/email)
  - Felhasználó módosítás (AJAX PATCH)
  - Felhasználó törlés (AJAX DELETE)
- Routing / hibák
  - 404, 405, 500 oldalak
  - Auth/role alapú átirányítások

### 3.2 Out-of-scope / Nem implementált (dokumentált, de a kódban hiányzik)

Az alábbi funkciók szerepelnek a projektdokumentációban/ötletlistában, de a jelenlegi kódbázisban nem található hozzájuk endpoint/nézet:

- Elfelejtett jelszó + emailben küldött reset link
- Útvonal tervezés (külső térkép API, szintemelkedés)
- Exportálás (kép/PDF)
- Google OAuth

## 4. Tesztelési megközelítés

- **Black-box** szemlélet (funkcionális UI + API viselkedés)
- **Ekvivalencia partíciók** és **határérték elemzés** (pl. username hossz, évjárat, rendszám formátum, dátum-idő)
- **Role-based hozzáférés** ellenőrzés (admin vs. normál user)
- **Hibakezelés** és HTTP státuszkódok (200/302/303/400/401/403/404/405/422/500)
- **Exploratív teszt** (rövid sessionök: „Mi törik el, ha…?”)

## 5. Tesztkörnyezet

### 5.1 Ajánlott lokális futtatás

- Docker Compose: `compose.dev.yml`
- App: http://localhost:8888
- phpMyAdmin: http://localhost:8080
- DB: localhost:3306

### 5.2 Böngészők (minimum)

- Chrome (aktuális)
- Firefox (aktuális)

## 6. Tesztadatok és szerepkörök

### 6.1 Beépített admin

- Felhasználónév: `admin`
- Jelszó: `admin`

### 6.2 Normál felhasználó

- A tesztek nagy része egy frissen regisztrált normál felhasználóval futtatható.

### 6.3 Ajánlott tesztadat-setup (gyors)

1) Hozz létre 1 normál felhasználót (pl. `tesztuser1`).
2) Adj hozzá legalább 2 járművet eltérő CO₂ rátával.
3) Rögzíts több naplóbejegyzést:
   - aktuális hónapban
   - előző hónapban
   - előző évben (ha a dátum mező engedi)

## 7. Entry / Exit kritériumok

### 7.1 Entry

- Alkalmazás elérhető (HTTP 200/302/303 várható a megfelelő útvonalakon)
- Adatbázis kapcsolat működik (nincs 500-as „loadFailed”)

### 7.2 Exit

- Smoke tesztek mind sikeresek
- Nincs **Critical / Major** severity bug a core flow-kban (login, jármű létrehozás, napló mentés)
- Dokumentált ismert hiányosságok/eltérések (ha vannak)

## 8. Kockázatok / megjegyzések

- CSRF védelem a kódban nem látható (state-changing `fetch()` hívások cookie alapú auth mellett).
- Néhány validáció (pl. negatív számok) a backendben a `filter_var` opciók miatt potenciálisan átcsúszhat.
- A dokumentációban szereplő „elfelejtett jelszó”, „export” jelenleg nem tesztelhető a meglévő UI-val.

## 9. Hibajegy (Bug Report) sablon

- **ID:** (pl. ECO-XXX)
- **Cím:** rövid, egyértelmű
- **Severity:** Critical / Major / Minor
- **Priority:** High / Medium / Low
- **Környezet:** OS, böngésző, build/branch, futtatási mód (Docker)
- **Előfeltétel:** (pl. bejelentkezve normál userként)
- **Reprodukciós lépések:** számozott lista
- **Elvárt eredmény:**
- **Tényleges eredmény:**
- **Melléklet:** screenshot/videó, console log, hálózati log
