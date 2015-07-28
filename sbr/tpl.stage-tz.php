<? $size_ln_descr = substr_count(reformat($stage->data['descr'], 70, 0, 0, 1), "<br") + 1 + ( $stage->data['attach'] ? (count($stage->data['attach']) + 2) : 0 ); // Количество переносов строк?>
<div>
<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
    <tr class="b-layout__tr">
        <td class="b-layout__left b-layout__left_width_72ps">
            <div class="b-post">
                <h2 class="b-post__title b-post__title_padbot_15">
                    
                    <? if($stage->isNewVersionTZ() && ($stage_changed || $stage_changed_for_frl)) { ?>
                    <span class="sbr-tz">Новое техническое задание от <?= date('d.m.Y', $stage->data['date_version_tz'][1])?> &#160;&#160;&#160;&#160;&#160;</span>
                    <span class="sbr-old-tz b-post__txt_hide">Техническое задание от <?= date('d.m.Y', $stage->data['date_version_tz'][0])?> &#160;&#160;&#160;&#160;&#160;</span>
                    <span class="b-post__txt"><a class="b-post__link b-post__link_dot_0f71c8" id="toggle-tz-link" href="javascript:void(0)" onclick="toggle_tz();">Посмотреть старое</a></span>
                    <? } else {//if?>
                    Техническое задание
                    <? }?>
                </h2>
                <div class="b-post__body b-post__body_relative b-post__body_overflow_hidden <?= $size_ln_descr > 5 && !($stage->isNewVersionTZ() && ($stage_changed || $stage_changed_for_frl))? "b-post__body_height_100" : ""?>">
                    <div class="b-post__txt <?= $stage->data['attach'] ? "b-post__txt_padbot_15" : ""; ?> b-post__txt_fontsize_15 sbr-tz">
                        <? if($stage->status == sbr_stages::STATUS_INARBITRAGE || $stage->status == sbr_stages::STATUS_ARBITRAGED) { $attached = $frl_version['attach']; ?>
                            <?= reformat($frl_version['descr'], 70, 0, 0, 1);?>
                        <? } else { $attached = $stage->data['attach']; //if?>
                            <?= reformat($stage->data['descr'], 70, 0, 0, 1)?>
                        <? }//if?>
                    </div>
                    <? if($attached) {?>
                    <span id="new_attach">
                        <div class="b-post__txt b-post__txt_padbot_10 b-post__txt_fontsize_15 b-post__txt_bold">Вложения</div>
                        <table class="b-layout__table" border="0" cellpadding="0" cellspacing="0">
                            <tbody>
                                <? foreach($attached as $id=>$a) {  
                                    if ($a['is_deleted'] === 't' && ( $stage->status == sbr_stages::STATUS_INARBITRAGE || $stage->status == sbr_stages::STATUS_ARBITRAGED ) ) {
                                        continue;
                                    }
                                    $aData = getAttachDisplayData(null, $a['name'], $a['path'] );?>
                                <tr class="b-layout__tr">
                                    <td class="b-layout__middle b-layout__middle_padbot_5">
                                        <div class="b-layout__txt">
                                            <i class="b-icon b-icon_attach_<?= $aData['class_ico'] === 'unknown' ? 'unknown' : $a['ftype']?>"></i> 
                                            <a href="<?=WDCPREFIX.'/'.$a['path'].$a['name']?>" class="b-layout__link" target="_blank"><?= reformat($a['orig_name'] ? $a['orig_name'] : $aData['orig_name'], 30)?></a>, <?= ConvertBtoMB($a['size'])?>
                                        </div>
                                    </td>
                                    <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5">
                                        <div class="b-layout__txt"><a href="<?=WDCPREFIX.'/'.$a['path'].$a['name']?>" class="b-layout__link" target="_blank">Скачать</a></div>
                                    </td>
                                </tr>
                                <? }//foreach?>
                            </tbody>
                        </table>
                    </span>
                    <? } ?>
                    
                    <? if($stage->isNewVersionTZ() && ($stage_changed || $stage_changed_for_frl)) {?>
                    <div class="b-post__txt b-post__txt_fontsize_15 <?= $stage->v_data['attach'] ? "b-post__txt_padbot_15" : ""; ?> b-post__txt_hide sbr-old-tz">
                        <?= reformat($stage->v_data['descr'], 70, 0, 0, 1)?>
                    </div>
                        <? if($stage->v_data['attach']) {?>
                        <span id="old_attach" style="display:none">
                            <div class="b-post__txt b-post__txt_padbot_10 b-post__txt_fontsize_15 b-post__txt_bold">Вложения</div>
                            <table class="b-layout__table" border="0" cellpadding="0" cellspacing="0">
                                <tbody>
                                    <? foreach($stage->v_data['attach'] as $id=>$a) {  
                                        if ($a['is_deleted'] === 't') {
                                            continue;
                                        }
                                        $aData = getAttachDisplayData(null, $a['name'], $a['path'] );?>
                                    <tr class="b-layout__tr">
                                        <td class="b-layout__middle b-layout__middle_padbot_5">
                                            <div class="b-layout__txt">
                                                <i class="b-icon b-icon_attach_<?= $aData['class_ico'] === 'unknown' ? 'unknown' : $a['ftype']?>"></i> 
                                                <a href="<?=WDCPREFIX.'/'.$a['path'].$a['name']?>" class="b-layout__link" target="_blank"><?= reformat($a['orig_name'] ? $a['orig_name'] : $aData['orig_name'], 30)?></a>, <?= ConvertBtoMB($a['size'])?>
                                            </div>
                                        </td>
                                        <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5">
                                            <div class="b-layout__txt"><a href="<?=WDCPREFIX.'/'.$a['path'].$a['name']?>" class="b-layout__link" target="_blank">Скачать</a></div>
                                        </td>
                                    </tr>
                                    <? }//foreach?>
                                </tbody>
                            </table>
                        </span>
                        <? }//if?>
                    <? } //if?>
                    <? if($size_ln_descr > 5 && !($stage->isNewVersionTZ() && ($stage_changed || $stage_changed_for_frl))) { ?>
                    <div class="b-post__weaken"></div>
                    <? } //if?>
                </div>
                <? if($size_ln_descr > 5 && !($stage->isNewVersionTZ() && ($stage_changed || $stage_changed_for_frl))) { ?>
                <div class="b-post__txt b-post__txt_padtop_20"><a id="toggler-tz" class="b-post__link b-post__link_dot_0f71c8" href="javascript:void(0)">Развернуть задание</a></div>
                <? }//if?>
            </div>
        </td>
        <td class="b-layout__right"></td>
    </tr>
</table>
</div>