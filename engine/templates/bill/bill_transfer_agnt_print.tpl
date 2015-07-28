<? require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/num_to_word.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><HEAD><TITLE>Счет</TITLE>
<META http-equiv=Content-Type content="text/html; charset=windows-1251">
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
xmlns:math="http://exslt.org/math"><B>СЧЕТ № <?=$billCode?> от <?=(date("d ",strtotime($reqv['invoiced_time'])).strtolower(monthtostr(date("m",strtotime($reqv['invoiced_time'])))).date(" Y г.",strtotime($reqv['invoiced_time'])))?></B></DIV><BR xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math">
<TABLE width="90%" border=0 xmlns:str="http://exslt.org/strings" 
xmlns:math="http://exslt.org/math">
  <TBODY>
  <TR>
    <TD width="50%">
      <DIV style="FONT-SIZE: 10pt">Заказчик: <?= reformat($reqv['full_name'], 28);?></DIV></TD>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">Телефоны: <?= $reqv['phone']?></DIV></TD></TR>
              <TR>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">Представитель заказчика: <?=$reqv['fio']?>
</DIV></TD>
                <TD width="50%">
                  <DIV style="FONT-SIZE: 10pt">Факс: </DIV></TD></TR></TBODY></TABLE>
                  
                  
                  <BR xmlns:str="http://exslt.org/strings">
                                        <TABLE class=invoice cellSpacing=0 cellPadding=3 width="90%" border=0>
                                            <TBODY>
                                                <TR>
                                                    <TD align=middle>
                                                        <DIV>№</DIV>
                                                    </TD>
                                                    <TD align=middle>
                                                        <DIV>№ заказа</DIV>
                                                    </TD>
                                                    <TD align=middle>
                                                        <DIV>Наименование</DIV>
                                                    </TD>
                                                    <TD align=middle>
                                                        <DIV>Сумма, руб.</DIV>
                                                    </TD>
                                                </TR>
                                                <TR>
                                                    <TD align=middle>
                                                        <DIV>1</DIV>
                                                    </TD>
                                                    <TD align=middle>
                                                        <DIV style="FONT-SIZE: 10pt"><?=$ord_num?></DIV>
                                                    </TD>
                                                    <TD align=middle>
                                                        <DIV style="FONT-SIZE: 10pt">
                                                            Услуги сайта www.Free-lance.ru
                                                        </DIV>
                                                    </TD>  
                                                    <TD align=right>
                                                        <DIV style="FONT-SIZE: 10pt"><?=number_format($sum, 2, ',', ' ')?></DIV>
                                                    </TD>
                                                </TR>
                                                <TR>
                                                    <TD style="BORDER-RIGHT: medium none; BORDER-TOP: medium none; BORDER-LEFT: medium none; BORDER-BOTTOM: medium none" align=right colSpan=3>
                                                        <DIV>Итого:</DIV>
                                                    </TD>
                                                    <TD align=right>
                                                        <DIV><?=number_format($sum, 2, ',', ' ')?></DIV>
                                                    </TD>
                                                </TR>
                                                <TR>
                                                    <TD 
                                                        style="BORDER-RIGHT: medium none; BORDER-TOP: medium none; BORDER-LEFT: medium none; BORDER-BOTTOM: medium none" 
                                                        align=right colSpan=3>
                                                        <DIV>НДС 18%:</DIV>
                                                    </TD>
                                                    <TD align=right>
                                                        <DIV>0 ,00</DIV>
                                                    </TD>
                                                </TR>
                                                <TR>
                                                    <TD style="BORDER-RIGHT: medium none; BORDER-TOP: medium none; BORDER-LEFT: medium none; BORDER-BOTTOM: medium none" align=right colSpan=3>
                                                        <DIV><B>Всего к оплате:</B></DIV>
                                                    </TD>
                                                    <TD align=right>
                                                        <DIV style="FONT-WEIGHT: bold"><?=number_format($sum, 2, ',', ' ')?></DIV>
                                                    </TD>
                                                </TR>
                                            </TBODY>
                                        </TABLE>
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <DIV style="FONT-SIZE: 10pt" xmlns:str="http://exslt.org/strings">
                                            <I><B>К оплате: <?= num2str($sum);?> </B></I>
                                        </DIV>
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <DIV style="FONT-SIZE: 10pt" xmlns:str="http://exslt.org/strings">
                                            Руководитель предприятия&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(В.О.Тарханов)
                                        </DIV>
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <DIV style="FONT-SIZE: 10pt" class="org">
                                            <I><B><U>Условия для расчетов:</U></B></I>
                                            <BR>1. Cчет действителен в течение пяти дней.
                                            <BR>2. В назначении платежа, пожалуйста, указывайте "<?=$billCode?>".
                                        </DIV>
                                        <DIV></DIV>
                                        <BR>
                                    </TD>
                                </TR>
                            </TBODY>
                        </table>
                        <BR>
                    </td>
                </TR>         
             </TBODY>
         </TABLE>
    </BODY>
</HTML>