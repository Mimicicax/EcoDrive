# Utazási napló tesztek

## Előfeltétel

- Bejelentkezve **normál felhasználóként**.
- Ajánlott: legyen legalább 1 mentett jármű (különben a jármű választó üres).

## NAPLÓ OLDAL / ÜRES ÁLLAPOT

### JRN-UI-001 – Napló oldal betöltés

- **Prioritás:** High
- **Lépések:**
  1. Nyisd meg: `/journal`.
- **Elvárt:**
  - Megjelenik az „Utazási napló” oldal.
  - Van „Bejegyzés hozzáadása” gomb.
  - Van szűrő (Jármű + Év) és „Szűrés” gomb.

### JRN-UI-002 – Üres napló állapot

- **Prioritás:** High
- **Előfeltétel:** a felhasználónak nincs mentett útja az adott szűrésre
- **Lépések:**
  1. `/journal`.
- **Elvárt:** üres állapot szöveg: „A napló üres…”.

## BEJEGYZÉS LÉTREHOZÁS (POST)

### JRN-CRT-001 – Bejegyzés hozzáadása (happy path)

- **Prioritás:** High
- **Előfeltétel:** legalább 1 jármű
- **Lépések:**
  1. Kattints: „Bejegyzés hozzáadása”.
  2. Jármű: válassz egyet.
  3. Indulás időpontja: állíts be egy értéket (aktuális hónap).
  4. Távolság: `10`.
  5. (Opcionális) cím mezők tetszőleges.
  6. „Hozzáadás”.
- **Elvárt:** a bejegyzés mentésre kerül, és a naplóban megjelenik a szűrésnek megfelelő listában.

### JRN-CRT-002 – Indulás időpontja kötelező

- **Prioritás:** High
- **Lépések:**
  1. Nyisd meg a modalt.
  2. Hagyd üresen az „Indulás időpontja” mezőt.
  3. Adj meg érvényes távolságot.
  4. „Hozzáadás”.
- **Elvárt:** hiba: `Az indulás megadása kötelező`.

### JRN-CRT-003 – Távolság kötelező + formátum

- **Prioritás:** High
- **Lépések:**
  1. Indulás legyen kitöltve.
  2. Távolság: üres.
  3. „Hozzáadás”.
  4. Ismételd meg távolságként: `abc`.
- **Elvárt:** mindkét esetben hiba: `A megadott távolság érvénytelen`.

### JRN-CRT-004 – Távolság tizedes vesszővel

- **Prioritás:** Medium
- **Lépések:**
  1. Távolság: `12,5`.
  2. „Hozzáadás”.
- **Elvárt:** mentés sikeres; a listában a távolság `12.5 km` (vagy ekvivalens float megjelenítés) formában jelenik meg.

### JRN-CRT-005 – Irányítószám validáció (4 számjegy)

- **Prioritás:** Medium
- **Lépések:**
  1. „Indulási hely” → irányítószám: `123`.
  2. „Hozzáadás”.
  3. Ismételd meg: `1234`.
- **Elvárt:**
  - `123`: hiba `A megadott irányítószám érvénytelen`.
  - `1234`: elfogadva.

### JRN-CRT-006 – Jármű hiányzik / nem található

- **Prioritás:** Medium
- **Megjegyzés:** a UI általában mindig küld járművet (select), de manipulációval tesztelhető.
- **Lépések (opció A):**
  1. DevTools → Network → módosítsd a POST body-t úgy, hogy `vehicle` üres legyen.
- **Elvárt:** hiba: `A gépjármű megadása kötelező`.

## BEJEGYZÉS MEGJELENÍTÉS

### JRN-VIEW-001 – Megjelenített dátum formátuma

- **Prioritás:** Low
- **Előfeltétel:** legyen legalább 1 bejegyzés
- **Lépések:**
  1. Nézd meg a bejegyzés „Indulás” mezőjét.
- **Elvárt:** formátum: `YYYY <hónapnév> DD, HH:MM` (pl. „2024 január 05, 14:58”).

### JRN-VIEW-002 – CO₂ kibocsátás számítás

- **Prioritás:** High
- **Előfeltétel:** ismert CO₂ ráta a járművön (pl. 100 g/km) és ismert távolság (pl. 10 km)
- **Lépések:**
  1. Állítsd a jármű CO₂ rátáját `100`-ra.
  2. Hozz létre bejegyzést `10` km távolsággal.
  3. Nézd meg a „Becsült CO2-kibocsátás” értéket.
- **Elvárt:** `10 * 100 = 1000 g` (kis kerekítési eltérés megengedett, a UI `round(...,2)`-t használ).

## SZŰRÉS (GET)

### JRN-FLT-001 – Szűrés jármű és év alapján

- **Prioritás:** High
- **Előfeltétel:** legyen több bejegyzés különböző években/járműveken
- **Lépések:**
  1. Válassz másik járművet.
  2. Válassz másik évet.
  3. „Szűrés”.
- **Elvárt:** csak az adott jármű + év bejegyzései jelennek meg.

### JRN-FLT-002 – Érvénytelen filter paraméterek kezelése

- **Prioritás:** Low
- **Lépések:**
  1. Kézzel írd át az URL-t pl. `?filterYear=1900&filterVehicle=NOPE`.
  2. Töltsd újra.
- **Elvárt:** a rendszer stabilan betölt; a szűrő visszaáll az első elérhető értékekre.

## TÖRLÉS (DELETE)

### JRN-DEL-001 – Bejegyzés törlése

- **Prioritás:** High
- **Előfeltétel:** legalább 1 bejegyzés
- **Lépések:**
  1. Kattints: „Bejegyzés törlése”.
  2. Confirm: OK.
- **Elvárt:** a bejegyzés eltűnik a listából.

### JRN-DEL-002 – Törlés megszakítása

- **Prioritás:** Low
- **Lépések:**
  1. „Bejegyzés törlése”.
  2. Confirm: Cancel.
- **Elvárt:** nincs változás.

### JRN-DEL-003 – Utolsó elem törlése után üres állapot + headingek takarítása

- **Prioritás:** Medium
- **Előfeltétel:** olyan szűrés, ahol csak 1 bejegyzés látszik
- **Lépések:**
  1. Töröld az utolsó bejegyzést.
- **Elvárt:** megjelenik az üres állapot („A napló üres…”), a korábbi év/hónap címek eltűnnek.

## MEGJEGYZÉS – Navigáció létrehozás után

### JRN-NAV-001 – Létrehozás utáni átirányítás és szűrés megőrzése

- **Prioritás:** Medium
- **Cél:** a létrehozás után a felhasználó maradjon (vagy térjen vissza) a naplóba és a szűrők álljanak a létrehozott bejegyzés évére/járművére.
- **Lépések:**
  1. Hozz létre új bejegyzést.
- **Elvárt:** `/journal` oldalra jutsz vissza, és a listában az új bejegyzés azonnal látható (a megfelelő filterekkel).
- **Megjegyzés:** ha más oldalra irányít át, vagy az új bejegyzés nem látszik, rögzíts bugot.

## API / JOGOSULTSÁG (NEGATÍV)

Ezek a tesztek DevTools (Network → „Edit and Resend”) vagy Postman segítségével futtathatók.

### JRN-API-001 – DELETE /journal: érvénytelen routeId

- **Prioritás:** Low
- **Lépések:**
  1. Küldj `DELETE /journal?routeId=abc`.
  2. Küldj `DELETE /journal` routeId nélkül.
- **Elvárt:** 400.

### JRN-API-002 – DELETE /journal: más felhasználó bejegyzése

- **Prioritás:** Medium
- **Előfeltétel:** legyen 2 user, mindkettőnek legalább 1 napló bejegyzés
- **Lépések:**
  1. Jelentkezz be U1-gyel.
  2. Küldj `DELETE /journal?routeId=<U2 route id>`.
- **Elvárt:** 401.

### JRN-API-003 – POST /journal: érvénytelen dátum formátum

- **Prioritás:** Low
- **Lépések:**
  1. Küldj `POST /journal` kérést úgy, hogy a `travel_start` mező nem a várt formátumú (pl. `2024/01/01 10:00`).
- **Elvárt:** hiba `Az indulás formátuma érvénytelen`.
