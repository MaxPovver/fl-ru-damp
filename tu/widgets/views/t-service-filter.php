<?php
if(!defined('IN_STDF')) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 * Представление для виджета TServiceFilter
 *
 * @var TServiceFilter $this
 */

/** @var bool $filter_show показывать ли фильтр */
$filter_show = true;
$validators = '';

/** @var string $category_field_id ID поля категории */
$category_field_id = 'category_id';
$category_selector = '';
$category_value = '';
$category_enabled = true;
$category_error = false;


if ($this->filter->category)
{
	$category_selector = "drop_down_default_{$this->filter->category} multi_drop_down_default_column_0";
	$category_value = $this->getCategoryAngGroupTitle();
}
elseif ($this->filter->category_group)
{
	$category_selector = "drop_down_default_{$this->filter->category_group} multi_drop_down_default_column_0";
	$category_value = $this->getCategoryGroupTitle();
}


/** @var array $prices цены */
$prices_enabled = true;
$prices_error = false;
$prices = $this->getPriceRanges();

/** @var string $location_field_id ID поля местоположения */
$location_field_id = 'location_id';
$location_selector = '';
$location_value = '';
$location_enabled = true;
$location_error = false;
if ($this->filter->city)
{
	$location_selector = "drop_down_default_{$this->filter->city} multi_drop_down_default_column_1";
	$location_value = $this->getCityTitle();
} elseif($this->filter->country)
{
	$location_selector = "drop_down_default_{$this->filter->country} multi_drop_down_default_column_1";
	$location_value = $this->getCountryTitle();
}

/** @var array $keyword_enabled ключевые слова */
$keyword_enabled = true;
$keyword_error = false;
$keywords = $this->filter->keywords;

/** @var int - Цена до */
$price_max = ($this->filter->price_max > 0)?$this->filter->price_max:'';

?>

<div id="b_ext_filter" class="b-layout ">
	<form id="frm" method="post" action="#tu_filter">
		<input type="hidden" name="action" value="tupostfilter" />
        <input type="hidden" name="order" id="order" value="<?php echo $this->filter->order ?>" />
        <input type="hidden" name="category_id_db_id" value="<?php echo $this->filter->category?$this->filter->category:$this->filter->category_group ?>" />
        
            <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_30 b-layout_margbot_20_ipad">
               <tr class="b-layout__tr">
                  <td class="b-layout__td b-layout__td_padright_10 b-layout__td_valign_bot">
                      <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5"><?php if(!(get_uid(false) && !is_emp())){  ?>Что вы хотите заказать<?php } else {?>Поиск услуг<?php } ?></div>
                      <div class="b-combo">
                          <div class="b-combo__input b-combo__input_height_35">
                              <input tabindex="9" id="keywords" type="text" placeholder="Например, дизайн визитки" value="<?=$keywords?>" class="b-combo__input-text" name="keywords" maxlength="255" />
                          </div>
                      </div>
                  </td>
                  <td class="b-layout__td b-layout__td_padright_10 b-layout__td_width_200 b-layout__td_valign_bot">
                      
                      <?php if(false): ?>
                      <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5">Специализация</div>
                      <div class="b-combo b-combo_shadow_width_280_ipad b-combo_overflow-x_yes">
                          <div class="
                                 b-combo__input
                                 b-combo__input_height_35
                                 b-combo__input_multi_dropdown
                                 b-combo__input_resize
                                 b-combo__input_orientation_right
                                 b-combo__input_visible_height_200
                                 <?php //b-combo__input_on_load_request_id_gettucategories ?>
                                 b-combo__input_init_tuCategories
                                 override_value_id_0_0_Все+категории
                                 <?=$category_selector?>
                                 <?php if (!$category_enabled) { ?>b-combo__input_disabled<?php } ?>
                                 <?php if ($category_error) { ?> b-combo__input_error<?php } ?>
                                 ">
                              <input tabindex="1" data-validators="<?=$validators?>" id="<?=$category_field_id?>" class="b-combo__input-text" name="<?=$category_field_id?>" type="text" size="80" value="<?=$category_value?$category_value:'Все категории'?>" />
                              <label for="<?=$category_field_id?>" class="b-combo__label"><?=$category_value?></label>
                              <span class="b-combo__arrow"></span>
                          </div>
                      </div>
                      <?php else: ?>
                      <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5">Цена до (руб.)</div>
                      <div class="b-combo">
                          <div class="b-combo__input b-combo__input_height_35">
                              <input tabindex="10" id="price_max" type="text" pattern="\d*" title="Введите сумму в рублях" placeholder="Например, 1000" value="<?=$price_max?>" class="b-combo__input-text" name="price_max" maxlength="7" />
                          </div>
                      </div>                      
                      <?php endif; ?>
                      
                  </td>
                  <td class="b-layout__td b-layout__td_width_120 b-layout__td_valign_bot">
                      <button class="b-button b-button_flat b-button_flat_orange b-button_padlr_10 b-button_block" type="submit">Найти услугу</button>
                  </td>
                  <?php if(!is_emp()){ ?>
                  <td class="b-layout__td b-layout__td_width_140 b-layout__td_padleft_10 b-layout__td_valign_bot b-layout__td_pad_null_ipad">
				      <?php if(!get_uid(false)){  ?>
                          <a class="b-button b-button_flat b-button_flat_green b-button_padlr_10 b-button_nowrap b-button_block" href="/registration/?user_action=new_tu">Добавить услугу</a>
					  <?php } else {?>
                          <a href="<?php echo sprintf(tservices_helper::url('new'),$_SESSION['login']); ?>" class="b-button b-button_flat b-button_flat_green b-button_padlr_10 b-button_nowrap b-button_block" onClick="yaCounter6051055.reachGoal('add_new_tu');">Добавить услугу</a>
					  <?php } ?>
                  </td>
				  <?php } ?>
               </tr>
            </table>
	</form>
</div>
<?php  /*
            <div class="b-frm-filtr__item"> 	
                    <div class="b-frm-fltr__title">Цена</div> 	
                    <?php foreach($prices as $i => $price) { ?> 	
                            <div class="b-check b-check_padbot_10"> 	
                                    <input tabindex="<?php echo $i+1 ?>" type="checkbox" name="<?=$price['id']?>" value="<?=$price['value']?>" class="b-check__input" id="<?=$price['id']?>" <?= ($price['checked']) ? 'checked="checked"': '' ?>> 	
                                    <label class="b-check__label b-check__label_ptsans" <?=$price['checked']?> for="<?=$price['id']?>"> 	
                                            <?=$price['title']?> 	
                                    </label> 	

                            </div> 	
                    <?php } ?> 	 
            </div> 





			<div class="b-frm-filtr__item" style="display:none;">
				<fieldset class="b-frm-filtr__item b-frm-filtr__item_last">
					<div class="b-combo b-combo_inline-block b-combo_overflow-x_yes b-combo_shadow_width_280_ipad">
						<div class="
							b-combo__input
							b-combo__input_multi_dropdown
							b-combo__input_arrow_yes
							b-combo__input_init_citiesList
							b-combo__input_on_click_request_id_getcities
							override_value_id_0_0_Все+страны
							override_value_id_1_0_Все+города
							<?=$location_selector?>
							<?php if (!$location_enabled) { ?>b-combo__input_disabled<?php } ?>
							<?php if ($location_error) { ?> b-combo__input_error<?php } ?>
							">
						<input tabindex="8" id="<?=$location_field_id?>" class="b-combo__input-text" name="<?=$location_field_id?>" type="text" size="80" value="<?=$location_value?$location_value:'Все страны'?>" />
						<label class="b-combo__label" for="<?=$location_field_id?>"></label>
						<span class="b-combo__arrow"></span>
					  </div>
                    </div>
				</fieldset>
			</div>
            
			<div class="b-frm-filtr__item">
				<div class="b-combo">
					<div class="b-combo__input">
						<input tabindex="9" id="keywords" type="text" placeholder="Ключевые слова" value="<?=$keywords?>" class="b-combo__input-text" name="keywords" maxlength="255" />
					</div>
				</div>
			</div>
                    
			<div class="b-buttons">
				<button onclick="$('frm').submit();" class="b-button b-button_flat b-button_flat_green" type="submit">Применить</button>&nbsp;&nbsp;
				<a onclick="TServices_Catalog.clearFilterForm(this);" class="b-buttons__link b-buttons__link_margleft_10 b-buttons__link_dot_0f71c8">Очистить</a>
			</div>
		</div> <!-- / div#filtrToggle -->
	</form>
</div> <!-- / div#b_ext_filter -->

<?php */  ?>

