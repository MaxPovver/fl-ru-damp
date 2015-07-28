<h2 class="b-layout__title b-layout__title_padbot_10 b-layout__title_padtop_70">Файлы по этапу</h2>			
<? if($sbr->isAdmin()) { ?>
<table class="b-layout__table" cellpadding="0" cellspacing="0" border="0">
    <? foreach($sbr->all_docs as $k=>$doc) { 
        $access_ico = 'b-icon__role_nn';
        if($doc['access_role'] == sbr::DOCS_ACCESS_ALL) {
            $access_ico = 'b-icon__role_fe';
        } elseif($doc['access_role'] == sbr::DOCS_ACCESS_FRL) {
            $access_ico = 'b-icon__role_f'; 
        } elseif($doc['access_role'] == sbr::DOCS_ACCESS_EMP) {
            $access_ico = 'b-icon__role_e'; 
        } elseif($doc['access_role'] === null) {
            $access_ico = 'b-icon__role_fe';
        }
    ?>
    <tr class="b-layout__tr <?= ($doc['is_deleted'] == 't' ? 'b-layout__tr_bg_ffdfdf' : '');?>" id="doc_<?=$doc['id']?>">
        <td class="b-layout__left b-layout__left_padright_10 b-layout__left_padbot_5">
            <div class="b-layout__txt b-layout__txt_padtop_2 b-layout__txt_fontsize_11"><?= date('d.m.Y', strtotime($doc['sign_time'] ? $doc['sign_time'] : $doc['publ_time'])); ?></div>
        </td>
        <td class="b-layout__middle b-layout__middle_padbot_5">
            <div class="b-layout__txt">&#160;<span class="b-icon b-icon__role <?= $access_ico; ?> b-icon_top_2" ></span>&#160;&#160;<i class="b-icon b-icon_attach_<?= getICOFile(CFile::getext($doc['file_name']));?>"></i> 
                <a class="b-layout__link <?= ( $sbr->isAdmin() && $doc['type'] != sbr::DOCS_TYPE_OFFER && $doc['id'] == $doc['first_doc_id']) ? "b-layout__link_color_ee1d16" : "";?>" href="<?= WDCPREFIX; ?>/<?=$doc['file_path'] . $doc['file_name']?>" target="_blank"><?= $doc['name']?></a>, <?= ConvertBtoMB($doc['file_size'])?>
            </div>
        </td>
        <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5">
            <div class="b-layout__txt">
                <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$doc['file_path'] . $doc['file_name']?>" target="_blank">Скачать</a>
            </div>
        </td>
        <?php if($sbr->isAdmin() && $doc['type'] != sbr::DOCS_TYPE_OFFER && $doc['access_role'] != null) { ?>
        <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5">
            <div class="b-layout__txt b-layout__txt_color_fd6c30">
                <a class="b-button b-button_admin_edit b-button_margright_20" href="javascript:void(0)" onclick="xajax_aEditDocument('<?= $stage->id?>', '<?= $doc['id']?>');"></a><a class="b-button b-button_admin_del" href="javascript:void(0)" onclick="xajax_aDelDocument('<?= $stage->id?>', '<?= $doc['id']?>');"></a>
                &nbsp;&nbsp;
                <? if($doc['id'] != $doc['first_doc_id']) {?>
                <a class="b-layout__link b-layout__link_bordbot_dot_ee1d16" href="javascript:void(0)" onclick="if(confirm('Вы хотите пересоздать документ?')) xajax_aRecreateDocLC('<?= $doc['id']; ?>', '<?= $sbr->emp_id; ?>', '<?= $stage->id; ?>', 'create', 'stage');">Создать новый</a> 
                <? } else if($doc['id'] == $doc['first_doc_id']) { //if?>
                <a class="b-layout__link b-layout__link_bordbot_dot_ee1d16"  href="javascript:void(0)" onmouseover="$('doc_<?=$doc['second_doc_id']?>').addClass('b-layout__tr_loadfon')" onmouseout="$('doc_<?=$doc['second_doc_id']?>').removeClass('b-layout__tr_loadfon')" onclick="xajax_aRecreateDocLC('<?= $doc['id']?>', '<?= $sbr->frl_id; ?>', <?= $stage->id; ?>, 'remove', 'stage');" title="При восстановлении будет удален выделенный файл">Восстановить</a>
                <? }//if?>
            </div>
        </td>
        <?php } elseif($doc['type'] !== null) { //if?>
        <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5">
            <a class="b-button b-button_admin_edit b-button_margright_20" href="javascript:void(0)" onclick="xajax_aEditDocument('<?= $stage->id?>', '<?= $doc['id']?>');"></a><a class="b-button b-button_admin_del" href="javascript:void(0)" onclick="xajax_aDelDocument('<?= $stage->id?>', '<?= $doc['id']?>');"></a>
        </td>
        <?php }?>
    </tr>
    <? }//foreach?>
</table>
<div class="b-file b-file_padtop_5">
    <table cellspacing="0" cellpadding="0" border="0" class="b-file_layout">
        <tbody><tr>
                <td class="b-file__button">            
                    <div class="b-file__wrap">
                        <a href="javascript:void(0)" onclick="$('popup_admin_files').removeClass('b-shadow_hide');" class="b-button b-button_flat b-button_flat_grey">Загрузить файл</a>
                    </div>
                </td>
                <td class="b-file__text">
                    <div style="z-index: 10;" class="b-filter">
                        <div class="b-filter__body b-filter__body_padtop_10"><a href="#" class="b-filter__link b-filter__link_fontsize_11 b-filter__link_dot_41 b-fileinfo">Требования к файлам</a></div>
                        <div class="b-shadow b-filter__toggle b-shadow__margleft_-110 b-shadow__margtop_10 b-shadow_hide b-fileinfo-shadow" style="margin-left: 0pt;">
                            <div class="b-shadow__right">
                                <div class="b-shadow__left">
                                    <div class="b-shadow__top">
                                        <div class="b-shadow__bottom">
                                            <div class="b-shadow__body b-shadow__body_pad_15 b-shadow_width_270 b-shadow__body_bg_fff">
                                                <div class="b-shadow__txt b-shadow__txt_fontsize_11">Запрещенные форматы: ade, adp, chm, cmd, com, cpl, exe, hta, ins, isp, jse, lib, mde, msk, msp, mst, pif, scr, sct, shb, sys, vb, vbe, vbs, vxd, wsc, wsf, wsh</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="b-shadow__tl"></div>
                            <div class="b-shadow__tr"></div>
                            <div class="b-shadow__bl"></div>
                            <div class="b-shadow__br"></div>
                            <div class="b-shadow__icon_nosik"></div>
                            <div class="b-shadow__icon_close"></div>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>	

<span id="popup_admin_files_edit"></span>

<?php $doc = null; include($_SERVER['DOCUMENT_ROOT'] . '/sbr/admin/tpl.popup-doc.php');?>
        
<?= attachedfiles::getFormTemplate('attachedfiles_admin_sbr', 'sbr', array(
    'maxfiles' =>    1,
    'no_description' => true,
    'maxsize'  =>    sbr::MAX_FILE_SIZE
)) ?>
<script type="text/javascript">
    new attachedFiles2( $('popup_admin_files').getElement('.attachedfiles_admin_sbr'), {
        'hiddenName':   'attaches[]',
        'files':        <?= json_encode($comment_files) ?>,
        'selectors': {'template' : '.attachedfiles_admin_sbr-tpl'}
    });
</script>
<? } else {?>
<table class="b-layout__table" cellpadding="0" cellspacing="0" border="0">
    <? foreach($sbr->all_docs as $k=>$doc) {?>
    <tr class="b-layout__tr" id="doc_<?=$doc['id']?>">
        <td class="b-layout__left b-layout__left_padright_10 b-layout__left_padbot_5">
            <div class="b-layout__txt b-layout__txt_padtop_2 b-layout__txt_fontsize_11"><?= date('d.m.Y', strtotime($doc['sign_time'] ? $doc['sign_time'] : $doc['publ_time'])); ?></div>
        </td>
        <td class="b-layout__middle b-layout__middle_padbot_5">
            <div class="b-layout__txt"><i class="b-icon b-icon_attach_<?= getICOFile(CFile::getext($doc['file_name']));?>"></i> 
                <a class="b-layout__link <?= ( $sbr->isAdmin() && $doc['type'] != sbr::DOCS_TYPE_OFFER && $doc['id'] == $doc['first_doc_id']) ? "b-layout__link_color_ee1d16" : "";?>" href="<?= WDCPREFIX; ?>/<?=$doc['file_path'] . $doc['file_name']?>" target="_blank"><?= $doc['name']?></a>, <?= ConvertBtoMB($doc['file_size'])?>
            </div>
        </td>
        <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5">
            <div class="b-layout__txt">
                <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$doc['file_path'] . $doc['file_name']?>" target="_blank">Скачать</a>
            </div>
        </td>
        <?php if($sbr->isAdmin() && $doc['type'] != sbr::DOCS_TYPE_OFFER && $doc['access_role'] != null) { ?>
        <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5">
            <div class="b-layout__txt">
                <? if($doc['id'] != $doc['first_doc_id']) {?>
                <a class="b-layout__link b-layout__link_bordbot_dot_ee1d16" href="javascript:void(0)" onclick="xajax_aRecreateDocLC('<?= $doc['id']; ?>', '<?= $sbr->emp_id; ?>', '<?= $stage->id; ?>', 'create', 'stage');">Создать новый</a> 
                <? } else if($doc['id'] == $doc['first_doc_id']) { //if?>
                <a class="b-layout__link b-layout__link_bordbot_dot_ee1d16"  href="javascript:void(0)" onmouseover="$('doc_<?=$doc['second_doc_id']?>').addClass('b-layout__tr_loadfon')" onmouseout="$('doc_<?=$doc['second_doc_id']?>').removeClass('b-layout__tr_loadfon')" onclick="xajax_aRecreateDocLC('<?= $doc['id']?>', '<?= $sbr->frl_id; ?>', <?= $stage->id; ?>, 'remove', 'stage');" title="При восстановлении будет удален выделенный файл">Восстановить</a>
                <? }//if?>
            </div>
        </td>
        <?php }//if?>
    </tr>
    <? }//foreach?>
</table>
<? }//else?>