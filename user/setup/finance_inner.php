<?php

if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/account.common.php");
$xajax->printJavascript('/xajax/');
$a_count = $attach ? count($attach) : 0;
$has_phone = $reqvs[sbr::FT_PHYS]['mob_phone'] != '';

?>

<?php if(!$access) { ?>
<div class="b-layout b-layout_pad_19">
    <div class="b-fon">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_bg_ffebbf">
            <div class="b-layout__txt b-layout__txt_padbot_10"><span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>Чтобы войти в &laquo;Финансы&raquo;, необходимо ввести 4 цифры из СМС, отправленного на ваш телефон:</div>
            <div class="b-combo b-combo_inline-block b-combo_valign_mid">
                <div class="b-combo__input b-combo__input_width_60 b-combo__input_height_31">
                    <input class="b-combo__input-text b-combo__input-text_center b-combo__input-text_fontsize_22 b-combo__input-text_bold" 
                           name="sms_code" type="text" size="80" value="<?= $code_debug;?>" 
                           id="auth_sms_code" 
                           onfocus="$('auth_sms_error').addClass('b-layout__txt_hide'); $(this).getParent().removeClass('b-combo__input_error')"/>
                </div>
            </div>
            <div class="b-layout__txt b-layout__txt_hide b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padleft_10 b-layout__txt_color_c10600" id="auth_sms_error">В СМС были другие 4 цифры.</div>
            <div class="b-buttons b-buttons_padtb_10 ">
                <a href="javascript:void(0)" onclick="xajax_authCode($('auth_sms_code').get('value'));" class="b-button b-button_flat b-button_flat_green">Войти</a>&#160;
                <span class="b-buttons__txt">или</span>
                <a href="javascript:void(0)" class="b-buttons__link b-buttons__link_dot_0f71c8" onclick="xajax_resendAuthCode();">выслать СМС еще раз</a>
            </div>
        </div>
    </div>
</div>
<?php } else { ?>

<script type="text/javascript">
//var SBR;
//window.addEvent('domready', function() { SBR = new Sbr('financeFrm'); } );
//Sbr.prototype.ERRORS=<?=sbr_meta::jsInputErrors($finance_error['sbr'], "ft{$form_type}[", "]")?>;
//Sbr.prototype.LOGIN='<?=$login?>';
var tmpe=<?=sbr_meta::jsInputErrors($finance_error['all'])?>;
//for(var k in tmpe)Sbr.prototype.ERRORS[k]=tmpe[k];
var FMAX=<?=account::MAX_FILE_COUNT?>;
var FCNT=<?=(account::MAX_FILE_COUNT - $a_count)?>;
function delFinAttach(id,login,noserver,err) {
    if(!noserver) {
        if(window.confirm('Вы действительно хотите удалить файл?'))
            xajax_delAttach(id,login);
        return;
    }
    if(err) alert(err);
    else if(FCNT < FMAX) {
        var fl=document.getElementById('files_list');
        var fb=document.getElementById('files_box');
        var a=document.getElementById('attach'+id);
        if(a) a.parentNode.removeChild(a);
        if(!FCNT) fb.style.display = 'block';
        var li=document.createElement('LI');
        fl.appendChild(li);
        li.innerHTML = '<input type="file" name="attach[]" size="23" />';
        FCNT++;
    }
}

function checkexts() {
    <?php $aAllowedExt = array_diff( $GLOBALS['graf_array'], array('swf') ) ?>
    var aAllowedExt = ['<?=implode("', '", $aAllowedExt )?>'];
    var val = 0;
    var grp = document.getElementById('financeFrm')['attach[]'];
    if (typeof grp.length != 'undefined') {
        for (i=0; i<grp.length; i++) {
            if (!specificExt(grp[i].value, aAllowedExt)) return false;
        }
    } else {
        if (!specificExt(grp.value, aAllowedExt)) return false;
    }
    return true;
}
</script>
<form action="" method="post" enctype="multipart/form-data" id="financeFrm" onsubmit="return checkexts()">
    <input type="hidden" name="action" value="updfin" />
    <input type="hidden" name="id" value="<?=$reqv->id?>" />
    <input type="hidden" name="form_type" id="form_type" value="<?= $form_type; ?>" />
    <input type="hidden" name="redirect_uri" value="<?=$redirect_uri?>" />
    
    
    <div class="b-layout_pad_20 b-layout_bordbot_dedfe0">
        <? if ($finance_success) { ?>
        <div class="b-fon b-fon_width_full b-fon_padbot_10">
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Изменения внесены
            </div>
        </div>
        <? } ?>
        
        <?php if($reqvs['validate_status'] == 1 && !$is_adm):?>
        <div class="b-fon b-fon_width_full b-fon_padbot_10 b-fon_margbot_20">
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
                <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span>
                Финансовая информация отправлена на модерацию. 
                Модерация осуществляется с 9:00 до 01:00 мск, 
                среднее время обработки данных составляет 15 минут.
            </div>
        </div>        
        <?php elseif ($block_finance_edit): ?>
        <div class="b-fon b-fon_width_full b-fon_padbot_10 b-fon_margbot_20">
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
                <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span>
                Обратите внимание: вы не можете самостоятельно менять финансовые данные, 
                уже указанные в ваших платежах по Безопасным сделкам. 
                Для изменения данных, пожалуйста, обратитесь в <a href="https://feedback.fl.ru/">Службу поддержки</a>.
            </div>
        </div>
        <?php elseif($reqvs['validate_status'] == -1 && !$is_adm): ?>
        <div class="b-fon b-fon_width_full b-fon_padbot_10 b-fon_margbot_20">
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
                <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span>
                Обратите внимание: на странице Финансы указаны некорректные данные: 
                <?=sbr_meta::getReqvBlockedReason($reqvs['user_id'])?>. 
                Пожалуйста, укажите корректные данные.
            </div>
        </div>
        <?php endif; ?>
        
        
        <?php if(isset($_SESSION['sms_accept_code'])): ?>
        <div class="b-layout b-layout_padbot_30">
            <div class="b-fon">
                <div class="b-fon__body b-fon__body_relative b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_bg_ffebbf">
                        <span class="b-icon b-icon_absolute b-icon_left_10 b-icon_sbr_oattent b-icon_pad_null"></span>
                        
                        <strong>
                            На телефонный номер <?=$reqvs[$form_type]['phone']?> 
                            отправлено смс-сообщение с кодом подтверждения.
                        </strong> <br/> 

                        <div class="b-layout__txt b-layout__txt_padtop_20">
                            <div class="b-layout__txt b-layout__txt_inline-block">Введите код:</div>
                            <div class="b-combo b-combo_inline-block b-combo_valign_mid">
                                <div class="b-combo__input b-combo__input_width_45">
                                    <input value="<?php if(isset($code_debug)): echo $code_debug; endif; ?>" 
                                           class="b-combo__input-text" type="text" 
                                           id="sms_accept_code" 
                                           name="sms_accept_code" 
                                           onfocus="$('sms_accept_error').addClass('b-layout__txt_hide'); $(this).getParent().removeClass('b-combo__input_error');">              
                                </div>
                            </div>
                            <div class="b-layout__txt b-layout__txt_hide b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_padleft_10 b-layout__txt_color_c10600" id="sms_accept_error"></div>
                            <div class="b-buttons b-buttons_padtop_20">
                                <a href="javascript:void(0)" 
                                   onclick="xajax_checkAcceptCode($('sms_accept_code').get('value'));" 
                                   class="b-button b-button_flat b-button_flat_green">Подтвердить</a>&#160;
                                <span class="b-buttons__txt">или</span>
                                <a href="javascript:void(0)" 
                                   class="b-buttons__link b-buttons__link_dot_0f71c8" 
                                   onclick="xajax_resendAcceptCode();">Получить код повторно</a>
                            </div>
                        </div>
                </div>
            </div>
        </div>        
        <?php endif; ?>
        

        <span class="ft<?=sbr::FT_PHYS?>_set" <?=$form_type==sbr::FT_JURI ? ' style="display:none"' : ''?>>
            <?php
            sbr::view_finance_tbl($reqvs, sbr::FT_PHYS, NULL, '', '', array(), array(
                'static' => $is_finance_allow_delete,
                'theme' => '',
                'group' => array(1, 2),
                'subdescr' => array(
                    1 => 'Иванов Иван Иванович',
                    2 => '01.01.1990'
                )
            )); 
            if ($has_phone) {
                $reqvs[sbr::FT_PHYS]['phone'] = $reqvs[sbr::FT_PHYS]['mob_phone'];
            }
            sbr::view_finance_tbl($reqvs, sbr::FT_PHYS, NULL, '', '', array(), array(
                'static' => $is_finance_allow_delete,
                'theme' => '',
                'subdescr' => array(3 => '+71234567890'),
                'disabled' => $has_phone,
                'notexample' => array(3),
                'group' => array(3,3)
            ));
            ?>
        </span>
        
        <span class="ft<?=sbr::FT_JURI?>_set" <?=$form_type==sbr::FT_PHYS ? ' style="display:none"' : ''?>>
            <?php
            sbr::view_finance_tbl($reqvs, sbr::FT_JURI, NULL, '', '', array(), array(
                'static' => $is_finance_allow_delete,
                'theme' => '',
                'group' => array(1, 2)
            )); 
            if ($has_phone) {
                $reqvs[sbr::FT_JURI]['phone'] = $reqvs[sbr::FT_JURI]['mob_phone'];
            }
            sbr::view_finance_tbl($reqvs, sbr::FT_JURI, NULL, '', '', array(), array(
                'static' => $is_finance_allow_delete,
                'theme' => '',
                'subdescr' => array(3 => '+71234567890'),
                'disabled' => $has_phone,
                'notexample' => array(3),
                'group' => array(3,3)
            ));
            ?>
        </span>

        <?php if($is_finance_allow_delete): ?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_200 b-layout__td_padbot_15 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                    <div class="b-layout__txt ">Резидентство</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_15 b-layout__td_padright_10 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                    <div class="b-layout__txt b-layout__txt_italic">
                        <?=sbr::$rez_list[$rez_type]?>
                    </div>
                </td>
            </tr>     
        </table>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_200 b-layout__td_padbot_15 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                    <div class="b-layout__txt ">Юридический статус</div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_15 b-layout__td_padright_10 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                    <div class="b-layout__txt b-layout__txt_italic">
                        <?php if($form_type == sbr::FT_PHYS): ?>
                        физическое лицо
                        <?php else: ?>
                        ИП или юридическое лицо
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>        
        <?php else: ?>
        <table class="b-layout__table b-layout__table_margbot_20">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_200 b-layout__td_padbot_15 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                    <div class="b-layout__txt ">Резидентство</div>
                </td>
                <td class="b-layout__td b-layout__td_width_240 b-layout__td_padbot_15 b-layout__td_padright_10 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                    <div class="b-radio b-radio_layout_horizontal">
                        <div class="b-radio__item">
                            <input id="_rt2" class="b-radio__input" name="rez_type" type="radio" value="<?= sbr::RT_RU ?>"<?=$rez_type!=sbr::RT_UABYKZ?' checked="checked"':'' ?> onclick="finance.switchReqvRT(<?= sbr::RT_RU ?>);" />
                            <label class="b-radio__label b-radio__label_fontsize_13" for="_rt2">резидент Российской Федерации</label>
                        </div>
                    </div>
                </td>
                <td class="b-layout__td b-layout__td_width_240 b-layout__td_padbot_15 b-layout__td_padright_10 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                    <div class="b-radio b-radio_layout_horizontal">
                        <div class="b-radio__item">
                            <input id="_rt3" class="b-radio__input" name="rez_type" type="radio" value="<?= sbr::RT_UABYKZ ?>"<?=$rez_type==sbr::RT_UABYKZ?' checked="checked"':'' ?> onclick="finance.switchReqvRT(<?= sbr::RT_UABYKZ ?>);" />
                            <label class="b-radio__label b-radio__label_fontsize_13" for="_rt3">нерезидент Российской Федерации</label>
                        </div>
                    </div>
                </td>
            </tr>     
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_200 b-layout__td_padbot_15 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad"></td>                
                <td class="b-layout__td b-layout__td_width_240 b-layout__td_padbot_15 b-layout__td_padright_10 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                    <div class="b-radio b-radio_layout_horizontal">
                        <div class="b-radio__item">
                            <input id="_rt4" class="b-radio__input" name="rez_type" type="radio" value="<?= sbr::RT_REFUGEE ?>"<?=$rez_type==sbr::RT_REFUGEE?' checked="checked"':'' ?> onclick="finance.switchReqvRT(<?= sbr::RT_REFUGEE ?>);" />
                            <label class="b-radio__label b-radio__label_fontsize_13" for="_rt4">беженец</label>
                        </div>
                    </div>
                </td>                
                <td class="b-layout__td b-layout__td_width_240 b-layout__td_padbot_15 b-layout__td_padright_10 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                    <div class="b-radio b-radio_layout_horizontal">
                        <div class="b-radio__item">
                            <input id="_rt5" class="b-radio__input" name="rez_type" type="radio" value="<?= sbr::RT_RESIDENCE ?>"<?=$rez_type==sbr::RT_RESIDENCE?' checked="checked"':'' ?> onclick="finance.switchReqvRT(<?= sbr::RT_RESIDENCE ?>);" />
                            <label class="b-radio__label b-radio__label_fontsize_13" for="_rt5">вид на жительство в РФ</label>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_200 b-layout__td_padbot_15 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                    <div class="b-layout__txt ">Юридический статус</div>
                </td>
                <td class="b-layout__td b-layout__td_width_240 b-layout__td_padbot_15 b-layout__td_padright_10 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                    <div class="b-radio b-radio_layout_horizontal">
                        <div class="b-radio__item">
                            <input id="status-fiz" class="b-radio__input" name="status" type="radio" value="1"<?=$form_type!=sbr::FT_JURI?' checked="checked"':'' ?> onclick="finance.switchReqvFT(<?= sbr::FT_JURI ?>,<?= sbr::FT_PHYS ?>);" />
                            <label class="b-radio__label b-radio__label_fontsize_13" for="status-fiz">физическое лицо</label>
                        </div>
                    </div>
                </td>
                <td id="block_status-ip" <?php if(in_array($rez_type, array(sbr::RT_REFUGEE, sbr::RT_RESIDENCE))): ?>style="display:none"<?php endif; ?> class="b-layout__td b-layout__td_width_240 b-layout__td_padbot_15 b-layout__td_padright_10 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                    <div class="b-radio b-radio_layout_horizontal">
                        <div class="b-radio__item">
                            <input id="status-ip" class="b-radio__input" name="status" type="radio" value="2"<?=$form_type==sbr::FT_JURI?' checked="checked"':'' ?> onclick="finance.switchReqvFT(<?= sbr::FT_PHYS ?>,<?= sbr::FT_JURI ?>);" />
                            <label class="b-radio__label b-radio__label_fontsize_13" for="status-ip">ИП или юридическое лицо</label>
                        </div>
                    </div>
                </td>
                <td class="b-layout__td b-layout__td_padbot_15 b-layout__td_width_full_iphone"></td>
            </tr>
        </table>
        <?php endif; ?>
    </div>
    
    
    <div class="b-layout_pad_20">
        <span class="ft<?=sbr::FT_PHYS?>_set" <?=$form_type==sbr::FT_JURI ? ' style="display:none"' : ''?>>
        <?php
            sbr::view_finance_tbl($reqvs, sbr::FT_PHYS, NULL, 'Паспортные данные', '', array(), array(
                'static' => $is_finance_allow_delete,
                'theme' => '',
                'group' => array(4, 8),
                'subdescr' => array(5 => '01.01.2000')
            ));
         ?>
        </span>
        
        <span class="ft<?=sbr::FT_JURI?>_set" <?=$form_type==sbr::FT_PHYS ? ' style="display:none"' : ''?>>
        <?php
            sbr::view_finance_tbl($reqvs, sbr::FT_JURI, NULL, 'Данные об организации или ИП', '', array(), array(
                'static' => $is_finance_allow_delete,
                'theme' => '',
                'group' => array(4, 9),
                'options' => array(4 => (array(0 => 'Выбрать тип организации') + sbr_meta::$types)),
                'notexample' => array(9)
            ));
         ?>
        </span>
        
        <?php if(!is_emp($u->role)): ?>
        <span class="ft<?=sbr::FT_PHYS?>_set" <?=($form_type==sbr::FT_JURI) ? ' style="display:none"' : ''?>>
        <?php   
            sbr::view_finance_tbl($reqvs, sbr::FT_PHYS, 'EL', 'Платежные реквизиты', '', array(), array(
                'static' => $is_finance_allow_delete,
                'theme' => '',
                'group' => array(10,12),
                'disabled' => false
            ));
            sbr::view_finance_tbl($reqvs, sbr::FT_PHYS, 'BANK', '', '', array(), array(
                'static' => $is_finance_allow_delete,
                'theme' => '',
                'group' => array(20,30),
                'notexample' => array(21),
            ));
        ?>
        </span>
        <?php endif; ?>
        
        <span class="ft<?=sbr::FT_JURI?>_set" <?=$form_type==sbr::FT_PHYS ? ' style="display:none"' : ''?>>
        <?php   
            sbr::view_finance_tbl($reqvs, sbr::FT_JURI, 'BANK', 'Платежные реквизиты', '', array(), array(
                'static' => $is_finance_allow_delete,
                'theme' => '',
                'group' => array(20,29)
            ));
        
        ?>
        </span>
<?php
    if(!is_emp($u->role)):
?>
        <span class="ft<?=sbr::FT_PHYS?>_set" <?=$form_type==sbr::FT_JURI ? ' style="display:none"' : ''?>>
            <table class="b-layout__table b-layout__table_width_full rez--itm1 rez--itm2 rez--itm3 rez--itm4">
            <tbody>
                <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_padtop_20 b-layout__td_width_200 b-layout__td_padbot_15 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                        <div class="b-layout__txt b-layout__txt b-layout__txt_padtop_5 b-layout__txt_padright_20">
                            Скан-копии страниц паспорта
                        </div>
                    </td>
                    <td id="attach_block" class="b-layout__td b-layout__td_padtop_20 b-layout__td_padbot_15 b-layout__td_padright_10 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
<?php
            // прикрепить скан
            $params = array(
                'file_description' => '
                    Необходимы начальная страница с основными данными и страница с данными о адресе регистрации (если регистрация предусмотрена законодательством вашей страны).<br/> 
                    Допустимы четко различимые сканы (или фото) страниц в файлах формата jpg, jpeg или png.',
                'button_title'  => 'Прикрепить скан',
                'new_interface' => true,
                'css_class' => 'b-file_padbot_20',
                'disabled' => $block_finance_edit,
                'req_txt' => 'Разрешенные форматы: jpg, jpeg или png.',
                'error' => isset($error['sbr']['err_attach']) ? $error['sbr']['err_attach'] : null
            );
            
            if ($is_adm):
                $params['hiddens'] = array(
                    'uid' => $uid,
                    'hash' => paramsHash(array($uid))
                );
            endif;
            
            sbr::view_finance_files('finance_doc', $attachedFilesDoc, $attachDoc, $params);
?>
                    </td>
                </tr>
            </tbody>
            </table>
        </span>
<?php
    endif;
?>
        <?php if(!$block_finance_edit): ?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
              <td class="b-layout__td b-layout__td_width_200 b-layout__td_padbot_15 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
              </td>
              <td class="b-layout__td b-layout__td_padbot_15 b-layout__td_padright_10 b-layout__td_width_full_iphone b-layout__td_pad_null_ipad">
                 <div class="b-buttons b-buttons_padtop_20">
                    <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green <?=$block_finance_edit?'b-button_disabled':'finance-save'?>">
                        Сохранить
                    </a>
                    <?php if(!$is_adm): ?>
                    <span class="b-buttons__txt"> &#160; или 
                        <a class="b-layout__link" href="<?=($redirect_uri)?urldecode($redirect_uri):'/'?>">пока не сохранять настройки</a>
                    </span>
                    <?php else: ?>
                    <a id="__finance_unblocked" 
                       href="javascript:void(0)" 
                       onclick="banned.unBlocked('23_<?php echo $reqvs['user_id'] ?>_0');"
                       class="b-button b-button_flat b-button_flat_orange <?php if($reqvs['validate_status'] == 2): ?>b-button_hide<?php endif; ?> b-button_margleft_45">
                        Подтвердить
                    </a>
                    <a id="__finance_blocked" 
                       href="javascript:void(0)" 
                       onclick="banned.delReason('23_<?php echo $reqvs['user_id'] ?>_0', 0, '', 0);"
                       class="b-button b-button_flat b-button_flat_red <?php if($reqvs['validate_status'] == -1): ?>b-button_hide<?php endif; ?> b-button_margleft_45">
                        Отклонить
                    </a>            
                    <?php endif; ?>
                 </div>                      
              </td>
            </tr>
        </table>
<?php
            if($is_adm):
                include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/del_overlay.php' );
            endif;
        
        elseif(isset($is_finance_allow_delete) && $is_finance_allow_delete):
?>
        <a id="__finance_delete" href="javascript:void(0);" class="b-button b-button_flat b-button_flat_red">
            Удалить финансовые данные
        </a>         
<?php 
        endif; 
?>
 </div>
    </div>
<?php

if(!$is_finance_allow_delete):

if ($error && isset($error['sbr']) && $error['sbr']) {
    foreach($error['sbr'] as $k=>$err) {
        $js_error['sbr'][$k] = iconv('WINDOWS-1251', 'UTF-8', $err);
    }
}

?>
<script type="text/javascript">
    window.addEvent('domready', function() {
        var finance = new Finance({form_type: '<?=$form_type?>'});
        finance.switchReqvRT(<?=$rez_type?>);
        finance.options.form_type = '<?= $form_type;?>';
        <?php if(isset($js_error)) { ?>
        if(finance) {
            finance.setErrors(<?= json_encode($js_error) ?>);
            finance.viewErrors();
            finance.viewStringErrors();
        }
        <?php } //if?>
        window.finance = finance;
    });
</script>
</form>
<style type="text/css">
.invalid{ background:none;}
</style>
<?php

endif;

}