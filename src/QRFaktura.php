<?php
//todo kontrolovat validitu ICO https://phpfashion.com/jak-overit-platne-ic-a-rodne-cislo
//todo kontrolovat validitu DIC

namespace BlahaSoft\QRFaktura;

require_once('phpqrcode/qrlib.php');

/**
 * Knihovna pro generování QR faktur v PHP.
 * Pro PHP 5.4 a vyssi
 *
 * @package BlahaSoft\QRFaktura
 * @since   1.0
 * @author  Jan Bláha <info@blahasoft.cz>
 * @copyright Copyright (c) 2017 Jan Bláha <info@blahasoft.cz>
 * @link    https://qrkody.eu
 * @license MIT
 */
class QRFaktura
{
    /**
     * Verze QR formátu QR Faktury
     */
    const VERSION = '1.0';

    /**
     * Verze QR formátu QR Platby
     */
    const QRP_VERSION = '1.0';

    /**
     * Prefix klicu QR Platby
     */
    const QRP_PREFIX = ''; //nepouziva se

    /**
     * Nazev vystupniho souboru
     */
    const SIND_FILENAME = 'QRKod.sind';

    /**
     * @var array klice pro QR Fakturu
     */   
    private $keys = [
        'ID'     => null, // Max. 40 - znaků oznaceni dokladu   !povinny        
        'DD'     => null, // Max. 8 znaků - datum vystaveni     !povinny        
        'AM'     => null, //Max. 18 znaků - Desetinné číslo Výše částky k uhrade.  !povinny        
        'TP'     => null, // Právě 1 znaky - typ danoveho plneni
        'TD'     => null, // Právě 1 znaků - typ dokladu
        'SA'     => null, // Právě 1 znaků - zda fa obsahuje zuctovani zaloh
        'MSG'    => null, // Max. 40 znaků - popis predmetu plneni
        'ON'     => null, // Max. 20 znaků - oznaceni objednavky
        'VS'     => null, // Max. 10 znaků - Celé číslo - variabilni symbol
        'VII'    => null, // Max. 14 znaků - alfanum. znaky DIC vystavce
        'INI'    => null, // Max. 14 znaků - alfanum. znaky ICO vystavce
        'VIR'    => null, // Max. 14 znaků - alfanum. znaky DIC prijemce
        'INR'    => null, // Max. 14 znaků - alfanum. znaky ICO prijemce
        'DUZP'   => null, // Právě. 8 znaků - datum uskutecneni zdan. plneni
        'DPPD'   => null, // Právě. 8 znaků - datum povinnosti priznat dan
        'DT'     => null, // Právě. 8 znaků - datum splatnosti
        'TB0'    => null, // Max. 18 znaků - Desetinné číslo Zaklad dane v zakladni sazbe DPH
        'T0'     => null, // Max. 18 znaků - Desetinné číslo Dan v zakladni sazbe DPH
        'TB1'    => null, // Max. 18 znaků - Desetinné číslo Zaklad dane v prvni snizene sazbe DPH 15%
        'T1'     => null, // Max. 18 znaků - Desetinné číslo Dan v prvni snizene sazbe DPH
        'TB2'    => null, // Max. 18 znaků - Desetinné číslo Zaklad dane v druhe snizene sazbe DPH 10%
        'T2'     => null, // Max. 18 znaků - Desetinné číslo Dan v prvni druhe sazbe DPH
        'NTB'    => null, // Max. 18 znaků - Desetinné číslo Osvobozena plneni
        'CC'     => null, // Právě 3 znaky - Měna platby.
        'FX'     => null, // Max. 18 znaků - Desetinné číslo Kurz cizi meny
        'FXA'    => null, // Max. 5 znaků - Cele číslo Pocet jednotek cizi meny
        'ACC'    => null, // Max. 46 - znaků IBAN, BIC Identifikace protistrany
        'CRC32'  => null, // Právě 8 znaků - Kontrolní součet - HEX.
        'X-SW'   => null, // Max. 30 - znaků oznaceni SW
        'X-URL'  => null, // Max. 70 - znaků ziskani faktury z online uloziste
    ];

    /**
     * @var array klice pro QR platbu
     */
    private $QRP_keys = [
        "ACC"    => null, // Max. 46 - znaků IBAN, BIC Identifikace protistrany !povinny
        'ALT-ACC'=> null, // Max. 46 - znaků IBAN, BIC Identifikace protistrany
        'AM'     => null, //Max. 10 znaků - Desetinné číslo Výše částky k uhrade.
        'CC'     => null, // Právě 3 znaky - Měna platby.
        'RF'     => null, // Max. 16 - znaků - Celé číslo identifikator platby pro prijemce
        'RN'     => null, // Max. 25 - znaků jmeno prijemce
        'DT'     => null, // Právě. 8 znaků - datum splatnosti
        'PT'     => null, // Max. 3 - znaků typ platby
        'MSG'    => null, // Max. 60 znaků - popis predmetu plneni
        'CRC32'  => null, // Právě 8 znaků - Kontrolní součet - HEX.
        'NT'     => null, // Právě 1 znaků - identifikace kanalu zaslani notifikace
        'NTA'    => null, // Max. 320 znaků - tel nebo mail

        //rozsireni QRPlatby pro CR
        'X-PER'  => null, // Max. 2 znaků - Celé číslo Pocet dni opakovani
        'X-VS'   => null, // Max. 10 znaků - Celé číslo - variabilni symbol
        'X-SS'   => null, // Max. 10 znaků - Celé číslo - specificky symbol
        'X-KS'   => null, // Max. 10 znaků - Celé číslo - konstantni symbol
        'X-ID'   => null, // Max. 20 znaků - identifikator platby na strane prijemce
        'X-URL'  => null, // Max. 140 - znaků URL pro vlastni potrebu

        'X-INV'  => null, // URL kodovany retezec QRFaktury
    ];

    const ERR_DESC = 'description'; //klic prvku pole s popisem chyby
    const ERR_CODE = 'code'; //klic prvku pole s kodem chyby
    const DUMMY_ERR_CODE = '0000'; //neznama chyba
    const ERR_CODE_NOT_EXIST = '0001'; //hodnota neexistuje
    const ERR_CODE_TOO_LONG = '0002'; //hodnota je prilis dlouha neb prilis kratka
    const ERR_CODE_BAD_FORMAT = '0003'; //spatny format hodnoty (datum, cislo atd.)

    /**
     * zda je vystup primo do prohlizece nebo zda se vrati klasicky
     */
    private $isDirectOutput = true;

    /**
     * zda kod obsahuje i QRPlatbu
     */
    private $isQRPlatba = false;

    /**
     * typ vystupu: 0 nebo prazdne - PNG obrazek; 1 - text/plain; 2 - soubor application/x-shortinvoicedescriptor
     */
    private $outputFormat = 0;

    /**
     * branding QR kódu (nápis QRFaktura nebo QRPlatba+F). Defaultne se nápis na obrázek přidává.
     */
    private $branding = true;

    /**
     * kompaktni format bez diakritiky. Defaultne se diakritika zachovava.
     */
    private $compress = false;

    /**
     * velikost kazdeho ctverce v QR kodu v px
     */
    private $QRSquareSize = 4;

    /**
     * Text ktery se ma vlozit do obrazku: QRFaktura nebo QRPlatba+F
     */
    private $QRText = '';

    /**
     * mozne hodnoty TP
     */
    private $rangeTP = array('0'=>'0', '1'=>'1', '2'=>'2');

    /**
     * mozne hodnoty TD
     */
    private $rangeTD = array('0'=>'0', '1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '9'=>'9');

    /**
     * mozne hodnoty SA
     */
    private $rangeSA = array('0'=>'0', '1'=>'1');

    /**
     * mozne hodnoty NT
     */
    private $rangeNT = array('P'=>'P', 'E'=>'E');

    /**
     * pole chyb
     */
    private $errDesc = array();

    /**
     * Kontruktor nové platby.
     *
     * @param bool $directOutput Přepínač, zda výstup posílat přímo do prohlížeče včetně hlaviček (true) nebo pouze jako standardní výstup (false)
     * @throws \Exception Pokud není parametr striktní TRUE nebo FALSE
     */
    public function __construct($directOutput)
    {
        if ($directOutput === true || $directOutput === false) {
            $this->isDirectOutput = $directOutput;
        }
        else {
            throw new \Exception('Parametr $directOutput v konstruktoru musí být striktní TRUE nebo FALSE');
        }
    }

    /**
     * Načtení parametrů z pole
     *
     * @param array $arr pole Qr parametru, pravdepodobne $_GET nebo jine URL-decoded pole
     * @return mixed QR kód nebo chybová struktura. Dle parametru v konstruktoru posílá výstup včetně HTTPS hlaviček nebou pouze stadardní výstup
     */
    public function getQRCode($arr)
    {
        //tento parametr musime nacist prvni, protoze podle nej se rovnou meni diakritika
        //bez diakritiky
        if (trim($arr['compress']) == '1') {
            $this->compress = true;
        }
        else {
            $this->compress = false;
        }

        $this->setText(trim($arr['ID']), 'ID', true, 40, false);
        $this->setAmount(trim($arr['AM']), 'AM', true, 18, 2, false);
        $this->setDate(trim($arr['DD']), 'DD', true, false);
        $this->setTP(trim($arr['TP']));
        $this->setTD(trim($arr['TD']));
        $this->setSA(trim($arr['SA']));
        $this->setText(trim($arr['ON']), 'ON', false, 20, false);
        $this->setInt(trim($arr['VS']), 'VS', false, 10, '', false);
        $this->setDIC(trim($arr['VII']), 'VII', false);
        $this->setInt(trim($arr['INI']), 'INI', false, 8, '', false);
        $this->setDIC(trim($arr['VIR']), 'VIR', false);
        $this->setInt(trim($arr['INR']), 'INR', false, 8, '', false);
        $this->setDate(trim($arr['DUZP']), 'DUZP', false, false);
        $this->setDate(trim($arr['DPPD']), 'DPPD', false, false);
        $this->setDate(trim($arr['DT']), 'DT', false, false);
        $this->setAmount(trim($arr['TB0']), 'TB0', false, 18, 2, false);
        $this->setAmount(trim($arr['T0']), 'T0', false, 18, 2, false);
        $this->setAmount(trim($arr['TB1']), 'TB1', false, 18, 2, false);
        $this->setAmount(trim($arr['T1']), 'T1', false, 18, 2, false);
        $this->setAmount(trim($arr['TB2']), 'TB2', false, 18, 2, false);
        $this->setAmount(trim($arr['T2']), 'T2', false, 18, 2, false);
        $this->setAmount(trim($arr['NTB']), 'NTB', false, 18, 2, false);
        $this->setCC(trim($arr['CC']), 'CC', false);
        $this->setAmount(trim($arr['FX']), 'FX', false, 18, 3, false);
        $this->setInt(trim($arr['FXA']), 'FXA', false, 5, '1', false);
        $this->setText(trim($arr['X-SW']), 'X-SW', false, 30, false);
        $this->setAccount(trim($arr['ACC']), 'ACC', false, false);
        $this->setChecksum(trim($arr['CRC32']), 'CRC32', false);

        /*
         * otestujeme validitu klicu ACC a AM pro qrplatbu
         * pokud jsou validni, vygenerujeme qrp+f
         * pokud nejsou validni, vygenerujeme pouze qrf
         */
        //kontrola zda pozadujeme qrplatbu a zaroven qrplatba je validni
        if (trim($arr['qrplatba']) == '1' && $this->isValidQRPlatba()) {
            //je to  validni qrplatba

            $this->setIsQRPlatba(true); //vcetne qr platby

            //do qrplatby nacteme duplicitni parametry pro qrplatbu i qrfakturu - s delsi delkou nez v qrfakutre
            $this->setText(trim($arr['X-URL']), 'X-URL', false, 140, true);
            $this->setText(trim($arr['MSG']), 'MSG', false, 60, true);

            //nacteme klice pro QRPlatbu
            $this->setText(trim($arr['ALT-ACC']), 'ALT-ACC', false, 93, true);
            $this->setText(trim($arr['RF']), 'RF', false, 16, true);
            $this->setText(trim($arr['RN']), 'RN', false, 35, true);
            $this->setText(trim($arr['PT']), 'PT', false, 3, true);
            $this->setNT(trim($arr['NT']));
            $this->setText(trim($arr['NTA']), 'NTA', false, 320, true);
            $this->setXPer(trim($arr['X-PER']));
            $this->setInt(trim($arr['X-VS']), 'X-VS', false, 10, '', true);
            $this->setInt(trim($arr['X-SS']), 'X-SS', false, 10, '', true);
            $this->setInt(trim($arr['X-KS']), 'X-KS', false, 10, '', true);
            $this->setText(trim($arr['X-ID']), 'X-ID', false, 20, true);

            //$this->setAccount(trim($arr['ACC']), 'ACC', true, true); //ACC se nacita jiz v qrfakture
            //$this->setAmount(trim($arr['AM']), 'AM', false, 10, 2, true); //AM se nacita jiz v qrfakture
            //$this->setDate(trim($arr['DT']), 'DT', false, true); //ACC se nacita jiz v qrfakture
            //$this->setCC(trim($arr['CC']), 'CC', true); //CC se nacita jiz v qrfakture
            //$this->setText(trim($arr['X-URL']), 'X-URL', false, 140, true); //X-URL se nacita jiz v qrfakture
            //$this->setText(trim($arr['MSG']), 'MSG', false, 60, true); //MSG se nacita jiz v qrfakture
            //$this->setChecksum(trim($arr['CRC32']), 'CRC32', true); //CRC32 se nacita jiz v qrfakture

            $this->setKeys(); //nastavime klice pro qrp a qrf
        }
        else { //bud to neni qrplatba vubec nebo to neni validni qrplatba
            //todo otazka zda nevyhodit chybu kdyz integrace QRF a QRP selze kvuli nevalidite QRP
            $this->setIsQRPlatba(false);

            //nacteme parametry pro qrfakturu
            $this->setText(trim($arr['X-URL']), 'X-URL', false, 70, false);
            $this->setText(trim($arr['MSG']), 'MSG', false, 40, false);
        }

        //typ vystupu
        if (trim($arr['output']) == '1') {
            $this->outputFormat = 1; //text/plain
        }
        else if (trim($arr['output']) == '2') {
            $this->outputFormat = 2; //soubor application/x-shortinvoicedescriptor
        }
        else {
            $this->outputFormat = 0; //obrazek
        }

        //popis QR kódu
        if (trim($arr['branding']) == '0') {
            $this->branding = false;
        }
        else {
            $this->branding = true;
        }


        //velikost pixelu v qr obrazku
        $qrpixelsize = trim($arr['qrpixelsize']);
        if ($this->isInteger($qrpixelsize) && $qrpixelsize >= 1 && $qrpixelsize <= 20) {
            $this->QRSquareSize = $qrpixelsize; //velikost pixelu v obrazku
        }

        if (count($this->errDesc) > 0) { //nalezeny chyby pri validaci parametru
            //pri primem vystupu do prohlizece posilame i hlavicku
            if ($this->isDirectOutput) {
                //http_response_code(400);
                header('HTTP/1.1 400 Bad Request');
                header('Content-Type: application/json;charset=utf-8');
                header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Some time in the past
                header("Cache-Control: no-store, no-cache, must-revalidate");
            }
            return $this->printErrors();
        }
        else { //vse OK
            if ($this->outputFormat == 1) { //posilame jen text

                //pri primem vystupu do prohlizece posilame i hlavicku
                if ($this->isDirectOutput) {
                    header('Content-Type: text/plain;charset=utf-8');
                    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Some time in the past
                    header("Cache-Control: no-store, no-cache, must-revalidate");
                    http_response_code(200);
                }
                return $this->printQRCodeAsText();
            }
            else if ($this->outputFormat == 2) { //posilame soubor application/x-shortinvoicedescriptor

                //pri primem vystupu do prohlizece posilame i hlavicku
                if ($this->isDirectOutput) {
                    header('Content-Type: application/x-shortinvoicedescriptor;charset=utf-8');
                    header('Content-Description: File Transfer');
                    header('Content-Disposition: attachment; filename="'.self::SIND_FILENAME.'"');
                    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Some time in the past
                    header("Cache-Control: no-store, no-cache, must-revalidate");
                    http_response_code(200);
                }
                return $this->printQRCodeAsText();
            }
            else { //posilame obrazek

                //pri primem vystupu do prohlizece posilame i hlavicku
                if ($this->isDirectOutput) {
                    header('Content-Type: image/png');
                    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Some time in the past
                    header("Cache-Control: no-store, no-cache, must-revalidate");
                    http_response_code(200);
                }
                return $this->printQRCodeAsImage($this->QRText);
            }
        }
    }

    /**
     * kontrola na validitu qrplatby
     *
     * @return bool Vysledek validity
     */
    private function isValidQRPlatba()
    {
        if ((($this->keys['ACC'] != null && strlen($this->keys['ACC']) > 0 && $this->keys['AM'] != null && strlen($this->keys['AM']) <= 10 && $this->keys['AM'] > 0))) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * nastaveni flagu ze se jedna o QR Platbu a text na QRkodu
     *
     * @param bool $isQRPlatba Flag zda se jedna o QRPlatbu
     */
    private function setIsQRPlatba($isQRPlatba)
    {
        if ($isQRPlatba) {
            $this->QRText = 'QR Platba+F';
            $this->isQRPlatba = true;
        }
        else {
            $this->QRText = 'QR Faktura';
            $this->isQRPlatba = false;
        }
    }

    /**
     * nastaveni klicu pri platnosti QRPlatby i QRFaktury
     * provadi se pouze pokud jsou vybrane klice v qrfakture validni podle qrplatby
     */
    private function setKeys()
    {
        /*
        1. Žádná hodnota řetězce QR Faktury nesmí obsahovat znak ‘*‘, nebo skupinu znaků ‘%2A’.
        - to je zkontrolovano pri nacitani parametru z URL, protoze %2A se dekoduje na *, takze se automaticky vsechny vstupy zkontroluji na pritomnost *

        2. Klíče, které jsou shodné v obou formátech (jak QR Faktura, tak QR Platba), jsou z řetězce QR Faktury vyjmuty a vloženy do řetězce QR Platby.
        Zvláštním případem je klíč „VS“ (QR Faktura), který se změní na „X-VS“ (QR Platba).

        3. Zbytek řetězce QR Faktury je URL-kódován tak, že všechny znaky ‘*’ jsou nahrazeny skupinou znaků ‘%2A’, dle standardu QR Platby.

        4. Takto URL-kódovaný řetězec QR Faktury je pak do řetězce QR Platby vložen jako hodnota klíče „X-INV“.

        5. Klíč MSG se může vyskytovat jak v řetězci QR Platby (Zpráva pro příjemce), tak zároveň URL-kódovaný v řetězci QR Faktury (Textový popis předmětu fakturace).

        6. Takto vytvořený výsledný řetězec QR Platby musí být validní dle standardu QR Platby:
            - Musí obsahovat validní klíč „ACC“ (číslo účtu), který je v řetězci povinný.
            - Celková částka (klíč AM) musí být kladná a ne delší než 10 znaků.
        */


        //2. Klíče, které jsou shodné v obou formátech (jak QR Faktura, tak QR Platba), jsou z řetězce QR Faktury vyjmuty a vloženy do řetězce QR Platby.
        //klice jsou zkontrolovany a validni, protoze jinak by byly null
        //pokud klic v QRFakture existuje, dame ho do QRPlatby a v QRFakture ho vymazeme
        //pokud neexistuje, ponechame existujici klic z QRPlatby


        //ACC v qrfakture je validni, muzeme ho presunout do qrplatby
        $this->QRP_keys['ACC'] = $this->keys['ACC'];
        $this->keys['ACC'] = null;

        //AM v qrfakture je validni, muzeme ho presunout do qrplatby
        $this->QRP_keys['AM'] = $this->keys['AM'];
        $this->keys['AM'] = null;

        $this->QRP_keys['CC'] = $this->keys['CC'];
        $this->keys['CC'] = null;

        $this->QRP_keys['DT'] = $this->keys['DT'];
        $this->keys['DT'] = null;

        //5. Klíč MSG se může vyskytovat jak v řetězci QR Platby (Zpráva pro příjemce), tak zároveň URL-kódovaný v řetězci QR Faktury (Textový popis předmětu fakturace).
        //u qrplatby+f se msg nacita automaticky do qrplatby, protoze tam je delsi limit 60
        //a do qrfaktury se kopiruje jen za predpokladu, ze neni delsi nez 40
        if ($this->QRP_keys['MSG'] != null && strlen($this->QRP_keys['MSG']) <= 40) { //limit MSG u qrfaktury je  40
            $this->keys['MSG'] = $this->QRP_keys['MSG'];
        }

        //to same pro X-URL
        //u qrplatby+f se X-URL nacita automaticky do qrplatby, protoze tam je delsi limit 140
        //a do qrfaktury se kopiruje jen za predpokladu, ze neni delsi nez 70
        if ($this->QRP_keys['X-URL'] != null && strlen($this->QRP_keys['X-URL']) <= 70) { //limit X-URL u qrfaktury je  70
            $this->keys['X-URL'] = $this->QRP_keys['X-URL'];
        }

        //todo  zjistit zda jen v QRP nebo jen v QRF nebo v obou
        $this->QRP_keys['CRC32'] = $this->keys['CRC32']; //zkopirujeme crc32 z qrf do qrp+f
        $this->keys['CRC32'] = null;

        //Zvláštním případem je klíč „VS“ (QR Faktura), který se změní na „X-VS“ (QR Platba).
        if ($this->keys['VS'] != null && strlen($this->keys['VS']) > 0) {
            $this->QRP_keys['X-VS'] = $this->keys['VS'];
            $this->keys['VS'] = null;
        }

        //3. Zbytek řetězce QR Faktury je URL-kódován tak, že všechny znaky ‘*’ jsou nahrazeny skupinou znaků ‘%2A’, dle standardu QR Platby.
        //4. Takto URL-kódovaný řetězec QR Faktury je pak do řetězce QR Platby vložen jako hodnota klíče „X-INV“.
        $this->QRP_keys['X-INV'] = $this->encodeQRFaktura();
    }

    /**
     * Nastavení identifikace kanalu QRPlatby
     *
     * @param string $typ Typ kanalu
     */
    private function setNT($typ)
    {
        if (!isset($typ) || is_null($typ) || strlen($typ) == 0) //promenna je prazdna
        {
            //nedavame NT do QR kodu
        }
        else if (!in_array($typ, $this->rangeNT)) { //mimo rozsah
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota NT='.$typ.' nemá požadovanou hodnotu z množiny hodnot');
        }
        else {
            $this->QRP_keys['NT'] = $typ; //vyplneno OK
        }
    }

    /**
     * Nastavení typu plneni
     *
     * @param string $typ Typ plneni
     */
    private function setTP($typ)
    {
        if (!isset($typ) || is_null($typ) || strlen($typ) == 0) //promenna je prazdna
        {
            $this->keys['TP'] = $this->rangeTP['0']; //nastavime 0
        }
        else if (!in_array($typ, $this->rangeTP)) { //mimo rozsah
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota TP='.$typ.' nemá požadovanou hodnotu z množiny hodnot');
        }
        else {
            $this->keys['TP'] = $typ; //vyplneno OK
        }
    }


    /**
     * Nastavení typu dokladu
     *
     * @param string $typ Typ dokladu
     */
    private function setTD($typ)
    {
        if (!isset($typ) || is_null($typ) || strlen($typ) == 0) //promenna je prazdna
        {
            $this->keys['TD'] = $this->rangeTD['9']; //nastavime 9
        }
        else if (!in_array($typ, $this->rangeTD)) { //mimo rozsah
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota TD='.$typ.' nemá požadovanou hodnotu z množiny hodnot');
        }
        else {
            $this->keys['TD'] = $typ; //vyplneno OK
        }
    }

    /**
     * Nastavení zuctovani zaloh
     *
     * @param string $typ Typ zuctovani
     */
    private function setSA($typ)
    {
        if (!isset($typ) || is_null($typ) || strlen($typ) == 0) //promenna je prazdna
        {
            $this->keys['SA'] = $this->rangeSA['0']; //nastavime 0
        }
        else if (!in_array($typ, $this->rangeSA)) { //mimo rozsah
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota SA='.$typ.' nemá požadovanou hodnotu z množiny hodnot');
        }
        else {
            $this->keys['SA'] = $typ; //vyplneno OK
        }
    }



    /**
     * Nastavení meny
     *
     * @param string $mena
     * @param string $key Klic, do ktereho se mena priradi
     * @param bool $isQRPlatba Flag zda se jedna o QRPlatbu
     */
    private function setCC($mena, $key, $isQRPlatba)
    {
        $len = 3;
        if (!isset($mena) || is_null($mena) || strlen($mena) == 0) //promenna je prazdna
        {
            $this->keys[$key] = 'CZK'; //nastavime CZK
        }
        else if (strlen($mena) != $len) {
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_TOO_LONG, self::ERR_DESC=>'Hodnota '.$key.'='.$mena.' neobsahuje právě '.$len.' znaky');
        }
        else if (!preg_match("/^[A-Z]+$/", $mena)) { //kontrola na znaky A-Z
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota '.$key.'='.$mena.' obsahuje jiné znaky než [A-Z]');
        }
        else {
            if ($isQRPlatba) {
                $this->QRP_keys[$key] = $mena;
            }
            else {
                $this->keys[$key] = $mena;
            }
        }
    }



    /**
     * Nastavení DIC
     *
     * @param string $dic DIC
     * @param string $key Klic, do ktereho se DIC priradi
     * @param bool $required Flag zda je parametr povinny
     */
    private function setDIC($dic, $key, $required)
    {
        $MAXLEN = 14;
        if (!isset($dic) || is_null($dic) || strlen($dic) == 0) //promenna je prazdna
        {
            if ($required) {
                $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_NOT_EXIST, self::ERR_DESC=>'Hodnota '.$key.' neexistuje');
            }
            else {
                //nedavame DIC do QR kodu
            }
        }
        else if (strlen($dic) > $MAXLEN) {
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_TOO_LONG, self::ERR_DESC=>'Hodnota '.$key.'='.$dic.' je delší než '.$MAXLEN.' znaků');
        }
        else if (!preg_match("/^[a-zA-Z0-9]+$/", $dic)) { //kontrola na znaky a-zA-Z0-9
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota '.$key.'='.$dic.' obsahuje jiné znaky než [a-z], [A-Z] a [0-9]');
        }
        else {
            $this->keys[$key] = $this->stripDiacritics($dic);
        }
    }


    /**
     * Nastavení celeho cisla
     *
     * @param int $nr Cele cislo
     * @param string $key Klic, do ktereho se hodnota priradi
     * @param bool $required Flag zda je parametr povinny
     * @param int $maxlen Maximalni delka celeho cisla - retezce
     * @param string $defaultValue Defaultni hodnota, pokud neni zadan $nr
     * @param bool $isQRPlatba Flag zda se jedna o QRPlatbu
     */
    private function setInt($nr, $key, $required, $maxlen, $defaultValue, $isQRPlatba)
    {
        if (!isset($nr) || is_null($nr) || strlen($nr) == 0) //promenna je prazdna
        {
            if ($required) {
                $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_NOT_EXIST, self::ERR_DESC=>'Hodnota '.$key.' neexistuje');
            }
            else {
                if (strlen($defaultValue) > 0) //mame nastavit defaultni hodnotu
                {
                    $this->keys[$key] = $defaultValue;
                }
                else {
                    //nedavame cislo do QR kodu
                }
            }
        }
        else if (strlen($nr) > $maxlen) {
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_TOO_LONG, self::ERR_DESC=>'Hodnota '.$key.'='.$nr.' je delší než '.$maxlen.' znaků');
        }
        else if (!$this->isInteger($nr)) {
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota '.$key.'='.$nr.' není celé číslo');
        }
        else {
            if ($isQRPlatba) {
                $this->QRP_keys[$key] = $nr;
            }
            else {
                $this->keys[$key] = $nr;
            }
        }
    }

    /**
     * Nastavení pocet dni opakovani platby
     *
     * @param int $nr Cele cislo 0-30
     */
    private function setXPer($nr)
    {
        $maxlen = 2;
        if (!isset($nr) || is_null($nr) || strlen($nr) == 0) //promenna je prazdna
        {
               //nedavame cislo do QR kodu
        }
        else if (strlen($nr) > $maxlen) {
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_TOO_LONG, self::ERR_DESC=>'Hodnota X-PER='.$nr.' je delší než '.$maxlen.' znaků');
        }
        else if (!$this->isInteger($nr)) {
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota X-PER='.$nr.' není celé číslo');
        }
        else if ($nr <0 || $nr > 30) {
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota X-PER='.$nr.' není v rozmezí 0-30');
        }
        else {
            $this->QRP_keys['X-PER'] = $nr;
        }
    }

    /**
     * Nastavení libovolneho textu
     *
     * @param string $text Prirazovana hodnota
     * @param string $key Klic, do ktereho se hodnota priradi
     * @param bool $required Flag zda je parametr povinny
     * @param int $maxlen Maximalni delka celeho cisla - retezce
     * @param bool $isQRPlatba Flag zda se jedna o QRPlatbu
     */
    private function setText($text, $key, $required, $maxlen, $isQRPlatba)
    {
        if (!isset($text) || is_null($text) || strlen($text) == 0) //promenna je prazdna
        {
            if ($required) {
                $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_NOT_EXIST, self::ERR_DESC=>'Hodnota '.$key.' neexistuje');
            }
            else {
                //nedavame text do QR kodu
            }
        }
        else if (strlen($text) > $maxlen) {
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_TOO_LONG, self::ERR_DESC=>'Hodnota '.$key.'='.$text.' je delší než '.$maxlen.' znaků');
        }
        else if ($this->containsStar($text)) { //kontrola na znak *
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota '.$key.'='.$text.' obsahuje znak *');
        }
        else {
            if ($isQRPlatba) {
                $this->QRP_keys[$key] = $this->stripDiacritics($text);
            }
            else {
                $this->keys[$key] = $this->stripDiacritics($text);
            }
        }
    }

    /**
     * Nastavení CRC32
     *
     * @param string $crc CRC32
     * @param string $key Klic, do ktereho se hodnota priradi
     * @param bool $isQRPlatba Flag zda se jedna o QRPlatbu
     */
    private function setChecksum($crc, $key, $isQRPlatba)
    {
        $MAXLEN = 8;
        if (!isset($crc) || is_null($crc) || strlen($crc) == 0) //promenna je prazdna
        {
                //nedavame CRC do QR kodu
        }
        else if (strlen($crc) != $MAXLEN) {
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_TOO_LONG, self::ERR_DESC=>'Hodnota '.$key.'='.$crc.' má jinou délku než '.$MAXLEN.' znaků');
        }
        else if (!preg_match("/^[A-F0-9]+$/", $crc)) { //kontrola na znaky A-F0-9
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota '.$key.'='.$crc.' obsahuje jiné znaky než [A-F] a [0-9]');
        }
        else {
            if ($isQRPlatba) {
                $this->QRP_keys[$key] = $crc;
            }
            else {
                $this->keys[$key] = $crc;
            }
        }
    }

    /**
     * Nastavení data
     *
     * @param string $date Datum
     * @param string $key Klic, do ktereho se hodnota priradi
     * @param bool $required Flag zda je parametr povinny
     * @param bool $isQRPlatba Flag zda se jedna o QRPlatbu
     */
    private function setDate($date, $key, $required, $isQRPlatba)
    {
        if (!isset($date) || is_null($date) || strlen($date) == 0) //promenna je prazdna
        {
            if ($required) {
                $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_NOT_EXIST, self::ERR_DESC=>'Hodnota '.$key.' neexistuje');
            }
            else {
                //nedavame datum do QR kodu
            }
        }
        else if (!$this->isValidDate($date)) { //chybny format datumu
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota '.$key.'='.$date.' není datum ve formátu YYYYMMDD');
        }
        else {
            if ($isQRPlatba) {
                $this->QRP_keys[$key] = $date;
            }
            else {
                $this->keys[$key] = $date;
            }
        }
    }



    /**
     * Nastavení castky
     *
     * @param float $amount Castka
     * @param string $key Klic, do ktereho se hodnota priradi
     * @param bool $required Flag zda je parametr povinny
     * @param int $maxlen Maximalni delka desetinneho cisla - retezce
     * @param int $decimalPlaces Pocet desetinnych mist
     * @param bool $isQRPlatba Flag zda se jedna o QRPlatbu
     */
    private function setAmount($amount, $key, $required, $maxlen, $decimalPlaces, $isQRPlatba)
    {
        if (!isset($amount) || is_null($amount) || strlen($amount) == 0) //promenna je prazdna
        {
            if ($required) {
                $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_NOT_EXIST, self::ERR_DESC=>'Hodnota '.$key.' neexistuje');
            }
            else {
                //nedavame castku do QR kodu
            }
        }
        else if (strlen($amount) > $maxlen) {
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_TOO_LONG, self::ERR_DESC=>'Hodnota '.$key.'='.$amount.' je delší než '.$maxlen.' znaků');
        }
        else if (!$this->isFloat($amount)) {
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota '.$key.'='.$amount.' není číslo');
        }
        else {
            try {
                if ($isQRPlatba) {
                    $this->QRP_keys[$key] = sprintf('%.'.$decimalPlaces.'f', $amount); //prevod na float
                }
                else {
                    $this->keys[$key] = sprintf('%.'.$decimalPlaces.'f', $amount); //prevod na float
                }
            }
            catch(\Exception $e) {
                $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Chyba při převodu hodnoty '.$key.'='.$amount.' na desetinné číslo: '.$e->getMessage());
            }
        }
    }



    /**
     * Nastavení čísla účtu ve formátu IBAN
     *
     * @param string $account Cislo uctu
     * @param string $key Klic, do ktereho se hodnota priradi
     * @param bool $required Flag zda je parametr povinny
     * @param bool $isQRPlatba Flag zda se jedna o QRPlatbu
     */
    private function setAccount($account, $key, $required, $isQRPlatba)
    {
        $MAXLEN = 46;
        if (!isset($account) || is_null($account) || strlen($account) == 0) //promenna je prazdna
        {
            if ($required) {
                $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_NOT_EXIST, self::ERR_DESC=>'Hodnota '.$key.' neexistuje');
            }
            else {
                //nedavame ucet do QR kodu
            }
        }
        else if (strlen($account) > $MAXLEN) {
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_TOO_LONG, self::ERR_DESC=>'Hodnota '.$key.'='.$account.' je delší než '.$MAXLEN.' znaků');
        }
        else if (!$this->isValidIBAN($account)) {
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota '.$key.'='.$account.' není číslo účtu ve formátu IBAN');
        }
        else if ($this->containsStar($account)) { //kontrola na znak *
            $this->errDesc[] = array(self::ERR_CODE=>self::ERR_CODE_BAD_FORMAT, self::ERR_DESC=>'Hodnota '.$key.'='.$account.' obsahuje znak *');
        }
        else {
            if ($isQRPlatba) {
                $this->QRP_keys[$key] = $account;
            }
            else {
                $this->keys[$key] = $account;
            }
        }
    }

    /**
     * Kontrola IBAN
     *
     * @param string $acc Cislo uctu
     * @return bool Zda je IBAN validní
     */
    private function isValidIBAN($acc)
    {
        //dalsi moznosti kontroly:
        //https://github.com/globalcitizen/php-iban
        //http://monshouwer.org/code-snipets/check-iban-bank-account-number-in-php/
        //https://github.com/cmpayments/iban

        //zda IBAN obsahuje i SWIFT ve formatu IBAN+SWIFT
        $posOfPlus = strpos($acc, '+');
        if ($posOfPlus !== false) //obsahuje SWIFT
        {
            //SWIFT docasne odstranime a pouzijeme ke kontrole pouze IBAN
            $iban = substr($acc, 0, $posOfPlus);
        }
        else { //bez SWIFT
            $iban = $acc;
        }

        $iban = strtolower(str_replace(' ','',$iban));
        $Countries = array('al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24);
        $Chars = array('a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35);

        if(strlen($iban) == $Countries[substr($iban,0,2)]){

            $MovedChar = substr($iban, 4).substr($iban,0,4);
            $MovedCharArray = str_split($MovedChar);
            $NewString = "";

            foreach($MovedCharArray AS $key => $value){
                if(!is_numeric($MovedCharArray[$key])){
                    $MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
                }
                $NewString .= $MovedCharArray[$key];
            }

            if(bcmod($NewString, '97') == 1)
            {
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    /**
     * Metoda  zakoduje retezec QR faktury tak, ze všechny znaky ‘*’ jsou nahrazeny skupinou znaků ‘%2A’, dle standardu QR Platby.
     * A mezi klíčem a hodnotou je znak :
     *
     * @return string Zakodovaný řetězec QRFaktury
     */
    private function encodeQRFaktura()
    {
        $chunks = array('SID', self::VERSION);
        foreach ($this->keys as $key => $value) {
            //vezmeme pouze klice QRFaktury
            if ($value === null) {
                continue;
            }
            $chunks[] = $key . ":" . $value;
        }
        return implode('%2A', $chunks);
    }


    /**
     * Metoda vrátí QR retezec jako textový řetězec.
     * Dle specifikace jsou jednotlivé části odděleny znakem *
     * A mezi klíčem a hodnotou je znak :
     *
     * @return string  Zakodovaný řetězec
     */
    public function __toString()
    {
        $tmpkeys = null;
        if ($this->isQRPlatba) { //qr faktura + platba
            $chunks = array('SPD', self::QRP_VERSION);
            $tmpkeys = $this->QRP_keys; //klice qrplatby
        }
        else { //pouze qr faktura
            $chunks = array('SID', self::VERSION);
            $tmpkeys = $this->keys; //klice qrfaktury
        }

        foreach ($tmpkeys as $key => $value) {
            if ($value === null) {
                continue;
            }
            $chunks[] = $key . ":" . $value;
        }
        return implode('*', $chunks);
    }

    
    /**
     * Kontrola datumu YYYYMMDD
     *
     * @param string $date Datum
     * @return bool Zda je datum ve formátu YYYMMDD
     */
    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Ymd', $date);
        return $d && $d->format('Ymd') === $date;
    }

    /**
     * Kontrola na integer, akceptuje i uvodni nuly
     *
     * @param string $string Celé číslo
     * @return bool Zda je vstup celé číslo
     */
    private function isInteger($string)
    {
        /*$number = filter_var($string, FILTER_VALIDATE_INT);
        return ($number !== FALSE);*/
        return ctype_digit($string);
    }

    /**
     * Kontrola zda string obsahuje *
     *
     * @param string $string Řetězec
     * @return bool Zda vstup obsahuje *
     */
    private function containsStar($string)
    {
        if (strpos($string, '*') !== false) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Kontrola na float
     *
     * @param string $string Desetinné číslo
     * @return bool Zda je vstup desetinné číslo
     */
    private function isFloat($string)
    {
        $number = filter_var($string, FILTER_VALIDATE_FLOAT);
        return ($number !== FALSE);
    }

    /**
     * Odstranění diaktitiky.
     *
     * @param string $string Řetězec s diakritikou
     * @return string Řetězec bez diakritiky
     */
    private function stripDiacritics($string)
    {
        //pokud je pozadavek na kompresi s oriznutim diakritiky
        if ($this->compress) {
            $string = str_replace(
                array('ě','š','č','ř','ž','ý','á','í','é','ú','ů','ó','ť','ď','ľ','ň','ŕ','â','ă','ä','ĺ','ć','ç','ę','ë','î','ń','ô','ő','ö','ů','ű','ü','*',),
                array('e','s','c','r','z','y','a','i','e','u','u','o','t','d','l','n','a','a','a','a','a','a','c','e','e','i','n','o','o','o','u','u','u','',),
                $string
            );
            $string = str_replace(
                array('Ě','Š','Č','Ř','Ž','Ý','Á','Í','É','Ú','Ů','Ó','Ť','Ď','Ľ','Ň','Ä','Ć','Ë','Ö','Ü',),
                array('E','S','C','R','Z','Y','A','I','E','U','U','O','T','D','L','N','A','C','E','O','U',),
                $string
            );
        }
        return $string;
    }

    /**
     * Metoda vrátí pole chyb jako json
     *
     * @return string JSON zakodované pole chyb
     */
    private function printErrors()
    {
        $errArray = array('description'=>'Chybná vstupní data', 'errors'=>$this->errDesc);
        return json_encode($errArray, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Metoda vrátí QR kód jako plaintext.
     *
     * @return string Qr kód
     */
    private function printQRCodeAsText()
    {
       return (string)$this;
    }

    /**
     * Metoda vrátí QR kód jako soubor application/x-shortinvoicedescriptor
     *
     * @return string  Qr kód
     */
    /*private function printQRCodeAsFile($filename)
    {
        echo (string)$this;
    }*/

    /**
     * Metoda vrátí QR kód jako PNG obrazek
     *
     * @return mixed raw image stream
     */
    private function printQRCodeAsImage()
    {

        /* --- ukladani obrazku do docasneho souboru a opetovne nacitani kvuli zapisu textu */
        /*
        while (true) { //najdeme volne jmeno docasneho souboru
            $filename = '.'.DIRECTORY_SEPARATOR.'tmpfiles'.DIRECTORY_SEPARATOR.uniqid('QR_', true) . '.png';
            if (!file_exists($filename)) break;
        }

        //ulozime obrazek do docasneho souboru
        \QRcode::png((string)$this, $filename, QR_ECLEVEL_L, $this->QRSquareSize, 8);
        */

        $imageStringFinal = null;
        if ($this->branding)
        {
            //napiseme do obrazku typ QR kodu

            /* --- ukladani obrazku do streamu bez zapisu do docasneho souboru  */
            ob_start();
            \QRcode::png((string)$this, null/*do streamu*/, QR_ECLEVEL_L/*uroven korence chyb*/, $this->QRSquareSize/*velikost pixelu*/, 10/*velikost okraje*/);
            $imageString = ob_get_contents();
            ob_end_clean();
            $im = imagecreatefromstring($imageString);
            $size = getimagesizefromstring($imageString);
            $black = imagecolorallocate($im, 0, 0, 0);

            // Print Text On Image
            //putenv('GDFONTPATH=' . realPath('fonts'));
            //text se pise do spodniho okraje kousek od leveho spodniho rohu
            if ($this->QRSquareSize > 15) {
                //pokud uz je moc velky obrazek, musime text posunout dolu a mensit
                $textStartY = $size[1]*0.98;
                $fontSize = $this->QRSquareSize*4;
            }
            else if ($this->QRSquareSize > 12) {
                //pokud uz je moc velky obrazek, musime text posunout dolu a mensit
                $textStartY = $size[1]*0.975;
                $fontSize = $this->QRSquareSize*4.5;
            }
            else {
                $textStartY = $size[1]*0.970;
                $fontSize = $this->QRSquareSize*5;
            }
            imagettftext($im, $fontSize/*fontsize*/, 0, $size[0]/6/*x*/, $textStartY/*y*/, $black, dirname(__FILE__) . DIRECTORY_SEPARATOR . 'font.ttf', $this->QRText);

            //print lines on image
            $linemargin = $size[0]*0.06; //zacatky a konce car od okraju obrazku

            //horizontalni cara nahore
            imageline($im, 0 + $linemargin, 0 + $linemargin, $size[0] - $linemargin, 0 + $linemargin, $black);
            //vertikalni cara vlevo
            imageline($im, 0 + $linemargin, 0 + $linemargin, 0 + $linemargin, $size[1] - $linemargin, $black);
            //vertikalni cara vpravo
            imageline($im, $size[0] - $linemargin, 0 + $linemargin , $size[0] - $linemargin, $size[1] - $linemargin, $black);
            //horizontalni cara dole vlevo od napisu
            imageline($im, 0 + $linemargin, $size[1] - $linemargin, 0 + $linemargin*2.0, $size[1] - $linemargin, $black);
            //horizontalni cara dole vpravo od napisu
            if ($this->QRSquareSize > 18) {
                $rightLineStartX = $size[0]/1.4 + $linemargin; //pokud uz je moc velky text, musime pravou caru zkratit
            }
            else if ($this->QRSquareSize > 14) {
                $rightLineStartX = $size[0]/1.5 + $linemargin; //pokud uz je moc velky text, musime pravou caru zkratit
            }
            else {
                $rightLineStartX = $size[0]/1.55 + $linemargin;
            }
            imageline($im, $rightLineStartX, $size[1] - $linemargin, $size[0] - $linemargin, $size[1] - $linemargin, $black);


            //neposilame obrazek primo do prihlizece, ale ulozime do stringu a ten vratime
            ob_start();
            imagepng($im);
            $imageStringFinal = ob_get_contents();
            ob_end_clean();

            imagedestroy($im);
        }
        else { //bez popisu
            ob_start();
            \QRcode::png((string)$this, null/*do streamu*/, QR_ECLEVEL_L/*uroven korence chyb*/, $this->QRSquareSize/*velikost pixelu*/, 8/*velikost okraje*/);
            $imageStringFinal = ob_get_contents();
            ob_end_clean();
        }
        return $imageStringFinal;
    }
}
?>