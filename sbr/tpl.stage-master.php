<script type="text/javascript">
    var mstage;
    window.addEvent('domready', 
        function() {
            mstage = new MStage();
            mstage.setActive(<?= $active_stage->id; ?>);
            <? foreach($sbr->stages as $i => $curr_stage) { ?>
            mstage.setStage(<?= $curr_stage->id?>);
            <? }//foreach?>
            mstage.initHScroll(<?= $position; ?>);
            <? if($all_agree) { ?>
            mstage.setSbrAgree(true);
            <? }//if?>
        }
    );
</script>
<?php
$crumbs = 
array(
    0 => array(
        'href' => '/' . sbr::NEW_TEMPLATE_SBR . '/', 
        'name' => '«Мои Сделки»'
    ),
    1 => array(
        'href' => '',
        'name' => $sbr->data['name'] . ' ' . $sbr->getContractNum()
    )
);
// Хлебные крошки
include("tpl.sbr-crumbs.php"); 

// Заказчик или исполнитель
include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-user.php");

// Оыкно помощи
include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.help.php");


$max_width = 440 * (count($sbr->stages)+1);
if($max_width < 1201) $max_width = false;
?>
<span id="master_content">
<div class="b-master b-master_sbr b-master_margbot_30">
    <div class="b-master_sbr">
        <ul class="b-master__list" <?= $max_width ? 'style="width: '.$max_width.'px"' : '';?> id="master-list">
            <li class="b-master__item b-master__item_first">
                <div class="b-master__left">
                    <div class="b-master__right b-master__right_straight">
                        <div class="b-master__txt b-master__txt_bold b-master__txt_padtop_17">Пройдите <?= (count($sbr->stages) + 1); ?> <?= ending(count($sbr->stages) + 1, 'простой<br />шаг', 'простых<br />шага', 'простых<br />шагов') ?> и примите сделку:</div>
                    </div>
                </div>
            </li>
            <!-- <a class="b-master__link" href="#"> -->
            <? foreach($sbr->stages as $i => $curr_stage) { ?>
            <li class="b-master__item <?= ($active_stage->id == $curr_stage->id ? "b-master__item_current" : "")?>">
                <div class="b-master__left">
                    <div class="b-master__right">
                        <div class="b-master__txt b-master__txt_padtop_17 b-master__txt_relative b-master__txt_overflow_hidden">
                            Этап <?= $i+1; ?><br />
                            <span id="step-<?= $curr_stage->id?>">
                            <?php if(($curr_stage->data['frl_agree'] == 't' || $last) && $active_stage->id != $curr_stage->id) {?>
                            <a class="b-master__link" href="javascript:void(0)" onclick="mstage.draw(<?= $curr_stage->id?>, event)"><?=$curr_stage->data['name']?></a>
                            <?php } else {//if?>
                            <?=$curr_stage->data['name']?>
                            <?php }//else?>
                            </span>
                            <? if(strlen($curr_stage->data['name']) > 50) { ?>
                            <span class="b-master__shadow b-master__shadow_right"></span>
                            <? }//if?>
                        </div>
                        <?php if($curr_stage->data['frl_agree'] == 't') { ?>
                        <span class="b-master__icon-e b-master__icon-e_ok" style="display:none"></span>
                        <?php }//if?>
                    </div>
                </div>
            </li>
            <? $last = ($active_stage->id == $curr_stage->id); }//foreach?>
            <li class="b-master__item b-master__item_last <?= ($position == count($sbr->stages) && !$last) ? "b-master__item_current" : ""?>">
                <div class="b-master__left">
                    <div class="b-master__right">
                        <div class="b-master__txt b-master__txt_padtop_17 b-master__txt_relative b-master__txt_overflow_hidden">
                            <span id="step-last">
                                
                            <?if($last) {?>    
                            <a class="b-master__link" href="javascript:void(0)" onclick="mstage.draw('last', event)">
                                Условия вашей работы<br />и расчет гонорара
                            </a>
                            <? } else {//if?>
                            Условия вашей работы<br />и расчет гонорара
                            <? }//else?>
                            </span>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <span class="b-master__shadow b-master__shadow_left" id="shadow-left" style="display:none"></span>
	<span class="b-master__shadow b-master__shadow_right"></span>
</div>

<? foreach($sbr->stages as $i => $curr_stage) { ?>
<span style="<?= ($active_stage->id == $curr_stage->id && !$all_agree ? "" : "display:none;");?>" id="master-stage-<?= $curr_stage->id;?>" class="master-stage">
    <div class="b-layout__txt b-layout__txt_padbot_30 b-layout__txt_bold">Бюджет этапа <?= sbr_meta::view_cost($curr_stage->data['cost'], $sbr->cost_sys)?> &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160; Время на этап <?= $curr_stage->data['work_days']?> <?=ending(abs($curr_stage->data['work_days']), 'день', 'дня', 'дней')?></div>
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_72ps">
                <h2 class="b-layout__title">Техническое задание</h2>
                <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_15">
                    <?= reformat($curr_stage->data['descr'], 70, 0, 0, 1)?>
                </div>
                
                <? if($curr_stage->data['attach']) { ?>
                <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_15 b-layout__txt_bold">Вложения</div>
                <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
                    <tbody>
                        <? foreach($curr_stage->data['attach'] as $id=>$a) {  $aData = getAttachDisplayData(null, $a['name'], $a['path'] ); ?>
                        <tr class="b-layout__tr">
                            <td class="b-layout__middle b-layout__middle_padbot_5">
                                <div class="b-layout__txt">
                                    <i class="b-icon b-icon_attach_<?= $aData['class_ico'] === 'unknown' ? 'unknown' : $a['ftype'] ?>"></i> 
                                    <a href="<?=WDCPREFIX.'/'.$a['path'].$a['name']?>" class="b-layout__link" target="_blank"><?= reformat($a['orig_name'], 30)?></a>, <?= ConvertBtoMB($a['size'])?>
                                </div>
                            </td>
                            <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5">
                                <div class="b-layout__txt"><a href="<?=WDCPREFIX.'/'.$a['path'].$a['name']?>" class="b-layout__link" target="_blank">Скачать</a></div>
                            </td>
                        </tr>
                        <? }//foreach?>
                    </tbody>
                </table>
                <? }//foreach?>
                <div class="b-buttons b-buttons_padtop_40">
                    <a class="b-button b-button_flat b-button_flat_green" href="javascript:void(0)" onclick="mstage.draw('<?= ($sbr->stages[$i+1] ? $sbr->stages[$i+1]->id : 'last');?>', event)">Продолжить</a>
                    <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span> 
                    <a href="javascript:void(0)" onclick="$('rrbox<?=$sbr->data['id']?>').toggleClass('b-shadow_hide'); return false;" class="b-buttons__link b-buttons__link_dot_c10601">
                        отказаться от всей сделки
                    </a>	
                </div>
            </td>
            <td class="b-layout__right"></td>
        </tr>
    </table>
</span>
<input name="stages[<?=$i;?>][id]" value="<?= $curr_stage->id;?>" type="hidden">
<input name="stages[<?=$i;?>][version]" value="<?= $curr_stage->version?>" type="hidden">
<? }//foreach?>
<? //sbr_meta::view_finance_popup("/" . sbr::NEW_TEMPLATE_SBR . "/?site=master&id={$sbr->id}");?>

<form method="post" id="refuseSbrFrm<?=$sbr->id?>">
    <input type="hidden" name="refuse" value="1" />
    <input type="hidden" name="id" value="<?=$sbr->data['id']?>" />
    <input type="hidden" name="version" value="<?=$sbr->data['version']?>" />
    <input type="hidden" name="rez_type" value="<?= ( $sbr->user_reqvs['rez_type'] ? $sbr->user_reqvs['rez_type'] : sbr::RT_RU ); ?>">
    <input type="hidden" name="action" value="agree" />
    <div class="b-shadow b-shadow_center b-shadow_zindex_11 b-shadow_width_620 b-shadow_hide" id="rrbox<?=$sbr->data['id']?>">
        <div class="b-shadow__right">
            <div class="b-shadow__left">
                <div class="b-shadow__top">
                    <div class="b-shadow__bottom">
                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                            <h1 class="b-shadow__title b-shadow__title_fontsize_34 b-shadow__title_padbot_15">Отказ от сделки</h1>
                            <div class="b-shadow__txt b-shadow__txt_padbot_20">Пожалуйста, укажите причину, по которой вы отказываетесь от сотрудничества в сделке:</div>
                            <div class="b-textarea">
                                    <textarea class="b-textarea__textarea b-textarea_noresize b-textarea__textarea_height_140 max-height_140 noresize" name="frl_refuse_reason" cols="" rows=""></textarea>
                            </div>
                            <div class="b-buttons b-buttons_padtop_15">
                                <a class="b-button b-button_flat b-button_flat_green"  href="javascript:void(0)" onclick="submitForm($('refuseSbrFrm<?= $sbr->id;?>'))">Отправить отказ</a>
                                <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
                                <a class="b-buttons__link b-buttons__link_dot_c10601" href="javascript:void(0)" onclick="$('rrbox<?=$sbr->data['id']?>').toggleClass('b-shadow_hide');">закрыть, не отправляя</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <span class="b-shadow__icon b-shadow__icon_close"></span>
    </div>
</form>

<form action="?id=<?=$sbr->id?>" method="post" id="currentsFrm<?=$sbr->id?>">
    <input type="hidden" name="refuse" value="" />
    <input type="hidden" name="id" value="<?=$sbr->data['id']?>" />
    <input type="hidden" name="version" value="<?=$sbr->data['version']?>" />
    <input type="hidden" name="rez_type" value="<?= ( $sbr->user_reqvs['rez_type'] ? $sbr->user_reqvs['rez_type'] : sbr::RT_RU ); ?>">
    <input type="hidden" name="action" value="agree" />
    <input type="hidden" name="ok" value="" >
    
    
<span style="<?= ($all_agree ? "" : "display:none;");?>" id="master-stage-last" class="master-stage">
<? include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-master-condition.php"); ?>
</span>
    
</form>
</span>
