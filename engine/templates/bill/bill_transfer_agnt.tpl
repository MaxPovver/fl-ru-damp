<? require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/num_to_word.php'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML xmlns:math = "http://exslt.org/math" xmlns:date = "http://exslt.org/dates-and-times">
    <HEAD>
        <TITLE>Free-lance.ru: Счет</TITLE>
        <META http-equiv=Content-Type content="text/html; charset=windows-1251">
        <link type="text/css" href="/css/block/style.css" rel="stylesheet" />
        <style type="text/css">
            @media print{
                .b-fon{ display:none;}
            }
        </style>
    </HEAD>
    <BODY text=#000000 bottomMargin=10 vLink=#0033cc aLink=#cc0033 link=#0033cc bgColor=#ffffff topMargin=10 marginheight="20" marginwidth="20">
        <TABLE class=operations cellSpacing=0 cellPadding=4 width="80%" border=0>
            <TBODY>
                <tr>
                    <TD vAlign=bottom><a href="/"><img src="/images/logo.png" width="197" height="28" alt="Удаленная работа, фри-ланс" class="logo" /></a></TD>
                    <TD vAlign=bottom align=left>&nbsp;</TD>
                    <TD vAlign=bottom align=right> </TD>
                </tr>
                <tr>
                    <td colspan="3">
                        <div class="b-fon b-fon_width_full b-fon_padtop_10 b-fon_padbot_10">
                            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf">
                                <a href="/sbr/?site=invoiced&id=<?=intval($_GET['id'])?>&print=1" target="_blank" class="b-button b-button_rectangle_color_green b-button_float_right">
                                    <span class="b-button__b1">
                                        <span class="b-button__b2">
                                            <span class="b-button__txt">Распечатать</span>
                                        </span>
                                    </span>
                                </a>
                                <div class="b-fon__txt b-fon__txt_padbot_5"><span class="b-fon__attent_pink"></span>Данный счет необходимо распечатать и оплатить.</div> 
                                <div class="b-fon__txt">Средства для сделки будут зарезервированы непосредственно по факту поступления денег на указанный расчетный счет.</div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr align=middle>
                    <td class=th colSpan=3>
                        <h2 class=title>Счет</h2>
                    </td>
                </tr>
                <tr>
                    <td colSpan=3>
                        <table cellSpacing=0 cellPadding=10 width="100%" border=0>
                            <TBODY>
                                <TR>
                                    <TD style="BORDER-RIGHT: #cccccc 1px solid; BORDER-TOP: #cccccc 1px solid; BORDER-LEFT: #cccccc 1px solid; BORDER-BOTTOM: #cccccc 1px solid">
                                        <br/>
                                        <TABLE width="100%" border=0 xmlns:str="http://exslt.org/strings">
                                            <TBODY>
                                                <TR>
                                                    <TD>&nbsp;</TD>
                                                    <TD vAlign=top align=right>
                                                        <DIV style="FONT-SIZE: 10pt"><B>129223, Москва, а/я 33</B></DIV>
                                                    </TD>
                                                </TR>
                                            </TBODY>
                                        </TABLE>
                                        <DIV style="FONT-SIZE: 11pt" align=center xmlns:str="http://exslt.org/strings"><B>Образец заполнения платежного поручения</B></DIV>
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <TABLE class=invoice cellSpacing=0 cellPadding=3 width="100%" border=0 xmlns:str="http://exslt.org/strings">
                                            <TBODY>
                                                <TR>
                                                    <TD>Получатель<BR>ИНН 7805399430 / КПП 771401001 ООО &laquo;Ваан&raquo;</TD>
                                                    <TD align=middle><BR>Сч. №</TD>
                                                    <TD><BR>40702810787880000803</TD></TR>
                                                <TR>
                                                    <TD rowSpan=2>Банк получателя<BR>в Московский филиал ОАО АКБ «РОСБАНК» г. Москва</TD>
                                                    <TD align=middle>БИК</TD>
                                                    <TD rowSpan=2>044583272<BR>30101810000000000272</TD>
                                                </TR>
                                                <TR>
                                                    <TD align=middle>Сч. №</TD>
                                                </TR>
                                            </TBODY>
                                        </TABLE>
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <DIV style="FONT-SIZE: 12pt" align=center xmlns:str="http://exslt.org/strings">
                                            <B>Счет № <?=$billCode?> от <?=(date("d ").strtolower(monthtostr(date("m"))).date(" Y г."))?></B>
                                        </DIV>
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <TABLE width="100%" border=0 xmlns:str="http://exslt.org/strings">
                                            <TBODY>
                                                <TR>
                                                    <TD width="50%">
                                                        <DIV style="FONT-SIZE: 10pt">Заказчик: <?= reformat($reqv['full_name'], 28)?></DIV>
                                                    </TD>
                                                    <TD width="50%">
                                                        <DIV style="FONT-SIZE: 10pt">Телефоны: <?= $reqv['phone']?></DIV>
                                                    </TD>
                                                </TR>
                                                <TR>
                                                    <TD width="50%">
                                                        <DIV style="FONT-SIZE: 10pt">Представитель заказчика: <?= reformat($reqv['fio']);?></DIV>
                                                    </TD>
                                                    <TD width="50%">
                                                        <DIV style="FONT-SIZE: 10pt">Факс: <?= reformat($reqv['fax'])?></DIV>
                                                    </TD>
                                                </TR>
                                            </TBODY>
                                        </TABLE>
                                        <BR xmlns:str="http://exslt.org/strings">
                                        <TABLE class=invoice cellSpacing=0 cellPadding=3 width="100%" border=0>
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