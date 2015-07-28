<?php
if(!defined('IN_STDF')) 
{ 
    header("HTTP/1.0 404 Not Found");
    exit;
}
?>

<script language="javascript" type="text/javascript">
    <?php if(false) if( $tuid > 0 || count($errors) > 0 ){ ?>
    var revalidate_tu_form = true;
    <?php } ?>
    var _OUID = <?php echo $user_obj->uid ?>;
</script>
<div class="b-menu b-menu_crumbs b-menu_padtop_6">
    <ul class="b-menu__list">  
        <?php if(false){ ?>
        <li class="b-menu__item">  
            <a class="b-menu__link" href="/users/<?=$user_obj->login ?>/">
                Профиль</a>&nbsp;&RightArrow;&nbsp;
        </li>    
        <?php } ?>
        <li class="b-menu__item">
            <a class="b-menu__link" href="/users/<?=$user_obj->login ?>/tu/">
            <?php if($is_adm){?>Все услуги фрилансера<?php }else{ ?>Мои услуги<?php } ?></a>&nbsp;&RightArrow;&nbsp;
        </li>
    </ul>
</div>

<h1 class="b-page__title">
    Создайте типовую услугу за пару минут
    <div class="b-txt b-txt_color_80 b-txt_normal">
        Типовая услуга — фиксированный объем работ, который вы можете выполнить по фиксированной цене
    </div>
</h1>

<aside class="b-layout__side b-layout__side_content_width_72ps b-layout__side_content">
    <div class="b-fon b-fon_bg_fff b-fon_pad_40_20 b-fon_bord_e6 b-fon_margbot_15 b-fon_margtop_10">
        <form action="" id="__form__tu_new" method="post" enctype="multipart/form-data"> 
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_30">
                    <h3 class="b-txt__h3">Название и стоимость услуги <span class="b-txt_color_de2c2c">*</span></h3>
                    <table class="b-layout__table">
                        <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_width_full">
                                <div class="b-combo">
                                    <div class="b-combo__input <?php if(isset($errors['title'])){ ?> b-combo__input_error<?php } ?>">
                                        <input tabindex="1" maxlength="100" data-validators="required minLength:4 maxLength:100" placeholder="" type="text" value="<?php echo str_replace('"', "&quot;",$tservice->title) ?>" class="b-combo__input-text" name="title" id="title">
                                        <label class="b-combo__label" for="title"></label>
                                    </div>
                                </div>
                                <?php echo tservices_helper::input_element_error('title', @$errors['title']); ?>
                            </td>
                            <td class="b-layout__td b-layout__txt_nowrap">
                                <span class="b-txt b-txt_inline-block b-txt_padtop_3 b-txt_padlr_5">&nbsp;за&nbsp;</span>
                                <div class="b-combo b-combo_inline-block">
                                    <div class="b-combo__input b-combo__input_width_90<?php if(isset($errors['price'])){ ?> b-combo__input_error<?php } ?>">
                                        <input tabindex="2" maxlength="6" data-validators="price uint" type="text" value="<?php echo ($tservice->price > 0)?$tservice->price:''?>" class="b-combo__input-text" name="price" id="price">
                                        <label class="b-combo__label" for="price"></label>
                                    </div>
                                </div>
                            <span class="b-txt b-txt_inline-block b-txt_padtop_3">&nbsp;<?php echo tservices_const::enum('currency', 'rus'); ?></span>
                                <?php echo tservices_helper::input_element_error('price', @$errors['price']); ?>
                            </td>                    
                        </tr>
                    </table>
                    <div class="b-txt b-txt_padtop_5 b-txt_fs_11">
                        Например: Дизайн визитки
                    </div>
                </td>
            </tr>    
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_30">
                    <h3 class="b-txt__h3">
                        Срок выполнения работы <span class="b-txt_color_de2c2c">*</span>
                        <?php tservices_helper::tooltip('Максимальный срок, в течение которого вы готовы выполнить весь объем работ, указанных в услуге.') ?>
                    </h3>
                    <div class="b-combo">
                        <div class="
                             b-combo__input 
                             b-combo__input_multi_dropdown 
                             b-combo__input_init_tuDayList 
                             show_all_records 
                             b-combo__input_width_100 
                             b-combo__input_resize 
                             b-combo__input_max-width_450 
                             multi_drop_down_default_column_0  
                             drop_down_default_<?php echo $tservice->days ?> 
                             b-combo__input_arrow_yes 
                             <?php if(isset($errors['days'])){ ?> b-combo__input_error<?php } ?> 
                             disallow_null">
                            <input tabindex="3" class="b-combo__input-text b-combo__input-text_pointer" value="" id="days" name="days" type="text" size="80" readonly="readonly"/>
                            <label for="days" class="b-combo__label"></label>
                            <span class="b-combo__arrow"></span>
                        </div>
                    </div>
                    <?php echo tservices_helper::input_element_error('days', @$errors['days']); ?>
                </td>
            </tr>
            <?php
                $value = 'Выберите категорию';
                if($tservice->category_id > 0)
                {
                    $category = new tservices_categories();
                    $titles = $category->getTitleAndSubtitle($tservice->category_id);
                    if($titles) $value = ((!empty($titles['group_title']))?$titles['group_title'] .': ':'') . $titles['spec_title'];
                }
            ?>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_30">
                   <h3 class="b-txt__h3">Категория <span class="b-txt_color_de2c2c">*</span></h3>
                   <div class="b-combo">
                      <div class="
                           b-combo__input 
                           b-combo__input_multi_dropdown 
                           b-combo__input_resize 
                           b-combo__input_width_160 
                           b-combo__input_max-width_220  
                           b-combo__input_orientation_right 
                           b-combo__input_visible_height_200 
                           drop_down_default_<?php echo $tservice->category_id ?>
                           multi_drop_down_default_column_0 
                           override_value_id_0_0_Выберите+категорию 
                           sort_cnt 
                           <?php if($is_exist_feedbacks > 0) {?>b-combo__input_disabled<?php } ?> 
                           b-combo__input_on_load_request_id_gettucategories 
                           <?php if(isset($errors['category'])){ ?>b-combo__input_error<?php } ?>
                           disallow_null">
                         <input tabindex="4" data-validators="category" id="category" class="b-combo__input-text" name="category" type="text" size="80" value="<?php echo $value ?>" />
                         <label for="category" class="b-combo__label"><?php echo $value ?></label>
                         <span class="b-combo__arrow"></span>
                      </div>
                   </div>
                   <?php echo tservices_helper::input_element_error('category', @$errors['category']); ?>
                </td>
            </tr>  
            
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_30">
                    <h3 class="b-txt__h3">
                        Ключевые слова <span class="b-txt_color_de2c2c">*</span>
                    </h3>
                    <div class="b-combo">
                        <div class="b-combo__input b-combo__input_max-width_500<?php if(isset($errors['tags'])){ ?> b-combo__input_error<?php } ?>">
                            <input tabindex="5" data-validators="required tags" id="tags" type="text" placeholder="" value="<?php echo implode($tservice->tags, ', ') ?>" class="b-combo__input-text" name="tags">
                            <label class="b-combo__label" for="tags"></label>
                        </div>                       
                    </div>
                    <?php echo tservices_helper::input_element_error('tags', @$errors['tags']); ?>
                    <div class="b-txt b-txt_padtop_5 b-txt_fs_11">
                        Можно указать до 10 слов через запятую
                    </div>
                </td>
            </tr>
            
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_30">
                    <h3 class="b-txt__h3">Превью услуги <span class="b-txt_color_de2c2c">*</span></h3>
                    <div id="preview_uploader">
                        <div class="qq-uploader-selector">
                        <div class="qq-upload-drop-area-selector b-file b-file_dragdrop b-file_dragdrop_maxwidth_470">
                            <div class="b-txt b-txt_center">
                                Перетащите файл сюда
                                <div style="width:185px; margin:0 auto;">
                                <div class="b-file__wrap">
                                    <a href="javascript:void(0)" class="qq-upload-button-selector b-txt__lnk_color_0f71c8 b-txt__lnk_outline_none">
                                        или выберите с диска
                                    </a>
                                </div>
				<?php echo tservices_helper::input_element_error('preview'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="b-txt b-txt_padtop_5 b-txt_fs_11 b-txt_margbot_20">
                            Минимальное разрешение 200х150 пикселей в формате jpg, jpeg, png.
                        </div>
                    
                        <ul class="qq-upload-list-selector b-file_attach-files b-file_attach-files_maxwidth_520 b-file_attach-files_pad_0 b-file_attach-files_margleft_null b-file_attach-files_margtop_null">
                            <li class="b-file_attach-files_element b-file_attach-files_element_width_60 b-file_attach-files_element_height_60 b-file_attach-files_element_bg_e1">
                                <div class="qq-progress-bar-container-selector">
                                    <div class="qq-progress-bar-selector b-file_attach-progress_bar"></div>
                                </div>
                                <img class="qq-thumbnail-selector" qq-max-size="60" qq-server-scale="true"/>
                                <span class="qq-upload-spinner-selector qq-upload-spinner b-file_attach-files_element_spinner"></span>
                                <a title="Удалить" class="qq-upload-delete-selector b-button b-button_circle_cross b-button_absolute b-button_top_5 b-button_right_5" href="javascript:void(0)"></a>
                            </li>  
                            <?php
                                if(count($preview_field))
                                {
                                    
                            ?>
                            <li class="test b-file_attach-files_element b-file_attach-files_element_width_60 b-file_attach-files_element_height_60 b-file_attach-files_element_bg_e1">
                                <img class="qq-thumbnail-selector" src="<?php echo $preview_field['src'] ?>" />
                                <span class="qq-upload-spinner-selector qq-upload-spinner b-file_attach-files_element_spinner b-file_attach-files_hide"></span>
                                <a data-hash="<?php echo $preview_field['hash'] ?>" data-qquuid="<?php echo $preview_field['qquuid'] ?>" title="Удалить" class="qq-upload-delete-selector b-button b-button_circle_cross b-button_absolute b-button_top_5 b-button_right_5" href="javascript:void(0)"></a>
                            </li>
                            <?php
                                }
                            ?>
                        </ul>
                        <input type="hidden" id="preview_sess" name="preview_sess" value="<?php echo $sess_p ?>" />
                        </div>
                    </div>
                </td>
            </tr>
            
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_30">
                    <h3 class="b-txt__h3">Примеры работ (фото и скриншоты)</h3>
                    <div id="files_uploader">
                        <div class="qq-uploader-selector">
                        <div class="qq-upload-drop-area-selector b-file b-file_dragdrop b-file_dragdrop_maxwidth_470">
                            <div class="b-txt b-txt_center">
                                Перетащите файл сюда
                                <div style="width:185px; margin:0 auto;">
                                <div class="b-file__wrap">
                                    <a href="javascript:void(0)" class="qq-upload-button-selector b-txt__lnk_color_0f71c8 b-txt__lnk_outline_none">
                                        или выберите с диска
                                    </a>
                                </div>
								<?php echo tservices_helper::input_element_error('uploader'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="b-txt b-txt_padtop_5 b-txt_fs_11 b-txt_margbot_20">
                            Минимальное разрешение 600x600 пикселей в формате jpg, jpeg, png.
                        </div>
                    
                        <ul class="qq-upload-list-selector b-file_attach-files b-file_attach-files_maxwidth_520 b-file_attach-files_pad_0 b-file_attach-files_margleft_null b-file_attach-files_margtop_null">
                            <li class="b-file_attach-files_element b-file_attach-files_element_width_60 b-file_attach-files_element_height_60 b-file_attach-files_element_bg_e1">
                                <div class="qq-progress-bar-container-selector">
                                    <div class="qq-progress-bar-selector b-file_attach-progress_bar"></div>
                                </div>
                                <img class="qq-thumbnail-selector" qq-max-size="60" qq-server-scale="true"/>
                                <span class="qq-upload-spinner-selector qq-upload-spinner b-file_attach-files_element_spinner"></span>
                                <a title="Удалить" class="qq-upload-delete-selector b-button b-button_circle_cross b-button_absolute b-button_top_5 b-button_right_5" href="javascript:void(0)"></a>
                            </li>  
                            <?php
                                if(count($uploader_field_element))
                                {
                                    foreach($uploader_field_element as $el)
                                    {
                            ?>
                            <li class="test b-file_attach-files_element b-file_attach-files_element_width_60 b-file_attach-files_element_height_60 b-file_attach-files_element_bg_e1">
                                <img class="qq-thumbnail-selector" src="<?php echo $el['src'] ?>" />
                                <span class="qq-upload-spinner-selector qq-upload-spinner b-file_attach-files_element_spinner b-file_attach-files_hide"></span>
                                <a data-hash="<?php echo $el['hash'] ?>" data-qquuid="<?php echo $el['qquuid'] ?>" title="Удалить" class="qq-upload-delete-selector b-button b-button_circle_cross b-button_absolute b-button_top_5 b-button_right_5" href="javascript:void(0)"></a>
                            </li>
                            <?php
                                    }
                                }
                            ?>
                        </ul>
                        <input type="hidden" id="uploader_sess" name="uploader_sess" value="<?php echo $sess ?>" />
                        </div>
                    </div>
                </td>
            </tr>
            

            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_30">
                    <h3 class="b-txt__h3">Примеры работ (видео)</h3>
                    <div id="video_items">
                    <?php
                        if(!$tservice->videos) $tservice->videos = array(array('url' => ''));
                        foreach($tservice->videos as $key => $field)
                        {
                    ?>
                    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10">
                        <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_width_500 b-layout__td_ipad">
                                <div class="b-combo b-combo__input_max-width_500">
                                    <div class="b-combo__input<?php if(isset($errors['videos'][$key])){ ?> b-combo__input_error<?php } ?>">
                                        <input tabindex="6" data-validators="video" placeholder="" type="text" value="<?php echo $field['url'] ?>" class="b-combo__input-text" name="<?php echo 'videos['.$key.']' ?>" id="<?php echo 'videos['.$key.']' ?>"/>
                                        <label class="b-combo__label" for="<?php echo 'videos['.$key.']' ?>"></label>
                                    </div>
                                </div>
                                <?php echo tservices_helper::input_element_error('videos['.$key.']', @$errors['videos'][$key], 'shadow_zindex_3'); ?>
                            </td>
                            <td class="b-layout__td b-layout__td_padleft_10 b-layout__td_valign_mid b-layout__td_ipad">
                                <a href="javascript:void(0)" class="b-button b-button_mini b-button_mini_nobg">
                                    <span class="b-button__icon b-button__icon_cross"></span>
                                </a>
                            </td>
                        </tr>
                    </table>
                    <?php } ?>
                    </div>
                    <div class="b-txt b-txt_fs_11">
                        Ссылка на видео с YouTube, RuTube или Vimeo
                    </div>
                    <a id="add-video" href="javascript:void(0)" class="b-txt__lnk b-txt__lnk_inline-block b-txt__lnk_color_0f71c8 b-txt__lnk_fs_15">+ Добавить еще одно видео</a>
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_30">
                    <h3 class="b-txt__h3">Подробное описание <span class="b-txt_color_de2c2c">*</span></h3>
                    <div class="b-textarea b-textarea_max-width_500<?php if(isset($errors['description'])){ ?> b-textarea_error<?php } ?>">
                        <textarea  tabindex="106" data-validators="required minLength:4 maxLength:5000" id="description" placeholder="Подробно опишите результат, который получит заказчик" class="b-textarea__textarea b-textarea_noresize" name="description"><?php echo $tservice->description ?></textarea>
                    </div>
                    <?php echo tservices_helper::input_element_error('description', @$errors['description']); ?>
                </td>
            </tr>   
            
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_30 b-layout__td_bordbot_c3">
                    <h3 class="b-txt__h3">
                        Заработайте больше, предлагая сопутствующие услуги
                        <?php tservices_helper::tooltip('Предложите покупателю вместе с основной услугой дополнительные опции за отдельные оплату и сроки') ?>
                    </h3>
                    <?php 
                        if(!$tservice->extra) $tservice->extra = array(array('title' => '','price' => '','days' => 1));
                    ?>
                    <div id="extra_items">
                    <?php
                        foreach($tservice->extra as $key => $field)
                        {
                    ?>
                    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10">
                        <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_width_full">
                                <div class="b-combo">
                                    <div class="b-combo__input<?php if(isset($errors['extra'][$key]['title'])){ ?> b-combo__input_error<?php } ?>">
                                        <input tabindex="107" maxlength="255" data-validators="relation" data-rel="title:price" type="text" placeholder="" value="<?php echo $field['title'] ?>" class="b-combo__input-text" name="<?php echo 'extra['.$key.'][title]' ?>" id="<?php echo 'extra['.$key.'][title]' ?>">
                                        <label class="b-combo__label" for="<?php echo 'extra['.$key.'][title]' ?>"></label>
                                    </div>
                                </div>
                                <?php echo tservices_helper::input_element_error('extra['.$key.'][title]', @$errors['extra'][$key]['title'], 'b-shadow_zindex_3'); ?>
                            </td>
                            <td class="b-layout__td b-layout__txt_nowrap">
                                <span class="b-txt b-txt_inline-block b-txt_padtop_3 b-txt_padlr_5">&nbsp;за&nbsp;</span>
                                <div class="b-combo b-combo_inline-block">
                                    <div class="b-combo__input b-combo__input_width_90<?php if(isset($errors['extra'][$key]['price'])){ ?> b-combo__input_error<?php } ?>">
                                        <input tabindex="108" maxlength="7" data-validators="relation intOrEmpty" data-rel="price:title" type="text" value="<?php echo $field['price'] ?>" class="b-combo__input-text" name="<?php echo 'extra['.$key.'][price]' ?>" id="<?php echo 'extra['.$key.'][price]' ?>"/>
                                        <label class="b-combo__label" for="<?php echo 'extra['.$key.'][price]' ?>"></label>
                                    </div>
                                </div>
                                <span class="b-txt b-txt_inline-block b-txt_padtop_3">&nbsp;<?php echo tservices_const::enum('currency', 'rus'); ?></span>
                                <?php echo tservices_helper::input_element_error('extra['.$key.'][price]', @$errors['extra'][$key]['price'], 'b-shadow_zindex_3'); ?>
                            </td>
                            <td class="b-layout__td b-layout__txt_nowrap">
                                <span class="b-txt b-txt_inline-block b-txt_padtop_3 b-txt_padlr_5">&nbsp;за&nbsp;</span>
                                <div class="b-combo b-combo_inline-block">
                                    <div class="
                                         b-combo__input 
                                         b-combo__input_multi_dropdown 
                                         b-combo__input_width_110 
                                         show_all_records 
                                         drop_down_default_<?php echo $field['days'] ?> 
                                         multi_drop_down_default_column_0  
                                         disallow_null 
                                         b-combo__input_init_tuDayListWithZero  
                                         <?php if(isset($errors['extra'][$key]['days'])){ ?>b-combo__input_error<?php } ?> 
                                         b-combo__input_arrow_yes">
                                        <input tabindex="109" type="text" value="<?php echo $field['days'] ?> <?php echo ending($field['days'], 'день', 'дня', 'дней') ?>" size="80" readonly="readonly" name="<?php echo 'extra['.$key.'][days]' ?>" id="<?php echo 'extra['.$key.'][days]' ?>" class="b-combo__input-text b-combo__input-text_pointer"/>
                                        <label class="b-combo__label" for="<?php echo 'extra['.$key.'][days]' ?>"></label>
                                        <span class="b-combo__arrow"></span> 
                                    </div>
                                </div>
                                <?php echo tservices_helper::input_element_error('extra['.$key.'][days]', @$errors['extra'][$key]['days']); ?>     
                            </td>
                            <td class="b-layout__td b-layout__td_padleft_10 b-layout__td_valign_mid  b-layout__td_ipad">
                                <a href="javascript:void(0)" class="b-button b-button_mini b-button_mini_nobg"><span class="b-button__icon b-button__icon_cross"></span></a>
                            </td>
                        </tr>
                    </table>
                    <?php } ?>
                    </div>
                    <a id="add-extra" href="javascript:void(0)" class="b-txt__lnk b-txt__lnk_inline-block b-txt__lnk_color_0f71c8 b-txt__lnk_fs_15">+ Добавить</a>
                </td>
            </tr>

            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_15 b-layout__td_padtop_15">
                    <div class="b-check b-check_inline-block b-check_padtop_5 b-check_padbot_5 b-check_padright_5">
                          <input tabindex="207"<?php if($tservice->is_express == 't'){ ?> checked="checked"<?php } ?> type="checkbox" value="1" name="express_activate" class="b-check__input" id="express_activate"/>
                          <label class="b-check__label b-check__label_ptsans b-page__desktop b-page__ipad" for="express_activate">
                              Могу выполнить срочно за дополнительные
                          </label>
                          <label class="b-check__label b-check__label_ptsans b-page__iphone" for="express_activate" style="vertical-align:top;">
                              Могу выполнить срочно<br>за дополнительные
                          </label>
                    </div>
                    <span class="b-layout_block_iphone">
                        <div class="b-combo b-combo_inline-block">
                            <div class="b-combo__input b-combo__input_width_90<?php if($tservice->is_express == 'f'){ ?> b-combo__input_disabled<?php } ?><?php if(isset($errors['express']['price'])){?> b-combo__input_error<?php } ?>">
                                <input tabindex="208" maxlength="6" data-validators="<?php if($tservice->is_express == 't'){?>required uint<?php } ?>" id="express[price]" type="text" name="express[price]" class="b-combo__input-text" value="<?php echo (($tservice->express_price > 0)?$tservice->express_price:'') ?>"<?php if($tservice->is_express == 'f'){ ?> disabled="disabled"<?php } ?>/>
                                <label for="express[price]" class="b-combo__label"></label>
                            </div>
                            <?php echo tservices_helper::input_element_error('express[price]', @$errors['express']['price']); ?>
                        </div>
                        <span class="b-txt b-txt_inline-block b-txt_padtop_3">&nbsp;<?php echo tservices_const::enum('currency', 'rus'); ?></span>
                        <span class="b-txt b-txt_inline-block b-txt_padtop_3 b-txt_padlr_5">&nbsp;за&nbsp;</span>
                        <span class="b-layout_block_iphone b-layout_padtop_7">
                            <div class="b-combo b-combo_inline-block">
                                <div class="
                                     b-combo__input 
                                     b-combo__input_multi_dropdown 
                                     b-combo__input_width_110 
                                     b-combo__input_arrow_yes 
                                     show_all_records 
                                     multi_drop_down_default_column_0 
                                     drop_down_default_<?php echo $tservice->express_days ?>
                                     disallow_null 
                                     b-combo__input_init_tuDayList 
                                     <?php if(isset($errors['express']['days'])){ ?>b-combo__input_error<?php } ?> 
                                     <?php if($tservice->is_express == 'f'){ ?>b-combo__input_disabled<?php } ?>">
                                    <input tabindex="209" id="express[days]" type="text" value="" name="express[days]" readonly="readonly" class="b-combo__input-text b-combo__input-text_pointer"<?php if($tservice->is_express == 'f'){ ?> disabled="disabled"<?php } ?>/>
                                    <label class="b-combo__label" for="express[days]"></label>
                                    <span class="b-combo__arrow"></span> 
                                </div>
                                <?php echo tservices_helper::input_element_error('express[days]', @$errors['express']['days']); ?>
                            </div>
                            <?php tservices_helper::tooltip('Укажите сумму доплаты за срочное выполнение всей работы (по услуге и дополнительным опциям)') ?>
                        </span>
                    </span>
                </td>
            </tr>

            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_30 b-layout__td_padtop_15">
                    <h3 class="b-txt__h3">
                        Необходимая от заказчика информация <span class="b-txt_color_de2c2c">*</span>
                    </h3>
                    <div class="b-textarea b-textarea_max-width_500<?php if(isset($errors['requirement'])){ ?> b-textarea_error<?php } ?>">
                        <textarea tabindex="210" data-validators="required minLength:4 maxLength:5000" placeholder="Опишите по пунктам, что должен предоставить заказчик для начала работы" class="b-textarea__textarea b-textarea_noresize" name="requirement" id="requirement"><?php echo $tservice->requirement ?></textarea>
                    </div>
                    <?php echo tservices_helper::input_element_error('requirement', @$errors['requirement']); ?>
                </td>
            </tr>
            
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_padbot_30">
                    
                    <h3 class="b-txt__h3">
                        Способ выполнения работы <span class="b-txt_color_de2c2c">*</span>
                        <?php tservices_helper::tooltip('Готовы ли вы встретиться с заказчиком лично, или работа происходит строго удаленно?') ?>
                    </h3>
                    
                    <div class="b-radio b-radio_layout_vertical">
                        
                        <div class="b-radio__item b-radio__item_padbot_10">
                            <input tabindex="211"<?php if($tservice->is_meet == 'f'){ ?> checked="checked"<?php } ?> type="radio" value="1" name="distance" class="b-radio__input" id="distance"/>
                            <label for="distance" class="b-radio__label b-radio__label_ptsans b-radio__label_margtop_-1">
                                Удаленно
                            </label>
                        </div>
                        <div class="b-radio__item">
                            <input tabindex="212"<?php if($tservice->is_meet == 't'){ ?> checked="checked"<?php } ?> type="radio" value="2" name="distance" class="b-radio__input" id="personal">
                            <label for="personal" class="b-radio__label b-radio__label_ptsans b-radio__label_margtop_-1">
                                Возможна личная встреча
                            </label>
                            
                            <div class="b-combo b-combo_inline-block b-combo_margtop_-5 b-combo_margleft_15_iphone b-combo_margtop_10_iphone b-combo_block_iphone">
                                <div class="
                                     b-combo__input 
                                     b-combo__input_multi_dropdown 
                                     b-combo__input_resize 
                                     b-combo__input_width_110 
                                     b-combo__input_max_width_400 
                                     b-combo__input_orientation_right 
                                     b-combo__input_init_citiesList 
                                     b-combo__input_on_click_request_id_getcities 
                                     exclude_value_0_0 
                                     exclude_value_1_0 
                                     disallow_null 
                                     <?php echo 'drop_down_default_'.$tservice->city.' multi_drop_down_default_column_0' ?>">
                                    
                                    <input tabindex="213" id="city" class="b-combo__input-text" name="city" type="text" size="80" value="<?php echo $location_value ?>" />
                                    <label class="b-combo__label" for="city"></label>
                                    <span class="b-combo__arrow"></span>
                                </div>
                            </div>
                            
                        </div>
                        <?php echo tservices_helper::input_element_error('distance', @$errors['distance']); ?>
                    </div>

                </td>
            </tr> 
            
            <tr class="b-layout__tr">
                <td class="b-layout__td">
                    <table  class="b-layout__table b-layout__table_width_full chk-tu-tbl">
                        <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_padright_10">
                                <div class="b-check">
                                    <input tabindex="214" title="Необходимо согласиться с условиями размещения" data-validators="" type="checkbox" value="1" name="agree" class="b-check__input validate-required-check" id="agree" <?php if($tservice->agree === 't'){ ?>checked="checked"<?php } ?>/>
                                </div>    
                            </td>
                            <td class="b-layout__td">
                                <label class="b-check__label b-check__label_ptsans b-check__label_color_80 b-check__label_fontsize_14" for="agree">
                                    Я подтверждаю, что указанная в услуге информация, сроки и цены соответствуют действительности. 
                                    Подтверждаю готовность оказывать услугу на заявленных условиях и принимаю тот факт, 
                                    что любой срыв сроков и договоренностей по моей вине может негативно сказаться на рейтинге и отзыве, 
                                    а также привести к возврату уплаченных средств.
                                </label> 
                                <?php echo tservices_helper::input_element_error('agree', @$errors['agree']); ?>
                            </td>                            
                        </tr>
                    </table>
                </td>
            </tr>
            
        </table>
        <input type="hidden" name="id" value="<?php echo ($tuid > 0)?$tuid:'' ?>" /> 
        <input type="hidden" name="active" value="1" />
        <input type="hidden" name="action" value="save" />   
        </form>
    </div>
    <?=$user_phone_tservice?>
    <table class="b-layout__table b-layout__table_width_full">
    <tr class="b-layout__tr">
    <td class="b-layout__td">
    <a href="javascript:void(0)" class="__send_btn b-button b-button_flat b-button_flat_green b-button_margright_20 b-button_margtop_15" onClick="<?=($tuid > 0 && $tservice->active === 't' ? "" : "yaCounter6051055.reachGoal('save_public_tu');")?>">
        <?php if( $tuid > 0 && $tservice->active === 't' ){ ?>
            Сохранить
        <?php } else { ?>
            Опубликовать
        <?php } ?>
    </a>
    <?php if(!$tservice->is_angry){ ?>
    <a href="javascript:void(0)" class="__send_btn __send_without_publish_btn b-button b-button_flat b-button_flat_grey b-button_flat_grey_pad_10_20 b-button_margtop_15 b-button_margright_20 b-button__txt_color_0f71c8" onClick="<?=($tuid > 0 && $tservice->active === 't' ? "yaCounter6051055.reachGoal('save_private_tu');" : "yaCounter6051055.reachGoal('add_private_tu');")?>">
        <?php if( $tuid > 0 && $tservice->active === 't' ){ ?>
            Снять с публикации
        <?php } else { ?>
            Сохранить без публикации
        <?php } ?>
    </a>
    <?php }else{ ?>
    <div class="b-txt b-txt_inline-block b-txt_fs_11 b-txt_padtop_10 b-txt_fr">
        Вы не можете удалить или скрыть услугу при наличии отрицательных отзывов в ней.
    </div>
    <?php } ?>
    <?php if($tuid > 0 && !$tservice->is_angry){ ?>
        <a href="javascript:void(0)" data-url="<?php echo sprintf(tservices_helper::url('delete'),$user_obj->login) ?>" onclick="yaCounter6051055.reachGoal('del_tu'); TServices.onServiceDeleteSubmit(this,<?php echo $tuid ?>)" class="b-button b-button_flat b-button_flat_red b-button_margtop_15 b-page__iphone">
            Удалить услугу
        </a>     
    <?php } ?>
    </td>
    <?php if($tuid > 0 && !$tservice->is_angry){ ?>
    <td class="b-layout__td b-layout__td_right b-page__desktop b-page__ipad">
        <a href="javascript:void(0)" data-url="<?php echo sprintf(tservices_helper::url('delete'),$user_obj->login) ?>" onclick="yaCounter6051055.reachGoal('del_tu'); TServices.onServiceDeleteSubmit(this,<?php echo $tuid ?>)" class="b-button b-button_flat b-button_flat_red b-button_margtop_15">
            Удалить услугу
        </a>
    </td>
    <?php } ?>
    </tr>
    </table>
</aside>

<?php include($fpath . 'tpl.form-sidebar.php'); ?>