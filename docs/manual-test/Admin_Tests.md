# Admin tesztek

## Előfeltétel

- Bejelentkezve **admin** felhasználóval (`admin` / `admin`).

## ADMIN OLDAL MEGJELENÉS

### ADM-UI-001 – Admin oldal alapállapot (nincs query)

- **Prioritás:** High
- **Lépések:**
  1. Nyisd meg: `/admin`.
- **Elvárt:**
  - Megjelenik a „Felhasználó-kezelés” oldal.
  - A keresőmező látható.
  - Üres állapot üzenet: „Keress felhasználókat…”.

## KERESÉS

### ADM-SRC-001 – Keresés felhasználónév alapján

- **Prioritás:** High
- **Előfeltétel:** létezik normál user
- **Lépések:**
  1. A keresőmezőbe írd a felhasználó nevét.
  2. „Keresés”.
- **Elvárt:**
  - Megjelenik a felhasználó adat kártya.
  - A mezők előtöltöttek (username, email).

### ADM-SRC-002 – Keresés email alapján

- **Prioritás:** High
- **Előfeltétel:** létezik normál user
- **Lépések:**
  1. Keresőbe írd a felhasználó email címét (valid email formátum).
  2. „Keresés”.
- **Elvárt:** felhasználó megtalálható.

### ADM-SRC-003 – Nincs találat

- **Prioritás:** Medium
- **Lépések:**
  1. Keresőbe írj nem létező értéket.
  2. „Keresés”.
- **Elvárt:** „A felhasználó nem található” üres állapot és a query megjelenik idézőjelben.

## FELHASZNÁLÓ MÓDOSÍTÁS (PATCH)

### ADM-UPD-001 – Felhasználónév módosítás (siker)

- **Prioritás:** High
- **Előfeltétel:** felhasználó kártya megjelenik
- **Lépések:**
  1. Írj be egy új, egyedi felhasználónevet.
  2. „Mentés” (confirm: OK).
  3. Próbálj belépni a felhasználóval az új felhasználónév + régi jelszó kombinációval.
- **Elvárt:** a felhasználó a megváltozott username-mel be tud lépni.

### ADM-UPD-002 – Email módosítás (siker)

- **Prioritás:** High
- **Lépések:**
  1. Írj be egy új, valid, egyedi email címet.
  2. „Mentés” (OK).
- **Elvárt:** mentés sikeres; a felhasználó emailje frissül.

### ADM-UPD-003 – Jelszó módosítás (siker)

- **Prioritás:** High
- **Lépések:**
  1. Add meg az „Új jelszó” és „Jelszó megerősítése” mezőket (pl. `Abcdefg1`).
  2. „Mentés” (OK).
  3. Próbálj belépni a felhasználóval az új jelszóval.
- **Elvárt:** bejelentkezés sikeres az új jelszóval.

### ADM-UPD-004 – Validációs hibák (username/email/password)

- **Prioritás:** High
- **Lépések:**
  1. Adj meg érvénytelen emailt (pl. `rossz-email`).
  2. Adj meg túl hosszú vagy `@`-ot tartalmazó usernevet.
  3. Adj meg hibás jelszó-párt (policy vagy mismatch).
  4. „Mentés” (OK).
- **Elvárt:**
  - HTTP 422 jellegű validációs hibák a UI-ban a megfelelő mezőknél jelennek meg.
  - Üzenetek:
    - Username: `A felhasználónév minimum 1, maximum 50 karakterből állhat` / `Érvénytelen karakter a felhasználónévben` / `A felhasználónév már foglalt`
    - Email: `Az email cím formátuma helytelen` / `Az email cím már foglalt`
    - Password: `A jelszónak legalább 8...` / `A jelszavak nem egyeznek`

### ADM-UPD-005 – Üres mező viselkedés

- **Prioritás:** Medium
- **Leírás:** admin oldalon az üresen hagyott username/email mező nem kerül validálásra és nem frissít.
- **Lépések:**
  1. Töröld ki teljesen a felhasználónév mezőt.
  2. „Mentés” (OK).
- **Elvárt:** üzleti elvárás szerint valószínűleg hiba lenne; ha a rendszer csendben nem frissít, dokumentáld viselkedésként / rögzíts bugot igény szerint.

## FELHASZNÁLÓ TÖRLÉS (DELETE)

### ADM-DEL-001 – Felhasználó törlése

- **Prioritás:** High
- **Előfeltétel:** felhasználó kártya látható
- **Lépések:**
  1. Kattints: „Felhasználó törlése”.
  2. Confirm: OK.
- **Elvárt:**
  - A kártya eltűnik.
  - Utána új kereséssel a user már nem található.

### ADM-DEL-002 – Felhasználó törlés megszakítása

- **Prioritás:** Low
- **Lépések:**
  1. „Felhasználó törlése”.
  2. Confirm: Cancel.
- **Elvárt:** nincs változás.

### ADM-DEL-003 – Admin saját magát nem törölheti

- **Prioritás:** High
- **Lépések:**
  1. Keress rá az admin felhasználóra (username: `admin`).
  2. „Felhasználó törlése” → Confirm: OK.
- **Elvárt:** a törlés sikertelen; a UI error banner megjelenik: „A felhasználó törlése nem sikerült”; az admin továbbra is be tud lépni.

## JOGOSULTSÁG

### ADM-AUTH-001 – Nem admin nem érheti el az admin oldalt

- **Prioritás:** High
- **Lépések:**
  1. Jelentkezz be normál userrel.
  2. Nyisd meg: `/admin`.
- **Elvárt:** átirányítás `/`.

## API / JOGOSULTSÁG (NEGATÍV)

Ezek DevTools/Postman segítségével futtathatók.

### ADM-API-001 – Nem admin PATCH/DELETE /admin → 403

- **Prioritás:** Medium
- **Előfeltétel:** bejelentkezve normál userrel
- **Lépések:**
  1. Küldj `PATCH /admin` kérést (pl. `user=<valami>` body-val).
  2. Küldj `DELETE /admin?user=<valami>` kérést.
- **Elvárt:** mindkét esetben 403.

### ADM-API-002 – Érvénytelen user paraméter → 404

- **Prioritás:** Low
- **Előfeltétel:** bejelentkezve adminnal
- **Lépések:**
  1. Küldj `PATCH /admin` kérést úgy, hogy `user=abc`.
- **Elvárt:** 404.
