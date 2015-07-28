<?php
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
            $prmd = "?prof=$prof_id&";
}

//создаем массив специализаций (для фильтра на главной он уже есть в $prfs, для фильтра в проектах фрилансера его нет, поэтому делаем проверку на существование
if (!sizeof($profs)) {
    $all_specs = professions::GetAllProfessions("", 0, 1);
} else {
    $all_specs = $profs;
}


?>
<script type="text/javascript">
    var filter_user_specs = new Array();
    var filter_specs = new Array();
    var filter_specs_ids = new Array();
<?
$spec_now = 0;
for ($i = 0; $i < sizeof($all_specs); $i++) {
    if ($all_specs[$i]['groupid'] != $spec_now) {
        $spec_now = $all_specs[$i]['groupid'];
        echo "filter_specs[" . $all_specs[$i]['groupid'] . "]=[";
    }


    echo "[" . $all_specs[$i]['id'] . ",'" . $all_specs[$i]['profname'] . "']";

    if ($all_specs[$i + 1]['groupid'] != $spec_now) {
        echo "];";
    } else {
        echo ",";
    }
}

$spec_now = 0;
for ($i = 0; $i < sizeof($all_specs); $i++) {
    if ($all_specs[$i]['groupid'] != $spec_now) {
        $spec_now = $all_specs[$i]['groupid'];
        echo "filter_specs_ids[" . $all_specs[$i]['groupid'] . "]={";
    }


    echo "" . $all_specs[$i]['id'] . ":1";

    if ($all_specs[$i + 1]['groupid'] != $spec_now) {
        echo "};";
    } else {
        echo ",";
    }
}

$cost_type = array(1 => "За месяц", 2 => "За 1000 знаков", 3 => "За Проект", 4 => "За час");
$curr_type = array("USD", "Euro", "Руб", "FM");
?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/freelancers_filter.php";?>
        var filter_mirror_specs = <?=freelancers_filters::getMirroredSpecsJsObject($all_mirrored_specs); ?>; 
        var filter_bullets = [[],[]];

<?
if (sizeof($gFilter)) {
    for ($ci = 0; $ci < 2; $ci++) {
        $ph_categories[$ci] = array();
        if (sizeof($gFilter[$ci])) {
            foreach ($gFilter[$ci] as $fkey => $fvalue) {
                if ($fkey) {
                    if ( !freelancers_filters::mirrorExistsInArray($fkey, $ph_categories[$ci], $mirrored_specs) ) {
                        if (!$fvalue) {
                            $proftitle = professions::GetGroup($fkey, $error);
                            $proftitle = $proftitle['name'];
                        } else {
                            $proftitle = professions::GetProfName($fkey);
                            $prof_group = professions::GetProfField($fkey, 'prof_group');
                        }
?>
                                filter_bullets[<?= $fvalue ?>][<?= $fkey ?>] = new Array();
                                filter_bullets[<?= $fvalue ?>][<?= $fkey ?>]['type']=<?= $fvalue ?>;
                                filter_bullets[<?= $fvalue ?>][<?= $fkey ?>]['title']='<?= $proftitle ?>';
                                filter_bullets[<?= $fvalue ?>][<?= $fkey ?>]['parentid']='<?= (!($fvalue) ? 0 : $prof_group) ?>';
<?
                        if ($mirrored_specs[$fkey]) {
?>filter_bullets[<?= $fvalue ?>][<?= $fkey ?>]['mirror']=<?= $mirrored_specs[$fkey] ?>;<?
                        } else {
?>filter_bullets[<?= $fvalue ?>][<?= $fkey ?>]['mirror']=0;<?
                        }
                    }
                    $ph_categories[$ci][]=$fkey;
                }
            }
        }
    }
}
?>

//Функция обновления списка подкатегорий в зависимости от выбранной категории в фильтре
function RefreshSubCategory(ele, without_sa)
{
   var category = ele.value;
   var div = ele.parentNode;
   var objSel = $(div.getElementsByTagName('select')[1]);
  if(typeof without_sa == 'undefined') without_sa = false;
  
  objSel.options.length = 0;
  objSel.disabled = 'disabled';
  var ft = true;
  if(!without_sa){
      if (curFBulletsBox == 2){
        objSel.options[objSel.options.length] = new Option('Все специализации', 0, ft, ft);
        ft = false;
      } else {
        objSel.options[objSel.options.length] = new Option('Выберите подраздел', 0);
      }
  }
  if(category == 0) {
      objSel.set('disabled', true);
  } else {
      objSel.set('disabled', false);
  }
  
  for (i in filter_specs[category]) {
    if (filter_specs[category][i][0]) {
        objSel.options[objSel.options.length] = new Option(filter_specs[category][i][1], filter_specs[category][i][0], ft, ft);
        ft = false;
    }
  }
  if(!without_sa){
        if (curFBulletsBox == 2){
            objSel.set('value','-1');
            objSel.options[0].selected = true;
        } else {
            objSel.set('value','0');
        }
    }
    
}
</script>
