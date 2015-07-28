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

if (!sizeof($profs)) {$all_specs = professions::GetAllProfessions("", 0, 1);}
else                 {$all_specs = $profs;}
?>
<script type="text/javascript">
    
function FilterOffersClearForm() {
    if(document.getElementById("pf_specs") != undefined) {
        filter_bullets = new Array();
        filter_bullets[0] = new Array();
        filter_bullets[1] = new Array();
        if($('pf_category')) FilterSubCategory($('pf_category').value);
        $('pf_specs').innerHTML = "";
        $('pf_only_my_offs').setProperty('checked', '');
    }
}
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
<div id="flt-pl" class="flt-out <?=(($filter_show)?"flt-show":"flt-hide")?>" page="<?=$filter_page?>" >
<form id="frm" method="post" action="/projects/?kind=8">
<div>
<input type="hidden" value="post_offers_filter" name="action">
<input type="hidden" value="0" name="kind">    <b class="b1"></b>
    <b class="b2"></b>
     <div class="flt-bar" >
          <a class="flt-tgl-lnk" href="javascript: void(0);"><?=(($filter_show)?"Свернуть":"Развернуть")?></a>
          <h3>Фильтр предложений</h3>
          <? if($filter_apply) { ?>
            <span class="flt-stat flt-on">включен&nbsp;&nbsp;&nbsp;<a href="/?kind=8&action=delete_offers_filter" class="flt-lnk">отключить</a></span>
          <? } else { ?>
            <span class="flt-stat flt-off">отключен&nbsp;&nbsp;&nbsp;<a href="/?kind=8&action=activate_offers_filter" class="flt-lnk">включить</a></span>
          <? } ?>
     </div>
     <div class="flt-cnt">
          <div class="flt-block" >
               <label class="flt-lbl">Раздел:</label>
               <div class="flt-b-in">
                    <div class="flt-b-row" >
                         <span class="flt-prm">
                           <select onchange="FilterSubCategory(this.value)" id="pf_category" name="pf_category" class="flt-p-sel">
                             <option value="0">Выберите раздел</option>
                             <? foreach($filter_categories as $cat) { if($cat['id']<=0) continue; ?>
                             <option value="<?=$cat['id']?>"><?=$cat['name']?></option>
                             <? } ?>
                           </select>
                         </span><a class="lnk-dot-blue" onclick="if($('pf_category').value != 0) FilterAddBullet(0, $('pf_category').value, $('pf_category').options[$('pf_category').selectedIndex].text, 0);" href="javascript: void(0);">Добавить раздел</a>
                    </div>
                    <div class="flt-b-row" >
                         <span id="frm_subcategory" class="flt-prm">
                           <select id="pf_subcategory" name="pf_subcategory" class="flt-p-sel" disabled="">
                             
                           <option value="0">Выберите подраздел</option></select>
                         </span><a class="lnk-dot-blue" onclick="if($('pf_subcategory').value != 0) FilterAddBullet(1, $('pf_subcategory').value, $('pf_subcategory').options[$('pf_subcategory').selectedIndex].text, $('pf_category').value);" href="javascript: void(0);">Добавить подраздел</a>
                    </div>
                   <?php if(!is_emp()) {?>
                   <div class="flt-b-row" >
                       <label>
                            <span class="i-chk">
                                <input type="checkbox" value="1" <? if ($filter['only_my_offs']=='t') {?>checked="checked" <? } ?>name="pf_only_my_offs" id="pf_only_my_offs">
                            </span>
                           Смотреть только мои предложения
                       </label> 
                    </div>
                   <?php }//if?>
                    <div id="pf_specs" class="flt-spec-list clear" ></div>
               </div>
          </div>
          <div class="flt-block flt-b-lc" >
               <label class="flt-lbl"></label>
               <div class="flt-b-in" >
                   <input type="submit" onclick="submit();" value="Применить фильтр" class="i-btn">&nbsp;&nbsp;&nbsp;<a href="javascript: void(0);" onclick="FilterOffersClearForm()" class="flt-lnk">Очистить форму</a>
               </div>
          </div>
     </div>
    <b class="b2"></b>
    <b class="b1"></b>
    </div>
</form>
</div>   
<script type="text/javascript">
FilterAddBullet(0,0,0,0);
</script>