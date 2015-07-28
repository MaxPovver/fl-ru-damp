<?
  require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/public.common.php");
  $xajax->printJavascript('/xajax/');

  $categories = professions::GetAllGroupsLite();
  $categories_specs = professions::GetAllProfessions((intval($project['category'])?intval($project['category']):$categories[0]['id']));
  $aCnt = (int)count($tmpPrj->getAttach());

  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
  $countries = country::GetCountries();

  if ($project['country']) {
    $cities = city::GetCities($project['country']);
  }
  
  $project['end_date'] = $_POST['end_date']? $_POST['end_date']: ($project['end_date']? date('d-m-Y', strtotime($project['end_date'])): '');
  $project['win_date'] = $_POST['win_date']? $_POST['win_date']: ($project['win_date']? date('d-m-Y', strtotime($project['win_date'])): '');

  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
  $oprj_exrates = new project_exrates();
  $prj_exrates = $oprj_exrates->GetAll();
  
  $professions = professions::GetAllProfessions();
  array_group($professions, 'groupid');
  $professions[0] = array();
?>

<? 
$templates = array(
    uploader::getTemplate('uploader', 'project/'),
    uploader::getTemplate('uploader.file', 'project/'),
    uploader::getTemplate('uploader.popup', ''),
);
uploader::init(array(
    'attachedfiles'  => uploader::sgetLoaderOptions($uploader->resource)
), $templates);
?>
<script type="text/javascript">
var draft_saved = 0;
</script>


<script type="text/javascript">

var AC=<?=$aCnt?>;
var AM=<?=new_projects::MAX_FILE_COUNT?>;
var mA=null;

var isBudgetSliderChangePrice = 1;

function adattach(id) {
  AC--;
  if((a=document.getElementById('attach'+id))) {
    a.parentNode.removeChild(a);
    if(AC<=0&&(ab=document.getElementById('attachBox')))
      ab.parentNode.removeChild(ab);
  }
  if(ia=document.getElementById('inpfA'))
    ia.disabled=false;
  if(AM-AC>1) {
    if(!mA)
      mA=new mAttach(document.getElementById('inpfBox'), AM-AC);
    else {
      mA.max=AM-AC;
      var tmp=mA.findClass(mA.objs[mA.objs.length-1], "addButton");
      tmp.replaceChild(mA.plus(), tmp.childNodes[0]);
    }
  }
}

function chkcost(_th) {
    _th.value=_th.value.replace(/^[0\D]+/g,'').replace(/[^\d\.]+/g,'').replace(/\..*$/,'');
    if(!($('fcurrency').value==-1 || $('fpriceby').value==-1) && CheckCatsAndSubCats()) { changeBudgetSlider(); }
}


// --- Слайдер

function CheckCatsAndSubCats() {
    var category_error = 0;
    var category_count = 0;
    $$('.apf-or-one)').each(function(el){
        category_count++;
        if(el.getElement('select[name^=categories]').value!=0 && el.getElement('select[name^=subcategories]').value==-1) {
            category_error = 1;
        }
        if(category_count==1 && el.getElement('select[name^=categories]').value==0) {
            category_error = 1;
        }
    });
    if(category_error==1) {
        return false;
    } else {
        return true;
    }
}


function changeBudgetSlider() {
    if($("f3").get('value')!='' && !($('fcurrency').value==-1 || $('fpriceby').value==-1) && CheckCatsAndSubCats()) {
        var price = $("f3").get('value');
        var priceFM = getBudgetInFM(price);
        setBudgetSlider(priceFM);
    }
}

function getBudgetInFM(price) {
    var fRUB = <?=$prj_exrates['41']?>;
    var fEUR = <?=$prj_exrates['31']?>;
    var fUSD = <?=$prj_exrates['21']?>;
    switch($('fcurrency').get('value')) {
        case '2':
            priceFM = price*fRUB;
            break;
        case '0':
            priceFM = price*fUSD;
            break;
        case '1':
            priceFM = price*fEUR;
            break;
        default:
            priceFM = price*1;
            break;
    }
    return priceFM.toFixed(0);
}

function getBudgetFromFM(priceFM) {
    var tRUB = <?=$prj_exrates['14']?>;
    var tEUR = <?=$prj_exrates['13']?>;
    var tUSD = <?=$prj_exrates['12']?>;
    switch($('fcurrency').get('value')) {
        case '2':
            price = priceFM*tRUB;
            break;
        case '0':
            price = priceFM*tUSD;
            break;
        case '1':
            price = priceFM*tEUR;
            break;
        default:
            price = priceFM*1;
            break;
    }
    return price.toFixed(0);
}

function setMinAvgMaxBudgetPrice() {
    if($('fcurrency').value==-1 || $('fpriceby').value==-1) { return false; }
    // Перевести в текущую валюту
    var is_prj_cat = 1;
    var count = 1;
    var sum_min = 0;
    var sum_avg = 0;
    var sum_max = 0;
    var cat_id = 0;
    var type = $('fpriceby').get('value');
    var itype;
    var ctype;
    switch(type) {
        case '1':
            itype = 'hour';
            ctype = 1;
            break;
        case '2':
            itype = 'hour';
            ctype = 8;
            break;
        case '3':
            itype = 'hour';
            ctype = 22*8;
            break;
        case '4':
            itype = 'prj';
            ctype = 1;
            break;
    }
    $$("#fcategory select]").each(function (select) {
        var val = select.getSelected().get('value');
        if(is_prj_cat==1) {
            cat_id = val;
            is_prj_cat = 0;
        } else {
            if(val=='') {
                subcat_id = 0;
            } else {
                subcat_id = val;
            }
            sum_min = sum_min + budget_price[itype]['min'][cat_id][subcat_id];
            sum_avg = sum_avg + budget_price[itype]['avg'][cat_id][subcat_id];
            sum_max = sum_max + budget_price[itype]['max'][cat_id][subcat_id];
            sum_min = sum_min / count;
            sum_avg = sum_avg / count;
            sum_max = sum_max / count;
            is_prj_cat = 1;
            count++;
        }
    });
    var s_min = sum_min*ctype;
    var s_avg = sum_avg*ctype;
    var s_max = sum_max*ctype;
    $("hb-low").set("text", s_min.toFixed(0));
    $("hb-middle").set("text", s_avg.toFixed(0));
    $("hb-high").set("text", s_max.toFixed(0));

}


function setBudgetSlider(price) {
    var priceMin = $("hb-low").get("text")-0;
    var priceAvg = $("hb-middle").get("text")-0;
    var priceMax = $("hb-high").get("text")-0;

    if(price<=priceMin) {
        $("fbudget_type").set("value", 1);
        isBudgetSliderChangePrice = 0;
        $("budget-point-l").fireEvent("click");
    }
    if((price>priceMin && price<=priceAvg) || (price>=priceAvg && price<priceMax)) {
        $("fbudget_type").set("value", 2);
        isBudgetSliderChangePrice = 0;
        $("budget-point-m").fireEvent("click");
    }
    if(price>=priceMax) {
        $("fbudget_type").set("value", 3);
        isBudgetSliderChangePrice = 0;
        $("budget-point-h").fireEvent("click");
    }
}

// --- Слайдер


var alert_show = false;
function chtext()
{
  descr = document.getElementById('f2').value;
  if (descr.length > 5000){
//    document.getElementById('f2').onkeypress = null;
    document.getElementById('f2').value = descr.substring(0, 5000);
    if(!alert_show){
    alert_show = true;
    alert('Слишком длинный текст описания проекта');
    alert_show = false;
    }

//    document.getElementById('f2').onkeypress = function(){return chtext();};
    return false;
  }
  return true;
}
function CityUpd(v){
  ct = document.getElementById("frm").city;
  ct.disabled = true;
  ct.options[0].innerHTML = "Подождите...";
  ct.value = 0;
  xajax_GetCitysByCid(v);
}

function SubCategoryUpd(v){
  ct = document.getElementById("frm").subcategory;
  ct.disabled = true;
  ct.options[0].innerHTML = "Подождите...";
  ct.value = 0;
  xajax_GetProfessionsBySpec(v);
}

function ShowCities()
{
	if (document.getElementById('f8').checked){
		document.getElementById('showcities').style.display = 'block';
	} else {
		document.getElementById('showcities').style.display = 'none';
	}
}

function xajax_SwitchFilter() {
	return true;
}

function CheckTxtFlds() {
    var error = 0;


    var error_type = '';

    if(($('f1').get('value').trim())=='') {
        $('prj_title_error').setStyle('display','block');
        error = 1;
    } else {
        $('prj_title_error').setStyle('display','none');
    }
    if(!error_type && error==1) error_type='name';


    if(($('f2').get('value').trim())=='') {
        $('prj_text_error').setStyle('display','block');
        error = 1;
    } else {
        $('prj_text_error').setStyle('display','none');
    }
    if(!error_type && error==1) error_type='descr';


    var category_error = 0;
    var category_count = 0;
    $$('.apf-or-one)').each(function(el){
        category_count++;
        if(el.getElement('select[name^=categories]').value!=0 && el.getElement('select[name^=subcategories]').value==-1) {
            category_error = 1;
        }
        if(category_count==1 && el.getElement('select[name^=categories]').value==0) {
            category_error = 1;
        }
    });
    if(category_error==1) {
        $('prj_razdel_error').setStyle('display','block');
        error = 1;
    } else {
        if($('prj_razdel_error')) $('prj_razdel_error').setStyle('display','none');
    }
    if(!error_type && error==1) error_type='category';


    var price = $('f3').value.replace(/^[0\D]+/g,'').replace(/[^\d\.]+/g,'').replace(/\..*$/,'');
    if ($('f3').get('disabled')) {
        price = 0;
    }
    if(price>0) {
        if($('fcurrency').value==-1) {
            $('prj_currency_error').setStyle('display','block');
            error = 1;
        } else {
            $('prj_currency_error').setStyle('display','none');
        }

        if($('fpriceby').value==-1) {
            $('prj_priceby_error').setStyle('display','block');
            error = 1;
        } else {
            $('prj_priceby_error').setStyle('display','none');
        }
    }
    if(!error_type && error==1) error_type='budget';


    if(error==1) {
        return '#field_'+error_type;
    } else {
        return '';
    }
}

function CheckDates() {
    <?php if(!hasPermissions('projects')) { ?>
    var error_end_date = 0;
    var error_win_date = 0;
    var error = 0;
    if($('end_date').get('value')=='') {
        $('end_date_error').setStyle('display','block');
        $('end_date_error_msg').set('html','Неправильная дата');
        error_end_date=1;
    } else {
        $('end_date_error').setStyle('display','none');
    }
    if($('win_date').get('value')=='') {
        $('win_date_error').setStyle('display','block');
        $('win_date_error_msg').set('html','Неправильная дата');
        error_win_date=1;
    } else {
        $('win_date_error').setStyle('display','none');
    }
    
    if(error_win_date==0 && error_end_date==0) {
        var cur_date = new Date();
        var end_date = f_tcalParseDate($('end_date').get('value'));
        var win_date = f_tcalParseDate($('win_date').get('value'));
        if(end_date.valueOf()<cur_date.valueOf()) {
            $('end_date_error').setStyle('display','block');
            $('end_date_error_msg').set('html','Дата окончания конкурса не может находиться в прошлом');
            error=1;
        } else {
            if(win_date.valueOf()<=end_date.valueOf()) {
                $('win_date_error').setStyle('display','block');
                $('win_date_error_msg').set('html','Дата определения победителя не должна предшествовать дате окончания конкурса');
                error=1;
            }
        }
    }
    
    if(error_win_date==1 || error_end_date==1 || error==1) {
        return '#field_date';
    } else {
        return '';
    }
    <?php } else { //if?>
    return '';
    <?php } // else?>
}
    var sub = new Array();
<? foreach ($categories as $cat) {
    $out_s = array();
?>

sub[<?= $cat['id'];?>] = new Array(
<? if(is_array($professions[$cat['id']])) foreach ($professions[$cat['id']] as $subcat){

    $out_s[] = " new Array({$subcat['id']}, '".clearTextForJS($subcat['profname'])."') ";
}
echo implode(', ',$out_s);
?>
);
<? } ?>
var curFBulletsBox = 2;


var budget_price = new Array();
budget_price['prj'] = new Array();
budget_price['hour'] = new Array();
budget_price['prj']['min'] = new Array();
budget_price['prj']['avg'] = new Array();
budget_price['prj']['max'] = new Array();
budget_price['hour']['min'] = new Array();
budget_price['hour']['avg'] = new Array();
budget_price['hour']['max'] = new Array();
<? foreach ($categories as $cat) { ?>
    budget_price['prj']['min'][<?=$cat['id']?>] = new Array();
    budget_price['prj']['avg'][<?=$cat['id']?>] = new Array();
    budget_price['prj']['max'][<?=$cat['id']?>] = new Array();
    budget_price['hour']['min'][<?=$cat['id']?>] = new Array();
    budget_price['hour']['avg'][<?=$cat['id']?>] = new Array();
    budget_price['hour']['max'][<?=$cat['id']?>] = new Array();
    <?
    $ncount_prj = 0;
    $ncount_hour = 0;  
    $nsum_min_prj = 0;
    $nsum_max_prj = 0;
    $nsum_avg_prj = 0;
    $nsum_min_hour = 0;
    $nsum_max_hour = 0;
    $nsum_avg_hour = 0;
    ?>
    <? if(is_array($professions[$cat['id']])) foreach ($professions[$cat['id']] as $subcat) { ?>
        budget_price['hour']['min'][<?=$cat['id']?>][<?=$subcat['id']?>] = <?=$subcat['min_cost_hour']?>;
        budget_price['hour']['avg'][<?=$cat['id']?>][<?=$subcat['id']?>] = <?=$subcat['avg_cost_hour']?>;
        budget_price['hour']['max'][<?=$cat['id']?>][<?=$subcat['id']?>] = <?=$subcat['max_cost_hour']?>;
        budget_price['prj']['min'][<?=$cat['id']?>][<?=$subcat['id']?>] = <?=$subcat['min_cost_prj']?>;
        budget_price['prj']['avg'][<?=$cat['id']?>][<?=$subcat['id']?>] = <?=$subcat['avg_cost_prj']?>;
        budget_price['prj']['max'][<?=$cat['id']?>][<?=$subcat['id']?>] = <?=$subcat['max_cost_prj']?>;
        <?
        $nsum_min_prj = $nsum_min_prj + $subcat['min_cost_prj'];
        $nsum_max_prj = $nsum_max_prj + $subcat['max_cost_prj'];
        $nsum_avg_prj = $nsum_avg_prj + $subcat['avg_cost_prj'];
        $nsum_min_hour = $nsum_min_hour + $subcat['min_cost_hour'];
        $nsum_max_hour = $nsum_max_hour + $subcat['max_cost_hour'];
        $nsum_avg_hour = $nsum_avg_hour + $subcat['avg_cost_hour'];
        if($subcat['avg_cost_prj']!=0) $ncount_prj++;
        if($subcat['avg_cost_hour']!=0) $ncount_hour++;
        ?>
    <? } ?>
    <?
    if($ncount_prj==0) $ncount_prj = 1;
    if($ncount_hour==0) $ncount_hour = 1;
    ?>
    budget_price['prj']['min'][<?=$cat['id']?>][0] = <?=round(($nsum_min_prj/$ncount_prj),0)?>;
    budget_price['prj']['avg'][<?=$cat['id']?>][0] = <?=round(($nsum_avg_prj/$ncount_prj),0)?>;
    budget_price['prj']['max'][<?=$cat['id']?>][0] = <?=round(($nsum_max_prj/$ncount_prj),0)?>;
    budget_price['hour']['min'][<?=$cat['id']?>][0] = <?=round(($nsum_min_hour/$ncount_hour),0)?>;
    budget_price['hour']['avg'][<?=$cat['id']?>][0] = <?=round(($nsum_avg_hour/$ncount_hour),0)?>;
    budget_price['hour']['max'][<?=$cat['id']?>][0] = <?=round(($nsum_max_hour/$ncount_hour),0)?>;
<? } ?>

function NextStep() {
    if(draft_saved==1) return false;
    
    changeBudgetSlider(); 
    
    var sFieldsError = CheckTxtFlds();
    <?=(($project['kind']==2||$project['kind']==7) ? 'var sDatesError = CheckDates();' : "var bTawl = tawlFormValidation(document.getElementById('frm'))")?>
    
    if ( sFieldsError == '' && <?=($project['kind']==2||$project['kind']==7)? "sDatesError == ''": 'bTawl'?> ) { 
        $('frm').submit(); 
    }
    else {
        if ( sFieldsError != '' ) {
            window.location = sFieldsError;
        }
        <?php if ( $project['kind']==2 || $project['kind']==7 ) {
        ?>
        if ( sFieldsError == '' && sDatesError != '' ) {
            window.location = sDatesError;
        }
        <?php } ?>
    }
    return false;
}

</script>

<? include_once($_SERVER['DOCUMENT_ROOT'].'/filter_specs.php');

if ($project['kind'] == 7)
{
    echo "<h1 class='b-page__title'>Новый конкурс</h1>";
}
else
{
    echo "<h1 class='b-page__title'>Новый проект</h1>";
}

?>

  <div class="add-project ">
  <div class="add-project-more">
  
    <?php if ( $_SESSION['account_operations'] || is_emp() ): ?>
						<div class="b-tel">
								<span class="b-tel__icon <?= (NY2012TIME?"b-tel__icon_red":"b-tel__icon_green")?>"></span>
								<big class="b-tel__number">8-800-555-33-14</big>
								<span class="b-tel__txt"><?= (NY2012TIME?"С 31 декабря по 9 января телефон<br />службы поддержки работать не будет":"пн-пт с 9:00 до 22:00 часов МСК, <br/>без выходных")?></span>
						</div>
    <br />
 						<!--
            <div class="fphones <?= (NY2012TIME?"fphones_red":"")?>">
							<i></i>
            	<strong>8-800-555-33-14</strong>
            	<span><?= (NY2012TIME?"С 31 декабря по 9 января телефон<br />службы поддержки работать не будет":"телефон поддержки")?></span>
            </div>
            <div class="fphones fphones_red">
							<i></i>
            	<strong>8-800-555-33-14</strong>
            	<span>телефон поддержки</span>
            </div>
						-->
   <?php endif; ?>
  </div>
    <div class="add-project-form">
    <form action="/public/" method="post" enctype="multipart/form-data" id="frm" name="frm">

    <!-- DraftInfo -->
    <div class="form fs-p drafts-v" id="draft_div_info" style="display: none;">
		<b class="b1"></b>
		<b class="b2"></b>
		<div class="form-in" id="draft_div_info_text"></div>
		<b class="b2"></b>
		<b class="b1"></b>
	</div>
    <!-- /DraftInfo -->

   
	<div class="apf-blc">
      
      <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
      	<tr class="b-layout__tr">
        	<td class="b-layout__left b-layout__left_width_95">
                <a name="field_name"></a>
        		<label for="f1" class="apf-label">Заголовок</label>
      		</td>
            <td class="b-layout__right b-layout__right_padbot_15">
            	<div class="b-input">
                	<input id="f1" class="b-input__text" name="name" type="text" value="<?=$project['name']?>" maxlength="60" onkeydown="$('prj_title_error').setStyle('display', 'none');"/>
                </div>
                <div class="errorBox" style="display:none; margin-top:5px;" id="prj_title_error"><img src="/images/ico_error.gif" alt="" width="22" height="18"> &nbsp;<span>Поле не заполнено</span></div>
                <?=($error['name'] ? view_error($error['name']) : '')?>
                <div style="margin-top:5px;" ><span>Специалист какой квалификации и на какие задачи вам требуется. Например: Дизайнер иконок на проект</span></div>
            </td>
        </tr>
        
      	<tr class="b-layout__tr">
        	<td class="b-layout__left b-layout__left_width_95">
                <a name="field_descr"></a>
                <label for="f2" class="apf-label">Текст</label>
      		</td>
            <td class="b-layout__right">
            	<div class="b-textarea">
                <textarea id="f2" cols="50" class="b-textarea__textarea tawl" name="descr" rel="5000" rows="7" onkeydown="$('prj_text_error').setStyle('display', 'none');"><?=$project['descr']?></textarea>
                </div>
                <div class="errorBox" style="display:none; margin-top:5px;" id="prj_text_error"><img src="/images/ico_error.gif" alt="" width="22" height="18"> &nbsp;<span>Поле не заполнено</span></div>
                <div style="margin-top:5px;" ><span>Подробно опишите задачу, сроки выполнения, другие условия работы</span></div>
                <?=($error['descr'] ? view_error($error['descr']) : '')?>
            </td>
        </tr>
      </table>
	  <div class="apf-option" style="margin-top: 10px">
        <div>
        </div>
      </div>
<style type="text/css">
.b-file__descript{ padding-right:180px;}
.qq-upload-error-text{ padding-top:4px;}
</style>
      <a name="field_attach"></a>
	  <div class="apf-files" id="apf-files" style="margin-top: 5px; position:relative;">
      		<a class="b-layout__link" href="https://www.free-lance.ru/service/docs/section/?id=2"><img style="position:absolute; right:10px; top:13px; z-index:1;" src="/images/stuff.png" alt="" width="179" height="26" /></a>
            <div id="attachedfiles" class="b-fon"></div>
            <div style="margin-top:5px;" ><span>Загрузите документ или иллюстрацию, дающую дополнительную информацию о проекте и задаче</span></div>
      </div>


    </div>

        <div class="apf-blc">
            <a name="field_category"></a>
			<div class="apf-option">
				<label for="f3" class="apf-label">Раздел</label>
				<div id="fcategory">
					<div class="apf-or" style="margin: 0 0 -7px 0;" id="cat_con">
                    <?php if($tmpPrj->getCategories()) foreach($tmpPrj->getCategories() as $ccat){ ?>
						<div class="apf-or-one" id="cat_line">
							<select class="" style="width: 170px" name="categories[]"  onchange="RefreshSubCategory(this); setMinAvgMaxBudgetPrice(); changeBudgetSlider(); $('prj_razdel_error').setStyle('display', 'none'); this.blur();">
							 <option value="0">Выберите раздел</option>
								<? foreach($categories as $cat) { if($cat['id']<=0) continue; ?>
                                <option value="<?=$cat['id']?>" <?=($ccat['category_id']==$cat['id'] ? ' selected' : '')?>><?=$cat['name']?></option>
                                <? } //if ?>
							</select>&nbsp;&nbsp;
							<select name="subcategories[]" style="width: 200px" <?if($ccat['category_id']==0):?>disabled<?endif;?> class="subcat" onchange="setMinAvgMaxBudgetPrice(); changeBudgetSlider(); $('prj_razdel_error').setStyle('display', 'none');">
							    <option value="0" <? if($ccat['subcategory_id'] == 0) echo "selected"; ?>>Все специализации</option>
								<?$categories_specs = $professions[$ccat['category_id']];
                                  for ($i=0; $i<sizeof($categories_specs); $i++) { ?>
                                    <option value="<?=$categories_specs[$i]['id']?>"<? if ($categories_specs[$i]['id'] == $ccat['subcategory_id']) echo(" selected") ?>><?=$categories_specs[$i]['profname']?></option>
                                <? } //for ?>
              
							</select>&nbsp;&nbsp;
						</div>
<?php }else{ ?>
        						<div class="apf-or-one" id="cat_line">
							<select class="" style="width: 170px" name="categories[]"  onchange="RefreshSubCategory(this); setMinAvgMaxBudgetPrice(); changeBudgetSlider(); $('prj_razdel_error').setStyle('display', 'none'); this.blur();">
							<option value="0" selected>Выберите раздел</option>	
							<? foreach($categories as $cat) { if($cat['id']<=0) continue; ?>
              <option value="<?=$cat['id']?>" <?=($ccat['category_id']==$cat['id'] ? ' selected' : '')?>><?=$cat['name']?></option>
            <? } ?>
							</select>&nbsp;&nbsp;
							<select name="subcategories[]" style="width: 200px" disabled class="subcat" onchange="setMinAvgMaxBudgetPrice(); changeBudgetSlider(); $('prj_razdel_error').setStyle('display', 'none');">
							<option value="0" selected>Выберите специализацию</option>
								<? /*for ($i=0; $i<sizeof($categories_specs); $i++) { ?>
              <option value="<?=$categories_specs[$i]['id']?>"><?=$categories_specs[$i]['profname']?></option>
              <? } */?>
              
							</select>&nbsp;&nbsp;
						</div>
<?php } ?>

					</div>
                    <div class="errorBox" style="display:none; margin-top:5px;" id="prj_razdel_error"><img src="/images/ico_error.gif" alt="" width="22" height="18"> &nbsp;<span id="prj_razdel_txt_error">Не выбран раздел и подраздел</span></div>
                    <div style="margin-top:5px;" ><span>Выберите раздел и специализацию из каталога фрилансеров</span></div>
					<?=($error['category'] ? view_error($error['category']) : '')?>
				</div>
			</div>
		</div>
    <div class="apf-blc" style="display: none">
      <div class="apf-option">
        <label for="f3" class="apf-label">Раздел</label>
        <div>
          <div class="apf-or">
            <select class="apf-select apf-category" name="category" onchange="SubCategoryUpd(this.value); this.blur();">
            <? foreach($categories as $cat) { if($cat['id']<=0) continue; ?>
              <option value="<?=$cat['id']?>" <?=($project['category']==$cat['id'] ? ' selected' : '')?>><?=$cat['name']?></option>
            <? } ?>
            </select>
          </div>
          <div class="apf-or flt-b-lc" id="frm_subcategory">
            <select name="subcategory" class="apf-select apf-category">
              <?for ($i=0; $i<sizeof($categories_specs); $i++) { ?>
              <option value="<?=$categories_specs[$i]['id']?>"<? if ($categories_specs[$i]['id'] == $project['subcategory']) echo(" selected") ?>><?=$categories_specs[$i]['profname']?></option>
              <? } ?>
              <option value="0" <? if (!$project['subcategory']) echo(" selected") ?>>Все специализации</option>
            </select>
          </div>
        </div>
        
      </div><div style="margin-top:5px;" ><span>Выберите раздел и специализацию из каталога фрилансеров</span></div>
    </div>
    
	<? if ($project['kind'] == 7) { ?>
    <a name="field_date"></a>
	<div class="apf-blc">
		<div class="apf-option">
			<label for="f3" class="apf-label" style="margin-top: -3px">Окончание конкурса</label>
			<div class="apf-or">
				<input type="text" maxlength="10" id="end_date" name="end_date" value="<?=$project['end_date']?>" class="apf-date" readonly="readonly" onfocus="$('end_date').blur()"/>
				<span class="apf-date" id="end_date_btn">&nbsp;</span>
                <div class="errorBox" style="display:none;" id="end_date_error"><img src="/images/ico_error.gif" alt="" width="22" height="18"> &nbsp;<span id="end_date_error_msg"></span></div>
				<?=($error['end_date'] ? view_error($error['end_date']) : '')?>
			</div>
		</div>
		<div class="apf-option" style="margin-top: 5px">
			<label for="f3" class="apf-label" style="margin-top: -3px">Объявление победителей</label>
			<div class="apf-or" style="margin-bottom: 0">
				<input type="text" maxlength="10" id="win_date" name="win_date" value="<?=$project['win_date']?>" class="apf-date" readonly="readonly" onfocus="$('win_date').blur()"/>
				<span class="apf-date" id="win_date_btn">&nbsp;</span>
                <div class="errorBox" style="display:none;" id="win_date_error"><img src="/images/ico_error.gif" alt="" width="22" height="18"> &nbsp;<span id="win_date_error_msg"></span></div>
				<?=($error['win_date'] ? view_error($error['win_date']) : '')?>
			</div>
		</div>
	</div>
	<? } ?>
	
      <? if ($project['kind'] != 2 && $project['kind'] != 7) { ?>
	  <div class="apf-blc">
      <a name="field_kind"></a>
	  <div class="apf-option">
          <label class="apf-label">&nbsp;</label>
          <div>
            <ul class="apf-list">
              <li><span class="apf-or-radio"><input type="radio" name="kind" value="1" id="f5"<?=(!$project['kind'] || $project['kind']==1 ? ' checked' : '')?> onclick="ShowCities()"/></span><label for="f5" onclick="ShowCities()">Проекты</label> - <span>Разовые проекты с фиксированной оплатой</span></li>
              <li><span class="apf-or-radio"><input type="radio" name="kind" value="4" id="f8"<?=($project['kind']==4 ? ' checked' : '')?> onclick="ShowCities()"/></span><label for="f8" onclick="ShowCities()">Вакансии</label> -
              <span>Вакансии на работу в офисе или удаленно</span>
              <?php if ( !is_pro() ) {?>
                  <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">Публикация &mdash; <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold b-layout__txt_color_fd6c30"><?= new_projects::getProjectInOfficePrice(); ?> руб</span>.<br />В тексте вакансии можно публиковать контактную информацию</div>
              <?php }?>
                <div class="apf-office" id="showcities" <?=(($project['kind']!=4)?"style=\"display:none;\"":"")?>>
                  <div class="apf-or">
                    <select id="fcountry" name="country" class="apf-select" onChange="CityUpd(this.value)">
                      <option value="0">Страна</option>
                      <?foreach ($countries as $countid => $country) { ?>
                      <option value="<?=$countid?>"<? if ($countid == $project['country']) echo(" selected") ?>><?=$country?></option>
                      <? } ?>
                    </select>
                  </div>
                  <div class="apf-or flt-b-lc" id="frm_city">
                  <select id="fcity" name="city" class="apf-select">
                    <option value="0">Город</option>
                     <?if (sizeof($cities)) foreach ($cities as $cityid => $city) { ?>
                     <option value="<?=$cityid?>"<? if ($cityid == $project['city']) echo(" selected") ?>><?=$city?></option>
                     <? } ?>
                  </select>
                </div>
              </div>
              </li>
            </ul>
            <?=($error['kind'] ? view_error($error['kind']) : '')?>
          </div>
        </div>
       </div>
	   <? } ?>
    

	<div class="apf-blc">
     <a name="field_budget"></a>
     <div class="apf-option apf-o-budjet">
         <label for="f3" class="apf-label">Бюджет</label>
         <div>
             <span class="apf-dogovor"><input name="agreement" type="checkbox" value="1" id="agreement" <?= isset($project['cost']) && intval($project['cost']) == 0 ? 'checked' : '' ?> /> <label for="agreement">По договоренности</label></span>
             <table cellpadding="0" cellspacing="0" border="0">
                 <tr>
                     <td style="vertical-align: middle"><input type="text" id="f3" class="apf-budget" name="cost" onchange="chkcost(this)" value="<?= $project['cost'] ?>" maxlength="6" /></td>
                     <td style="vertical-align: middle; padding-left: 5px">
                         <select id="fcurrency" name="currency" class="apf-select" onChange="setMinAvgMaxBudgetPrice(); changeBudgetSlider();">
                             <option value="-1">Выберите валюту</option>
                             <option value="2"<?= ($project['currency'] == 2 && !(isset($project['cost']) && intval($project['cost']) == 0) ? ' selected="selected"' : '') ?>>Руб</option>
                             <option value="0"<?= ($project['currency'] === '0' && !(isset($project['cost']) && intval($project['cost']) == 0) ? ' selected="selected"' : '') ?>>USD</option>
                             <option value="1"<?= ($project['currency'] == 1 && !(isset($project['cost']) && intval($project['cost']) == 0) ? ' selected="selected"' : '') ?>>Euro</option>
                         </select>
                     </td>
                     <td style="vertical-align: middle; padding-left: 5px">
                         <select id="fpriceby" name="priceby" class="apf-select" onChange="setMinAvgMaxBudgetPrice(); changeBudgetSlider();">
                             <option value="-1">Выберите из списка</option>
                             <option value="1"<?= ($project['priceby'] == 1 ? ' selected="selected"' : '') ?>>цена за час</option>
                             <option value="2"<?= ($project['priceby'] == 2 ? ' selected="selected"' : '') ?>>цена за день</option>
                             <option value="3"<?= ($project['priceby'] == 3 ? ' selected="selected"' : '') ?>>цена за месяц</option>
                             <option value="4"<?= ($project['priceby'] == 4 ? ' selected="selected"' : '') ?>>цена за проект</option>
                  
                         </select>
                     </td>
                 </tr>
             </table>
             <?= ($error['currency'] ? view_error($error['currency']) : '') ?>
             <?= ($error['cost'] ? view_error($error['cost']) : '') ?>
             <div class="errorBox" style="display:none; margin-top:5px;" id="prj_currency_error"><img src="/images/ico_error.gif" alt="" width="22" height="18"> &nbsp;<span>Вы не выбрали валюту</span></div>
             <div class="errorBox" style="display:none; margin-top:5px;" id="prj_priceby_error"><img src="/images/ico_error.gif" alt="" width="22" height="18"> &nbsp;<span>Вы не выбрали вид бюджета</span></div>
         </div>
     </div>

     <!-- Слайдер -->
     <input type="hidden" name="budget_type" id="fbudget_type" value="<?= intval($project['budget_type']) ?>">
                    <div class="budget-select budget-middle">
                        <div class="budget-select-lug"></div>
                        <div class="budget-select-content fl-form fl-form-o">
                            <div class="b-layout__txt">Для выбора наиболее подходящей аудитории исполнителей укажите уровень кандидатов.</div>
                            <div class="budget-slider">
                                <div class="budget-scale">
                                    <span class="point-l"></span>
                                    <span class="point-h"></span>
                                    <span class="point-m"></span>
                                </div>
                                <div class="budget-pointer-road">
                                    <span style="left: 167px; " class="budget-pointer"></span>
                                    <span class="budget-point budget-point-l" id="budget-point-l"></span>
                                    <span class="budget-point budget-point-m" id="budget-point-m"></span>
                                    <span class="budget-point budget-point-h" id="budget-point-h"></span>
                                </div>
                                <div class="budget-levels">
                                    <span class="budget-l b-layout__txt b-layout__txt_fontsize_11" id="budget-point-l">Низкий</span>
                                    <span class="budget-m b-layout__txt b-layout__txt_fontsize_11" id="budget-point-m">Средний</span>
                                    <span class="budget-h b-layout__txt b-layout__txt_fontsize_11" id="budget-point-h">Высокий</span>
                                </div>
                            </div>
                            <div class="b-layout__txt b-layout__txt_fontsize_11">Учитывается средняя стоимость проектов или рабочего времени фрилансеров. Подробнее <a href="http://feedback.free-lance.ru/article/details/id/147" target="_blank">тут</a>.</div>
                            <span class="cc cc-lt"></span>
                            <span class="cc cc-rt"></span>
                            <span class="cc cc-lb"></span>
                            <span class="cc cc-rb"></span>
                        </div>
                        <!-- скрытые поля с ценами -->
                        <div class="hidden-budget">
                            <div id="hb-low">0</div>
                            <div id="hb-middle">0</div>
                            <div id="hb-high">0</div>
                        </div>
                        <!--// скрытые поля с ценами -->
                    </div>
     <!-- /Слайдер -->



      <div class="apf-o-pro">
        <div class="b-check b-check_padbot_10">
           <input class="b-check__input" name="pro_only" type="checkbox" id="f22" value="1"<?=($project['pro_only']=='f' ? '' : ' checked="checked"')?> />
           <label class="b-check__label" for="f22">Хочу получать ответы только от пользователей с аккаунтом <?= view_pro(false, false, true, 'пользователей с платным аккаунтом')?></label>
        </div>
          
        <?php if(false): ?>  
            <? if (strtotime('2013-06-13 23:59:59') > time()) { ?>
            <input name="verify_only" type="hidden" id="f24" value="0" />

            <div class="b-check">
                <input class="b-check__input" type="checkbox" value="1" disabled="disabled" />
                <label class="b-check__label b-check__label_color_666" for="f24">Хочу получать ответы только от пользователей с верифицированным<br /> аккаунтом <?= view_verify('верифицированных пользователей', '')?> (будет доступно с 14 июня)</label>
            </div>
            <? } else { ?>
            <div class="b-check">
                <input class="b-check__input" name="verify_only" type="checkbox" id="f24" value="1"<?=($project['verify_only']=='t' ? ' checked="checked"' : '')?> />
                <label class="b-check__label b-check__label_color_666" for="f24">Хочу получать ответы только от пользователей с верифицированным<br /> аккаунтом <?= view_verify('верифицированных пользователей', '')?></label>
            </div>
            <? } ?>
        <?php endif ?>
        
      </div>
     <? if(hasPermissions('projects')) { ?>
     <div class="apf-o-pro" style="background:#fff">
        <div class="b-check">
          <input class="b-check__input" name="strong_top" type="checkbox" id="f23" value="1" <?=($project['strong_top']=='0' ? '' : ' checked="checked"')?> />
          <label class="b-check__label" for="f23">Закрепить железно наверху ленты</label>
        </div>
      </div>
     <? }//if?>
      <? /* #0019741 if ($project['kind'] != 7) { ?>
      <div class="apf-sbr">
        <span class="apf-pro-check"><input name="prefer_sbr" id="prefer_sbr" type="checkbox" value="1"<?=($project['prefer_sbr']=='t' ? ' checked="checked"':'')?>></span>
        <p class="apf-pro-only">Чтобы обезопасить себя и сократить риски при работе с фрилансерами,<br>
          воспользуйтесь <a href="/norisk2/" target="_blank" class="sbr-ic">Сделкой Без Риска</a>.</p>
      </div>
      <? } */?>
    </div>
    <div class="apf-option apf-submit">

        <?php if($use_draft) { ?>
        <span class="todrafts">
             <span id="draft_time_save" class="time-save" style="float:none; display: none;"></span> <a href="javascript:DraftSave();" onclick="this.blur();" class="btnr-mb"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">В черновики</span></span></span></a>
	    </span>
        <? } ?>


      <input type="hidden" id="draft_id" name="draft_id" value="<?=$draft_id?>"/>
      <input type="hidden" id="draft_prj_id" name="draft_prj_id" value="<?=$draft_prj_id?>"/>
      <input type="hidden" name="action" value="next"/>
      <input type="hidden" name="step" value="1"/>
      <input type="hidden" name="pk" value="<?=$key?>"/>

      <a href="" onClick="return NextStep()" class="btnr btnr-blue"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Далее »</span></span></span></a>
    </div>
    </form>
  </div>
</div>
<script type="text/javascript">
  <? if ($project['kind'] == 2 || $project['kind'] == 7) { ?>
  new tcal ({ 'formname': 'frm', 'controlname': 'end_date', 'iconId': 'end_date_btn', 'clickEvent': function(){ $('end_date_error').setStyle('display', 'none'); } });
  new tcal ({ 'formname': 'frm', 'controlname': 'win_date', 'iconId': 'win_date_btn', 'clickEvent': function(){ $('win_date_error').setStyle('display', 'none'); } });
  <? } ?>

    window.addEvent('domready', function() {
        if(typeof MultiInput == 'function'){
            <?php if(is_pro()){ ?>
            var mx = new MultiInput('cat_con','cat_line', true);
            mx.init();
            <?} //if?>
        }
    <?php if($project['cost']) { ?>
        isBudgetSliderChangePrice = 0;
        setMinAvgMaxBudgetPrice();
        changeBudgetSlider();
    <?php } ?>

        <? if($error_type) { ?>
            window.location = '#field_<?=$error_type?>';
        <? } ?>

    });

    <?php if(intval(__paramInit('int', 'auto_draft', 'auto_draft'))==1) { ?>
    var is_auto_draft = 1;
    <?php } else { ?>
    var is_auto_draft = 0;
    <?php } ?>

    <?php if($use_draft) { ?>
    DraftInit(1);
    <? } ?>
</script>
