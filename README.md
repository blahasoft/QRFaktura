# QR Faktura

PHP knihovna pro generování QR kódů pro faktury. Generuje kód typu QR-Faktura nebo QR-Platba+faktura.

[![Build Status](https://img.shields.io/travis/blahasoft/QRFaktura/master.svg?style=flat-square)](https://travis-ci.org/blahasoft/QRFaktura)
[![Latest Version](https://img.shields.io/packagist/v/blahasoft/QRFaktura.svg?style=flat-square)](https://packagist.org/packages/blahasoft/QRFaktura)
[![Total Downloads](https://img.shields.io/packagist/dt/blahasoft/QRFaktura.svg?style=flat-square)](https://packagist.org/packages/blahasoft/QRFaktura)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

## Instalace pomocí Composeru

`composer require blahasoft/qrfaktura`

## Příklad

```php
<?php

require "vendor/autoload.php";

use BlahaSoft\QRFaktura\QRFaktura;

$qrFakt = new QRFaktura(true); //vystup primo do prohlizece

echo $qrFakt->getQRCode($_GET); //posle vygenerovany QR kod do prohlizece

// nebo...

$qrFakt = new QRFaktura(false); // standardni vystup

$img = $qrFakt->getQRCode($_GET); //vrati vygenerovany QR kod

echo '<html><body><img src="data:image/png;base64,'.base64_encode($img).'" /></body></html>';*/
```

## Odkazy

- Oficiální web QR Faktury - https://qrfaktura.cz/
- Aplikace na načítání QR faktur do účetnictví - https://qrkody.eu

