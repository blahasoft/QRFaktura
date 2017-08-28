# QR Faktura

Knihovna pro generování QR faktur v PHP. Generuje kód typu QR-Faktura nebo QR-Platba+faktura.

## Instalace pomocí Composeru

`composer require blahasoft/qrfaktura:~1.0`

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

