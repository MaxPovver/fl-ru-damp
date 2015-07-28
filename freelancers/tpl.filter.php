<?

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");

$filter_categories = professions::GetAllGroupsLite(TRUE);
$filter_subcategories = professions::GetAllProfessions(1);
$filter_countries = country::GetCountries();
if ($mFilter['country']) {
    $filter_cities = city::GetCities($mFilter['country']);
}

//
$all_mirrored_specs = professions::GetAllMirroredProfsId();
$mirrored_specs = array();
for ($is = 0; $is < sizeof($all_mirrored_specs); $is++) {
    $mirrored_specs[$all_mirrored_specs[$is]['main_prof']] = $all_mirrored_specs[$is]['mirror_prof'];
    $mirrored_specs[$all_mirrored_specs[$is]['mirror_prof']] = $all_mirrored_specs[$is]['main_prof'];
}

switch ($filter_page) {
    case 1:
        $frm_action = '/proj/?p=list';
        $prmd = '&amp;';
        $has_hidd = FALSE;
        break;
    default:
        $frm_action = '/freelancers/';
        if (!$prof_id)
            $prmd = '?';
        else
            $prmd = "{$prof_link}/?";
}

//создаем массив специализаций (для фильтра на главной он уже есть в $prfs, для фильтра в проектах фрилансера его нет, поэтому делаем проверку на существование
if (!sizeof($profs)) {
    $all_specs = professions::GetAllProfessions("", 0, 1);
} else {
    $all_specs = $profs;
}

$rank = freelancers_filters::getRankCount($prof_id);

$cost_filter = null;

if ( $cFilter && is_array($cFilter) && count($cFilter) ) {
    foreach ($cFilter as $i => $c) {
        $cost_filter[$c['type_date']] = $c;
    }
}

$currencies = array(
    'USD',
    'Euro',
    'Руб',
    'FM'
);

$f_tgl = isset($_COOKIE['f_tgl2']) ? explode(',', $_COOKIE['f_tgl2']) : array();

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


function FilterCatalogAddCategoryType() {
    if ($('pf_subcategory').value == 0) {
        //добавляем категорию
        if(Number($('pf_category').value) > 0) {
            tl = $('pf_category').options[$('pf_category').selectedIndex].text;
            tlf = tl;
            if (tl.length > 28) {
                tl = tl.substr(0, 28) + '...';
            }
            FilterAddBullet(0, $('pf_category').value, tl, undefined, tlf);
        }
    }
    else {
        //добавляем подкатегорию
        if(Number($('pf_category').value) > 0) {
            tl = $('pf_subcategory').options[$('pf_subcategory').selectedIndex].text;
            tlf = tl;
            if (tl.length > 28) {
                tl = tl.substr(0, 28) + '...';
            }
            FilterAddBullet(1, $('pf_subcategory').value, tl, $('pf_category').value, tlf);
        }
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
$curr_type = array("USD", "Euro", "Руб", "FM");
?>
var filter_mirror_specs = {<?
for ($i=0; $i<count($all_mirrored_specs),$ms=$all_mirrored_specs[$i]; $i++)
    print(($i?',':'').$ms['mirror_prof'].':'.$ms['main_prof'].','.$ms['main_prof'].':'.$ms['mirror_prof']);
?>};
var filter_bullets = [[],[]];

<?
if (sizeof($gFilter)) {
  for ($ci=0; $ci<2; $ci++) {
    $ph_categories[$ci] = array();
    if (sizeof($gFilter[$ci])) {
      foreach ($gFilter[$ci] as $fkey => $fvalue) {
       if ($fkey) {
        if (!in_array($mirrored_specs[$fkey], $ph_categories[$ci]))
        {
          if (!$fvalue)
          {
            $proftitle = professions::GetGroup($fkey, $error);
            $proftitle = $proftitle['name'];
          } else {
            $proftitle = professions::GetProfName($fkey);
            $prof_group = professions::GetProfField($fkey, 'prof_group');
          }
          $proftitle_full = $proftitle;
          $proftitle = LenghtFormatEx($proftitle, 28, '...', 1);

?>
filter_bullets[<?=$fvalue?>][<?=$fkey?>] = new Array();
filter_bullets[<?=$fvalue?>][<?=$fkey?>]['type'] = <?=$fvalue?>;
filter_bullets[<?=$fvalue?>][<?=$fkey?>]['title'] = '<?=$proftitle?>';
filter_bullets[<?=$fvalue?>][<?=$fkey?>]['title_full'] = '<?=$proftitle_full?>';
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
    
//    if ($('flt-cat')) $('flt-cat').getElements('div').setStyle('height', 'auto');
    
    if(document.getElementById("pf_specs") != undefined) {
        filter_bullets = new Array();
        filter_bullets[0] = new Array();
        filter_bullets[1] = new Array();
        if($('pf_category')) FilterSubCategory($('pf_category').value);
        $('pf_specs').innerHTML = "";
    }
    
    FilterCityUpd(0);
    
    $('cost_box').getElements("select").each(function(e){
       e.options[0].selected = true; 
    });
    
    cc = $("frm").pf_country;
    
    cc.options[0].selected = true;
    
    ss = form.getElement('select[name=sex]');
    if (ss) {
        ss.options[0].selected = true;
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

<form id="frm" onsubmit="" name="f_filter" action="/freelancers/<?= ($prof_link ? "{$prof_link}/" : ($prof_id ? '?prof=' . $prof_id : '')) ?>" method="POST">
    <input type="hidden" name="action" value="postfilter"/>
    <div class="form cat-flt <?= in_array('all', $f_tgl) ? 'cat-flt-hidden' : '' ?>" id="frl-filters">
        <b class="b1"></b>
        <b class="b2"></b>
        <div class="form-in">
            <div class="form-h">
                <a href="javascript:;" onclick="frlFiltersToggle(this)" id="frlFiltersToggle" class="lnk-dot-blue">Поиск по каталогу</a>
            </div>
            <div class="form-b">
                <div class="form-block first">
                    <div class="form-el">
                        <div class="cat-flt-str" id="body_1" style="position: relative;">
                            <input type="text" id="kword_se" name="kword" value="<?= htmlspecialchars($mFilter['kwords'], ENT_QUOTES, 'cp1251') ?>" />
                        </div>
                        <ul class="form-list">
                            <?php if(false){ ?>
                            <li><label><input type="checkbox" class="i-chk" /> Рекомендации работодателей</label></li>
                            <?php } ?>
                            <li><label><input type="checkbox" class="i-chk" name="is_pro" value="1" <?=($mFilter['is_pro']=="t"?'checked="true"':'')?> /> C <a href="/payed/"><span class="b-icon__pro b-icon__pro_f"></span></a> аккаунтом</label></li>
                            <li><label><input type="checkbox" class="i-chk" name="success_sbr[0]" value="1" <?=($mFilter['success_sbr'][0]==1?'checked="true"':'')?> /> С успешными <img src="/images/ico-sbr.png" alt="" /> <a href="/norisk2/" target="_blank">«Безопасными Сделками»</a></label></li>
                        </ul>
                        <? if (!$prof_id) { ?>
                        <div class="b-spec">
                            <label class="form-l"><strong>Специализация</strong></label>
                            <div class="f-sel">
                               <select class="flt-p-sel" name="pf_category" id="pf_category" onChange="FilterSubCategory(this.value)">
                                   <option value="0">Все разделы</option>
                                 <? foreach($filter_categories as $cat) { if($cat['id']<=0) continue; ?>
                                 <option value="<?=$cat['id']?>"><?=$cat['name']?></option>
                                 <? } ?>
                               </select>
                            </div>
                            <div class="f-sel" id="frm_subcategory">
                               <select class="flt-p-sel" name="pf_subcategory" id="pf_subcategory" disabled="disabled">
                                 <option value="0">Все подкатегории</option>
                                 <? if(false) for ($i=0; $i<sizeof($filter_subcategories); $i++) { ?>
                                 <option value="<?=$filter_subcategories[$i]['id']?>"><?=$filter_subcategories[$i]['profname']?></option>
                                 <? } ?>
                               </select>
                            </div>
                            <a href="javascript: void(0);" onclick="FilterCatalogAddCategoryType();" class="lnk-dot-666">Добавить еще</a>
                            <div class="spec-list c" id="pf_specs"></div>
                        </div>
                        <? } else { ?>
                        <div class="spec-list c" id="pf_specs" style="display:none"></div>
                        <? } ?>
                    </div>
                </div>
				<div class = "c hidden-cl"></div>
                <div class="form-block last">
                    <div class="form-el">
                        <div class="f-tgl">
                            <a href="" class="lnk-dot-blue">Стоимость работы за месяц</a>
                        </div>
                        <div>
                            <input type="hidden" name="cost_type[]" value="1" />
                            <input type="text" name="from_cost[]" value="<?= isset($cost_filter[1]) ? $cost_filter[1]['cost_from'] : '' ?>" class="w65" />
                            &mdash;
                            <input type="text" name="to_cost[]" value="<?= isset($cost_filter[1]) ? $cost_filter[1]['cost_to'] : '' ?>" class="w65" />
                            <select name="curr_type[]">
                                <? foreach ($currencies as $i => $row) { ?>
                                <option value="<?= $i ?>" <?= isset($cost_filter[1]) && $cost_filter[1]['cost_type'] == $i ? 'selected' : '' ?>><?= $row ?></option>
                                <? } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-el">
                        <div class="f-tgl">
                            <a href="" class="lnk-dot-blue">Стоимость работы за 1000 знаков</a>
                        </div>
                        <div>
                            <input type="hidden" name="cost_type[]" value="2" />
                            <input type="text" name="from_cost[]" value="<?= isset($cost_filter[2]) ? $cost_filter[2]['cost_from'] : '' ?>" class="w65" />
                            &mdash;
                            <input type="text" name="to_cost[]" value="<?= isset($cost_filter[2]) ? $cost_filter[2]['cost_to'] : '' ?>" class="w65" />
                            <select name="curr_type[]">
                                <? foreach ($currencies as $i => $row) { ?>
                                <option value="<?= $i ?>" <?= isset($cost_filter[2]) && $cost_filter[2]['cost_type'] == $i ? 'selected' : '' ?>><?= $row ?></option>
                                <? } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-el">
                        <div class="f-tgl">
                            <a href="" class="lnk-dot-blue">Стоимость работы за проект</a>
                        </div>
                        <div>
                            <input type="hidden" name="cost_type[]" value="3" />
                            <input type="text" name="from_cost[]" value="<?= isset($cost_filter[3]) ? $cost_filter[3]['cost_from'] : '' ?>" class="w65" />
                            &mdash;
                            <input type="text" name="to_cost[]" value="<?= isset($cost_filter[3]) ? $cost_filter[3]['cost_to'] : '' ?>" class="w65" />
                            <select name="curr_type[]">
                                <? foreach ($currencies as $i => $row) { ?>
                                <option value="<?= $i ?>" <?= isset($cost_filter[3]) && $cost_filter[3]['cost_type'] == $i ? 'selected' : '' ?>><?= $row ?></option>
                                <? } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-el">
                        <div class="f-tgl">
                            <a href="" class="lnk-dot-blue">Стоимость работы за час</a>
                        </div>
                        <div>
                            <input type="hidden" name="cost_type[]" value="4" />
                            <input type="text" name="from_cost[]" value="<?= isset($cost_filter[4]) ? $cost_filter[4]['cost_from'] : '' ?>" class="w65" />
                            &mdash;
                            <input type="text" name="to_cost[]" value="<?= isset($cost_filter[4]) ? $cost_filter[4]['cost_to'] : '' ?>" class="w65" />
                            <select name="curr_type[]">
                                <? foreach ($currencies as $i => $row) { ?>
                                <option value="<?= $i ?>" <?= isset($cost_filter[4]) && $cost_filter[4]['cost_type'] == $i ? 'selected' : '' ?>><?= $row ?></option>
                                <? } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-el">
                        <div class="f-tgl">
                            <a href="" class="lnk-dot-blue">Опыт работы</a>
                        </div>
                        <div>
                            <input type="text" size="10" name="exp[]" value="<?=$mFilter['exp_from']==0?'':$mFilter['exp_from']?>" maxlength="3" class="w65" />
                            &mdash;
                            <input type="text" size="10" maxlength="3" name="exp[]" value="<?=$mFilter['exp_to']==0?'':$mFilter['exp_to']?>" class="w65" /> лет
                        </div>
                    </div>
                    <div class="form-el">
                        <div class="f-tgl">
                            <a href="" class="lnk-dot-blue">Имя или логин</a>
                        </div>
                        <div>
                            <input type="text" name="login" value="<?=stripcslashes($mFilter['login'])?>" />
                        </div>
                    </div>
                    <div class="form-el">
                        <div class="f-tgl">
                            <a href="" class="lnk-dot-blue">Возраст</a>
                        </div>
                        <div>
                            <input type="text" size="10" maxlength="3" name="age[]" value="<?=$mFilter['age_from']==0?'':$mFilter['age_from']?>" class="w65" />
                            &mdash;
                            <input type="text" size="10" maxlength="3" name="age[]" value="<?=$mFilter['age_to']==0?'':$mFilter['age_to']?>" class="w65" /> лет
                        </div>
                    </div>
                    <div class="form-el">
                        <div class="f-tgl">
                            <a href="" class="lnk-dot-blue">Пол</a>
                        </div>
                        <div>
                            <select name="sex" class="w120">
                                <option value="0">любой</option>
                                <option value="1" <?= $mFilter['sex'] == 1 ? 'selected' : '' ?>>Женский</option>
                                <option value="2" <?= $mFilter['sex'] == 2 ? 'selected' : '' ?>>Мужской</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-el">
                        <div class="f-tgl">
                            <a href="" class="lnk-dot-blue">Месторасположение</a>
                        </div>
                        <div>
                            <div class="f-sel">
                                <select class="flt-p-sel" id="pf_country" name="pf_country" onChange="FilterCityUpd(this.value)">
                                    <option value="0">Все страны</option>
                                    <? foreach ($filter_countries as $countid => $country): ?>
                                        <option value="<?= $countid ?>"<? if ($countid == $mFilter['country']) echo(" selected") ?>><?= $country ?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                            <div class="f-sel" id="frm_city">
                                <select class="flt-p-sel" name="pf_city">
                                    <option value="0">Все города</option>
                                    <? if (sizeof($filter_cities)) foreach ($filter_cities as $cityid => $city): ?>
                                        <option value="<?= $cityid ?>"<? if ($cityid == $mFilter['city']) echo(" selected") ?>><?= $city ?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-el">
                        <div class="f-tgl">
                            <a href="" class="lnk-dot-blue">Дополнительные параметры</a>
                        </div>
                        <div style="display: none;">
                            <ul class="form-list">
                                <li><div class="b-check"><input type="checkbox" class="b-check__input" name="in_office" value="1" <?=($mFilter['in_office']=='t'?'checked="checked"':'')?> /> <label class="b-check__label">Ищет работу в офисе</label></div></li>
                                <li><div class="b-check"><input type="checkbox" class="b-check__input" name="in_fav" value="1" <?=($mFilter['in_fav']=="t"?'checked="checked"':'')?> /> <label class="b-check__label">У меня в избранных</label></div></li>
                                <li><div class="b-check"><input type="checkbox" class="b-check__input" name="only_free" value="1" <?=($mFilter['only_free']=="t"?'checked="checked"':'')?> /> <label class="b-check__label">Только свободные</label></div></li>
                                <li><div class="b-check"><input type="checkbox" class="b-check__input" name="is_positive" value="1" <?=($mFilter['is_positive']=="t"?'checked="checked"':'')?> /> <label class="b-check__label">С положительными отзывами</label></div></li>
                                <li><div class="b-check"><input type="checkbox" class="b-check__input" name="is_preview" value="1" <?=($mFilter['is_preview']=="t"?'checked="checked"':'')?> /> <label class="b-check__label">Только с примерами работ</label></div></li>
                                <li><div class="b-check"><input type="checkbox" class="b-check__input" name="not_negative" value="1" <?=($mFilter['not_negative']=="t"?'checked="checked"':'')?> /> <label class="b-check__label">Без отрицательных отзывов</label></div></li>
                            </ul>
                        </div>
                    </div>
                    <div class="form-el">
                        <input type="submit" value="Применить" />
                        <input type="button" value="Очистить" onclick="FilterCatalogClearForm()" />
                        <? if ($filter_apply) { ?>
                        <a href="<?=$frm_action?><?=$prmd?>action=deletefilter<?=$filter_query?>" class="flt-lnk">Отключить фильтр</a>
                        <? } else { ?>
                        <a href="<?=$frm_action?><?=$prmd?>action=activatefilter<?=$filter_query?>" class="flt-lnk">Включить фильтр</a>
                        <? } ?>
                    </div>
                </div>
            </div>
        </div>
        <b class="b2"></b>
        <b class="b1"></b>
    </div>
</form>
<script language="javascript">
FilterAddBullet(0,0,0,0);
</script>   
