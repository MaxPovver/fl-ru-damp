<div class="b-shadow <?= $doc['id'] ? '' : 'b-shadow_hide'?> b-shadow_width_600 b-shadow_zindex_11 b-shadow_center" id="popup_admin_files<?= $doc['id'] ? $doc['id'] : ''?>">
    <div class="b-shadow__right">
        <div class="b-shadow__left">
            <div class="b-shadow__top">
                <div class="b-shadow__bottom">
                    <div class="b-shadow__body b-shadow__body_pad_15 b-shadow__body_bg_fff">
                        <h3 class="b-shadow__title b-shadow__title_padbot_15">Новый файл</h3>
                        <? if($doc['id']) { ?>
                        <table class="b-layout__table" cellpadding="0" cellspacing="0" border="0">
                            <tr class="b-layout__tr">
                                <td class="b-layout__middle b-layout__middle_padbot_5">
                                    <div class="b-layout__txt">
                                        <i class="b-icon b-icon_attach_<?= getICOFile(CFile::getext($doc['file_name'])); ?>"></i> <a class="b-layout__link" href="<?=  ( WDCPREFIX . '/' . $doc['file_path'] . $doc['file_name']); ?>"><?= $doc['name'];?> </a>, <?= ConvertBtoMB($doc['file_size']); ?>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <? } //else?>
                        
                        <div class="b-file b-file_padbot_15">
                            <div class="b-fon b-fon_width_full attachedfiles_admin_sbr<?= $doc['id'] ? $doc['id'] : ''?>"></div>
                        </div>                  
                        <table class="b-layout__table" cellpadding="0" cellspacing="0" border="0">
                            <tr class="b-layout__tr">
                                <td class="b-layout__one b-layout__one_padright_10"><div class="b-layout__txt">Название документа:</div></td>
                                <td class="b-layout__one">
                                    <div class="b-input b-input_inline-block b-input_width_360">
                                        <input id="doc_name<?=$doc['id']?>" name="doc_name" class="b-input__text" type="text" value="<?= $doc['name']; ?>" />
                                    </div>
                                </td>
                            </tr>
                            <tr class="b-layout__tr">
                                <td class="b-layout__one b-layout__one_padright_10"><div class="b-layout__txt">Тип документа:</div></td>
                                <td class="b-layout__one">
                                    <div class="b-form__txt b-check_padbot_15">
                                        <span class="nra-doc-sel">
                                            <select name="type" id="doc_type<?=$doc['id']?>">
                                                <?
                                                  foreach(sbr::$docs_types as $type=>$val) {
                                                      if(!$sbr->isAdmin() && !($val[1] & (sbr::DOCS_ACCESS_EMP*$sbr->isEmp() | sbr::DOCS_ACCESS_FRL*$sbr->isFrl()))) continue;
                                                ?>
                                                <option value="<?=$type?>"<?=($type==$doc['type'] ? ' selected="true"' : '')?>><?=$val[0]?></option>
                                                <? } ?>
                                            </select>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="b-layout__tr">
                                <td class="b-layout__one b-layout__one_padright_10"><div class="b-layout__txt">Файл видит:</div></td>
                                <td class="b-layout__one">
                                    <div class="b-check b-check_padbot_15 b-check_padtop_3">
                                        <input id="doc_access_frl<?= $doc['id'];?>" value="<?= sbr::DOCS_ACCESS_FRL?>" name="doc_access_frl" class="b-check__input" type="checkbox" <?= ( in_array($doc['access_role'], array(sbr::DOCS_ACCESS_ALL, sbr::DOCS_ACCESS_FRL))  ? "checked" : ""); ?> />
                                        <label class="b-check__label" for="doc_access_frl<?= $doc['id'];?>">Исполнитель</label>
                                    </div>
                                    <div class="b-check b-check_padbot_15">
                                        <input id="doc_access_emp<?= $doc['id'];?>" value="<?= sbr::DOCS_ACCESS_EMP?>" name="doc_access_emp" class="b-check__input" type="checkbox" <?= ( in_array($doc['access_role'], array(sbr::DOCS_ACCESS_ALL, sbr::DOCS_ACCESS_EMP)) ? "checked" : ""); ?> />
                                        <label class="b-check__label" for="doc_access_emp<?= $doc['id'];?>">Заказчик</label>
                                    </div>
                                </td>
                            </tr>
                            
                        </table>
                        <div class="b-buttons">
                            <a class="b-button b-button_flat b-button_flat_green" href="javascript:void(0)" onclick="sendDataDocument(<?=$stage->id?>, '<?=$doc['id']?>');">Сохранить</a>&#160;&#160;&#160;
                            <span class="b-buttons__txt">или</span>
                            <a href="javascript:void(0)" class="b-buttons__link b-buttons__link_dot_0f71c8" onclick="$('popup_admin_files<?= $doc['id']?>').addClass('b-shadow_hide');">отменить</a>
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
    <div class="b-shadow__icon_close"></div>
</div>
<script>
    var sended = false;
    function sendDataDocument(sbr_id, id) {
        
        if(id == undefined) id = '';
        
        var name    = $('doc_name' + id).get('value');
        var type    = $('doc_type' + id).get('value');
        var access  = 0;
        var session = null;
        
        if($('doc_access_frl' + id).checked == true && $('doc_access_emp' + id).checked == true) {
            access = <?= sbr::DOCS_ACCESS_ALL;?>;
        } else if($('doc_access_frl' + id).checked == true) {
            access = <?= sbr::DOCS_ACCESS_FRL;?>;
        } else if($('doc_access_emp' + id).checked == true) {
            access = <?= sbr::DOCS_ACCESS_EMP;?>;
        }
        
        if( $('popup_admin_files' + id).getElement('input[name^=attachedfiles_session]') ) {
            session = $('popup_admin_files' + id).getElement('input[name^=attachedfiles_session]').get('value');
        }
        
        if(type == 0) {
            alert('Выберите тип документа из списка');
            return;
        }
        
        if(sended == false) {
            sended = true;
            xajax_aSaveDocument(sbr_id, id, name, type, access, session);
        }
    }
</script>