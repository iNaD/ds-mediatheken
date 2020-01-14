# DS Mediatheken

#### This Readme is also available in [English](README_EN.md)

Ermöglicht es mit der Synology Download Station Videos aus den Mediatheken der ÖR herunterzuladen.

[![Build Status](https://travis-ci.com/iNaD/ds-mediatheken.svg?branch=master)](https://travis-ci.com/iNaD/ds-mediatheken)

## Inhalt

- [Features](#features)
- [Voraussetzungen](#voraussetzungen)
- [Unterstützte Mediatheken](#unterstützte-mediatheken)
- [Download](#download)
- [Installation](#installation)
- [Deinstallation](#deinstallation)
- [Update](#update)
- [Nutzung](#nutzung)
- [Hinweise](#hinweise)
- [Spenden](#spenden)
- [Lizenz](#lizenz)

### Features

- Lädt in der höchst verfügbaren MP4 Qualität herunter
- Bennent die Datei nach Sendungs- und Folgentitel (soweit eindeutig analysierbar)

### Voraussetzungen

Ein Synology-NAS mit einer aktuellen Version des `DiskStation Managers` (mindestens DSM 5.0) und dem Programm `Download Station`.

### Unterstützte Mediatheken

- 3sat (`3sat.de`)
- ARD (`ardmediathek.de`, `mediathek.daserste.de`)
- Arte (`arte.tv`)
- BR (`br.de`)
- KiKa (`kika.de`)
- MDR (`mdr.de`)
- NDR (`ndr.de`)
- RBB (`mediathek.rbb-online.de`)
- WDR (`wdr.de/mediathek`, `one.ard.de/mediathek`)
- ZDF Mediathek (`zdf.de`)

### Download

Die aktuelle Version ist immer in den [Releases](https://github.com/iNaD/ds-mediatheken/releases/latest) zu finden als `mediathek.host`.

### Installation

1. Über das Webinterface des `Diskstation Managers` die `Download Station` Anwendung öffnen
2. Die `Einstellungen` von `Download Station` aufrufen
3. Auf den Reiter `Dateihosting` wechseln und auf `Hinzufügen` klicken
4. Dort dann die Datei `mediathek.host` auswählen und auf `Hinzufügen` klicken
5. In der Liste taucht nach einer kurzen Ladezeit der Eintrag `ÖR Mediatheken` auf

Die Installation ist abgeschlossen und es benötigt keine weitere Einrichtung.

**Wichtig für alle Nutzer der alten Provider**

Die alten Provider sollten entfernt werden, da es sonst zu Problemen kommen kann! (Betrifft nur die unterstützten Mediatheken)

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

Dieser Link wird bei der `Download Station` nun einfach hinzugefügt und nach kurzer Zeit beginnt der Download über den Provider.

### Hinweise


- **Wichtig** Es wird zur Zeit die höchstmögliche MP4 Qualität heruntergeladen die verfügbar ist.
- Es könnte passieren, dass ein Download nicht funktioniert und keine Videodatei heruntergeladen wird. Sollte dies der Fall sein, so [erstelle ein Ticket](https://github.com/iNaD/ds-mediatheken/issues/new) mit dem Link zur Folge und ein paar Informationen über deine Diskstation.

### Spenden

Du möchtest das Projekt unterstützen und schätzt die Arbeit dahinter? Schreib doch einfach eine nette E-Mail oder spendiere mir über [Paypal](https://paypal.me/theiNaD) eine kleine Erfrischung.

### Lizenz

```
The MIT License (MIT)

Copyright (c) 2017-2020 Daniel Gehn

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
