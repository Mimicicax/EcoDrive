# EcoDrive - Manuális Teszt Dokumentáció

## Tartalomjegyzék
1. [Bevezetés](#bevezetés)
2. [Tesztkörnyezet](#tesztkörnyezet)
3. [Regisztrációs tesztek](#regisztrációs-tesztek)
4. [Bejelentkezési tesztek](#bejelentkezési-tesztek)
5. [Profil kezelési tesztek](#profil-kezelési-tesztek)
6. [Járműkezelési tesztek](#járműkezelési-tesztek)
7. [Navigációs tesztek](#navigációs-tesztek)
8. [Biztonsági tesztek](#biztonsági-tesztek)

---

## Bevezetés

Ez a dokumentum tartalmazza az EcoDrive alkalmazás manuális teszteseteit. A teszteket egy junior tesztelő is képes végrehajtani a részletes leírások alapján.

### Tesztelési célok
- Funkcionális követelmények teljesülésének ellenőrzése
- Felhasználói élmény (UX) validálása
- Hibakezelés és validáció tesztelése
- Biztonság alapvető ellenőrzése

---

## Tesztkörnyezet

### Előfeltételek
- Böngésző: Chrome, Firefox, vagy Edge legfrissebb verzió
- Test adatbázis: tiszta állapot minden teszt előtt
- Szerver: lokális development környezet (http://localhost)

### Teszt adatok előkészítése
- Létező teszt felhasználó: `testuser` / `test@example.com` / `TestPass123`
- Töröljük az összes korábbi tesztadatot
- Ellenőrizzük, hogy az adatbázis elérhető

---

## Regisztrációs tesztek

### TC-REG-001: Sikeres regisztráció érvényes adatokkal

**Előfeltétel:** Nincs bejelentkezett felhasználó

**Lépések:**
1. Nyisd meg a főoldalt: `http://localhost`
2. Kattints a "Regisztráció" linkre
3. Töltsd ki az űrlapot:
   - Felhasználónév: `newuser01`
   - E-mail cím: `newuser01@test.com`
   - Jelszó: `SecurePass123`
   - Jelszó megerősítés: `SecurePass123`
4. Kattints a "Regisztráció" gombra

**Elvárt eredmény:**
- Átirányítás a bejelentkezési oldalra
- Sikeres regisztráció üzenet (ha van ilyen)
- Az új felhasználó be tud jelentkezni a megadott adatokkal

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-REG-002: Regisztráció túl rövid felhasználónévvel

**Előfeltétel:** Nincs bejelentkezett felhasználó

**Lépések:**
1. Nyisd meg a regisztrációs oldalt
2. Töltsd ki az űrlapot:
   - Felhasználónév: `ab` (2 karakter)
   - E-mail cím: `short@test.com`
   - Jelszó: `Pass123`
   - Jelszó megerősítés: `Pass123`
3. Kattints a "Regisztráció" gombra

**Elvárt eredmény:**
- Hibaüzenet jelenik meg: "A felhasználónév legalább 3 karakter hosszú kell legyen" (vagy hasonló)
- A regisztráció nem történik meg
- Az űrlap továbbra is látható

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-REG-003: Regisztráció érvénytelen email címmel

**Előfeltétel:** Nincs bejelentkezett felhasználó

**Lépések:**
1. Nyisd meg a regisztrációs oldalt
2. Töltsd ki az űrlapot:
   - Felhasználónév: `valideuser`
   - E-mail cím: `notanemail` (@ nélküli cím)
   - Jelszó: `Pass123`
   - Jelszó megerősítés: `Pass123`
3. Kattints a "Regisztráció" gombra

**Elvárt eredmény:**
- Hibaüzenet: "Érvénytelen e-mail cím" (vagy hasonló)
- Regisztráció sikertelen
- Űrlap megmarad a beírt adatokkal

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-REG-004: Regisztráció nem egyező jelszavakkal

**Előfeltétel:** Nincs bejelentkezett felhasználó

**Lépések:**
1. Nyisd meg a regisztrációs oldalt
2. Töltsd ki az űrlapot:
   - Felhasználónév: `passwordtest`
   - E-mail cím: `passwordtest@test.com`
   - Jelszó: `Password123`
   - Jelszó megerősítés: `Password456`
3. Kattints a "Regisztráció" gombra

**Elvárt eredmény:**
- Hibaüzenet: "A jelszavak nem egyeznek" (vagy hasonló)
- Regisztráció sikertelen

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-REG-005: Regisztráció már létező felhasználónévvel

**Előfeltétel:** Létezik már egy felhasználó `existinguser` névvel

**Lépések:**
1. Nyisd meg a regisztrációs oldalt
2. Töltsd ki az űrlapot:
   - Felhasználónév: `existinguser`
   - E-mail cím: `newemail@test.com`
   - Jelszó: `Pass123`
   - Jelszó megerősítés: `Pass123`
3. Kattints a "Regisztráció" gombra

**Elvárt eredmény:**
- Hibaüzenet: "Ez a felhasználónév már foglalt" (vagy hasonló)
- Regisztráció sikertelen

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-REG-006: Regisztráció már létező email címmel

**Előfeltétel:** Létezik már egy felhasználó `existing@test.com` email címmel

**Lépések:**
1. Nyisd meg a regisztrációs oldalt
2. Töltsd ki az űrlapot:
   - Felhasználónév: `newusername`
   - E-mail cím: `existing@test.com`
   - Jelszó: `Pass123`
   - Jelszó megerősítés: `Pass123`
3. Kattints a "Regisztráció" gombra

**Elvárt eredmény:**
- Hibaüzenet: "Ez az e-mail cím már használatban van" (vagy hasonló)
- Regisztráció sikertelen

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

## Bejelentkezési tesztek

### TC-LOGIN-001: Sikeres bejelentkezés felhasználónévvel

**Előfeltétel:** 
- Létezik felhasználó: `logintest` / `logintest@test.com` / `LoginPass123`
- Nincs bejelentkezett felhasználó

**Lépések:**
1. Nyisd meg a bejelentkezési oldalt
2. Töltsd ki az űrlapot:
   - Felhasználónév vagy email: `logintest`
   - Jelszó: `LoginPass123`
3. Kattints a "Bejelentkezés" gombra

**Elvárt eredmény:**
- Átirányítás a főoldalra
- Megjelenik a felhasználó neve az oldalon
- A navigációban látható a "Kijelentkezés" opció

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-LOGIN-002: Sikeres bejelentkezés email címmel

**Előfeltétel:** 
- Létezik felhasználó: `emaillogin` / `emaillogin@test.com` / `EmailPass123`
- Nincs bejelentkezett felhasználó

**Lépések:**
1. Nyisd meg a bejelentkezési oldalt
2. Töltsd ki az űrlapot:
   - Felhasználónév vagy email: `emaillogin@test.com`
   - Jelszó: `EmailPass123`
3. Kattints a "Bejelentkezés" gombra

**Elvárt eredmény:**
- Sikeres bejelentkezés
- Átirányítás a főoldalra

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-LOGIN-003: Bejelentkezés helytelen jelszóval

**Előfeltétel:** 
- Létezik felhasználó: `testuser` / `test@test.com` / `CorrectPass123`
- Nincs bejelentkezett felhasználó

**Lépések:**
1. Nyisd meg a bejelentkezési oldalt
2. Töltsd ki az űrlapot:
   - Felhasználónév vagy email: `testuser`
   - Jelszó: `WrongPassword`
3. Kattints a "Bejelentkezés" gombra

**Elvárt eredmény:**
- Hibaüzenet: "A felhasználónév vagy jelszó helytelen"
- Marad a bejelentkezési oldalon
- A felhasználónév mező kitöltve marad (jelszó törlődik)

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-LOGIN-004: Bejelentkezés nem létező felhasználóval

**Előfeltétel:** Nincs bejelentkezett felhasználó

**Lépések:**
1. Nyisd meg a bejelentkezési oldalt
2. Töltsd ki az űrlapot:
   - Felhasználónév vagy email: `nonexistentuser`
   - Jelszó: `AnyPassword123`
3. Kattints a "Bejelentkezés" gombra

**Elvárt eredmény:**
- Hibaüzenet: "A felhasználónév vagy jelszó helytelen"
- Bejelentkezés sikertelen

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-LOGIN-005: Bejelentkezés üres mezőkkel

**Előfeltétel:** Nincs bejelentkezett felhasználó

**Lépések:**
1. Nyisd meg a bejelentkezési oldalt
2. Hagyd üresen mindkét mezőt
3. Kattints a "Bejelentkezés" gombra

**Elvárt eredmény:**
- HTML5 validáció jelenik meg (böngésző által)
- VAGY hibaüzenet a szervertől
- Bejelentkezés sikertelen

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

## Profil kezelési tesztek

### TC-PROFILE-001: Felhasználónév módosítása

**Előfeltétel:** Bejelentkezve mint `profiletest` / `profile@test.com`

**Lépések:**
1. Nyisd meg a Profil oldalt
2. Kattints a "Felhasználónév módosítása" gombra/linkre
3. Írd be az új nevet: `newprofilename`
4. Kattints a "Mentés" gombra

**Elvárt eredmény:**
- Sikeres módosítás üzenet
- Az új név megjelenik az oldalon
- A következő bejelentkezésnél az új névvel lehet belépni

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-PROFILE-002: Email cím módosítása

**Előfeltétel:** Bejelentkezve

**Lépések:**
1. Nyisd meg a Profil oldalt
2. Kattints az "Email cím módosítása" gombra
3. Írd be az új email címet: `newemail@test.com`
4. Kattints a "Mentés" gombra

**Elvárt eredmény:**
- Sikeres módosítás
- Az új email cím látható a profilban

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-PROFILE-003: Jelszó módosítása

**Előfeltétel:** Bejelentkezve, jelenlegi jelszó: `OldPass123`

**Lépések:**
1. Nyisd meg a Profil oldalt
2. Kattints a "Jelszó módosítása" gombra
3. Töltsd ki az űrlapot:
   - Jelenlegi jelszó: `OldPass123`
   - Új jelszó: `NewPass456`
   - Új jelszó megerősítése: `NewPass456`
4. Kattints a "Mentés" gombra

**Elvárt eredmény:**
- Sikeres módosítás üzenet
- Kijelentkezés után az új jelszóval be lehet lépni
- A régi jelszó már nem működik

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-PROFILE-004: Jelszó módosítása helytelen jelenlegi jelszóval

**Előfeltétel:** Bejelentkezve, jelszó: `CurrentPass123`

**Lépések:**
1. Nyisd meg a Profil oldalt
2. Kattints a "Jelszó módosítása" gombra
3. Töltsd ki az űrlapot:
   - Jelenlegi jelszó: `WrongPassword`
   - Új jelszó: `NewPass456`
   - Új jelszó megerősítése: `NewPass456`
4. Kattints a "Mentés" gombra

**Elvárt eredmény:**
- Hibaüzenet: "A megadott jelszó helytelen"
- Jelszó nem változik meg

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

## Járműkezelési tesztek

### TC-VEHICLE-001: Új jármű hozzáadása

**Előfeltétel:** Bejelentkezve, nincs még jármű

**Lépések:**
1. Nyisd meg a "Járműveim" oldalt
2. Kattints az "Új jármű hozzáadása" gombra
3. Töltsd ki az űrlapot:
   - Márka: `Toyota`
   - Modell: `Corolla`
   - Rendszám: `ABC123`
   - Évjárat: `2020`
   - Fogyasztás (l/100km): `5.5`
4. Kattints a "Mentés" gombra

**Elvárt eredmény:**
- Sikeres mentés üzenet
- Az új jármű megjelenik a listában
- Az adatok helyesen jelennek meg

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-VEHICLE-002: Jármű adatainak módosítása

**Előfeltétel:** Bejelentkezve, van legalább egy jármű

**Lépések:**
1. Nyisd meg a "Járműveim" oldalt
2. Kattints a módosítani kívánt jármű "Szerkesztés" gombjára
3. Módosítsd az adatokat:
   - Fogyasztás: `6.0` (korábbi: 5.5)
4. Kattints a "Mentés" gombra

**Elvárt eredmény:**
- Sikeres módosítás
- Az új fogyasztási érték látható a listában

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-VEHICLE-003: Jármű törlése

**Előfeltétel:** Bejelentkezve, van legalább egy jármű

**Lépések:**
1. Nyisd meg a "Járműveim" oldalt
2. Kattints a törölni kívánt jármű "Törlés" gombjára
3. Erősítsd meg a törlést (ha van megerősítő ablak)

**Elvárt eredmény:**
- A jármű eltűnik a listából
- Sikeres törlés üzenet (ha van)

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-VEHICLE-004: Jármű hozzáadása már létező rendszámmal

**Előfeltétel:** 
- Bejelentkezve
- Létezik már jármű `XYZ789` rendszámmal

**Lépések:**
1. Nyisd meg a "Járműveim" oldalt
2. Kattints az "Új jármű hozzáadása" gombra
3. Töltsd ki az űrlapot:
   - Márka: `Honda`
   - Modell: `Civic`
   - Rendszám: `XYZ789`
   - Évjárat: `2019`
   - Fogyasztás: `6.0`
4. Kattints a "Mentés" gombra

**Elvárt eredmény:**
- Hibaüzenet: "Ez a rendszám már használatban van"
- Jármű nem kerül hozzáadásra

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-VEHICLE-005: Több jármű megjelenítése

**Előfeltétel:** 
- Bejelentkezve
- Van legalább 3 jármű hozzáadva

**Lépések:**
1. Nyisd meg a "Járműveim" oldalt
2. Ellenőrizd a megjelenített járműveket

**Elvárt eredmény:**
- Minden jármű megjelenik a listában
- Az adatok helyesek mindegyiknél
- A rendszámok nagybetűsek

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

## Navigációs tesztek

### TC-NAV-001: Főoldal elérése bejelentkezés nélkül

**Előfeltétel:** Nincs bejelentkezett felhasználó

**Lépések:**
1. Nyisd meg: `http://localhost`

**Elvárt eredmény:**
- A főoldal betölt
- Látható a "Bejelentkezés" és "Regisztráció" link
- Védett oldalak nem érhetők el

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-NAV-002: Védett oldal elérése bejelentkezés nélkül

**Előfeltétel:** Nincs bejelentkezett felhasználó

**Lépések:**
1. Próbáld meg közvetlenül elérni: `http://localhost/profile`

**Elvárt eredmény:**
- Átirányítás a bejelentkezési oldalra
- VAGY hibaoldal jelenik meg

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-NAV-003: Navigáció bejelentkezett állapotban

**Előfeltétel:** Bejelentkezve

**Lépések:**
1. Kattints végig a navigációs menüben szereplő linkeken:
   - Főoldal
   - Profil
   - Járműveim
   - Kijelentkezés

**Elvárt eredmény:**
- Minden link a megfelelő oldalra vezet
- Nincs 404 vagy 500-as hiba

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

## Biztonsági tesztek

### TC-SEC-001: Kijelentkezés után session törlése

**Előfeltétel:** Bejelentkezve

**Lépések:**
1. Jelentkezz be
2. Kattints a "Kijelentkezés" gombra
3. Nyisd meg a böngésző fejlesztői eszközeit (F12)
4. Nézd meg a Cookie-kat

**Elvárt eredmény:**
- A session cookie törlődik vagy érvénytelenné válik
- Vissza gombbal nem lehet visszalépni védett oldalra

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-SEC-002: XSS védelem tesztelése

**Előfeltétel:** Bejelentkezve

**Lépések:**
1. Próbálj meg jármű hozzáadásakor script injection-t:
   - Márka: `<script>alert('XSS')</script>`
   - Modell: `TestModel`
   - Rendszám: `SEC001`
   - Évjárat: `2020`
   - Fogyasztás: `5.0`
2. Mentés után nézd meg a járműlistát

**Elvárt eredmény:**
- A script NEM fut le
- A < és > karakterek escapelve jelennek meg
- VAGY a bejegyzés nem mentődik el

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

### TC-SEC-003: SQL Injection védelem

**Előfeltétel:** Nincs bejelentkezett felhasználó

**Lépések:**
1. Próbálj meg bejelentkezni:
   - Felhasználónév: `admin' OR '1'='1`
   - Jelszó: `anything`

**Elvárt eredmény:**
- Bejelentkezés sikertelen
- Nincs SQL hiba
- Normál hibaüzenet jelenik meg

**Státusz:** [ ] Sikeres / [ ] Sikertelen

**Megjegyzések:**
_______________________________________________________________________________

---

## Teszt összesítő

**Tesztelés dátuma:** _______________

**Tesztelő neve:** _______________

**Összesített eredmények:**
- Összes teszt: ___
- Sikeres: ___
- Sikertelen: ___
- Kihagyott: ___

**Kritikus hibák:** 
_______________________________________________________________________________
_______________________________________________________________________________

**Egyéb megjegyzések:**
_______________________________________________________________________________
_______________________________________________________________________________
_______________________________________________________________________________

---

**Aláírás:** _______________
