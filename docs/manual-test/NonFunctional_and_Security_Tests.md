# Nem-funkcionális és biztonsági tesztek

A következő tesztek checklist jellegűek (kockázat-alapúak). Nem mindegyik pass/fail jellegű; több esetben „kockázat dokumentálás” a cél.

## BIZTONSÁG (OWASP jellegű alapok)

### SEC-001 – Biztonsági HTTP headerek (.htaccess)

- **Prioritás:** Medium
- **Lépések:**
  1. Nyisd meg bármelyik oldalt.
  2. DevTools → Network → válaszd ki a dokumentum requestet.
  3. Ellenőrizd a Response Headers-t.
- **Elvárt:**
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: SAMEORIGIN`
  - `X-XSS-Protection: 1; mode=block`
  - `Referrer-Policy: strict-origin-when-cross-origin`

### SEC-002 – Session cookie HttpOnly

- **Prioritás:** Medium
- **Előfeltétel:** bejelentkezve
- **Lépések:**
  1. DevTools → Application/Storage → Cookies.
- **Elvárt:** a session cookie `HttpOnly`.

### SEC-003 – XSS próbálkozás: jármű mezők

- **Prioritás:** Medium
- **Lépések:**
  1. Jármű létrehozásnál a márka/modell mezőbe írd: `<script>alert(1)</script>`.
  2. Mentsd el.
  3. Nézd meg a listát.
- **Elvárt:** a szöveg **escape-elve** jelenik meg (nem fut le script).

### SEC-004 – XSS próbálkozás: napló cím mezők

- **Prioritás:** Medium
- **Lépések:**
  1. Napló bejegyzésnél a „Város” vagy „Utca” mezőbe írd a fenti payloadot.
  2. Mentsd el.
- **Elvárt:** nincs script futás; az oldal stabil.

### SEC-005 – SQL injection smoke

- **Prioritás:** Low
- **Lépések:**
  1. Login username mezőbe: `' OR 1=1 --`.
  2. Jelszó: bármi.
  3. Próbálj belépni.
- **Elvárt:** nem sikerül bejelentkezni; nincs szerver hiba.

### SEC-006 – CSRF kockázat (dokumentáció)

- **Prioritás:** Medium
- **Leírás:** state-changing endpointok (PUT/PATCH/DELETE) cookie auth mellett CSRF token nélkül kockázatos.
- **Lépések:**
  1. Dokumentáld, hogy van-e CSRF védelem a requestekben (token/header).
- **Elvárt:** ha nincs, kockázatként rögzíteni (nem feltétlen fail, de biztonsági backlog).

## HASZNÁLHATÓSÁG / UX

### UX-001 – Validációs hibák olvashatósága

- **Prioritás:** Low
- **Lépések:**
  1. Generálj validációs hibát (pl. regisztrációnál hibás email).
- **Elvárt:** a hiba jól látható, a mező piros jelölést kap.

### UX-002 – Dupla kattintás / többszöri mentés elleni védelem

- **Prioritás:** Low
- **Lépések:**
  1. Profil mentésnél kattints többször gyorsan a gombra.
- **Elvárt:** a UI letiltja a mezőket/gombot a request futása közben; nem duplázódik a művelet.

## KOMPATIBILITÁS / RESZPONZIVITÁS

### COMP-001 – Böngésző kompatibilitás

- **Prioritás:** Low
- **Lépések:**
  1. Futtasd a smoke flow-t (login → vehicles → journal → statistics → logout) Chrome-ban.
  2. Ismételd Firefox-ban.
- **Elvárt:** nincs layout törés, JS funkciók működnek.

### COMP-002 – Viewport / mobil nézet

- **Prioritás:** Low
- **Lépések:**
  1. DevTools → Toggle device toolbar.
  2. Próbáld ki 375×812 és 768×1024 méreteken.
- **Elvárt:** navigáció elérhető (hamburger), nincs használhatatlanul széteső UI.

## TELJESÍTMÉNY (alap)

### PERF-001 – Oldalbetöltési idők (smoke)

- **Prioritás:** Low
- **Lépések:**
  1. DevTools → Network → Disable cache.
  2. Töltsd be: `/vehicles`, `/journal`, `/statistics`.
- **Elvárt:** első betöltés ésszerű időn belül (pl. < 2-3s lokál deven), nincs tömeges 4xx/5xx.
