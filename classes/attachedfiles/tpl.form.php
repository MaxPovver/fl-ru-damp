<?php

$is_disabled = (isset($params['disabled']) && $params['disabled'] == true);
$hiddens = isset($params['hiddens']) && is_array($params['hiddens'])? $params['hiddens'] : array();

?>
<div class="<?= $cssName ?>-tpl attachedfiles-tpl" style="display: none; " id="attached_<?= $attachedfiles_session;?>">
    <input type="hidden" name="attachedfiles_type" value="<?= $attached_type ?>" />
    <input type="hidden" name="<?= $attachedfiles_session ? "attachedfiles_session" : "attachedfiles_session[]"?>" value="<?= $attachedfiles_session ?>"/>
    <?php if($hiddens): ?>
        <?php foreach($hiddens as $name => $value): ?>
    <input type="hidden" name="attachedfiles_<?=$name?>" value="<?= $value ?>" />
        <?php endforeach; ?>
    <?php endif; ?>
    <table class="b-icon-layout__table attachedfiles_table" cellpadding="0" cellspacing="0" border="0">
        <tr style="display:none" class="b-icon-layout__tr attachedfiles_template">
            <td class="b-icon-layout__icon b-icon-layout__icon_height_25"><i class="b-icon"></i></td>
            <td class="b-icon-layout__files b-icon-layout__files_fontsize_13 b-layout__td_padtop_4">
                <span class="b-layout__txt"><a href="javascript:void(0)" class="b-icon-layout__link b-icon-layout__link_fontsize_13">&nbsp;</a></span>&#160;&#160;
                <?php if(!$is_disabled): ?>
                <a href="javascript:void(0)" class="b-button b-button_admin_del b-button_margtop_-5"></a>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <div class='attachedfiles_error' style='display: none;'>
        <table class='b-icon-layout'>
            <tr>
                <td class='b-icon-layout__icon' style="vertical-align:top"><span class="b-icon b-icon_sbr_rattent"></span></td>
                <td class='b-icon-layout__files'><div class="b-layout__txt attachedfiles_errortxt"></div></td>
                <td class='b-icon-layout__operate' style="vertical-align:top"><div class="b-layout__txt">&#160;<a class='b-layout__link b-layout__link_bordbot_dot_000 attachedfiles_hide_error' href='javascript:void(0)' onclick="this.getParent('.attachedfiles_error').hide()">Скрыть</a></div></td>
            </tr>
        </table>
    </div>
    <div class='attachedfiles_uploadingfile' style='display:none'>
        <table class='b-icon-layout wdh100'>
            <tr>
                <td class='b-icon-layout__icon'><img class='b-fon__loader load-spinner' src='/images/load_fav_btn.gif' alt='' width='24' height='24' /></td>
                <td class='b-icon-layout__files'><div class="b-layout__txt b-layout__txt_nowrap">Идет загрузка файла…</div></td>
                <td class='b-icon-layout__size'>&nbsp;</td>
                <td class='b-icon-layout__operate'>&nbsp;</td>
            </tr>
        </table>
    </div>
    <div class='' style='display: none;'>
        <table class='b-icon-layout wdh100'>
            <tr class="attachedfiles_deletingfile">
                <td class='b-icon-layout__icon'><img class='b-fon__loader load-spinner' src='/images/load_fav_btn.gif' alt='' width='24' height='24' /></td>
                <td class='b-icon-layout__files'><div class="b-layout__txt b-layout__txt_nowrap">Идет удаление файла…</div></td>
                <td class='b-icon-layout__size'>&nbsp;</td>
                <td class='b-icon-layout__operate'>&nbsp;</td>
            </tr>
        </table>
    </div>
    <div class='b-fon__item attachedfiles_error' style='display: none;'>
        <table class='b-icon-layout wdh100'>
            <tr>
                <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/ico_error.gif' alt='' width='22' height='18' /></td>
                <td id='attachedfiles_errortxt' class='b-icon-layout__files'></td>
                <td class='b-icon-layout__operate'><a class='b-icon-layout__link b-icon-layout__link_dot_666' href='#' onClick='attachedFiles.hideError(); return false;'>Скрыть</a></td>
            </tr>
        </table>
    </div>
    <?php if(!$is_disabled): ?>
    <table class="b-file_layout" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td class="b-file__button">            
                <div class="b-file__wrap attachedfiles_file_div">
                    <input class="b-file__input" name='attachedfiles_file' type="file" onclick="if($(this).getParent().getElement('.attachedfiles_button').hasClass('b-button_disabled')) return false;" />
                    <a class="b-button b-button_flat b-button_flat_grey attachedfiles_button" href="javascript:void(0)" onclick="if($(this).hasClass('b-button_disabled')) return false;"><?= $params['button_title'] ? $params['button_title'] : "Прикрепить файл" ?></a>
                </div>
            </td>
            <td class="b-file__text">
                <? if($params['new_interface']) {?>
                <div style="z-index: 10;" class="b-filter">
                    <div class="b-filter__body b-filter__body_padtop_5">
                        <div class="b-file__txt b-file__txt_relative b-file__txt_padleft_15">
                            <a href="javascript:void(0)" class="b-filter__link b-filter__link_fontsize_11 b-filter__link_dot_41 b-fileinfo">Требования к файлам</a>
                            <div class="b-shadow b-filter__toggle b-shadow_hide b-shadow_left_-90 b-shadow_top_15 b-fileinfo-shadow">
                                                <div class="b-shadow__body b-shadow__body_pad_15 b-shadow_width_270 b-shadow__body_bg_fff">
                                                    <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">Разрешается добавлять не более <span class="b-shadow__txt b-shadow__txt_bold"><?= $params['maxfiles'] . ending($params['maxfiles'], ' файла', ' файлов', ' файлов') ?></span> объемом не более <?= ConvertBtoMB($params['maxsize']) ?>.</div>
                                                    <?php if($params['graph_hint']) {?>
                                                    <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">jpg и gif размером <span class="b-shadow__txt b-shadow__txt_bold">600х1000 пикс.</span> и весом не более 300 КБ будут вставлены в текст поста, остальные файлы будут приложены к нему.</div>
                                                    <? } elseif(isset($params['req_txt'])) {//if?>
                                                    <div class="b-shadow__txt b-shadow__txt_fontsize_11"><?=$params['req_txt']?></div>
                                                    <?php } else { ?>
                                                    <div class="b-shadow__txt b-shadow__txt_fontsize_11">Запрещенные форматы: ade, adp, chm, cmd, com, cpl, exe, hta, ins, isp, jse, lib, mde, msk, msp, mst, pif, scr, sct, shb, sys, vb, vbe, vbs, vxd, wsc, wsf, wsh</div>
                                                    <? } ?>
                                                </div>
                                <div class="b-shadow__icon_nosik"></div>
                                <div class="b-shadow__icon_close"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <? } elseif (!$params['no_description']) { ?>
                    <? if ($params['file_description']) { ?>
                    <div class="b-filter__body_padtop_10">
                        <div class="b-file__txt b-file__txt_padleft_15 b-file__txt_indent_-13"><?= $params['file_description'] ?></div>
                    </div>
                    <? } ?>
                    <div class="b-filter">
                        <div class="b-filter__body b-filter__body_padtop_10">
                            <a href="javascript:void(0)" class="b-filter__link b-filter__link_fontsize_11 b-filter__link_dot_41 b-fileinfo">Требования к файлам</a>
                        </div>
                        <div id="attachedfiles_info" class="b-shadow b-filter__toggle b-shadow_hide b-shadow_top_30 b-shadow_zindex_110 b-fileinfo-shadow" style="z-index:110">
                                            <div class="b-shadow__body b-shadow__body_pad_15 b-shadow_width_270 b-shadow__body_bg_fff">
                                                <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">Разрешается добавлять не более <span class="b-shadow__txt b-shadow__txt_bold"><?= $params['maxfiles'] . ending($params['maxfiles'], ' файла', ' файлов', ' файлов') ?></span> объемом не более <?= ConvertBtoMB($params['maxsize']) ?>.</div>
                                                <?php if($params['graph_hint']) {?>
                                                    <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">jpg и gif размером <span class="b-shadow__txt b-shadow__txt_bold">600х1000 пикс.</span> и весом не более 300 КБ будут вставлены в текст поста, остальные файлы будут приложены к нему.</div>
                                                <?php } elseif ($params['graph_carusel']) { ?>
                                                    <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">Разрешенные форматы: jpg, png, размером не более <span class="b-shadow__txt b-shadow__txt_bold">1000х1000 пикс.</span></div>
                                                <? } elseif ($params['graph_userpic']) { ?>
                                                    <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">Разрешенные форматы: jpg, png, размером не более <span class="b-shadow__txt b-shadow__txt_bold">100х100 пикс.</span></div>
                                                <? }//if?>
                                                <? if (!$params['no_forbidden_formats']) { ?>
                                                <div class="b-shadow__txt b-shadow__txt_fontsize_11">Запрещенные форматы: ade, adp, chm, cmd, com, cpl, exe, hta, ins, isp, jse, lib, mde, msk, msp, mst, pif, scr, sct, shb, sys, vb, vbe, vbs, vxd, wsc, wsf, wsh</div>
                                                <? } ?>
                                            </div>
                            <div class="b-shadow__icon_nosik b-shadow__icon_left_50"></div>
                            <div id="attachedfiles_close_info" class="b-shadow__icon_close"></div>
                        </div>
                    </div>
                <? } ?>
            </td>
        </tr>
    </table>
    <? if ($params['file_description'] && $params['new_interface']) { ?>
    <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_fontsize_11"><?= $params['file_description'] ?></div>
    <? }//if?>
    <?php endif; ?>
</div>