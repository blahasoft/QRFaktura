<?php

/**
 * volani knihovny pro generování QR faktur v PHP.
 */


//error_reporting(E_ALL & ~E_NOTICE);
//error_reporting(E_ALL);
//ini_set('display_errors','On');

//require_once('src/QRFaktura.php');
require '/vendor/autoload.php';
use BlahaSoft\QRFaktura\QRFaktura;

$qrFakt = new QRFaktura(true);  //vystup primo do prohlizece
echo $qrFakt->getQRCode($_GET); //posle vygenerovany QR kod do prohlizece

// nebo...
/*$qrFakt = new QRFaktura(false); // standardni vystup
$img = $qrFakt->getQRCode($_GET); //vrati vygenerovany QR kod
echo '<html><body><img src="data:image/png;base64,'.base64_encode($img).'" /></body></html>';*/
?>