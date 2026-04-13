# Profil tesztek

## Előfeltétel

- Bejelentkezve **normál felhasználóként**.

## PROFIL OLDAL MEGJELENÉS

### PRF-UI-001 – Profil oldal betöltés

- **Prioritás:** High
- **Lépések:**
  1. Nyisd meg: `/profile`.
- **Elvárt:**
  - Megjelenik a „Profil” oldal.
  - Látszik két kártya: „Email cím és felhasználónév”, „Jelszó”.
  - A felhasználónév/email mezők előtöltöttek a bejelentkezett user adataival.

## FELHASZNÁLÓNÉV / EMAIL MÓDOSÍTÁS

### PRF-DATA-001 – Mentés változtatás nélkül

- **Prioritás:** Medium
- **Lépések:**
  1. `/profile` → „Email cím és felhasználónév” kártya.
  2. Ne módosíts semmit.
  3. Kattints: „Mentés”.
- **Elvárt:** nincs validációs hiba, nincs hiba banner.

### PRF-DATA-002 – Felhasználónév módosítás (siker)

- **Prioritás:** High
- **Lépések:**
  1. A felhasználónév mezőt módosítsd egy egyedi értékre.
  2. „Mentés”.
  3. Frissítsd az oldalt.
- **Elvárt:** a frissítés után is az új felhasználónév jelenik meg.

### PRF-DATA-003 – Email módosítás (siker)

- **Prioritás:** High
- **Lépések:**
  1. Az email mezőt módosítsd egy egyedi, valid emailre.
  2. „Mentés”.
  3. Frissítsd az oldalt.
- **Elvárt:** az új email megmarad.

### PRF-DATA-004 – Felhasználónév üresre állítása

- **Prioritás:** High
- **Lépések:**
  1. Töröld ki teljesen a felhasználónév mezőt.
  2. „Mentés”.
- **Elvárt:** mező hibásnak jelölődik; üzenet: `A felhasználónév minimum 1, maximum 50 karakterből állhat`.

### PRF-DATA-005 – Email formátum hibás

- **Prioritás:** High
- **Lépések:**
  1. Email mező: `rossz-email`.
  2. „Mentés”.
- **Elvárt:** hibaüzenet: `Az email cím formátuma helytelen`.

### PRF-DATA-006 – Felhasználónév már foglalt

- **Prioritás:** High
- **Előfeltétel:** létezik másik user a cél username-mel
- **Lépések:**
  1. Username mezőbe írd be a másik user felhasználónevét.
  2. „Mentés”.
- **Elvárt:** hibaüzenet: `A felhasználónév már foglalt`.

### PRF-DATA-007 – Email már foglalt

- **Prioritás:** High
- **Előfeltétel:** létezik másik user a cél email címmel
- **Lépések:**
  1. Email mezőbe írd be a másik user emailjét.
  2. „Mentés”.
- **Elvárt:** hibaüzenet: `Az email cím már foglalt`.

### PRF-DATA-008 – Csak a kis/nagybetű változik (case sensitivity)

- **Prioritás:** Medium
- **Leírás:** a backend összehasonlítása sztring-egyezést néz, az adatbázis collation viszont jellemzően case-insensitive.
- **Lépések:**
  1. Módosítsd a felhasználónevet úgy, hogy csak kis/nagybetű tér el (pl. `TesztUser` → `tesztuser`).
  2. „Mentés”.
- **Elvárt:** üzleti elvárás szerint ez inkább „nincs változás” (vagy engedett). Ha `A felhasználónév már foglalt` hibát kapsz, rögzíts bugot.

## JELSZÓ MÓDOSÍTÁS

### PRF-PASS-001 – Jelszócsere sikeres

- **Prioritás:** High
- **Lépések:**
  1. „Jelszó” kártya.
  2. Add meg a helyes „Jelenlegi jelszó”-t.
  3. Add meg új jelszóként a P1 jelszót és megerősítésként is P1-et.
  4. „Új jelszó beállítása”.
  5. Jelentkezz ki, majd be az új jelszóval.
- **Elvárt:** az új jelszóval be tudsz jelentkezni, a régivel nem.

### PRF-PASS-002 – Hibás jelenlegi jelszó

- **Prioritás:** High
- **Lépések:**
  1. „Jelenlegi jelszó” mezőbe írj rossz jelszót.
  2. Új jelszó + megerősítés legyen P1.
  3. „Új jelszó beállítása”.
- **Elvárt:** hibaüzenet a jelenlegi jelszóra: `A megadott jelszó helytelen`.

### PRF-PASS-003 – Új jelszó policy sértés (P2–P5)

- **Prioritás:** High
- **Lépések:**
  1. „Jelenlegi jelszó” helyes.
  2. Új jelszó/megerősítés: futtasd a P2–P5 eseteket.
- **Elvárt:**
  - P2/P3/P4: hiba `A jelszónak legalább 8...`.
  - P5: hiba `A jelszavak nem egyeznek`.

### PRF-PASS-004 – Üres mezők jelszó váltásnál

- **Prioritás:** Medium
- **Lépések:**
  1. Hagyd üresen a mezőket.
  2. „Új jelszó beállítása”.
- **Elvárt:** validációs hibák jelennek meg (legalább a jelszó policy és/vagy jelenlegi jelszó hibája).

## HIBAKEZELÉS / UX

### PRF-UX-001 – Betöltés alatti állapot

- **Prioritás:** Low
- **Lépések:**
  1. Kattints mentésre.
  2. Figyeld meg, hogy a gomb és mezők átmenetileg „disabled” és a gombon spinner jelenik meg.
- **Elvárt:** a felület nem enged dupla mentést a kérés futása közben.
