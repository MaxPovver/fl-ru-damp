<script type="text/javascript">
window.addEvent('domready', 
    function() {
        init_event_buttons();
        calcAmmountOfOption($$('.scalc-click, .scalc-change'), $('scalc_result'));
        category  = <?= (int) $category;?>;
        
        setMinAvgMaxBudgetPrice();
        changeBudgetSlider();
        
        <?php if($data['kind'] == 7) { ?>
        setTypeProject(1);
        <?php }//if?>
    }
); 
  
var pTopPrice = <?= round($pTopPrice,2)?>;
var cTopPrice = <?= round($cTopPrice,2);?>;
  
var fRUB = <?=$prj_exrates['41']?>;
var fEUR = <?=$prj_exrates['31']?>;
var fUSD = <?=$prj_exrates['21']?>;

var tRUB = <?=$prj_exrates['14']?>;
var tEUR = <?=$prj_exrates['13']?>;
var tUSD = <?=$prj_exrates['12']?>;

var budget_price = new Array();
budget_price['prj'] = new Array();
budget_price['hour'] = new Array();
budget_price['prj']['min'] = new Array();
budget_price['prj']['avg'] = new Array();
budget_price['prj']['max'] = new Array();
budget_price['hour']['min'] = new Array();
budget_price['hour']['avg'] = new Array();
budget_price['hour']['max'] = new Array();
<? foreach ($categories as $cat) { ?>
    budget_price['prj']['min'][<?=$cat['id']?>] = new Array();
    budget_price['prj']['avg'][<?=$cat['id']?>] = new Array();
    budget_price['prj']['max'][<?=$cat['id']?>] = new Array();
    budget_price['hour']['min'][<?=$cat['id']?>] = new Array();
    budget_price['hour']['avg'][<?=$cat['id']?>] = new Array();
    budget_price['hour']['max'][<?=$cat['id']?>] = new Array();
    <?
    $ncount_prj = 0;
    $ncount_hour = 0;  
    $nsum_min_prj = 0;
    $nsum_max_prj = 0;
    $nsum_avg_prj = 0;
    $nsum_min_hour = 0;
    $nsum_max_hour = 0;
    $nsum_avg_hour = 0;
    ?>
    <? if(is_array($professions[$cat['id']])) foreach ($professions[$cat['id']] as $subcat) { ?>
        budget_price['hour']['min'][<?=$cat['id']?>][<?=$subcat['id']?>] = <?=$subcat['min_cost_hour']?>;
        budget_price['hour']['avg'][<?=$cat['id']?>][<?=$subcat['id']?>] = <?=$subcat['avg_cost_hour']?>;
        budget_price['hour']['max'][<?=$cat['id']?>][<?=$subcat['id']?>] = <?=$subcat['max_cost_hour']?>;
        budget_price['prj']['min'][<?=$cat['id']?>][<?=$subcat['id']?>] = <?=$subcat['min_cost_prj']?>;
        budget_price['prj']['avg'][<?=$cat['id']?>][<?=$subcat['id']?>] = <?=$subcat['avg_cost_prj']?>;
        budget_price['prj']['max'][<?=$cat['id']?>][<?=$subcat['id']?>] = <?=$subcat['max_cost_prj']?>;
        <?
        $nsum_min_prj = $nsum_min_prj + $subcat['min_cost_prj'];
        $nsum_max_prj = $nsum_max_prj + $subcat['max_cost_prj'];
        $nsum_avg_prj = $nsum_avg_prj + $subcat['avg_cost_prj'];
        $nsum_min_hour = $nsum_min_hour + $subcat['min_cost_hour'];
        $nsum_max_hour = $nsum_max_hour + $subcat['max_cost_hour'];
        $nsum_avg_hour = $nsum_avg_hour + $subcat['avg_cost_hour'];
        if($subcat['avg_cost_prj']!=0) $ncount_prj++;
        if($subcat['avg_cost_hour']!=0) $ncount_hour++;
        ?>
    <? } ?>
    <?
    if($ncount_prj==0) $ncount_prj = 1;
    if($ncount_hour==0) $ncount_hour = 1;
    ?>
    budget_price['prj']['min'][<?=$cat['id']?>][0] = <?=round(($nsum_min_prj/$ncount_prj),0)?>;
    budget_price['prj']['avg'][<?=$cat['id']?>][0] = <?=round(($nsum_avg_prj/$ncount_prj),0)?>;
    budget_price['prj']['max'][<?=$cat['id']?>][0] = <?=round(($nsum_max_prj/$ncount_prj),0)?>;
    budget_price['hour']['min'][<?=$cat['id']?>][0] = <?=round(($nsum_min_hour/$ncount_hour),0)?>;
    budget_price['hour']['avg'][<?=$cat['id']?>][0] = <?=round(($nsum_avg_hour/$ncount_hour),0)?>;
    budget_price['hour']['max'][<?=$cat['id']?>][0] = <?=round(($nsum_max_hour/$ncount_hour),0)?>;
<? } ?>

</script>

<?
if ($error['name']) {
    $el = 'prj-name-hashtag';
} elseif ($error['descr']) {
    $el = 'prj-descr-hashtag';
} elseif ($error['category']) {
    $el = 'prj-category-hashtag';
} elseif ($error['cost']) {
    $el = 'prj-cost-hashtag';
} elseif ($error['currency']) {
    $el = 'prj-currency-hashtag';
} elseif ($error['end_date']) {
    $el = 'prj-enddate-hashtag';
} elseif ($error['win_date']) {
    $el = 'prj-windate-hashtag';
} elseif ($error['logo_image']) {
    $el = 'prj-logoimage-hashtag';
} elseif ($error['logo_link']) {
    $el = 'logo_block_link';
}
?>
<script type="text/javascript">
window.addEvent('domready', function(){
    // прокрутка к элементу
    function scrollToEl (el) {
        var el = $(el), xScroll, yScroll, userBarHeight = 45;
        if (el) {
            xScroll = window.getScroll().x;
            yScroll = el.getPosition().y - userBarHeight;
            yScroll = yScroll < 0 ? 0 : yScroll;
            window.scrollTo(xScroll, yScroll);
        }
    }    
    scrollToEl('<?= $el ?>')    
});
</script>

<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right ">
    <form method="post" enctype="multipart/form-data" id="frm" name="frm">
        <input type="hidden" name="action" value="create_project"/>
        <input type="hidden" name="kind" id="kind" value="<?= $data['kind'] == 7 ? "contest" : "project"; ?>">
        <input type="hidden" name="r_category" value="<?= $category; ?>" id="h_category">
        <input type="hidden" name="r_subcategory" value="<?= $subcategory; ?>" id="h_subcategory">
        <input type="hidden" name="r_priceby" value="<?= $data['priceby'] !== null ? $data['priceby'] : -1?>" id="r_priceby">
        <input type="hidden" name="r_currency" value="<?= $data['currency'] !== null ? $data['currency'] : -1?>" id="r_currency">
        <?php if($data['logo_id']) { ?>
        <input type="hidden" name="logo_id" value="<?= $data['logo_id']?>" id="logo_id">
        <?php }//if?>
    <div class="b-layout__txt b-layout__txt_padbot_40">Самый простой способ найти исполнителя – опубликовать проект. Проекты бывают трех типов: фри-ланс-проект, конкурс и вакансия в офис.</div>
    <div class="b-buttons b-buttons_margleft_-4 b-buttons_padbot_20">
        <a href="javascript:void(0)" class="b-button b-button_rectangle_color_transparent b-button_toggle <?= $data['kind'] != 7 ? "b-button_active" : ""; ?> " onclick="setTypeProject(0)">
            <span class="b-button__b1">
                <span class="b-button__b2 b-button__b2_padlr_10">
                    <span class="b-button__txt">Добавить проект</span>
                </span>
            </span>
        </a>&#160;&#160;
        <a href="javascript:void(0)" class="b-button b-button_rectangle_color_transparent b-button_toggle  <?= $data['kind'] == 7 ? "b-button_active" : ""; ?>" onclick="setTypeProject(1)">
            <span class="b-button__b1">
                <span class="b-button__b2 b-button__b2_padlr_10">
                    <span class="b-button__txt">Добавить конкурс</span>
                </span>
            </span>
        </a>
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_15 project-elm"><span class="b-layout__bold">Проект</span> предполагает разовое задание, которое может быть выполнено удаленно. В описании проекта необходимо максимально подробно описать суть задачи, а еще лучше – приложить детальное техзадание (ТЗ) на выполнение работы.</div>
    <div class="b-layout__txt b-layout__txt_padbot_15 b-layout_hide contest-elm">Создайте <span class="b-layout__bold">конкурс</span>, если хотите выбрать лучшего исполнителя по результатам выполнения тестового задания. В описании конкурса нужно указать суть тестовой задачи, условия проведения конкурса и критерии выбора победителя.</div>
    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout_hide contest-elm">Публикация конкурса платная &mdash; <span class="b-layout__txt b-layout__txt_bold b-layout__txt_color_fd6c30"><?= new_projects::getKonkursPrice();?> рублей</span></div>
    <div class="b-check b-check_padtop_3 b-check_padbot_20 project-elm">
        <input id="in_office" class="b-check__input" name="in_office" type="checkbox" value="1" <?= $data['kind'] == 4 ? "checked='checked'" : ""?>/>
        <label for="in_office" class="b-check__label b-check__label_fontsize_13">Исполнитель нужен для работы в офисе</label>
    </div>
    <? if ( $error['project'] ) { ?>
    <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
        <span class="b-form__error"></span> <?= $error['project']?>
    </div>
    <? } ?>
    <div class="b-layout b-layout_margleft_-80">
        <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_80"><div class="b-layout__txt b-layout__txt_padtop_5">Заголовок</div></td>
                <td class="b-layout__right b-layout__right_padbot_20">
                    <div class="b-combo" id="prj-name-hashtag">
                        <div class="b-combo__input <?=($error['name'] ? 'b-combo__input_error' : '')?>">
                            <input type="text" size="80" name="name" maxlength="60" class="b-combo__input-text" value="<?= stripslashes($data['name'])?>" onfocus="clearErrorPrjBlock(this)"/>
                        </div>
                    </div>
                </td>
            </tr>
            <? if ( $error['name'] ) { ?>
            <tr id="errPrjField_name" class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_55"></td>
                <td class="">
                    <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                        <span class="b-form__error"></span> <?=$error['name']?>
                    </div>
                </td>
            </tr>
            <? } ?>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_80 b-layout__left_padbot_15"><div class="b-layout__txt b-layout__txt_padtop_1" id="condition_descr">Задание</div></td>
                <td class="b-layout__right b-layout__right_padbot_15">
                    <div id="prj-descr-hashtag" class="b-textarea <?=($error['descr'] ? 'b-textarea_error' : '')?>">
                        <textarea name="descr" rel="5000" class="b-textarea__textarea b-textarea__textarea_height_100 tawl" cols="80" rows="5" onfocus="clearErrorPrjBlock(this)"><?= stripslashes($data['descr'])?></textarea>
                    </div>
                </td>
            </tr>
            <? if ( $error['descr'] ) { ?>
            <tr id="errPrjField_descr" class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_55"></td>
                <td class="">
                    <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                        <span class="b-form__error"></span> <?= $error['descr']?>
                    </div>
                </td>
            </tr>
            <? } ?>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_80">&#160;</td>
                <td class="b-layout__right b-layout__right_padbot_15">
                    <div class="b-form b-file">
                        <div class="b-fon">
                            <div id="attachedfiles" class="b-fon__body_pad_10 b-icon-layout i-button">
                                <table id="attachedfiles_table" class="b-icon-layout__table" cellpadding="0" cellspacing="0" border="0">
                                    <tr id="attachedfiles_template" style="display:none" class="b-icon-layout__tr">
                                        <td class="b-icon-layout__icon"><i class="b-icon"></i></td>
                                        <td class="b-icon-layout__files"><a href="javascript:void(0)" class="b-icon-layout__link">&nbsp;</a>&nbsp;</td>
                                        <td class="b-icon-layout__operate b-icon-layout__operate_padleft_10"><a href="javascript:void(0)" class="b-button b-button_m_delete"></a></td>
                                    </tr>
                                </table>
                                <div id='attachedfiles_error' style='display: none;'>
                                    <table class='b-icon-layout wdh100'>
                                        <tr>
                                            <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/ico_error.gif' alt='' width='22' height='18'></td>
                                            <td class='b-icon-layout__files' id='attachedfiles_errortxt' colspan='2'></td>
                                            <td class='b-icon-layout__operate'><a id="attachedfiles_hide_error" class='b-icon-layout__link b-icon-layout__link_dot_666' href='javascript:void(0)'>Скрыть</a></td>
                                        </tr>
                                    </table>
                                </div>
                                <div id='attachedfiles_uploadingfile' style='display:none'>
                                    <table class='b-icon-layout wdh100'>
                                        <tr>
                                            <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/loader-gray.gif' alt='' width='24' height='24'></td>
                                            <td class='b-icon-layout__files'>Идет загрузка файла…</td>
                                            <td class='b-icon-layout__size'>&nbsp;</td>
                                            <td class='b-icon-layout__operate'>&nbsp;</td>
                                        </tr>
                                    </table>
                                </div>
                                <div id='attachedfiles_deletingfile' style='display: none;'>
                                    <table class='b-icon-layout wdh100'>
                                        <tr>
                                            <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/loader-gray.gif' alt='' width='24' height='24'></td>
                                            <td class='b-icon-layout__files'>Идет удаление файла…</td>
                                            <td class='b-icon-layout__size'>&nbsp;</td>
                                            <td class='b-icon-layout__operate'>&nbsp;</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class='b-fon__item' id='attachedfiles_error' style='display: none;'>
                                    <table class='b-icon-layout wdh100'>
                                        <tr>
                                            <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/ico_error.gif' alt='' width='22' height='18'></td>
                                            <td class='b-icon-layout__files' id='attachedfiles_errortxt' colspan='2'></td>
                                            <td class='b-icon-layout__operate'><a class='b-icon-layout__link b-icon-layout__link_dot_666' href='#' onClick='attachedFiles.hideError(); return false;'>Скрыть</a></td>
                                        </tr>
                                    </table>
                                </div>
                                <table class="b-file_layout" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td class="b-file__button">            
                                            <div class="b-file__wrap" id="attachedfiles_file_div">
                                                <input id="attachedfiles_file" name='attachedfiles_file' class="b-file__input" type="file" />
                                                <a href="javascript:void(0)" class="b-button b-button_rectangle_color_transparent">
                                                    <span class="b-button__b1">
                                                        <span class="b-button__b2">
                                                            <span class="b-button__txt">Прикрепить файлы</span>
                                                        </span>
                                                    </span>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="b-file__text">
                                            <div class="b-filter">
                                                <div class="b-filter__body b-filter__body_padtop_10">
                                                    <a href="javascript:void(0)" class="b-filter__link b-filter__link_fontsize_11 b-filter__link_dot_41" id="hint_files">Требования к файлам</a>
                                                </div>
                                                <div id="attachedfiles_info" class="b-shadow b-filter__toggle b-shadow_hide b-shadow__margleft_-110 b-shadow__margtop_10">
                                                    <div class="b-shadow__right">
                                                        <div class="b-shadow__left">
                                                            <div class="b-shadow__top">
                                                                <div class="b-shadow__bottom">
                                                                    <div class="b-shadow__body b-shadow__body_pad_15 b-shadow_width_270 b-shadow__body_bg_fff">
                                                                        <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">Разрешается добавлять не более <span class="b-shadow__txt b-shadow__txt_bold">10 файлов</span> общим объемом не более 5 МБ.</div>
                                                                        <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_padbot_5">jpg и gif размером <span class="b-shadow__txt b-shadow__txt_bold">600х1000 пикс.</span> и весом не более 300 КБ будут вставлены в текст поста, остальные файлы будут приложены к нему.</div>
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
                                                    <div id="attachedfiles_close_info" class="b-shadow__icon_close"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <script type="text/javascript">
                        window.addEvent('domready', function(){
                            $(document.body).addEvent('click', function(){
                                $('attachedfiles_info').addClass('b-shadow_hide');
                            });
                            $('hint_files').removeEvents('click').addEvent('click', function(e){ e.stop(); $('attachedfiles_info').toggleClass('b-shadow_hide');});
                            $('attachedfiles_close_info').removeEvents('click').addEvent('click', function(){ e.stop(); $('attachedfiles_info').toggleClass('b-shadow_hide');});
                        });
                        
                        (function () {
                            var attachedfiles_list = new Array();
                            <?php
                            if ($action == 'create_project') {
                                $attachedfiles = new attachedfiles($attachedfiles_session);
                                $attachedfiles_files = $attachedfiles->getFiles();
                            } else {
                                $attachedfiles = new attachedfiles();
                                $attachedfiles_files = $attachedfiles->getFilesForWizard($existPrjID);
                            }

                            if($attachedfiles_files) {
                                $n = 0;
                                foreach($attachedfiles_files as $attachedfiles_file) {
                                    echo "attachedfiles_list[{$n}] = new Object;\n";
                                    echo "attachedfiles_list[{$n}].id = '".md5($attachedfiles_file['id'])."';\n";
                                    echo "attachedfiles_list[{$n}].name = '{$attachedfiles_file['orig_name']}';\n";
                                    echo "attachedfiles_list[{$n}].path = '".WDCPREFIX."/{$attachedfiles_file['path']}{$attachedfiles_file['name']}';\n";
                                    echo "attachedfiles_list[{$n}].size = '".ConvertBtoMB($attachedfiles_file['size'])."';\n";
                                    echo "attachedfiles_list[{$n}].type = '{$attachedfiles_file['type']}';\n";
                                    $n++;
                                }
                            }
                            ?>
                            
                            attachedFiles.initComm('attachedfiles', 
                                               '<?= $attachedfiles->getSession()?>',
                                               attachedfiles_list, 
                                               '<?= wizard::MAX_FILE_COUNT?>',
                                               '<?= wizard::MAX_FILE_SIZE?>',
                                               '<?= implode(', ', $GLOBALS['disallowed_array'])?>',
                                               'wizard',
                                               '<?=$wizard->uid?>',
                                               '/attachedfiles_wizard.php'
                                               );
                            })();
                    </script>
                    <input type='hidden' id='attachedfiles_uid' name='attachedfiles_uid' value='<?=get_uid(false)?>'>
                    <input type='hidden' id='attachedfiles_action' name='attachedfiles_action' value=''>
                    <input type='hidden' id='attachedfiles_delete' name='attachedfiles_delete' value=''>
                    <input type='hidden' id='attachedfiles_type' name='attachedfiles_type' value='wizard'>
                    <input type='hidden' id='attachedfiles_session' name='attachedfiles_session' value='<?=$attachedfiles->getSession()?>'>
                    <iframe id='attachedfiles_hiddenframe' name='attachedfiles_hiddenframe' style='display: none;'></iframe>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_80"><div class="b-layout__txt b-layout__txt_padtop_4">Разделы</div></td>
                <td class="b-layout__right b-layout__right_padbot_20">
                    <div class="b-combo" id="prj-category-hashtag">
                        <div class="b-combo__input b-combo__input_width_150 b-combo__input_multi_dropdown b-combo__input_resize b-combo__input_arrow_yes b-combo__input_init_professionsList drop_down_default_<?= $subcategory ? (int)$subcategory : (int)$category?> multi_drop_down_default_column_<?= $subcategory ? "1" : "0"?> <?=($error['category'] ? 'b-combo__input_error' : '')?>">
                            <input type="text" value="<?= $data['categories'] ? $category_name : "Выберите раздел";?>" size="80" name="category" class="b-combo__input-text b-combo__input-text_fontsize_15" id="category" onchange="saveCatValue(); setMinAvgMaxBudgetPrice(); changeBudgetSlider();" onfocus="clearErrorPrjBlock(this)">
                            <label for="category" class="b-combo__label b-combo__label_fontsize_15"></label>
                            <span id="category_arrow" class="b-combo__arrow"></span>
                        </div>
                    </div>
                </td>
            </tr>
            <? if ( $error['category'] ) { ?>
            <tr id="errPrjField_category"  class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_55"></td>
                <td class="">
                    <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                        <span class="b-form__error"></span> <?= $error['category']?>
                    </div>
                </td>
            </tr>
            <? } ?>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_80 b-layout__left_padbot_20"><div class="b-layout__txt" id="name_of_payment">Бюджет</div></td>
                <td class="b-layout__right b-layout__right_padbot_20">
                    <div class="b-check b-check_padtop_3 b-check_padbot_10">
                        <input id="agreement" class="b-check__input" name="agreement" type="checkbox" value="1" />
                        <label for="agreement" class="b-check__label b-check__label_fontsize_13">По договоренности</label>
                    </div>
                    <div id="prj-cost-hashtag" class="b-combo b-combo_inline-block b-combo_margright_10">
                        <div class="b-combo__input b-combo__input_width_100 <?=($error['cost'] ? 'b-combo__input_error' : '')?>">
                            <input id="f3" class="b-combo__input-text" name="cost" type="text" size="80" value="<?= $data['cost']?>" onchange="chkcost(this)" onfocus="clearErrorPrjBlock(this, 'budget')"/>
                        </div>
                    </div>

                    <div id="prj-currency-hashtag" class="b-combo b-combo_inline-block b-combo_margright_10 b-combo_zindex_2">
                        <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_125 b-combo__input_resize b-combo__input_min-width_40 b-combo__input_arrow_yes b-combo__input_init_currency_data drop_down_default_<?= isset($data['currency']) ? (int) $data['currency'] : '-1';?> multi_drop_down_default_column_0 <?=($error['currency'] ? 'b-combo__input_error' : '')?>" readonly="readonly">
                            <input type="text" readonly="readonly" value="<?= $currency_name ? $currency_name : "Выберите валюту"?>" size="80" name="currency" class="b-combo__input-text b-combo__input-text_fontsize_15" id="currency" onchange="setMinAvgMaxBudgetPrice(); changeBudgetSlider(); saveChangeSingleValue('currency');" onfocus="clearErrorBlock(this, 'b-layout__right')">
                            <label for="currency" class="b-combo__label"></label>
                            <span id="currency_arrow" class="b-combo__arrow"></span>
                        </div>
                    </div>							
                    <div class="b-combo b-combo_inline-block b-combo_margright_10 b-combo_zindex_2">
                        <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_140 b-combo__input_arrow_yes b-combo__input_resize b-combo__input_init_cost_data drop_down_default_<?= isset($data['priceby']) ? (int) $data['priceby'] : '-1';?> multi_drop_down_default_column_0 <?=($error['priceby'] ? 'b-combo__input_error' : '')?> ">
                            <input readonly="readonly" type="text" value="<?= $priceby_name ? $priceby_name : "Выберите из списка"?>" size="80" name="priceby" class="b-combo__input-text b-combo__input-text_fontsize_15" id="priceby" onchange="setMinAvgMaxBudgetPrice(); changeBudgetSlider(); saveChangeSingleValue('priceby');" onfocus="clearErrorPrjBlock(this, 'budget')">
                            <label for="priceby" class="b-combo__label"></label>
                            <span id="cost_arrow" class="b-combo__arrow"></span>
                        </div>
                    </div>	
                    
                    <input type="hidden" name="budget_type" id="fbudget_type" value="<?= intval($project['budget_type']) ?>" onfocus="clearErrorPrjBlock(this, 'budget')">
                </td>
            </tr>
            <? if ( $error['cost'] || $error['currency'] || $error['priceby'] ) { ?>
            <tr  id="errPrjField_budget" class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_55"></td>
                <td class="">
                    <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                        <span class="b-form__error"></span> <?=($error['cost']? $error['cost']: ($error['currency']? $error['currency']: ($error['priceby'])))?>
                    </div>
                </td>
            </tr>
            <? } ?>
            <tr class="b-layout__tr b-layout_hide contest-elm">
                <td class="b-layout__left b-layout__left_width_80"><div class="b-layout__txt b-layout__txt_padtop_4">Окончание</div></td>
                <td class="b-layout__right b-layout__right_padbot_20">
                    <div id="prj-enddate-hashtag" class="b-combo">
                        <div class="b-combo__input b-combo__input_width_150 b-combo__input_calendar b-combo__input_resize b-combo__input_arrow_yes date_format_use_text date_min_limit_<?=date('Y_m_d', strtotime("+1 day"))?> <?=($error['end_date'] ? 'b-combo__input_error' : '')?>">
                            <input type="text" value="<?= $data['end_date'] ? date('d.m.Y', strtotime($data['end_date'])) : date('d.m.Y', strtotime("+1 day"))?>" size="80" name="end_date" class="b-combo__input-text b-combo__input-text_fontsize_15" id="end_date" onfocus="clearErrorPrjBlock(this)">
                            <label for="end_date" class="b-combo__label"></label>
                            <span id="end_date_arrow" class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                </td>
            </tr>
            <? if ( $error['end_date'] ) { ?>
            <tr  id="errPrjField_end_date" class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_55"></td>
                <td class="">
                    <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                        <span class="b-form__error"></span> <?= $error['end_date']?>
                    </div>
                </td>
            </tr>
            <? } ?>
            <tr class="b-layout__tr b-layout_hide contest-elm">
                <td class="b-layout__left b-layout__left_width_80"><div class="b-layout__txt b-layout__txt_padtop_4">Подведение итогов</div></td>
                <td class="b-layout__right b-layout__right_padbot_20">
                    <div id="prj-windate-hashtag" class="b-combo">
                        <div class="b-combo__input b-combo__input_width_150 b-combo__input_calendar b-combo__input_resize b-combo__input_arrow_yes date_format_use_text date_min_limit_<?=date('Y_m_d', strtotime("+2 day"))?> <?=($error['win_date'] ? 'b-combo__input_error' : '')?>">
                            <input type="text" value="<?= $data['win_date'] ? date('d.m.Y', strtotime($data['win_date'])) : date('d.m.Y', strtotime("+2 day"))?>" size="80" name="win_date" class="b-combo__input-text b-combo__input-text_fontsize_15" id="win_date" onfocus="clearErrorPrjBlock(this)">
                            <label for="win_date" class="b-combo__label"></label>
                            <span id="win_date_arrow" class="b-combo__arrow-date"></span>
                        </div>
                    </div>
                </td>
            </tr>
            <? if ( $error['win_date'] ) { ?>
            <tr  id="errPrjField_win_date" class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_55"></td>
                <td class="">
                    <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                        <span class="b-form__error"></span> <?= $error['win_date']?>
                    </div>
                </td>
            </tr>
            <? } ?>
            <tr class="b-layout__tr <?= $data['kind'] == 4 ? "" : "b-layout_hide"?>" id="block_location">
                <td class="b-layout__left b-layout__left_width_80 location-title"><?= $data['kind'] == 4 ? "<div class='b-layout__txt b-layout__txt_lineheight_1'>Офис находится в</div>" : ""?></td>
                <td class="b-layout__right b-layout__right_padbot_15 location-content">
                    <?php if($data['kind'] == 4) { ?>
                    <div class="b-combo">
                        <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_150 b-combo__input_arrow_yes b-combo__input_resize b-combo__input_on_load_request_id_getcountries b-combo__input_on_click_request_id_getcities drop_down_default_<?= ($location == 1 ? $data['city'] : $data['country']) ?> multi_drop_down_default_column_<?= $location; ?>">
                            <input type="text" value="<?= $location_name?>" size="80" name="location" class="b-combo__input-text b-combo__input-text_fontsize_15" id="location">
                            <label for="location" class="b-combo__label"></label>
                            <span id="cost_arrow" class="b-combo__arrow"></span>
                        </div>
                    </div>
                    <?php } else {?>
                    <div class="b-layout__txt b-layout__txt_padbot_20 i-button">
                        <a class="b-button b-button_poll_plus location-addbtn" href="javascript:void(0)"></a>&#160;<a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle b-layout__link_lineheight_15 location-addbtn" href="javascript:void(0)">Месторасположение офиса</a><span class="b-layout__txt b-layout__txt_valign_middle">: страна и город</span>
                    </div>
                    <?php }?>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_80"></td>
                <td class="b-layout__right b-layout__right_padbot_15">
                    <div class="b-check b-check_padbot_15">
                        <input id="b-check2" class="b-check__input" name="pro_only" type="checkbox" value="1" <?= $data['pro_only'] ? "checked='checked'" : ""?> />
                        <label for="b-check2" class="b-check__label b-check__label_fontsize_13">Только для <span class="b-icon b-icon__pro b-icon__pro_f" title="PRO"></span></label>
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11">Отвечать на проект cмогут только фрилансеры с профессиональным<br />аккаунтом &mdash; наиболее серьезная и активная часть аудитории сайта.</div>
                </td>
            </tr>
        </table>
    </div>
    	
	<div class="i-shadow i-shadow_width_100">
                    <div class="b-shadow b-shadow_m b-shadow_inline-block b-shadow_left_550" id="screen_shot" style="top:<?= $price>0?"30px":"-110px"?>">
                                    <div class="b-shadow__right">
                                                    <div class="b-shadow__left">
                                                                    <div class="b-shadow__top">
                                                                                    <div class="b-shadow__bottom">
                                                                                                    <div class="b-shadow__body b-shadow__body_bg_fff">

                                                                                                                    <div class="i-prompt">
                                                                                                                                    <div id="prj_pointer" class="b-prompt b-prompt_left_-170 b-prompt_top_175 b-prompt_width_150">
                                                                                                                                                    <div id="prj_pointer_text" class="b-prompt__txt b-prompt__txt_color_6db335 b-prompt__txt_italic">Ваш проект будет<br />опубликован где-то<br />здесь</div>
                                                                                                                                                    <div class="b-prompt__arrow b-prompt__arrow_left_40  b-prompt__arrow_1"></div>
                                                                                                                                    </div>
                                                                                                                    </div>
                                                                                                                    <div class="b-pay-prj b-pay-prj__1"></div>
                                                                                                    </div>
                                                                                    </div>
                                                                    </div>
                                                    </div>
                                    </div>
                                    <div class="b-shadow__tl"></div>
                                    <div class="b-shadow__tr"></div>
                                    <div class="b-shadow__bl"></div>
                                    <div class="b-shadow__br"></div>
                    </div>
    </div>

    <h2 class="b-layout__title b-layout__title_padtop_20 b-layout__title_padbot_20"><a class="b-layout__link b-layout__link_bordbot_dot_000 paid-option" href="javascript:void(0)">Сделать проект заметнее</a></h2>
    <div class="b-layout__txt b-layout__txt_padbot_20">У платных объявлений в разы больше просмотров и ответов<br/> от потенциальных исполнителей. Обычно фрилансеры<br/> относятся к платным проектам более серьезно.</div>
        
    
    
    <div class="b-layout b-layout_padleft_30 b-layout_margleft_-30 b-layout_overflow_hidden" id="paid_option" style=" height:<?= $price>0?"auto":"0px"?>">
    <div id="paid_option_inner"> 
        <div class="b-check b-check_relative b-check_padbot_10">
            <span id="option_top_pin" class="b-check__pin2" style="display: none"></span>
            <input id="ntop1" class="b-check__input scalc-click count-change" name="option_top" type="checkbox" value="1" <?= $option['top'] == 1 ? "checked='checked'" : ""?> price="<?= $pTopPrice;?>" />
            <label id="option_top_label_off" class="b-check__label  b-check__label_fontsize_13" for="ntop1">Закрепить наверху ленты</label>
            <label id="option_top_label_on" class="b-check__label  b-check__label_fontsize_13" for="ntop1" style="display:none">Закреплен наверху ленты</label>
            <div id="option_top_count_block" class="b-form b-form_padtop_5 b-form_padleft_20 b-form_hide">
                <label class="b-form__name b-form__name_fontsize_13 b-form__name_padtop_5">на&#160;</label>
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_width_40">
                        <input class="b-combo__input-text scalc-change" id="ntop2" name="option_top_count" maxlength="3" type="text" size="80" value="<?= $option['top'] == 1 ? $option['top_count'] : "1"?>" price="<?= $pTopPrice;?>" maxlength="4" change1="день" change2="дня" change3="дней"/>
                    </div>
                </div>
                <label class="b-form__name b-form__name_fontsize_13 b-form__name_padtop_5">&#160;<span class="scalc-change-name">день</span> за <span class="b-form__txt b-form__txt_bold b-form__txt_inline b-form__text_color_fd6c30"><span class="scalc-change-result" id="ntopresult"><?= $pTopPrice;?></span> руб.</span></label>
            </div>
        </div>
        <div class="b-check b-check_padbot_20">
            <input id="option_logo" class="b-check__input scalc-click" name="option_logo" type="checkbox" value="1" price="<?= $logoPrc;?>" onclick="setLogo(this)" <?= $option['logo'] == 1 ? "checked='checked'" : ""?> />
            <label id="option_logo_label_off" class="b-check__label b-check__label_fontsize_13" for="option_logo">Добавить логотип со ссылкой за</label>
            <label id="option_logo_label_on" class="b-check__label b-check__label_fontsize_13" for="option_logo">Добавлен логотип со ссылкой за  <span class="b-layout__txt b-layout__txt_lineheight_1 b-layout__txt_bold b-layout__txt_color_fd6c30"><?= $logoPrc;?> руб.</span></label>
            
            <span id="logo_block" class="logo-element">
            <?php if($data['logo_id'] > 0) { ?>
                <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_padleft_18 logo-img">
                    <div class="b-layout__txt b-layout__txt_relative b-layout__txt_inline-block">
                        <a class="b-button b-button_bgcolor_fff b-button_bord_solid_3_fff b-button_admin_del b-button_right_-4 b-button_top_-6" href="javascript:void(0)" onclick="deleteLogo('<?= (int)$data['logo_id']?>');"></a>
                        <a class="b-layout__link" href="<?= $logo_path?>">					
                            <img class="b-layout__pic b-layout__pic_bord_ece9e9" src="<?= $logo_path?>" alt="" />
                        </a>
                    </div>						
                </div>
            <?php }//if?>
            </span>
            <? /* предзагрузка спинера */ ?>
            <img src="/images/loader-2.gif" style="display:none">
            <div id="prj-logoimage-hashtag" class="b-file b-file_margleft_18 b-file_margtop_5 b-file__fon-m logo-element b-file__input_loading logo-add-element<?=($error['logo_image'] ? ' b-file_error_border' : '')?>" <?= ($data['logo_id'] > 0)? 'style="display:none"' : ($option['logo'] == 1 ? '' : 'style="display:none"'); ?>>
                <div class="b-file__wrap b-file__wrap_margtop_50">
                    <input class="b-file__input" type="file" name="logo_attach" onchange="uploadLogoFile();"/>
                    <a id="prj-logoimage-block" class="b-button b-button_rectangle_color_transparent b-button_block"  href="javascript:void(0)">
                        <span class="b-button__b1">
                            <span class="b-button__b2">
                                <span class="b-button__txt">Прикрепить файл</span>
                            </span>
                        </span>
                    </a>
                    <iframe style="width:1px;height:1px;visibility: hidden;" scrolling="no" id="fupload" name="fupload" src="about:blank" frameborder="0"></iframe>
                </div>
            </div><!-- b-work-empty -->
												<div class="b-layout__txt b-layout__txt_padleft_18 b-layout__txt_fontsize_11">Не более 50 Кб, 150 пикселей в ширину, до 150 пикселей<br />в высоту (gif, jpeg, png)</div>
            <div id="logo_block_link" class="b-form b-form_padtop_10 b-form_margleft_-22 logo-element" <?= ($option['logo'] > 0) ? '' : 'style="display:none"'; ?>>
                <div class="b-form__name b-form__name_fontsize_13 b-form__name_padtop_6 b-form__name_width_40">http://</div>
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_width_138 <?=($error['logo_link'] ? 'b-combo__input_error' : '')?>">
                        <input class="b-combo__input-text <?= ($option['logo'] && $data['logo_link']) ? '' : 'b-combo__input-text_color_a7'; ?>" name="logo_link" type="text" size="80" 
                               onfocus="clearErrorPrjBlock(this); if(this.value == 'Адрес сайта') this.value = ''" onblur="if(this.value != 'Адрес сайта' && this.value.length == 0) { $(this).addClass('b-combo__input-text_color_a7'); this.value = 'Адрес сайта' }" 
                               value="<?= ($option['logo'] && $data['logo_link']) ? $data['logo_link'] : "Адрес сайта"?>" />
                    </div>
                <? if ( $error['logo_link'] ) { ?>
                <div  id="errPrjField_logo_link" class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10 b-shadow__margtop_10">
                    <span class="b-form__error"></span> <?= $error['logo_link']?>
                </div>
                <? } ?>
                </div>
            </div>
        </div>
        
        <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">Выбранные услуги будут стоить <span class="b-layout__txt b-layout__txt_color_fd6c30"><span id="scalc_result"><?= ($price>0?$price:0)?></span> <?= ending($price>0?$price:0, 'рубль', 'рубля', 'рублeй');?></span></div>
        <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_11">Оплатить услуги можно на последнем шаге мастера</div>
    </div>		
    </div>

    <div class="b-buttons b-buttons_padtop_20">
        <a href="javascript:void(0)" class="b-button b-button_rectangle_color_green" onclick="$('frm').submit();">
            <span class="b-button__b1">
                <span class="b-button__b2 b-button__b2_padlr_15">
                    <span class="b-button__txt">Продолжить</span>
                </span>
            </span>
        </a>&#160;&#160;
        <a href="/wizard/registration/?action=next&complited=1" class="b-buttons__link">пропустить этот шаг</a>
        <span class="b-buttons__txt">&#160;или&#160;</span>
        <a href="/wizard/registration/?action=exit" class="b-buttons__link b-buttons__link_color_c10601">выйти из мастера</a>
								
    </div>

    </form>
</div>
