# EcoDrive – Tesztterv (Test Plan)

**Verzió:** 1.3
**Dátum:** 2026-03-02
**Projekt:** EcoDrive – CO₂-kibocsátás követő webalkalmazás
**Tesztelés típusa:** Manuális feketedoboz (Black-box) tesztelés

---

## 1. Hatókör (Scope)

A tesztelés az EcoDrive webalkalmazás következő funkcióit fedi le:

| Modul                                                            | Fájl                  |
| ---------------------------------------------------------------- | ---------------------- |
| Autentikáció (regisztráció, bejelentkezés, kijelentkezés)  | `01_autentikacio.md` |
| Profil kezelés (felhasználónév, email, jelszó módosítás) | `02_profil.md`       |
| Járműkezelés (létrehozás, szerkesztés, törlés)           | `03_jarmuvek.md`     |
| Utazási napló (bejegyzés létrehozás, törlés, szűrés)    | `04_naplo.md`        |
| Statisztika megjelenítés                                       | `05_statisztika.md`  |
| Admin panel (felhasználó keresés, szerkesztés, törlés)     | `06_admin.md`        |
| Biztonság és nem-funkcionális követelmények                 | `07_biztonsag.md`    |

---

## 2. Tesztelési környezet

| Elem            | Érték                                         |
| --------------- | ----------------------------------------------- |
| Alkalmazás URL | `http://localhost:8888`                       |
| Böngésző     | Google Chrome (legújabb), Firefox (legújabb)  |
| OS              | Windows 10/11                                   |
| Adatbázis      | MySQL 9.5 (Docker konténer)                    |
| Indítás       | `docker compose -f compose.dev.yml up`        |
| Admin fiók     | felhasználónév:`admin`, jelszó: `admin` |

---

## 3. Tesztelési módszer

- **Ekvivalencia particionálás:** Az érvényes és érvénytelen adatcsoportokból egy-egy reprezentatív értéket tesztelünk.
- **Határérték elemzés:** A validációs határok közelében (pl. min 8 karakter jelszó → 7, 8, 9 karakter) tesztelünk.
- **Negatív tesztelés:** Szándékosan hibás adatokat adunk meg, hogy ellenőrizzük a hibaüzenetek megjelenését.
- **Felfedező tesztelés:** A modulok közötti átmeneteket és a bejelentkezési állapothoz kötött hozzáférés-vezérlést ellenőrizzük.

---

## 4. Teszteset azonosítók

A tesztesetek azonosítója az alábbi konvenciót követi:

```
TC-[MODUL]-[SORSZÁM]
```

Példák:

- `TC-AUTH-01` → Autentikáció, 1. teszteset
- `TC-VEH-05` → Járműkezelés, 5. teszteset

---

## 5. Hibajegy súlyossági szintek

| Szint              | Leírás                                                   |
| ------------------ | ---------------------------------------------------------- |
| **Critical** | Az alkalmazás összeomlik, adatvesztés, biztonsági rés |
| **Major**    | Fő funkció nem működik (pl. nem lehet bejelentkezni)   |
| **Minor**    | Mellékfunkció hibás, de van kerülő megoldás          |
| **Trivial**  | UI eltérés, elírás, kozmetikai hiba                    |

---

## 6. Belépési és kilépési kritériumok

**Belépési feltételek (tesztelés megkezdéséhez):**

- Az alkalmazás Docker konténerekkel elindítható
- Az adatbázis inicializálva van (`database.sql` lefutott)
- Az admin fiók (`admin`/`admin`) elérhető

**Kilépési feltételek (tesztelés befejezéséhez):**

- Minden Critical és Major súlyosságú hibajegy javítva van
- Az összes teszteset le van futtatva

---

## 7. Tesztadatok

A tesztek futtatásához az alábbi adatokat javasolt előkészíteni:

| Adat                  | Érték                                                   |
| --------------------- | --------------------------------------------------------- |
| Teszt felhasználó 1 | `teszt_user_1` / `teszt1@example.com` / `Teszt1234` |
| Teszt felhasználó 2 | `teszt_user_2` / `teszt2@example.com` / `Teszt1234` |
| Teszt rendszám 1     | `AB-CD-001`                                             |
| Teszt rendszám 2     | `EF-GH-002`                                             |
