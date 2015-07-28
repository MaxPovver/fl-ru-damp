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