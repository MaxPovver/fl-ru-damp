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

  if (!$uid)
    return 0;

  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");

  $has_hidd = TRUE;
  $filter_apply = ($filter['active'] == "t");
  $filter_categories = professions::GetAllGroupsLite(TRUE);
  $filter_countries = country::GetCountries();
  if ($filter['country']) {$filter_cities = city::GetCities($filter['country']);}

  switch($filter_page) {
    case 1:
      $frm_action = '/proj/?p=list';
      $prmd='&amp;';
      $has_hidd = FALSE;
      break;
    default:
      $frm_action = '/';
      $prmd='?';
  }

  if(!$filter) {
    $filter = array(
         'user_id' => $uid,
         'cost_from' => '',
         'cost_to' => '',
         'currency' => 0,
         'wo_cost' => 't',
         'only_sbr' => 'f',
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

//  if(isset($_COOKIE['new_pf'.$filter_page]))
//    $filter_show = $_COOKIE['new_pf'.$filter_page];
//  else {
//    $filter_show = 1;
//    setcookie("new_pf".$filter_page, $filter_show, time()+60*60*24*30, "/");
//  }

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
</script>



							<div class="form projects-flt" style="overflow: hidden">
<form action="<?=$frm_action?>" method="post" id="frm">
<div>
<input type="hidden" name="action" value="postfilter" />
<?=$filter_inputs?>
                            <div class="flt-cnt" <?=(($filter_show)?"style='display:block;'":"")?>>
								<div class="form-in">
									<div class="form-block first">
										<div class="c">
											<div class="pf-r">
												<div class="form-el">
													<label class="form-label2">Местоположение:</label>
													<div class="flt-b-in" style="overflow:visible;">
														<div class="flt-b-row">
                                                           <span class="flt-prm">
                                                             <select class="flt-p-sel" id="pf_country" name="pf_country" onChange="FilterCityUpd(this.value)">
                                                                   <option value="0">Все страны</option>
                                                                   <?foreach ($filter_countries as $countid => $country) { ?>
                                                                   <option value="<?=$countid?>"<? if ($countid == $filter['country']) echo(" selected") ?>><?=$country?></option>
                                                                   <?}?>
                                                             </select>
                                                           </span>
														</div>
														<div class="flt-b-row flt-b-lc">
                                                           <span class="flt-prm" id="frm_city">
                                                             <select class="flt-p-sel" name="pf_city">
                                                               <option value="0">Все города</option>
                                                               <?if (sizeof($filter_cities)) foreach ($filter_cities as $cityid => $city) { ?>
                                                               <option value="<?=$cityid?>"<? if ($cityid == $filter['city']) echo(" selected") ?>><?=$city?></option>
                                                               <? } ?>
                                                             </select>
                                                           </span>
														</div>
													</div>
												</div>
												<div class="form-el">
													<label class="form-label2">Ключевые слова:</label>
													<div class="flt-b-in">
                                                        <input type="text" id="pf_keywords" name="pf_keywords" value="<?=htmlspecialchars($filter['keywords'], ENT_QUOTES, 'cp1251')?>" class="flt-p-keys" maxlength="255" />
													</div>
												</div>
											</div>
											<div class="pf-l">
												<div class="form-el">
													<label class="form-label">Бюджет:</label>
													<div class="form-input">
                                                        <input type="text" size="10" id="pf_cost_from" name="pf_cost_from" value="<?=$filter['cost_from']?>" maxlength="6" /> &mdash;
	                                                    <input type="text" size="10" id="pf_cost_to" name="pf_cost_to" value="<?=$filter['cost_to']?>" maxlength="6" />&nbsp;&nbsp;
                                                        <select name="pf_currency">
                                                           <option value="0" <?=($filter['currency'] == 0 ? "selected=\"selected\"" : "")?>>USD</option>
                                                           <option value="1" <?=($filter['currency'] == 1 ? "selected=\"selected\"" : "")?>>Euro</option>
                                                           <option value="2" <?=($filter['currency'] == 2 ? "selected=\"selected\"" : "")?>>Руб</option>
                                                        </select>
														<div class="c">
															<label><span class="i-chk"><input type="checkbox" id="pf_wo_budjet" name="pf_wo_budjet" value="1" <? if ($filter['wo_cost'] == 't') {?> checked="checked" <? } ?> /></span> Смотреть проекты с неуказанным бюджетом</label>
														</div>
														<div class="c">
															<label><span class="i-chk"><input type="checkbox" id="pf_only_sbr" name="pf_only_sbr" value="1" <? if ($filter['only_sbr'] == 't') {?> checked="checked" <? } ?> /></span> Только проекты, предусматривающие «<a href="/promo/<?= sbr::NEW_TEMPLATE_SBR?>/" target="_blank">Безопасную Сделку</a>»</label>
														</div>
													</div>
												</div>
												<div class="form-el">
													<label class="form-label">Категория:</label>
													<div class="form-input">
														<div class="flt-b-row">
                                                            <span class="flt-prm">
                                                                <select class="flt-p-sel" name="pf_category" id="pf_category" onChange="FilterSubCategory(this.value)">
                                                                    <option value="0">Выберите раздел</option>
                                                                    <? foreach($filter_categories as $cat) { if($cat['id']<=0) continue; ?>
                                                                    <option value="<?=$cat['id']?>"><?=$cat['name']?></option>
                                                                    <? } ?>
                                                                </select>
                                                            </span><a href="javascript: void(0);" onclick="if($('pf_category').value != 0) FilterAddBullet(0, $('pf_category').value, $('pf_category').options[$('pf_category').selectedIndex].text, 0);" class="lnk-dot-blue">Добавить</a>
														</div>
														<div class="flt-b-row">
                                                            <span class="flt-prm" id="frm_subcategory">
                                                                <select class="flt-p-sel" name="pf_subcategory" id="pf_subcategory">
                                                                    <option value="0">Выберите подраздел</option>
                                                                </select>
                                                                </span><a href="javascript: void(0);" onclick="if($('pf_subcategory').value != 0) FilterAddBullet(1, $('pf_subcategory').value, $('pf_subcategory').options[$('pf_subcategory').selectedIndex].text, $('pf_category').value);" class="lnk-dot-blue">Добавить</a>
														</div>
														<div class="flt-b-row2" style="display: none;">
															<label><span class="i-chk"><input type="checkbox" id="pf_my_specs" name="pf_my_specs" value="1" <? if ($filter['my_specs']=='t' && 1==2) {?> checked="checked" <? } ?> /></span> Смотреть только мои специализаци</label>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="flt-spec-list c" id="pf_specs"></div>
									</div>
									<div class="form-block last">
										<div class="form-el form-btns">
                                            <input type="submit" class="i-btn i-bold" value="Применить фильтр" onclick="submit();"/>&nbsp;&nbsp; <a href="javascript: void(0);" onclick="FilterClearForm('flt-pl-usr')" class="lnk-dot-666k">Очистить форму</a>
										</div>
									</div>
								</div>
								<b class="b2"></b>
								<b class="b1"></b>

                              </div>
</div>
</form>                 
							</div>




<script type="text/javascript">
FilterAddBullet(0,0,0,0);
</script>


