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
├── css/styles.css         Design Tokens, Komponenten, Sektionen, Responsive
├── js/script.js           CONFIG-Objekt, Reveal/Scrollspy/Parallax
├── js/lenis.min.js        Smooth-Scroll-Library (self-hosted, MIT-Lizenz)
├── fonts/                 Self-hosted Variable Fonts (.woff2)
└── assets/img/            Redaktionelle Stock-Fotografie (siehe unten)
```

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

- `fonts/inter-variable-latin.woff2` — Inter (Body-Text), SIL Open Font License
- `fonts/space-grotesk-variable-latin.woff2` — Space Grotesk (Headlines), SIL Open Font License

Beide sind Variable Fonts über den vollen Latin-Zeichensatz (inkl. deutscher
Umlaute/ß). Verschiedene Schriftschnitte werden in `css/styles.css` über
mehrere `@font-face`-Deklarationen mit fixem `font-weight` aus derselben
Datei ausgewählt — moderne Browser unterstützen das nativ.

## Smooth Scroll (Lenis)

`js/lenis.min.js` ist Lenis v1.1.18 (MIT-Lizenz), lokal gehostet statt per
Live-CDN eingebunden (DSGVO). Bei `prefers-reduced-motion: reduce` wird
automatisch auf natives `scroll-behavior: smooth` zurückgefallen und Lenis
nicht initialisiert.

## Kontaktformular

Das Formular in `#kontakt` sendet aktuell **nicht wirklich** — es zeigt nur
eine Erfolgsmeldung im Frontend (`js/script.js`, Submit-Handler unten in der
Datei). Eine Backend-Anbindung (z. B. ein `contact.php`-Pendant) ist
ausdrücklich nicht Teil dieses Durchgangs.

## Rechtliches

`impressum.html` und `datenschutz.html` sind bewusst leere Platzhalter mit
Seiten-Chrome (Header/Footer) — Inhalte gemäß TMG/DSGVO müssen in einem
separaten Schritt juristisch geprüft ergänzt werden.

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
