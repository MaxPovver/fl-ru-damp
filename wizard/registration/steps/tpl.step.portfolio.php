<script type="text/javascript">
    var work   = new Work();
    var upload = new Upload();
    <?= (is_array($portf_insert))? "work.setWork('".count($portf_insert)."');" : ""?>
   
</script>
<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right  b-layout__right_padbot_20">
    <?php if ($answersExists > 0){?>
        <div class="b-fon b-fon_width_full b-fon_padbot_20">
        	<div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                <span class="b-fon__ok"></span>Ваш ответ на проект будет опубликован после регистрации
            </div>
        </div>
    <?php }//endif?>
    <div class="b-layout__txt b-layout__txt_padbot_40">После того как заказчик ознакомится с вашим предложением, он захочет посмотреть на ваши портфолио и профиль с личной информацией. Укажите все как можно более подробно, уделяя особое внимание своим профессиональным знаниям, навыкам и умениям.</div>
    <h2 class="b-layout__title ">Личная информация</h2>
    
    <form method="post" action="/wizard/upload.php?type=upload" id="upload_form" target="fupload" enctype="multipart/form-data" style="position:absolute; left:-9999px">
        <input type="hidden" name="action" id="ps_action" value="upload" />
        <input type="hidden" name="u_token_key" value="<?= $_SESSION['rand'] ?>">
    </form>
    <iframe style="width:1px;height:1px;visibility: hidden; position:absolute; left:-9999px;" scrolling="no" id="fupload" name="fupload" src="about:blank" frameborder="0"></iframe>
    
    <form method="POST" name="frm" id="frm">
        <input type="hidden" name="action" value="upd_portf">
    <div class="b-layout b-layout_padtop_20 b-layout_margleft_-140">
        <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_140"><div class="b-layout__txt b-layout__txt_padtop_5">Ваша специализация</div></td>
                <td class="b-layout__right b-layout__right_padbot_20">
                    <div class="b-combo">
                        <div class="b-combo__input b-combo__input_width_210 b-combo__input_multi_dropdown b-combo__input_resize b-combo__input_arrow_yes b-combo__input_init_professionsList drop_down_default_<?= $spec ? (int)$spec : (int)$category?> multi_drop_down_default_column_<?= $spec ? "1" : "0"?>"">
                            <input id="spec" class="b-combo__input-text" name="spec" type="text" size="80" value="<?= ($spec_name ? $spec_name : (($category_name ? $category_name . ":" : ""). ($subcategory_name ? " " . $subcategory_name :""))) ?>" />
                            <label class="b-combo__label" for="spec"></label>
                            <span class="b-combo__arrow"></span>
                        </div>
                    </div>
                </td>
            </tr>
            <? if ( $error['spec'] ) { ?>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_55"></td>
                <td colspan="2" class="">
                    <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                        <span class="b-form__error"></span> <?= $error['spec']?>
                    </div>
                </td>
            </tr>
            <? } ?>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_140"><div class="b-layout__txt b-layout__txt_padtop_5">Опыт работы</div></td>
                <td class="b-layout__right b-layout__right_padbot_20">
                    <div class="b-combo b-combo_inline-block b-combo_margright_5">
                        <div class="b-combo__input b-combo__input_width_60">
                            <input  class="b-combo__input-text" name="exp" type="text" size="80" value="<?= $data['exp']?>" maxlength="2"/>
                        </div>
                    </div><div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">лет</div>
                </td>
            </tr>
            <? if ( $error['exp'] ) { ?>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_55"></td>
                <td colspan="2" class="">
                    <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                        <span class="b-form__error"></span> <?= $error['exp']?>
                    </div>
                </td>
            </tr>
            <? } ?>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_140"><div class="b-layout__txt b-layout__txt_padtop_5">Стоимость работы</div></td>
                <td class="b-layout__right b-layout__right_padbot_10">
                    <div class="b-combo b-combo_inline-block b-combo_margright_5">
                        <div class="b-combo__input b-combo__input_width_60">
                            <input  class="b-combo__input-text" name="cost_hour" type="text" size="80" value="<?= $data['cost_hour']?>" maxlength="6"/>
                        </div>
                    </div>
                    <div class="b-combo b-combo_inline-block b-combo_margright_5">
                        <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_40 b-combo__input_resize b-combo__input_arrow_yes b-combo__input_init_currency_data drop_down_default_<?= (int)$data['cost_type_hour']?> multi_drop_down_default_column_0">
                            <input id="currency_hour" readonly="readonly" class="b-combo__input-text b-combo__input-text_fontsize_15" name="currency_hour" type="text" size="80" value="<?= ($curr_hour_name ? $curr_hour_name : "USD")?>"/>
                            <label class="b-combo__label" for="currency_hour"></label>
                            <span class="b-combo__arrow"></span>
                        </div>
                        
                    </div>
                    <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">в час</div>
                </td>
            </tr>
            <? if ( $error['cost_hour'] ) { ?>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_55"></td>
                <td colspan="2" class="">
                    <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                        <span class="b-form__error"></span> <?= $error['cost_hour']?>
                    </div>
                </td>
            </tr>
            <? } ?>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_140">&#160;</td>
                <td class="b-layout__right b-layout__right_padbot_20">
                    <div class="b-combo b-combo_inline-block b-combo_margright_5">
                        <div class="b-combo__input b-combo__input_width_60">
                            <input  class="b-combo__input-text" name="cost_month" type="text" size="80" value="<?= $data['cost_month']?>" maxlength="6"/>
                        </div>
                    </div>
                    <div class="b-combo b-combo_inline-block b-combo_margright_5">
                        <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_40 b-combo__input_resize b-combo__input_arrow_yes b-combo__input_init_currency_data drop_down_default_<?= (int)$data['cost_type_month']?> multi_drop_down_default_column_0">
                            <input id="currency_month" readonly="readonly" class="b-combo__input-text b-combo__input-text_fontsize_15" name="currency_month" type="text" size="80" value="<?= ($curr_month_name ? $curr_month_name : "USD")?>" />
                            <label class="b-combo__label" for="currency_month"></label>
                            <span class="b-combo__arrow"></span>
                        </div>
                        
                    </div>
                    <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">в месяц</div>
                </td>
            </tr>
            <? if ( $error['cost_month'] ) { ?>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_55"></td>
                <td colspan="2" class="">
                    <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                        <span class="b-form__error"></span> <?= $error['cost_month']?>
                    </div>
                </td>
            </tr>
            <? } ?>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_140">&#160;</td>
                <td class="b-layout__right b-layout__right_padbot_10">
                    <div class="b-check">
                        <input  class="b-check__input" id="in_office" name="in_office" type="checkbox" value="1" <?= ($data['in_office']?"checked":"")?>/>
                        <label  class="b-check__label b-check__label_fontsize_13" for="in_office">Ищу долгосрочную работу в офисе</label>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_padbot_20 b-layout__left_width_140">&#160;</td>
                <td class="b-layout__right b-layout__right_padbot_20">
                    <input type="hidden" name="resume_id" id="resume_id" value="<?= (int)$data['resume']?>">
                    <?php if($data['resume'] > 0) {?>
                    <span>
                        <div class="b-layout__txt b-layout__txt_inline-block  b-layout__txt_valign_top b-layout__txt_padtop_7">
                            <a class="b-layout__link" href="<?= WDCPREFIX . '/wizard/'. $resume->name; ?>" target="_blank"><span class="b-icon b-icon_margtop_-3 b-icon_mid_<?= getICOFile($resume->getext())?>"></span><?= $resume->original_name?></a>&nbsp;&nbsp;
                        </div><a href="javascript:void(0)" onclick="upload.remove(this);" class="b-button b-button_margtop_10 b-button_admin_del"></a>&nbsp;&nbsp;&nbsp;&nbsp;
                    </span>
                    <?php }//if?>
                    <div class="b-file" <?= ($data['resume'] > 0 ? "style='display:none'" : "")?>>
                        <table class="b-file_layout" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td class="b-file__button">            
                                    <div class="b-file__wrap  b-file__wrap_margleft_-3">
                                        <input type="file" name="resume" class="b-file__input" onchange="upload.load(this)">
                                        <a href="javascript:void(0)" class="b-button b-button_rectangle_color_transparent">
                                            <span class="b-button__b1">
                                                <span class="b-button__b2">
                                                    <span class="b-button__txt">Загрузить резюме</span>
                                                </span>
                                            </span>
                                        </a>
                                    </div>
                                </td>
                                <td class="b-file__text">&#160;</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_140"><div class="b-layout__txt">Доп. информация</div></td>
                <td class="b-layout__right b-layout__right_padbot_10">
                    <div class="b-textarea">
                        <textarea class="b-textarea__textarea b-textarea__textarea_height_120 tawl" name="info" rel="4000" cols="80" rows="5"><?= $data['info'];?></textarea>
                    </div>
                </td>
            </tr>
            <? if ( $error['info'] ) { ?>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_55"></td>
                <td colspan="2" class="">
                    <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                        <span class="b-form__error"></span> <?= $error['info']?>
                    </div>
                </td>
            </tr>
            <? } ?>
        </table>
    </div>


    <h2 class="b-layout__title b-layout__title_padtop_30 b-layout__title_padbot_30" id="portfolio_first">
        <a href="javascript:void(0)" onclick="work.create();" class="b-button b-button_margtop_-5 b-button_float_right b-button_round_green">
            <span class="b-button__b1">
                <span class="b-button__b2">
                    <span class="b-button__txt">Добавить работу</span>
                </span>
            </span>
        </a>
        Работы в портфолио
    </h2>
        
    <?php if($count_portf > 0) {?>
    <div class="b-fon b-fon_width_full b-fon_padbot_20">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
            <span class="b-fon__ok"></span>Вы создали <?=$count_portf?> <?=ending($count_portf, "работу", "работы", "работ")?>. После регистрации на сайте, в настройках в разделе портфолио вы сможете удалить или изменить их.
        </div>
    </div>
    <?php }//if?>    
        
    <?php if(is_array($portf_insert) && count($portf_insert)) {?>
        <?php foreach($portf_insert as $k=>$value) {
            if ($value['link'] === 'Ссылка') {
                $value['link'] = '';
            }
        ?>
        <div class="b-fon b-fon_width_full b-fon_padbot_20 b-fon-portfolio b-fon_overflow_hidden">
            <input type="hidden" name="id[<?= $k ?>]" value="<?= $value['id'] ?>" />
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_bg_f0ffdf i-button">
                <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full" work="<?= $k?>">
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_padbot_15">
                            <div class="b-combo">
                                <div class="b-combo__input">
                                    <input  class="b-combo__input-text" name="name[<?=$k?>]" type="text" size="80" maxlength="80" value="<?= $value['name'] ?>" onfocus="clearErrorBlock(this);"/>
                                </div>
                            </div>
                        </td>
                        <td class="b-layout__right b-layout__right_padbot_15 b-layout__right_width_15 b-layout__right_padleft_10">
                            <a href="javascript:void(0);" onclick="work.remove(this);" class="b-button b-button_admin_del"></a>
                        </td>
                    </tr>
                    <? if ( $error['portf'.$k]['name'] ) { ?>
                    <tr class="b-layout__tr">
                        <td colspan="2" class="">
                            <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                                <span class="b-form__error"></span> <?= $error['portf'.$k]['name']?>
                            </div>
                        </td>
                    </tr>
                    <? } ?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left <?= $value['link'] != '' || $value['descr'] != '' ? "b-layout__left_padbot_15" : "" ?>">
                            <span>
                                <?php if($value['pict_id'] > 0) { $pict = new CFile($value['pict_id']);?>
                                <?
                                $name = $pict->original_name;
                                $maxLen = 25;
                                // сокращаем название файла, сохраняя расширение
                                if (strlen($name) > $maxLen) {
                                    $arr = explode('.', $name);
                                    $ext = array_pop($arr);
                                    $name = preg_replace("/.$ext$/", '', $name);
                                    $name = substr($name, 0, $maxLen) . '...';
                                    $name = $name . '.' . $ext;
                                }
                                ?>
                                <span>
                                    <div class="b-layout__txt b-layout__txt_inline-block  b-layout__txt_valign_top b-layout__txt_padtop_7">
                                        <a class="b-layout__link" href="<?= WDCPREFIX . '/wizard/'. $pict->name; ?>"><span class="b-icon b-icon_margtop_-3 b-icon_mid_<?= getICOFile($pict->getext())?>"></span><?= $name?></a>&nbsp;&nbsp;
                                    </div><a href="javascript:void(0)" onclick="upload.remove(this);" class="b-button b-button_margtop_10 b-button_admin_del"></a>&nbsp;&nbsp;&nbsp;&nbsp;
                                </span>
                                <?php }//if?>
                                
                                <input type="hidden" name="pict_id[<?= $k?>]" id="pict<?= $k?>_id" value="<?= intval($value['pict_id'])?>">
                                <div class="b-file b-file_inline-block b-file_padright_20" <?= ($value['pict_id'] > 0 ? "style='display:none'" : "")?>>
                                    <div class="b-file__wrap  b-file__wrap_margleft_-3">
                                        <input type="file" name="pict[<?= $k?>]" class="b-file__input" onchange="upload.load(this)">
                                        <a href="javascript:void(0)" class="b-button b-button_rectangle_color_transparent">
                                            <span class="b-button__b1">
                                                <span class="b-button__b2">
                                                    <span class="b-button__txt">Прикрепить файл</span>
                                                </span>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </span>
                            <div class="b-layout__txt b-layout__txt_inline-block  b-layout__txt_valign_top b-layout__txt_padtop_7">
                                <?php if($value['link'] == '') {?>
                                <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="work.create_field(1, this);">Поставить ссылку</a> &#160;&#160;&#160;
                                <?php }//if?>
                                <?php if($value['descr'] == '') {?>
                                <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="work.create_field(2, this);">Добавить описание</a>
                                <?php }//if?>
                            </div>
                        </td>
                        <td class="b-layout__right b-layout__right_width_15 b-layout__right_padleft_10">&#160;</td>
                    </tr>
                    <?php if($value['link'] != '') {?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_padbot_15">
                            <div class="b-combo">
                                <div class="b-combo__input">
                                    <input  class="b-combo__input-text" name="link[<?=$k?>]" type="text" size="80" value="<?= $value['link'] ?>" onfocus="clearErrorBlock(this);" graytext="Ссылка" />
                                </div>
                            </div>
                        </td>
                        <td class="b-layout__right b-layout__right_padbot_10 b-layout__right_width_15 b-layout__right_padleft_10">&#160;</td>
                    </tr>
                    <? if ( $error['portf'.$k]['link'] ) { ?>
                    <tr class="b-layout__tr">
                        <td colspan="2" class="">
                            <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                                <span class="b-form__error"></span> <?= $error['portf'.$k]['link']?>
                            </div>
                        </td>
                    </tr>
                    <? } ?>
                    <?php }//if?>
                    <?php if($value['descr'] != '') {?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left">
                            <div class="b-textarea">
                                <textarea class="b-textarea__textarea tawl" name="descr[<?=$k?>]" cols="" rows="" rel="1500" graytext="Описание"><?= $value['descr'] ?></textarea>
                            </div>
                        </td>
                        <td class="b-layout__right b-layout__right_padbot_10 b-layout__right_width_15 b-layout__right_padleft_10">&#160;</td>
                    </tr>
                    <? if ( $error['descr'.$k]['name'] ) { ?>
                    <tr class="b-layout__tr">
                        <td colspan="2" class="">
                            <div class="b-layout__txt b-layout__txt_color_c4271f b-layout__txt_padbot_10">
                                <span class="b-form__error"></span> <?= $error['descr'.$k]['name']?>
                            </div>
                        </td>
                    </tr>
                    <? } ?>
                    <?php }//if?>
                </table>
            </div>
        </div>
        <?php }//foreach?>
    <?php } else { //if?>
    <div class="b-fon b-fon_width_full b-fon_padbot_20 b-fon-portfolio b-fon_overflow_hidden">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_bg_f0ffdf i-button">
            <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full" work="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_padbot_15">
                        <div class="b-combo">
                            <div class="b-combo__input">
                                <input  class="b-combo__input-text b-combo__input-text_color_a7" name="name[0]" type="text" size="80" maxlength="80" value="" graytext="Название работы"  />
                            </div>
                        </div>
                    </td>
                    <td class="b-layout__right b-layout__right_padbot_15 b-layout__right_width_15 b-layout__right_padleft_10">
                        <a href="javascript:void(0);" onclick="work.remove(this);" class="b-button b-button_admin_del"></a>
                    </td>
                </tr>
                <tr class="b-layout__tr">
                    <td class="b-layout__left">
                        <span>
                            <input type="hidden" name="pict_id[0]" id="pict0_id" value="0">
                            <div class="b-file b-file_inline-block b-file_padright_20">
                                <div class="b-file__wrap  b-file__wrap_margleft_-3">
                                    <input type="file" name="pict[0]" class="b-file__input" onchange="upload.load(this)">
                                    <a href="javascript:void(0)" class="b-button b-button_rectangle_color_transparent">
                                        <span class="b-button__b1">
                                            <span class="b-button__b2">
                                                <span class="b-button__txt">Прикрепить файл</span>
                                            </span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </span>
                        <div class="b-layout__txt b-layout__txt_inline-block  b-layout__txt_valign_top b-layout__txt_padtop_7"><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="work.create_field(1, this);">Поставить ссылку</a> &#160;&#160;&#160; <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="work.create_field(2, this);">Добавить описание</a></div>
                    </td>
                    <td class="b-layout__right b-layout__right_width_15 b-layout__right_padleft_10">&#160;</td>
                </tr>
            </table>
        </div>
    </div>
    <?php }//if?>
    <div id="end_of_portfolios"></div> 
        
    </form>
    <div class="b-buttons b-buttons_padtop_40 ">
        <a href="javascript:void(0)" onclick="if(!$(this).hasClass('b-button_rectangle_color_disable')) {clearGrayPortfolioTitles();$('frm').submit();}" class="b-button b-button_rectangle_color_green" id="submit_button">
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


</div>