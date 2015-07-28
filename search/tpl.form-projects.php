<?php
require_once ($_SERVER['DOCUMENT_ROOT'] . "/xajax/countrys.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
$xajax->printJavascript('/xajax/');

$is_show_adv = isset($_SESSION['search_advanced'][$type]);

if(!$filter) {
    $filter = $_SESSION['search_advanced'][$type];
}

$categories = professions::GetAllGroupsLite(TRUE);
$countries = country::GetCountries();

if ($filter['country']) {
    $cities = city::GetCities($filter['country']);
}

$all_mirrored_specs = professions::GetAllMirroredProfsId();
$mirrored_specs = array();
for ($is=0; $is<sizeof($all_mirrored_specs); $is++) {
    $mirrored_specs[$all_mirrored_specs[$is]['main_prof']] = $all_mirrored_specs[$is]['mirror_prof'];
    $mirrored_specs[$all_mirrored_specs[$is]['mirror_prof']] = $all_mirrored_specs[$is]['main_prof'];
}

$_SESSION['ph_categories'] = $filter['categories'];

  //создаем массив специализаций (для фильтра на главной он уже есть в $prfs, для фильтра в проектах фрилансера его нет, поэтому делаем проверку на существование
if (!sizeof($profs)) {
    $all_specs = professions::GetAllProfessions("", 0, 1);
} else {
    $all_specs = $profs;
}

?>
<script type="text/javascript">
//1 = фильтр проектов
//2 = фильтр фрилансеров
var curFBulletsBox = 1;

var filter_user_specs={
<?php if ($filter['user_specs']) {
    $i=0;
    foreach($filter['user_specs'] as $ms) {
        print(($i++?',':'').$ms.':1'); 
    }
}?>};

var filter_specs = new Array();
var filter_specs_ids = new Array();
<?
$spec_now = 0;
for ($i=0; $i<sizeof($all_specs); $i++) {
    if ($all_specs[$i]['groupid'] != $spec_now) {
        $spec_now = $all_specs[$i]['groupid'];
        echo "filter_specs[".$all_specs[$i]['groupid']."]=[";
    }
    
    echo "[".$all_specs[$i]['id'].",'".$all_specs[$i]['profname']."']";

    if ($all_specs[$i+1]['groupid'] != $spec_now) {
        echo "];";
    } else {
        echo ",";
    }
}

$spec_now = 0;
for ($i=0; $i<sizeof($all_specs); $i++) {
    if ($all_specs[$i]['groupid'] != $spec_now) {
        $spec_now = $all_specs[$i]['groupid'];
        echo "filter_specs_ids[".$all_specs[$i]['groupid']."]={";
    }
    
    echo "".$all_specs[$i]['id'].":1";

    if ($all_specs[$i+1]['groupid'] != $spec_now) {
        echo "};";
    } else {
        echo ",";
    }
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
                $fvalue = intval($fvalue);
                $fkey   = intval($fkey);
                if ($fkey) {
                    if ( !freelancers_filters::mirrorExistsInArray($fkey, $ph_categories[$ci], $mirrored_specs) ) {
                        if (!$fvalue) {
                            $proftitle = professions::GetGroup($fkey, $error);
                            $proftitle = $proftitle['name'];
                        } else {
                            $proftitle = professions::GetProfName($fkey);
                            $prof_group = professions::GetProfField($fkey, 'prof_group');
                        }
                        ?>filter_bullets[<?=$fvalue?>][<?=$fkey?>] = new Array();<?
                        ?>filter_bullets[<?=$fvalue?>][<?=$fkey?>]['type'] = <?=(int)$fvalue?>;<?
                        ?>filter_bullets[<?=$fvalue?>][<?=$fkey?>]['title'] = '<?=$proftitle?>';<?
                        ?>filter_bullets[<?=$fvalue?>][<?=$fkey?>]['parentid'] = '<?=(!($fvalue)?0:(int)$prof_group)?>';<?
                        
                        if ($mirrored_specs[$fkey]) {
                            ?>filter_bullets[<?=$fvalue?>][<?=$fkey?>]['mirror'] = <?=$mirrored_specs[$fkey]?>;<?
                        } else {
                            ?>filter_bullets[<?=$fvalue?>][<?=$fkey?>]['mirror'] = 0;<?
                        }
                    }
                    $ph_categories[$ci][] = (int)$fkey;
                }
            }
        }
    }
}
?>

</script>
<fieldset class="flt-cnt" id="advanced-search" style="display:<?= $is_show_adv?"block":"none";?>; padding:0 15px;">
          <div class="flt-block c" >
               <label class="flt-lbl">Бюджет:</label>
               <div class="flt-b-in" >
                    <span class="flt-prm bjt-bl">
                        <input type="text" maxlength="6" value="<?= $filter['cost_from']!=''?intval($filter['cost_from']):$filter['cost_from']?>" name="pf_cost_from" id="pf_cost_from" class="flt-prm1" size="10" /> &mdash;
	                	<input type="text" maxlength="6" value="<?= $filter['cost_to']!=''?intval($filter['cost_to']):$filter['cost_to']?>" name="pf_cost_to" id="pf_cost_to" class="flt-prm1" size="10" />&nbsp;&nbsp;
                            <select name="pf_currency" class="pf-sel" id="pf_currency">
                               <option value="2" <?= ($filter['currency'] === null || $filter['currency'] == 2 ? 'selected="selected"' : '')?>>Руб</option>
                               <option value="0" <?= ($filter['currency'] !== null && $filter['currency'] == 0 ? 'selected="selected"' : '')?>>USD</option>
                               <option value="1" <?= ($filter['currency'] == 1 ? 'selected="selected"' : '')?>>Euro</option>
                            </select>
                    </span>
                    <div class="b-check b-check_inline-block b-check_padtop_5">
                        <input class="b-check__input" type="checkbox" value="1" name="pf_wo_budjet" id="pf_wo_budjet" <?= ($filter['wo_cost'] == 't')?'checked="checked"':''?> /> 
                        <label for="pf_wo_budjet" class="b-check__label">Смотреть проекты с неуказанным бюджетом</label>
                    </div>
                    <br/><br/>
                    <?/* #0019741
                    <label><span class="i-chk"><input type="checkbox" id="pf_only_sbr" name="pf_only_sbr" value="1" <?= ($filter['only_sbr'] == 't') ? 'checked="checked"' : ''; ?> /></span> Только проекты, предусматривающие "<a href="/promo/sbr/" target="_blank">Сделку без риска</a>"</label>
                     */ ?>
               </div>
          </div>
          <div class="flt-block c" >
               <label class="flt-lbl">Раздел:</label>
               <div class="flt-b-in" >
                    <div class="flt-b-row" style="padding-bottom:10px;">
                         <span class="flt-prm">
                           <select onchange="FilterSubCategory(this.value)" id="pf_category" name="pf_category" class="flt-p-sel">
                             <option value="0">Выберите раздел</option>
                             <? foreach($categories as $category) { if($category['id'] <= 0) continue; ?>
                             <option value="<?=$category['id']?>"><?=$category['name']?></option>
                             <? } ?>
                           </select>
                         </span><a href="javascript: void(0);" onclick="if($('pf_category').value != 0) FilterAddBullet(0, $('pf_category').value, $('pf_category').options[$('pf_category').selectedIndex].text, 0);" class="lnk-dot-blue">Добавить</a>
                    </div>
                    <div class="flt-b-row">
                         <span id="frm_subcategory" class="flt-prm">
                           <select id="pf_subcategory" name="pf_subcategory" class="flt-p-sel" >
                           		<option value="0">Выберите подраздел</option>
                           </select>
                         </span><a href="javascript: void(0);" onclick="if($('pf_subcategory').value != 0) FilterAddBullet(1, $('pf_subcategory').value, $('pf_subcategory').options[$('pf_subcategory').selectedIndex].text, $('pf_category').value);" class="lnk-dot-blue">Добавить</a>
                    </div>
                    <?php if($_SESSION['uid'] && !is_emp($_SESSION['role'])) {?>
                    <div class="b-check  b-check_inline-block b-check_padtop_10" >
                        <input class="b-check__input" type="checkbox" value="1" name="pf_my_specs" id="pf_my_specs" <?= ($filter['my_specs']=='t'?'checked="checked"':'');?> <?= (!$user_specs?"disabled title='У вас не выбраны специализации'":"")?>/> 
                        <label for="pf_my_specs" class="b-check__label" <?= (!$user_specs?"title='У вас нет выбранных специализаций'":"")?>>Смотреть только мои специализации</label>
                    </div>
                    <?php } //if?>
                    <br/>
                    <div class="flt-spec-list " id="pf_specs"> </div>
               </div>
          </div>
          <div class="flt-block c" >
                 <label class="flt-lbl">Месторасположение:</label>
                 <div class="flt-b-in" >
                      <div class="flt-b-row" style="padding-bottom:10px;" >
                           <span class="flt-prm">
                             <select  name="pf_country" id="pf_country" class="flt-p-sel" onChange="$('pf_country').set('disabled', 'disabled');xajax_GetCitysByCid(this.value);">
                                <option value="0">Все страны</option>
                                <?php foreach($countries as $country_id => $country_name) {?>
                                <option value="<?= $country_id?>" <?= ($country_id == $filter['country'])?'selected="selected"':'';?>><?= $country_name?></option>
                                <?php }?>
                             </select>
                           </span>
                      </div>
                      <div class="flt-b-row flt-b-lc">
                           <span id="frm_city" class="flt-prm">
                             <select name="pf_city" class="flt-p-sel">
                               <option value="0">Все города</option>
                               <?php if($cities) {?>
                                   <?php foreach($cities as $city_id=>$city_name) {?>
                                   <option value="<?=$city_id?>" <?= ($city_id == $filter['city'])?'selected="selected"':'';?>><?= $city_name?></option>
                                   <?php } //foreach?>
                               <?php }//if?>
                             </select>
                           </span>
                      </div>
                 </div>
            </div>
            <div class="b-buttons b-buttons_padbot_30 b-buttons_padtop_20">
                   <a class="b-button b-button_flat b-button_flat_grey" href="javascript:void(0)" onclick="setFilterBulletForSubmit(); $('search-action').set('value', 'search_advanced'); $('main-search-form').submit();">Найти с учетом параметров</a>
                   <span class="center_clear"><a href="javascript: void(0);" onclick="FilterClearForm()" class="flt-lnk">Очистить форму</a></span>
               </div>
</fieldset>
<script type="text/javascript">
FilterAddBullet(0,0,0,0);
function setFilterBulletForSubmit() {
    if($('pf_subcategory').value != 0) {
        FilterAddBullet(1, $('pf_subcategory').value, $('pf_subcategory').options[$('pf_subcategory').selectedIndex].text, $('pf_category').value);
    } else if($('pf_category').value != 0) {
        FilterAddBullet(0, $('pf_category').value, $('pf_category').options[$('pf_category').selectedIndex].text, 0);
    }
}
//Функция очистки полей фильтра
function FilterClearForm(f) {
  if(f == undefined) f = 'advanced-search';
  filter_bullets = new Array();
  filter_bullets[0] = new Array();
  filter_bullets[1] = new Array();
  $('pf_cost_from').value = '';
  $('pf_cost_to').value = '';
  $('pf_wo_budjet').checked = false;
  //$('pf_only_sbr').checked = false;
  if($('pf_my_specs') != undefined) $('pf_my_specs').checked = false;
  $('pf_specs').innerHTML = '';
  $('pf_country').value = 0;
  $('pf_category').value=0;
  $('pf_currency').getElements('option').set('selected', '');
  $('pf_currency').getElement('option[value=2]').set('selected', 'selected');
  FilterSubCategory($('pf_category').value);
  FilterCityUpd(0);
}

//Функция обновления гордов в фильтре через ajax
function FilterCityUpd(v) {
    if($("main-search-form").pf_city != undefined) {
        ct = $("main-search-form").pf_city;
    } else {
        ct = $("main-search-form").city;

    }
  ct.disabled = true;
  ct.options[0].innerHTML = "Подождите...";
  ct.value = 0;
  xajax_GetCitysByCid(v);
}

</script>
                