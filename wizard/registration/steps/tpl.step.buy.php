<script type="text/javascript">
window.addEvent('domready', 
    function() {
        calcAmmountOfOption($$('.scalc-click'), $('scalc_result'));
    }
);
</script>

<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
    <?php if($payed) { ?>
    <div class="b-layout__txt b-layout__txt_padbot_40">Для того чтобы приобрести выбранные вами услуги, необходимо <a class="b-layout__link" href="/bill/" target="_blank">пополнить личный счет</a> на сайте.</div>				
        <h2 class="b-layout__title b-layout__title_padbot_20">Вы заказали услуги:</h2>
        <form method="POST" name="frm" id="frm">
            <input type="hidden" name="action" value="upd_pay_options">
            <?php 
            // был ли заказан или куплен ПРО акк.
            $is_pro = is_pro();
            foreach($payed as $k=>$pay) { ?>
                <input type="hidden" name="options[<?=$pay['id']?>]" value="1">
                <? if ($pay['op_code'] == 15) {
                    $is_pro = true;
                }
            }

            // бонусная сумма (для ПРО = 10, для неПРО = 0)
            $proBonus = $is_pro ? new_projects::PRICE_ADDED : 0;
            ?>
            <input type="hidden" name="pro_bonus" value="<?= $proBonus; ?>">
            <input type="hidden" name="is_pro" value="<?= is_pro() ? 1 : 0 ?>">
        <?php foreach($payed as $k=>$pay) {
            // если уже куплен ПРО, то выделение цветом - бесплатно
            if (is_pro() && $pay['op_code'] == 53 && $pay['option'] == 2) {
                if (!is_array($disabled)) {
                    $disabled = array(0);
                }
                $disabled[$pay['id']] = true;                    
            }
            if (!$disabled[$pay['id']]) {
                // учитываем количество дней для "Закрепления проекта на верху"
                if ((int)$pay['option'] === 1) {
                    $days = $pay['top_count'];
                } else {
                    $days = 1;
                }
                // определяем стоимость позиции учитывая бонус для ПРО
                $ammount = $pay['ammount'] - ($pay['op_code'] != 15 ? $proBonus * $days : 0);
                $sum += $ammount;
            } ?>
            <div class="b-check b-check_padbot_10">
                <?php if($disabled[$pay['id']]) { ?>
                <input id="def<?= (int)$pay['id']?>" type="hidden" value="1" name="default[<?= $pay['id']?>]" />
                <?php }//if?>
                <input id="pay<?= (int)$pay['id']?>" type="checkbox" value="1" name="pay_options[<?= $pay['id']?>]" class="b-check__input scalc-click" <?= ($disabled[$pay['id']] ? 'disab="1"':'')?> checked="checked" price="<?= round($pay['ammount'],2)?>" top_count="<?= (int)$pay['top_count']?>" op_code="<?= (int)$pay['op_code']?>" option="<?= (int)$pay['option']?>" <?= ($dis[$pay['id']])?"dis='{$dis[$pay['id']]}'":""?> pid="<?=$pay['id']?>"/>
                <label class="b-check__label b-check__label_fontsize_13" >
                    <?php switch($pay['op_code']) {
                        case 15:
                            ?>Аккаунт  <span class="b-icon b-icon__pro b-icon__pro_e"></span> на <?= ($pay['ammount']/10)?> <?= ending($pay['ammount']/10, "месяц", "месяца", "месяцев");?><?
                            break;
                        case 53:
                            switch($pay['option']) {
                                case 1:
                                    ?>Закрепление <?=$pay['type'] == 1?"конкурса":"проекта"?> «<?=$pay['project_name']?>» наверху ленты<?
                                    break;
                                case 2:
                                    ?>Выделение цветом <?=$pay['type'] == 1?"конкурса":"проекта"?> «<?=$pay['project_name']?>»<?
                                    break;
                                case 3:
                                    ?>Выделение жирным <?=$pay['type'] == 1?"конкурса":"проекта"?> «<?=$pay['project_name']?>»<?
                                    break;
                                case 4:
                                    ?>Логотип для <?=$pay['type'] == 1?"конкурса":"проекта"?> «<?=$pay['project_name']?>»<?
                                    break;
                                default:
                                    ?>Платный <?=$pay['type'] == 1?"конкурс":"проект"?> «<?=$pay['project_name']?>»<?
                                    break;
                            }
                            break;
                        case 61:
                            ?><?=$pay['option']?> <?=ending($pay['option'], 'платный','платных','платных')?> <?=ending($pay['option'], 'ответ', 'ответа', 'ответов')?> на <?= ending($pay['option'], 'проект', 'проекты', 'проекты')?><?
                            break;
                        case 76:
                            ?>Аккаунт  <span class="b-icon b-icon__pro b-icon__pro_f"></span> на 1 неделю<?
                            break;
                        case 48:
                            ?>Аккаунт  <span class="b-icon b-icon__pro b-icon__pro_f"></span> на 1 месяц<?
                            break;
                        case 49:
                            ?>Аккаунт  <span class="b-icon b-icon__pro b-icon__pro_f"></span> на 3 месяца<?
                            break;
                        case 50:
                            ?>Аккаунт  <span class="b-icon b-icon__pro b-icon__pro_f"></span> на 6 месяцев<?
                            break;
                        case 51:
                            ?>Аккаунт  <span class="b-icon b-icon__pro b-icon__pro_f"></span> на 1 год<?
                            break;
                        case 9:
                        case 106:
                            ?>Публикация конкурса «<?=$pay['project_name']?>»<?
                            break;
                        default:
                            break;
                    }//?>
                </label>
            </div>	
        <?php }//foreach?>
        <?/*
        
            <div class="b-check b-check_padbot_10">
                <input type="checkbox" value="" name="" class="b-check__input" />
                <label class="b-check__label b-check__label_fontsize_13" >Аккаунт  <span class="b-icon b-icon__pro b-icon__pro_e"></span> на 1 неделю</label>
            </div>		
            <div class="b-check b-check_padbot_10">
                <input type="checkbox" value="" name="" class="b-check__input" />
                <label class="b-check__label b-check__label_fontsize_13" >Публикация конкурса «Требуется дизайн визитки»</label>
            </div>		
            <div class="b-check b-check_padbot_10">
                <input type="checkbox" value="" name="" class="b-check__input" />
                <label class="b-check__label b-check__label_fontsize_13" >Выделение конкурса «Требуется дизайн визитки»</label>
            </div>		
            <div class="b-check">
                <input type="checkbox" value="" name="" class="b-check__input" />
                <label class="b-check__label b-check__label_fontsize_13" >Выделение проекта «Креативный дизайнер в офис. Москва»</label>
            </div>	*/?>	
        </form>
        <h2 class="b-layout__title b-layout__title_padtop_30">Это стоит <span class="b-layout__txt b-layout__txt_fontsize_22 b-layout__txt_color_fd6c30"><span id="scalc_result"><?= round($sum,2)?></span> <?= ending(round($sum), 'рубль', 'рубля', 'рублей');?></span></h2>
       <?/* <div class="b-layout__txt b-layout__txt_fontsize_11">FM &ndash; это внутренняя валюта сайта. 1 FM = 30 российских рублей.</div> */?>
    <?php } else {//if?>
        <h2 class="b-layout__title">Вы не заказали ни одной услуги</h2>
        <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11">На сайте есть большое количество дополнительных услуг, которые сделают вашу работу проще и комфортнее. Если вы захотите воспользоваться ими, вам потребуется <a class="b-layout__link" href="/bill/" target="_blank">пополнить личный счет</a>.</div>
        <?/* <div class="b-layout__txt b-layout__txt_padbot_40 b-layout__txt_fontsize_11">FM &ndash; это внутренняя валюта сайта. 1 FM = 30 российских рублей.</div> */?>
    <?php }//else?>
    <form method="POST" name="frm" id="frm">
        <input type="hidden" name="action" value="upd_pay_options">
        <input type="hidden" name="dontpayed" value="1">
        <div class="b-buttons b-buttons_padtop_40 b-buttons_padbot_40">
            <a href="javascript:void(0)" onclick="$('frm').submit();" class="b-button b-button_rectangle_color_green">
                <span class="b-button__b1">
                    <span class="b-button__b2 b-button__b2_padlr_15">
                        <span class="b-button__txt">Завершить мастер</span>
                    </span>
                </span>
            </a>&#160;&#160;
            <span class="b-buttons__txt">&#160;или&#160;</span>
            <a href="/wizard/registration/?action=exit" class="b-buttons__link b-buttons__link_color_c10601">выйти из мастера</a><span class="b-buttons__txt b-buttons__txt_color_ee1d16">&nbsp;—&nbsp;выбранные вами платные услуги не будут активированы</span> 
        </div>
    </form>
    
    <div class="b-layout__txt ">Вы можете не пополнять счет сейчас, но в этом случае, все заказанные вами услуги пропадут. Вы сможете их выбрать в любое удобное для вас время на странице &laquo;<a class="b-layout__link" href="/service/">Услуги</a>&raquo;.</div>		
    
</div>