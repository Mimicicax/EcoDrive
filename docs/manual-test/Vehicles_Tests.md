# Járművek tesztek

## Előfeltétel

- Bejelentkezve **normál felhasználóként**.

## RENDSZÁM FORMÁTUM TESZTADATOK

A backend az alábbi formátumokat fogadja el:

- `AAA-123`
- `AB-CD-123`
- Dashes nélküli minták: `AAA1234`, `AAAA123`, `AAAAA12`, `AAAAAA1`

| Kód | Rendszám     | Várható                                     |
| ---- | ------------- | --------------------------------------------- |
| LP1  | `ABC-123`   | Elfogadva                                     |
| LP2  | `AB-CD-123` | Elfogadva                                     |
| LP3  | `ABC1234`   | Elfogadva                                     |
| LP4  | `AAAA123`   | Elfogadva                                     |
| LP5  | `AAAAA12`   | Elfogadva                                     |
| LP6  | `AAAAAA1`   | Elfogadva                                     |
| LP7  | `AB-123`    | Hiba: "A rendszám formátuma nem megfelelő" |
| LP8  | `ABC-12A`   | Hiba                                          |
| LP9  | `` (üres)    | Hiba: "A rendszám megadása kötelező"      |

## LISTA / ÜRES ÁLLAPOT

### VHC-LST-001 – Üres állapot jármű nélkül

- **Prioritás:** High
- **Előfeltétel:** a usernek nincs mentett járműve
- **Lépések:**
  1. Nyisd meg: `/vehicles`.
- **Elvárt:** „Még nem mentettél el egy járművet sem” üres állapot.

## JÁRMŰ LÉTREHOZÁS (POST)

### VHC-CRT-001 – Jármű hozzáadása (happy path)

- **Prioritás:** High
- **Lépések:**
  1. `/vehicles` → „Jármű hozzáadása”.
  2. Márka: pl. `Tesztla`.
  3. Modell: pl. `Oktávia`.
  4. Rendszám: LP1.
  5. Évjárat: aktuális év.
  6. Fogyasztás: `3.4`.
  7. CO₂ (g/km): `108.2`.
  8. „Hozzáadás”.
- **Elvárt:** a modal bezáródik / oldal frissül, a jármű megjelenik a listában.

### VHC-CRT-002 – Rendszám formátum (LP1–LP9)

- **Prioritás:** High
- **Lépések:**
  1. Nyisd meg a hozzáadás modalt.
  2. Használd a táblázat rendszámait (LP1–LP9), a többi mező legyen érvényes.
  3. Kattints „Hozzáadás”.
- **Elvárt:**
  - LP1–LP6: sikeres mentés.
  - LP7–LP9: hibaüzenet: `A rendszám formátuma nem megfelelő` vagy üresnél `A rendszám megadása kötelező`.

### VHC-CRT-003 – Rendszám kisbetűből nagybetűsítés

- **Prioritás:** Medium
- **Lépések:**
  1. Adj hozzá járművet úgy, hogy a rendszámot kisbetűvel írod be (pl. `ab-cd-123`).
- **Elvárt:** a listában a rendszám nagybetűvel jelenik meg (pl. `AB-CD-123`).

### VHC-CRT-004 – Duplikált rendszám (case-insensitive)

- **Prioritás:** High
- **Előfeltétel:** létezik egy jármű ugyanazzal a rendszámmal
- **Lépések:**
  1. Próbálj meg létrehozni még egy járművet ugyanazzal a rendszámmal (akár eltérő kis/nagybetűvel).
- **Elvárt:** hiba: `A rendszám már foglalt`.

### VHC-CRT-005 – Márka kötelező és formátum

- **Prioritás:** High
- **Lépések:**
  1. Hagyd üresen a „Márka” mezőt, a többi mező érvényes.
  2. „Hozzáadás”.
  3. Majd próbáld: `Tes!la` (felkiáltójel).
- **Elvárt:**
  - Üresnél: `A márkanév megadása kötelező`
  - Tiltott karakter esetén: `A márkanévnek alfanumerikusnak kell lennie`

### VHC-CRT-006 – Modell kötelező és formátum

- **Prioritás:** High
- **Lépések:**
  1. Hagyd üresen a „Modell” mezőt.
  2. Majd próbáld: `Modell#1`.
- **Elvárt:**
  - Üresnél: `A modell megadása kötelező`
  - Tiltott karakter esetén: `A modellnek alfanumerikusnak kell lennie`

### VHC-CRT-007 – Évjárat határértékek

- **Prioritás:** High
- **Lépések:**
  1. Próbáld évjáratként: `1899`, `1900`, aktuális év, aktuális év + 1.
- **Elvárt:**
  - `1900` és aktuális év: elfogadva.
  - `1899` és aktuális év + 1: hiba `Az évjárat érvénytelen`.

### VHC-CRT-008 – Fogyasztás / CO₂ mező formátum

- **Prioritás:** High
- **Lépések:**
  1. Fogyasztás mezőbe írj: `3,4`.
  2. CO₂ mezőbe írj: `108,2`.
  3. „Hozzáadás”.
  4. Majd próbáld: `abc`.
- **Elvárt:**
  - `3,4` és `108,2`: elfogadva.
  - `abc`: hiba `A megadott fogyasztás formátuma nem megfelelő` / `A megadott CO2 kibocsátás formátuma nem megfelelő`.

### VHC-CRT-009 – Negatív számok (kockázati teszt)

- **Prioritás:** Medium
- **Lépések:**
  1. Add meg fogyasztásnak: `-1`.
  2. Add meg CO₂-nek: `-10`.
  3. „Hozzáadás”.
- **Elvárt:** üzleti logika szerint elutasítás (nem lehet negatív).
- **Megjegyzés:** ha elfogadja, rögzíts bugot (backend validáció gyanús).

### VHC-CRT-010 – Márka/Modell hossz (DB kompatibilitás)

- **Prioritás:** Medium
- **Leírás:** a DB-ben a `brand` és `model` mezők `VARCHAR(20)`.
- **Lépések:**
  1. Adj meg 20 karakteres márkát/modellt (elfogadott karakterekkel).
  2. Adj meg 21+ karakteres márkát/modellt.
  3. „Hozzáadás”.
- **Elvárt:**
  - 20 karakter: siker.
  - 21+ karakter: üzleti elvárás szerint validációs hiba vagy stabil mentés – ha adatvesztés/truncation/500 jelentkezik, rögzíts bugot.

## JÁRMŰ MÓDOSÍTÁS (PUT)

### VHC-UPD-001 – Jármű módosítás siker

- **Prioritás:** High
- **Előfeltétel:** legalább 1 jármű a listában
- **Lépések:**
  1. Módosítsd a márkát/modellt/évjáratot.
  2. Kattints: „Mentés”.
  3. Figyeld meg a kártya fejlécét (rendszám, márka, modell, év).
  4. Frissítsd az oldalt.
- **Elvárt:**
  - Mentés után a fejléc azonnal frissül.
  - Frissítés után is megmaradnak az adatok.

### VHC-UPD-002 – Validációs hibák módosításnál

- **Prioritás:** High
- **Lépések:**
  1. Írj érvénytelen márkát/modellt/rendszámot/évjáratot.
  2. „Mentés”.
- **Elvárt:** a mező(k) hibásnak jelölődnek, és hibaüzenet(ek) jelennek meg a kártyán.

### VHC-UPD-003 – Rendszám ütközés módosításnál

- **Prioritás:** High
- **Előfeltétel:** legalább 2 jármű létezik
- **Lépések:**
  1. Az egyik jármű rendszámát állítsd a másik rendszámára.
  2. „Mentés”.
- **Elvárt:** hiba `A rendszám már foglalt`.

## JÁRMŰ TÖRLÉS (DELETE)

### VHC-DEL-001 – Jármű törlés megerősítéssel

- **Prioritás:** High
- **Előfeltétel:** legalább 1 jármű
- **Lépések:**
  1. Kattints: „Jármű törlése”.
  2. A confirm ablakban válaszd: OK.
- **Elvárt:** a kártya eltűnik (animációval), majd a jármű már nem látszik.

### VHC-DEL-002 – Jármű törlés megszakítása

- **Prioritás:** Low
- **Lépések:**
  1. „Jármű törlése”.
  2. Confirm: Cancel.
- **Elvárt:** semmi nem változik.

### VHC-DEL-003 – Utolsó jármű törlése után üres állapot

- **Prioritás:** Medium
- **Előfeltétel:** pontosan 1 jármű van
- **Lépések:**
  1. Töröld a járművet.
- **Elvárt:** megjelenik az üres állapot („Még nem mentettél el egy járművet sem…”).

## MODAL UX

### VHC-MOD-001 – Modal bezárás reseteli a mezőket és hibákat

- **Prioritás:** Low
- **Lépések:**
  1. Nyisd meg a „Jármű hozzáadása” modalt.
  2. Generálj validációs hibát (pl. üres márka), így piros hiba jelenik meg.
  3. Zárd be a modalt („Mégsem” vagy Esc).
  4. Nyisd meg újra.
- **Elvárt:** az inputok üresek (vagy default), és a korábbi hibaelemek nem látszanak.

## API / JOGOSULTSÁG (NEGATÍV)

Ezek a tesztek DevTools (Network → „Edit and Resend”) vagy Postman segítségével futtathatók.

### VHC-API-001 – PUT /vehicles: nem létező vehicleId

- **Prioritás:** Low
- **Lépések:**
  1. Küldj `PUT /vehicles` kérést érvényes body-val, de `vehicleId` legyen nem létező (pl. `999999`).
- **Elvárt:** 404.

### VHC-API-002 – PUT /vehicles: más felhasználó járműve

- **Prioritás:** Medium
- **Előfeltétel:** legyen 2 külön user, mindkettőnek jármű
- **Lépések:**
  1. Jelentkezz be U1-gyel.
  2. Küldj `PUT /vehicles` kérést úgy, hogy a `vehicleId` U2 járművéé.
- **Elvárt:** 401.

### VHC-API-003 – DELETE /vehicles: hiányzó vehicleId

- **Prioritás:** Low
- **Lépések:**
  1. Küldj `DELETE /vehicles` kérést query string nélkül.
- **Elvárt:** 422 és JSON body: `{ "error": "A jármű megadása kötelező" }`.

### VHC-API-004 – DELETE /vehicles: érvénytelen vehicleId

- **Prioritás:** Low
- **Lépések:**
  1. Küldj `DELETE /vehicles?vehicleId=abc`.
- **Elvárt:** 400.

### VHC-API-005 – DELETE /vehicles: más felhasználó járműve

- **Prioritás:** Medium
- **Előfeltétel:** legyen 2 külön user, mindkettőnek jármű
- **Lépések:**
  1. Jelentkezz be U1-gyel.
  2. Küldj `DELETE /vehicles?vehicleId=<U2 jármű id>`.
- **Elvárt:** 401.
