<?
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/reqv_ordered.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/num_to_word.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
	session_start();
	if (!$_SESSION['login']) {header ("Location: /fbd.php"); exit;}
	
	$tid = $bill->tid;
	$reqv = new reqv_ordered();
	if ($tid) $has_reqv = $reqv->GetRow($tid, (hasPermissions('bank') && hasPermissions('adm')) ? '' :  " AND user_id='".get_uid()."'");
	if(!$reqv->id) {header ("Location: /403.php"); exit;}
	$sum = $reqv->ammount;
	//if (is_admin()||is_admin_sm()) {
	if (get_uid(0) != $reqv->user_id && hasPermissions('bank')) {
		$acc = new account();
		$acc->GetInfo($reqv->user_id);
		$acc_num = $acc->id;
	} else {
		$acc_num = $bill->acc['id'];
	} 
	$billCode = 'Б-'.$acc_num.'-'.($reqv->bill_no+1);
    if($reqv->sbr_id) {
        $sbr = new sbr_emp($reqv->user_id);
        if($sbr->initFromId($reqv->sbr_id, false, false, NULL, false)) {
            $contract_num = $sbr->getContractNum();
		    $billCode = 'Б-'.$contract_num;
		    $sbr_nds = $sbr->getCommNds($sbr_comm);
        }
    }
    $ord_num = $reqv->id;
    $sum = round($sum,2);
    if($sbr_nds) {
        $sbr_nds = round($sbr_nds,2);
        $sbr_comm = round($sbr_comm,2);
    }
    $stc = new static_compress;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><HEAD><TITLE>Счет</TITLE>
<META http-equiv=Content-Type content="text/html; charset=windows-1251">
<?php $stc->Send(); ?>
<META content="MSHTML 6.00.2900.2963" name=GENERATOR></HEAD>
<BODY><BR><BR>
<TABLE width="90%" border=0 xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math">
  <TBODY>
  <TR>
    <TD><?= PrintSiteLogo(); ?></TD>
    <TD vAlign=top align=right>
      <DIV style="FONT-SIZE: 10pt"><B>129223, Москва, а/я 33</B>
	  </DIV></TD></TR></TBODY></TABLE>
<DIV style="FONT-SIZE: 11pt" align=center xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math"><B>Образец заполнения платежного 
поручения</B></DIV><BR xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math">
<TABLE class=invoice cellSpacing=0 cellPadding=3 width="90%" border=0 
xmlns:str="http://exslt.org/strings" xmlns:math="http://exslt.org/math">
<TBODY>
              <TR>
                <TD>Получатель<BR>ИНН 7805399430 / КПП 771401001 ООО &laquo;Ваан&raquo;</TD>
                <TD align=middle><BR>Сч. №</TD>
                <TD><BR>40702810787880000803</TD></TR>
              <TR>
                <TD rowSpan=2>Банк получателя<BR>в Московский филиал ОАО АКБ «РОСБАНК» г. Москва
</TD>
                <TD align=middle>БИК</TD>
                <TD rowSpan=2>044583272<BR>30101810000000000272</TD></TR>
              <TR>
    <TD align=middle>Сч. №</TD></TR></TBODY></TABLE><BR 
xmlns:str="http://exslt.org/strings" xmlns:math="http://exslt.org/math"><BR 
xmlns:str="http://exslt.org/strings" xmlns:math="http://exslt.org/math">
<DIV style="FONT-SIZE: 12pt" align=center xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math"><B>СЧЕТ № <?=$billCode?> от <?=(date("d ",strtotime($reqv->op_date)).strtolower(monthtostr(date("m",strtotime($reqv->op_date)))).date(" Y г.",strtotime($reqv->op_date)))?></B></DIV><BR xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math">
<TABLE width="90%" border=0 xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math">
  <TBODY>
  <TR>
    <TD width="50%">
      <DIV style="FONT-SIZE: 10pt">Заказчик: <?= reformat($reqv->full_name, 28)?></DIV></TD>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">Телефоны: <?=$reqv->phone?></DIV></TD></TR>
              <TR>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">Представитель заказчика: <?=$reqv->fio?>
</DIV></TD>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">Факс: <?=$reqv->fax?></DIV></TD></TR></TBODY></TABLE><BR 
xmlns:str="http://exslt.org/strings" xmlns:math="http://exslt.org/math">
<TABLE class=invoice cellSpacing=0 cellPadding=3 width="90%" border=0>
              <TBODY>
              <TR>
                <TD align=middle>
                  <DIV>№</DIV></TD>
                <TD align=middle>
                  <DIV>№ заказа</DIV></TD>
                <TD align=middle>
                  <DIV>Наименование</DIV></TD>
                <TD align=middle>
                  <DIV>Сумма, руб.</DIV></TD></TR>
              <TR>
                <TD align=middle>
                  <DIV>1</DIV></TD>
                <TD align=middle>
                  <DIV style="FONT-SIZE: 10pt"><?=$ord_num?></DIV></TD>
                <TD align=middle>
                  <DIV style="FONT-SIZE: 10pt">
                    <? if($contract_num) { ?>
                      Оплата по договору-оферте № <?=$contract_num?>
                    <? } else { ?>
                      Услуги сайта www.Free-lance.ru
                    <? } ?>
                  </DIV>
                </TD>  
                <TD align=right>
                  <DIV style="FONT-SIZE: 10pt"><?=number_format($contract_num ? $sum-$sbr_nds : $sum-round($sum*18/118, 2), 2, ',', ' ')?></DIV>
                </TD>
              </TR>
              <TR>
                <TD 
                style="BORDER-RIGHT: medium none; BORDER-TOP: medium none; BORDER-LEFT: medium none; BORDER-BOTTOM: medium none" 
                align=right colSpan=3>
                  <DIV>Итого:</DIV></TD>
                <TD align=right><DIV>
                  <?=number_format($contract_num ? $sum-$sbr_nds : $sum-round($sum*18/118, 2), 2, ',', ' ')?></DIV>
                </TD>
              </TR>
              <TR>
                <TD 
                style="BORDER-RIGHT: medium none; BORDER-TOP: medium none; BORDER-LEFT: medium none; BORDER-BOTTOM: medium none" 
                align=right colSpan=3>
                <? if($contract_num) { ?>
                  <DIV>НДС<?=$sbr_comm ? ' (с агентского вознаграждения)' : ''?>:</DIV></TD>
                <TD align=right>
                  <DIV><?=number_format($sbr_nds, 2, ',', ' ')?></DIV></TD></TR>
                <? } else { ?>
                  <DIV>НДС 18%:</DIV></TD>
                <TD align=right>
                  <DIV><?=number_format(round($sum*18/118, 2), 2, ',', ' ')?></DIV></TD></TR>
                <? } ?>
              <TR>
                <TD 
                style="BORDER-RIGHT: medium none; BORDER-TOP: medium none; BORDER-LEFT: medium none; BORDER-BOTTOM: medium none" 
                align=right colSpan=3>
                  <DIV><B>Всего к оплате:</B></DIV></TD>
                <TD align=right>
                  <DIV style="FONT-WEIGHT: bold"><?=number_format($sum, 2, ',', ' ')?></DIV></TD></TR></TBODY></TABLE><BR 
            xmlns:str="http://exslt.org/strings">
            <DIV style="FONT-SIZE: 10pt" 
            xmlns:str="http://exslt.org/strings"><I><B>К оплате:
            <?=num2str($sum)?><?
               if($contract_num) { ?>. 
              <? if($sbr_nds) { ?>
                В том числе НДС 18% &mdash; <?=num2str($sbr_nds)?>
                <? if($sbr_comm) { ?>
                  с суммы агентского вознаграждения ООО "Ваан" &mdash; <?=num2str($sbr_comm)?>.
                <? } ?>
              <? } else { ?>
              <? } ?>
            <? } ?>
            
            </B></I></DIV><BR 
            xmlns:str="http://exslt.org/strings">
            <DIV style="FONT-SIZE: 10pt" 
            xmlns:str="http://exslt.org/strings">Руководитель 
            предприятия&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(В.О. Тарханов)</DIV><BR 
            xmlns:str="http://exslt.org/strings"><BR 
            xmlns:str="http://exslt.org/strings">
            <DIV style="FONT-SIZE: 10pt" class="org"><I><B><U>Условия для 
            расчетов:</U></B></I><BR>1. Cчет действителен в течение <?=$contract_num ? 'трех' : 'пяти'?>
            дней.<BR>2. В назначении платежа, пожалуйста, указывайте 
            <? if($contract_num) { ?>
              <? if($sbr_nds) { ?>
                "<?=$billCode?>. В том числе НДС 18% &mdash; <?=num2strL($sbr_nds)?><? if($sbr_comm) { ?> с суммы агентского вознаграждения ООО "Ваан" &mdash; <?=num2strL($sbr_comm)?><? } ?>".
              <? } else { ?>
                "<?=$billCode?>. НДС не облагается".
              <? } ?>
            <? } else { ?>
               "<?=$billCode?>".
            <? } ?>
            <? if($$show_ex_code || $show_ex_code){ ?>
            <BR/>3. Условия для расчетов: код валютный операции для предоплаты за услуги - 35020
            <? } ?>
            </DIV>
            <DIV>
            </DIV><BR></TD></TR></TBODY>
</TABLE>
      <DIV><BR>
	</TD></TR>
</TBODY></TABLE></BODY></HTML>
