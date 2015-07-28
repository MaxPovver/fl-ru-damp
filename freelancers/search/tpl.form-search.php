<? if($type == 'projects') { //if 1.2 0014574?>	 
<script>	 
kword = search_kwords;	 
</script>	 
<? } elseif($type != 'users') { //if 1.1  0014574?>	 
<script>	 
kword = kword.combine(search_kwords);	 
</script>	 
<? } ?>
<div id="search-form">
    <form action="" id="main-search-form" method="get">
       <div>
        <input type="hidden" id="search-action" name="action" value="<?=($action == 'search_advanced')?$action:'search'?>" />
        <?php if($show_all_freelancers): ?><input type="hidden" name="show" value="all" /><?php endif; ?>

            
                    
                    <table class="b-layout__table b-layout__table_width_full">
                        <tr class="b-layout__tr">
                            <td class="b-layout__td b-layout__td_valign_bot ">
                                <div class="b-combo b-combo_margbot_10_ipad">
                                    <div class="b-combo__input b-combo__input_height_35 b-combo__input_search">
                                        <input id="search-request" type="text" placeholder="Укажите любые данные об исполнителе" class="b-combo__input-text" autocomplete="off"  name="search_string" value="<?= htmlspecialchars($search_string?str_replace($string_professions, "", $search_string):"")?>" />
                                        <input id="search-hint" name="search_hint" type="hidden" value="<?=$search_input_hint?>">
                                    </div>
                                </div>                                 
                            </td>
                            <td class="b-layout__td b-layout__td_width_160 b-layout__td_valign_bot b-layout__td_padleft_10 b-layout__td_width_full_ipad b-layout__td_pad_null_ipad">
                                <button type="submit" class="b-button b-button_flat b-button_flat_green b-button_nowrap b-button_padlr_10">
                                    Найти исполнителя
                                </button>
                            </td>
                            <?php /*
                            <td class="b-layout__td b-layout__td_width_180 b-layout__td_valign_bot b-layout__td_width_full_ipad b-layout__td_padleft_10 b-layout__td_pad_null_ipad"><a class="b-button b-button_flat b-button_flat_green b-button_nowrap b-button_padlr_10" href="/masssending/">Рассылка по каталогу</a></td>
									 скрываем временно 28134 п.15
									 */ ?>
                        </tr>
                    </table>
                        <?php if(is_emp()||!get_uid(false)) { ?><a class="b-button b-button_flat b-button_flat_green b-page__ipad b-page__iphone __ga__sidebar__add_project" href="/public/?step=1&kind=1">Опубликуйте проект</a><?php } ?>
                   
                    
                    
                    
                    
                    <?php if(false): ?> 
                    
                    <div class="flt-block <?= isset($_SESSION['search_advanced'][$type])?"":"last";?>">
                          <div class="b-search <?php if($view_advanced){ ?>b-search_margright_370<?php } else {?>b-search_margright_200<?php } ?> b-search_marg_null_iphone">
                                  <table class="b-search__table" cellpadding="0" cellspacing="0"><tr class="b-search__tr"><td class="b-search__input">
                                      <div id="body_1" class="b-input  b-input_height_24">
                                              <input id="search-request" class="b-input__text " placeholder="Например, <?=$search_input_hint?>" autocomplete="off"  name="search_string" type="text" value="<?= htmlspecialchars($search_string?str_replace($string_professions, "", $search_string):"")?>" />
                                              <input id="search-hint" name="search_hint" type="hidden" value="<?=$search_input_hint?>">
                                      </div>
                                  </td><td class="b-search__button">
                                  <a id="search-button" class="b-button b-button_flat b-button_flat_grey b-button_margleft_5" href="javascript:void(0)">Найти</a>
                                  </td></tr>
                                  </table>
                           </div>
                        
                        
                          <?php if(false): ?>  
                        
                          <div class="search-ext">
                              <?php if($view_advanced){ ?><a href="javascript:void(0)" id="search-advanced-button"><?= $name_advanced_search;?></a><?php }//if?>
                              <?php include($_SERVER['DOCUMENT_ROOT']."/search/tpl.user-limit-block.php"); ?>
                          </div>
                        
                          <?php endif; ?>
                        
                    </div>
                    <?php endif; ?>
                    
                    
                    
                
                <?php include('tpl.form-users.php');?>
                
        </div>
        
        
        
    </form>
</div>
<!--/#search-form-->
                