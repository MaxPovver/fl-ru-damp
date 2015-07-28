<?php

require_once $_SERVER['DOCUMENT_ROOT']."/classes/payment_keys.php";

define ('IFREE_URL',  'http://beta.free-lance.ru/income/ifree.php');
define ('AUTH_LOGIN', 'freelance');
define ('AUTH_PASS',  '4vbirfhgbitkdljv\'7');
define ('SECRET_KEY', IFREE_KEY);


function strip_magic_quotes($arr) {
	foreach ($arr as $k => $v) {
		if (is_array($v)) {
			$arr[$k] = strip_magic_quotes($v);
		} else {
			$arr[$k] = stripslashes($v);
		}
	}
	return $arr;
}

if (get_magic_quotes_gpc()) {
	if (!empty($_GET)) $_GET = strip_magic_quotes($_GET);
	if (!empty($_POST)) $_POST = strip_magic_quotes($_POST);
	if (!empty($_COOKIE)) $_COOKIE = strip_magic_quotes($_COOKIE);
	if (!empty($_FILES)) $_FILES = strip_magic_quotes($_FILES);
}

if (!empty($_POST)) {

    $time = @mktime();
    
    $q = array (
        'evtId' => $_POST['evtId'],
        'phone' => $_POST['phone'],
        'abonentId' => $_POST['abonentId'],
        'country' => $_POST['country'],
        'serviceNumber' => $_POST['serviceNumber'],
        'operator' => $_POST['operator'],
        'smsText' => base64_encode($_POST['smsText']),
        'now' => @date('Ymdhis', $time),
        'md5key' => md5($_POST['serviceNumber'] . base64_encode($_POST['smsText']) . $_POST['country'] . $_POST['abonentId'] . SECRET_KEY . @date('Ymdhis', $time))
    );

    $query = '';
    foreach ($q as $key=>$val) $query .= '&'.$key.'='.urlencode($val);
    $url = IFREE_URL.'?'.substr($query, 1);
    if (AUTH_LOGIN) {
		$context = stream_context_create(array(
			'http' => array('header' => 'Authorization: Basic '.base64_encode(AUTH_LOGIN.':'.AUTH_PASS).PHP_EOL)
		));
		$result = file_get_contents($url, FALSE, $context);
    } else {
		$result = file_get_contents($url);
	}
	
	
	if ($result) {
		if (preg_match("/\<ErrorText\>\<\!\[CDATA\[(.*?)\]\]\>\<\/ErrorText\>/", $result, $o)) {
			$result = array('status'=>'error', 'xml'=>$result, 'sms'=>$o[1]);
		} else if (preg_match("/\<SmsText\>\<\!\[CDATA\[(.*?)\]\]\>\\<\/SmsText\>/", $result, $o)) {
			$result = array('status'=>'ok', 'xml'=>$result, 'sms'=>$o[1]);
		} else {
			$result = array('status'=>'critical', 'xml'=>$result, 'sms'=>'');
		}
	}
    
} else {

    $result = '';
    
}

header("Content-Type: text/html; charset=utf-8", FALSE);

?><HTML>

<HEAD>
    <title>I-Free Test</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        .caption { font: bold 14px Arial; background-color: #075CC6; color: white; }
		.label { font: normal 12px Tahoma; vertical-align: top; }
        INPUT, SELECT, TEXTAREA { font: normal 12px Tahoma; width: 200px; }
        .button { padding-top: 10px }
        .button INPUT { width: 80px; }
		.help { font: normal 11px Verdana; background-color: #F5F5F5; padding-right: 4px;}
		H3 { color: red; }
		H4 { margin: 1px; padding: 1px; font: bold 14px Tahoma; }
		.sms-answer { font: normal 12px Verdana; padding: 3px; }
    </style>
</HEAD>

<BODY>

<? if ($result && $result['status'] == 'critical') { ?>

<h3>Ошибка при работе сервиса!</h3>
<pre><?=$result['xml']?></pre>

<p>&nbsp;</p>

<? } else if ($result) { ?>

<h4>Ответная смс:</h4>
<div class="sms-answer" style="color: <?=(($result['status']=='ok')? 'green': 'red')?>">
	<span><?=htmlspecialchars($result['sms'])?></span>
</div>

<br>

<h4>В XML формате:</h4>
<div class="sms-answer">
	<span><?=htmlspecialchars($result['xml'])?></span>
</div>
	
<hr>

<? } ?>

<form action=<?=$_SERVER['PHP_SELF']?> method="post">
<table cellpadding="1" cellspacing="2" border="0">

<tr>
	<td colspan="3" class="caption">Тест отправки SMS</td>
</tr>

<? if (isset($_GET['full'])) { ?>
<tr>
    <td class="label">ID запроса: </td>
    <td class="value"><input name="evtId" value="<?=(isset($_POST['evtId'])? $_POST['evtId']: round(rand(1, 1000000)))?>"></td>
	<td class="help">Уникальный ID SMS-запроса [evtId] </td>
</tr>
<? } else { ?>
	<input type="hidden" name="evtId" value="<?=(isset($_POST['evtId'])? $_POST['evtId']: round(rand(1, 1000000)))?>">
<? } ?>

<tr>
    <td class="label">Номер абонента: </td>
    <td class="value"><input name="phone" value="<?=(isset($_POST['phone'])? $_POST['phone']: '')?>"></td>
	<td class="help">Без + в начале, только цифры. Например: 79508469719 [phone]</td>
</tr>

<? if (isset($_GET['full'])) { ?>
<tr>
    <td class="label">ID абонента: </td>
    <td class="value"><input name="abonentId" value="<?=(isset($_POST['abonentId'])? $_POST['abonentId']: round(rand(1, 1000000)))?>"></td>
	<td class="help">Уникальный ID абонента в системе I-Free Partners [abonentId]</td>
</tr>
<? } else { ?>
	<input type="hidden" name="abonentId" value="<?=(isset($_POST['abonentId'])? $_POST['abonentId']: round(rand(1, 1000000)))?>">
<? } ?>

<tr>
    <td class="label">Сервисный номер: </td>
    <td class="value">
		<select name="serviceNumber">
		<option value="4107"<?=((isset($_POST['serviceNumber']) && $_POST['serviceNumber']=='4107')? ' selected': '')?>>4107</option>
		<option value="4108"<?=((isset($_POST['serviceNumber']) && $_POST['serviceNumber']=='4108')? ' selected': '')?>>4108</option>
		<option value="4161"<?=((isset($_POST['serviceNumber']) && $_POST['serviceNumber']=='4161')? ' selected': '')?>>4161</option>
		<option value="4443"<?=((isset($_POST['serviceNumber']) && $_POST['serviceNumber']=='4443')? ' selected': '')?>>4443</option>
		<option value="4444"<?=((isset($_POST['serviceNumber']) && $_POST['serviceNumber']=='4444')? ' selected': '')?>>4444</option>
		<option value="4445"<?=((isset($_POST['serviceNumber']) && $_POST['serviceNumber']=='4445')? ' selected': '')?>>4445</option>
		<option value="4446"<?=((isset($_POST['serviceNumber']) && $_POST['serviceNumber']=='4446')? ' selected': '')?>>4446</option>
		<option value="4447"<?=((isset($_POST['serviceNumber']) && $_POST['serviceNumber']=='4447')? ' selected': '')?>>4447</option>
		<option value="4449"<?=((isset($_POST['serviceNumber']) && $_POST['serviceNumber']=='4449')? ' selected': '')?>>4449</option>
		<option value="7733"<?=((isset($_POST['serviceNumber']) && $_POST['serviceNumber']=='7733')? ' selected': '')?>>7733</option>
		</select>
	</td>
	<td class="help">Сервисный номер, на который необходимо произвести запрос [serviceNumber]</td>
</tr>

<tr>
    <td class="label">Страна: </td>
    <td class="value">
        <select name="country">
		<option value="am"<?=((isset($_POST['country']) && $_POST['country']=='am')? ' selected': '')?>>Армения</option>
		<option value="az"<?=((isset($_POST['country']) && $_POST['country']=='az')? ' selected': '')?>>Азербайджан</option>
		<option value="by"<?=((isset($_POST['country']) && $_POST['country']=='by')? ' selected': '')?>>Беларусь</option>
		<option value="de"<?=((isset($_POST['country']) && $_POST['country']=='de')? ' selected': '')?>>Германия</option>
		<option value="ee"<?=((isset($_POST['country']) && $_POST['country']=='ee')? ' selected': '')?>>Эстония</option>
		<option value="ge"<?=((isset($_POST['country']) && $_POST['country']=='ge')? ' selected': '')?>>Грузия</option>
		<option value="il"<?=((isset($_POST['country']) && $_POST['country']=='il')? ' selected': '')?>>Израиль</option>
		<option value="kg"<?=((isset($_POST['country']) && $_POST['country']=='kg')? ' selected': '')?>>Кыргызстан</option>
		<option value="kz"<?=((isset($_POST['country']) && $_POST['country']=='kz')? ' selected': '')?>>Казахстан</option>
		<option value="lt"<?=((isset($_POST['country']) && $_POST['country']=='lt')? ' selected': '')?>>Литва</option>
		<option value="lv"<?=((isset($_POST['country']) && $_POST['country']=='lv')? ' selected': '')?>>Латвия</option>
		<option value="md"<?=((isset($_POST['country']) && $_POST['country']=='md')? ' selected': '')?>>Молдова</option>
		<option value="ru"<?=((isset($_POST['country']) && $_POST['country']=='ru')? ' selected': '')?>>Россия</option>
		<option value="tj"<?=((isset($_POST['country']) && $_POST['country']=='tj')? ' selected': '')?>>Таджикистан</option>
		<option value="ua"<?=((isset($_POST['country']) && $_POST['country']=='ua')? ' selected': '')?>>Украина</option>
		<option value="uz"<?=((isset($_POST['country']) && $_POST['country']=='uz')? ' selected': '')?>>Узбекистан</option>
        </select>
    </td>
	<td class="help">Страна абонента [country]</td>
</tr>

<tr>
    <td class="label">Оператор: </td>
    <td class="value">
		<select name="operator">
		<option value="4"<?=((isset($_POST['operator']) && $_POST['operator']=='4')? ' selected': '')?>>Akos</option>
		<option value="149"<?=((isset($_POST['operator']) && $_POST['operator']=='149')? ' selected': '')?>>altaysvyaz</option>
		<option value="1"<?=((isset($_POST['operator']) && $_POST['operator']=='1')? ' selected': '')?>>AstraGSM</option>
		<option value="6"<?=((isset($_POST['operator']) && $_POST['operator']=='6')? ' selected': '')?>>BaikalWestCom</option>
		<option value="7"<?=((isset($_POST['operator']) && $_POST['operator']=='7')? ' selected': '')?>>BashSell</option>
		<option value="42"<?=((isset($_POST['operator']) && $_POST['operator']=='42')? ' selected': '')?>>Beeline</option>
		<option value="21"<?=((isset($_POST['operator']) && $_POST['operator']=='21')? ' selected': '')?>>ETK</option>
		<option value="5"<?=((isset($_POST['operator']) && $_POST['operator']=='5')? ' selected': '')?>>Indigo</option>
		<option value="25"<?=((isset($_POST['operator']) && $_POST['operator']=='25')? ' selected': '')?>>Indigo</option>
		<option value="30"<?=((isset($_POST['operator']) && $_POST['operator']=='30')? ' selected': '')?>>Indigo</option>
		<option value="15"<?=((isset($_POST['operator']) && $_POST['operator']=='15')? ' selected': '')?>>Megafon</option>
		<option value="16"<?=((isset($_POST['operator']) && $_POST['operator']=='16')? ' selected': '')?>>Megafon</option>
		<option value="17"<?=((isset($_POST['operator']) && $_POST['operator']=='17')? ' selected': '')?>>Megafon</option>
		<option value="18"<?=((isset($_POST['operator']) && $_POST['operator']=='18')? ' selected': '')?>>Megafon</option>
		<option value="23"<?=((isset($_POST['operator']) && $_POST['operator']=='23')? ' selected': '')?>>Megafon</option>
		<option value="63"<?=((isset($_POST['operator']) && $_POST['operator']=='63')? ' selected': '')?>>Megafon</option>
		<option value="64"<?=((isset($_POST['operator']) && $_POST['operator']=='64')? ' selected': '')?>>Megafon</option>
		<option value="65"<?=((isset($_POST['operator']) && $_POST['operator']=='65')? ' selected': '')?>>Megafon</option>
		<option value="14"<?=((isset($_POST['operator']) && $_POST['operator']=='14')? ' selected': '')?>>MMN</option>
		<option value="9"<?=((isset($_POST['operator']) && $_POST['operator']=='9')? ' selected': '')?>>Motiv-Ekaterinburg</option>
		<option value="22"<?=((isset($_POST['operator']) && $_POST['operator']=='22')? ' selected': '')?>>MTS</option>
		<option value="102"<?=((isset($_POST['operator']) && $_POST['operator']=='102')? ' selected': '')?>>NSS Penza</option>
		<option value="27"<?=((isset($_POST['operator']) && $_POST['operator']=='27')? ' selected': '')?>>NTK</option>
		<option value="26"<?=((isset($_POST['operator']) && $_POST['operator']=='26')? ' selected': '')?>>ON</option>
		<option value="33"<?=((isset($_POST['operator']) && $_POST['operator']=='33')? ' selected': '')?>>ON</option>
		<option value="81"<?=((isset($_POST['operator']) && $_POST['operator']=='81')? ' selected': '')?>>ON</option>
		<option value="83"<?=((isset($_POST['operator']) && $_POST['operator']=='83')? ' selected': '')?>>ON</option>
		<option value="84"<?=((isset($_POST['operator']) && $_POST['operator']=='84')? ' selected': '')?>>ON</option>
		<option value="28"<?=((isset($_POST['operator']) && $_POST['operator']=='28')? ' selected': '')?>>OrenburgGSM</option>
		<option value="31"<?=((isset($_POST['operator']) && $_POST['operator']=='31')? ' selected': '')?>>PenzaGSM</option>
		<option value="36"<?=((isset($_POST['operator']) && $_POST['operator']=='36')? ' selected': '')?>>SibirTelecom</option>
		<option value="43"<?=((isset($_POST['operator']) && $_POST['operator']=='43')? ' selected': '')?>>SkyLink</option>
		<option value="62"<?=((isset($_POST['operator']) && $_POST['operator']=='62')? ' selected': '')?>>SkyLink</option>
		<option value="10"<?=((isset($_POST['operator']) && $_POST['operator']=='10')? ' selected': '')?>>Smarts</option>
		<option value="35"<?=((isset($_POST['operator']) && $_POST['operator']=='35')? ' selected': '')?>>Smarts</option>
		<option value="39"<?=((isset($_POST['operator']) && $_POST['operator']=='39')? ' selected': '')?>>Smarts</option>
		<option value="41"<?=((isset($_POST['operator']) && $_POST['operator']=='41')? ' selected': '')?>>Smarts</option>
		<option value="44"<?=((isset($_POST['operator']) && $_POST['operator']=='44')? ' selected': '')?>>TELE2</option>
		<option value="158"<?=((isset($_POST['operator']) && $_POST['operator']=='158')? ' selected': '')?>>Teleset</option>
		<option value="11"<?=((isset($_POST['operator']) && $_POST['operator']=='11')? ' selected': '')?>>UralSvyasInform</option>
		<option value="147"<?=((isset($_POST['operator']) && $_POST['operator']=='147')? ' selected': '')?>>UUSS</option>
		<option value="2"<?=((isset($_POST['operator']) && $_POST['operator']=='2')? ' selected': '')?>>VolgaTelecom</option>
		<option value="37"<?=((isset($_POST['operator']) && $_POST['operator']=='37')? ' selected': '')?>>VolgaTelecom</option>
	</select>
	</td>
	<td class="help">Сотовый оператор абонента [operator]</td>
</tr>

<tr>
    <td class="label">Текст: </td>
    <td class="value"><input name="smsText" value="<?=htmlspecialchars(isset($_POST['smsText'])? $_POST['smsText']: '')?>"></textarea></td>
	<td class="help">Текс SMS сообщения [smsText]</td>
</tr>

<tr>
    <td class="button" colspan="3"><input type="submit" value="Отправить"></td>
</tr>

</table>
</form>

</BODY>