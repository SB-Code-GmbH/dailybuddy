# Content Timeline Module für dailybuddy

Dieses Modul fügt ein professionelles Content Timeline Widget zu deinem dailybuddy WordPress-Plugin hinzu.

## Installation

1. Kopiere den gesamten `content-timeline` Ordner in dein Plugin-Verzeichnis:
   ```
   /wp-content/plugins/dein-plugin/modules/elementor-extensions/content-timeline/
   ```

2. Die Ordnerstruktur sollte so aussehen:
   ```
   content-timeline/
   ├── assets/
   │   ├── editor.css
   │   ├── script.js
   │   └── style.css
   ├── config.php
   ├── module.php
   └── widget.php
   ```

3. Stelle sicher, dass dein Plugin die folgenden Konstanten definiert hat:
   - `DAILYBUDDY_PATH` - Der absolute Pfad zu deinem Plugin
   - `DAILYBUDDY_URL` - Die URL zu deinem Plugin
   - `DAILYBUDDY_VERSION` - Die Version deines Plugins

4. Registriere das Modul in deinem Plugin's Haupt-Initialisierungscode:
   ```php
   require_once DAILYBUDDY_PATH . 'modules/elementor-extensions/content-timeline/module.php';
   ```

## Features

### Content-Quellen
- **Dynamic Posts**: Zeigt automatisch Beiträge aus deiner WordPress-Datenbank an
- **Custom Content**: Erstelle individuelle Timeline-Einträge mit dem Repeater

### Layouts
- **Vertical Timeline**: Klassische vertikale Timeline mit abwechselnden Seiten
- **Horizontal Timeline**: Scrollbare horizontale Timeline

### Anpassungsmöglichkeiten
- Zeige/Verstecke Elemente: Titel, Datum, Bild, Excerpt, Read More Button
- Vollständige Style-Kontrolle über:
  - Timeline-Linie (Farbe, Breite)
  - Timeline-Items (Hintergrund, Border, Shadow, Padding)
  - Titel (Farbe, Typography, Spacing)
  - Datum (Farbe, Typography)
  - Content (Farbe, Typography)

### Query-Optionen (Dynamic Posts)
- Post Type Auswahl
- Posts Per Page
- Order By (Date, Title, Modified, Random)
- Order (ASC/DESC)

## Verwendung

1. Öffne den Elementor Editor
2. Suche nach "Content Timeline" in der Widget-Suche
3. Ziehe das Widget in deine Seite
4. Wähle zwischen "Dynamic Posts" oder "Custom Content"
5. Konfiguriere die Einstellungen nach deinen Wünschen
6. Style das Widget mit den Style-Tabs

## Anpassungen an dein Plugin

Das Modul ist bereits für die dailybuddy-Struktur angepasst. Falls du weitere Anpassungen brauchst:

### Widget-Name ändern
In `widget.php`, Zeile 28:
```php
return 'dailybuddy-content-timeline';
```

### Kategorie ändern
In `widget.php`, Zeile 52:
```php
return array('dailybuddy');
```

### Text-Domain anpassen
Ersetze alle `'dailybuddy'` Strings mit deiner eigenen Text-Domain.

## Abhängigkeiten

- WordPress 5.0+
- Elementor 3.0+
- PHP 7.4+

## Browser-Kompatibilität

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile Browsers (iOS Safari, Chrome Mobile)

## Responsive Design

Das Widget ist vollständig responsive und passt sich automatisch an verschiedene Bildschirmgrößen an:
- Desktop: Vollständige Timeline mit abwechselnden Seiten
- Tablet: Optimierte Darstellung
- Mobile: Vereinfachte Timeline auf einer Seite

## Support & Erweiterungen

Wenn du zusätzliche Features benötigst:
- ACF Integration (wie im Original)
- Zusätzliche Layouts
- Erweiterte Animations-Optionen
- Filter-Funktionalität

Kontaktiere mich gerne für weitere Anpassungen!

## Credits

Adaptiert von Essential Addons for Elementor's Content Timeline Widget
Angepasst für das dailybuddy WordPress-Plugin
