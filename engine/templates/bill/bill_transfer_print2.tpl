<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pf.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/num_to_word.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
	session_start();
	if (!$_SESSION['login'] || !(hasPermissions('bank') && hasPermissions('adm'))) {header ("Location: /403.php"); exit;}
	
	$tid = $$tid;
	$reqv = new pf();
	if ($tid) $has_reqv = $reqv->GetOrderer($tid);
	if(!$has_reqv['id']) {header ("Location: /403.php"); exit;}
	$sum = $has_reqv['sum'];
	
	$billCode = $has_reqv['bill_num'];
    
    $ord_num = $has_reqv['id'];
    $sum = round($sum,2);
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
xmlns:math="http://exslt.org/math"><B>СЧЕТ № <?=$billCode?> от <?=(date("d ",strtotime($has_reqv['invoiced_time'])).strtolower(monthtostr(date("m",strtotime($has_reqv['invoiced_time'])))).date(" Y г.",strtotime($has_reqv['invoiced_time'])))?></B></DIV><BR xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math">
<TABLE width="90%" border=0 xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math">
  <TBODY>
  <TR>
    <TD width="50%">
      <DIV style="FONT-SIZE: 10pt">Заказчик: <?= reformat($has_reqv['company'], 28);?></DIV></TD>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">Телефоны: </DIV></TD></TR>
              <TR>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">Представитель заказчика: <?=$has_reqv['company']?>
</DIV></TD>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">Факс: </DIV></TD></TR></TBODY></TABLE><BR 
xmlns:str="http://exslt.org/strings" xmlns:math="http://exslt.org/math">
<TABLE class=invoice cellSpacing=0 cellPadding=3 width="90%" border=0>
{{include "bill/bill_transfer_form.tpl"}}
