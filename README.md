# DS Mediatheken

Ermöglicht es mit der Synology Download Station Videos aus den Mediatheken der ÖR herunterzuladen.

## Inhalt

- [Features](#features)
- [Voraussetzungen](#voraussetzungen)
- [Unterstützte Mediatheken](#unterstützte-mediatheken)
- [Installation](#installation)
- [Deinstallation](#deinstallation)
- [Update](#update)
- [Nutzung](#nutzung)
- [Hinweise](#hinweise)
- [Lizenz](#lizenz)

### Features

- Lädt in der höchst verfügbaren MP4 Qualität herunter
- Bennent die Datei nach Sendungs- und Folgentitel (soweit eindeutig analysierbar)

### Voraussetzungen

Ein Synology-NAS mit einer aktuellen Version des `DiskStation Managers` und dem Programm `Download Station`.

### Unterstützte Mediatheken

- ZDF Mediathek (`zdf.de`)
- 3sat (`3sat.de`)

### Installation

1. Über das Webinterface des `Diskstation Managers` die `Download Station` Anwendung öffnen
2. Die `Einstellungen` von `Download Station` aufrufen
3. Auf den Reiter `Dateihosting` wechseln und auf `Hinzufügen` klicken
4. Dort dann die Datei `mediathek.host` auswählen und auf `Hinzufügen` klicken
5. In der Liste taucht nach einer kurzen Ladezeit der Eintrag `ÖR Mediatheken` auf

Die Installation ist abgeschlossen und es benötigt keine weitere Einrichtung.

**Wichtig für alle Nutzer der alten Provider**

Die alten Provider sollten entfernt werden, da es sonst zu Problemen kommen kann! (Bezieht sich auf die aktuell unterstützten Mediatheken)

### Deinstallation

1. Über das Webinterface des `Diskstation Managers` die `Download Station` Anwendung öffnen
2. Die `Einstellungen` von `Download Station` aufrufen
3. Auf den Reiter `Dateihosting` wechseln, `ÖR Mediatheken` auswählen und auf `Löschen` klicken
4. `ÖR Mediatheken` verschwindet nach einer kurzen Ladezeit aus der Liste

### Update

1. Der Anleitung [Deinstallation](#deinstallation) folgen
2. Der Anleitung [Installation](#installation) folgen

### Nutzung

Zum Herunterladen eines Videos, eine der [unterstützten Mediatheken](#unterstützte-mediatheken) aufrufen, Folge heraussuchen und den Link zur Folge kopieren.

Dieser Link wird bei der Download Station nun einfach hinzugefügt und nach kurzer Zeit beginnt der Download über den Provider.

### Hinweise


- **Wichtig** Es wird zur Zeit die höchstmögliche MP4 Qualität heruntergeladen die verfügbar ist.
- Es könnte passieren, dass ein Download nicht funktioniert und keine Videodatei heruntergeladen wird. Sollte dies der Fall sein, so [erstelle ein Ticket](https://github.com/iNaD/ds-mediatheken/issues/new) mit dem Link zur Folge und ein paar Informationen über deine Diskstation.

### Lizenz

```
The MIT License (MIT)

Copyright (c) 2017 Daniel Gehn

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```
