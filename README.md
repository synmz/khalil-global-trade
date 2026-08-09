# Khalil Global Trade — Website

Premium One-Pager für Khalil Global Trade (B2B-Handel: Industriemaschinen,
Laborgeräte, chemische Rohstoffe, internationale Beschaffung).

Reines HTML/CSS/JS — kein Build-Schritt, kein npm-Zwang, kein Framework.
Lauffähig durch einfaches Hochladen auf klassischem Webspace.

## Struktur

```
/
├── index.html            One-Pager mit allen Sektionen
├── impressum.html         Platzhalter (Inhalt separater Task)
├── datenschutz.html       Platzhalter (Inhalt separater Task)
├── css/styles.css         Design Tokens, Komponenten, Sektionen, Responsive, RTL
├── js/script.js           CONFIG-Objekt, Reveal/Scrollspy/Parallax, i18n-Engine
├── js/i18n.js             Übersetzungswörterbücher DE/EN/TR/AR
├── js/lenis.min.js        Smooth-Scroll-Library (self-hosted, MIT-Lizenz)
├── fonts/                 Self-hosted Variable Fonts (.woff2, Latin + Latin-Ext)
└── assets/img/            Redaktionelle Stock-Fotografie (siehe unten)
```

## Mehrsprachigkeit (DE / EN / TR / AR)

Die Seite bietet einen Sprachumschalter oben rechts im Header (Desktop) bzw.
oben im aufklappbaren Menü (Mobile). Der Wechsel erfolgt direkt auf der
Seite ohne Neuladen — es gibt bewusst **keine** separaten URLs pro Sprache
(z. B. `/en/`), da dies bei einem reinen HTML/CSS/JS-Setup ohne Build-Schritt
den Pflegeaufwand vervierfacht hätte (vier komplette HTML-Dateien statt
einer). Die gewählte Sprache wird in `localStorage` gespeichert und beim
nächsten Besuch automatisch wiederhergestellt.

**Wichtig — bitte vor Launch lesen:** Die Übersetzungen in `js/i18n.js`
wurden KI-gestützt erstellt. Sie sind fachlich und grammatikalisch sorgfältig
formuliert, ersetzen aber keine Prüfung durch Muttersprachler — das gilt
besonders für Türkisch und Arabisch. Bitte vor dem Live-Gang von
entsprechenden Sprechern gegenlesen lassen, insbesondere Fachbegriffe wie
Produktbezeichnungen und Branchennamen.

### Funktionsweise

- Jedes übersetzbare Element in `index.html` trägt ein `data-i18n="..."`-
  Attribut (Punktnotation, z. B. `data-i18n="hero.subtitle"`). Für
  Platzhaltertexte in Formularfeldern: `data-i18n-placeholder`, für Alt-Texte
  von Bildern: `data-i18n-alt`, für `aria-label`: `data-i18n-aria`.
- `js/i18n.js` enthält für jede Sprache ein Objekt mit denselben Schlüsseln.
- `js/script.js` (Funktion `applyLanguage()`) liest beim Sprachwechsel das
  passende Wörterbuch aus und ersetzt `textContent`/`placeholder`/`alt`/
  `aria-label` aller markierten Elemente. Zusätzlich werden `<html lang>`,
  `<html dir>`, `<title>` und die Meta-Description aktualisiert.

### Neuen Text übersetzbar machen

1. In `index.html` das Element mit `data-i18n="bereich.name"` markieren
   (neuer, eindeutiger Schlüssel).
2. In `js/i18n.js` denselben Schlüssel in **allen vier** Sprachobjekten
   (`de`, `en`, `tr`, `ar`) ergänzen.

### Text in einer bestehenden Sprache ändern

Einfach den entsprechenden String in `js/i18n.js` im jeweiligen Sprachobjekt
anpassen — keine Änderung an `index.html` nötig.

### Rechts-nach-links (Arabisch)

Bei Auswahl von Arabisch setzt das Script `dir="rtl"` auf `<html>`. Die
meiste Spiegelung (Kopfzeile, Grids, Textausrichtung) übernehmen moderne
Browser automatisch, da Flexbox und CSS Grid richtungsbewusst sind. Manuelle
Korrekturen gibt es nur dort, wo harte `left`/`right`-Werte oder
Richtungs-Icons (Pfeile) nötig waren — siehe Abschnitt „RTL (Arabisch)" ganz
unten in `css/styles.css`.

Für arabischen Text greift automatisch die System-Schriftart des Betriebssystems,
da die selbst gehosteten Fonts (Inter, Space Grotesk) kein arabisches
Alphabet enthalten. Das ist normales, erwartetes Verhalten und keine
fehlende Konfiguration.

## Konfiguration

Firmenname, E-Mail, Telefon und Adresse werden zentral in `js/script.js`
oben im `CONFIG`-Objekt gepflegt und automatisch in alle `[data-config]`-
Elemente auf der Seite eingesetzt. Bitte vor Launch mit echten Kundendaten
befüllen — und dieselben Werte im JSON-LD-Block (`<head>` von `index.html`)
sowie im Impressum nachziehen.

## Bildmaterial

Der Kunde hat kein eigenes Bildmaterial. Für diesen Durchgang wurde daher
kuratierte, lizenzfreie Stock-Fotografie von Pexels eingesetzt (Pexels
License — kostenlose kommerzielle Nutzung, keine Namensnennung
verpflichtend). Die Bilder sind bewusst nach der visuellen Sprache der
Referenzbilder des Kunden ausgewählt (Dämmerung/Nacht-Stimmung, satte
Farben, editorial statt Stock-Klischee).

| Datei | Einsatzort | Motiv | Fotograf (Pexels) | Foto-ID |
|---|---|---|---|---|
| `assets/img/hero-refinery.jpg` | Hero, Vollflächig | Petrochemische Anlage bei Nacht, Luftaufnahme | Tom Fisk | [10386893](https://www.pexels.com/photo/10386893/) |
| `assets/img/about-facility.jpg` | Über uns | Moderne Glasfassade, Unternehmenszentrale | wal_ | [37320179](https://www.pexels.com/photo/37320179/) |
| `assets/img/product-machinery.jpg` | Produkte Card 01 „Maschinenhandel“ | CNC-Fräsmaschine in Produktionshalle | Connor Lucock | [28929510](https://www.pexels.com/photo/28929510/) |
| `assets/img/product-lab.jpg` | Produkte Card 02 „Laborgeräte“ | Laborglas mit blauer Flüssigkeit | Carlos Garcia | [18725478](https://www.pexels.com/photo/18725478/) |
| `assets/img/product-chemicals.jpg` | Produkte Card 03 „Chemische Rohstoffe“ | Mitarbeiter mit Fässern im Lager | Martin Zapata | [20379378](https://www.pexels.com/photo/20379378/) |
| `assets/img/product-additives.jpg` | Produkte Card 04 „Spezialchemikalien & Additive“ | Farbige Chemikalien in Laborkolben | Ivan S | [9628800](https://www.pexels.com/photo/9628800/) |
| `assets/img/product-sourcing.jpg` | Produkte Card 05 „Internationale Beschaffung“ | Beleuchteter Containerhafen bei Nacht | Fatih Turan | [16229885](https://www.pexels.com/photo/16229885/) |

**Empfehlung vor Launch:** Diese Stock-Fotos sind hochwertig und lizenzrechtlich
unbedenklich, aber generisch — sie zeigen nicht die tatsächlichen Anlagen,
Produkte oder das Team von Khalil Global Trade. Sobald eigenes Bildmaterial
(Firmenfotos, echte Produktaufnahmen) vorliegt, sollten diese Dateien 1:1
ausgetauscht werden (gleicher Dateiname und Seitenverhältnis genügt, kein
HTML-Umbau nötig). Für Web-Performance zusätzlich als WebP exportieren.

### Bilder austauschen

1. Neue Datei mit identischem Namen nach `assets/img/` legen (z. B. neues
   `hero-refinery.jpg` über die bestehende Datei kopieren) — dann ist keine
   Änderung an `index.html` nötig.
2. Alternativ neuen Dateinamen verwenden und das `src`-Attribut des
   jeweiligen `<img>`-Tags in `index.html` anpassen.
3. Für responsive Auflösungen zusätzlich `srcset`/`sizes` ergänzen, z. B.:

```html
<img
  src="assets/img/hero-refinery-1600.jpg"
  srcset="
    assets/img/hero-refinery-800.jpg 800w,
    assets/img/hero-refinery-1600.jpg 1600w,
    assets/img/hero-refinery-2400.jpg 2400w"
  sizes="100vw"
  loading="lazy"
  alt="...">
```

(Für das Hero-Bild `loading="lazy"` weglassen — es ist above the fold.)

4. `width`/`height`-Attribute auf die tatsächlichen Bildmaße anpassen, um
   Layout-Shifts zu vermeiden.

## Fonts

Selbst gehostete Variable Fonts (kein Google-Fonts-CDN, DSGVO-konform):

- `fonts/inter-variable-latin.woff2` / `-latin-ext.woff2` — Inter (Body-Text), SIL Open Font License
- `fonts/space-grotesk-variable-latin.woff2` / `-latin-ext.woff2` — Space Grotesk (Headlines), SIL Open Font License

Je Schrift gibt es zwei `@font-face`-Dateien mit unterschiedlichem
`unicode-range`: „Latin" deckt Deutsch/Englisch ab, „Latin-Ext" ergänzt
zusätzliche Zeichen u. a. für Türkisch (ş, ğ, İ). Der Browser lädt und nutzt
automatisch die passende Datei pro Zeichen — keine weitere Konfiguration
nötig. Beide sind Variable Fonts; verschiedene Schriftschnitte werden in
`css/styles.css` über mehrere `@font-face`-Deklarationen mit fixem
`font-weight` aus derselben Datei ausgewählt.

Arabischer Text nutzt bewusst die System-Schriftart (siehe Abschnitt
„Mehrsprachigkeit" oben) — Inter/Space Grotesk enthalten kein arabisches
Alphabet.

## Smooth Scroll (Lenis)

`js/lenis.min.js` ist Lenis v1.1.18 (MIT-Lizenz), lokal gehostet statt per
Live-CDN eingebunden (DSGVO). Bei `prefers-reduced-motion: reduce` wird
automatisch auf natives `scroll-behavior: smooth` zurückgefallen und Lenis
nicht initialisiert.

## Kontaktformular

Das Formular in `#kontakt` sendet per `fetch()` (`js/script.js`,
`initContactForm`) an `contact.php`. Das PHP-Backend validiert und
versendet serverseitig eine E-Mail an `info@khalilglobaltrade.de` und ist
mehrfach abgesichert:

- CSRF-Schutz per Session-Token (wird vor dem Absenden per `GET
  contact.php?csrf=1` geholt)
- Herkunftsprüfung (Origin/Referer-Allowlist) als zusätzliche Absicherung
- Honeypot-Feld (`name="website"`) gegen einfache Spam-Bots
- Dateibasiertes Rate-Limiting pro IP (`storage/ratelimit/`, per
  `.htaccess` vor Web-Zugriff geschützt)
- Strikte Server-seitige Eingabevalidierung & Schutz vor
  E-Mail-Header-Injection

**Voraussetzung:** Ein PHP-fähiger Webserver (PHP 7.4+) mit funktionsfähigem
`mail()` (bzw. konfiguriertem Sendmail/SMTP-Relay). Beim rein lokalen Öffnen
von `index.html` im Browser (ohne PHP-Server) läuft das Formular ins Leere,
da `contact.php` dann nicht ausgeführt wird — zum Testen einen PHP-Server
nutzen (`php -S localhost:8000`) statt `python3 -m http.server`.

## Rechtliches

`impressum.html` enthält eine vollständige Vorlage gemäß § 5 DDG (vormals
§ 5 TMG). Noch offene, unternehmensspezifische Angaben (Rechtsform,
Vertretungsberechtigung, Anschrift, Telefon, Handelsregister, USt-IdNr.,
verantwortliche Person nach § 18 Abs. 2 MStV) sind mit `<span class="tbd">`
markiert und müssen vor dem Live-Gang ergänzt werden. Die Vorlage ersetzt
keine anwaltliche Prüfung.

`datenschutz.html` enthält eine vollständige Vorlage gemäß DSGVO, die
ausschließlich die tatsächlich im Code stattfindenden Verarbeitungen
beschreibt (Server-Logfiles, Kontaktformular inkl. Session-Cookie & IP-
Rate-Limiting, `localStorage` für die Sprachwahl — keine Google Fonts,
kein Tracking, keine Analyse-Tools). Offene, unternehmensspezifische
Angaben (Anschrift, Hosting-/E-Mail-Provider, Speicherfristen, zuständige
Aufsichtsbehörde) sind mit `<span class="tbd">` markiert. Beide
Rechtsseiten ersetzen keine anwaltliche Prüfung.

## Lokal testen

Kein Server nötig — `index.html` kann direkt im Browser geöffnet werden.
Für relative Pfade in einer Entwicklungsumgebung empfiehlt sich dennoch ein
einfacher lokaler Server, z. B.:

```bash
python3 -m http.server 8000
```

Anschließend `http://localhost:8000` öffnen.

## Browser-Unterstützung

Getestet für aktuelle Versionen von Chrome, Firefox, Safari, Edge. Nutzt
moderne CSS-Features (`clamp()`, CSS Grid, `:focus-visible`, `svh`-Einheit)
sowie `IntersectionObserver` — beides seit mehreren Jahren breit unterstützt.
