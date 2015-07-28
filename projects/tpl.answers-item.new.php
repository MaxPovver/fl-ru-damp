<script type="text/javascript">
var ac_sum = <?= round($_SESSION['ac_sum'],2);?>;
var op = [];
<? foreach ($op_codes as $ammount=>$sum) { ?>
op[<?=$ammount?>] = <?=round($sum,2)?>;
<? } ?>
var is_disabled_button = -1;

</script>
<div class="b-pay-answer b-fon b-fon_padbot_30 b-fon_padtop_10">
    <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_bg_ffebbf">
        <div class="b-pay-answer__txt b-pay-answer__txt_float_right b-pay-answer__txt_fontsize_11">
            <a class="b-pay-answer__link" href="/payed/">Аккаунт</a> <span class="b-icon b-icon__pro b-icon__pro_f" title="Платный аккаунт" alt="Платный аккаунт"></span> снимает ограничения
        </div>
        <div class="b-pay-answer__txt">
            <span class="b-icon b-icon_top_-1 b-icon_sbr_ocom"></span>Осталось <?= $user_answers->free_offers?> <a href="https://feedback.fl.ru/article/details/id/102" target="_blank" class="b-pay-answer__link"><?=ending($user_answers->free_offers, "бесплатный", "бесплатных", "бесплатных")?></a><?if ($user_answers->pay_offers > 0) { ?>, <?= $user_answers->pay_offers?> <?=ending($user_answers->pay_offers, "платный", "платных", "платных")?> <? }//if?>  ответов на проекты
        </div>
        
    </div>
</div>
