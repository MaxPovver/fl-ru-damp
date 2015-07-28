<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");

$filter_apply = ($filter['active'] == "t");
$filter_categories = professions::GetAllGroupsLite(TRUE);

$all_mirrored_specs = professions::GetAllMirroredProfsId();
$mirrored_specs = array();
for ($is=0; $is<sizeof($all_mirrored_specs); $is++) {
    $mirrored_specs[$all_mirrored_specs[$is]['main_prof']] = $all_mirrored_specs[$is]['mirror_prof'];
    $mirrored_specs[$all_mirrored_specs[$is]['mirror_prof']] = $all_mirrored_specs[$is]['main_prof'];
}

$_SESSION['ph_categories'] = $filter['categories'];

$frm_action = "/index.php";

if (!sizeof($profs)) {$all_specs = professions::GetAllProfessions("", 0, 1);}
else                 {$all_specs = $profs;}
?>
<script type="text/javascript">
    
    
//1 = фильтр проектов
//2 = фильтр фрилансеров
var curFBulletsBox = 1;

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
<?
if (sizeof($_SESSION['ph_categories'])) {
  for ($ci=0; $ci<2; $ci++) {
    $ph_categories[$ci] = array();
    if (sizeof($_SESSION['ph_categories'][$ci])) {
      foreach ($_SESSION['ph_categories'][$ci] as $fkey => $fvalue) {
       if ($fkey) {
        if ( !freelancers_filters::mirrorExistsInArray($fkey, $ph_categories[$ci], $mirrored_specs) )
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

function FilterCatalogAddCategoryType() {
    if ($('comboe_column_id').value == 0 || Number($('comboe_db_id').value) == 0) {
        //добавляем категорию
        $('comboe_db_id').set("value", Number($$('input[name="comboe_columns[0]"]').get('value')));
        if(Number($('comboe_db_id').value) > 0) {
            tl = $('comboe').get("value");
            tlf = tl;
            if (tl.length > 28) {
                tl = tl.substr(0, 28) + '...';
            }
            FilterAddBulletNew(0, $('comboe_db_id').value, tl, undefined, tlf);
            $('comboe').set("value", "Все разделы");
            $('comboe_db_id').set("value", 0);
            $('comboe_column_id').set("value", 0);
        }
    } else {
        //добавляем подкатегорию
        if(Number($('comboe_db_id').value) > 0) {
            tl = $('comboe').get("value");
            tlf = tl;
            if (tl.length > 28) {
                tl = tl.substr(0, 28) + '...';
            }
            for(var i = 1;i<=filter_specs_ids.length;i++) {
                if(filter_specs_ids[i])
                if(filter_specs_ids[i][$('comboe_db_id').value] == 1) {
                    var category_id = i;
                    break;
                }
            }
            FilterAddBulletNew(1, $('comboe_db_id').value, tl, category_id, tlf);
            $('comboe').set("value", "Все разделы");
            $('comboe_db_id').set("value", 0);
            $('comboe_column_id').set("value", 0);
        }
    }
}

</script>

<div class="b-page__filter">
    <div class="b-menu b-menu_tabs b-menu_relative b-menu_padbot_2">
        <div class="b-menu__filter">
            <span class="b-icon b-icon_top_2 b-icon__filtr b-icon_float_left<?= $filter_apply ? ' b-icon__filtr_on' : ' b-icon__filtr_off' ?> b-icon_margleft_20"></span>
            <a class="b-menu__link  <?= $filter_apply ? 'b-menu__link_bordbot_dot_6db335' : 'b-menu__link_bordbot_dot_c10600' ?> b-menu__filter_switcher <?= $kind==8 ? "filter-offers" : ""; ?>">Фильтр</a>
        </div>
         <ul class="b-menu__list"></ul>
    </div>
    
    <form id="frm" action="<?=$frm_action?>?kind=8" method="post">
        <div id="b_ext_filter" class="b-ext-filter b-ext-filter_zindex_2 <?= !$filter_show?"b-ext-filter_hide":""?>" page="<?=$filter_page?>">
            <input type="hidden" value="post_offers_filter" name="action" />
            <input type="hidden" value="0" name="kind" />
            <div class="b-ext-filter__inner">
                <div class="b-ext-filter__txt b-ext-filter__txt_padbot_10">
                    Фильтр предложений
                    <? if ($filter_apply) { ?>
                        <span class="b-ext-filter__switcher b-ext-filter__switcher_on">
                                <span class="b-icon b-icon__filtr b-icon__filtr_on"></span>включен &#160;&#160;&#160;&#160;
                                <a class="b-ext-filter__link " href="<?=$frm_action?>?kind=8&action=delete_offers_filter" onClick="_gaq.push(['_trackEvent', 'User', '<?=(is_emp() ? 'Employer' : (get_uid(false) ? 'Freelance' : 'Unauthorized'))?>', 'button_filter_delete_catalog']); ga('send', 'event', '<?=(is_emp() ? 'Employer' : (get_uid(false) ? 'Freelance' : 'Unauthorized'))?>', 'button_filter_delete_catalog'); ">Отключить</a>
                        </span>
                    <? } else { ?>
                        <span class="b-ext-filter__switcher b-ext-filter__switcher_off">
                                <span class="b-icon b-icon__filtr b-icon__filtr_off"></span>отключен &#160;&#160;
                                <a class="b-ext-filter__link " href="<?=$frm_action?>?kind=8&action=activate_offers_filter" onClick="_gaq.push(['_trackEvent', 'User', '<?=(is_emp() ? 'Employer' : (get_uid(false) ? 'Freelance' : 'Unauthorized'))?>', 'button_filter_include_main']); ga('send', 'event', '<?=(is_emp() ? 'Employer' : (get_uid(false) ? 'Freelance' : 'Unauthorized'))?>', 'button_filter_include_main'); ">Включить</a>
                        </span>
                    <? } ?>
                    <a class="b-ext-filter__slide <?= !$filter_show?"b-ext-filter__slide_hide":""?> filter-offers" href="#">Cвернуть фильтр<span class="b-ext-filter__toggler b-ext-filter__toggler_up"></span></a>
                </div>
                <div class="b-form b-form_padtop_15">
                    <input name="pf_category" id="pf_category" type="hidden" />
                    <input name="pf_subcategory" id="pf_subcategory" type="hidden" />
                    <label class="b-form__name b-form__name_padtop_8 b-form__name_width_60">Разделы</label><div
                     class="b-combo b-combo_inline-block b-combo_margright_10">
                        <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_150 b-combo__input_resize b-combo__input_max-width_450 b-combo__input_visible_height_200 b-combo__input_arrow_yes  b-combo__input_init_professionsList sort_cnt drop_down_default_0 multi_drop_down_default_column_0">
                            <input id="comboe" class="b-combo__input-text" name="" type="text" size="80" value="Все разделы" />
                            <span class="b-combo__arrow"></span>
                        </div>
                    </div><a
                     class="b-button b-button_inline-block b-button_rectangle_color_transparent b-button_margtop_-4" href="javascript:void(0)" onclick="FilterCatalogAddCategoryType();"> 
                        <span class="b-button__b1"> 
                            <span class="b-button__b2"> 
                                <span class="b-button__txt">Добавить</span> 
                            </span> 
                        </span> 
                    </a>
                    <? if(!is_emp()) {?>
                    <div class="b-check b-check_padleft_60 b-check_padtop_8">
                        <input id="pf_only_my_offs" class="b-check__input" type="checkbox" value="1" <? if ($filter['only_my_offs']=='t') {?>checked="checked" <? } ?>name="pf_only_my_offs" />
                        <label class="b-check__label " for="pf_only_my_offs">Смотреть только мои предложения </label>
                    </div>
                    <? }//if?>
                    <ul class="b-ext-filter__list" id="pf_specs"></ul>
                </div>
                <div class="b-form b-form_padtop_10 b-form_padleft_57">
                    <a class="b-button b-button_rectangle_color_transparent"  href="javascript:void(0)" onclick="_gaq.push(['_trackEvent', 'User', '<?=(is_emp() ? 'Employer' : (get_uid(false) ? 'Freelance' : 'Unauthorized'))?>', 'button_filter_apply_main']); ga('send', 'event', '<?=(is_emp() ? 'Employer' : (get_uid(false) ? 'Freelance' : 'Unauthorized'))?>', 'button_filter_apply_main'); $('frm').submit();">
                        <span class="b-button__b1">
                            <span class="b-button__b2">
                                <span class="b-button__txt">Применить фильтр</span>
                            </span>
                        </span>
                    </a>
                </div>
                <span class="b-ext-filter__nosik b-ext-filter__nosik_right_25"></span>
            </div><!-- b-ext-filter__body -->
        </div><!-- b-ext-filter -->
    </form>
</div>
<script type="text/javascript">
FilterAddBulletNew(0,0,0,0);
</script>