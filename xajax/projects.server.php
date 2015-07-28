<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/projects.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_filter.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_complains.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/notifications.php';

//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/buffer.php");
//$buf_dir = "index_buffer/";

function TabChange($prj_kind, $page, $filter = 0){
    global $session;
    session_start();
    $buf_dir = $GLOBALS['buf_dir'];
    $buf_file_name = $GLOBALS['buf_file_name'] = "prjs_kind".$prj_kind."p".$page;
    $objResponse = new xajaxResponse();
    $prj_kind = intval(trim($prj_kind));
    $filter = intval(trim($filter));
    $page = intval(trim($page));
    $content = '';
    $_SERVER['PHP_SELF']='/';
    $_SERVER['QUERY_STRING']='';
    if (!$prj_kind) $prj_kind = 0;
    if (!$filter) $filter = 0;
    if ($prj_kind >= 0 && $prj_kind <= 4){
        $prj = new projects;
        $content = $prj->SearchDB($prj_kind, $page, 0, (($filter)?true:false));
        $objResponse->assign("projects-list","innerHTML", $content);
        $objResponse->assign("processing","style.visibility","hidden");
    }
    return $objResponse;
}

function OpenAllProjects($open)
{
    $objResponse = new xajaxResponse();
    setcookie('isPrjOpened', (int)$open, time()+60*60*24*30, "/");
    return $objResponse;
}

function SwitchOS()
{
    session_start();
    $objResponse = &new xajaxResponse();
    $os = $_SESSION['os_banner'];
    $os = ($os == 0) ? 1 : 0;
    $_SESSION['os_banner'] = $os;
    if ($os == 1)
    {
        $objResponse->assign("a_hisho", "innerHTML", 'Свернуть');
    }
    else
    {
        $objResponse->assign("a_hisho", "innerHTML", 'Развернуть');
    }
    return $objResponse;
}


function HideTopProjects($cmd) {
    session_start();
    $objResponse = new xajaxResponse();
    if($cmd=='hide') {
        $objResponse->script('$("hide_top_project_lnk").set("cmd", "show");');
        $objResponse->script('$("hide_top_project_lnk").set("html", "Показать все");');
        $objResponse->script('$$(".topprjpay").each(function(el){ el.hide(); });');
        setcookie("hidetopprjlenta", '1', time()+60*60*24*30, '/');
        setcookie("hidetopprjlenta_time", time(), time()+60*60*24*30, '/');
    } elseif($cmd=='show') {
        $objResponse->script('$("hide_top_project_lnk").set("cmd", "hide");');
        $objResponse->script('$("hide_top_project_lnk").set("html", "Скрыть все");');
        $objResponse->script('$$(".topprjpay").each(function(el){ el.show(); });');
        setcookie("hidetopprjlenta", '0', time()+60*60*24*30, '/');
    }
    return $objResponse;
}

/**
 * Скрывает или отображает платные проекты.
 *
 * @see projects_filter::ShowClosedProjects()
 * @see projects_filter::initClosedProjects()
 *
 * @param string $id     id определенного или all для всех
 * @param string $type   скрыть (hide) или показать (unhide)
 * @param int $kind  тип текущей закладки главной страницы (см. new_projects::getProjects())
 * @param int $page  номер текущей страницы.
 * @param bool $filter  включен ли фильтр у юзера.
 *
 * @return object xajaxResponse
 */
function _HideProject($id, $type, $kind, $page, $filter) {
    session_start();
    $objResponse = new xajaxResponse();
    $all = ($id == 'all');
    $id = (int)$id;
    $uid = $_SESSION['uid'];
    
    if ($type == 'hide') {
        $prj = new new_projects();
        $content = $prj->SearchDB((int)$kind, 2, 0, $uf, true, true);
        $prjs = $prj->getProjects($num_prjs, (int)$kind, 2, true, $uf, true, true);
        $num = 0;

        $nDH = $nH;
        $_SESSION['ph'][$id] = base64_encode(projects::GetField($id, 'name'));
        $_SESSION['top_payed'] --;
        $objResponse->assign("project-item{$id}", 'outerHTML', '');

        if($filter) {
            $prj_filter = new projects_filters();
            $uf = $prj_filter->GetFilter($uid, $error);
        }
        if($prjs[$num]) {
            $this_kind = $prjs[$num]['kind'];
            $this_uid = get_uid(false);
            $this_pro_last = $_SESSION['pro_last'];
            $this_is_pro   = payed::CheckPro($_SESSION['login']);
            $this_edit_mode = hasPermissions('projects');
            if ($this_uid) {
                $this_user_role = $_SESSION['role'];
            }
            $this_project = $prjs[$num];
            $row = $this_project;
            if ($this_edit_mode || $this_project['kind'] == 2 || $this_project['user_id'] == $this_uid || $this_project['offer_id'] || $this_pro_last) {
                $this_show_data = 1;
                $row['show_data'] = 1;
            } else {
                $this_show_data = 0;
                $row['show_data'] = 0;
            }
            $descr = $row['descr'];
            $descr = preg_replace("/^ /","\x07",$descr);
            $descr = preg_replace("/(\n) /","$1\x07",$descr);
            $descr = reformat(strip_tags(LenghtFormatEx($descr, 180),"<br />"), 50, 1, 0, 1);
            $descr = preg_replace("/\x07/","&nbsp;",$descr);
            $row['descr'] = $descr;
            $row['t_is_payed'] = ($this_project['payed'] && $this_project['kind'] != 2 && $this_project['kind'] != 7);
            $row['t_is_contest'] = ($this_project['kind'] == 2 || $this_project['kind'] == 7);
            $row['t_pro_only'] = ($this_project['pro_only'] == "t" );
            $row['t_prefer_sbr'] = ($this_project['prefer_sbr'] == "t" );
            $row['priceby'] = $this_project['priceby'];
            $row['t_is_adm'] = hasPermissions('projects');
            $row['t_is_ontop'] = (strtotime($this_project['top_to']) >= time());
            $row['unread'] = ((int) $this_project['unread_p_msgs'] + (int) $this_project['unread_c_msgs'] + (int) $this_project['unread_c_prjs']);
            $row['t_is_proonly'] = ($this_project['pro_only'] == 't' && !$_SESSION['pro_last'] && !$this_edit_mode && ($this_uid != $this_project['user_id']));
            $row['friendly_url'] = getFriendlyURL('project', array('id'=>$row['id'], 'name'=>$row['name']));
            $attaches = projects::GetAllAttach($this_project['id']);
            $attaches = !$attaches ? array() : $attaches;
            foreach ($attaches as $k => $a) {
                $a['virus'] = is_null($a['virus']) ? $a['virus'] : bindec($a['virus']);
                $attaches[$k] = $a;
            }
            $row['attaches'] = $this_project['attaches'] = $attaches;
            $is_ajax = true;
            $can_change_prj = hasPermissions("projects");
            ob_start();
            require($_SERVER['DOCUMENT_ROOT'].'/projects/tpl.lenta-item.php');
            $prj_html = ob_get_contents();
            ob_end_clean();
            $objResponse->script('ndiv = new Element("div", {id: "project-item'.$prjs[$num]['id'].'", class: "b-post b-post_pad_10_15_15_20 b-post_margleft_-20 b-post_margright_-15 b-post_margbot_15 b-layout b-post_relative b-post_overflow_hidden'.($row['is_color']=='t' ? ' b-post_bg_fffded' : '').'"});');
            $objResponse->script('ndiv.inject($("projects-list").getLast("div[id^=project-item]"), "after");');
            $objResponse->assign("project-item".$prjs[$num]['id'], "innerHTML", $prj_html);
        }
    } else {   
        if ($all && $_SESSION['ph']) {
            $nDH = -1 * $nH * count($_SESSION['ph']);
            unset($_SESSION['ph']);
        } else {
            $nDH = -1 * $nH;
            unset($_SESSION['ph'][$id]);
            if(!$_SESSION['ph'])
                unset($_SESSION['ph']);
        }

        if($filter) {
            $prj_filter = new projects_filters();
            $uf = $prj_filter->GetFilter($uid, $error);
        }
        $prj = new new_projects();
        $content = $prj->SearchDB((int)$kind, (int)$page, 0, $uf, true, true);
        $objResponse->assign("projects-list","innerHTML", $content);
    }
    
    if($_SESSION['ph']) $ccph = array_keys($_SESSION['ph']);
    setcookie("ph[{$uid}]", $ccph ? implode(',',$ccph) : '', time()+60*60*24*30, '/');

    if ($kind == 2 || $kind == 7) {
        $prjWord_1 = 'скрытый конкурс';
        $prjWord_2 = 'скрытых конкурса';
        $prjWord_5 = 'скрытых конкурсов';
    } elseif ($kind == 4) {
        $prjWord_1 = 'скрытая вакансия';
        $prjWord_2 = 'скрытых вакансии';
        $prjWord_5 = 'скрытых вакансий';
    } else {
        $prjWord_1 = 'скрытый проект';
        $prjWord_2 = 'скрытых проекта';
        $prjWord_5 = 'скрытых проектов';
    }

    $cnt = "";
    if(sizeof($_SESSION['ph']) && $_SESSION['uid']) {
        $cnt =  sizeof($_SESSION['ph']) . " " . ending(sizeof($_SESSION['ph']), $prjWord_1, $prjWord_2, $prjWord_5);
    }
    $objResponse->assign("hide_project_count", "innerHTML", $cnt);
    // если до этого проекты отображались в режиме "Только название", то сворачиваем их
    $objResponse->call("rollProjects");

    return $objResponse;
}

/**
 * Скрывает или отображает платные проекты.
 *
 * @see projects_filter::ShowClosedProjects()
 * @see projects_filter::initClosedProjects()
 *
 * @param string $id     id определенного или all для всех
 * @param string $type   скрыть (hide) или показать (unhide)
 * @param int $kind  тип текущей закладки главной страницы (см. new_projects::getProjects())
 * @param int $page  номер текущей страницы.
 * @param bool $filter  включен ли фильтр у юзера.
 *
 * @return object xajaxResponse
 */
function HideProject($id, $type, $kind, $page, $filter)
{
    session_start();
    $objResponse = new xajaxResponse();
    $all = ($id == 'all');
    $id = (int)$id;
    $uid = $_SESSION['uid'];
    
    if ($type == 'hide') {
        if ($all) {
            if($tops = new_projects::getTopProjects()) {
                foreach($tops as $t) {
                    $_SESSION['ph'][$t['id']] = base64_encode($t['name']);
                    $objResponse->assign("project-item{$t['id']}", 'outerHTML', '');
                }
            }
            $nDH = $nH * count($_SESSION['ph']);
            unset($_SESSION['top_payed']);
        }
        else {
            $nDH = $nH;
            $_SESSION['ph'][$id] = base64_encode(projects::GetField($id, 'name'));
            $_SESSION['top_payed'] --;
            $objResponse->assign("project-item{$id}", 'outerHTML', '');
        }
    }
    else
    {   
        if ($all && $_SESSION['ph']) {
            $nDH = -1 * $nH * count($_SESSION['ph']);
            unset($_SESSION['ph']);
        }
        else {
            $nDH = -1 * $nH;
            unset($_SESSION['ph'][$id]);
            if(!$_SESSION['ph'])
                unset($_SESSION['ph']);
        }

        if($filter) {
            $prj_filter = new projects_filters();
            $uf = $prj_filter->GetFilter($uid, $error);
        }
        $prj = new new_projects();
        $content = $prj->SearchDB((int)$kind, (int)$page, 0, $uf, true);
        $objResponse->assign("projects-list","innerHTML", $content);
    }
    
    if($_SESSION['ph']) $ccph = array_keys($_SESSION['ph']);
    setcookie("ph[{$uid}]", $ccph ? implode(',',$ccph) : '', time()+60*60*24*30, '/');

    $content = projects_filters::ShowClosedProjects((int)$kind, (int)$page, (int)$filter);
    $objResponse->assign("flt-hide-content", "innerHTML", $content);
    $objResponse->assign("flt-hide-cnt", "innerHTML", (sizeof($_SESSION['ph'])?"(".sizeof($_SESSION['ph']).")":""));
    
    // компенсируем изменение высоты блока скрытых проектов
    $objResponse->script("height=$('flt-hide-content').getStyle('height').toInt()+'px';$('flt-hide-content').getParent().setStyle('height', height);
    fbox=$('flt-ph');fslider=new Fx.Slide(fbox.getElement('.flt-cnt'),{duration:400});
    fbox.f_isShw=fbox.hasClass('flt-show');fbox.getElement('.flt-cnt').setStyle('display','block');
    if(fbox.f_isShw){fslider.show();}else{fslider.hide();}");
    
    return $objResponse;
}

function WstProj($offer_id, $cur_folder = 1)
{
    session_start();
	$objResponse = new xajaxResponse();

	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_dialogue.php");
    $user_id = get_uid(false);
    
	if($r = projects_offers::WasteProj($offer_id, $user_id)) {
		$objResponse->script("
		  if(pobx = document.getElementById('prjoffer_box{$offer_id}')) {
		    pobx.parentNode.removeChild(pobx,true);
		    if(curfc = document.getElementById('prjfld_cnt{$cur_folder}')) {
		      curfc.innerHTML = parseInt(curfc.innerHTML) + {$r};
  		    if(wstc = document.getElementById('prjfld_cnt5')) // корзина
  		      wstc.innerHTML = parseInt(wstc.innerHTML) - {$r};
  		    if(allc = document.getElementById('prjfld_cnt0')) // все вместе
  		      allc.innerHTML = parseInt(allc.innerHTML) + {$r};
		    }
		    dprj();
		  }
		");
	}

    // обновляем мигающий значек проекта
    if (!projects_offers::CheckNewFrlEvents($user_id, false) && !projects_offers_dialogue::CountMessagesForFrl($user_id, true, false)) {
        $objResponse->script("
            if($('new_offers_messages')) $('new_offers_messages').removeClass('l-projects-a');
        ");
    }

    $memBuff = new memBuff();
    $memBuff->delete("prjMsgsCnt{$user_id}");
    $memBuff->delete("prjMsgsCntWst{$user_id}");
	
    $objResponse->script("Notification()");
	return $objResponse;
}


/**
 * Проверка показывать ли индикацию Проектов в шапке
 */
function getProjectIndication() {
    define('LAST_REFRESH_DISABLE', 1);
    session_start();
    
    $aRes = array();
    $nCountM = $nCountE = 0;
    if ( isset($_SESSION['uid']) ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_dialogue.php"); 
         
        if(is_emp()) {
            $nCountE = projects_offers::CheckNewEmpEvents($_SESSION['uid']);
            $nCountM = projects_offers_dialogue::CountMessagesForEmp($_SESSION['uid'], true, false);
           
            if($nCountM != $_SESSION['lst_emp_new_messages']['cnt']) {
                $last_emp_new_messages_pid = projects_offers_dialogue::FindLastMessageProjectForEmp($_SESSION['uid']);
            } else {
                $last_emp_new_messages_pid = $_SESSION['lst_emp_new_messages']['pid'];
            }
            
            $_SESSION['lst_emp_new_messages']['cnt'] = $nCountM;
        } else {
            if (!($nCountE = projects_offers::CheckNewFrlEvents($_SESSION['uid'], false))) {
                $nCountM = projects_offers_dialogue::CountMessagesForFrl($_SESSION['uid'], true, false);
            }
        }
        
        if ( $nCountM === NULL || $nCountE === NULL ) {
        	$aRes['success'] = false;
        } else {
            $aRes['success'] = true;
            $aRes['count']   = $nCountM + $nCountE;
            
            if($nCountM > 0 && is_emp()) {
                $aRes['count_msg'] = $nCountM;
                $aRes['last_emp_new_message'] = $last_emp_new_messages_pid;
            }
            $aRes['time']    = PRJ_CHECK_DELAY;
        }
    } else {
        $aRes['success'] = false;
    }
    
    echo json_encode( $aRes );
}

function getPositionProject($id, $top_to, $now, $payed, $post_date, $kind) {
    $objResponse = new xajaxResponse();
    $payed  = (($top_to>$now && $payed) ? 1 : 0 );
    $counte = projects::CountProjectByID($id);
    $page   =  floor($counte/$GLOBALS["prjspp"])+1;
    $counte_page=$counte % $GLOBALS["prjspp"];
    if ($counte_page == 0) {
        $counte_page = $GLOBALS["prjspp"];
        $page--;
    }
    
    $html = '<a class="public_blue" href="/projects/?kind='.$kind.'&page='.$page.'#prj'.$id.'">'.$counte_page.'-е по счету ('.$page.'-я страница)</a>';
    
    $objResponse->assign("prj_pos_{$id}", "innerHTML", $html);
    $objResponse->script("$('pos_link_{$id}').destroy();");
    
    return $objResponse;
}

/**
 * Добавляет жалобу на предложение фрилансера
 * 
 * @param  int $nOfferId Идентификатор предложения на которое жалуются
 * @param  int $nUserId Идентификатор пользователя который жалуется
 * @param  int $nType Тип нарушения
 * @param  string $sMsg Суть жалобы
 * @return object xajaxResponse
 */
function sendOfferComplain( $nOfferId = 0, $nUserId = 0, $nType = 1, $sMsg = '' ) {
    session_start();
    
    if ( isset($_SESSION['uid']) ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer_offers.php' );
        
        $objResponse = new xajaxResponse();
        $offers = new freelancer_offers();
        
        if ( $offers->getOfferById($nOfferId, false) ) {
            if ( $offers->AddComplain($nOfferId, $nUserId, intval($nType), $sMsg) ) {
            	$objResponse->script("$('offer_complain_$nOfferId').set('html','Ваша жалоба на рассмотрении');");
                $objResponse->script("$('offer_complain_$nOfferId').set('onclick','');");
            }
        }
        
        $objResponse->script('complainBusy=false;');
        $objResponse->script("$$('.b-popup').setStyle('display', 'none');");
        
        return $objResponse;
    }
}

/**
 * Возвращает список жалоб на предложение фрилансера в админке.
 * 
 * с xajax не работает
 * 
 * @param  int $nOfferId Идентификатор предложения на которое жалуются
 * @return string json_encode данные
 */
function getOfferComplaints( $nOfferId = 0 ) {
    session_start();
    
    $res = array();
    
    if ( hasPermissions('projects') && $nOfferId ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer_offers.php' );
        
        $offers = new freelancer_offers();
        $aMsgs = $offers->getOfferComplaints( $nOfferId );
        $aData = array();
        
        foreach ( $aMsgs as $aOne ) {
            $aTmp = array(
                'login'   => iconv( 'CP1251', 'UTF-8', $aOne['login'] ), 
                'name'    => iconv( 'CP1251', 'UTF-8', $aOne['uname'] ), 
                'surname' => iconv( 'CP1251', 'UTF-8', $aOne['usurname'] ), 
                'date'    => date( 'd.m.Y', strtotime($aOne['date']) ),
                'time'    => date( 'H:i', strtotime($aOne['date']) ),
                'text'    => $aOne['msg'] ? iconv( 'CP1251', 'UTF-8', hyphen_words(reformat($aOne['msg'], 60), true) ) : '',
                'type'    => iconv( 'CP1251', 'UTF-8', $offers->GetComplainType($aOne['type']) )
            );
        	$aData[] = $aTmp;
        }
        
        $res['success'] = true;
        $res['data']    = $aData;
    } 
    else {
        $res['success'] = false;
    }
    
    echo json_encode( $res );
}

/**
 * Возвращает список жалоб на проект в админке.
 * 
 * с xajax не работает
 * 
 * @param  int $nProjId Идентификатор проекта на который жалуются
 * @param  string $group группа, в которой находится -> (new, approved, refused)
 * @return string json_encode данные
 */
function getProjectComplaints( $nPrjId = 0 , $group = 'new' ) {
    session_start();
    
    $res = array();
    
    if ( hasPermissions('projects') && $nPrjId ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
        
        $oPrj  = new projects();
        $aMsgs = $oPrj->getProjectComplaints( $nPrjId , $group );
        $aData = array();
        
        foreach ( $aMsgs as $aOne ) {
            $aFiles = array();
            
            if ( $aOne['files'] ) {
                $files = preg_split( "/,/", $aOne['files'] );
                
                foreach ( $files as $file ) {
                    if ( $file && $file != 'false' ) {
                        $aFiles[] = '<a target="_blank" href="' . WDCPREFIX . '/users/' . $aOne['e_login'] . '/upload/' . $file . '">' . $file . '</a><br/>';
                    }
                }
            }
            
            $status = 0;
            if($aOne['is_satisfied'] == 't') $status = 1;
            elseif($aOne['is_satisfied'] == 'f') $status = 2;
            
            $aTmp = array(
                'login'   => iconv( 'CP1251', 'UTF-8', $aOne['login'] ), 
                'name'    => iconv( 'CP1251', 'UTF-8', $aOne['uname'] ), 
                'surname' => iconv( 'CP1251', 'UTF-8', $aOne['usurname'] ), 
                'date'    => date( 'd.m.Y', strtotime($aOne['date']) ),
                'text'    => $aOne['msg'] ? iconv( 'CP1251', 'UTF-8', reformat(html_entity_decode($aOne['msg'], ENT_QUOTES),60) ) : '',
                'type'    => iconv( 'CP1251', 'UTF-8', projects_complains::GetComplainType($aOne['type'], true) ),
                'status'  => $status, 
                'pdate'   => date( 'd.m.Y', strtotime($aOne['processed_at']) ),
                'admin_login'   => iconv( 'CP1251', 'UTF-8', $aOne['admin_login'] ), 
                'admin_uname'    => iconv( 'CP1251', 'UTF-8', $aOne['admin_uname'] ), 
                'admin_usurname' => iconv( 'CP1251', 'UTF-8', $aOne['admin_usurname'] ), 
                'files'   => $aFiles
            );
            
        	$aData[] = $aTmp;
        }
        
        $res['success'] = true;
        $res['data']    = $aData;
    } 
    else {
        $res['success'] = false;
    }
    
    echo json_encode( $res );
}

function setReadAllProject() {
    session_start();
    $objResponse = new xajaxResponse();
    if ( is_emp()) {
        
        projects::SetReadAll(get_uid(false));
        $tip = notifications::getEmpGroupTip();

        $objResponse->script("
            $('new_offers_content').dispose();
            $$('.new-offer-image').each(function(elm) {
                var span = new Element('span', {'html': $(elm).getNext().get('html')});
                $(elm).getNext().dispose();
                $(elm).grab(span, 'after');
                $(elm).dispose();
            });
            
            var mt = $$('.b-user-menu-tasks-clause a');
            if(mt)
            {
                mt.set('title','{$tip['tip']}');
                var mt_cnt = mt.getElement('.b-user-menu-clause-quantity');    
                if(mt_cnt){".(($tip['count'] > 0)?"mt_cnt.set('html',{$tip['count']})":"mt_cnt.destroy();")."}
            }
        ");
    }
    
    return $objResponse;
}

$xajax->processRequest();

?>
