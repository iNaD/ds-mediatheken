# DS Mediatheken

#### Dieses Readme ist auch auf [Deutsch](README.md) verfügbar

Allows you to download videos from the german public service broadcasters' media libraries via Synology Download Station.

[![Build Status](https://travis-ci.com/iNaD/ds-mediatheken.svg?branch=master)](https://travis-ci.com/iNaD/ds-mediatheken)

## Table of Content

- [Features](#features)
- [Requirements](#requirements)
- [Supported Media Libraries](#supported-media-libraries)
- [Download](#download)
- [Installation](#installation)
- [Uninstallation](#uninstallation)
- [Update](#update)
- [Usage](#usage)
- [Notes](#notes)
- [Donations](#donations)
- [License](#license)

### Features

- Downloads in the highest MP4 quality available
- Titles the files by show and episode name (if precisely possible)

### Requirements

A Synology NAS with the latest version of `DiskStation Manager` (at least DSM 5.0) and the software `Download Station`.

### Supported Media Libraries

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

The current Version can always be found at the [Releases](https://github.com/iNaD/ds-mediatheken/releases/latest) page as `mediathek.host`.

### Installation

1. Using the Webinterface of `Diskstation Manager`, open the `Download Station` application
2. Open the `Settings` panel of `Download Station`
3. Switch to the Tab `File Hosting` and click `Add`
4. There you have to choose the `mediathek.host` file and confirm with `Add`
5. After a short period of time the entry `ÖR Mediatheken` will appear

The installation is finished and there is no further setup.

**Important for users of the old providers**

You should remove the old providers to prevent conflicts! (Only applies the currently supported media libraries)

### Uninstallation

1. Using the Webinterface of `Diskstation Manager`, open the `Download Station` application
2. Open the `Settings` panel of `Download Station`
3. Switch to the Tab `File Hosting`, select `ÖR Mediatheken` and click on `Delete`
4. `ÖR Mediatheken` will disappear from the list after a short loading time

### Update

1. Follow the [Uninstallation](#uninstallation) guide
2. Follow the [Installation](#installation) guide

### Usage

To download a video just open one of the [Supported Media Libraries](#supported-media-libraries), find your episode and copy the link to it.

This link just has to be added in the `Download Station` and after a short period of time the download will start using this provider.

### Notes


- **Important** Currently the highest MP4 quality available will be downloaded.
- It may happen that a download doesn't work as expected and no video file is downloaded. If this is the case, please [create an Issue](https://github.com/iNaD/ds-mediatheken/issues/new) with the link to the episode and some information about your Diskstation.

### Donations

You want to support the project or appreciate the work? Just write me some kind words via E-Mail or donate a small refreshment via [Paypal](https://paypal.me/theiNaD).

### License

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
