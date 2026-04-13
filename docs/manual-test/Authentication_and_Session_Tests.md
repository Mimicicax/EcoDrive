# Autentikáció és session tesztek

## Tesztadatok (javasolt)

- **Normál felhasználó (U1):** a tesztek során regisztrálandó (pl. `tesztuser1`, `tesztuser1@example.com`).
- **Admin:** `admin` / `admin` (adatbázis seed).

### Jelszó teszt adatok (ekvivalencia partíciók + határérték)

| Kód | Jelszó       | Megerősítés | Várható                               |
| ---- | ------------- | -------------- | --------------------------------------- |
| P1   | `Abcdefg1`  | `Abcdefg1`   | Elfogadva                               |
| P2   | `Abcdefg`   | `Abcdefg`    | Hiba: "A jelszónak legalább 8..."     |
| P3   | `abcdefgh1` | `abcdefgh1`  | Hiba a policy szerint (nincs nagybetű) |
| P4   | `Abcdefgh`  | `Abcdefgh`   | Hiba a policy szerint (nincs szám)     |
| P5   | `Abcdefg1`  | `Abcdefg2`   | Hiba: "A jelszavak nem egyeznek"        |

Megjegyzés: a hibaüzenet a rendszerben a következő: `A jelszónak legalább 8 karakterből kell állnia és tartalmaznia kell legalább egy nagybetűt és számot`.

## REGISZTRÁCIÓ

### AUTH-REG-001 – Sikeres regisztráció

- **Prioritás:** High
- **Előfeltétel:** kijelentkezett állapot
- **Lépések:**
  1. Nyisd meg: `/register`.
  2. Add meg érvényes `Felhasználónév`, `Email cím` értékeket.
  3. Add meg a P1 jelszó-párt.
  4. Kattints: „Regisztráció”.
- **Elvárt:** átirányítás a bejelentkezés oldalra (`/login`).

### AUTH-REG-002 – Felhasználónév határértékek (1 és 50 karakter)

- **Prioritás:** High
- **Előfeltétel:** kijelentkezett állapot
- **Lépések:**
  1. Nyisd meg: `/register`.
  2. Regisztrálj 1 karakteres username-mel (pl. `a`).
  3. Regisztrálj 50 karakteres username-mel.
- **Elvárt:** mindkét esetben sikeres regisztráció (ha az email egyedi).

### AUTH-REG-003 – Felhasználónév túl hosszú (51 karakter)

- **Prioritás:** High
- **Előfeltétel:** kijelentkezett állapot
- **Lépések:**
  1. Nyisd meg: `/register`.
  2. Adj meg 51 karakteres `Felhasználónév` mezőt.
  3. Adj meg egyedi emailt és P1 jelszó-párt.
  4. Kattints: „Regisztráció”.
- **Elvárt:** hibaüzenet a felhasználónévhez: `A felhasználónév minimum 1, maximum 50 karakterből állhat`.

### AUTH-REG-004 – Felhasználónév tiltott karakter: `@`

- **Prioritás:** High
- **Előfeltétel:** kijelentkezett állapot
- **Lépések:**
  1. `/register`.
  2. Username: pl. `nev@valami`.
  3. Egyedi email + P1.
  4. „Regisztráció”.
- **Elvárt:** hibaüzenet: `Érvénytelen karakter a felhasználónévben`.

### AUTH-REG-005 – Felhasználónév már foglalt

- **Prioritás:** High
- **Előfeltétel:** létezik a megadott username (pl. előző tesztben létrehozva)
- **Lépések:**
  1. `/register`.
  2. Add meg a már létező felhasználónevet.
  3. Adj meg egyedi emailt + P1.
  4. „Regisztráció”.
- **Elvárt:** hibaüzenet: `A felhasználónév már foglalt`.

### AUTH-REG-006 – Email formátum hibás

- **Prioritás:** High
- **Előfeltétel:** kijelentkezett állapot
- **Lépések:**
  1. `/register`.
  2. Email: pl. `not-an-email`.
  3. Username egyedi + P1.
  4. „Regisztráció”.
- **Elvárt:** hibaüzenet: `Az email cím formátuma helytelen`.

### AUTH-REG-007 – Email már foglalt

- **Prioritás:** High
- **Előfeltétel:** létezik a megadott email (pl. előző tesztben létrehozva)
- **Lépések:**
  1. `/register`.
  2. Email: már létező.
  3. Username egyedi + P1.
  4. „Regisztráció”.
- **Elvárt:** hibaüzenet: `Az email cím már foglalt`.

### AUTH-REG-008 – Jelszó policy variációk (P2–P5)

- **Prioritás:** High
- **Előfeltétel:** kijelentkezett állapot
- **Lépések:**
  1. `/register`.
  2. Fix: username+email legyen egyedi.
  3. Futtasd le a táblázatban szereplő jelszó párokat (P2–P5).
- **Elvárt:**
  - P2/P3/P4: hibaüzenet a jelszóhoz: `A jelszónak legalább 8 karakterből kell állnia és tartalmaznia kell legalább egy nagybetűt és számot`.
  - P5: hibaüzenet: `A jelszavak nem egyeznek`.

### AUTH-REG-009 – Regisztráció bejelentkezett állapotban

- **Prioritás:** Medium
- **Előfeltétel:** bejelentkezve normál userként
- **Lépések:**
  1. Nyisd meg: `/register`.
- **Elvárt:** átirányítás a főoldalra (`/`).

## BEJELENTKEZÉS / KIJELENTKEZÉS

### AUTH-LGN-001 – Sikeres bejelentkezés felhasználónévvel

- **Prioritás:** High
- **Előfeltétel:** létező normál user (U1)
- **Lépések:**
  1. `/login`.
  2. `Felhasználónév`: U1 username.
  3. `Jelszó`: U1 jelszó.
  4. „Bejelentkezés”.
- **Elvárt:** átirányítás `/` (vagy az első védett oldal) és megjelenik a navigáció.

### AUTH-LGN-002 – Sikeres bejelentkezés emaillel

- **Prioritás:** High
- **Előfeltétel:** létező normál user (U1)
- **Lépések:**
  1. `/login`.
  2. `Felhasználónév`: U1 email.
  3. `Jelszó`: U1 jelszó.
  4. „Bejelentkezés”.
- **Elvárt:** sikeres login (átirányítás `/`).

### AUTH-LGN-003 – Hibás jelszó

- **Prioritás:** High
- **Előfeltétel:** létező normál user
- **Lépések:**
  1. `/login`.
  2. Add meg a helyes usernevet/emailt, de rossz jelszót.
  3. „Bejelentkezés”.
- **Elvárt:** a bejelentkezés sikertelen; hibaüzenet megjelenik: `A felhasználónév vagy jelszó helytelen`.

### AUTH-LGN-004 – Nem létező felhasználó

- **Prioritás:** High
- **Előfeltétel:** kijelentkezett
- **Lépések:**
  1. `/login`.
  2. Username: `nincsilyenuser`.
  3. Jelszó: tetszőleges.
  4. „Bejelentkezés”.
- **Elvárt:** ugyanaz az általános hibaüzenet: `A felhasználónév vagy jelszó helytelen`.

### AUTH-LGN-005 – Sikertelen login után a username mező megmarad

- **Prioritás:** Medium
- **Előfeltétel:** kijelentkezett
- **Lépések:**
  1. Indíts sikertelen login próbát (AUTH-LGN-003).
- **Elvárt:** a `Felhasználónév` mezőben megmarad a korábban beírt érték.

### AUTH-LGN-006 – Login oldal bejelentkezve

- **Prioritás:** Medium
- **Előfeltétel:** bejelentkezve
- **Lépések:**
  1. Nyisd meg: `/login`.
- **Elvárt:** átirányítás `/`.

### AUTH-LGO-001 – Kijelentkezés

- **Prioritás:** High
- **Előfeltétel:** bejelentkezve
- **Lépések:**
  1. Kattints a „Kijelentkezés” gombra.
- **Elvárt:** átirányítás `/login` és a védett oldalak újratöltéskor loginra irányítanak.

### AUTH-LGO-002 – Kijelentkezés kijelentkezve

- **Prioritás:** Low
- **Előfeltétel:** kijelentkezett
- **Lépések:**
  1. Nyisd meg: `/logout`.
- **Elvárt:** átirányítás `/`.

## HOZZÁFÉRÉS VEDETT OLDALAKHOZ

### AUTH-ACC-001 – Védett oldalak bejelentkezés nélkül

- **Prioritás:** High
- **Előfeltétel:** kijelentkezett
- **Lépések:**
  1. Nyisd meg sorban: `/`, `/vehicles`, `/profile`, `/journal`, `/statistics`, `/admin`.
- **Elvárt:** mindegyik esetben átirányítás a `/login` oldalra.

### AUTH-ACC-002 – Admin role redirect (admin nem mehet user-oldalra)

- **Prioritás:** High
- **Előfeltétel:** bejelentkezve adminnal
- **Lépések:**
  1. Nyisd meg: `/vehicles`.
  2. Nyisd meg: `/journal`.
  3. Nyisd meg: `/statistics`.
- **Elvárt:** mindhárom esetben átirányítás `/admin`.

### AUTH-ACC-003 – Nem admin nem érheti el az admin oldalt

- **Prioritás:** High
- **Előfeltétel:** bejelentkezve normál userrel
- **Lépések:**
  1. Nyisd meg: `/admin`.
- **Elvárt:** átirányítás `/`.

### AUTH-UI-001 – Navigáció elemei szerepkör szerint

- **Prioritás:** Medium
- **Lépések (normál user):**
  1. Jelentkezz be normál felhasználóval.
  2. Ellenőrizd a bal oldali navigáció linkjeit.
- **Elvárt (normál user):**
  - Látható: „Profil”, „Járműveim”, „Utazási napló”, „Statisztika”, „Kijelentkezés”.
  - Nem látható: „Felhasználók”.
- **Lépések (admin):**
  1. Jelentkezz be adminnal.
  2. Ellenőrizd a navigáció linkjeit.
- **Elvárt (admin):**
  - Látható: „Profil”, „Felhasználók”, „Kijelentkezés”.
  - Nem látható: „Járműveim”, „Utazási napló”, „Statisztika”.

### AUTH-ACC-004 – Session cookie tulajdonságok

- **Prioritás:** Medium
- **Előfeltétel:** sikeres login
- **Lépések:**
  1. Böngésző DevTools → Application/Storage → Cookies.
  2. Keresd meg a session sütit.
- **Elvárt:**
  - Név: `ECODRIVE_SESSION` (vagy ami `.env`-ben a `SESSION_COOKIE_NAME`).
  - `HttpOnly` be van kapcsolva.
  - Path: `/`.
  - Lejárat kb. 7 nap.

## ROUTING / HIBÁK

### AUTH-RT-001 – Trailing slash redirect

- **Prioritás:** Low
- **Előfeltétel:** bejelentkezett (hogy védett oldalak betöltődjenek)
- **Lépések:**
  1. Nyisd meg: `/vehicles/`.
- **Elvárt:** automatikus átirányítás `/vehicles` (nincs végtelen loop).

### AUTH-RT-002 – 404 oldal

- **Prioritás:** Low
- **Előfeltétel:** mindegy
- **Lépések:**
  1. Nyisd meg: `/valami-nem-letezik`.
- **Elvárt:** 404-es oldal jelenik meg „A keresett oldal nem található” üzenettel.

### AUTH-RT-003 – 405 Method Not Allowed

- **Prioritás:** Low
- **Előfeltétel:** bejelentkezett
- **Lépések:**
  1. Küldj pl. `POST` kérést `/statistics`-ra (DevTools/REST kliens/Postman).
- **Elvárt:** 405 státuszkód.
