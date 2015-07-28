<?


$pdrd_disabled = ($sbr->scheme_type != sbr::SCHEME_PDRD2 && time() < strtotime('2011-01-01'));
$frl_ftype = sbr::FT_PHYS;
if($sbr->frl_id) {
    $frl = new freelancer();
    $frl->GetUserByUID($sbr->frl_id);
    if(!$sbr->frl_login) $sbr->data['frl_login'] = $frl->login;
    if($frl_reqvs = sbr_meta::getUserReqvs($frl->uid)) {
        $frl_ftype = (int)$frl_reqvs['form_type'];
        $frl_rtype = $frl_reqvs['rez_type'];
    }
}
if($sbr->data['is_draft'] == 't' && $sbr->data['scheme_type'] == sbr::SCHEME_PDRD2) {
    $sbr->data['scheme_type'] = sbr::SCHEME_LC;
}


$crumbs = 
array(
    0 => array(
        'href' => '/' . sbr::NEW_TEMPLATE_SBR . '/', 
        'name' => '«Мои Сделки»'
    ),
    1 => array(
        'href' => '/' . sbr::NEW_TEMPLATE_SBR . '/?id=' . $sbr->id, 
        'name' => ($site=='create' ? ( $prj_init ? 'Новая сделка в открытом проекте' : 'Новая сделка без публикации проекта' ) : $sbr->data['name'])
    )
);
$css_selector_crumbs = "b-page__title_padbot_30";
// Хлебные крошки
include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.sbr-crumbs.php"); 
?>

<form action="<?= $action_form; ?>" method="post" id="norisk-form" >
    <input type="hidden" name="draft" value="0" id="is_draft">
    <input type="hidden" name="save" value="0" id="is_save">
    <? if($sbr->status==sbr::STATUS_CANCELED || $sbr->status==sbr::STATUS_REFUSED) { ?>
    <input type="hidden" name="sended" value="1" id="is_send" />
    <? }//if?>
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_min-width_830 b-layout__left_width_72ps">
                <div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_color_c7271e b-layout__txt_height_15">
                    <span id="filled-content" class="b-layout__txt b-layout__txt_color_c7271e <?= $notFilled ? "" : "b-layout__txt_hide"?>">Пожалуйста, заполните все поля данной формы. </span>
                    <span><?= !$sbr->data['id'] ? 'Обратите внимание, что минимальный бюджет этапа &ndash; ' . sbr_stages::MIN_COST_RUR . ' ' . ending(sbr_stages::MIN_COST_RUR, 'рубль', 'рубля', 'рублей') . '.' : '' ?></span>
                </div>
                <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_150">
                            <div class="b-layout__txt b-layout__txt_padtop_5">Название сделки</div>
                        </td>
                        <td class="b-layout__right">
                            <div class="b-combo">
                                <div class="b-combo__input">
                                    <input type="text" value="<?=html_attr($sbr->data['name'])?>" maxlength="<?=sbr::NAME_LENGTH?>" size="80" name="name" class="b-combo__input-text" id="c1">
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>									

                <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_150">
                            <div class="b-layout__txt b-layout__txt_lineheight_1">Имя или логин<br />исполнителя</div>
                        </td>
                        <td class="b-layout__right">
                            <div class="b-combo">
                                <? $disabled_frl = ($site=='create' || $sbr->isDraft() || $sbr->data['status']==sbr::STATUS_CANCELED || $sbr->data['status']==sbr::STATUS_REFUSED) ? '' : 'b-combo__input_disabled'; ?>
                                <div class="b-combo__input <?= ($sbr->error['frl_db_id'] ? "b-combo__input_error" : "");?>  b-combo__input_resize b-combo__input_dropdown b-combo__input_width_200 b-combo__input_max-width_700 b-combo__input_arrow-user_yes b_combo__input_quantity_symbols_3 get_only_freelancers b_combo__input_request_id_getuserlistbysbr __search_in_sbr <?= $disabled_frl ?> <?= ($sbr->data['frl_id'] > 0 ? "drop_down_default_" . html_attr($sbr->data['frl_id']) : ""); ?> <?= $sbr->project && $sbr->project['kind'] == 7 ? 'b-combo__input_disabled' : '' ?>">
                                    <input type="text" value="" size="80" name="" class="b-combo__input-text" id="frl" autocomplete="off" onchange="changeFrlRezType('<?= $rez_type == sbr::RT_UABYKZ ? 1: 0;?>');" onblur="setExecUser(this.value);">
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                
                <div class="b-fon b-fon_padbot_20 <?= $sbr->error['frl_ban']? '' : ' b-fon_hide';?>" id="frl_ban">
                    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
                        <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span>Данный пользователь заблокирован на сайте.
                    </div>
                </div>
                
                <div class="b-fon b-fon_padbot_20 <?=($frl && !$frl_rtype ? '' : ' b-fon_hide')?>" id="unknown_frl_rez">
                    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
                        <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span>Обратите внимание, исполнитель не указал свое резиденство. Для нерезидентов Российской Федерации действует особое ограничение &mdash; максимальный бюджет задачи в рублевом эквиваленте не может превышать <?= sbr::MAX_COST_USD_FIZ ?> USD (если Стороны являются физическими лицами) или <?= sbr::MAX_COST_USD ?> USD (в иных случаях).
                    </div>
                </div>
                
                <div class="b-fon b-fon_padbot_20 <?=($rez_type == sbr::RT_UABYKZ ? 'b-fon_nohide' : ' b-fon_hide')?>" id="nerez_frl_rez">
                    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
                        <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span>Для нерезидентов Российской Федерации действуют особые ограничения — максимальный бюджет задачи в рублевом эквиваленте не может превышать <?= sbr::MAX_COST_USD_FIZ ?> USD (если Стороны являются физическими лицами) или <?= sbr::MAX_COST_USD ?> USD (в иных случаях).
                    </div>
                </div>
                <?php if($site != 'editstage' && $site != 'edit') { ?>
                <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_width_150">
                            <div class="b-layout__txt  b-layout__txt_padtop_5">Разделы</div>
                        </td>
                        <td class="b-layout__right">
                            <? foreach ($sbr->data['professions'] as $index => $prof) { ?>
                                <div class="b-combo b-combo_margbot_10">
                                    <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_200 b-combo__input_resize b-combo__input_max-width_450 b-combo__input_visible_height_200 b-combo__input_arrow_yes b-combo__input_init_professionsList sort_cnt drop_down_default_<?= $prof['default'] ?> multi_drop_down_default_column_<?= $prof['default_column'] ?> exclude_value_0_0">
                                        <input type="hidden" name="profession<?= $index ?>_columns[0]" class="mlddcolumn" value="<?= $prof['category_id'] ?>">
                                        <input type="hidden" name="profession<?= $index ?>_columns[1]" class="mlddcolumn" value="<?= $prof['subcategory_id'] ?>">
                                        <input id="profession<?= $index ?>" class="b-combo__input-text" name="profession<?= $index ?>" type="text" size="80" value="<?= $prof['prof_name'] ?>" />
                                        <span class="b-combo__arrow"></span>
                                    </div>
                                </div>
                            <? } ?>
                        </td>
                    </tr>
                </table>
                <?php } //if ?>                                                                            

                <? // Этап ?>
                <? $num = 0;
                foreach($sbr->stages as $k=>$stage) { 
                    if($site=='editstage' && $stage->id != $stage_id) continue;
                ?>
                <div class="b-fon b-fon_width_full b-fon_padbot_15 norisk-stage-block">
                    <input type="hidden" name="stages[<?=$num?>][id]" value="<?=$stage->data['id']?>" tmpname="id" />
                    <input type="hidden" name="stages[<?=$num?>][attachedfiles_session]" id="attachedfiles_session_<?=$num?>" value="<?=$attachedfiles_session?>" tmpname="attachedfiles_session" />
                    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                        <? if($site=='create' || $sbr->isDraft()) { // !!! !$sbr->reserved_id || ?>
                        <a href="#" title="Удалить этап" class="b-button b-button_admin_del b-button_float_right close-block <?=($sbr->stages_cnt > 1 ? '' : 'b-button_hide')?>"></a>
                        <? } ?>
                        <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_22 norisk-stage-header">Этап <?= ($stage->num + 1)?></div>

                        <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
                            <tr class="b-layout__tr">
                                <td class="b-layout__left b-layout__left_width_140">
                                    <div class="b-layout__txt b-layout__txt_padtop_5">Название</div>
                                </td>
                                <td class="b-layout__right">
                                    <div class="b-combo">
                                        <div class="b-combo__input <?= $stage->isFixedState() ? 'b-combo__input_disabled' : '' ?>">
                                            <input class="b-combo__input-text " type="text" value="<?=html_attr($stage->data['name'])?>" size="80" name="stages[<?=$num?>][name]" tmpname="name" maxlength="<?=sbr_stages::NAME_LENGTH?>"/>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_15">
                            <tr class="b-layout__tr">
                                <td class="b-layout__left b-layout__left_width_140">
                                    <div class="b-layout__txt">Описание</div>
                                </td>
                                <td class="b-layout__right">
                                    <div class="b-textarea">
                                        <textarea class="b-textarea__textarea" name="stages[<?=$num?>][descr]" tmpname="descr" cols="" rows="" <?= $stage->isFixedState() ? ' disabled ' : '' ?> onfocus="$(this).getParent('.b-textarea').removeClass('b-textarea_error');"><?= $stage->data['descr']?></textarea>
                                    </div>
                                </td>
                            </tr>
                        </table>


                        <? // Загрузка файлов ?>
                        <? if(!$stage->isFixedState()) { ?>
                        <div class="b-file b-file_padleft_140 b-file_padbot_15" style="position:relative;">
                        	<? /* <a class="b-layout__link" href="https://www.free-lance.ru/service/docs/section/?id=2"><img style="position:absolute; right:10px; bottom:18px; z-index:1; border:0;" src="/images/stuff.png" alt="" width="179" height="26" /></a> */ ?>
                            <div class="attachedfiles1"></div>
                        </div>
                        <? }//if?>
                        <? // Загрузка файлов ?>


                        <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
                            <tr class="b-layout__tr">
                                <td class="b-layout__left b-layout__left_width_140">
                                    <div class="b-layout__txt b-layout__txt_padtop_5">Время на этап</div>
                                </td>
                                <td class="b-layout__right">
                                    <div class="b-combo b-combo_inline-block">
                                        <div class="b-combo__input b-combo__input_width_70 <?= $stage->isFixedState() || $sbr->reserved_id ? 'b-combo__input_disabled' : '' ?>">
                                            <input class="b-combo__input-text" type="text" value="<?=($stage->data['work_days'] ? html_attr($stage->data['work_days']) : '')?>" maxlength="3" size="80" name="stages[<?=$num?>][work_time]" tmpname="work_time"/>
                                        </div>
                                    </div>
                                    <span class="b-layout__txt b-layout__txt_valign_top b-layout__txt_width_60 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;дней</span>
                                    <? if ($site === 'editstage') { ?>
                                        <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5"> &mdash; отсчет времени, добавленного на этап, ведется с даты начала работ по текущей сделке</span>
                                    <? } else { ?>
                                        <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5"> &mdash; отсчет времени начнется с момента резервирования денег, максимальное время этапа <?= sbr_stages::MAX_WORK_TIME;?> дней.</span>
                                    <? } ?>
                                    
                                </td>
                            </tr>
                        </table>
                        
                        <? if($sbr->reserved_id) { 
                            $endTime = strtotime($lc['dateEndLC']);
                            //$endTime = strtotime( $stage->data['start_time'] . ' + ' . $stage->data['int_work_time'] . 'days');
                            ?>
                        <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
                            <tr class="b-layout__tr">
                                <td class="b-layout__left b-layout__left_width_140">
                                    <div class="b-layout__txt b-layout__txt_padtop_5">Добавить время</div>
                                </td>
                                <td class="b-layout__right">
                                    <div class="b-combo b-combo_inline-block">
                                        <div class="b-combo__input b-combo__input_width_70 <?= $stage->isFixedState() ? 'b-combo__input_disabled' : '' ?>">
                                            <?
                                            $endUnixTime = $endTime < time() ? time() : $endTime;
                                            ?>
                                            <input class="b-combo__input-text" type="text" value="0" maxlength="3" size="80" name="stages[<?=$num?>][work_time_add]" tmpname="work_time_add" id="work_time_add" data-time="<?= $endUnixTime * 1000; //в милисекундах?>" data-year="<?= date('Y', $endTime) ?>" data-month="<?= date('m', $endTime) ?>" data-day="<?= date('d', $endTime) ?>" />
                                        </div>
                                    </div>
                                    <span class="b-layout__txt b-layout__txt_valign_top b-layout__txt_width_60 b-layout__txt_inline-block b-layout__txt_padtop_5" id="str_label_work_time_add">&#160;дней</span>
                                    <span class="b-layout__txt b-layout__txt_hide b-layout__txt_width_525 b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5" id="label_descr_work_time_add"> &mdash; Обратите внимание: срок действия аккредитива в результате установленных вами изменений сдвинется до 
                                        <strong id="stage_end_date"><?= $endTime < time() ? date('d.m.Y') : date('d.m.Y', $endTime) ?> г.</strong> В том случае, если исполнитель не завершит сделку со своей стороны, возврат денежных средств будет произведен на следующий рабочий день по истечении указанной даты.</span>
                                </td>
                            </tr>
                        </table> 
                        <? }//if?>
                        
                        <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10">
                            <tr class="b-layout__tr">
                                <td class="b-layout__left b-layout__left_width_140">
                                    <div class="b-layout__txt b-layout__txt_padtop_5">Бюджет</div>
                                </td>
                                <td class="b-layout__right">
                                    <div class="b-combo b-combo_inline-block">
                                        <div class="b-combo__input b-combo__input_width_70 <?= $sbr->reserved_id || $sbr->isReserveProcess() || $sbr->data['status'] >= sbr::STATUS_CHANGED ? 'b-combo__input_disabled' : '' ?> <?= $sbr->error['cost'][$k] ? "b-combo__input_error" : "" ;?>">
                                            <input class="b-combo__input-text" type="text" value="<?=html_attr($stage->data['cost'])?>" maxlength="12" size="80" name="cost" <?= $sbr->reserved_id || $sbr->isReserveProcess() || $sbr->data['status'] >= sbr::STATUS_CHANGED ? 'readonly' : '' ?>/>
                                        </div>
                                    </div>
                                    <span class="b-layout__txt b-layout__txt_width_60 b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;руб.</span>
                                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5"> &mdash; минимальный бюджет &mdash; <span class="mincost-val"><?=sbr_stages::MIN_COST_RUR?></span> руб.</span><br/>
                                    <span id="alert_frl_is_fiz" class="b-layout__txt b-layout__txt_padleft_150 b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5 b-layout__txt_hide" style="padding-left:138px;"> &mdash; максимальный бюджет &mdash; <?= number_format($sbr->usd2rur(sbr::MAX_COST_USD_FIZ), 2, '.', ' ') ?> руб. (эквивалент <?= sbr::MAX_COST_USD_FIZ ?> USD), поскольку <?= $rez_type == sbr::RT_UABYKZ ? "вы не являетесь" : "выбранный исполнитель не является"?> резидентом Российской Федерации</span>
                                    <span id="alert_frl_is_jur" class="b-layout__txt b-layout__txt_padleft_150 b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padtop_5 b-layout__txt_hide" style="padding-left:138px;"> &mdash; максимальный бюджет &mdash; <?= number_format($sbr->maxNorezCost(), 2, '.', ' ')?> руб. (эквивалент <?=sbr::MAX_COST_USD?> USD), поскольку <?= $rez_type == sbr::RT_UABYKZ ? "вы не являетесь" : "выбранный исполнитель не является"?> резидентом Российской Федерации</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <? $num++;} ?>
                <? // Этап ?>
                
                <? if($site!='editstage' && ($site=='create' || $sbr->isDraft())) { // !!! ?>
                <div class="b-layout__txt b-layout__txt_padbot_5 i-button norisk-stage-new">
                    <a class="b-button b-button_margright_5 b-button_poll_plus" href="javascript:void(0)"></a><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle" href="javascript:void(0)">Еще один этап</a>
                </div>
                <? } ?>

                <?php if(!$sbr->user_reqvs['rez_type']) {?>
                <h2 class="b-layout__title b-layout__title_padtop_50">Резидентство</h2>
                <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
                    <tbody><tr class="b-layout__tr">
<!--                            <td class="b-layout__left b-layout__left_width_160">
                                <div class="b-layout__txt">Резидентство</div>
                            </td>-->
                            <td class="b-layout__middle b-layout__middle_padbot_20">
                                <div class="b-radio b-radio_layout_vertical">
                                    <div class="b-radio__item b-radio__item_padbot_10">
                                        <input type="radio" id="rq1" class="b-radio__input" name="rez_type" value="<?=sbr::RT_RU?>" <?=($rt_disabled && $rez_type && $rez_type != sbr::RT_RU ? ' disabled="disabled"' : '' )?><?=($rt_checked && $rez_type == sbr::RT_RU ? ' checked="checked"' : '' )?> onclick="if ($('sbr_create_agree_emp').get('checked')) $('submit_form').removeClass('b-button_disabled')">
                                        <label class="b-radio__label b-radio__label_fontsize_13" for="rq1">Я подтверждаю, что являюсь резидентом Российской Федерации</label>
                                    </div>
                                    <div class="b-radio__item b-radio__item_padbot_10">
                                        <input type="radio" id="rq2" class="b-radio__input" name="rez_type" value="<?=sbr::RT_UABYKZ?>" <?=($rt_disabled && $rez_type && $rez_type != sbr::RT_UABYKZ ? ' disabled="disabled"' : '' )?><?=($rt_checked && $rez_type == sbr::RT_UABYKZ ? ' checked="checked"' : '' )?> onclick="if ($('sbr_create_agree_emp').get('checked')) $('submit_form').removeClass('b-button_disabled')">
                                        <label class="b-radio__label b-radio__label_fontsize_13" for="rq2">Я подтверждаю, что являюсь резидентом любого другого государства,<br />кроме Российской Федерации</label>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php }//if?>
                
                <h2 class="b-layout__title b-layout__title_padtop_50">Расчет бюджета проекта</h2>
                                        <input type="hidden" id="scheme_type<?=sbr::SCHEME_LC?>" name="scheme_type" value="<?=sbr::SCHEME_LC?>" >
                
<?/*
                <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
                    <tbody><tr class="b-layout__tr">
                            <td class="b-layout__left b-layout__left_width_160">
                                <div class="b-layout__txt">Способ перевода денег</div>
                            </td>
                            <td class="b-layout__middle b-layout__middle_padbot_20">
                                <div class="b-radio b-radio_layout_vertical">
                                    <? foreach($EXRATE_CODES as $id=>$ex) {
                                            if($id==exrates::FM || $id==exrates::WMZ) continue;
                                            if(($id==exrates::YM || $id==exrates::WMR) && $sbr->user_reqvs['form_type']==sbr::FT_JURI) continue;
                                    ?>
                                    <div class="b-radio__item b-radio__item_padbot_10">
                                        <input type="radio" id="q<?= $id ?>" class="b-radio__input" name="cost_sys" value="<?= $id ?>" <?=($sbr->cost_sys==$id ? ' checked ' : '')?><?=(($rt_checked && $rez_type==sbr::RT_UABYKZ && $id!=exrates::BANK) || ($sbr->reserved_id) ? ' disabled ' : '')?> >
                                        <label class="b-radio__label b-radio__label_fontsize_13" for="q<?= $id ?>"><?=$ex[0]?></label>
                                    </div>
                                    <? } ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
*/?>
                <div class="b-tax ">
                    <div class="b-tax__fon">
                        <div class="b-tax__rama-t">
                            <div class="b-tax__rama-b">
                                <div class="b-tax__rama-l">
                                    <div class="b-tax__rama-r">
                                        <div class="b-tax__content">
                                            <? foreach($sbr_schemes as $sch) { ?>
                                            <? // схемы ?>
                                            <div style="display:none" class="sch_<?=$sch['type']?>">
                                                <div class="b-tax__level b-tax__level_padbot_12">
                                                    <div class="b-tax__txt b-tax__txt_width_160 b-tax__txt_inline-block">Бюджет всех этапов</div>
                                                    <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_bold" id="sch_<?=$sch['type']?>_f"><?=(float)$sbr->data['cost']?></div>
                                                </div>
                                                
                                                <div class="nalogi">
                                                    <div class="b-tax__level b-tax__level_padbot_12 b-tax__level_double">
                                                        <div class="b-tax__txt b-tax__txt_padleft_1 b-tax__txt_width_160 b-tax__txt_inline-block b-tax__txt_fontsize_11">Налоги и вычеты</div>
                                                        <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_fontsize_11">Сумма, руб.</div>
                                                        <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_inline-block b-tax__txt_fontsize_11">% от бюджета проекта</div>
                                                    </div>

                                                    <? foreach($sch['taxes'][1] as $id=>$tax) { 
                                                        if($sch['type'] == sbr::SCHEME_PDRD2) {
                                                        $s=$e=''; if($id==sbr::TAX_NDS) {$s='<strong>';$e='</strong>';}  ?>
                                                        <? // строка налога ?>
                                                        <div class="b-tax__level <?= ($id==sbr::TAX_NDS ? "b-tax__level_bordtop_9ea599" : "");?> b-tax__level_padbot_12 b-tax__level_padtop_15 taxrow-class" id="taxrow_<?=$sch['type'].'_'.$id?>">
                                                            <? if (18 <= $tax['tax_id'] && $tax['tax_id'] <= 35) { ?>
                                                                <div class="b-tax__txt b-tax__txt_width_160 b-tax__txt_inline-block">
                                                                    <?= $tax['name'] ?>
                                                                </div>
                                                            <? } else { ?>
                                                                <div class="b-tax__txt b-tax__txt_width_160 b-tax__txt_inline-block">
                                                                    <div class="i-shadow i-shadow_inline-block i-shadow_margleft_-16">
                                                                        <span class="b-shadow__icon b-shadow__icon_margright_5 b-shadow__icon_quest"></span>
                                                                        <div class="b-shadow b-shadow_width_270 b-shadow_left_-117 b-shadow_top_12 b-shadow_hide b-moneyinfo">
                                                                            <div class="b-shadow__right">
                                                                                <div class="b-shadow__left">
                                                                                    <div class="b-shadow__top">
                                                                                        <div class="b-shadow__bottom">
                                                                                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                                                                                <div class="b-shadow__txt"><?= $tax['name']; ?></div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="b-shadow__tl"></div>
                                                                            <div class="b-shadow__tr"></div>
                                                                            <div class="b-shadow__bl"></div>
                                                                            <div class="b-shadow__br"></div>
                                                                            <span class="b-shadow__icon b-shadow__icon_close"></span>
                                                                            <span class="b-shadow__icon b-shadow__icon_nosik"></span>
                                                                        </div>
                                                                    </div><?= $tax['abbr'] ?><? if ($tax['tax_id'] == 2 || $tax['tax_id'] == 3) { ?> free-lance.ru<? } ?>
                                                                </div>
                                                            <? } ?>

                                                            <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_bold" id="taxsum_<?= $sch['type'] ?>_<?=$id ?>">0</div>
                                                            <div class="b-tax__txt b-tax__txt_width_150 b-tax__txt_inline-block b-tax__txt_fontsize_11" id="taxper_<?= $sch['type'] ?>_<?= $id ?>">
                                                                <?php if($id==sbr::TAX_NDS) { ?>
                                                                    <?= $tax['percent']*100 ?>% от бюджета + налоги
                                                                <?php } else {//if?>
                                                                    <?= $tax['percent']*100 ?>
                                                                <?php }//else?>
                                                            </div>
                                                        </div>
                                                        <? // строка налога ?>
                                                        <? } else if($sch['type'] == sbr::SCHEME_LC)  {
                                                            $tax_percent += ( $tax['percent']*100 );
                                                            $tid = 100;?>
                                                        <? }//else?>
                                                    <? } ?>
                                                
                                                    <? if($sch['type'] == sbr::SCHEME_LC) { ?>
                                                    <div class="b-tax__level b-tax__level_padbot_12 b-tax__level_padtop_15 taxrow-class" id="taxrow_<?=$sch['type'].'_'.$tid?>">
                                                        <div class="b-tax__txt b-tax__txt_width_160 b-tax__txt_inline-block">
                                                            Вознаграждение Общества, комиссии Банка и платежных систем за открытие аккредитива
                                                        </div>
                                                        <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_inline-block b-tax__txt_bold" id="taxsum_<?= $sch['type'] ?>_<?=$tid ?>">0</div>
                                                        <div class="b-tax__txt b-tax__txt_width_150 b-tax__txt_inline-block b-tax__txt_fontsize_11" id="taxper_<?= $sch['type'] ?>_<?= $tid ?>">
                                                            <?= $tax_percent; ?>
                                                        </div>
                                                    </div>
                                                    <? }//if?>
                                                    
                                                </div>
                                                    
                                            </div>
                                            <? // схемы ?>
                                            <? } ?>
                                        
                                            <? // итого ?>
                                            <div class="b-tax__level b-tax__level_padtop_15">

                                                <div class="i-shadow">
                                                    <div class="b-shadow b-shadow_width_300 b-shadow_right_-420 b-shadow_top_-50 stages-recalc-popup b-shadow_hide">
                                                        <div class="b-shadow__right">
                                                            <div class="b-shadow__left">
                                                                <div class="b-shadow__top">
                                                                    <div class="b-shadow__bottom">
                                                                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                                                                            <div class="b-shadow__h4 b-shadow__h4_bold b-shadow__h4_padbot_10 b-shadow__h4_color_c10601">Бюджеты этапов пересчитаны</div>
                                                                            <div class="stage-row">
                                                                                <div class="b-shadow__txt b-shadow__txt_padbot_10 stage-title">Нарисовать дизайн сайта:</div>
                                                                                <div class="b-shadow__txt b-shadow__txt_padbot_15">
                                                                                    <div class="b-combo b-combo_margtop_-5 b-combo_inline-block">
                                                                                        <div class="b-combo__input b-combo__input_width_80">
                                                                                            <input id="c1" class="b-combo__input-text" name="" type="text" size="80" value="9231,54" />
                                                                                        </div>
                                                                                    </div>&#160;&#160;руб.
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="b-shadow__tl"></div>
                                                        <div class="b-shadow__tr"></div>
                                                        <div class="b-shadow__bl"></div>
                                                        <div class="b-shadow__br"></div>
                                                        <span class="b-shadow__icon b-shadow__icon_close"></span>
                                                        <span class="b-shadow__icon b-shadow__icon_nosik-left"></span>
                                                    </div>
                                                </div>

                                                <div class="b-tax__txt b-tax__txt_bold b-tax__txt_width_160 b-tax__txt_inline-block">Итого к оплате</div>
                                                <div class="b-tax__txt b-tax__txt_inline-block">

                                                    <div class="b-combo b-combo_inline-block b-combo_margtop_-6">
                                                        <div class="b-combo__input b-combo__input_width_90 <?= $sbr->reserved_id || $sbr->isReserveProcess() || $sbr->data['status'] >= sbr::STATUS_CHANGED ? 'b-combo__input_disabled' : '' ?>">
                                                            <input id="cost_total" class="b-combo__input-text b-combo__input-text_bold b-combo__input-text_fontsize_15" name="cost_total" type="text" size="80" value="" <?= $sbr->reserved_id || $sbr->isReserveProcess() || $sbr->data['status'] >= sbr::STATUS_CHANGED ? 'readonly' : '' ?>/>
                                                        </div>
                                                    </div><span class="b-tax__txt b-tax__txt_bold b-tax__txt_fontsize_15">&#160;&#160;руб.</span>
                                                </div>
                                                
                                                <div class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_fontsize_11" id="taxes_alert" style="display:none">Вы заключаете сделку с исполнителем не указавшим резидентство. Во время резервирования итоговая сумма может незначительно отличаться.</div>
                                                
                                            </div>
                                            <? // итого ?>
                                    
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>  



                
                <div class="b-buttons b-buttons_padtop_40 b-buttons_padleft_20">
                    <a href="javascript:void(0)" onclick=" $('norisk-form').submit(); " class="b-button b-button_flat b-button_flat_green " id="submit_form"><span id="btn_changed_stage">Предложить сделку</span></a>
                    <span class="b-buttons__txt b-buttons__txt_padleft_10"><?= ($site == 'editstage' ? 'повторно, сохранив изменения' : 'исполнителю');?></span><?
                    if($site == 'create' || $sbr->isDraft() || $sbr->status == sbr::STATUS_CANCELED || $sbr->status == sbr::STATUS_REFUSED) {
                        ?><span class="b-buttons__txt">,</span> <a class="b-buttons__link" href="javascript:void(0)" onclick="$('is_draft').set('value', '1'); $('norisk-form').submit();"></a> 
                    <? } ?>
                    <span class="b-buttons__txt">или</span> <a class="b-buttons__link b-buttons__link_color_c10601" href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?site=Stage&id=<?= $stage_id;?>">закрыть, не отправляя</a>    
                </div>  
                <div class=" b-layout__txt b-layout__txt_padtop_20">Нажимая кнопку &laquo;Предложить сделку&raquo;, вы отправляете в ОАО ПСКБ заявление на открытие аккредитива по <a class="b-layout__link" href="http://www.fl.ru/offer_lc.pdf" target="_blank">Договору</a> и предлагаете исполнителю сотрудничество с заключением <a class="b-layout__link" href="http://www.fl.ru/agreement_lc.pdf" target="_blank">Соглашения</a> о выполнении работ в рамках Безопасной сделки.</div>
            </td>
            <td class="b-layout__right b-layout__right_padleft_30">
                <?php if($sbr_drafts) {?>
                    <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link b-layout__link_color_000 b-layout__link_bold" href="?site=drafts">Черновики</a></div>
                    <?php  foreach($sbr_drafts as $draft) { ?>
                        <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="?site=edit&id=<?= $draft['id'] ?>"><?=reformat($draft['name'],38,0,1)?></a></div>
                    <? } //foreach?>
                <?php }//if?>
            </td>
        </tr>
    </table>
    <? if($sbr->data['delstages']) { foreach ($sbr->data['delstages'] as $id=>$d)?>
    <input type="hidden" name="delstages[<?=$id?>]" value="<?=$id?>" />
    <? } ?>
    <? if($site == 'create') { ?>
        <? if(isset($sbr->data['tservice_id']) && $sbr->data['tservice_id'] > 0) { ?>
    <input type="hidden" name="tuid" value="<?= $sbr->data['tservice_id'] ?>" />
    <input type="hidden" name="tuhash" value="<?= $sbr->data['tservice_hash'] ?>" />
        <? } else { ?>
    <input type="hidden" name="project_id" value="<?=$sbr->project_id?>" />
        <? } ?>
    <? } ?>
    <? if($site != 'create') { ?>
    <input type="hidden" name="id" value="<?=$sbr->id?>" />
    <? } ?>
    <? if($site == 'editstage') { ?>
    <input type="hidden" name="stage_id" value="<?=$stage_id?>" />
    <? } ?>
    <? if($version) { ?>
    <input type="hidden" name="v" value="<?=$version?>" />
    <? } ?>
    <input type="hidden" name="site" value="<?=$site?>" />
    <input type="hidden" name="action" value="<?=$site?>" />
</form>

<div class="i-shadow popup-tpl">
    <div class="b-shadow b-shadow_width_300 b-shadow_right_-290 b-shadow_top_-45 b-shadow_hide">
        <div class="b-shadow__right">
            <div class="b-shadow__left">
                <div class="b-shadow__top">
                    <div class="b-shadow__bottom">
                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                            <div class="b-shadow__h4 b-shadow__h4_bold b-shadow__h4_padbot_10 b-shadow__h4_color_c10601 popup-tpl-header">&nbsp;</div>
                            <div class="popup-tpl-rows">
                                <div class="popup-tpl-row">
                                    <div class="b-shadow__txt b-shadow__txt_padbot_10 popup-tpl-title">&nbsp;</div>
                                    <div class="b-shadow__txt b-shadow__txt_padbot_15">
                                        <div class="b-combo b-combo_margtop_-5 b-combo_inline-block">
                                            <div class="b-combo__input b-combo__input_width_80">
                                                <input id="c1" class="b-combo__input-text" name="" type="text" size="80" value="" />
                                            </div>
                                        </div>&#160;&#160;руб.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <span class="b-shadow__icon b-shadow__icon_close"></span>
        <span class="b-shadow__icon b-shadow__icon_nosik-left"></span>
    </div>
</div>
<?= attachedfiles::getFormTemplate('attachedfiles1', 'sbr', array(
    'maxsize'  =>    sbr::MAX_FILE_SIZE,
    'maxfiles' =>    sbr::MAX_FILES,
    'graph_hint' =>  false
)) ?>
<script>
    var stageNum = 0;
    Norisk.attaches = [];
    var sbr = new Norisk({
        'id':              'norisk-form',
        'scheme_id':       '<?= $sbr->scheme['id'] ?>',
        'schemes' :        <?= sbr_meta::jsSchemeTaxes($sbr_schemes, $frl_reqvs, $sbr->getUserReqvs(), sbr::EMP, exrates::BANK) ?>,
        'attaches':        <?= json_encode($stages_files) ?>,
        'attach_sessions': <?= json_encode($attachedfiles->session)?>,
        'mincost':         <?= sbr_stages::MIN_COST_RUR ?>,
        'reztype':         '',
        'ereztype':        '<?= $rez_type == sbr::RT_UABYKZ ? 'UABYKZ' : ''?>',
        'emp_form_type':   <?= (int)$sbr->user_reqvs['form_type'] ?>, // юридическая форма заказчика
        'maxcost':         <?= $sbr->maxNorezCost()?>,
        'maxcost_fiz':     <?= (int)$sbr->usd2rur(sbr::MAX_COST_USD_FIZ) ?>,
        'errors':          <?= json_encode($sbr->error) ?>,
        'onStageAdd': function(st) {
            //console.log(st.form.norisk.options.attaches[(st.form.stagesCnt)]);
            new attachedFiles2(st.element.getElement('.attachedfiles1'), {
                'hiddenName':   'stages['+(stageNum)+'][attaches][]', // начинается с нуля
                'files':        st.form.norisk.options.attaches[(stageNum)],
                'action_delete': 'delete_file_stage'
            }, st.form.norisk.options.attach_sessions[(stageNum)]);
            stageNum++;
            init_fileinfo();
            $$('.b-file__input').addEvent('mouseover',function(){this.getNext('.b-button').addClass('b-button_hover')});
            $$('.b-file__input').addEvent('mouseout',function(){this.getNext('.b-button').removeClass('b-button_hover')});
        }
    });
    
    <? if($frl_reqvs['rez_type'] == sbr::RT_UABYKZ || $rez_type == sbr::RT_UABYKZ) {?>
    window.addEvent('domready', function(){
        $$('#alert_frl_is_jur').removeClass('b-layout__txt_hide');
    });
    <? }//if?>
        
    var sbrRezTypeSelected = <?= (int)$sbr->user_reqvs['rez_type'] ?>;
    
</script>
