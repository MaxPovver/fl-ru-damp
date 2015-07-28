<?

    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
    
    $filter_categories = professions::GetAllGroupsLite(TRUE);
    $filter_subcategories = professions::GetAllProfessions(1);
    $filter_countries = country::GetCountries();
    if ($mFilter['country']) {$filter_cities = city::GetCities($mFilter['country']);}
    
    //
    $all_mirrored_specs = professions::GetAllMirroredProfsId();
    $mirrored_specs = array();
    for ($is=0; $is<sizeof($all_mirrored_specs); $is++) {
        $mirrored_specs[$all_mirrored_specs[$is]['main_prof']] = $all_mirrored_specs[$is]['mirror_prof'];
        $mirrored_specs[$all_mirrored_specs[$is]['mirror_prof']] = $all_mirrored_specs[$is]['main_prof'];
    }

    switch($filter_page) {
    case 1:
      $frm_action = '/proj/?p=list';
      $prmd='&amp;';
      $has_hidd = FALSE;
      break;
    default:
      $frm_action = '/freelancers/';
      if(!$prof_id) $prmd='?';
      else $prmd = "{$prof_link}/".($f_country_lnk ? $f_country_lnk.'/' : '').($f_city_lnk ? $f_city_lnk.'/' : '')."?";
    }
    
    //создаем массив специализаций (для фильтра на главной он уже есть в $prfs, для фильтра в проектах фрилансера его нет, поэтому делаем проверку на существование
    if (!sizeof($profs)) {$all_specs = professions::GetAllProfessions("", 0, 1);}
    else                 {$all_specs = $profs;}
    
    $rank = freelancers_filters::getRankCount($prof_id);
    
?>
<script language="javascript" type="text/javascript">


/*   
window.onload = function() {
    var KeyWord = __key(1);
    KeyWord.bind(document.getElementById('se'), kword, {bodybox:"body_1"});
    
   
}*/
<? if($mFilter['success_sbr'][0]==1 || $mFilter['success_sbr'][1]==1 || $mFilter['success_sbr'][2]==1 || $mFilter['success_sbr'][3]==1): ?>
var vsbr = 1;
<? else:?>
var vsbr = 0;
<? endif; ?>


function FilterCatalogAddCategoryType()
{
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

$cost_type = array(1 => "За месяц", 2 => "За 1000 знаков", 3 => "За Проект", 4 => "За час");
//$curr_type = array("Руб", "USD", "Euro");
$curr_type = array(
    array('name' => 'Руб', 'value' => 2),
    array('name' => 'USD', 'value' => 0),
    array('name' => 'Euro', 'value' => 1),
);
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


function FilterCatalogClearForm()
{
    var elm,form = document.forms["f_filter"];
    for(var i = 0; i<form.elements.length; i++) {
        elm=form.elements[i];
        if(elm.type == 'checkbox') {
            elm.checked=false;
            continue;
        }
        if(elm.type != "button" && elm.type != "submit" && elm.tagName != 'SELECT' && elm.type != "hidden") elm.value = "";
    }
    
    $('flt-cat').getElements('div').setStyle('height', 'auto');
    
    if(document.getElementById("pf_specs") != undefined) {
        filter_bullets = new Array();
        filter_bullets[0] = new Array();
        filter_bullets[1] = new Array();
        if($('pf_category')) FilterSubCategory($('pf_category').value);
        $('pf_specs').innerHTML = "";
    }
    $('cost_box').getElements("select").each(function(e){
        if (e.get('name') == 'curr_type[]') { // сбрасываем валюту в рубли
            e.options[2].selected = true;
        } else {
            e.options[0].selected = true;
        }
    });

    ComboboxManager.getInput('pf_country').reload("");        
    cc = $("frm").pf_country;    
    if (cc.options) {
        cc.options[0].selected = true;
    }
    
    return true;
}


var cost_count = 0;

function addCost(o, inp, def) {
    var flt_cat_cnt_box = $('flt-cat').getElement('.flt-cnt').getParent();
    flt_cat_cnt_box.setStyle('height', 'auto');
     
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
    hBox += '</select> <input type="text" size="10" maxlength="6" name="from_cost[]" class="" value="'+inp.fc+'"/></span>&nbsp;&mdash;&nbsp; ';
    hBox += '<span class="flt-prm10"><input type="text" maxlength="6" name="to_cost[]" size="10" class="" value="'+inp.tc+'" />&nbsp;';
    hBox += '<select name="curr_type[]">';
    <? foreach ($curr_type as $k=>$v) { ?>
        var s = <?=$v['value']?> == inp.cut ? 'selected' : '';
        hBox += '<option value="<?=$v['value']?>" '+s+'><?=$v['name']?></option>';
    <? } ?>
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
var IE='\v'=='v';
window.addEvent('domready', function() {
    var cntr = $('flt-cat').getElement('.flt-cnt').getParent();
    var ch = cntr.getSize().y;
    if (!Browser.ie) {
        cntr.setStyle('height', 'auto');
    } else {
        cntr.setStyle('height', '');
    }
});
</script>

<?php
$filter_action = '/freelancers/'.($prof_link? "{$prof_link}/": ($prof_id? '?prof='.$prof_id: ''));

if ($show_all_freelancers) {
  $filter_action .= strpos($filter_action, '?') !== FALSE?'&show=all':'?show=all';
}
?>

<form id="frm" onsubmit="return false;" name="f_filter" action="<?=$filter_action?>" method="POST">
        <div class="flt-out <?=(($filter_show)?"flt-show":"flt-hide")?>" id="flt-cat" page="<?=$filter_page?>">
            <input type="hidden" name="action" value="postfilter" />
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="flt-bar">
                 <a href="javascript: void(0);" class="flt-tgl-lnk"><?=(($filter_show)?"Свернуть":"Развернуть")?></a>
                 <h3>Фильтр 
                 <? if($filter_apply): ?>
                    <span class="flt-stat flt-on">включен&nbsp;&nbsp;&nbsp;<a href="<?=$frm_action?><?=$prmd?>action=deletefilter<?=$filter_query?>" onClick="_gaq.push(['_trackEvent', 'User', '<?=(is_emp() ? 'Employer' : (get_uid(false) ? 'Freelance' : 'Unauthorized'))?>', 'button_filter_delete_catalog']); ga('send', 'event', '<?=(is_emp() ? 'Employer' : (get_uid(false) ? 'Freelance' : 'Unauthorized'))?>', 'button_filter_delete_catalog');" class="flt-lnk">отключить</a></span>
                 <? else: ?>
                    <span class="flt-stat flt-off">отключен&nbsp;&nbsp;&nbsp;<a href="<?=$frm_action?><?=$prmd?>action=activatefilter<?=$filter_query?>" onClick="_gaq.push(['_trackEvent', 'User', '<?=(is_emp() ? 'Employer' : (get_uid(false) ? 'Freelance' : 'Unauthorized'))?>', 'button_filter_include_catalog']); ga('send', 'event', '<?=(is_emp() ? 'Employer' : (get_uid(false) ? 'Freelance' : 'Unauthorized'))?>', 'button_filter_include_catalog');" class="flt-lnk">включить</a></span>
                 <? endif; ?>
								 </h3>
                 <?/* <span class="flt-stat flt-off">отключен&nbsp;&nbsp;&nbsp;<a href="" class="flt-lnk">включить</a></span> <!-- <span class="flt-stat flt-on">включен&nbsp;&nbsp;&nbsp;<a href="" class="flt-lnk">отключить</a></span> --> */ ?>
            </div>
            <div class="flt-cnt">

<? if (!$prof_id &&!$prof_group_id): ?>
               <div class="flt-block flt-b-fc">
                    <label class="flt-lbl">Специализации:</label>
                    <div class="flt-b-in">
                         <span class="flt-add" ><span class="flt-spec"><span class="flt-s-in"><a href="javascript: void(0);" onclick="FilterCatalogAddCategoryType();"><img src="/images/flt-add.png" alt="" width="15" height="15" />Добавить еще</a></span></span></span>
                         <div class="flt-b-row">
                             <span class="flt-prm">
                               <select class="flt-p-sel" name="pf_category" id="pf_category" onChange="FilterSubCategory(this.value)">
                                   <option value="0">Все разделы</option>
                                 <? foreach($filter_categories as $cat) { if($cat['id']<=0) continue; ?>
                                 <option value="<?=$cat['id']?>"><?=$cat['name']?></option>
                                 <? } ?>
                               </select>
                             </span>
                             <span class="flt-prm" id="frm_subcategory">
                               <select class="flt-p-sel" name="pf_subcategory" id="pf_subcategory" disabled="disabled">
                                 <option value="0">Все подкатегории</option>
                                 <? if(false) for ($i=0; $i<sizeof($filter_subcategories); $i++) { ?>
                                 <option value="<?=$filter_subcategories[$i]['id']?>"><?=$filter_subcategories[$i]['profname']?></option>
                                 <? } ?>
                               </select>
                             </span>
                         </div>
                         <div class="flt-spec-list " id="pf_specs"></div>
                    </div>
                  </div>
<? else: ?>
<div class="flt-spec-list " id="pf_specs" style="display:none"></div>
<? endif; ?>
                  <div class="flt-block<?=(!$prof_id ? '' : ' flt-b-fc')?>">
                       <label class="flt-lbl">Ключевые слова:</label>
                       <div class="flt-b-in">
                            <div class="flt-prm6">
															<div class="b-input-hint">
                                <div style="position:relative;" id="body_1">
                                <textarea id="kword_se" class="flt-prm7" rows="2" name="kword" cols="10" value="<?=htmlspecialchars(stripslashes($mFilter['kwords']))?>"><?=htmlspecialchars(stripslashes($mFilter['kwords']))?></textarea>
                                
                                </div>
															</div>
                                
                            </div>
                           
                            <a href="https://feedback.fl.ru" class="lnk-dot-blue">Помощь</a>
                            <div style="clear:both">Ключевые слова вводятся через запятую.</div>
                       </div>
                        
                  </div>
                  <div class="flt-block flt-block-btmp">
                        
                       <label class="flt-lbl">Стоимость:</label>
                       <div class="flt-b-in" id="cost_box"></div>
                       
                       <script type="text/javascript"> 
                           <? if($cFilter): ?>
                           <? foreach($cFilter as $c=>$v): ?>
                           <?if($c==0):?>
                           var b = addCost(undefined, {ct:<?=$v['type_date']?>, fc:<?=($v['cost_from']==0?"''":$v['cost_from'])?>, tc:<?=($v['cost_to']==0?"''":$v['cost_to'])?>, cut:<?=$v['cost_type']?>}, <?=count($cFilter)>1?1:0?>);
                           <?else:?>
                           var m = addCost(b, {ct:<?=$v['type_date']?>, fc:<?=($v['cost_from']==0?"''":$v['cost_from'])?>, tc:<?=($v['cost_to']==0?"''":$v['cost_to'])?>, cut:<?=$v['cost_type']?>});
                           <?endif;?>
                           <?if(count($cFilter)>1):?>b = m;<?endif;?>
                           <? endforeach; ?>
                           <? else: ?>
                           addCost(); 
                           <? endif; ?>
                       </script>
                  </div>
                  <div class="flt-block">
                        <label class="flt-lbl">Опыт работы:</label>
                        <div class="flt-b-in">
                            <span class="flt-prm"><input class="flt-prm3" type="text" size="10" name="exp[]" value="<?=$mFilter['exp_from']==0?'':$mFilter['exp_from']?>" maxlength="3" /> &mdash; <input class="flt-prm3" type="text" size="10" maxlength="3" name="exp[]" value="<?=$mFilter['exp_to']==0?'':$mFilter['exp_to']?>" />&nbsp; лет</span>
                       </div>
                  </div>
                  <div class="flt-block">
                        <label class="flt-lbl">Имя или логин:</label>
                        <div class="flt-b-in">
                            <span class="flt-prm11">
                                <span class="flt-prm">Возраст: <input type="text" size="10" maxlength="3" name="age[]" value="<?=$mFilter['age_from']==0?'':$mFilter['age_from']?>" class="flt-prm3" /> &mdash; <input type="text" size="10" maxlength="3" name="age[]" value="<?=$mFilter['age_to']==0?'':$mFilter['age_to']?>" class="flt-prm3" />&nbsp; лет</span>
                                <input class="flt-prm4" type="text" size="10" name="login" value="<?=htmlspecialchars(stripslashes($mFilter['login']))?>" />
                            </span>
                        </div>
                  </div>
                  <div class="flt-block flt-block-occurrence">
                       <label class="flt-lbl">Месторасположение:</label>
                       <div class="flt-b-in">
                            <div class="flt-b-row">
                                 <span class="flt-prm">
                                    <div class="b-combo">
                                        <div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_150 b-combo__input_resize b-combo__input_arrow_yes  b-combo__input_init_citiesList b-combo__input_on_click_request_id_getcities b-combo__input_max-width_450 all_value_id_0_0_Все+страны all_value_id_1_0_Все+города <?=$locationId ?>">
                                            <input id="pf_country" class="b-combo__input-text" type="text"  size="80" value="<?=$location ?>"/>
                                            <span class="b-combo__arrow"></span>
                                        </div>
                                    </div>
                                 </span>
                            </div>
                            <div class="flt-b-row flt-b-row-mb">
                                <label><input class="i-chk" type="checkbox" name="in_office" value="1" <?=($mFilter['in_office']=='t'?'checked="checked"':'')?>/> Ищет работу в офисе</label>
                            </div>
                       </div>
                  </div>
                  <div class="flt-block">
                       <label class="flt-lbl">Дополнительно:</label>
                       <div class="flt-b-in">
                            <ul class="flt-more c">
                                <li><div class="b-check"><input class="b-check__input" type="checkbox" name="only_tu" value="1" <?=($mFilter['only_tu']=="t"?'checked="checked"':'')?>/> <label class="b-check__label b-check__label_bold">С Типовыми услугами</label></div></li>
                                 <? if(get_uid(false)) { ?>
                                 <li><div class="b-check"><input class="b-check__input" type="checkbox" name="in_fav" value="1" <?=($mFilter['in_fav']=="t"?'checked="checked"':'')?>/> <label class="b-check__label">У меня в избранных</label></div></li>
                                 <? } ?>
                                 <li><div class="b-check"><input class="b-check__input" type="checkbox" name="only_free" value="1" <?=($mFilter['only_free']=="t"?'checked="checked"':'')?>/> <label class="b-check__label">Только свободные</label></div></li>
                                 <li><div class="b-check"><input class="b-check__input" type="checkbox" name="is_pro" value="1" <?=($mFilter['is_pro']=="t"?'checked="checked"':'')?>/> <label class="b-check__label">С <?= view_pro('', false, true, 'платным аккаунтом')?> аккаунтом</label></div></li>
                                 <?/*<li class="flt-more-b"><div class="b-check"><input class="b-check__input" type="checkbox" name="only_online" value="1" <?=($mFilter['only_online']=="t"?'checked="checked"':'')?>/> <label class="b-check__label">Сейчас на сайте</label></div></li> */?>
                                 <li><div class="b-check"><input class="b-check__input" type="checkbox" name="is_preview" value="1" <?=($mFilter['is_preview']=="t"?'checked="checked"':'')?>/> <label class="b-check__label">Только с примерами работ</label></div></li>
                                 <li><div class="b-check"><input class="b-check__input" type="checkbox" name="is_verify" value="1" <?=($mFilter['is_verify']=="t"?'checked="checked"':'')?> /> <label class="b-check__label">С <?= view_verify('подтвержденными паспортными данными', '')?> аккаунтом</label></div></li>
                                 
                                 <li><div class="b-check"><input class="b-check__input" type="checkbox" name="sbr_is_positive" value="1" <?=($mFilter['sbr_is_positive']=="t"?'checked="checked"':'')?>/> <label class="b-check__label">С положительными рекомендациями</label></div></li>
                                <li><div class="b-check"><input class="b-check__input" type="checkbox" name="sbr_not_negative" value="1" <?=($mFilter['sbr_not_negative']=="t"?'checked="checked"':'')?>/> <label class="b-check__label">Без отрицательных рекомендаций</label></div></li>
                            </ul>
                           
                       </div>
                  </div>
                  <div class="flt-block flt-b-lc">
                       <label class="flt-lbl">&nbsp;</label>
                       <div class="flt-b-in">
                            <input class="i-btn" type="button" name="enable_filter" value="Применить фильтр" onclick="_gaq.push(['_trackEvent', 'User', '<?=(is_emp() ? 'Employer' : (get_uid(false) ? 'Freelance' : 'Unauthorized'))?>', 'button_filter_apply_catalog']); ga('send', 'event', '<?=(is_emp() ? 'Employer' : (get_uid(false) ? 'Freelance' : 'Unauthorized'))?>', 'button_filter_apply_catalog'); $('frm').submit()"/>&nbsp;&nbsp;&nbsp;<a href="javascript: void(0);" onclick="FilterCatalogClearForm()" class="flt-lnk">Очистить форму</a>
                       </div>
                  </div>
             </div>
              <b class="b2"></b>
              <b class="b1"></b>
        </div>
     </form>   
     <!-- Фильтр по ключевому слову --> 
     <? if($key_word) { ?>
	 <div class="cat-flt-key"> 
	    Просмотр результатов по ключевому слову: <strong><?=stripslashes(htmlspecialchars($key_word))?></strong><br /> 
	    <a href="/freelancers_new" class="lnk-dot-blue">Сбросить фильтр по ключевому слову</a> 
	 </div>
	 <? } ?> 
	 <!-- Конец фильтра по ключевому слову --> 
     <? if($hhf) { ?>
	 <div class="cat-flt-key"> 
	    Просмотр результатов по параметрам поиска HH.RU<br /> 
	    <a href="/freelancers_new" class="lnk-dot-blue">Сбросить</a> 
	 </div>
	 <? } ?> 
<script language="javascript">
FilterAddBullet(0,0,0,0);
</script>    
<?



?>
