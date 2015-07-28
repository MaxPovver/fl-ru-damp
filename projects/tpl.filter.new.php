<? 
  // Фильтр проектов. Вставляется в разные места. На входе:
  // $uid -- get_uid().
  // $filter -- массив с параметрами фильтра.
  // $filter_page -- код страницы (см. таблицу projects_filters).
  // $filter_show -- 1: фильтр развернут, 0: свернут. /Параметр больше не используется - Эдуард, 8.10.2009/
  // $filter_inputs -- дополнительные INPUT-ы в форму.
  // $kind -- ид. закладки (если фильтр на главной странице).
  // $page -- номер страницы (если фильтр на главной странице).
  // Плюс должны быть включены заранее все xajax функции, которые тут используются.

  if (!$uid || is_emp()) {
    if($filter_page!=0)
      return 0;
  }

  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");

  $has_hidd = TRUE;
  $filter_categories = professions::GetAllGroupsLite(TRUE);
  //$filter_countries = country::GetCountries();
  //if ($filter['country']) {$filter_cities = city::GetCities($filter['country']);}

  if($filter['city']) {
      $location_selector = "drop_down_default_{$filter['city']} multi_drop_down_default_column_1";
      $location_value    = city::GetCountryName($filter['city']).": ".city::getCityName($filter['city']);
  } elseif($filter['country']) {
      $location_selector = "drop_down_default_{$filter['country']} multi_drop_down_default_column_0";
      $location_value    = country::getCountryName($filter['country']) . ": Все города";
  }
  
  if(!$_SESSION['ph'] && !$_SESSION['top_payed']) {
      $has_hidd = false; // скрываем блок если нечего скрывать
  }
  
  if(!$filter) {
    $filter = array(
         'user_id' => $uid,
         'cost_from' => '',
         'cost_to' => '',
         'currency' => 2,
         'wo_cost' => 't',
         'country' => 0,
         'city' => 0,
         'keywords' => '',
         'categories' => array());
  }

  if($filter_params && is_array($filter_params)) {
    $filter_inputs = '';
    $filter_query = '';
    foreach($filter_params as $pn=>$pv) {
      $filter_inputs .= '<input type="hidden" name="'.$pn.'" value="'.$pv.'" />';
      $filter_query .= "&amp;{$pn}={$pv}";
    }
  }

  $all_mirrored_specs = professions::GetAllMirroredProfsId();
  $mirrored_specs = array();
  for ($is=0; $is<sizeof($all_mirrored_specs); $is++)
  {
    $mirrored_specs[$all_mirrored_specs[$is]['main_prof']] = $all_mirrored_specs[$is]['mirror_prof'];
    $mirrored_specs[$all_mirrored_specs[$is]['mirror_prof']] = $all_mirrored_specs[$is]['main_prof'];
  }


  $_SESSION['ph_categories'] = $filter['categories'];

  //создаем массив специализаций (для фильтра на главной он уже есть в $prfs, для фильтра в проектах фрилансера его нет, поэтому делаем проверку на существование
  if (!sizeof($profs)) {$all_specs = professions::GetAllProfessions("", 0, 1);}
  else                 {$all_specs = $profs;}

  
if ($kind == 2 || $kind == 7) {
    $kindTitle = 'Конкурсы';
} elseif ($kind == 4) {
    $kindTitle = 'Вакансии';
} else {
    $kindTitle = 'Проекты';
}

?>

<style type="text/css">
  .b-ext-filter__list{
  padding:0 0 0 0px;
  margin:0;
  list-style:none;
  overflow:hidden;
  }
</style>

<script type="text/javascript">
    
    
//1 = фильтр проектов
//2 = фильтр фрилансеров
var curFBulletsBox = 1;

var PROJECTS_FILTER_CURRENCY = <?= $filter['currency'] === null ? 2 : (int)$filter['currency'] ?>;


var filter_user_specs={<?
if ($filter['user_specs']) {
  $i=0;
  foreach($filter['user_specs'] as $ms)
    print(($i++?',':'').$ms.':1'); 
}
?>};

var filter_specs = new Array();
var filter_specs_ids = new Array();
<?
$spec_now = 0;
for ($i=0; $i<sizeof($all_specs); $i++)
{
  if ($all_specs[$i]['groupid'] != $spec_now) {
    $spec_now = $all_specs[$i]['groupid'];
    echo "filter_specs[".$all_specs[$i]['groupid']."]=[";
  }

  echo "[".$all_specs[$i]['id'].",'".$all_specs[$i]['profname']."']";

  if ($all_specs[$i+1]['groupid'] != $spec_now) {echo "];";}
  else {echo ",";}
}

$spec_now = 0;
for ($i=0; $i<sizeof($all_specs); $i++)
{
  if ($all_specs[$i]['groupid'] != $spec_now) {
    $spec_now = $all_specs[$i]['groupid'];
    echo "filter_specs_ids[".$all_specs[$i]['groupid']."]={";
  }

  
  echo "".$all_specs[$i]['id'].":1";

  if ($all_specs[$i+1]['groupid'] != $spec_now) {echo "};";}
  else {echo ",";}
}

?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/freelancers_filter.php";?>
var filter_mirror_specs = <?=freelancers_filters::getMirroredSpecsJsObject($all_mirrored_specs); ?>;
var filter_bullets = [[],[]];
<?if (sizeof($_SESSION['ph_categories'])) {
  for ($ci=0; $ci<2; $ci++) {
    $ph_categories[$ci] = array();
    if (sizeof($_SESSION['ph_categories'][$ci])) {
      foreach ($_SESSION['ph_categories'][$ci] as $fkey => $fvalue) {
       if ($fkey) {
        if (!freelancers_filters::mirrorExistsInArray($fkey, $ph_categories[$ci], $mirrored_specs))
        {
          if (!$fvalue)
          {
            $proftitle = professions::GetGroup($fkey, $error);
            $proftitle = $proftitle['name'];
          } else {
            $proftitle = professions::GetProfName($fkey);
            $prof_group = professions::GetProfField($fkey, 'prof_group');
          }
?>
filter_bullets[<?=$fvalue?>][<?=$fkey?>] = new Array();
filter_bullets[<?=$fvalue?>][<?=$fkey?>]['type'] = <?=$fvalue?>;
filter_bullets[<?=$fvalue?>][<?=$fkey?>]['title'] = '<?=$proftitle?>';
filter_bullets[<?=$fvalue?>][<?=$fkey?>]['parentid'] = '<?=(!($fvalue)?0:$prof_group)?>';
<?
            if ($mirrored_specs[$fkey]) {
                ?>filter_bullets[<?=$fvalue?>][<?=$fkey?>]['mirror'] = <?=$mirrored_specs[$fkey]?>;<?
            } else {
                ?>filter_bullets[<?=$fvalue?>][<?=$fkey?>]['mirror'] = 0;<?
            }
        }
        $ph_categories[$ci][] = $fkey;
       }
      }
    }
  }
}
?>

function setMoneyType(i) {
    ;//$('pf_currency').set('value', i);
}

function FilterCatalogAddCategoryType() {
    if ($('comboe_column_id').value == 0) {
        //добавляем категорию
        if(Number($('comboe_db_id').value) > 0) {
            tl = $('comboe').get("value");
            /*tl = tl.replace(/: ?/, ''); а зачем это было???*/ 
            tlf = tl;
            if (tl.length > 28) {
                tl = tl.substr(0, 28) + '...';
            }
            FilterAddBulletNew(0, $('comboe_db_id').value, tl, undefined, tlf);
            ComboboxManager.setDefaultValue('comboe', 'Все специализации', 0);            
        }
    } else {
        //добавляем подкатегорию
        //if(Number($('comboe_db_id').value) > 0) {
            tl = $('comboe').get("value");
            tlf = tl;
            if (tl.length > 28) {
                tl = tl.substr(0, 28) + '...';
            }
            for(var i = 1;i<=filter_specs_ids.length;i++) {
                if(filter_specs_ids[i] && filter_specs_ids[i][$('comboe_db_id').value] == 1) {
                    var category_id = i;
                    break;
                }
            }
            var type  = 1;
            var value = $('comboe_db_id').value;
            var combo = ComboboxManager.getInput("comboe");
            if ((value == 0)&&(parseInt(combo.breadCrumbs[0]) )) {
                type =  0;
                value = parseInt(combo.breadCrumbs[0]);
            }
            FilterAddBulletNew(type, value, tl, category_id, tlf);
            ComboboxManager.setDefaultValue('comboe', 'Все специализации', 0);            
        //}
    }
}

</script>

<div id="b_ext_filter" class="b-layout b-frm-filtr <?= !$filter_show?"b-layout_hide":""?>" page="<?=$filter_page?>">
    <form id="frm" action="<?=$frm_action?>" method="post">
    	<input type="hidden" name="action" value="postfilter" />
    	<input type="hidden" name="kind" value="<?= $kind ?>" />
        <script type="text/javascript">
            function togF(r) {
                var d = new Date();
                d.setMonth(d.getMonth() + 1);
                if (!$('b_ext_filter').hasClass('b-layout_hide')) {
                    $('b_ext_filter').addClass('b-layout_hide');
                    r.set('text', 'Развернуть');
                        
                    document.cookie='new_pf'+$('b_ext_filter').get('page')+'='+''+'; expires='+d.toGMTString() + '; path=/';
                } else {
                    $('b_ext_filter').removeClass('b-layout_hide');
                    r.set('text', 'Cвернуть');
                    document.cookie='new_pf'+$('b_ext_filter').get('page')+'='+'1'+'; expires='+d.toGMTString() + '; path=/';
                }
            }
        </script>

        <!-- Специализации -->
        <div class="b-frm-filtr__item">
            <div class="b-layout b-frm-filtr__subitem <?=$kind !== 1 ? 'b-layout_float_left' : ''?>">
                <input id="pf_category" name="pf_category" type="hidden" />
                <input id="pf_subcategory" name="pf_subcategory" type="hidden" />
                <div class="b-layout">
                    <table class="b-layout__table b-layout__table_width_full">
                        <tbody>
                            <tr class="b-layout__tr">
                                <td class="b-layout__td">
                                    <div class="b-combo b-combo_margright_5 b-combo_zindex_3">
                                        <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_resize b-combo__input_max-width_450 b-combo__input_visible_height_200 b-combo__input_arrow_yes b-combo__input_init_professionsList sort_cnt drop_down_default_0 multi_drop_down_default_column_0 exclude_value_0_0">
                                            <input id="comboe" class="b-combo__input-text" name="" type="text" size="80" value="Все специализации" />
                                            <span class="b-combo__arrow"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="b-layout__td">
                                    <a class="b-button b-button_flat b-button_flat_grey" href="javascript:void(0)" onclick="FilterCatalogAddCategoryType();">Добавить</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>            
                <ul id="pf_specs" class="b-ext-filter__list b-layout_padtop_10"></ul>
            </div>
            <!-- Страны -->
            <?php if ($kind == 5 || $kind == 4): ?>
            <div class="b-layout b-frm-filtr__subitem">
                <div class="b-combo  b-combo_zindex_2">
                    <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_arrow_yes b-combo__input_init_citiesList b-combo__input_on_click_request_id_getcities <?=$location_selector?> override_value_id_0_0_Все+страны override_value_id_1_0_Все+города">
                        <input id="location" class="b-combo__input-text" name="" type="text" size="80" value="<?= ($location_value ? $location_value : "Все страны")?>" />
                        <label class="b-combo__label" for="location"></label>
                        <span class="b-combo__arrow"></span>
                    </div>
                </div>
            </div>
            <?php elseif ($kind == 2 || $kind == 7): ?>
            <div class="b-layout b-frm-filtr__subitem">
                <label class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">До окончания от&#160;</label>
                <div class="b-combo b-combo_inline-block">
                    <div class="b-combo__input b-combo__input_width_25">
                        <input class="b-combo__input-text" name="pf_end_days_from" type="text" size="10" value="<?= $filter['konkurs_end_days_from'] !== null ? $filter['konkurs_end_days_from'] : '' ?>" />
                    </div>
                </div><label
                                    class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;до&#160;</label><div
                                     class="b-combo b-combo_inline-block">
                                    <div class="b-combo__input b-combo__input_width_25">
                                        <input class="b-combo__input-text" name="pf_end_days_to" type="text" size="10" value="<?= $filter['konkurs_end_days_to'] !== null ? $filter['konkurs_end_days_to'] : '' ?>" />
                                    </div>
                                </div><span
                                 class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_5">&#160;дней</span>

            </div>
            <?php endif; ?>
            <div class="b-layout b-layout_clear"></div>
        </div>
            
        <!-- Опции -->
        <div class="b-frm-filtr__item">
            <div class="b-check b-check_padbot_10 b-check_inline-block b-check_padright_30">
                <input id="for-pro" class="b-check__input" name="pf_pro_only" type="checkbox" <?= ($filter['pro_only']=='t') ? 'checked="checked"': '' ?> value="1" />
                <label for="for-pro" class="b-check__label b-check__label_fontsize_13">Только для <?= view_pro(false, false, true, 'пользователей с платным аккаунтом')?></label>
            </div>
            
            <div class="b-check b-check_padbot_10 b-check_inline-block b-check_padright_30">
                <input id="for-ver" class="b-check__input" name="pf_verify_only" type="checkbox" <?= ($filter['verify_only']=='t') ? 'checked="checked"': '' ?> value="1" />
                <label for="for-ver" class="b-check__label b-check__label_fontsize_13">Только для <?= view_verify('верифицированных пользователей', '')?></label>
            </div>

            <div class="b-check b-check_padbot_10 b-check_inline-block b-check_padright_30">
                <input id="for-urgent" class="b-check__input" name="pf_urgent_only" type="checkbox" <?= ($filter['urgent_only']=='t') ? 'checked="checked"': '' ?> value="1" />
                <label for="for-urgent" class="b-check__label b-check__label_fontsize_13">Только срочные <span class="b-icon b-icon__fire b-icon_top_1"></span></label>
            </div>
            
            <div class="b-check b-check_padbot_10 b-check_inline-block b-check_padright_30">
                <input id="for-less2" class="b-check__input" name="pf_less_offers" type="checkbox" <?= ($filter['less_offers']=='t') ? 'checked="checked"': '' ?> value="1" />
                <label for="for-less2" class="b-check__label b-check__label_fontsize_13">Меньше 2 ответов в проекте</label>
            </div>
            
            <?php if (!(is_emp() || !get_uid(false))): ?>
            <div class="b-check b-check_inline-block b-check_padright_30"> 		 
                <input id="pf_my_specs" class="b-check__input" type="checkbox" name="pf_my_specs" value="1" <?= ($filter['my_specs']=='t') ? 'checked="checked"': '' ?> />
                <label for="pf_my_specs" class="b-check__label b-check__label_fontsize_13"> <?= $kindTitle ?> только по моей специализации</label> 	
            </div>                 
            <?php endif; ?>
            
            <!-- Заблокированные -->
            <?php if (hasPermissions('projects')): ?>
            <div class="b-check b-check_inline-block b-check_padright_30">
                <input id="for-block" class="b-check__input" name="pf_block_only" type="checkbox" <?= ($filter['block_only']=='t') ? 'checked="checked"': '' ?> value="1" />
                <label for="for-block" class="b-check__label b-check__label_fontsize_13">Только заблокированные</label>
            </div>
            <?php endif; ?>
            
            <div class="b-check b-check_padbot_10 b-check_inline-block b-check_padright_30">
                <input id="for-hide_exec" class="b-check__input" name="hide_exec" type="checkbox" <?= ($filter['hide_exec']=='t') ? 'checked="checked"': '' ?> value="1" />
                <label for="for-hide_exec" class="b-check__label b-check__label_fontsize_13">Скрыть проекты в которых определен исполнитель</label>
            </div>

        </div>
            
        <!-- Бюджет -->
        <div class="b-frm-filtr__item">
            <div class="b-layout b-frm-filtr__subitem b-layout_float_left">
                <table class="b-layout__table b-layout__table_width_full">
                    <tr class="b-layout__tr">
                        <td class="b-layout__td b-layout__td_width_70">
                            <div class="b-layout__txt b-layout__txt_padtop_5">Бюджет от</div>
                        </td>
                        <td class="b-layout__td b-layout__td_padright_10">
                            <div class="b-combo">
                                <div class="b-combo__input b-combo_valign_mid">
                                    <input id="pf_cost_from" class="b-combo__input-text b-combo__input-text_fontsize_15" name="pf_cost_from" value="<?=$filter['cost_from']?>" maxlength="6" type="text" size="80"  />
                                    <label class="b-combo__label" for="pf_cost_from"></label>
                                </div>
                            </div>
                        </td>
                        <td class="b-layout__td b-layout__td_width_60"><script type="text/javascript"> var currencyList = {0:"USD", 1:"Евро", 2:"Руб"}</script><div
                         class="b-combo b-combo_inline-block b-combo_valign_mid">
                                <div class="b-combo__input b-combo__input_width_65 	b-combo__input_multi_dropdown b-combo__input_min-width_40 b-combo__input_arrow_yes b-combo__input_init_currencyList drop_down_default_2 reverse_list" >
                                    <input id="pf_currency" type="hidden" name="pf_currency" value="<?= $filter['currency'] === null ? 2 : (int)$filter['currency'] ?>" />
                                    <input id="currency_text" class="b-combo__input-text b-combo__input-text_fontsize_15" name="" type="text" size="80" onchange="$('pf_currency').value = $('currency_text_db_id').value" readonly="readonly"/>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Ключевые слова -->
            <div class="b-layout b-frm-filtr__subitem">
                <div class="b-combo b-combo_static">
                    <div class="b-combo__input b-combo__input_static">
                        <input id="pf_keywords" class="b-combo__input-text" placeholder="Ключевые слова" type="text" name="pf_keywords" value="<?=htmlspecialchars($filter['keywords'], ENT_QUOTES, 'cp1251')?>" maxlength="255" />
                    </div>
                </div>
            </div>
        </div>
            

        <div class="b-layout b-layout_overflow_hidden">
            <div class="b-buttons b-buttons_float_right">
                <button type="button" class="b-button b-button_flat b-button_flat_green" onclick="$('frm').submit();">
                    Применить фильтр
                </button>
                &#160;&#160;<a class="b-buttons__link b-buttons__link_margleft_10 b-buttons__link_dot_0f71c8" 
                               onclick="FilterClearFormNew()">Очистить</a>
            </div>
         </div>
    </form>
</div><!-- b-ext-filter -->
