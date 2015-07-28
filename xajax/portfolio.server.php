<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/portfolio.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo/SeoTags.php");

/**
 * Добавление работы в избранное.
 *
 * @param integer $prj_id
 * @param integer $prof_id
 * @return xajax object
 */
//function AddFav($prj_id, $prof_id)
//{
//  global $session;
//  session_start();
//  $uid = $_SESSION['uid'];
//  $objResponse = new xajaxResponse();
//  $portfolio = &new portfolio();
//
//  if ($uid && $prj_id)
//  {
//    $info = $portfolio->ChangeFav($prj_id, $prof_id, $uid);
//  }
//  if (isset($info))
//  {
//    $objResponse->assign("fav_count", "innerHTML", $info[0]);
//    if ($info[1])
//    {
//      $objResponse->assign("favstar_" . $prj_id, "src", '/images/ico_star.gif');
//    }
//    else
//    {
//      $objResponse->assign("favstar_" . $prj_id, "src", '/images/ico_star_empty_white.gif');
//    }
//  }
//  return $objResponse;
//}

/**
 * Переключает и запоминает в сессии статус фильтра работ.
 *
 * @return object xajaxResponse
 */
function SwitchFilter()
{
  session_start();
  $objResponse = &new xajaxResponse();
  $filter_show = $_SESSION['portfolio_filter'];
  $filter_show = ($filter_show == 0) ? 1 : 0;
  $_SESSION['portfolio_filter'] = $filter_show;
  if ($filter_show == 1)
  {
    $objResponse->assign("pfa", "src", '/images/arrow_wt_d.gif');
  }
  else
  {
    $objResponse->assign("pfa", "src", '/images/arrow_wt_r.gif');
  }
  return $objResponse;
}

function openProfession($sId = '', $aParams = array()) {
    $objResponse = &new xajaxResponse();
    
    if ( (int) $sId != $_SESSION['uid'] ) {
        return $objResponse;
    }
    
    $prof_id   = (int)$aParams['sProfId'];
    $category  = current( portfolio::getPortfolioCategory($prof_id) );
    
    $kwords    = new kwords();
    $user_keys = $kwords->getUserKeys(get_uid(), $prof_id); 
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/tpl.profession-edit.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    
    $objResponse->assign( "popup_loader", "innerHTML", $sHtml );
    $objResponse->script( "portfolio.initPopup('profession_edit_popup');");
    $objResponse->script( "ComboboxManager.initCombobox( $('profession_edit_popup').getElements('.b-combo__input') );");
	
    return $objResponse;
}

function editProfession($uid = '', $params = '') {
    $objResponse = &new xajaxResponse();
    $uid = intval($uid);
    if( $uid != get_uid(false) || !get_uid(false) ) {
        return $objResponse;
    }
    $params = stripslashes($params);
    parse_str($params, $params);
    $params['portf_text'] = _htmlentities($params['portf_text'], 'UTF-8');
    $params['old_portf_text'] = _htmlentities($params['old_portf_text'], 'UTF-8');
    $params['user_keys']  = _htmlentities($params['user_keys'], 'UTF-8');
    $params = uploader::encodeCharset($params, array('utf-8', 'cp1251'));
    
    $prof_id    = intval($params['prof_id']);
	$profession = new professions();
    $profession->updateUserKeywordsProfessions($uid, $prof_id, $params['user_keys']);
    $profession->updateProfessionUser($uid, $prof_id, $params);
    
    if( !empty( $profession->errors ) ) {
        $errors = json_encode( array_map("win2utf", $profession->errors) );
        $objResponse->script("portfolio.viewError({$errors}, 'profession_edit_popup')");
    } else {
        $stop_words  = new stop_words( hasPermissions('users') );
        $user = new users();
        $user->login = $_SESSION['login'];
        $user->uid   = $_SESSION['uid'];
        $user->is_pro = is_pro() ? 't':'f';
        $is_owner    = ( $uid == $_SESSION['uid'] );
        $success     = true;
        $pinfo       = current( portfolio::getPortfolioCategory($prof_id) );
        
        $pinfo['mainprofname'] = $pinfo['group_name'];
        $pinfo['profname']     = $pinfo['prof_name'];
        $pinfo['gr_prevs']     = $pinfo['show_preview'];
        $pinfo = $profession->prepareCostText($pinfo, $stop_words);
        
        $ukeys[$prof_id] = $profession->loadProfessionUserKeyword($user->uid, $prof_id);
        
        if($params['position'] == 2) {
            $afterProfID = (int) $params['position_category_db_id'];
            $afterProf = current( portfolio::getPortfolioCategory($afterProfID) );
            
            $newPosition = ($afterProf['ordering'] + 1);
            if( $newPosition != ($pinfo['ordering']) ) {
                if($newPosition > $pinfo['ordering']) $newPosition--;
                $profession->changePositionProfessionsUser($newPosition, $uid, $prof_id);
                $change_position = true;
            }
        } else if($params['position'] != $pinfo['ordering']) {
            if(!is_pro()) {
                // Если пользователь не ПРо у него скрыты два раздела которые существуют они стоят на первых позициях всегда, и поэтому нельзя ставить разделу 1 позицию
                // позицию надо ставить исходя из позиций этих скрытых разделов.
                $fpos = $profession->GetProfDesc($uid, professions::BEST_PROF_ID);
                $spos = $profession->GetProfDesc($uid, professions::CLIENTS_PROF_ID);
                if($fpos['ordering'] == $spos['ordering']) { // Оба на первой позиции
                    $first = 2;
                } else { // Позиции корректны
                    $first = 3;
                }
            }
            $afterProfID     = 0;
            $profession->changePositionProfessionsUser(is_pro() ? 1 : $first, $uid, $prof_id);
            $change_position = true;
            $is_first        = true;
        }
        
        ob_start();
        include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/tpl.profession.item.php' );
        $sHtml = ob_get_contents();
        ob_end_clean();
        
        if( ($pinfo['show_preview'] == 't' && $params['on_preview_default'] == '0') || 
            ($pinfo['show_preview'] == 'f' && $params['on_preview_default'] == '1')   ) { // Меняем вид работ
            $portfolio = new portfolio();
            $works  = $portfolio->GetPortf($user->uid, $prof_id, true);
            
            if(!empty($works)) {
                $result = portfolio::prepareDataPortfolio($works, $uid, $stop_words, true);
                extract($result);
                $work[0]['id'] = 100;
                ob_start();
                include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/tpl.portfolio.works.php' );
                $sWorks = ob_get_contents();
                ob_end_clean();
            } else {
                $work[0]['id'] = null;
                $pp_noblocks[$prof_id]   = array();
                ob_start();
                include ( $_SERVER['DOCUMENT_ROOT'] . '/user/tpl.portfolio.works.php' );
                $sWorks = ob_get_contents();
                ob_end_clean();
            }
        }
        
        $objResponse->assign( "profession_{$prof_id}", "innerHTML", $sHtml );
        if($sWorks != '') {
            $objResponse->assign( "prof_works_{$prof_id}", "innerHTML", $sWorks );
        }
        $objResponse->script( "$('profession_edit_popup').dispose(); ");
        
        if( !empty($change_position) ) {
            $action = $is_first ? 'before' : 'after';
            $objResponse->script("portfolio.setPosition({$prof_id}, {$afterProfID}, '{$action}')");
        }
        
        $objResponse->script("JSScroll($('professions_works_{$prof_id}'));");
    }
    
    return $objResponse;
}

function removeProfession($uid, $params) {
    $objResponse = &new xajaxResponse();
    
    $uid = intval($uid);
    $prof_id= intval($params['prof_id']);
    
    if($uid != get_uid(false) || !get_uid(false) || $prof_id < 0) {
        return $objResponse;
    }
    
    $category = current( portfolio::getPortfolioCategory($prof_id) );
    if($category['is_work'] > 0) { // С работами раздел удалить нельзя!
        return $objResponse;
    }
    
    professions::removePortfChoise($uid, $prof_id);
    $objResponse->assign( "professions_works_{$prof_id}", "innerHTML", "");
    $objResponse->script( "$('profession_edit_popup').dispose(); ");
    
    return $objResponse;
}

function openEditWork($uid, $params) {
    $objResponse = &new xajaxResponse();
    if($uid == null) { 
        $uid = get_uid(false);
    }
    $uid     = intval($uid);
    $work_id = intval($params['id']);
    $prof_id = intval($params['prof_id']);
    
    if($uid != get_uid(false) || !get_uid(false) || ( $prof_id <= 0 && $prof_id != -4 && $prof_id != -3 ) ) {
        return $objResponse;
    }
    $wmode_margin = 80;
    if($work_id == 0) { // Значит новую работу надо
        $is_edit = false;
        $work    = array();
        $work['prof_id'] = $prof_id;
        $wmode_margin = 57;
    } else {
        $portfolio = new portfolio();
        $work      = $portfolio->GetPortfById($work_id);

        if($work['user_id '] == $uid) { 
            return $objResponse;
        }
        $is_edit = true;
    }
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/tpl.portfolio-work-edit.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    $btn_file__portf  = $work['pict'] ? 'Заменить файл' : 'Загрузить файл';
    $btn_file_preview = $work['prev_pict'] ? 'Заменить картинку' : 'Загрузить картинку';
    
    $setting_uploader = "{ is_replace: true, text: { uploadButton: '{$btn_file__portf}'}, validation: { maxFileCount: 1 } }";
    $setting_uploader_preview = "{ is_replace: true, text: { uploadButton: '{$btn_file_preview}'}, validation: { maxFileCount: 1, restrictedExtensions: [], allowedExtensions: ['gif', 'jpg', 'jpeg', 'png'] }, umask: '" . uploader::umask('pf_preview') . "' }";
    
    $objResponse->assign( "popup_loader", "innerHTML", $sHtml );
    $objResponse->script( "portfolio.initPopup('portfolio_work_edit');");
    $objResponse->script( "portfolio.initExpandLink('portfolio_work_edit');");
    $objResponse->script( "ComboboxManager.initCombobox( $('portfolio_work_edit').getElements('.b-combo__input') );");
    $objResponse->script( "uploader.create('work_main_file', {$setting_uploader});");
    $objResponse->script( "uploader.create('work_preview_file', {$setting_uploader_preview});");
    $objResponse->script( "var opts = $('swf_params').getElement('select').options;
        for (var i = 0; i < opts.length; i++) {
            if (opts[i].text == '{$work['wmode']}') {
                $('swf_params').getElement('select').selectedIndex = i;
            }
        }
        if ( $$('div.qq-upload-portfolio') ) {
            $$('div.qq-upload-portfolio').setStyle('margin-top', '{$wmode_margin}px');
        }
    ");
    
    return $objResponse;
}

function editWork($uid, $params) {
    $objResponse = &new xajaxResponse();
    if($uid == null) $uid = get_uid(false);
    $uid     = intval($uid);
    $work_id = intval($params['id']);
    
    if($uid != get_uid(false) || !get_uid(false) || $work_id < 0 || is_emp()) {
        return $objResponse;
    }
    $params = stripslashes($params);
    parse_str($params, $params);
    $params['work_descr'] = _htmlentities($params['work_descr'], 'UTF-8');
    $params['work_name']  = _htmlentities($params['work_name'], 'UTF-8');
    $params = uploader::encodeCharset($params, array('utf-8', 'cp1251'));
    $edited = portfolio::editWork($uid, $params);
    
    if(is_array($edited)) { // Вернуло ошибки
        $errors = json_encode( array_map("win2utf", $edited) );
        $objResponse->script("portfolio.viewError({$errors}, 'portfolio_work_edit')");
        
        return $objResponse;
    }
    
    if($edited) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );
        $first_prof  = intval($params['prof_id_first']);
        $second_prof = intval($params['work_category_db_id']);
        
        $objResponse->script("$('portfolio_work_edit').getParent().dispose()");
        $stop_words  = new stop_words( hasPermissions('users') );
        $profession  = new professions();
        $portfolio   = new portfolio();
        $user        = new users();
        $user->login = $_SESSION['login'];
        $user->uid   = $_SESSION['uid'];
        $user->is_pro = is_pro() ? 't':'f';
        $is_owner    = ( $uid == $_SESSION['uid'] );
        
        if($first_prof != $second_prof) {
            $pinfo       = current( portfolio::getPortfolioCategory($second_prof) );
            $pinfo['mainprofname'] = $pinfo['group_name'];
            $pinfo['profname']     = $pinfo['prof_name'];
            $pinfo['gr_prevs']     = $pinfo['show_preview'];
            $pinfo = $profession->prepareCostText($pinfo, $stop_words);
        
            $works  = $portfolio->GetPortf($uid, $second_prof, true);
            if(!empty($works)) {
                $result = portfolio::prepareDataPortfolio($works, $uid, $stop_words, true);
                extract($result);
                
                $work[0]['id'] = 100;
                $prof_id = $second_prof;
                ob_start();
                include ( $_SERVER['DOCUMENT_ROOT'] . '/user/tpl.portfolio.works.php' );
                $second_works = ob_get_contents();
                ob_end_clean();
            } else {
                $work[0]['id'] = null;
                $prof_id = $first_prof;
                $pp_noblocks[$prof_id]   = array();
                ob_start();
                include ( $_SERVER['DOCUMENT_ROOT'] . '/user/tpl.portfolio.works.php' );
                $first_works = ob_get_contents();
                ob_end_clean();
            }
            
            $pinfo       = current( portfolio::getPortfolioCategory($first_prof) );
            $pinfo['mainprofname'] = $pinfo['group_name'];
            $pinfo['profname']     = $pinfo['prof_name'];
            $pinfo['gr_prevs']     = $pinfo['show_preview'];
            $pinfo = $profession->prepareCostText($pinfo, $stop_words);
            
            $works  = $portfolio->GetPortf($uid, $first_prof, true);
            if(!empty($works)) {
                $result = portfolio::prepareDataPortfolio($works, $uid, $stop_words, true);
                extract($result);
                
                $work[0]['id'] = 100;
                $prof_id = $first_prof;
                ob_start();
                include ( $_SERVER['DOCUMENT_ROOT'] . '/user/tpl.portfolio.works.php' );
                $first_works = ob_get_contents();
                ob_end_clean();
            } else {
                $work[0]['id'] = null;
                $prof_id = $first_prof;
                $pp_noblocks[$prof_id]   = array();
                ob_start();
                include ( $_SERVER['DOCUMENT_ROOT'] . '/user/tpl.portfolio.works.php' );
                $first_works = ob_get_contents();
                ob_end_clean();
            }
            
            if($first_works != '') {
                $objResponse->assign( "prof_works_{$first_prof}", "innerHTML", $first_works );
            }
            
            if($second_works != '') {
                $objResponse->assign( "prof_works_{$second_prof}", "innerHTML", $second_works );
            }
            
        } else {
            $pinfo       = current( portfolio::getPortfolioCategory($second_prof) );
            $pinfo['mainprofname'] = $pinfo['group_name'];
            $pinfo['profname']     = $pinfo['prof_name'];
            $pinfo['gr_prevs']     = $pinfo['show_preview'];
            $pinfo = $profession->prepareCostText($pinfo, $stop_words);
            
            $works  = $portfolio->GetPortf($uid, $second_prof, true);
           
            if(!empty($works)) {
                $result = portfolio::prepareDataPortfolio($works, $uid, $stop_words, true);
                extract($result);
                $work[0]['id'] = 100;
                $prof_id = $second_prof;
                ob_start();
                include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/tpl.portfolio.works.php' );
                $sWorks = ob_get_contents();
                ob_end_clean();
            } else {
                $work[0]['id'] = null;
                $prof_id = $first_prof;
                $pp_noblocks[$prof_id]   = array();
                ob_start();
                include ( $_SERVER['DOCUMENT_ROOT'] . '/user/tpl.portfolio.works.php' );
                $first_works = ob_get_contents();
                ob_end_clean();
            }
            
            if($sWorks != '') {
                $objResponse->assign( "prof_works_{$second_prof}", "innerHTML", $sWorks );
            }
        }
        
        $objResponse->script("JSScroll($('professions_works_{$second_prof}'));");
    }
    
    return $objResponse;
}

function updatePreview($file_id, $resource) {
    $objResponse = &new xajaxResponse();
    
    $CFile = new CFile($file_id);
    $path_file = $CFile->path . 'sm_' . $CFile->name;
    if($CFile->image_size['height'] > portfolio::PREVIEW_MAX_HEIGHT || $CFile->image_size['width'] > portfolio::PREVIEW_MAX_WIDTH) {
        $CFile = $CFile->resizeImage($path_file, portfolio::PREVIEW_MAX_WIDTH, portfolio::PREVIEW_MAX_HEIGHT, 'auto', true);
    } else {
        $CFile = uploader::remoteCopy($CFile->id, $CFile->table, $CFile->path, false, 'sm_f_');
    }
    uploader::sclear($resource); // Чистим заменяем
    uploader::screateFile($CFile, $resource);
    $callback = uploader::getCallback(uploader::sgetTypeUpload($resource));
    $template = uploader::getTemplate('uploader.file', 'portfolio/');
    $template = str_replace(array('{idFile}', '{fileName}'), array($CFile->id, $CFile->original_name), $template);
   
    
    $objResponse->script("
        $$('#work_preview_file .qq-upload-list').set('html', '{$template}');
        $$('#work_preview_file .qq-upload-list .qq-upload-file').set('href', '" . WDCPREFIX . "/{$path_file}');
        $$('#work_preview_file .qq-upload-list .qq-uploader-fileID').show();
    ");
    $objResponse->script("
        $$('#work_preview_file .qq-upload-delete').addEvent('click', function() {
            new Request.JSON({
                url: '/uploader.php',
                onSuccess: function(resp) {
                    if(resp.success) {
                        $$('#work_preview_file .qq-upload-list').set('html', '');
                        if(resp.onComplete !== undefined) {
                            eval(resp.onComplete);
                        }
                    }  
                }
            }).post({
                'action': 'remove',
                'files' : [{$CFile->id}],
                'resource': '{$resource}',
                'u_token_key': '{$_SESSION['rand']}'
            });
        });
    ");
    $objResponse->script($callback);
    
    return $objResponse;
}

function removeWork($uid, $params) {
    $objResponse = &new xajaxResponse();
    if(!$uid) $uid = get_uid(false);
    $uid     = intval($uid);
    $work_id = intval($params['id']);
    $prof_id = intval($params['prof_id']);
    
    if($uid != get_uid(false) || !get_uid(false) || $work_id < 0) {
        return $objResponse;
    }
   
    $deleted = portfolio::DelPortf($uid, $work_id);
    
    if(!$deleted) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );
        
        $stop_words  = new stop_words( hasPermissions('users') );
        $profession  = new professions();
        $portfolio   = new portfolio();
        $user        = new users();
        $user->login = $_SESSION['login'];
        $user->uid   = $_SESSION['uid'];
        $user->is_pro = is_pro() ? 't':'f';
        $is_owner    = ( $uid == $_SESSION['uid'] );
        
        $pinfo       = current( portfolio::getPortfolioCategory($prof_id) );
        $pinfo['mainprofname'] = $pinfo['group_name'];
        $pinfo['profname']     = $pinfo['prof_name'];
        $pinfo['gr_prevs']     = $pinfo['show_preview'];
        $pinfo = $profession->prepareCostText($pinfo, $stop_words);
        
        $works  = $portfolio->GetPortf($uid, $prof_id, true);
           
        if(!empty($works)) {
            $result = portfolio::prepareDataPortfolio($works, $uid, $stop_words, true);
            extract($result);
            $work[0]['id'] = 100;
            ob_start();
            include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/tpl.portfolio.works.php' );
            $sWorks = ob_get_contents();
            ob_end_clean();
        } else {
            $work[0]['id'] = null;
            $pp_noblocks[$prof_id] = array();
            ob_start();
            include ( $_SERVER['DOCUMENT_ROOT'] . '/user/tpl.portfolio.works.php' );
            $sWorks = ob_get_contents();
            ob_end_clean();
        }
        
        if($sWorks != '') {
            $objResponse->assign("prof_works_{$prof_id}", "innerHTML", $sWorks);
        }
        $objResponse->script("$('portfolio_work_edit').getParent().dispose()");
    } else {
        $objResponse->call('alert', 'Ошибка, работу удалить не удалось.');
    }
    
    return $objResponse;
}


$xajax->processRequest();
?>