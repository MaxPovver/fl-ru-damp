<?php
$op_codes = $answers->GetOpCodes();
?>
<script type="text/javascript">

var ac_sum = <?=round($_SESSION['ac_sum'],2);?>;
var op = [];
<?php foreach ($op_codes as $ammount=>$sum) { ?>
op[<?=$ammount?>] = <?=$sum?>;
<? } //foreach?>
<? 
/** 
 * в IE мен€ютс€ стили отображени€ при disabled = true, это костыль дл€ данной ситуации - (решить через CSS не удалось) 
 * задача  http://beta.free-lance.ru/mantis/view.php?id=12554, вопрос 5 по ней
 */?>
var is_disabled_button = -1;
</script>

<a name="new_offer"></a><h1>¬ы не можете ответить на проект</h1>


<div class="b-pay-answer b-fon b-fon_bg_f0ffdf b-fon__body_pad_5_10 b-fon_margbot_25">
        <div class="b-pay-answer__txt">
            <span class="b-layout__txt b-layout__txt_bold">
                ќсталось ответов на проекты: 0
            </span>
            &nbsp;&mdash;&nbsp;
            <?php if(isAllowTestPro()): ?>
                <a class="b-layout__link b-layout__link_inline-block b-layout__link_lineheight_1" href="/payed/"> упите</a> 
                <a class="b-layout__link  b-layout__link_inline-block b-layout__link_lineheight_1" href="/payed/"><span title="PRO" class="b-icon b-icon__pro b-icon__pro_f"></span></a> 
                <a class="b-layout__link  b-layout__link_inline-block b-layout__link_lineheight_1" href="/payed/">на 1 мес€ц за <span class="b-layout__txt_through b-layout__txt_color_99"><?=payed::getPriceByOpCode(48)?></span> <?=payed::getPriceByOpCode(163)?> рублей и отвечайте на проекты!</a>
            <?php else: ?>
                <a class="b-layout__link b-layout__link_inline-block b-layout__link_lineheight_1" href="/payed/"> упите</a> 
                <a class="b-layout__link  b-layout__link_inline-block b-layout__link_lineheight_1" href="/payed/"><span title="PRO" class="b-icon b-icon__pro b-icon__pro_f"></span></a> 
                <a class="b-layout__link  b-layout__link_inline-block b-layout__link_lineheight_1" href="/payed/">на 1 мес€ц за <?=payed::getPriceByOpCode(48)?> рублей и отвечайте на проекты!</a>
            <?php endif; ?>
       </div>
</div>
<?php

//показываем попап успешной покупки ѕ–ќ после редиректа
$quickPRO_type = 'project'; 
require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_buy_pro_win.php"); 

?>
