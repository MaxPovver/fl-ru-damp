<?php 
define( 'IS_SITE_ADMIN', 1 );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mailer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
session_start();

$uid = get_uid();
if (!(hasPermissions('adm') && hasPermissions('mailer'))) {
	header("Location: /404.php");
	exit;
}

$content     = '../content2.php';

$gAction = __paramInit('string', 'action', null, '');
$pAction = __paramInit('string', null, 'action', '');

$page = __paramInit('int', 'page', 'page', 1);

$mailer = new mailer();
$is_sending_me = __paramInit('int', 'sending', null, false);
$filter = array(
    'emp'     => __paramInit('int', 'emp'),
    'frl'     => __paramInit('int', 'frl'),
    'users'   => __paramInit('int', 'users'),
    'from'    => __paramInit('string', 'from', null, null),
    'to'      => __paramInit('string', 'to', null, null),
    'keyword' => __paramInit('string', 'keyword'),
    'sending' => __paramInit('int', 'sending'),
    'draft'   => __paramInit('int', 'draft'),
    'regular' => __paramInit('int', 'regular'),
    'pause'   => __paramInit('int', 'pause'),
    'digest'  => __paramInit('int', 'digest'),
    'mailer'  => __paramInit('int', 'mailer'),
    'sort'    => __paramInit('int', 'sort', null, 7)
);
function mailer_sort_url($top, $bottom, $sort) {
    $link = "?".url('emp,frl,from,to,sending,users,draft,regular,pause,page', array('sort'=> ($sort == $top ? $bottom : $top) )); 
    if($sort == $top || $sort == $bottom) {
        $img = $sort == $top ? "/images/arrow_black_bottom.gif":"/images/arrow_black_top.gif";
    } else {
        $img = false;
    }
    return array($link, $img);
}
$filter = array_filter($filter);
$act = __paramInit('string', 'act');
if($act == 'filter') { // Если запущен фильтр
    $filter['from'] = ($filter['from'] === null ? '' : $filter['from']);
    $filter['to']   = ($filter['to'] === null ? '' : $filter['to']);
}
if(empty($filter)) $filter = false;
$css_file = array();
switch ($pAction) {
    case 'delete':
        $id = __paramInit('int', null, 'id');
        $mailer->delete($id);
        header("Location: /siteadmin/mailer/");
        exit;
        break;
    case 'create_and_sendme':
        $insert  = $mailer->initPost($_POST);
        $message = $mailer->loadPOST($_POST);
        
        if(!$mailer->error) {
            if($insert['filter_file']) {
                $insert['filter_file'] = $mailer->uploadExtra();
            } else {
                if($insert['filter_emp']) $insert['filter_emp'] = $mailer->createFilter("mailer_filter_employer", $insert['filter_emp']);
                if($insert['filter_frl']) $insert['filter_frl'] = $mailer->createFilter("mailer_filter_freelancer", $insert['filter_frl']);
                $count  = mailer::getCountRecipients(array("emp", "frl"), $message);
                $insert['count_rec_emp'] = (int) $count[0];
                $insert['count_rec_frl'] = (int) $count[1];
            }
            $id = $mailer->create($insert);
            
            if($id && $_POST['attachedfiles_session'] && $insert['is_attached'] == true) {
                $mailer->addAttachedFiles($_POST['attachedfiles_files'], $id);
                $attachedfiles = new attachedfiles($_POST['attachedfiles_session']);
                $attachedfiles->clear();
            }
            $mailer->sendForMe($id, $message);
            header("Location: /siteadmin/mailer/?action=edit&id={$id}&sending=1");
            exit;
        }
        break;
    case 'digest':
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Digest/DigestFactory.php");
        
        $blocks = new DigestFactory();
        $blocks->createDefaultBlocks();
        
        $ablocks = $blocks->getBlocks();
        
        foreach($ablocks as $block) {
            $class = $block->__toString();
            if(is_array($_POST['position'][$class]) && count($_POST['position'][$class]) > 1) {
                $blocks->createAdditionBlocks(clone $block, count($_POST['position'][$class])-1);
            }
        }
        $error = false;
        foreach($blocks->getBlocks() as $block) {
            $block->initialize($_POST);
            if($block->isError()) {
                $error = true;
            }
            if($block->isCheck()) {
                $check = true; // Хотя бы один блок
            }
        }
        $blocks->sort();
        
        if($check == false) {
            $_error['block'] = true;
            $error = true;
        }
        
        $preview      = __paramInit('int', null, 'preview');
        $from_mail    = __paramInit('string', null, 'from_mail');
        $title_mail   = __paramInit('string', null, 'title_mail');
        //$message_mail = __paramInit('string', null, 'message_mail');
        $check_frl    = __paramInit('int', null, 'freelancers', null);
        $check_emp    = __paramInit('int', null, 'employers', null);
        $send_type    = __paramInit('int', null, 'send_type');
        $send_date    = __paramInit('string', null, 'send_date_eng_format');
        $send_time    = __paramInit('string', null, 'time_sending');
        $regular      = __paramInit('string', null, 'regular_week');
        $draft        = __paramInit('int', null, 'draft');
        
        if(is_empty_html($title_mail)) {
            $_error['title_mail'] = true;
            $error = true;
        }
        
        if(!$check_frl && !$check_emp) {
            $_error['check_recipient'] = true;
            $error = true;
        }
        
        $uCount = users::getCountUsersAll();
        
        $date_sending = ( $send_type == 1? date('Y-m-d H:i') : date('Y-m-d ', strtotime($send_date)). "{$send_time}:00" );
        
        $insert = array(
            'in_draft'      => ($draft == 1 ? 'true' : 'false'),
            'count_rec_emp' => $uCount['live_emp_today'],
            'count_rec_frl' => $uCount['live_frl_today'],
            'filter_frl'    => $check_frl ? 0 : null,
            'filter_emp'    => $check_emp ? 0 : null,
            'user_id'       => get_uid(false),
            'type_send_regular' => 1,
            'type_sending'  => '01', // только на почту
            'date_sending'  => $date_sending,
            'subject'       => $title_mail,
            'message'       => $blocks->createHTMLMessage()
        );
        $digest = $insert;
        
        if(!$error) {
            $digest_id = $mailer->create($insert);

            if($digest_id) {
                if($blocks->saveDigestBlocks($digest_id)) {
                    if($draft == 1) {
                        $_SESSION['is_save_digest'] = ($preview ? false: true);
                        header_location_exit("/siteadmin/mailer/?action=digest_edit&id={$digest_id}" . ($preview ? "&preview=1" : ""));
                    } else {
                        header_location_exit("/siteadmin/mailer/");
                    }
                }
            }
        }
        
        break;
    case 'digest_edit':
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Digest/DigestFactory.php");
        
        $blocks = new DigestFactory();
        $blocks->createDefaultBlocks();
        
        $ablocks = $blocks->getBlocks();
        
        foreach($ablocks as $block) {
            $class = $block->__toString();
            if(is_array($_POST['position'][$class]) && count($_POST['position'][$class]) > 1) {
                $blocks->createAdditionBlocks(clone $block, count($_POST['position'][$class])-1);
            }
        }
        $error = false;
        foreach($blocks->getBlocks() as $block) {
            $block->initialize($_POST);
            if($block->isError()) {
                $error = true;
            }
            if($block->isCheck()) {
                $check = true; // Хотя бы один блок
            }
        }
        $blocks->sort();
        
        if($check == false) {
            $_error['block'] = true;
            $error = true;
        }
        
        $uCount = users::getCountUsersAll();
        
        $preview      = __paramInit('int', null, 'preview');
        $digest_id    = __paramInit('int', null, 'id');
        $from_mail    = __paramInit('string', null, 'from_mail');
        $title_mail   = __paramInit('string', null, 'title_mail');
        $check_frl    = __paramInit('int', null, 'freelancers', null);
        $check_emp    = __paramInit('int', null, 'employers', null);
        $send_type    = __paramInit('int', null, 'send_type');
        $send_date    = __paramInit('string', null, 'send_date_eng_format');
        $send_time    = __paramInit('string', null, 'time_sending');
        $regular      = __paramInit('string', null, 'regular_week');
        $draft        = __paramInit('int', null, 'draft');
        
        if(is_empty_html($title_mail)) {
            $_error['title_mail'] = true;
            $error = true;
        }
        
        if(!$check_frl && !$check_emp) {
            $_error['check_recipient'] = true;
            $error = true;
        }
        
        $date_sending = ( $send_type == 1? date('Y-m-d H:i') : date('Y-m-d ', strtotime($send_date)). "{$send_time}:00" );
         
        $update = array(
            'in_draft'      => ($draft == 1 ? 'true' : 'false'),
            'count_rec_emp' => $uCount['live_emp_today'],
            'count_rec_frl' => $uCount['live_frl_today'],
            'filter_frl'    => $check_frl ? 0 : null,
            'filter_emp'    => $check_emp ? 0 : null,
            'type_send_regular' => 1,
            'date_sending'  => $date_sending,
            'subject'       => $title_mail,
            'message'       => $blocks->createHTMLMessage()
        );
        $digest = $update;
        if(!$error) {
            if( $mailer->update($update, $digest_id) ) {
                $blocks->updateDigestBlocks($digest_id);
                $_SESSION['is_save_digest'] = true;
                if($preview) {
                    include $_SERVER['DOCUMENT_ROOT'] . '/siteadmin/mailer/digest/tpl.preview.php';
                    exit;
                }
                
                if($draft != 1) { // Рассылка инициирована, кидаем на главную
                    header_location_exit("/siteadmin/mailer/");
                }
            }
        }
        
        break;
    case 'create':
        $insert  = $mailer->initPost($_POST);
        $message = $mailer->loadPOST($_POST);
        
        
        if(!$mailer->error) {
            if($insert['filter_file']) {
                $insert['filter_file'] = $mailer->uploadExtra();
            } else {
                $count  = mailer::getCountRecipients(array("emp", "frl"), $message);
                $insert['count_rec_emp'] = (int) $count[0];
                $insert['count_rec_frl'] = (int) $count[1];
                if($insert['filter_emp']) $insert['filter_emp'] = $mailer->createFilter("mailer_filter_employer", $insert['filter_emp']);
                if($insert['filter_frl']) $insert['filter_frl'] = $mailer->createFilter("mailer_filter_freelancer", $insert['filter_frl']);
            }
            
            $id = $mailer->create($insert);
            
            if($id && $_POST['attachedfiles_session'] && $insert['is_attached'] == true) {
                $mailer->addAttachedFiles($_POST['attachedfiles_files'], $id);
                $attachedfiles = new attachedfiles($_POST['attachedfiles_session']);
                $attachedfiles->clear();
            }
            
            if (__paramInit('int', null, 'preview') == 1) {
                header_location_exit("/siteadmin/mailer/?action=preview&id={$id}");
            }
            
            header("Location: /siteadmin/mailer/");
            exit;
        }
        
        break;
    case 'edit_and_sendme':
        $insert = $mailer->initPost($_POST);
        $id = __paramInit('int', 'id', 'id');
        $id_filter_frl = __paramInit('int', null, 'id_filter_frl');
        $id_filter_emp = __paramInit('int', null, 'id_filter_emp');
        $file_name = __paramInit('string', null, 'file_name');
        
        $message = $mailer->loadPOST($_POST);
        $message['id'] = $id;
        
        if(!$mailer->error) {
            
            if($insert['filter_file']) {
                
				$insert['filter_file'] = $mailer->uploadExtra($file_name);
                $message['filter_file'] = $insert['filter_file'];
			
            } else {
                $count  = mailer::getCountRecipients(array("emp", "frl"), $message);
        
                $insert['count_rec_emp'] = (int) $count[0];
                $insert['count_rec_frl'] = (int) $count[1];

                if($insert['filter_emp']) {
                    $insert['filter_emp'] = $mailer->updateFilter("mailer_filter_employer", $insert['filter_emp'], $id_filter_emp);
                } else {
                    $insert['filter_emp'] = null;
                }

                if($insert['filter_frl']) {
                    $insert['filter_frl'] = $mailer->updateFilter("mailer_filter_freelancer" , $insert['filter_frl'], $id_filter_frl);
                } else {
                    $insert['filter_frl'] = null;
                }
            }
            
            if($_POST['attachedfiles_session'] && $insert['is_attached'] == true) {
                $mailer->addAttachedFiles($_POST['attachedfiles_files'], $id);
                $attachedfiles = new attachedfiles($_POST['attachedfiles_session']);
                $attachedfiles->clear();
            }
            $is_update_mailer = true;
            $mailer->update($insert, $id);
            
            if($insert['filter_emp'] == null) {
                $mailer->deleteFilter("mailer_filter_employer", $id_filter_emp);
            }
            
            if($insert['filter_frl'] == null) {
                $mailer->deleteFilter("mailer_filter_freelancer", $id_filter_frl);
            }
            $mailer->sendForMe($id);
            $is_sending_me = true;
        }
        
        break;  
    case 'edit':
        $insert = $mailer->initPost($_POST);
        $id = __paramInit('int', 'id', 'id');
        $id_filter_frl = __paramInit('int', null, 'id_filter_frl');
        $id_filter_emp = __paramInit('int', null, 'id_filter_emp');
        $file_name = __paramInit('string', null, 'file_name');
        
        $message = $mailer->loadPOST($_POST);
        $message['id'] = $id;

        if(!$mailer->error) {
            if($insert['filter_emp']) {
                $insert['filter_emp'] = $mailer->updateFilter("mailer_filter_employer", $insert['filter_emp'], $id_filter_emp);
            } else {
                $insert['filter_emp'] = null;
            }
            
            if($insert['filter_frl']) {
                $insert['filter_frl'] = $mailer->updateFilter("mailer_filter_freelancer" , $insert['filter_frl'], $id_filter_frl);
            } else {
                $insert['filter_frl'] = null;
            }

            if($insert['filter_file']) {
				$insert['filter_file'] = $mailer->uploadExtra($file_name);
                $message['filter_file'] = $insert['filter_file'];
			} else {
                $count  = mailer::getCountRecipients(array("emp", "frl"), $message);
        
                $insert['count_rec_emp'] = (int) $count[0];
                $insert['count_rec_frl'] = (int) $count[1];
                
            }
            
            if($_POST['attachedfiles_session'] && $insert['is_attached'] == true) {
                $mailer->addAttachedFiles($_POST['attachedfiles_files'], $id);
                $attachedfiles = new attachedfiles($_POST['attachedfiles_session']);
                $attachedfiles->clear();
            }
            $is_update_mailer = true;
            $mailer->update($insert, $id);
            
            if($insert['filter_emp'] == null) {
                $mailer->deleteFilter("mailer_filter_employer", $id_filter_emp);
            }
            
            if($insert['filter_frl'] == null) {
                $mailer->deleteFilter("mailer_filter_freelancer", $id_filter_frl);
            }
            
            if (__paramInit('int', null, 'preview') == 1) {
                header_location_exit("/siteadmin/mailer/?action=preview&id={$message['id']}");
            }
            
            if ($message['in_draft'] == 'false') {
                header("Location: /siteadmin/mailer/");
                exit;               
            }
        }
        break;
    default:
        break;
}

switch($gAction) {
    
    case 'preview_only':
        
        $mailer_id = __paramInit('int', 'id');
        $message = $mailer->getMailerById($mailer_id);
        echo $mailer->getMailContent($message['message']);
        exit;
        break;
        
    case 'preview':
        
        $mailer_id = __paramInit('int', 'id');
        $message = $mailer->getMailerById($mailer_id);
        $inner_page = 'tpl.preview.php';
        $content = '../content-full-width.php';
        break;    
    
    case 'send':
        $mailer->getMailerSend();
        exit;
        break;
    case 'digest_edit':
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Digest/DigestFactory.php");
        $id = __paramInit('int', 'id');
        $is_edit = true;
        if(!$digest) {
            $digest = $mailer->getDigestById($id);
        }
        $rec_emp_count = $digest['count_rec_emp'];
        $rec_frl_count = $digest['count_rec_frl'];
        
        if(!$blocks) {
            $load_blocks = unserialize( $digest['blocks'] );
            $blocks = new DigestFactory();
            $blocks->createBlocks( $load_blocks );
        }
        
        $send_type = strtotime($digest['date_sending']) > time() ? 2 : 1;
        
        $preview = __paramInit('int', 'preview');
        if($preview) {
            $digest_id = $id;
            include $_SERVER['DOCUMENT_ROOT'] . '/siteadmin/mailer/digest/tpl.preview.php';
            exit;
        }
            
        $js_file  = array('/css/block/b-textarea/b-textarea.js', 'highlight.min.js', 'highlight.init.js', 'digest.js');
        $js_file_utf8[] = '/scripts/ckedit/ckeditor.js';
        array_push($css_file, 'wysiwyg.css', 'hljs.css');
        $inner_page = "tpl.digest.php";
        
        break;
    case 'digest':
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Digest/DigestFactory.php");
        $uCount = !isset($uCount) ? users::getCountUsersAll() : $uCount;
        $rec_emp_count = $uCount['live_emp_today'];
        $rec_frl_count = $uCount['live_frl_today'];
        
        if(!$blocks) {
            $digest['filter_frl'] = 1;
            $digest['filter_emp'] = 1;
            $blocks = new DigestFactory();
            $blocks->createDefaultBlocks();
        }
        
        $js_file  = array('/css/block/b-textarea/b-textarea.js', 'highlight.min.js', 'highlight.init.js', 'digest.js');
        $js_file_utf8[] = '/scripts/ckedit/ckeditor.js';
        array_push($css_file, 'wysiwyg.css', 'hljs.css');
        $inner_page = "tpl.digest.php";
        break;
    case 'create':
        $is_sub_regular = !empty(mailer::$SUB_TYPE_REGULAR[$message['type_regular']]);
        if(empty($count)) {
            list($rec_emp_count, $rec_frl_count) = $mailer->getCountRecipients(array('emp', 'frl'), $message);
        } else {
            list($rec_emp_count, $rec_frl_count) = $count;
        }
        
        $specs = professions::GetAllGroupsLite();
        $countries = country::GetCountries();
        $inner_page = "tpl.create.php";
        $js_file = array( 'highlight.min.js', 'highlight.init.js', 'mailer.js', 'attachedfiles.js' );
        $js_file_utf8[] = '/scripts/ckedit/ckeditor.js';
        break; 
    case 'edit':
        $specs = professions::GetAllGroupsLite();
        $countries = country::GetCountries();
        $id = __paramInit('int', 'id');
        if(!$pAction) {
            $message = $mailer->getMailerById($id);
        }
        if(empty($count)) {
            list($rec_emp_count, $rec_frl_count) = $mailer->getCountRecipients(array('emp', 'frl'), $message);
        } else {
            list($rec_emp_count, $rec_frl_count) = $count;
        }
        
        $is_sub_regular = !empty(mailer::$SUB_TYPE_REGULAR[$message['type_regular']]);
        
        if($message['flocation']['country'] > 0) {
            $citys = city::GetCities($message['flocation']['country']);
        }
        $inner_page = "tpl.create.php";
        $js_file = array( 'highlight.min.js', 'highlight.init.js', 'mailer.js', 'attachedfiles.js' );
        $js_file_utf8[] = '/scripts/ckedit/ckeditor.js';
        break;
    case 'report':
        $specs = professions::GetAllGroupsLite();
        $countries = country::GetCountries();
        
        $id = __paramInit('int', 'id');
        $message = $mailer->getMailerById($id);
        
        
        $sum_rec = $mailer->calcSumRecipientsCount($message, array($message['count_rec_emp'], $message['count_rec_frl']));
        if($message['is_digest']) {
            $inner_page = $_SERVER['DOCUMENT_ROOT']."/siteadmin/mailer/digest/tpl.report.php";
        } else {
            $attachedfiles_files = $mailer->getAttach($message['id']);
            $inner_page = "tpl.report.php";
        }
        $js_file    = array( '/css/block/b-shadow/b-shadow.js' );
        break;
    default:
        $count = 0;
        $list_mailer = $mailer->getMailer($page, $filter, $count);
        
        $pages = ceil($count / mailer::LIMIT_MAILER_LIST);
        $inner_page  = "tpl.index.php";
        $js_file = array( 'mailer.js' );
        break;
}

array_push($css_file, "moderation.css", 'new-admin.css');

switch($gAction) {
    case 'create':
        $is_created = true;
        //$css_file[] = '/css/block/b-calendar/b-calendar.css';
    case 'edit':
    default:                
        $css_file[] = '/css/block/b-combo/b-combo.css';
}
$css_file = array('moderation.css','nav.css', '/css/block/b-input-hint/b-input-hint.css' );

$js_file[] = 'uploader.js';    
$menu_item   = 100;
$rpath       = '../../';
$header      = $rpath . 'header.php';
$footer      = $rpath . 'footer.html';

$template    = 'template2.php';


include( $rpath . $template );