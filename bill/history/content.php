<?= $xajax->printJavascript('/xajax/'); ?>
<a name="top"></a>
<div class="b-layout b-layout__page">

    <h1 class="b-page__title">Счет на сайте</h1>
    
    <?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/tpl.head_menu.php"); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.score.php"); ?>

    <? if (count($history['items'])) { ?>
        <?
        $pagesCount = $history['pagesCount'];
        $billHref = '%s?page=%d';
        $billHref .= $event ? "&event=$event" : '';
        $billHref .= $period ? "&period=$period" : '';
        $billHref .= '%s';
        ?>

        <div class="b-layout b-layout_overflow_auto b-layout_margbot_20">
            <table class="b-layout__table  b-layout__table_width_full b-layout__table_ipad">
                <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_bordbot_3_e6 b-layout__td_padbot_10 b-layout__td_width_90">
                        <div class="b-layout__txt b-layout__txt_italic">Дата</div>
                    </td>
                    <td class="b-layout__td b-layout__td_bordbot_3_e6 b-layout__td_padbot_10 b-layout__td_padleft_10">
                        <div class="b-layout__txt b-layout__txt_italic">Событие</div>
                    </td>
                    <td class="b-layout__td b-layout__td_bordbot_3_e6 b-layout__td_padbot_10 b-layout__td_padleft_10 b-layout__td_width_90">
                        <div class="b-layout__txt b-layout__txt_italic">Сумма, руб.</div>
                    </td>
                    <td class="b-layout__td b-layout__td_bordbot_3_e6 b-layout__td_padbot_10 b-layout__td_padleft_10 b-layout__td_width_90">
                        <div class="b-layout__txt b-layout__txt_italic">Баланс, руб.</div>
                    </td>
                    <td class="b-layout__td b-layout__td_bordbot_3_e6 b-layout__td_padbot_10 b-layout__td_padleft_10 b-layout__td_width_240">
                        <div class="b-layout__txt b-layout__txt_italic">Примечание</div>
                    </td>
                </tr>
    
                <? foreach($history['items'] as $item) { ?>
                    <?
                    $itemName = account::GetHistoryText($item);
                    $itemText = str_replace( '%username%', $_SESSION['login'], $itemText );
                    ?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__td b-layout__td_padtb_10 b-layout__td_width_90 b-layout__td_bordbot_e6">
                            <div class="b-layout__txt b-layout__txt_color_808080 b-layout__txt_fontsize_11"><?= date("d.m.Y", strtotime($item['op_date'])) ?><br><?= date("H:i", strtotime($item['op_date'])) ?></div>
                        </td>
                        <td class="b-layout__td b-layout__td_padtb_10 b-layout__td_padleft_10 b-layout__td_bordbot_e6">
                            <div class="b-layout__txt <?= ($item['status'] == 'cancel' ? "b-layout__txt_color_808080" : "")?>" id="<?= ($item['op_code'] != billing::RESERVE_OP_CODE? "bil" : "res")?><?=$item['id']?>">
                                <?= $itemName ?>
                            </div>
                        </td>
                        <td class="b-layout__td b-layout__td_padtb_10 b-layout__td_padleft_10 b-layout__td_width_90 b-layout__td_bordbot_e6">
                            <?php if($item['op_code'] != billing::RESERVE_OP_CODE) { ?>
                            <div class="b-layout__txt b-layout__txt_fontsize_15 <?= $item['ammount'] < 0 ? 'b-layout__txt_color_c10600' : 'b-layout__txt_color_6db335' ?>"><?= $item['ammount'] < 0 ? '-' : '+' ?><?= abs($item['ammount']) ?></div>
                            <?php } elseif($item['status'] == 'reserve') { //if?>
                            &nbsp;
                            <?php } else {//else?>
                            <div class="b-layout__txt b-layout__txt_fontsize_15">&mdash;</div>
                            <?php }//else?>
                        </td>
                        <td class="b-layout__td b-layout__td_padtb_10 b-layout__td_padleft_10 b-layout__td_width_90 b-layout__td_bordbot_e6">
                            <div class="b-layout__txt b-layout__txt_fontsize_15"><?= $item['op_code'] != billing::RESERVE_OP_CODE ? $item['balance'] : "&mdash;" ?></div>
                        </td>
                        <td class="b-layout__td b-layout__td_padtb_10 b-layout__td_padleft_10 b-layout__td_width_240 b-layout__td_bordbot_e6">
                            <?php if($item['op_code'] != billing::RESERVE_OP_CODE) { ?>
                            <div class="b-layout__txt b-layout__txt_fontsize_15"><?= reformat(htmlspecialchars_decode($item['comments']), 27, 0, 1) ?></div>
                            <?php } elseif($item['status'] == 'reserve') {//if?>
                            <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_color_c10600" id="com<?=$item['id']?>">Ожидание оплаты<br><?= exrates::getNameExratesForHistory($item['payment_sys'])?></div>
                            <?php } elseif($item['status'] == 'cancel') {//if?>
                            <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_color_808080" id="com<?=$item['id']?>">Список заказов отменен</div>
                            <?php }//else?>
                        </td>
                    </tr>
                <? } ?>
            </table>
        </div>

        <?php if(new_paginator2($page, $pagesCount)) {?>
                <?= new_paginator2($page, $pagesCount, 3, $billHref) ?>
        <?php } ?>

    <? } else { ?>
        <div class="b-post b-post_padtop_20 b-post_padbot_15">
            <h4 class="b-post__h4 b-post__h4_padbot_5 b-post__h4_center">Операций не найдено</h4>
            <div class="b-post__txt b-post__txt_center">Попробуйте изменить параметры фильтра</div>
        </div>
    <? } ?>

    <form method="get" id="history_form">
        <input type="hidden" name="event" id="event" value="<?= $event ?>" />
        <input type="hidden" name="period" id="period" value="<?= $period ?>" />
    </form>
    <div class="b-fon b-fon_pad_15 b-fon_bg_f2 b-fon_margtop_20">
        <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_3 b-layout__txt_fontsize_15 b-page__desktop b-page__ipad">Показаны&#160;</div>
        <script>
            var eventsList = {};
            eventsList[0] = 'Все операции';
            <? if (is_array($events)) foreach ($events as $eventCode => $eventName) { ?>
            eventsList[<?= $eventCode ?>] = '<?= str_replace(array('%username%', PHP_EOL), array($_SESSION['login'], ''), $eventName ) ?>';
            <? } ?>
        </script>
        <div class="b-combo b-combo_inline-block b-combo_overflow-x_yes b-combo_shadow_width_280 b-combo_margbot_20_ipad">
            <div class="b-combo__input b-combo__input_width_220_iphone b-combo__input_multi_dropdown b-combo__input_init_eventsList drop_down_default_<?= $event ?> b-combo__input_width_210  b-combo__input_arrow_yes">
                <input class="b-combo__input-text" id="_event" name="_event" type="text" size="80" value="" />
                <span class="b-combo__arrow"></span>
            </div>
        </div>&#160;
        <script>
            var periodsList = {
                0:   'За последнюю неделю',
                1:  'За последний месяц',
                2:   'За последний год',
                3:    'За все время'
            };
        </script>
        <div class="b-combo b-combo_inline-block b-combo_margbot_20_ipad b-combo_shadow_width_280">
            <div class="b-combo__input b-combo__input_width_220_iphone b-combo__input_multi_dropdown b-combo__input_init_periodsList drop_down_default_<?= $period ?> b-combo__input_width_170 b-combo__input_arrow_yes">
                <input class="b-combo__input-text" id="_period" name="_period" type="text" size="80" value="" />
            </div>
        </div>&#160;
        <a href="javascript:void(0)" onclick="$('period').set('value', $('_period_db_id').get('value')); $('event').set('value', $('_event_db_id').get('value')); $('history_form').submit();" class="b-button b-button_flat b-button_flat_grey b-button_margtop_-4">Применить</a>
    </div>

    <div class="b-layout b-layout_pad_10 b-layout_bord_e6 b-layout_margtop_50">
        <img class="b-layout__pic b-layout__pic_float_left b-layout__pic_margright_10" src="/images/temp/help.png" width="50" height="50">
        <h3 class="b-layout__h3">Возникли вопросы?</h3>
        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_color_808080">Вы можете обратиться к нашей <noindex><a rel="nofollow" class="b-layout__link" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=9239" target="_blank">Базе знаний</a></noindex> <div class="b-icon b-icon_top_8 b-icon__cub b-icon_pad_null"></div></div>
    </div>

</div>