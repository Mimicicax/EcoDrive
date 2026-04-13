# Járműkezelés – Manuális Tesztesetek

**Modul:** Járművek (Autóim)  
**Érintett végpontok:** `GET /vehicles`, `POST /vehicles`, `PUT /vehicles`, `DELETE /vehicles`

---

## Validációs szabályok (referencia)

| Mező | Szabály |
|---|---|
| Márka | Kötelező, nem üres, alfanumerikus + kötőjel + szóköz |
| Modell | Kötelező, nem üres, alfanumerikus + kötőjel + szóköz |
| Rendszám | Kötelező, egyedi, elfogadott formátumok: `ABC-123`, `AB-CD-123`, `ABC1234`, `ABCD123`, `ABCDE12`, `ABCDEF1` |
| Évjárat | Kötelező, egész szám, 1900 – aktuális év (2026) |
| Fogyasztás (L/100 km) | Opcionális, pozitív tizedes tört |
| CO₂ kibocsátás (g/km) | Opcionális, pozitív tizedes tört |

---

## Járművek listázása

### TC-VEH-01 – Járművek oldal megjelenítése, ha nincs jármű

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve, nincs mentett járműve |
| **Lépések** | 1. Navigálj a `/vehicles` oldalra |
| **Elvárt eredmény** | Az oldal betölt, a jármű lista üres, megjelenik az „Új autó" gomb |
| **Súlyosság** | Major |

---

### TC-VEH-02 – Járművek oldal megjelenítése meglévő járművekkel

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve, van legalább 1 mentett járműve |
| **Lépések** | 1. Navigálj a `/vehicles` oldalra |
| **Elvárt eredmény** | A járművek kártyaként jelennek meg a rendszámukat és adataikat megjelenítve |
| **Súlyosság** | Major |

---

### TC-VEH-03 – Járművek oldal szűrés évjárat szerint

| | |
|---|---|
| **Előfeltétel** | Legalább 2 jármű van mentve különböző évjárattal (pl. 2020 és 2023) |
| **Lépések** | 1. Navigálj a `/vehicles` oldalra <br> 2. A „Szűrés" legördülő menüből válaszd: `2020` |
| **Elvárt eredmény** | Csak a 2020-as évjáratú jármű(k) jelennek meg |
| **Súlyosság** | Minor |

---

## Jármű létrehozása

### TC-VEH-04 – Sikeres jármű létrehozása minden mezővel

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve, az `AB-CD-001` rendszám szabad |
| **Lépések** | 1. Kattints az „Új autó" gombra <br> 2. Márka: `Teszla`, Modell: `Oktávia`, Rendszám: `AB-CD-001`, Évjárat: `2022`, Fogyasztás: `6.5`, CO₂: `120` <br> 3. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | Az új jármű megjelenik a listán |
| **Súlyosság** | Critical |

---

### TC-VEH-05 – Sikeres jármű létrehozása fogyasztás és CO₂ nélkül (opcionális mezők)

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Kattints az „Új autó" gombra <br> 2. Márka: `Mazda`, Modell: `3`, Rendszám: `EF-GH-002`, Évjárat: `2019` <br> 3. Fogyasztás és CO₂ mezők üresen maradnak <br> 4. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | A jármű létrejön, az alapértelmezett CO₂-érték (108.2 g/km) kerül alkalmazásra |
| **Súlyosság** | Major |

---

### TC-VEH-06 – Jármű létrehozása üres márka mezővel

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Kattints az „Új autó" gombra <br> 2. Hagyj üresen a Márka mezőt, a többi mezőt töltsd ki érvényes adatokkal <br> 3. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | „A márkanév megadása kötelező" hibaüzenet jelenik meg |
| **Súlyosság** | Major |

---

### TC-VEH-07 – Jármű létrehozása üres modell mezővel

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Kattints az „Új autó" gombra <br> 2. Hagyj üresen a Modell mezőt, a többi mezőt töltsd ki érvényes adatokkal <br> 3. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | „A modell megadása kötelező" hibaüzenet jelenik meg |
| **Súlyosság** | Major |

---

### TC-VEH-08 – Jármű létrehozása érvénytelen rendszám formátummal

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Kattints az „Új autó" gombra <br> 2. Rendszám: `NEMJÓ123` <br> 3. A többi mezőt töltsd ki érvényes adatokkal <br> 4. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | „A rendszám formátuma nem megfelelő" hibaüzenet jelenik meg |
| **Súlyosság** | Major |

---

### TC-VEH-09 – Jármű létrehozása már foglalt rendszámmal

| | |
|---|---|
| **Előfeltétel** | Már létezik jármű `AB-CD-001` rendszámmal (bármely felhasználónál) |
| **Lépések** | 1. Kattints az „Új autó" gombra <br> 2. Rendszám: `AB-CD-001` <br> 3. A többi mezőt töltsd ki érvényes adatokkal <br> 4. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | „A rendszám már foglalt" hibaüzenet jelenik meg |
| **Súlyosság** | Major |

---

### TC-VEH-10 – Jármű létrehozása 1899-es évjárattal (határérték alatt)

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Kattints az „Új autó" gombra <br> 2. Évjárat: `1899` <br> 3. A többi mezőt töltsd ki érvényes adatokkal <br> 4. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | „Az évjárat érvénytelen" hibaüzenet jelenik meg |
| **Súlyosság** | Minor |

---

### TC-VEH-11 – Jármű létrehozása 1900-as évjárattal (határérték)

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Kattints az „Új autó" gombra <br> 2. Évjárat: `1900` <br> 3. A többi mezőt töltsd ki érvényes adatokkal <br> 4. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | Sikeres jármű létrehozás |
| **Súlyosság** | Minor |

---

### TC-VEH-12 – Jármű létrehozása aktuális évnél (2026) nagyobb évjárattal (határérték felett)

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Kattints az „Új autó" gombra <br> 2. Évjárat: `2027` <br> 3. A többi mezőt töltsd ki érvényes adatokkal <br> 4. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | „Az évjárat érvénytelen" hibaüzenet jelenik meg |
| **Súlyosság** | Minor |

---

### TC-VEH-13 – Jármű létrehozása negatív fogyasztás értékkel

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Kattints az „Új autó" gombra <br> 2. Fogyasztás: `-5` <br> 3. A többi mezőt töltsd ki érvényes adatokkal <br> 4. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | „A megadott fogyasztás formátuma nem megfelelő" hibaüzenet jelenik meg |
| **Súlyosság** | Minor |

---

### TC-VEH-14 – Rendszám formátum: `ABC-123` (érvényes)

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve, a rendszám szabad |
| **Lépések** | 1. Kattints az „Új autó" gombra <br> 2. Rendszám: `ABC-123` <br> 3. A többi mezőt töltsd ki érvényes adatokkal |
| **Elvárt eredmény** | Sikeres jármű létrehozás |
| **Súlyosság** | Minor |

---

### TC-VEH-15 – Rendszám formátum: `ABCDEF1` (érvényes, 6 betű + 1 szám)

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve, a rendszám szabad |
| **Lépések** | 1. Kattints az „Új autó" gombra <br> 2. Rendszám: `ABCDEF1` <br> 3. A többi mezőt töltsd ki érvényes adatokkal |
| **Elvárt eredmény** | Sikeres jármű létrehozás |
| **Súlyosság** | Minor |

---

## Jármű szerkesztése

### TC-VEH-16 – Sikeres jármű adatok módosítása

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve, van legalább 1 járműve |
| **Lépések** | 1. Navigálj a `/vehicles` oldalra <br> 2. Módosítsd az egyik jármű Márka mezőjét: `Honda` <br> 3. Kattints az adott jármű „Változtatások mentése" gombjára |
| **Elvárt eredmény** | HTTP 200 válasz, a jármű adatai frissülnek az oldalon |
| **Súlyosság** | Major |

---

### TC-VEH-17 – Jármű szerkesztése üres márka mezővel

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve, van legalább 1 járműve |
| **Lépések** | 1. Navigálj a `/vehicles` oldalra <br> 2. Töröld ki az egyik jármű Márka mezőjét <br> 3. Kattints az adott jármű „Változtatások mentése" gombjára |
| **Elvárt eredmény** | HTTP 400 válasz, „A márkanév megadása kötelező" hibaüzenet |
| **Súlyosság** | Major |

---

### TC-VEH-18 – Jármű szerkesztése: rendszám módosítása szabad rendszámra

| | |
|---|---|
| **Előfeltétel** | Létezik egy jármű `AB-CD-001` rendszámmal, a `ZZ-ZZ-999` rendszám szabad |
| **Lépések** | 1. Navigálj a `/vehicles` oldalra <br> 2. Módosítsd az `AB-CD-001` rendszámú jármű rendszámát `ZZ-ZZ-999`-re <br> 3. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | HTTP 200 válasz, a rendszám frissül |
| **Súlyosság** | Minor |

---

### TC-VEH-19 – Jármű szerkesztése: rendszám módosítása foglalt rendszámra

| | |
|---|---|
| **Előfeltétel** | Két jármű létezik: `AB-CD-001` és `EF-GH-002` |
| **Lépések** | 1. Navigálj a `/vehicles` oldalra <br> 2. Módosítsd az `AB-CD-001` jármű rendszámát `EF-GH-002`-re <br> 3. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | HTTP 400 válasz, „A rendszám már foglalt" hibaüzenet |
| **Súlyosság** | Major |

---

## Jármű törlése

### TC-VEH-20 – Sikeres jármű törlése

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve, van legalább 1 járműve |
| **Lépések** | 1. Navigálj a `/vehicles` oldalra <br> 2. Kattints az egyik jármű „Autó törlése" gombjára <br> 3. Erősítsd meg a törlést (ha van megerősítő dialógus) |
| **Elvárt eredmény** | HTTP 200 válasz, a jármű eltűnik a listáról |
| **Súlyosság** | Critical |

---

### TC-VEH-21 – Más felhasználó járművének törlési kísérlete

| | |
|---|---|
| **Előfeltétel** | Két különböző felhasználó (`user1`, `user2`) van bejelentkezve különböző böngészőkben, `user2`-nek van egy járműve |
| **Lépések** | 1. `user1` megpróbál közvetlenül hívni: `DELETE /vehicles?vehicleId=[user2 jármű ID-je]` (pl. Postmannel vagy fejlesztői eszközzel) |
| **Elvárt eredmény** | HTTP 401 válasz, a jármű nem törlődik |
| **Súlyosság** | Critical |

---

### TC-VEH-22 – Nem létező jármű törlési kísérlete

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Közvetlen DELETE kérés: `DELETE /vehicles?vehicleId=99999` |
| **Elvárt eredmény** | HTTP 404 válasz |
| **Súlyosság** | Minor |

---

### TC-VEH-23 – Jármű törlése törli a hozzá tartozó napló bejegyzéseket is

| | |
|---|---|
| **Előfeltétel** | A felhasználónak van 1 járműve és ahhoz legalább 1 napló bejegyzése |
| **Lépések** | 1. Navigálj a `/journal` oldalra, jegyezd fel a bejegyzések számát <br> 2. Navigálj a `/vehicles` oldalra <br> 3. Töröld a járművet |
| **Elvárt eredmény** | A jármű törlődik, a napló bejegyzések is törlődnek (az adatbázisban CASCADE törlés) |
| **Súlyosság** | Major |
