# Profil kezelés – Manuális Tesztesetek

**Modul:** Profil  
**Érintett végpontok:** `GET /profile`, `PATCH /profile`

---

## Validációs szabályok (referencia)

| Mező | Szabály |
|---|---|
| Felhasználónév | 1–50 karakter, nem tartalmazhat `@`-t, egyedinek kell lennie |
| Email | Érvényes email formátum, egyedinek kell lennie |
| Jelenlegi jelszó | Meg kell egyeznie a tárolt jelszóval |
| Új jelszó | Min. 8 karakter, legalább 1 nagybetű és 1 szám |
| Új jelszó megerősítése | Meg kell egyeznie az új jelszóval |

---

## Profil oldal megjelenítése

### TC-PROF-01 – Profil oldal megjelenítése bejelentkezett felhasználónak

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Navigálj a `/profile` oldalra |
| **Elvárt eredmény** | Az oldal betölt és megjeleníti a jelenlegi felhasználónevet és email-t az input mezőkben |
| **Súlyosság** | Major |

---

### TC-PROF-02 – Profil oldal nem elérhető bejelentkezés nélkül

| | |
|---|---|
| **Előfeltétel** | A felhasználó nincs bejelentkezve |
| **Lépések** | 1. Navigálj a `/profile` URL-re |
| **Elvárt eredmény** | Átirányítás a `/login` oldalra |
| **Súlyosság** | Critical |

---

## Email és felhasználónév módosítás

### TC-PROF-03 – Sikeres felhasználónév módosítás

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve, az `uj_nev` felhasználónév szabad |
| **Lépések** | 1. Navigálj a `/profile` oldalra <br> 2. Írd át a Felhasználónév mezőt: `uj_nev` <br> 3. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | HTTP 200 válasz, a felhasználónév frissül |
| **Súlyosság** | Major |

---

### TC-PROF-04 – Sikeres email módosítás

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve, az `ujmail@example.com` szabad |
| **Lépések** | 1. Navigálj a `/profile` oldalra <br> 2. Írd át az Email mező értékét: `ujmail@example.com` <br> 3. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | HTTP 200 válasz, az email frissül |
| **Súlyosság** | Major |

---

### TC-PROF-05 – Felhasználónév módosítás már foglalt névre

| | |
|---|---|
| **Előfeltétel** | Létezik egy másik fiók `teszt_user_2` névvel |
| **Lépések** | 1. Navigálj a `/profile` oldalra <br> 2. Felhasználónév mező: `teszt_user_2` <br> 3. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | HTTP 400 válasz, „A felhasználónév már foglalt" hibaüzenet |
| **Súlyosság** | Major |

---

### TC-PROF-06 – Email módosítás már használt email-re

| | |
|---|---|
| **Előfeltétel** | Egy másik fiókhoz tartozik `teszt2@example.com` |
| **Lépések** | 1. Navigálj a `/profile` oldalra <br> 2. Email mező: `teszt2@example.com` <br> 3. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | HTTP 400 válasz, „Az email cím már foglalt" hibaüzenet |
| **Súlyosság** | Major |

---

### TC-PROF-07 – Email módosítás érvénytelen formátumú email-re

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Navigálj a `/profile` oldalra <br> 2. Email mező: `nemvalid` <br> 3. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | HTTP 400 válasz, „Az email cím formátuma helytelen" hibaüzenet |
| **Súlyosság** | Major |

---

### TC-PROF-08 – Felhasználónév módosítás, ha ugyanaz az érték marad (nem küld kérést a szerverre)

| | |
|---|---|
| **Előfeltétel** | A felhasználó `teszt_user_1` névvel van bejelentkezve |
| **Lépések** | 1. Navigálj a `/profile` oldalra <br> 2. A Felhasználónév mező értéke ne változzon (`teszt_user_1`) <br> 3. Kattints a „Változtatások mentése" gombra |
| **Elvárt eredmény** | HTTP 200 válasz, nincs hibaüzenet (a szerver felismeri, hogy nem változott az érték) |
| **Súlyosság** | Minor |

---

## Jelszó módosítás

### TC-PROF-09 – Sikeres jelszó módosítás

| | |
|---|---|
| **Előfeltétel** | A felhasználó `Teszt1234` jelszóval van bejelentkezve |
| **Lépések** | 1. Navigálj a `/profile` oldalra <br> 2. Jelenlegi jelszó: `Teszt1234` <br> 3. Új jelszó: `UjJelszo5` <br> 4. Új jelszó megerősítése: `UjJelszo5` <br> 5. Kattints az „Új jelszó beállítása" gombra |
| **Elvárt eredmény** | HTTP 200 válasz, a jelszó megváltozik |
| **Súlyosság** | Critical |

---

### TC-PROF-10 – Jelszó módosítás helytelen jelenlegi jelszóval

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Navigálj a `/profile` oldalra <br> 2. Jelenlegi jelszó: `RossazJelszo1` <br> 3. Új jelszó: `UjJelszo5` <br> 4. Új jelszó megerősítése: `UjJelszo5` <br> 5. Kattints az „Új jelszó beállítása" gombra |
| **Elvárt eredmény** | HTTP 400 válasz, „A megadott jelszó helytelen" hibaüzenet |
| **Súlyosság** | Critical |

---

### TC-PROF-11 – Jelszó módosítás nem egyező új jelszavakkal

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Navigálj a `/profile` oldalra <br> 2. Jelenlegi jelszó: `Teszt1234` <br> 3. Új jelszó: `UjJelszo5` <br> 4. Új jelszó megerősítése: `MasJelszo6` <br> 5. Kattints az „Új jelszó beállítása" gombra |
| **Elvárt eredmény** | HTTP 400 válasz, „A jelszavak nem egyeznek" hibaüzenet |
| **Súlyosság** | Major |

---

### TC-PROF-12 – Jelszó módosítás 7 karakteres új jelszóval (határérték)

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Navigálj a `/profile` oldalra <br> 2. Jelenlegi jelszó: `Teszt1234` <br> 3. Új jelszó: `Abc123X` (7 karakter) <br> 4. Új jelszó megerősítése: `Abc123X` <br> 5. Kattints az „Új jelszó beállítása" gombra |
| **Elvárt eredmény** | HTTP 400 válasz, jelszó hosszára vonatkozó hibaüzenet |
| **Súlyosság** | Major |

---

### TC-PROF-13 – Jelszó módosítás 8 karakteres új jelszóval (határérték)

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Navigálj a `/profile` oldalra <br> 2. Jelenlegi jelszó: `Teszt1234` <br> 3. Új jelszó: `Abc12345` (8 karakter, van nagybetű és szám) <br> 4. Új jelszó megerősítése: `Abc12345` <br> 5. Kattints az „Új jelszó beállítása" gombra |
| **Elvárt eredmény** | HTTP 200 válasz, sikeres jelszócsere |
| **Súlyosság** | Major |

---

### TC-PROF-14 – Jelszó módosítás szám nélküli új jelszóval

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Navigálj a `/profile` oldalra <br> 2. Jelenlegi jelszó: `Teszt1234` <br> 3. Új jelszó: `Abcdefgh` (nincs szám) <br> 4. Kattints az „Új jelszó beállítása" gombra |
| **Elvárt eredmény** | HTTP 400 válasz, jelszó formátumra vonatkozó hibaüzenet |
| **Súlyosság** | Major |

---

### TC-PROF-15 – Jelszó módosítás nagybetű nélküli új jelszóval

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Navigálj a `/profile` oldalra <br> 2. Jelenlegi jelszó: `Teszt1234` <br> 3. Új jelszó: `abc12345` (nincs nagybetű) <br> 4. Kattints az „Új jelszó beállítása" gombra |
| **Elvárt eredmény** | HTTP 400 válasz, jelszó formátumra vonatkozó hibaüzenet |
| **Súlyosság** | Major |
