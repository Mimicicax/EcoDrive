# Statisztika tesztek

## Előfeltétel

- Bejelentkezve **normál felhasználóként**.
- Legyenek naplózott utak (különben az oldal üres állapotot mutat).

## STATISZTIKA OLDAL

### STS-UI-001 – Üres állapot, ha nincs idei adat

- **Prioritás:** High
- **Előfeltétel:** nincs út mentve az aktuális évben
- **Lépések:**
  1. Nyisd meg: `/statistics`.
- **Elvárt:** üres állapot: „Nincsenek adatok erre az évre”.

### STS-UI-002 – Statisztika megjelenik, ha van idei adat

- **Prioritás:** High
- **Előfeltétel:** legyen legalább 1 út mentve az aktuális évben
- **Lépések:**
  1. `/statistics`.
- **Elvárt:**
  - Megjelenik 4 kártya (Havi, Éves, Megoszlás, Becsült kibocsátás).
  - A grafikonok renderelődnek (nem üresek, nem hibáznak a konzolban).

## SZÁMÍTÁSOK HELYESSÉGE (KONTROLL ADATOKKAL)

### STS-CALC-001 – Havi összkibocsátás és távolság

- **Prioritás:** High
- **Előfeltétel:** ismert, egyszerű adatok
- **Javasolt setup:**
  - Jármű CO₂ ráta: `100 g/km`
  - Hozz létre az aktuális hónapban 2 utat: `10 km` és `5 km`
- **Lépések:**
  1. Nyisd meg: `/statistics`.
  2. A „Havi CO₂-kibocsátás” kártyán olvasd le az értékeket.
- **Elvárt:**
  - Havi kibocsátás: `(10*100 + 5*100) g = 1500 g = 1.5 kg`.
  - Havi megtett távolság: `15 km`.
  - Kerekítés max. 2 tizedes.

### STS-CALC-002 – Éves összkibocsátás és távolság

- **Prioritás:** High
- **Előfeltétel:** legyen több út az év különböző hónapjaiban
- **Lépések:**
  1. `/statistics`.
  2. Olvasd le az „Éves CO₂-kibocsátás” kártya értékeit.
- **Elvárt:**
  - Éves kibocsátás: az adott év összes útjának kibocsátás összege.
  - Éves távolság: az adott év összes útjának távolság összege.

### STS-CALC-003 – Előző hónap összehasonlítás (nyíl + %)

- **Prioritás:** Medium
- **Előfeltétel:** legyen út az előző hónapban és a jelenlegi hónapban is
- **Lépések:**
  1. `/statistics`.
  2. A „Ebben a hónapban” sorban nézd meg a zárójelben lévő %-ot és nyilat.
- **Elvárt:**
  - Ha a jelenlegi havi kibocsátás kisebb, akkor `-X%` és lefelé nyíl.
  - Ha nagyobb, `+X%` és felfelé nyíl.

### STS-CALC-004 – Tavaly ebben a hónapban sor megjelenése

- **Prioritás:** Low
- **Előfeltétel:** legyen legalább 1 út a **tavalyi évben** az **aktuális hónapban**
- **Lépések:**
  1. `/statistics`.
- **Elvárt:** megjelenik a „Tavaly ebben a hónapban” blokk és %-os eltérés.

## GRAFIKONOK / MEGOSZLÁS

### STS-CHART-001 – Kibocsátás megoszlás járművenként

- **Prioritás:** Medium
- **Előfeltétel:** legalább 2 jármű + mindkettővel legyenek idei utak
- **Lépések:**
  1. `/statistics`.
  2. A „Kibocsátás megoszlás” kártyán ellenőrizd a label-eket.
- **Elvárt:**
  - Éves megoszlás chart: minden olyan jármű rendszáma szerepel, amivel idén volt út.
  - Havi megoszlás chart: csak azok a járművek szerepelnek, amikkel ebben a hónapban volt út.

## EXTRAPOLÁCIÓ

### STS-EXT-001 – Becsült kibocsátás grafikonok nem adnak NaN/Infinity értéket

- **Prioritás:** Medium
- **Előfeltétel:** legyen legalább 1 idei út
- **Lépések:**
  1. `/statistics`.
  2. Nyisd meg a böngésző konzolt.
  3. Ellenőrizd, hogy nincs Chart.js hiba.
  4. Nézd meg a két extrapolációs chart címében a „Becsült … összkibocsátás” értéket.
- **Elvárt:** értelmes szám jelenik meg, nincs grafikon render hiba.

## JOGOSULTSÁG

### STS-AUTH-001 – Admin nem érheti el a statisztikát

- **Prioritás:** High
- **Előfeltétel:** bejelentkezve adminnal
- **Lépések:**
  1. Nyisd meg: `/statistics`.
- **Elvárt:** átirányítás `/admin`.
