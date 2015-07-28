<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/bank_payments.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/num_to_word.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
if(!defined('IN_SBR')) { // в СБР уже все есть.
  session_start();
  $uid = get_uid(false);
  $id = intval($$pid);
  $print_mode = $$print_mode;
}

if (!$_SESSION['login']) {header ("Location: /403.php"); exit;}

$bp = new bank_payments();
if($id) {
  $bp->GetRow($id, (hasPermissions('bankpayments') && hasPermissions('adm')) ? '' : " AND user_id = {$uid}");
}
if(!$bp->id) { header("Location: /404.php"); exit; }
if($bp->sbr_id) {
    $sbr = new sbr_emp($bp->user_id);
    if($sbr->initFromId($bp->sbr_id, false, false, NULL, false)) {
        $contract_num = $sbr->getContractNum();
        $sbr_nds = $sbr->getCommNds($sbr_comm);
    }
}
$bp->sum = round($bp->sum, 2);
$sum_rk = preg_split('/[.,]/', $bp->sum);
$sum_rk[1] = str_pad($sum_rk[1], 2, '0');
$stc = new static_compress;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Удаленная работа (фри-ланс) на Free-lance.ru</title>
		<meta content="text/html; charset=windows-1251" http-equiv="Content-Type"/>
		<?php $stc->Add("/css/style.css"); ?>
		<?php $stc->Send(); ?>
	</head>
	<body>
<? if(!$print_mode) { ?>
		<div class="container">
			<div class="body clear">
				<div class="main clear">
					<a href="/"><?= PrintSiteLogo(); ?></a>
<? } ?>
   				<table class="cbr-tbl">
   					<tr>
   						<th rowspan="7" style="border-bottom: none;">
   							<strong>Извещение</strong>
   						</th>
   						<td colspan="3">
   							<strong>ООО &laquo;ВААН&raquo;</strong><br />
   							ИНН 7805399430, КПП 771401001<br />
   							Расчетный счет: <strong>40702810787880000803</strong>
   							в Московский филиал ОАО АКБ «РОСБАНК» г. Москва<br />
   							БИК 044583272,<br/>
                            Корр. счет: 30101810000000000272<br />
   							<strong>Счет №: <?=$bp->bill_num?></strong>
   						</td>
   					</tr>
   					<tr>
   						<td colspan="3">
   							<strong><?=$bp->fio?></strong>
   						</td>
   					</tr>
   					<tr>
   						<td colspan="3">
   							<span class="tcbr-hint">(Ф.И.О. плательщика)</span>
   							<strong><?=$bp->address?></strong>
   						</td>
   					</tr>
   					<tr>
   						<td colspan="3">
   							<span class="tcbr-hint">(адрес плательщика)</span>
   							<strong></strong>
   						</td>
   					</tr>
   					<tr>
   						<td rowspan="2" style="text-align:center;">
   							Назначение платежа
   						</td>
   						<td colspan="2" style="text-align:center;">
   							Сумма
   						</td>
   					</tr>
   					<tr>
   						<td style="width: 15%; text-align:center;">
   							Руб.
   						</td>
   						<td style="width: 15%; text-align:center;">
   							Коп.
   						</td>
   					</tr>
   					<tr>
   						<td>
   							<strong>
                              <? if($contract_num) { ?>
                                Оплата по договору-оферте № <?=$contract_num?>.
                                <? if($sbr_nds) { ?>
                                  В том числе НДС 18% &mdash; <?=num2strL($sbr_nds)?>
                                  <? if($sbr_comm) { ?> с суммы агентского вознаграждения ООО "Ваан" &mdash; <?=num2strL($sbr_comm)?><? } ?>
                                <? } else { ?>
                                  НДС не облагается
                                <? } ?>
                              <? } else { ?>
   			    			    Оплата услуг сайта Free-lance.ru по счету<br/>№ <?=$bp->bill_num?>. В том числе НДС.
                              <? } ?>
   							</strong>
   						</td>
   						<td style="text-align:center; vertical-align:middle;">
   							<strong><?=$sum_rk[0]?></strong>
   						</td>
   						<td style="text-align:center; vertical-align:middle;">
   							<strong><?=$sum_rk[1]?></strong>
   						</td>
   					</tr>
   					<tr>
   						<th style="border-top: none;">
   							<strong>Кассир</strong>
   						</th>
   						<td colspan="3">
   							Подпись<br />
   							плательщика __________&nbsp;&nbsp;&nbsp;&laquo;__&raquo; _____ 20__ г.
   						</td>
   					</tr>
   					<tr>
   						<th rowspan="7" style="border-bottom: none;">
   							<strong>Квитанция</strong>
   						</th>
   						<td colspan="3">
   							<strong>ООО &laquo;ВААН&raquo;</strong><br />
   							ИНН 7805399430, КПП 771401001<br />
   							Расчетный счет: <strong>40702810787880000803</strong>
   							в Московский филиал ОАО АКБ «РОСБАНК» г. Москва<br />
   							БИК 044583272,<br/>
                            Корр. счет: 30101810000000000272<br />
   							<strong>Счет №: <?=$bp->bill_num?></strong>
   						</td>
   					</tr>
   					<tr>
   						<td colspan="3">
   							<strong><?=$bp->fio?></strong>
   						</td>
   					</tr>
   					<tr>
   						<td colspan="3">
   							<span class="tcbr-hint">(Ф.И.О. плательщика)</span>
   							<strong><?=$bp->address?></strong>
   						</td>
   					</tr>
   					<tr>
   						<td colspan="3">
   							<span class="tcbr-hint">(адрес плательщика)</span>
   							<strong></strong>
   						</td>
   					</tr>
   					<tr>
   						<td rowspan="2" style="text-align:center;">
   							Назначение платежа
   						</td>
   						<td colspan="2" style="text-align:center;">
   							Сумма
   						</td>
   					</tr>
   					<tr>
   						<td style="width: 15%; text-align:center;">
   							Руб.
   						</td>
   						<td style="width: 15%; text-align:center;">
   							Коп.
   						</td>
   					</tr>
   					<tr>
   						<td>
   							<strong>
                              <? if($contract_num) { ?>
                                Оплата по договору-оферте № <?=$contract_num?>.
                                <? if($sbr_nds) { ?>
                                  В том числе НДС 18% &mdash; <?=num2strL($sbr_nds)?>
                                  <? if($sbr_comm) { ?> с суммы агентского вознаграждения ООО "Ваан" &mdash; <?=num2strL($sbr_comm)?><? } ?>
                                <? } else { ?>
                                  НДС не облагается
                                <? } ?>
                              <? } else { ?>
   			    			    Оплата услуг сайта Free-lance.ru по счету<br/>№ <?=$bp->bill_num?>. В том числе НДС.
                              <? } ?>
   							</strong>
   						</td>
   						<td style="text-align:center; vertical-align:middle;">
   							<strong><?=$sum_rk[0]?></strong>
   						</td>
   						<td style="text-align:center; vertical-align:middle;">
   							<strong><?=$sum_rk[1]?></strong>
   						</td>
   					</tr>
   					<tr>
   						<th style="border-top: none;">
   							<strong>Кассир</strong>
   						</th>
   						<td colspan="3">
   							Подпись<br />
   							плательщика __________&nbsp;&nbsp;&nbsp;&laquo;__&raquo; _____ 20__ г.
   						</td>
   					</tr>
   				</table>
<? if(!$print_mode) { ?>
					<div class="tcbr-btns">
 						<form action="/bill/print/<?=$id?>/1/" method="get" target="_blank" style="display:inline">
 						  <input type="submit" class="i-btn" value="Распечатать"/>
                          <input type="hidden" value="<?php print $_SESSION["rand"]?>" name="u_token_key"/>
 						</form>
					  <? if(!$bp->accepted_time) { ?>
  						<form action="/bill/sber/" method="post" style="display:inline"<? if(defined('IN_SBR')) { ?>onsubmit="document.location.href=document.location.href.replace(/&action=[^&]*/,'')+'#body'; return false;"<? } ?>>
  						  <input type="hidden" name="id" value="<?=$bp->id?>" />
  						  <input type="submit" class="i-btn" value="Изменить" />
  						  <input type="hidden" value="<?php print $_SESSION["rand"]?>" name="u_token_key"/>
  						</form>
					  <? } ?>
					</div>
				</div>
			</div>
		</div>
<? } ?>
<? if($print_mode) { ?>
    <script type="text/javascript">window.print();</script> 
<? } ?>
	</body>
</html>
