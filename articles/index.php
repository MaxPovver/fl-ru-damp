<?php
/**
 * Статьи
 *
 */
$grey_articles = 1;
$g_page_id = "0|29";
$stretch_page = true;
$showMainDiv  = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/articles.php");

$css_file = array( "ai.css", '/css/block/b-menu/_vertical/b-menu_vertical.css' );

$uid = get_uid();

if($_GET['id'] && !$_GET['newurl'] && !$_POST) {
  $article_info = articles::getArticle($_GET['id'], get_uid(false));
  if($article_info['id']) {
    $query_string = preg_replace("/id={$article_info['id']}/", "", $_SERVER['QUERY_STRING']);
    $query_string = preg_replace("/^&/", "", $query_string);
    header ('HTTP/1.1 301 Moved Permanently');
    header ('Location: '.getFriendlyURL("article", $article_info['id']).($query_string ? "?{$query_string}" : ""));
    exit;
  }
}

if (__paramInit('string', null, 'action') == "wysiwygUploadImage") {  	
  	if ($uid) {
        $info = getimagesize($_FILES['wysiwyg_uploadimage']['tmp_name']);
        if ($info['mime'] && strpos($info['mime'], 'shockwave-flash') === false) {
            $cfile = new CFile($_FILES['wysiwyg_uploadimage'], "file");                    
            $fname = $cfile->MoveUploadedFile($_SESSION['login']."/upload");
            if ($cfile->image_size['width'] > articles::MAX_IMAGE_WIDTH || $cfile->image_size['height'] > articles::MAX_IMAGE_HEIGHT) {
                $cfile->Delete($cfile->id);
                echo "status=fileTooBig&msg=Размер изображения превышает максимально допустимый: ".articles::MAX_IMAGE_WIDTH." x ".articles::MAX_IMAGE_HEIGHT;
                exit;
            }
            if ($fname) {
                //добавить данные о файле
                articles::addWysiwygFile($cfile);
                //запомнить идентификатор временного файла
                session_start();                                                                        
                $_SESSION['wysiwyg_inline_files'][$cfile->id] = $cfile->id;
                $link = WDCPREFIX."/users/".substr($_SESSION['login'], 0, 2)."/".$_SESSION['login']."/upload/".$fname;
                echo "status=uploadSuccess&url={$link}";
            }else {                       
                echo "status=uploadFailed&msg=Ошибка загрузки файла";
                exit;
            }
        }else {
            echo "status=wrongFormat&msg=Загрузите изображение формата gif, png или jpg";
        }
    }else echo "status=fail&msg=У вас недостаточно прав, чтобы оставить этот комментарий";
    exit;
  }

$url_parts = parse_url($_SERVER['REQUEST_URI']);
if($_GET['id']) {
  $friendly_url = getFriendlyURL('article', $_GET['id']);
  if(strtolower($url_parts['path'])!=$friendly_url) {
    header ('HTTP/1.1 301 Moved Permanently');
    header ('Location: '.$friendly_url);
    exit;
  }
}

$msgs_on_page = 20;

$mpath = dirname(__FILE__);
$rpath = realpath(dirname(__FILE__) . '/../' );

session_start();
$uid = get_uid();

$_page = __paramInit('string', null, 'page');
if (!$_page) $_page = __paramInit('string', 'page');


$id = __paramInit('int', 'id', 'id');
if(!$_page && $id) $_page = 'view';

$ord = __paramInit('string', 'ord');
$ord = !$ord ? 'date' : $ord;

$page = __paramInit('int', 'p');
if ( !$page ) {
    $page = 1;
    $bPageDefault = true;
} elseif ($page < 1) {
    include(ABS_PATH . '/404.php');
    exit;
}

$GET = $_GET;
foreach($GET as $k => $v) {
    switch($k) {
        case 'ord':
            $v = preg_replace('/(\W+)/si', '', $ord);
            $ord = $v;
            break;
        case 'p':
            $v = $page;
            break;
        case 'id':
            $v = $id;
            break;
    }
    $GET[$k] = $v;
}

if($uid) {
    $_uid = (hasPermissions('articles')) ? null : $uid;
    $articles_unpub = (hasPermissions('articles')) ? articles::ArticlesCount(false, $_uid) : null;
}

$query = array();
if($ord != 'date') {
    $query['ord'] = $ord;
}
if($year) {
    $query['yr'] = $year;
}

$tab = $_page;
$task = __paramInit('string', 'task', 'task');
$pop_tags = articles::getPopularTags();
$page_title = "Статьи - фриланс, удаленная работа на FL.ru";
$page_descr = "Статьи - фриланс, удаленная работа на FL.ru";

if(get_uid()) $is_approved = articles::isApprovedArticles(get_uid());

//var_dump($task, $_page); die();
switch ($task) {
    
    /* Добавление статьи */
    case 'add-article':
        if(!$uid) exit();

        $_POST['title'] = iconv('UTF-8', 'CP1251', $_POST['title']);
        $_POST['short'] = iconv('UTF-8', 'CP1251', $_POST['short']);
        $title = (__paramInit('htmltext', null, 'title', null, articles::ARTICLE_MAX_TITLELENGTH));
        $short = (__paramInit('html', null, 'short'));
//        $msgtext = __paramInit('html', null, 'msgtext');
//        $msgtext = change_q_x($_POST['msgtext'], FALSE, TRUE, "b|div.*?|meta|strong|br\s?\/?|i|em|p|ul|ol|li|s|h[1-6]{1}", false, false);

        $sAdmTags = hasPermissions('articles') ? '<img>' : '';
        //$msgtext = $_POST['msgtext'];
        //$msgtext = strip_tags( $msgtext, '<p><b><strong><i><em><br><ul><li><ol><h1><h2><h3><h4><h5><h6><a><noindex><strike>' . $sAdmTags );
        $msgtext = iconv('UTF-8', 'CP1251', $_POST['msgtext']);
        $msgtext = __paramValue('ckeditor', $msgtext);

        $image = __paramInit('html', null, 'attached');


        if(trim($short) == '') {
            $alert['short'] = 'Поле не должно быть пустым!';
        }
        if(trim($msgtext) == '') {
            $alert['msgtext'] = 'Поле не должно быть пустым!';
        }
        if(trim($title) == '') {
            $alert['title'] = 'Поле не должно быть пустым!';
        }

        if(!$image) {
            $alert['logo'] = 'Необходимо загрузить файл';
        }
        $_POST['kword'] = iconv('UTF-8', 'CP1251', $_POST['kword']);
        $kwords = explode(",", $_POST['kword']);
        $kwords = array_map("trim", $kwords);
        $kwords = array_map( 'antispam', $kwords );
        if(!isset($alert)) {
            //$short = iconv('UTF-8', 'CP1251', $short);
            //$msgtext = iconv('UTF-8', 'CP1251', $msgtext);
            
            $title   = antispam( $title );
            $short   = antispam( $short );
            //$msgtext = antispam( $msgtext );
            
            list($newid, $errs) = articles::AddArticle($uid, $title, $short, $msgtext, $image);
            if(count($kwords) > 0 && $newid) {
                articles::addArticleTags($newid, $kwords);
            }
            if(!$newid) $alert['alert'] = 'Ошибка при добавлении статьи';
        }

        if(isset($alert)) {
            foreach($alert as $k => $msg) {
                $alert[$k] = iconv('CP1251', 'UTF-8', $msg);
            }
            $result['errorMessages'] = $alert;
        } else {
            $result['success'] = true;
            $result['newid'] = $newid;
        }

        echo json_encode($result);
        exit();
        break;

    /* Редактирование статьи */
    case 'edit-article':
        
        if(!hasPermissions('articles')) exit();
        $id = __paramInit('html', null, 'id');

        $article = articles::getArticle($id, $uid);

        $_POST['title'] = iconv('UTF-8', 'CP1251', $_POST['title']);
        $_POST['short'] = iconv('UTF-8', 'CP1251', $_POST['short']);

//        $title = str_replace("\\", "&#92;", $title);
        $title = (__paramInit('htmltext', null, 'title', null, articles::ARTICLE_MAX_TITLELENGTH));
        $short = (__paramInit('html', null, 'short'));
        //$short = change_q_x($short, false, true);
//        $msgtext = __paramInit('html', null, 'msgtext');
//        $msgtext = change_q_x($_POST['msgtext'], FALSE, TRUE, "b|div.*?|meta|strong|br\s?\/?|i|em|p|ul|ol|li|s|h[1-6]{1}", false, false);

        $sAdmTags = hasPermissions('articles') ? '<img>' : '';
        //$msgtext = $_POST['msgtext'];
        //$msgtext = strip_tags( $msgtext, '<p><b><strong><i><em><br><ul><li><ol><h1><h2><h3><h4><h5><h6><a><noindex><strike>' . $sAdmTags );
        $msgtext = iconv('UTF-8', 'CP1251', $_POST['msgtext']);
        $msgtext = __paramValue('ckeditor', $msgtext);
        
        $image = __paramInit('html', null, 'attached');
        $logo = __paramInit('html', null, 'logo');
        $rmlogo = __paramInit('html', null, 'rmlogo');

        $page_view = __paramInit('html', null, 'page_view');

        if(trim($short) == '') {
            $alert['short'] = 'Поле не должно быть пустым!';
        }
        if(trim($msgtext) == '') {
            $alert['msgtext'] = 'Поле не должно быть пустым!';
        }
        if(trim($title) == '') {
            $alert['title'] = 'Поле не должно быть пустым!';
        }
        if(!$image && !$logo) {
            $alert['logo'] = 'Вы должны загрузить изображение!';
        }
        
        if(!$image && $logo) $image = $article['logo'];

        if(!isset($alert)) {
            if($rmlogo && $article['logo'] == $rmlogo) {
                $file = new CFile();
                $file->Delete($article['logo']);
            }

            //$short = iconv('UTF-8', 'CP1251', $short);
            //$msgtext = iconv('UTF-8', 'CP1251', $msgtext);
            
            $title   = antispam( $title );
            $short   = antispam( $short );
            $msgtext = antispam( $msgtext );

            $res = articles::updateArticle($uid, $article['id'], $title, $short, $msgtext, $image);
            $_POST['kword'] = iconv('UTF-8', 'CP1251', $_POST['kword']);
            if(trim($_POST['kword']) != "") {
                $kwords = explode(",", $_POST['kword']);
                $kwords = array_map("trim", $kwords);
                $kwords = array_map( 'antispam', $kwords );
            }
            if(count($kwords) > 0) {
                articles::addArticleTags($article['id'], $kwords);
            } else {
                articles::clearArticleTags($article['id']);
            }
            if(!$res) $alert['alert'] = 'Ошибка при обновлении статьи';
        }

        if(isset($alert)) {
            foreach($alert as $k => $msg) {
                $alert[$k] = iconv('CP1251', 'UTF-8', $msg);
            }
            $result['errorMessages'] = $alert;
        } else {
            $result['success'] = true;
            $article = articles::getArticle($id, $uid);
            if(count($article['kwords']) > 0) {
                foreach($article['kwords'] as $n=>$val) {
                    $article['kwords'][$n]['name'] = iconv('CP1251', 'UTF-8', htmlspecialchars($val['name'])); 
                }
            }
            if($article['logo'])
                $article['logo_url'] = WDCPREFIX . '/' . $article['path'] . $article['fname'];
            
            if($page_view != 'view') {
                $article['short'] = reformat($article['short'], 50, 0, 0, 1);
                $article['short'] = iconv('CP1251', 'UTF-8', $article['short']);
            } else {
                $article['msgtext'] = textWrap(stripslashes($article['msgtext']), 70);
                $article['msgtext'] = iconv('CP1251', 'UTF-8', $article['msgtext']);
            }

            $article['title'] = reformat($article['title'], ($page_view == 'view' ? 59 : 32), 0, 1);
            $article['title'] = iconv('CP1251', 'UTF-8', $article['title']);

            $result['article'] = $article;
            $result['page_view'] = $page_view;
        }

        echo json_encode($result);
        exit();
        break;
    
    /* получение одной статьи (для редактирования)*/
    case 'get-article' :
        $result = array();

        $article = articles::getArticle($id, $uid);

        if($article) {
            if(count($article['kwords'])>0) {
                foreach($article['kwords'] as $val) {
                    $tags[] = $val['name'];
                }
                $article['kword'] = iconv('CP1251', 'UTF-8', implode(", ", $tags));
            }
            $article['short'] = iconv('CP1251', 'UTF-8', html_entity_decode($article['short']));
            $article['short'] = str_replace('&#039;', '\'', $article['short']);
            $article['msgtext'] = iconv('CP1251', 'UTF-8', $article['msgtext']);
            $article['title'] = html_entity_decode($article['title']);
            $article['title'] = str_replace('&#039;', '\'', $article['title']);
            $article['title'] = iconv('CP1251', 'UTF-8', $article['title']);
            if(hasPermissions('articles')) {
                $result['btn_name_save'] = true;
            }
            $result['article'] = $article;
            
            if($article['logo']) {
                $result['attach_url'] = WDCPREFIX . '/' . $article['path'] . $article['fname'];
            }
        }

        echo json_encode($result);

        exit();
        break;


    case 'del-article' :
        if(!hasPermissions('articles')) exit();
        
        $article = articles::getArticle($id, $uid);
        if(!$article) return false;

        if($article['logo']) {
            $file = new CFile();
            $file->Delete($article['logo']);
        }

        $warn = stripslashes(__paramInit('htmltext', null, 'msgtxt'));
        
        articles::delArticle($id, $warn);

        $q = array();
        parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $q);
        if(isset($q['id'])) unset($q['id']);
        
        if ( $q['p'] ) {
            $count = ($q['page'] == 'unpublished') ? articles::ArticlesCount(false) : articles::ArticlesCount();
            
            if ( $count - 1 < (intval($q['p']) - 1) * $msgs_on_page ) {
            	$q['p'] = $q['p'] - 1;
            	
            	if ( $q['p'] < 2 ) {
            		unset($q['p']);
            	}
            }
        }
        echo 
        header('Location: /articles/' . url($GET, $q, true, '?'));
        exit();
        break;

    case 'add-comment':
        if(!$uid) {
            header("Location: /fbd.php");
            die();
        }
        $result = array();
        $result['success'] = false;
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/articles_comments.php");
        $mod = (hasPermissions('articles'))? 0 : 1;
        $comments = new articles_comments();
        
        $tn = 0;
        $msg = change_q_x($_POST['cmsgtext'], FALSE, TRUE, 'b|i|p|ul|ol|li|s|h[1-6]{1}', false, false);
        $reply = __paramInit('int', null, 'reply_to', NULL);
        $order_type = __paramInit('int', null, 'ord');
        $thread = __paramInit('int', 'id');

        if(!$msg || is_empty_html($msg)) {
            $alert[1] = 'Поле не должно быть пустым';
        } else {
            $msg = preg_replace("/(li|ol|ul)>[\n]+/iU", "$1>", $msg);

            $tidy = new tidy();
            $msg = $tidy->repairString(
                str_replace(array(' '), array('&nbsp;'), nl2br($msg) ),
                array('show-body-only' => true, 'wrap' => '0'), 'raw');
            $msg = str_replace("\n", "", $msg);
            $msg = preg_replace("/\h/", " ", $msg);
        }

        $yt_link = $_POST['yt_link'];
        if ($yt_link != '') {
            $v_yt_link = video_validate($yt_link);
            if(!$v_yt_link) {
                $alert[3] = "Неверная ссылка.";
            } else {
                $yt_link = $v_yt_link;
            }
        } else {
            $yt_link = null;
        }

        // загрузка файлов
        $files = array();
        $attach = $_FILES['attach'];
        if (is_array($attach) && !empty($attach['name'])) {
            foreach ($attach['name'] as $key=>$v) {
                if (!$attach['name'][$key] || $key > articles_comments::MAX_FILE_COUNT) continue;
                $files[] = new CFile(array(
                    'name'     => $attach['name'][$key],
                    'type'     => $attach['type'][$key],
                    'tmp_name' => $attach['tmp_name'][$key],
                    'error'    => $attach['error'][$key],
                    'size'     => $attach['size'][$key]
                ));
            }
        }

        list($att, $uperr, $error_flag) = $comments->UploadFiles($files, array('width' => 390, 'height' => 1000, 'less' => 0));
        if($uperr) {
            $alert[2] = $uperr;
        }

        if(!isset($alert)) {
            $new = $comments->Add($uid, $reply, $thread, $msg, $yt_link, $att, getRemoteIP(), $err, $tn);
        }

        if($new) {
            $q = array();
            parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $q);
            $q['goto'] = $new;
            
            header('Location: ./' . url($GET, $q, true, '?'));
//            echo "<script>document.location.href = '{$_SERVER['HTTP_REFERER']}#c_$new';</script>";
            exit();
        }
        break;

    case 'edit-comment':
        if(!$uid) {
            header("Location: /fbd.php");
            die();
        }

        $result = array();
        $result['success'] = false;
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/articles_comments.php");
        $mod = (hasPermissions('articles'));
        $comments = new articles_comments();

        $msg = change_q_x($_POST['cmsgtext'], FALSE, TRUE, 'b|i|p|ul|ol|li|s|h[1-6]{1}', false, false);
        $reply = __paramInit('int', null, 'reply_to', NULL);

        $comment = $comments->getComment($reply);

        if(!$mod && $comment['from_id'] != $uid) {
            header("Location: /fbd.php");
            die();
        }

        if(!$msg || is_empty_html($msg)) {
            $alert[1] = 'Поле не должно быть пустым';
        } else {
            $msg = preg_replace("/(li|ol|ul)>[\n]+/iU", "$1>", $msg);
            
            $tidy = new tidy();
            $msg = $tidy->repairString(
                str_replace(array(' '), array('&nbsp;'), nl2br($msg) ),
                array('show-body-only' => true, 'wrap' => '0'), 'raw');
            $msg = str_replace("\n", "", $msg);
            $msg = preg_replace("/\h/", " ", $msg);
        }
        
        $yt_link = $_POST['yt_link'];
        if ($yt_link != '') {
            $v_yt_link = video_validate($yt_link);
            if(!$v_yt_link) {
                $alert[3] = "Неверная ссылка.";
            } else {
                $yt_link = $v_yt_link;
            }
        } else {
            $yt_link = null;
        }

        // загрузка файлов
        $files = array();
        $attach = $_FILES['attach'];
        if (is_array($attach) && !empty($attach['name'])) {
            foreach ($attach['name'] as $key=>$v) {
                if (!$attach['name'][$key]) continue;
                $files[] = new CFile(array(
                    'name'     => $attach['name'][$key],
                    'type'     => $attach['type'][$key],
                    'tmp_name' => $attach['tmp_name'][$key],
                    'error'    => $attach['error'][$key],
                    'size'     => $attach['size'][$key]
                ));
            }
        }

        list($att, $uperr, $error_flag) = $comments->UploadFiles($files, array('width' => 390, 'height' => 1000, 'less' => 0), $comment['login']);
        if($uperr) {
            $alert[2] = $uperr;
        }

        if(isset($_POST['rmattaches']) && is_array($_POST['rmattaches'])) {
            $comments->removeAttaches($reply, $_POST['rmattaches']);
        }

        if(!isset($alert)) {
            $comments->Update($reply, $uid, $msg, $yt_link, $att, count($_POST['attaches']), $err, $tn);
            
            $q = array();
            parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $q);
            $q['goto'] = $reply;
            
            header('Location: ./' . url($GET, $q, true, '?'));
            exit();
        } 

        break;

    case 'del-comment':
        if(!$uid) exit();
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/articles_comments.php");
        $mod = (hasPermissions('articles'));
        $comments = new articles_comments();
        $comment = $comments->getComment($id);

        if($mod || $comment['from_id'] == $uid) {
            $comments->DeleteComment($id, $uid);
        }

        $q = array();
        parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $q);
        $q['goto'] = $comment['id'];

        header('Location: ./' . url($GET, $q, true, '?'));
//        echo "<script>document.location.href = '{$_SERVER['HTTP_REFERER']}#c_{$comment['id']}';</script>";
        exit();
        break;

    case 'restore-comment':
        if(!$uid) exit();

        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/articles_comments.php");
        $comments = new articles_comments();
        $comment = $comments->getComment($id);

        if(hasPermissions('articles')) {
            $comments->RestoreComment($id, $uid);
        }

        $q = array();
        parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $q);
        $q['goto'] = $comment['id'];

        header('Location: ./' . url($GET, $q, true, '?'));
//        echo "<script>document.location.href = '{$_SERVER['HTTP_REFERER']}#c_{$comment['id']}';</script>";
        exit();
        break;

    case 'approve' :
        if(!hasPermissions('articles') || !$_POST['task']) {
            exit();
        }
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/articles.php");
        
        $id = __paramInit('int', null, 'id');
        if(articles::setApproved($id, $uid)) {
            $q = array();
            parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $q);
            if(isset($q['id'])) unset($q['id']);
            
            if ( $q['p'] ) {
                $count = ($q['page'] == 'unpublished') ? articles::ArticlesCount(false) : articles::ArticlesCount();
                
                if ( $count - 1 < (intval($q['p']) - 1) * $msgs_on_page ) {
                	$q['p'] = $q['p'] - 1;
                	
                	if ( $q['p'] < 2 ) {
                		unset($q['p']);
                	}
                }
            }
            
            header('Location: /articles/' . url($GET, $q, true, '?'));
            exit();
        }

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
        break;
    case 'decline':
        if(!hasPermissions('articles') || !$_POST['task']) {
            exit();
        }
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/articles.php");
        
        $id = __paramInit('int', null, 'id');
        if(articles::setDecline($id, $uid)) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
            $article = articles::getArticle($id);
            
            $adm = new users();
            $adm->getUser('admin');
            
            $text = "Здравствуйте, {$article['uname']}. \r\n\r\n";         
            $text .= "Ваша статья «{$article['title']}» поступила на модерацию в раздел «Статьи и интервью» сайта Free-lance.ru. ";
            $text .= "К сожалению, ее формат не подходит для публикации в этом разделе. \r\n\r\n";
            $text .= "Вы можете опубликовать свою работу в блогах для ознакомления или в своем портфолио по инструкции http://feedback.free-lance.ru/article/details/id/204 . \r\n";
            $text .= "Команда Free-lance.ru благодарит вас за участие в жизни нашего портала. \r\n\r\n";
            $text .= "С уважением, \r\n";
            $text .= "Алена, редактор Free-lance.ru";
            
            messages::Add($adm->uid, $article['login'], addslashes($text));
            
            $q = array();
            parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $q);
            if(isset($q['id'])) unset($q['id']);
            
            if ( $q['p'] ) {
                $count = ($q['page'] == 'unpublished') ? articles::ArticlesCount(false) : articles::ArticlesCount();
                
                if ( $count - 1 < (intval($q['p']) - 1) * $msgs_on_page ) {
                	$q['p'] = $q['p'] - 1;
                	
                	if ( $q['p'] < 2 ) {
                		unset($q['p']);
                	}
                }
            }
            
            header('Location: /articles/' . url($GET, $q, true, '?'));
            exit();
        }
    case 'undecline':
        if(!hasPermissions('articles') || !$_POST['task']) {
            exit();
        }
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/articles.php");
        
        $id = __paramInit('int', null, 'id');
        if(articles::setUnDecline($id, $uid)) {
            $q = array();
            parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $q);
            if(isset($q['id'])) unset($q['id']);
            
            if ( $q['p'] ) {
                $count = ($q['page'] == 'declined') ? articles::ArticlesCount(false) : articles::ArticlesCount();
                
                if ( $count - 1 < (intval($q['p']) - 1) * $msgs_on_page ) {
                	$q['p'] = $q['p'] - 1;
                	
                	if ( $q['p'] < 2 ) {
                		unset($q['p']);
                	}
                }
            }
            
            header('Location: /articles/' . url($GET, $q, true, '?'));
            exit();
        }
        break;
    case 'upload':
        if(!$uid) exit();

        $result = array();
        $result['success'] = false;

        $error_flag = 0;

        $file = new CFile($_FILES['attach']);
        $file->table = 'file';
        if ($file->tmp_name) {
            $file->max_size = articles::ARTICLE_MAX_LOGOSIZE;
            $file->proportional = 1;
            $file->max_image_size = array('width'=>100, 'height'=>100, 'less'=>1, 'prevent_less' => 1);
            $file->allowed_ext = array_diff( $GLOBALS['graf_array'], array('swf') );
            $file->resize = 1;
            $file->proportional = 1;
//            $file->topfill = 1;
            $file->server_root = 1;

            $f_name = $file->MoveUploadedFile("about/articles/");
            if (!isNulArray($file->error)) {
                $alert = "Файл не удовлетворяет условиям загрузки";
                $error_flag = 1;
            }

            $fileid = $file->id;
            $filepath = $file->path;
            $filename = $file->name;
//
//            if(!$error_flag && !$file->img_to_small('sm_' . $f_name, array('width' => 100, 'height' => 100))) {
//                $alert = "Невозможно уменьшить изображение";
//                $error_flag = 1;
//            }
        }

        if(isset($alert)) {
            $result['errorMessage'] = iconv('CP1251', 'UTF-8', $alert);
        } else {
            $result['success'] = true;
            $result['file'] = array(
                'id' => $fileid,
                'path' => $filepath,
                'fname' => $filename
            );
        }

        echo json_encode($result);

        exit();
        break;
}
$js_file_utf8[] = '/scripts/ckedit/ckeditor.js';

switch($_page) {
    case 'new':
        if(!$uid) {
            header('Location: /fbd.php');
            exit();
        }

        $content = 'content_new.php';
        $title   = antispam(__paramInit('html', null, 'title'));
        $short   = antispam(__paramInit('html', null, 'short'));
        $msgtext = antispam(__paramInit('html', null, 'msgtext'));

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            if(trim($short) == '') {
                $alert[1] = 'Поле не должно быть пустым!';
            }
            if(trim($msgtext) == '') {
                $alert[2] = 'Поле не должно быть пустым!';
            }
            if(trim($title) == '') {
                $alert[0] = 'Поле не должно быть пустым!';
            }
            $file = new CFile($_FILES['attach']);
            if ($file->tmp_name) {
                $file->max_size = articles::ARTICLE_MAX_LOGOSIZE;
                $file->proportional = 1;
                $file->max_image_size = array('width'=>100, 'height'=>100, 'less'=>1);
                $file->resize = 1;
                $file->proportional = 1;
    //            $file->topfill = 1;
                $file->server_root = 1;

                $f_name = $file->MoveUploadedFile("about/articles/");
                if (!isNulArray($file->error)) {
                    $alert[3] = "Файл не удовлетворяет условиям загрузки";
                    $error_flag = 1;
                }

                $fileid = $file->id;
                $filepath = $file->path;
                $filename = $file->name;
//
//                if(!$file->img_to_small('sm_' . $f_name, array('width' => 100, 'height' => 100))) {
//                    $alert[3] = "Невозможно уменьшить изображение";
//                    $error_flag = 1;
//                }
            }

            if(!$fileid) {
                $alert[3] = 'Необходимо загрузить файл.';
            }

            if(!isset($alert)) {
                list($e, $errs) = articles::AddArticle($uid, $title, $short, $msgtext, $fileid);
                if($errs) $alert[0] = $errs;
            }
//            var_dump($alert);
            if(!isset($alert)) {
                header('Location: ./?page=unpublished');
                exit();
            }
        }
        break;
    case 'view':
        if(!$id) {
            header('Location: /404.php');
            exit();
        }
        
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
        $stop_words = new stop_words( hasPermissions('articles') );
        
        $content = "content_view.php";
        $article = articles::getArticle($id, $uid);
        $FBShare = array(
            "title"       => htmlspecialchars($article['title'], ENT_QUOTES),
            "description" => "",
            "image"       => HTTP_PREFIX."www.free-lance.ru/images/free-lance_logo.jpg"  
        );
        if(!$article) {
            header('Location: /404.php');
            exit();
        }
        
        if ( $article['approved'] == 't' ) {
            $js_file = array( 'highlight.min.js', 'highlight.init.js', 'comments.all.js', 'banned.js' );
        }

        if ( $article['title'] ) {
            $page_title = "{$article['title']} - фриланс, удаленная работа на FL.ru";
            $page_descr = "{$article['title']} - фриланс, удаленная работа на FL.ru";
        }

        $hidden = array();
        if($article['hidden_threads']) {
            $hidden_db = preg_replace('/[\{\}]/', '', $article['hidden_threads']);
            $hidden = explode(',', $hidden_db);
        }

//        $page = __paramInit('int', 'p');
//        if (!$page) $page = 1;

        if($article['approved'] == 'f' && !hasPermissions('articles') && $article['user_id'] != $uid) {
            include(ABS_PATH . '/404.php');
        }
        
        if ( $article['approved'] == 't' ) {
            $css_file[] = 'hljs.css';
            $css_file[] = 'wysiwyg.css';
        }
        
        $tab = $article['approved'] == 'f' ? 'unpublished' : '';

        if($uid && $article['approved'] == 't') articles::setArticleLVT($uid, $article);

        $dt = $article['approved'] == 't' ? $article['approve_date'] : $article['post_time'];

        $nav = articles::getNavigation($dt, ($article['approved'] == 't'));
        $navigation = array();

        if($nav[1]['pos'] == 1) {
            $navigation['next'] = $nav[1];
        } else {
            $navigation['prev'] = $nav[1];
        }
        if(isset($nav[2])) $navigation['prev'] = $nav[2];


        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/comments/CommentsArticles.php");
        $comments = new CommentsArticles($article['id'], $article['lastviewtime'], array(
            'hidden_threads' => $article['hidden_threads']
        ));
        $comments->tpl_path = $_SERVER['DOCUMENT_ROOT'] . "/classes/comments/";
        $comments_html = $comments->render();

//        echo $comments_html;
//        die();

        break;
    case 'declined':
        if(!$uid || (!hasPermissions('articles')) ) {
            header('Location: /fbd.php');
            exit();
        }
        $content = "content_declined.php";
        $_uid = (hasPermissions('articles')) ? null : $uid;
        $articles = articles::getArticles($page, $msgs_on_page, $uid, 0, false, $_uid, null, true);
        $articles_count = articles::ArticlesCount(false, $_uid, 0, true);

        $pages = ceil($articles_count / $msgs_on_page);
        
        if ( 
            ($articles_count == 0 || $articles_count - 1 < ($page - 1) * $msgs_on_page) && !$bPageDefault 
            || $pages == 1 && !$bPageDefault  && $page != 1 
        ) {
        	include( ABS_PATH . '/404.php' );
            exit;
        }
        break;
    case 'unpublished':
        if(!$uid || (!hasPermissions('articles')) ) {
            header('Location: /fbd.php');
            exit();
        }
        
        $content = "content_unpublished.php";

//        $page = __paramInit('int', 'p');
//        if (!$page) $page = 1;

        $_uid = (hasPermissions('articles')) ? null : $uid;
        $articles = articles::getArticles($page, $msgs_on_page, $uid, 0, false, $_uid, 'unpublic');
        $articles_count = articles::ArticlesCount(false, $_uid);

        $pages = ceil($articles_count / $msgs_on_page);
        
        if ( 
            ($articles_count == 0 || $articles_count - 1 < ($page - 1) * $msgs_on_page) && !$bPageDefault 
            || $pages == 1 && !$bPageDefault  && $page != 1 
        ) {
        	include( ABS_PATH . '/404.php' );
            exit;
        }

        break;
    /* Рейтинг */
    case 'rate':
        if(!$uid) {
            header('Location: /fbd.php');
            exit();
        }
        $is_ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
        $result = array();
        $result['success'] = false;

        $article_id = __paramInit('int', 'id');
        $type = __paramInit('string', 'to');
        $type = $type == 'up' ? 1 : -1;

        $rate_val = articles::setRating($uid, $article_id, $type);
        if($rate_val !== false) {
            $result['success'] = true;
            $result['id'] = $article_id;
            $result['val'] = $type;
            $result['rate_val'] = $rate_val;
        }

        if($is_ajax) {
            echo json_encode($result);
        } else {
            header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/') );
        }
        exit();
        break;

    /* Закладки */
    case 'bookmark':
        if(!$uid && !isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')
            exit();

        $result = array();
        $result['success'] = false;

        $article_id = __paramInit('int', 'id');
        $type = __paramInit('string', 'type');

        if(articles::bookmarkArticle($uid, $article_id, $type)) {
            $result['success'] = true;
            $result['id'] = $article_id;
            $result['type'] = $type-1;
        }
        echo json_encode($result);

        exit();
        break;

    /* Удаление закладки */
    case 'delbm':
        if(!$uid && !isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')
            exit();

        $result = array();
        $result['success'] = false;

        $article_id = __paramInit('int', 'id');

        if($article_id && articles::bookmarkDel($uid, $article_id)) {
            $result['success'] = true;
            $result['id'] = $article_id;
        }
        echo json_encode($result);

        exit();
        break;

    /* Редактирование закладки */
    case 'editbm':
        if(!$uid && !isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')
            exit();

        $result = array();
        $result['success'] = false;

        $article_id = __paramInit('int', null, 'id');
        $type = __paramInit('int', null, 'type');
        $_title = __paramInit('htmltext', null, 'title');
        $title = iconv('UTF-8', 'CP1251', $_title);

        if($article_id && articles::bookmarkEdit($uid, $article_id, $title, $type)) {
            $result['success'] = true;
            $result['id'] = $article_id;
            $result['type'] = $type;
            $result['title'] = iconv('CP1251', 'UTF-8', reformat(stripslashes($title), 19, 0, 1) );
        }
        echo json_encode($result);

        exit();
        break;

    case 'sortbm':
        if(!$uid && !isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')
            exit();

        $result = array();
        $result['success'] = false;

        $type = __paramInit('int', null, 'type');
        switch ($type) {
            case 0:
                $order = 'time';
                break;
            case 1:
                $order = 'priority';
                break;
            case 2:
                $order = 'title';
                break;
            default:
                $order = 'time';
                break;
        }

        if($bookmarks = articles::getBookmarks($uid, $order)) {
            ob_start();

            $is_ajax = true;
            include('part/bookmarks.php');

            $out = ob_get_contents();
            ob_end_clean();

            $result['success'] = true;
            $result['type'] = $type;
//            var_dump($out);
            $result['html'] = iconv('CP1251', 'UTF-8', $out);
        }
        echo json_encode($result);

        exit();
        break;

    case 'comment' :
        $id = __paramInit('int', 'id');
        if(!$id && !$uid && !isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')
            exit();

        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/articles_comments.php");

        $comment = articles_comments::getComment($id);
        $attaches = articles_comments::getAttaches($id, true);

        $result = array();
        $result['success'] = false;

        if(hasPermissions('articles') || $uid == $comment['from_id']) {

            $comment['msgtext'] = preg_replace("/\h/", " ", $comment['msgtext']);
            $tidy = new tidy();
            $comment['msgtext'] = $tidy->repairString(
                str_replace(array(' '), array('&nbsp;'), iconv('CP1251', 'UTF-8', nl2br($comment['msgtext'])) ),
                array('show-body-only' => true, 'wrap' => '0'), 'raw');
            $comment['msgtext'] = preg_replace("/\h/", " ", $comment['msgtext']);
            
            $comment['msgtext'] = preg_replace('/\n?<br\s?\/?>\n?/', "\n", $comment['msgtext']);
            $comment['msgtext'] = html_entity_decode($comment['msgtext']);

            $result['success'] = true;
            $result['data'] = $comment;
            $result['attaches'] = $attaches;
        }
        
        echo json_encode($result);

        exit();
        break;

    default:
        $content = "content_index.php";

//        $page = __paramInit('int', 'p');
//        if (!$page) $page = 1;
        if(isset($_GET['tag'])) {
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/kwords.php");
            $tag_name = kwords::getKeyById(intval($_GET['tag']));
        }
        $articles = articles::getArticles($page, $msgs_on_page, $uid, $_GET['tag'], true, null, $ord);
        $articles_count = articles::ArticlesCount(true, null, $_GET['tag']);

        $order = intval($_COOKIE['bmOrderType']);
        switch ($order) {
            case 0:
                $order = 'time';
                break;
            case 1:
                $order = 'priority';
                break;
            case 2:
                $order = 'title';
                break;
            default:
                $order = 'time';
                break;
        }

        $bookmarks = articles::getBookmarks($uid, $order);
        $authors = articles::getTopAuthors();

        $pages = ceil($articles_count / $msgs_on_page);
        
        if ( 
            ($articles_count == 0 || $articles_count - 1 < ($page - 1) * $msgs_on_page) && !$bPageDefault 
            || $pages == 1 && !$bPageDefault && $page != 1
        ) {
        	include( ABS_PATH . '/404.php' );
            exit;
        }
}

if ( hasPermissions('articles') ) {
    $js_file[] = 'uploader.js';
}

$js_file[] = 'mootools-forms.js';
$js_file[] = 'articles.js';
$js_file[] = 'kwords.js';
$js_file[] = '/kword_js.php';

include ($rpath."/template2.php");