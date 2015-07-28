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
$subcategories = professions::GetAllProfessions(1);
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

//создаем массив специализаций (для фильтра на главной он уже есть в $prfs, для фильтра в проектах фрилансера его нет, поэтому делаем проверку на существование
if (!sizeof($profs)) {
    $all_specs = professions::GetAllProfessions("", 0, 1);
} else {
    $all_specs = $profs;
}


?>
<script type="text/javascript">
<?php if($filter['success_sbr'][0] == 1 || $filter['success_sbr'][1] == 1 || $filter['success_sbr'][2] == 1 || $filter['success_sbr'][3] == 1) { ?>
var vsbr = 1;
<?php } else { //if?>
var vsbr = 0;
<?php } //else ?>


function FilterCatalogAddCategoryType() {
  if ($('pf_subcategory').value == 0) {
    //добавляем категорию
   if(Number($('pf_category').value) > 0) FilterAddBullet(0, $('pf_category').value, $('pf_category').options[$('pf_category').selectedIndex].text, 0);
  }
  else {
    //добавляем подкатегорию
    if(Number($('pf_category').value) > 0) FilterAddBullet(1, $('pf_subcategory').value, $('pf_subcategory').options[$('pf_subcategory').selectedIndex].text, $('pf_category').value);
  }
}

//1 = фильтр проектов
//2 = фильтр фрилансеров
var curFBulletsBox = 2;

var maxCostBlock = 12;
var filter_user_specs = new Array();
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

    if ($all_specs[$i+1]['groupid'] != $spec_now) {echo "];";}
    else {echo ",";}
}

$spec_now = 0;
for ($i=0; $i<sizeof($all_specs); $i++) {
    if ($all_specs[$i]['groupid'] != $spec_now) {
        $spec_now = $all_specs[$i]['groupid'];
        echo "filter_specs_ids[".$all_specs[$i]['groupid']."]={";
    }
    echo "".$all_specs[$i]['id'].":1";
    if ($all_specs[$i+1]['groupid'] != $spec_now) {echo "};";}
    else {echo ",";}
}

$cost_type = array(1 => "За месяц", 2 => "За 1000 знаков", 3 => "За Проект", 4 => "За час");
$curr_type = array('2' => "Руб", '0' => "USD", '1' => "Euro");
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/freelancers_filter.php";?>
var filter_mirror_specs = <?=freelancers_filters::getMirroredSpecsJsObject($all_mirrored_specs); ?>; 
var filter_bullets = [[],[]];

<?
if (sizeof($gFilter)) {
  for ($ci=0; $ci<2; $ci++) {
    $ph_categories[$ci] = array();
    if (sizeof($gFilter[$ci])) {
      foreach ($gFilter[$ci] as $fkey => $fvalue) {
          $fvalue = intval($fvalue);
          $fkey = intval($fkey);
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


function FilterCatalogClearForm()
{
    var elm,form = document.forms["main-search-form"];
    for(var i = 0; i<form.elements.length; i++) {
        elm=form.elements[i];
        if(elm.type == 'checkbox') {
            elm.checked=false;
            continue;
        }
        if(elm.type != "button" && elm.type != "submit" && elm.tagName != 'SELECT' && elm.type != "hidden" && elm.name != 'search_string') elm.value = "";
    }
    
    if(document.getElementById("pf_specs") != undefined) {
        filter_bullets = new Array();
        filter_bullets[0] = new Array();
        filter_bullets[1] = new Array();
        if($('pf_category')) {
            $('pf_category').value = 0;
            FilterSubCategory($('pf_category').value);
        }
        $('pf_specs').innerHTML = "";
    }
    
    $('cost_box').getElements("select").each(function(e){
        if (e.get('name') === 'curr_type[]') {
            e.options[2].selected = true; 
        } else {
            e.options[0].selected = true; 
        }
    
    });
    
    xajax_GetCitysByCid(0);
    
    cc = form.pf_country;
    
    cc.options[0].selected = true;
    
    return true;
}


var cost_count = 0;

function addCost(o, inp, def) {
     
    if((!o || !o.tagName) && this.tagName)
        o = this;
    cost_count += 1;
    if(o) add2del(o);
    if(inp == undefined) inp = {ct:1, fc:'', tc:'', cut:0};
    if(def == undefined) def = 0;
    var bSpan = document.createElement("span");
    
    if(cost_count < maxCostBlock && def == 0) {
        bSpan.className = "flt-add";
        bSpan.onclick=addCost;
        bSpan.innerHTML = '<span class="flt-spec"><span class="flt-s-in"><a href="javascript: void(0);"><img src="/images/flt-add.png" alt="" width="15" height="15" /><span>Добавить еще</span></a></span></span>';
    } else {
        bSpan.className = "flt-remove";
        bSpan.onclick=delCost;
        bSpan.innerHTML = '<span class="flt-spec"><span class="flt-s-in"><a href="javascript: void(0);"><img src="/images/flt-close.png" alt="" width="15" height="15" /><span>Убрать</span></a></span></span>';
    }
    
    var mSpan = document.createElement("span");
    mSpan.className = "flt-prm8";
    
    hBox = '<span class="flt-prm9"> <select name="cost_type[]">';
    <? foreach($cost_type as $k=>$v): ?>
    if(<?=$k?> == inp.ct) { var s = 'selected';}
    else {var s = '';}
    hBox += '<option value="<?=$k?>" '+s+'><?=$v?></option>';
    <? endforeach; ?>
    hBox += '</select> <input type="text" size="10" maxlength="6" name="from_cost[]" class="" value="'+inp.fc+'"/></span><span class="b-page__desktop b-page__ipad">&nbsp;&mdash;&nbsp;</span> ';
    hBox += '<span class="flt-prm10"><input type="text" maxlength="6" name="to_cost[]" size="10" class="" value="'+inp.tc+'" />&nbsp;';
    hBox += '<select name="curr_type[]">';
    <? foreach($curr_type as $k=>$v): ?>
    if(<?=$k?> == inp.cut) { var s = 'selected';}
    else {var s = '';}
    hBox += '<option value="<?=$k?>" '+s+'><?=$v?></option>';
    <? endforeach; ?>
    hBox += '</select></span></span>';
    
    mSpan.innerHTML = hBox;
    
    document.getElementById("cost_box").appendChild(bSpan);
    document.getElementById("cost_box").appendChild(mSpan);
    
    return bSpan;
}

function delCost(o) {
    if((!o || !o.tagName) && this.tagName)
        o = this;
    cost_count -=1;
    var box = document.getElementById("cost_box");
    var r = o.nextSibling;
    if(r != null && box.childNodes.length >1) {
        box.removeChild(r);  
        box.removeChild(o);          
    }
    
    if(cost_count == maxCostBlock-1) {
        var c = box.childNodes;
        add2del(c[c.length-2]);
    }
}

function add2del(o) {
    var a = o.childNodes[0].childNodes[0].childNodes[0]; // Ссылка
    var i = a.childNodes[0]; // Картинка
    var t = a.childNodes[1]; // Текст
    
    if(o.className == "flt-add") { 
        o.onclick = delCost;
        o.className = "flt-remove";
        a.href = "javascript: void(1)";
        i.src = "/images/flt-close.png";
        t.innerHTML = "Убрать";
    } else {
        o.onclick = addCost;
        o.className = "flt-add";
        a.href = "javascript: void(0)";
        i.src = "/images/flt-add.png";
        t.innerHTML = "Добавить еще";
    }                        
}

</script>

<fieldset class="flt-cnt flt-usr" id="advanced-search" style="display:<?= $is_show_adv?"block":"none";?>; padding:0 15px;">

    <div class="flt-block c">
        <label class="flt-lbl">Специализации:</label>
        <div class="flt-b-in">
            <span class="flt-add">
                <span class="flt-spec">
                    <span class="flt-s-in"><a href="javascript: void(0);" onclick="FilterCatalogAddCategoryType();"><img width="15" height="15" src="/images/flt-add.png" alt="" /><span>Добавить еще</span></a></span>
                </span>
            </span>
            <div class="flt-b-row">
                <span class="flt-prm">
                    <select class="flt-p-sel" name="pf_category" id="pf_category" onChange="FilterSubCategory(this.value)">
                        <option value="0">Все разделы</option>
                        <?php foreach($categories as $category) { if($category['id']<=0) continue; ?>
                        <option value="<?=$category['id']?>"><?=$category['name']?></option>
                        <?php } //foreach ?>
                    </select>
                </span>
                <span class="flt-prm" >
                    <select class="flt-p-sel" name="pf_subcategory" id="pf_subcategory"  disabled="disabled">
                        <option value="0">Все подкатегории</option>
                    </select>
                </span>
            </div>
            <div class="flt-spec-list" id="pf_specs"></div>
        </div>
    </div>
    <?/*<div class="flt-block c">
        <label class="flt-lbl">Ключевые слова:</label>
        <div class="flt-b-in" >
            <span class="flt-prm6">
                <textarea  class="flt-prm7" cols="10" name="kword" rows="2"><?= stripslashes($filter['kwords'])?></textarea>
            </span>
            <a class="lnk-dot-blue" href="/help/?q=948">Помощь</a>
            <br /><br /><br />Ключевые слова вводятся через запятую.
        </div>
    </div>*/?>
    <div class="flt-block flt-block-btmp c">
        <label class="flt-lbl">Стоимость:</label>
        <div id="cost_box" class="flt-b-in" ></div>
        <script type="text/javascript">
        <?php if($cFilter) { ?>
            <?php foreach($cFilter as $c=>$v) { ?>
                <?php if($c==0) { ?>
                var b = addCost(undefined, {ct:<?=$v['type_date']?>, fc:<?=($v['cost_from']==0?"''":$v['cost_from'])?>, tc:<?=($v['cost_to']==0?"''":$v['cost_to'])?>, cut:<?=$v['cost_type']==0?"''":$v['cost_type']?>}, <?=count($cFilter)>1?1:0?>);
                <?php } else { //if?>
                var m = addCost(b, {ct:<?=$v['type_date']?>, fc:<?=($v['cost_from']==0?"''":$v['cost_from'])?>, tc:<?=($v['cost_to']==0?"''":$v['cost_to'])?>, cut:<?=$v['cost_type']==0?"''":$v['cost_type']?>});
                <?php } //else?>
                
                <?php if(count($cFilter)>1) { ?>
                b = m;
                <?php }//if?>
            <?php } //foreach ?>
        <?php } else { // if ?>
            addCost(); 
        <?php } //else ?>
        </script>
    </div>
    <div class="flt-block c" >
        <label class="flt-lbl">Опыт работы:</label>
        <div class="flt-b-in" >
            <span class="flt-prm11">
                <span class="flt-prm">
                    <input type="text" class="flt-prm3" maxlength="3" value="<?= $filter['exp_from'] == 0 ? '' : $filter['exp_from']?>" name="exp[]" size="10" /> &mdash; 
                    <input type="text" class="flt-prm3" maxlength="3" value="<?= $filter['exp_to']   == 0 ? '' : $filter['exp_to']?>" name="exp[]" size="10" />&nbsp; лет
                </span>
                <span class="flt-prm">Возраст: 
                   <span class="age_block">
                        <input type="text" class="flt-prm3" value="<?= $filter['age_from'] == 0 ? '' : $filter['age_from']?>" name="age[]" maxlength="3" size="10" /> &mdash; 
                        <input type="text" class="flt-prm3" value="<?= $filter['age_to']   == 0 ? '' : $filter['age_to']?>" name="age[]" maxlength="3" size="10" />&nbsp; лет
                   </span>
                 </span>
             </span>
        </div>
    </div>
    <div class="flt-block c" >
        <label class="flt-lbl">Месторасположение:</label>
        <div class="flt-b-in" >
            <div class="flt-b-row" >
                <span class="flt-prm">
                    <select onchange="xajax_GetCitysByCid(this.value);" name="pf_country" id="pf_country" class="flt-p-sel">
                        <option value="0">Все страны</option>
                        <?php foreach ($countries as $country_id => $country_name) { ?>
                        <option value="<?= $country_id?>" <?= $country_id == $filter['country']?'selected="selected"':'';?>><?= $country_name?></option>
                        <?php } //foreach ?>
                    </select>
                </span>
                <span id="frm_city" class="flt-prm">
                    <select name="pf_city" class="flt-p-sel">
                        <option value="0">Все города</option>
                        <?php if($cities) {?>
                            <?php foreach ($cities as $city_id => $city_name) { ?>
                            <option value="<?= $city_id?>" <?= $city_id == $filter['city'] ? 'selected=" selected"':'';?>><?= $city_name?></option>
                            <?php } //foreach ?>
                        <?php } //if?>
                    </select>
                </span>
            </div>
            <div class="b-check b-check_padtop_10" >
               <input id="in_office" type="checkbox" value="1" name="in_office" class="b-check__input" <?= ($filter['in_office'] ? 'checked="checked"' : '');?> />
               <label for="in_office" class="b-check__label">Ищет работу в офисе</label>
            </div>
        </div>
    </div>
    
    <div class="flt-block c" >
        <label class="flt-lbl">Дополнительно:</label>
        <div class="flt-b-in" >
            <ul class="flt-more c">
                <?php if($_SESSION['uid']) {?>
                   <li>
                      <div class="b-check">
                          <input id="in_fav" class="b-check__input" type="checkbox" name="in_fav" value="1" <?=    ($filter['in_fav']    ? 'checked="checked"' : '')?> /> 
                          <label for="in_fav" class="b-check__label" for="in_fav">У меня в избранных</label>
                      </div>
                   </li> 
																<?php }//if?>
                <li>
                   <div class="b-check">
                       <input id="only_free" class="b-check__input" type="checkbox" name="only_free" value="1" <?= ($filter['only_free'] ? 'checked="checked"' : '')?> /> 
                       <label for="only_free" class="b-check__label" for="only_free">Только свободные</label>
                   </div>
                </li>
                <li class="flt-more-b">
                   <div class="b-check">
                       <input id="is_pro" class="b-check__input" type="checkbox" name="is_pro" value="1" <?= ($filter['is_pro'] ?'checked="checked"':'')?> /> 
                       <label for="is_pro" class="b-check__label" for="is_pro">С <a class="b-layout__link" href="../payed/"><span class="b-icon b-icon__pro b-icon__pro_f" title="Платный аккаунт" alt="Платный аккаунт"></span></a> аккаунтом</label>
                   </div>
                </li>
                <li class="flt-more-b">
                   <div class="b-check">
                      <input id="is_verify" class="b-check__input" type="checkbox" name="is_verify" value="1" <?= ($filter['is_verify'] ?'checked="checked"':'')?> /> 
                      <label for="is_verify" class="b-check__label" for="is_verify">С <span class="b-icon b-icon__ver" title="верифицированым" alt="верифицированым"></span> аккаунтом</label>
                   </div>
                </li>
                <li>
                   <div class="b-check">
                      <input id="is_preview" class="b-check__input" type="checkbox" name="is_preview" value="1" <?=   ($filter['is_preview']   ? 'checked="checked"' : '')?> /> 
                      <label for="is_preview" class="b-check__label" for="is_preview">Только с примерами работ</label>
                   </div>
                </li>
                <li>
                   <div class="b-check">
                       <input id="sbr_is_positive" class="b-check__input" type="checkbox" name="sbr_is_positive" value="1" <?=($filter['sbr_is_positive']?'checked="checked"':'')?>/> 
                       <label for="sbr_is_positive" class="b-check__label" for="sbr_is_positive">С положительными рекомендациями</label>
                   </div>
                </li>
                <li>
                   <div class="b-check">
                       <input id="sbr_not_negative" class="b-check__input" type="checkbox" name="sbr_not_negative" value="1" <?=($filter['sbr_not_negative']?'checked="checked"':'')?>/> 
                       <label for="sbr_not_negative" class="b-check__label" for="sbr_not_negative">Без отрицательных рекомендаций</label>
                   </div>
                </li>
                <?/*<li><label><input class="i-chk" type="checkbox" name="opi_is_positive" value="1" <?=($filter['opi_is_positive']?'checked="checked"':'')?>/> С положительными мнениями</label></li>
                <li><label><input class="i-chk" type="checkbox" name="opi_not_negative" value="1" <?=($filter['opi_not_negative']?'checked="checked"':'')?>/> Без отрицательных мнений</label></li>*/?>
            </ul>
        </div>
    </div>
            <div class="b-buttons b-buttons_padbot_30 b-buttons_padtop_20">
                <a class="b-button b-button_flat b-button_flat_grey" href="javascript:void(0)" onclick="FilterCatalogAddCategoryType(); $('search-action').set('value', 'search_advanced'); $('main-search-form').submit();">Найти с учетом параметров</a><span class="center_clear"><a class="flt-lnk"  href="javascript: void(0);" onclick="FilterCatalogClearForm()">Очистить форму</a></span>
            </div>
   
</fieldset>   
<script type="text/javascript">
FilterAddBullet(0,0,0,0);
</script>