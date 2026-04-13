# Autentikáció – Manuális Tesztesetek

**Modul:** Autentikáció  
**Érintett végpontok:** `GET /login`, `POST /login`, `GET /register`, `POST /register`, `GET /logout`

---

## Validációs szabályok (referencia)

| Mező | Szabály |
|---|---|
| Felhasználónév | 1–50 karakter, nem tartalmazhat `@` karaktert, egyedinek kell lennie |
| Email | Érvényes email formátum, egyedinek kell lennie |
| Jelszó | Min. 8 karakter, legalább 1 nagybetű és 1 szám |
| Jelszó megerősítés | Meg kell egyeznie a jelszóval |

---

## Regisztráció

### TC-AUTH-01 – Sikeres regisztráció érvényes adatokkal

| | |
|---|---|
| **Előfeltétel** | A felhasználó nincs bejelentkezve, az email és felhasználónév szabad |
| **Lépések** | 1. Navigálj a `/register` oldalra <br> 2. Töltsd ki: Email: `teszt1@example.com`, Felhasználónév: `teszt_user_1`, Jelszó: `Teszt1234`, Jelszó megerősítése: `Teszt1234` <br> 3. Kattints a „Regisztráció" gombra |
| **Elvárt eredmény** | Átirányítás a `/login` oldalra |
| **Súlyosság** | Critical |

---

### TC-AUTH-02 – Regisztráció már foglalt felhasználónévvel

| | |
|---|---|
| **Előfeltétel** | Létezik egy fiók `teszt_user_1` felhasználónévvel |
| **Lépések** | 1. Navigálj a `/register` oldalra <br> 2. Töltsd ki: Email: `masik@example.com`, Felhasználónév: `teszt_user_1`, Jelszó: `Teszt1234`, Jelszó megerősítése: `Teszt1234` <br> 3. Kattints a „Regisztráció" gombra |
| **Elvárt eredmény** | Az oldal újratöltődik, „A felhasználónév már foglalt" hibaüzenet jelenik meg |
| **Súlyosság** | Major |

---

### TC-AUTH-03 – Regisztráció már foglalt email címmel

| | |
|---|---|
| **Előfeltétel** | Létezik egy fiók `teszt1@example.com` email-lel |
| **Lépések** | 1. Navigálj a `/register` oldalra <br> 2. Töltsd ki: Email: `teszt1@example.com`, Felhasználónév: `uj_user`, Jelszó: `Teszt1234`, Jelszó megerősítése: `Teszt1234` <br> 3. Kattints a „Regisztráció" gombra |
| **Elvárt eredmény** | „Az email cím már foglalt" hibaüzenet jelenik meg |
| **Súlyosság** | Major |

---

### TC-AUTH-04 – Regisztráció érvénytelen email formátummal

| | |
|---|---|
| **Előfeltétel** | — |
| **Lépések** | 1. Navigálj a `/register` oldalra <br> 2. Email mezőbe írd: `nemvalidemail` <br> 3. Töltsd ki a többi mezőt érvényes adatokkal <br> 4. Kattints a „Regisztráció" gombra |
| **Elvárt eredmény** | „Az email cím formátuma helytelen" hibaüzenet jelenik meg |
| **Súlyosság** | Major |

---

### TC-AUTH-05 – Regisztráció túl rövid jelszóval (határérték: 7 karakter)

| | |
|---|---|
| **Előfeltétel** | — |
| **Lépések** | 1. Navigálj a `/register` oldalra <br> 2. Jelszó mezőbe írd: `Abc123X` (7 karakter) <br> 3. Töltsd ki a többi mezőt érvényes adatokkal <br> 4. Kattints a „Regisztráció" gombra |
| **Elvárt eredmény** | „A jelszónak legalább 8 karakterből kell állnia..." hibaüzenet jelenik meg |
| **Súlyosság** | Major |

---

### TC-AUTH-06 – Regisztráció pontosan 8 karakteres jelszóval (határérték)

| | |
|---|---|
| **Előfeltétel** | — |
| **Lépések** | 1. Navigálj a `/register` oldalra <br> 2. Jelszó: `Abc1234x` (8 karakter, van nagybetű és szám) <br> 3. Töltsd ki a többi mezőt érvényes adatokkal <br> 4. Kattints a „Regisztráció" gombra |
| **Elvárt eredmény** | Sikeres regisztráció, átirányítás `/login` oldalra |
| **Súlyosság** | Major |

---

### TC-AUTH-07 – Regisztráció jelszó nagybetű nélkül

| | |
|---|---|
| **Előfeltétel** | — |
| **Lépések** | 1. Navigálj a `/register` oldalra <br> 2. Jelszó: `abc12345` (nincs nagybetű) <br> 3. Töltsd ki a többi mezőt érvényes adatokkal <br> 4. Kattints a „Regisztráció" gombra |
| **Elvárt eredmény** | „A jelszónak legalább 8 karakterből kell állnia és tartalmaznia kell legalább egy nagybetűt és számot" hibaüzenet jelenik meg |
| **Súlyosság** | Major |

---

### TC-AUTH-08 – Regisztráció jelszó szám nélkül

| | |
|---|---|
| **Előfeltétel** | — |
| **Lépések** | 1. Navigálj a `/register` oldalra <br> 2. Jelszó: `Abcdefgh` (nincs szám) <br> 3. Töltsd ki a többi mezőt érvényes adatokkal <br> 4. Kattints a „Regisztráció" gombra |
| **Elvárt eredmény** | „A jelszónak legalább 8 karakterből kell állnia és tartalmaznia kell legalább egy nagybetűt és számot" hibaüzenet jelenik meg |
| **Súlyosság** | Major |

---

### TC-AUTH-09 – Regisztráció nem egyező jelszavakkal

| | |
|---|---|
| **Előfeltétel** | — |
| **Lépések** | 1. Navigálj a `/register` oldalra <br> 2. Jelszó: `Teszt1234`, Jelszó megerősítése: `Teszt5678` <br> 3. Töltsd ki a többi mezőt érvényes adatokkal <br> 4. Kattints a „Regisztráció" gombra |
| **Elvárt eredmény** | „A jelszavak nem egyeznek" hibaüzenet jelenik meg |
| **Súlyosság** | Major |

---

### TC-AUTH-10 – Regisztráció üres felhasználónévvel

| | |
|---|---|
| **Előfeltétel** | — |
| **Lépések** | 1. Navigálj a `/register` oldalra <br> 2. Hagyj üresen a Felhasználónév mezőt <br> 3. Töltsd ki a többi mezőt érvényes adatokkal <br> 4. Kattints a „Regisztráció" gombra |
| **Elvárt eredmény** | „A felhasználónév minimum 1, maximum 50 karakterből állhat" hibaüzenet jelenik meg |
| **Súlyosság** | Major |

---

### TC-AUTH-11 – Regisztráció 50 karakteres felhasználónévvel (határérték)

| | |
|---|---|
| **Előfeltétel** | — |
| **Lépések** | 1. Navigálj a `/register` oldalra <br> 2. Felhasználónév: `aaaaaaaaaabbbbbbbbbbccccccccccddddddddddeeeeeeeee1` (50 karakter) <br> 3. Töltsd ki a többi mezőt érvényes adatokkal <br> 4. Kattints a „Regisztráció" gombra |
| **Elvárt eredmény** | Sikeres regisztráció |
| **Súlyosság** | Minor |

---

### TC-AUTH-12 – Regisztráció 51 karakteres felhasználónévvel (határérték)

| | |
|---|---|
| **Előfeltétel** | — |
| **Lépések** | 1. Navigálj a `/register` oldalra <br> 2. Felhasználónév: `aaaaaaaaaabbbbbbbbbbccccccccccddddddddddeeeeeeeee12` (51 karakter) <br> 3. Töltsd ki a többi mezőt érvényes adatokkal <br> 4. Kattints a „Regisztráció" gombra |
| **Elvárt eredmény** | „A felhasználónév minimum 1, maximum 50 karakterből állhat" hibaüzenet jelenik meg |
| **Súlyosság** | Minor |

---

### TC-AUTH-13 – Regisztráció `@` karaktert tartalmazó felhasználónévvel

| | |
|---|---|
| **Előfeltétel** | — |
| **Lépések** | 1. Navigálj a `/register` oldalra <br> 2. Felhasználónév: `teszt@user` <br> 3. Töltsd ki a többi mezőt érvényes adatokkal <br> 4. Kattints a „Regisztráció" gombra |
| **Elvárt eredmény** | „Érvénytelen karakter a felhasználónévben" hibaüzenet jelenik meg |
| **Súlyosság** | Minor |

---

### TC-AUTH-14 – „Már van fiókom" gomb a regisztrációs oldalon

| | |
|---|---|
| **Előfeltétel** | A felhasználó a `/register` oldalon van |
| **Lépések** | 1. Kattints a „Már van fiókom" gombra |
| **Elvárt eredmény** | Átirányítás a `/login` oldalra |
| **Súlyosság** | Minor |

---

## Bejelentkezés

### TC-AUTH-15 – Sikeres bejelentkezés felhasználónévvel

| | |
|---|---|
| **Előfeltétel** | Létezik `teszt_user_1` fiók |
| **Lépések** | 1. Navigálj a `/login` oldalra <br> 2. Felhasználónév: `teszt_user_1`, Jelszó: `Teszt1234` <br> 3. Kattints a „Bejelentkezés" gombra |
| **Elvárt eredmény** | Átirányítás a `/` (főoldal/járművek) oldalra, a felhasználó be van jelentkezve |
| **Súlyosság** | Critical |

---

### TC-AUTH-16 – Sikeres bejelentkezés email címmel

| | |
|---|---|
| **Előfeltétel** | Létezik `teszt1@example.com` fiók |
| **Lépések** | 1. Navigálj a `/login` oldalra <br> 2. Felhasználónév mező: `teszt1@example.com`, Jelszó: `Teszt1234` <br> 3. Kattints a „Bejelentkezés" gombra |
| **Elvárt eredmény** | Sikeres bejelentkezés, átirányítás a főoldalra |
| **Súlyosság** | Critical |

---

### TC-AUTH-17 – Sikertelen bejelentkezés nem létező felhasználónévvel

| | |
|---|---|
| **Előfeltétel** | — |
| **Lépések** | 1. Navigálj a `/login` oldalra <br> 2. Felhasználónév: `nemletezik`, Jelszó: `Teszt1234` <br> 3. Kattints a „Bejelentkezés" gombra |
| **Elvárt eredmény** | „A felhasználónév vagy jelszó helytelen" hibaüzenet jelenik meg, az oldal nem irányít át |
| **Súlyosság** | Major |

---

### TC-AUTH-18 – Sikertelen bejelentkezés helytelen jelszóval

| | |
|---|---|
| **Előfeltétel** | Létezik `teszt_user_1` fiók |
| **Lépések** | 1. Navigálj a `/login` oldalra <br> 2. Felhasználónév: `teszt_user_1`, Jelszó: `RossazJelszo1` <br> 3. Kattints a „Bejelentkezés" gombra |
| **Elvárt eredmény** | „A felhasználónév vagy jelszó helytelen" hibaüzenet, a felhasználónév mező megőrzi a beírt értéket |
| **Súlyosság** | Major |

---

### TC-AUTH-19 – Bejelentkezési oldal visszatölt a felhasználónevet hiba esetén

| | |
|---|---|
| **Előfeltétel** | — |
| **Lépések** | 1. Navigálj a `/login` oldalra <br> 2. Felhasználónév: `teszt_user_1`, Jelszó: `RossazJelszo1` <br> 3. Kattints a „Bejelentkezés" gombra |
| **Elvárt eredmény** | A felhasználónév mezőben „teszt_user_1" marad (nem ürül ki) |
| **Súlyosság** | Minor |

---

### TC-AUTH-20 – Bejelentkezett felhasználó nem éri el a login oldalt

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Navigálj a `/login` URL-re |
| **Elvárt eredmény** | Automatikus átirányítás a főoldalra (`/`) |
| **Súlyosság** | Minor |

---

### TC-AUTH-21 – Bejelentkezett felhasználó nem éri el a regisztrációs oldalt

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Navigálj a `/register` URL-re |
| **Elvárt eredmény** | Automatikus átirányítás a főoldalra (`/`) |
| **Súlyosság** | Minor |

---

### TC-AUTH-22 – „Még nincs fiókom" gomb a bejelentkezési oldalon

| | |
|---|---|
| **Előfeltétel** | A felhasználó a `/login` oldalon van |
| **Lépések** | 1. Kattints a „Még nincs fiókom" gombra |
| **Elvárt eredmény** | Átirányítás a `/register` oldalra |
| **Súlyosság** | Minor |

---

## Kijelentkezés

### TC-AUTH-23 – Sikeres kijelentkezés

| | |
|---|---|
| **Előfeltétel** | A felhasználó be van jelentkezve |
| **Lépések** | 1. Navigálj a `/logout` URL-re |
| **Elvárt eredmény** | A session törlődik, átirányítás a `/login` oldalra, a felhasználó ki van jelentkezve |
| **Súlyosság** | Critical |

---

### TC-AUTH-24 – Kijelentkezés után védett oldalak nem érhetők el

| | |
|---|---|
| **Előfeltétel** | A felhasználó épp kijelentkezett |
| **Lépések** | 1. Navigálj a `/vehicles` URL-re |
| **Elvárt eredmény** | Átirányítás a `/login` oldalra |
| **Súlyosság** | Critical |

---

### TC-AUTH-25 – Nem bejelentkezett felhasználó kijelentkezési link hívása

| | |
|---|---|
| **Előfeltétel** | A felhasználó nincs bejelentkezve |
| **Lépések** | 1. Navigálj a `/logout` URL-re |
| **Elvárt eredmény** | Átirányítás a főoldalra (nem dob hibát) |
| **Súlyosság** | Minor |

---

## Hozzáférés-vezérlés

### TC-AUTH-26 – Bejelentkezés nélkül védett oldal elérési kísérlete

| | |
|---|---|
| **Előfeltétel** | A felhasználó nincs bejelentkezve |
| **Lépések** | 1. Navigálj közvetlenül a `/journal` URL-re |
| **Elvárt eredmény** | Átirányítás a `/login` oldalra |
| **Súlyosság** | Critical |

---

### TC-AUTH-27 – Bejelentkezés nélkül statistics oldal elérési kísérlete

| | |
|---|---|
| **Előfeltétel** | A felhasználó nincs bejelentkezve |
| **Lépések** | 1. Navigálj közvetlenül a `/statistics` URL-re |
| **Elvárt eredmény** | Átirányítás a `/login` oldalra |
| **Súlyosság** | Critical |
