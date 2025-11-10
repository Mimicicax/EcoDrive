# Autentikáció
- regisztráció
	- email
	- felhasználónév (egyedi)
	- jelszó (8 karakter, legalább egy nagybetű és szám)
- bejelentkezés
		- felhasználónév vagy email

- elfelejtett jelszó
- megadott emailre (ha megvan az adatbázisban) visszaállító link küldése,
                  az oldalon új jelszót lehet beállítani. Az azonosítás a kiküldött link URL-jében
                  egy pár percig érvényes random kód ami a felhasználóhoz van kötve

# Profil
- email módosítás
- felhasználó név módosítás
- jelszó módosítás

# Autó
- fogyasztás (L/100 km-ben megadva mert gyakran így adják meg, ezt mi át tudjuk
          váltani L/km-be a szerveroldalon)
- név, márka, modell, év, rendszám
- autókat el lehet menteni és törölni

# Útvonal tervezés
- útvonal hossz
- szintkülönbség
- indulás időpontja
- útvonal elnevezése és mentése
- tervezéskor véletlenszerű CO2 csökkentő tanácsok, pl:
	- rövidebb útvonal
	- kerülje a torlódást, dugót, városokat
	- sík utakat válasszon vagy legalább olyanokat ahol az emelkedő egyenletesek,
                  a lejtőkön 0 is lehet a fogyasztás
	- javasolt a tömegközlekedés, rövid távolságokra a gyaloglás
	- szállítson minél több utast egy helyre külön utazás helyett
	- minimalizálja a jármű össztömegét

# Megtett utak
- megadhatja, hogy egy adott, már elmentett útvonalon mikor indult el és melyik autóval,
- CO2 kibocsátás becslés (az adott autóhoz beállított fogyasztás alapján;
          feltételezzük, hogy csak a távolságtól függ a fogyasztás)
- ezekből készülnek majd a statisztikák

# Statisztika
- eddigi CO2 kibocsátás (összegezzük a megtett utak CO2 kibocsátás becsléseit)
- éves CO2 kibocsátás extrapolálás (az eddigi fogyasztások alapján
          lineáris függvénnyel extrapolálunk pl. legkisebb négyzetek módszerével, ezt én meg tudom
          csinálni)
- megtett távolság (összegezzük a megtett útvonalak hosszait)
- napi átlag (elosztjuk a megtett távolságot az utazással töltött napokkal)
- havi átlagfogyasztás
- átlag CO2 kibocsátástól való eltérés (az átlag CO2 kibocsátás autósoknál 108.2 g/km
          Európában; ha ettől magasabb, pirossal, ha alaacsonyabb zölddel írjruk ki egy megfelelő
          irányú nyíllal mellette)
- Exportálási lehetőség (a megjelenített statisztikát ki tudjuk exportálni képként, esetleg PDF-ként)
