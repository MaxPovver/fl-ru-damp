<? $last_pro_code = 48; ?>
<div class="b-layout b-layout_pad_10 b-layout_bord_e6 b-layout_relative b-layout_margbot_10 service" data-name="pro" data-auto="<?= $service['is_auto'] == 't' ? 1 : 0?>">
    <input type="hidden" name="opcode" value="<?= $last_pro_code ?>" />
    
    <span class="b-page__desktop b-page__ipad"><span class="b-icon b-icon__spro b-icon__spro_f b-icon_absolute b-icon_left_10" title="PRO"></span></span>
    <span class="b-layout__txt b-layout__txt_float_right b-layout__txt_fontsize_11 b-page__desktop b-page__ipad"><a href="/payed/" class="b-layout__link promo-link">Подробнее об услуге</a></span>
    <h3 class="b-layout__h3 b-layout__h3_padleft_70 b-layout__txt_padleft_null_iphone">
        Профессиональный аккаунт  &#160;&#160;
        <? if($service['type'] == 'active') { ?>
            <span class="b-layout__txt b-layout__txt_fontsize_11 <?= $service['expired']['is_day_expired'] ? "b-layout__txt_color_c10600" : "b-layout__txt_color_808080"?> b-layouyt__txt_weight_normal">
                <?= $service['expired']['date_str']?> 
                <? if($service['type'] == 'active' && $service['is_auto'] == 't') { ?>
                (включено автопродление)
                <? }//if?>
            </span>    
        <? } elseif($service['type'] == 'lately') { //if?>
            <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_808080 b-layouyt__txt_weight_normal">Срок действия истек <?= date('d.m.Y', strtotime($service['d']))?></span>
        <? } //if?>
    </h3>
    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_10 b-layout__txt_padleft_70 b-layout__txt_padleft_null_iphone">Аккаунт PRO предоставляет своим обладателям бонусы на сайте: открытые контакты, безлимитные ответы на проекты, увеличенный рейтинг, дополнительные специализации в каталоге фрилансеров, расширенные возможности в портфолио и многое другое.</div>



    <div class="b-buttons b-buttons_padleft_70 b-buttons_padbot_10 b-layout__txt_padleft_null_iphone">
        <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green btn-pay "><?= billing::$btn_name_for_type[$service['type']] ?></a>
    </div>
    <?php

        $popup_mini_data = payed::getPayedPROList();
        foreach($popup_mini_data as $data) {
            if($last_pro_code == $data['opcode']) {
                $last_operation = array(
                    'month'     =>  $data['month'], 
                    'op_code'   =>  $data['opcode'],
                    'sum'       =>  $data['cost']
                );
                break;
            }
        }
        $popup_content   = $_SERVER['DOCUMENT_ROOT'] . "/bill/widget/popups/popup.frl_pro.php";
        include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.popup.php" );

    ?>
    <span class="b-layout__txt b-layout__txt_padtop_10 b-layout__txt_fontsize_11 b-page__iphone"><a href="/payed/" class="b-layout__link promo-link">Подробнее об услуге</a></span>
</div>

