<?php
define('NO_CSRF', true);
//header('Content-Type: text/plain; charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/wm_payments.php');
$req = new SimpleXMLElement(file_get_contents('php://input'));

echo '<?xml version="1.0"?>';
if(!$req->payment->date) {
?>
<w3s.response>
<retval>0</retval>  
<retdesc>UKG1</retdesc>
<payment>
 <name><?=$req->payment->name?></name>
 <passport_serie><?=$req->payment->passport_serie?></passport_serie>
 <passport_number><?=$req->payment->passport_number?></passport_number>
 <passport_date><?=$req->payment->passport_date?></passport_date>
 <price><?=$req->payment->price?></price>
 <purse><?=$req->payment->purse?></purse>
  <limit>10000</limit>
</payment>
</w3s.response>
<? } else { ?>
<w3s.response>
<retval>0</retval>  
<retdesc>UKG2</retdesc>
<payment>
 <name><?=$req->payment->name?></name>
 <passport_serie><?=$req->payment->passport_serie?></passport_serie>
 <passport_number><?=$req->payment->passport_number?></passport_number>
 <passport_date><?=$req->payment->passport_date?></passport_date>
 <price><?=$req->payment->price?></price>
 <purse><?=$req->payment->purse?></purse>
 <rest>40000</rest>
 <cheque><?=$req->payment->cheque?></cheque>
 <date><?=$req->payment->date?></date>
 <kiosk_id><?=$req->payment->kiosk_id?></kiosk_id>
 <phone><?=$req->payment->phone?></phone>
 <wmtranid><?=mt_rand(1, 1000)?></wmtranid>
 <dateupd><?=gmdate('Ymd H:i:s')?></dateupd>
</payment>
</w3s.response>
<? } ?>


<?
/*

<payment>
 <name><?=$req->payment->name?></name>
 <passport_serie><?=$req->payment->passport_serie?></passport_serie>
 <passport_number><?=$req->payment->passport_number?></passport_number>
 <passport_date><?=$req->payment->passport_date?></passport_date>
 <price><?=$req->payment->price?></price>
 <purse><?=$req->payment->purse?></purse>
 <rest>40000</rest>
 <cheque><?=$req->payment->cheque?></cheque>
 <date><?=$req->payment->date?></date>
 <kiosk_id><?=$req->payment->kiosk_id?></kiosk_id>
 <phone><?=$req->payment->phone?></phone>
 <wmtranid><?=mt_rand(1, 1000)?></wmtranid>
 <dateupd><?=gmdate('Ymd H:i:s')?></dateupd>
</payment>

*/

?>


