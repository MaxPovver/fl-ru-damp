<?php


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/profiler.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reqv.php');
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');

//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/YandexMoney3/Array2XML.php');


//------------------------------------------------------------------------------


$results = array();
//$profiler = new profiler();


//------------------------------------------------------------------------------


/*
AgentID=200385
https://bo-demo02.yamoney.ru:9094/
 */

/*
 https://server:port/webservice/deposition/api/testDeposition

Адрес операции зачисления перевода: 
https://server:port/webservice/deposition/api/makeDeposition

 */

/*
$params = array(
    '@attributes' => array(
    
    'agentId' => 200385,
    'clientOrderId' => 272517,
    'requestDT' => '2013-04-12T00:01:54.000Z',
    ),

        'paymentParams' => array(
            'skr_destinationCardSynonim' => '79052075556'
        )
);



$converter = new Array2XML();
$converter->setTopNodeName('testDepositionRequest');
$converter->importArray($params);

print_r($converter->saveXml());
exit;
*/


/*
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><testDepositionRequest/>');
$xml->addAttribute('agentId', 200385);

var_dump($xml->asXML());
exit;
*/





$url = 'https://bo-demo02.yamoney.ru:9094/webservice/deposition/api/testDeposition';


$xml_str = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<testDepositionRequest agentId="200385"
                       clientOrderId="272517"
                       requestDT="2013-04-12T00:01:54.000Z"
                       dstAccount="25700130535186"
                       amount="249.00"
                       currency="10643"
                       contract="">
         <paymentParams>
                  <skr_destinationCardSynonim>79052075556</skr_destinationCardSynonim>
                  <pdr_firstName>Владимир</pdr_firstName>
                  <pof_offerAccepted>1</pof_offerAccepted> 
                  <pdr_secondName>Владимирович</pdr_secondName>
                  <pdr_lastName>Владимиров</pdr_lastName>
                  <cps_phoneNumber>79052075556</cps_phoneNumber>
                  <pdr_docType>21</pdr_docType>
                  <pdr_docNum>4002109067</pdr_docNum>
                  <pdr_postcode>194044</pdr_postcode>
                  <pdr_country>Санкт-Петербург</pdr_country>
                  <pdr_city></pdr_City>
                  <pdr_address>Большой пр, ПС, д.12</pdr_address>
         </paymentParams>
</testDepositionRequest>
XML;

$xml_str = iconv('CP1251', 'UTF-8', $xml_str);


/*
file_put_contents("msg.txt", $xml_str);

$headers_msg = array();
$pubkey = file_get_contents("certnew_Vaan.cer");
$res = openssl_pkcs7_encrypt("msg.txt", "enc.txt", $pubkey, $headers_msg);

//var_dump($res);

$data = file_get_contents("enc.txt");
$parts = explode("\n\n", $data, 2);
*/

//certnew_Vaan – клиентский сертификат, им вы авторизуетесь у нас и подписываете им свои запросы.
//Depositdemo – сертификат для валидации наших ответов о результатах выполнения операции.

//private.key
//tkaevient2014

$descriptorspec = array(
            0 => array("pipe", "r"), // stdin is a pipe that the child will read from
            1 => array("pipe", "w"), // stdout is a pipe that the child will write to
            2 => array("pipe", "w")); // stderr is a file to write to

$process = proc_open('openssl smime -sign -signer certnew_Vaan.cer -inkey private.key -nochain -nocerts -outform PEM -nodetach',$descriptorspec, $pipes);

if (is_resource($process)) {
    
    fwrite($pipes[0], 'tkaevient2014');
    fwrite($pipes[0], "\n");
    fwrite($pipes[0], $xml_str);
    fclose($pipes[0]);
    
    $execResult = stream_get_contents($pipes[1]);
    $execErrors =  stream_get_contents($pipes[2]);
    fclose($pipes[1]);

    $return_value = proc_close($process);
    
    var_dump($execResult);
    
}

exit;

/*
        $process = proc_open(
            'openssl smime -sign -signer ' . $certificate .
            ' -inkey ' . $privkey .
            ' -nochain -nocerts -outform PEM -nodetach',
            $descriptorspec, $pipes);
*/


//openssl smime -sign -signer certnew_Vaan.cer -nochain -nocerts -outform PEM -nodetach
//openssl.exe smime -sign -signer public.pem -inkey private.pem -nochain -nocerts -outform PEM -nodetach





$request = new HTTP_Request2($url);

//$request->setMethod(HTTP_Request2::METHOD_POST);



/*
$request->setConfig(array(
    'ssl_verify_peer'   => FALSE,
    'ssl_verify_host'   => FALSE
    
    //'ssl_verify_peer' => TRUE,
    //'ssl_local_cert' => 'ym.p7b'
));
*/

$request->setHeader($parts[0]);
$request->setBody($parts[1]);


//$request->addUpload($url, $headers_msg, $sendFilename, $contentType);

//$request->s

//$request->getHeaders();

$response = $request->send();


var_dump($response->getStatus());


/*
$request->setMethod(HTTP_Request2::METHOD_POST)
    ->addPostParameter('username', 'vassily')
    ->addPostParameter(array(
        'email' => 'vassily.pupkin@mail.ru',
        'phone' => '+7 (495) 123-45-67'
    ))
    ->addUpload('avatar', './exploit.exe', 'me_and_my_cat.jpg', 'image/jpeg');

*/


//$test = new COM();

//print_r($xml_str);
//exit;

//$infile = dirname(__FILE__) . "/cert.crt";

//var_dump(openssl_pkcs7_encrypt($infile, $outfile, $single_cert, $empty_headers));


//$outfile = tempnam(b"/tmp", b"ssl");




//var_dump(function_exists('openssl_pkcs7_decrypt'));






/*
$uid = 33;

$reqvs = sbr_meta::getUserReqvs($uid);
$reqv = $reqvs[$reqvs['form_type']];

//Проверка наличия резерва средств
$reserve_id = 3;
$reserveInstance = ReservesModelFactory::getInstanceById($reserve_id);
$reserve_data = $reserveInstance->getReserveData();

$sum = $reserve_data['reserve_price'];
$sum = ($sum < 10 ? 10 : $sum);
$reqv['price'] = $sum;

$reserveInstance->getReservesBank()->generateInvoice($reqv);
*/



//------------------------------------------------------------------------------

//$profiler->start('fill_frl_mem');

//------------------------------------------------------------------------------




//------------------------------------------------------------------------------

//$profiler->stop('fill_frl_mem');

//------------------------------------------------------------------------------


//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;