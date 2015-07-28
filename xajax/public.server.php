<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/public.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/countrys.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");

function HideDizkonAdv() {
    $objResponse = new xajaxResponse();
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/splash_screens.php");
    splash_screens::setViewed(splash_screens::SPLASH_DIZKON);
    return $objResponse;
}

function DelAttach($attach_id, $key)
{
	  $objResponse = new xajaxResponse();
    $tmpPrj = new tmp_project($key);
    if($tmpPrj->init(1)) {
        $tmpPrj->delAttach($attach_id, true);
        $objResponse->script("try{adattach({$attach_id})}catch(eeee){}");
    }
    return $objResponse;
}

function DelLogo($key)
{
	  $objResponse = new xajaxResponse();
    $tmpPrj = new tmp_project($key);
    if($tmpPrj->init(2)) {
        $tmpPrj->delLogo(true);
        $objResponse->script("try{adlogo()}catch(eeee){}");
    }
    return $objResponse;
}


function GetCitysByCid($country_id){
	$objResponse = new xajaxResponse();
	
	if ($country_id){
		$cities = city::GetCities($country_id);
	}
	$out_text = "<select name=\"city\" class=\"apf-select\"><option value=\"0\">Не выбрано</option>";
	if($cities) foreach ($cities as $cityid => $city)
		$out_text .= "<option value=".$cityid.">".$city."</option>";
	$out_text .= "</select>";
	$objResponse->assign("frm_city","innerHTML",$out_text);
	return $objResponse;
}

function GetProfessionsBySpec($spec_id){
	$objResponse = new xajaxResponse();
	
	if ($spec_id){
		$specs = professions::GetAllProfessions($spec_id);
	}

	$out_text = "<select name=\"subcategory\" class=\"apf-select\">";
	if($specs) for ($i=0; $i<sizeof($specs); $i++)
		$out_text .= "<option value=".$specs[$i]['id'].">".$specs[$i]['profname']."</option>";
	$out_text .= "<option value=\"0\">Другое</option></select>";
	$objResponse->assign("frm_subcategory","innerHTML",$out_text);

	return $objResponse;
}

/**
 * Переключает и запоминает в сессии статус основного фильтра фрилансеров.
 *
 * @return object xajaxResponse
 */
function SwitchFilter()
{
  session_start();
  $objResponse = &new xajaxResponse();
  $filter_show = $_SESSION['public_filter'];
  $filter_show = ($filter_show == 0) ? 1 : 0;
  $_SESSION['public_filter'] = $filter_show;
  return $objResponse;
}

/**
 * формирует превью проекта на главной странице
 */
function GetPreview ($data) {
    $objResponse = new xajaxResponse();

    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/CFile.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/HTML/projects_lenta.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
    require_once($_SERVER["DOCUMENT_ROOT"]."/classes/memBuff2.php");
        
    $kind = 1;
    
    $memBuff = new memBuff();
    $htmlLenta = $memBuff->get('projectsLentaHTML');
    
    if (!$htmlLenta) {
        $prj = new new_projects();
        $prjs = $prj->getProjects($num_prjs, -1, 1, false, null, true);
        foreach($prjs as &$proj) { // стираем принадлежность проекта к пользователю, чтобы не появилось кнопок РЕДАКТИРОВАТЬ и пр.
            unset($proj['user_id']);
        }
        unset($proj);

        $htmlPrj = new HTMLProjects();
        $htmlPrj->template = "/projects/tpl.lenta.new.php";
        $prj_content = $htmlPrj->ShowProjects($num_prjs, $prjs, 1, 1, null, true);

        $prfs = new professions();
        $profs = $prfs->GetAllProfessions('', 0, 1);

        // подложка с лентой проектов
        ob_start();
        include($_SERVER['DOCUMENT_ROOT'] . '/templates/main.php');
        $htmlLenta = ob_get_clean();
        
        // кэшируем ленту проектов
        $memBuff->set('projectsLentaHTML', $htmlLenta, 1800);
    }
    
    // подготавливаем данные для шаблона в ленту проектов
    $row = array();
    $row['kind'] =          __paramValue('int', $data['kind']);
    $row['cost'] =          __paramValue('int', $data['cost']);
    $row['currency'] =      __paramValue('int', $data['currency_db_id']);
    $row['priceby'] =       __paramValue('int', $data['priceby_db_id']);
    $row['name'] =          stripslashes(__paramValue('html', $data['name'], null, true));
    
    $contacts = array(
        'phone' => array(
            'name' => 'Телефон',
            'value' => ''
        ),
        'site' => array(
            'name' => 'Сайт',
            'value' => ''
        ),
        'icq' => array(
            'name' => 'ICQ',
            'value' => ''
        ),
        'skype' => array(
            'name' => 'Skype',
            'value' => ''
        ),
        'email' => array(
            'name' => 'E-mail',
            'value' => ''
        )
    );
    
    if (isset($data['contacts'])) {
        foreach ($data['contacts'] as $name => $value) {
            if(!isset($contacts[$name])) continue;
            switch ($name) {
                case 'site':
                    if (!url_validate(ltrim(ltrim($value, 'http://'), 'https://')) && trim($value) != '') {
                        $error["contact_{$name}"] = 'Поле заполнено некорректно';
                    }
                    if (strpos($value, 'htt') === false && trim($value) != '') {
                        $value = 'http://' . $value;
                    }
                    break;
                case 'email':
                    if (!is_email($value) && trim($value) != '') {
                        $error["contact_{$name}"] = 'Поле заполнено некорректно';
                    }
                    break;
            }
            $contacts[$name]['value'] = __paramValue('htmltext', stripslashes($value));
        }
        $row['contacts'] = serialize($contacts);
    }
    $descrFull = stripslashes(__paramValue('html', $data['descr'], null, true));
    $descr = preg_replace("/^ /", "\x07", $descrFull);
    $descr = preg_replace("/(\n) /", "$1\x07", $descr);
    $descr = reformat(strip_tags(htmlspecialchars(LenghtFormatEx(htmlspecialchars_decode($descr, ENT_QUOTES), 180), ENT_QUOTES), "<br />"), 50, 1, 0, 1);
    $descr = preg_replace("/\x07/", "&nbsp;", $descr);
    $row['descr'] = $descr;
    
    $row['t_is_payed'] =    $data['logo_ok'] || $data['top_ok'];
    $row['t_is_ontop'] =    __paramValue('bool', $data['top_ok']);
    $row['t_pro_only'] =    $data['pro_only'] ? 't' : 'f';
    $row['t_verify_only'] = $data['verify_only'] ? 't' : 'f';
    $row['t_urgent'] = $data['urgent'] ? 't' : 'f';
    $row['t_hide'] = $data['hide'] ? 't' : 'f';
    $row['create_date'] =   date('Y-m-d H:i', strtotime(date('Y-m-d H:i:s')) - 120); // делаем дату публикации 2 минуты назад
    $row['end_date'] =      __paramValue('string', $data['end_date']);
    $row['win_date'] =      __paramValue('string', $data['win_date']);
    $row['country'] =       __paramValue('int', $data['project_location_columns'][0]);
    $row['city'] =          __paramValue('int', $data['project_location_columns'][1]);
    list($row['country_name'], $row['city_name']) = explode(': ', __paramValue('string', $data['location']));
    $logoOK =               __paramValue('bool', $data['logo_ok']);
    $topOK =               __paramValue('bool', $data['top_ok']);
    $row['link'] =          __paramValue('string', $data['link']);
    if ($logoOK) {
        $logoAttach = new attachedfiles($data['logo_attachedfiles_session']);
        $logoFiles = $logoAttach->getFiles(array(1));
        if (count($logoFiles)) {
            $logoFile = array_pop($logoFiles); // загружено может быть несколько файлов, берем последний
            $logoCFile = new CFile($logoFile['id']);
        } elseif (__paramValue('int', $data['logo_file_id'])) {
            $logoCFile = new CFile(__paramValue('int', $data['logo_file_id']));
        }
        $row['logo_name'] = $logoCFile->name;
        $row['logo_path'] = $logoCFile->path;
    }
    $is_ajax = true;
    
    // подготовка данных для подробной страницы проекта
    $project = $row;
    $categories = array();
    for ($i = 0; $i < 3; $i++) {
        $categoryID = __paramValue('int', $data['project_profession' . $i . '_columns'][0]);
        $subcategoryID = __paramValue('int', $data['project_profession' . $i . '_spec_columns'][0]);
        if ($categoryID || $subcategoryID) {
            $categories[] = array('category_id' => $categoryID, 'subcategory_id' => $subcategoryID);
        }
    }
    $project['spec_txt'] = projects::_getSpecsStr($categories, ' / ', ', ', true);
    $project['ico_payed'] = $logoOK;
    $project['is_upped'] = $topOK;
    $project['descr'] = $descrFull;
    $project['logo_id'] = $logoCFile->id;
    $project['prefer_sbr'] = __paramValue('bool', $data['prefer_sbr']) ? 't' : 'f';
    $project['urgent'] = __paramValue('bool', $data['urgent']) ? 't' : 'f';
    $project['hide'] = __paramValue('bool', $data['hide']) ? 't' : 'f';
    
    if(trim($project['contacts']) != '') {
        $contacts_employer = unserialize($project['contacts']);
        $empty_contacts_employer = 0;
        foreach($contacts_employer as $name=>$contact) { 
            if(trim($contact['value']) == '') $empty_contacts_employer++;
        }
        $is_contacts_employer_empty = ( count($contacts_employer) == $empty_contacts_employer );
    }
    $isPreview = true;
    $project_exRates = project_exrates::GetAll();
    $translate_exRates = array (0 => 2, 1 => 3, 2 => 4, 3 => 1);    
    
    ob_start();
        include($_SERVER['DOCUMENT_ROOT'] . '/public/new/tpl.preview.php'); ?>
    <? $htmlProject = ob_get_clean();
    
    
    $objResponse->assign('project_preview_lenta', 'innerHTML', $htmlLenta);
    $objResponse->assign('project_preview_content', 'innerHTML', $htmlProject);
    $objResponse->script('Public.showPreview()');

    return $objResponse;
}


/**
 * Получение 3х смежных по категориям ТУ
 * 
 * @param type $cat_group
 * @param type $cat
 * @return \xajaxResponse
 */
function getRelativeTU($cat_group, $cat) 
{
    $objResponse = new xajaxResponse();
    
    $cat_group = intval($cat_group);
    $cat = intval($cat);
    
    if (!$cat_group) {
        return $objResponse;
    }
    
    require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/yii/tinyyii.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceFilter.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_catalog.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_categories.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_helper.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceModel.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/FreelancerModel.php');
    
    $tservices_categories = new tservices_categories();
    $category_group = $tservices_categories->getIdByGid($cat_group);
    $category = $tservices_categories->getIdByPid($cat);
    
    $limit = 3;    
    $tservicesCatalogModel = new tservices_catalog();
    $tservicesCatalogModel->category_id = $category ? (int)$category : (int)$category_group;
    $tservicesCatalogModel->setPage($limit);

    // поиск записей
    $list = $tservicesCatalogModel->cache(300)->getList();
    $tservices = $list['list'];
    
    // расширение сведений о типовых услугах
    $tserviceModel = TServiceModel::model();
    $tserviceModel->extend($tservices, 'id')
                  ->readVideos($tservices, 'videos', 'videos'); // во всех строках "распаковать" массив видео-клипов

    // расширение сведений о пользователях
    $freelancerModel = FreelancerModel::model();
    $freelancerModel->extend($tservices, 'user_id', 'user');

    
    if (($cat_group || $cat) && count($tservices)) {
        $html = Template::render(ABS_PATH . '/templates/recomend_tu.php', array('tservices' => $tservices));
        $objResponse->script("$('otherprojects').addClass('b-layout_hide');");
        $objResponse->assign('recomend_tu', 'innerHTML', $html);
    } else {
        $objResponse->script("$('otherprojects').removeClass('b-layout_hide');");
        $objResponse->assign('recomend_tu', 'innerHTML', "");
    }
    
    return $objResponse;
}

$xajax->processRequest();
?>