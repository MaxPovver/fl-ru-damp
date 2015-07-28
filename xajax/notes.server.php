<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/notes.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/notes.php");

/**
 * Изменение заметки для страницы просмотра профиля пользователя.
 * Добавление, изменение, удаление
 * 
 * @param  int $login UID пользователя которому пишем заметку
 * @param  string $text Текст заметки
 * @return object xajaxResponse
 */
function saveHeaderNote($login, $text, $rating = 0, $fromProject = false) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
    session_start();
    
	$objResponse = new xajaxResponse();
    $oNotes      = new notes();
    $aNote       = $oNotes->GetNote( $_SESSION['uid'], $login, $error );
    $oUser       = new users;
	$nTargetId   = $oUser->GetUid( $sError, $login );
    
	$text = strip_only( trim($text), '<script>' );
	$text = change_q_x( $text, FALSE, TRUE, "", false, false );
	
	//$objResponse->script("$('zametka').removeClass('zametka-lnk').removeClass('zametka');");
    
    if ( $aNote ) {
    	if ( $text != '' ) {
    	    $error = notes::Update( $_SESSION['uid'], (int)$nTargetId, $text, $rating );
    	    $text = stripslashes( $text );
    	    $objResponse->assign( 'zametkaBD', 'outerHTML', getHeaderNote($login, $text) );
    	}
        else {
            $error = notes::DeleteNote( $_SESSION['uid'], (int)$nTargetId );
            $objResponse->assign('zametkaBD', 'outerHTML', '
                <div id="zametka" class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_inline-block">
                    <span class="b-icon b-icon__cont b-icon__cont_note"></span>
																				<a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_fontsize_13" href="javascript:void(0);" onclick="$(\'zametka_fmr\').toggleClass(\'b-layout_hide\');">Оставить заметку</a>
                </div>
            ');
        }
    }
    elseif ( $text ) {
        $error = notes::Add($_SESSION['uid'], (int)$nTargetId, $text);
        $text = stripslashes( $text );
        $objResponse->assign( 'zametka', 'outerHTML', getHeaderNote($login, $text) );
    }
    $objResponse->script('headerNoteText();');
    // !!! тут нужен htmlspecialchars_decode - эта переменная хранит исходный код заметки который в текстарию подставляется
    $objResponse->script("headerNote = '". input_ref_scr(htmlspecialchars_decode($text)) ."';");
    
    return $objResponse;
}

/**
 * то же что saveHeaderNote, только используется из проектов
 */
function saveHeaderNoteFromProject($login, $text, $rating = 0, $fromProject = false) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
    session_start();
    
	$objResponse = new xajaxResponse();
    $oNotes      = new notes();
    $aNote       = $oNotes->GetNote( $_SESSION['uid'], $login, $error );
    $oUser       = new users;
	$nTargetId   = $oUser->GetUid( $sError, $login );
    
    $text = substr($text, 0, 200);
	$text = strip_only( trim($text), '<script>' );
	$text = change_q_x( $text, FALSE, TRUE, "", false, false );
	
    // если заметка уже есть
    if ($aNote) {
    	if ( $text != '' ) {
    	    $error = notes::Update( $_SESSION['uid'], (int)$nTargetId, $text, $rating );
    	    $text = stripslashes( $text );
    	} else {
            $error = notes::DeleteNote( $_SESSION['uid'], (int)$nTargetId );
        }
    } elseif ($text) {
        $error = notes::Add($_SESSION['uid'], (int)$nTargetId, $text);
        $text = stripslashes($text);
    }
    // экранируем бэкслэши
    $text = str_replace('\\', '\\\\', $text);
    $text = reformat($text, 22, 0, 0, 1, 22);
    $text = str_replace('"', '\"', $text);
    $objResponse->script('$("noteTextBlock").fireEvent("noteSaved", "' . $text . '")');
    
    return $objResponse;
}

/**
 * Генерирует HTML код заметки для страницы просмотра профиля пользователя.
 * 
 * @param  int $login UID пользователя которому пишем заметку
 * @param  string $text Текст заметки
 * @return string HTML код
 *
 * код удаления заметки
 *                           <a href="javascript:void(0);" onclick="if(confirm(\'Вы действительно хотите удалить заметку?\')){xajax_saveHeaderNote(\'' . $login . '\',\'\');}">
 *                              <img src="/images/btn-remove2.png" width="11" height="11" alt="" />
 *                           </a>
 */
function getHeaderNote( $login, $text ) {
    return '
        <div class="bBD" id="zametkaBD">
            <div id="zametka" class="b-layout b-layout_pad_10 b-layout_bord_ffeda9 b-layout_bordrad_1 b-fon_bg_fff9bf_hover b-layout_hover">
				<a class="b-icon b-icon__edit b-icon_float_right b-layout_hover_show" href="javascript:void(0);" onclick="headerNoteForm();"></a>
				<div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">Ваша заметка</div>
				<div class="b-layout__txt b-layout__txt_fontsize_11">' . reformat($text, 24, 0, 0, 1, 24) . '</div>
            </div>
        </div>
    ';
}

/**
 * Функция вызывающая форму редактирования и добавления заметки
 *
 * @param integer $uid ИД пользователя которому пишем заметку
 * @return object xajaxResponse; @see xajax
 */
function getNotesForm($uid, $to_uid = false, $type=1) {
    session_start();
    if($to_uid !== false && $_SESSION['uid'] != $uid) return false;
    include_once $_SERVER['DOCUMENT_ROOT'].'/classes/notes.php';
    
    if($to_uid !== false) {
        $note = notes::GetNoteInt($uid, $to_uid, $error);
        $data = array("uid" => $to_uid) + $note;  
        $spec_uid = $to_uid;  
    } else {
        $data = array("uid" => $uid);
        $spec_uid = $uid;
    }
    
	$objResponse = new xajaxResponse();
	
	$data['n_text'] = html_entity_decode($data['n_text'], ENT_QUOTES);
   
    if($type == 100) {    
        $objResponse->script("$('note_user').setStyle('display','block');");   
        $objResponse->script("$('note_userid').set('value', '{$data['uid']}');");
        $objResponse->assign("notesTxt", "innerText",  "".$data['n_text']);
        $objResponse->script("$('note_action').set('value', '". ( $data['login']?"upd":"add" ) ."');");  
    } elseif ($type == 101) {
        $objResponse->script("$('note_user').removeClass('b-shadow_hide');");
        $objResponse->script("$('note_userid').set('value', '{$data['uid']}');");
        $objResponse->assign("notesTxt", "value",  $data['n_text']);
        $objResponse->script("$('note_action').set('value', '". ( $data['login']?"upd":"add" ) ."');");          
    } else {
        $objResponse->script("if($('elm-offset-{$spec_uid}-{$type}')){
	                         $('noteFormContent').destroy();
                             var noteFormContent = new Element('span#noteFormContent');
                             $('elm-offset-{$spec_uid}-{$type}').adopt(noteFormContent); 
                          }");
	
	
        $objResponse->assign("noteFormContent", "innerHTML",  notes::getNotesForm($data, $type));
    
        $objResponse->script("$$('#ov-izbr-2').setStyle('display','block');");
        $objResponse->script("
            $$('.izbr-choose li a').addEvent('click', function(){
                this.getParent('li').getParent('.izbr-choose').getElements('li').removeClass('active');
                this.getParent('li').addClass('active');
                return false;
            });");
    }
    return $objResponse;    
}

/**
 * Добавление, редактирование заметки пользователя
 *
 * @param integer $uid     ИД пользователя которому пишем заметку
 * @param string  $text    Текст заметки
 * @param integer $rating  Тип заметки (1 - положительная, 0 - нейтральная, -1 - отрицательная)
 * @param string  $act     действие с заметкой (upd - обновление, add - добавление)
 * @return object xajaxResponse; @see xajax
 */
function addNotes($uid, $text, $rating, $act, $type) {
    session_start();
    include_once $_SERVER['DOCUMENT_ROOT'].'/classes/notes.php';
    $objResponse = new xajaxResponse();
    $text = trim($text);
    
    if($text == "") {
        $objResponse->script("alert('Заполните заметку')");    
        return $objResponse; 
    }
    
    // Режем тег <script>
	$text = strip_only(trim($text),'<script>');
	//$text = stripslashes($text);
	$text = change_q_x($text, FALSE, TRUE, "", false, false);
    if(strlen($text) > 200)
        $text = substr($text, 0, 200);
        
    $note = array("n_text" => ($text));
    $rec['uid'] = $uid;
    ob_start();
    include TPL_DIR_NOTES."/tpl.notes-textitem.php";
    $html = ob_get_clean();
   
    
    
    switch($rating) {
        /*case  1: 
            $objResponse->script("$$('.userFav_{$uid}').getParent('div').addClass('fs-g');");
            break;
        case  0:
            $objResponse->script("$$('.userFav_{$uid}').getParent('div').removeClass('add-z');");
            break;
        case -1:
            $objResponse->script("$$('.userFav_{$uid}').getParent('div').addClass('fs-p');");
            break;*/
        default:
            $rating = 0;
            //$objResponse->script("$$('.userFav_{$uid}').getParent('div').addClass('fs-o');");
            break;
    }
    
    if($uid>0 && strlen(trim($text)) > 0 && $act=="add") {
        notes::Add($_SESSION['uid'], $uid, $text, $rating);
    } else {
        notes::Update($_SESSION['uid'], $uid, $text, $rating);
    }
    $html = str_replace("'", "\'", $html);
    if($type == 100) {
        $html = '<strong class="b-note__bold">Ваша заметка :</strong> '.reformat($text, 30, 0, 0, 1, 30);
        $objResponse->script("$('note_{$uid}').set('html', '$html');");
        $objResponse->script("$('note_user').setStyle('display','none');");
    } elseif ($type == 101) {
        $html = '<strong class="b-note__bold">Ваша заметка :</strong> '.reformat($text, 30, 0, 0, 1, 30);
        $objResponse->script("$('note_{$uid}').set('html', '$html');");
        $objResponse->script("$('note_user').addClass('b-shadow_hide');");
    } else {
    // Удаляем все классы
        $objResponse->script("$$('.userFav_{$uid}').getParent('div').removeClass('add-z').removeClass('fs-g').removeClass('fs-p');");
        $objResponse->script("$$('.userFav_{$uid}').set('html', '$html');");
        $objResponse->script("$('ov-izbr-2').destroy();");
    }
    return $objResponse;  
}

function EditNote($login, $action, $text, $rating = 0) {
    session_start();
    $objResponse = new xajaxResponse();
    $nuid = get_uid(false);

    //$text = str_replace('&', '&amp;', $text);

    //$text = stripslashes($text);
	$text = strip_only(trim($text),'<script>');
    $text = change_q_x($text, FALSE, TRUE, "", false, false);
	// !! кол-во символов также указано в /scripts/note.js
    if(strlen($text) > 200)
        $text = substr($text, 0, 200);

    switch ($action) {
        case "add":
            if ($text)
                $error = notes::Add($nuid, $login, $text, 0, "?");
            break;
        case "update":
            if ($text) {
                $error = notes::Update($nuid, $login, $text, $rating, "?");
            } else {
                $error = notes::DeleteNote($nuid, $login, "?");
                $action = 'delete';
            }
            break;
    }
    if ($error) return false;

    $text_src = input_ref_scr(stripslashes($text));
    $text_src = str_replace('&', '&amp;', $text_src);
    $text = reformat($text, 54, 0, 0, 1, 54);
	//$text = addslashes($text);

    switch ($action) {
        case 'add':
        case 'update':
            if(is_empty_html($text)) {
                $s = "
                    document.getElement('div.form-templ').setStyle('display', 'none');
                    document.getElement('div.form-templ input').set('disabled', false);
                    cancelNote();
                ";
                break;
            }
            $s = "
                n = $('note_{$login}');
                n.getElement('.uprj-note-cnt>p').set('html', '$text');
                n.setStyle('display', 'block');

                document.getElement('div.form-templ').setStyle('display', 'none');
                document.getElement('div.form-templ input').set('disabled', false);

                if($('team_{$login}')) $('team_{$login}').getElement('.uprj-st3').setStyle('display', 'none');
                cancelNote();
            ";
            break;
        case 'delete':
            $s = "
                n = $('note_{$login}');
                n.getElement('.uprj-note-cnt>p').set('html', '');
                n.setStyle('display', 'none');

                if($('team_{$login}')) $('team_{$login}').getElement('.uprj-st3').setStyle('display', 'inline-block');
                document.getElement('div.uprj-note.form-templ').store('action', false);
                cancelNote();
            ";
            break;
    }
    $objResponse->script($s);
    
    return $objResponse;
}

/**
 * Удаление заметки пользователя
 *
 * @param integer $uid      ИД пользователя владельца заметки
 * @param inetger $to_uid   ИД пользователя на кого написана заметка
 * @return object xajaxResponse; @see xajax
 */
function delNote($uid, $to_uid, $type) {
    session_start();
	if($_SESSION['uid'] != $uid) return false; 
	
	include_once $_SERVER['DOCUMENT_ROOT'].'/classes/notes.php';
    $objResponse = new xajaxResponse();
	
    notes::DeleteNote($uid, $to_uid);
    if($type == 100) {
        $objResponse->script("$$('.userFav_{$to_uid}').destroy();");
    } else {
        $html = '<div class="sent-mark"><a href="javascript:void(0)" onclick="xajax_getNotesForm('.$to_uid.', false, '.$type.')">Оставить заметку</a>&nbsp;<span></span></div>';
        // $objResponse->script("$$('.userFav_{$to_uid}').getParent('div').removeClass('fs-g').removeClass('fs-p').addClass('add-z');");
        // $objResponse->script("$$('.userFav_{$to_uid}').getParent('div').removeClass('fs-o').addClass('add-z');");
        $objResponse->script("$$('.userFav_{$to_uid}').set('html', '$html');");
    }
    //$objResponse->script("$('ov-izbr-2').destroy();"); // Если удаляем с формы
    return $objResponse;  
}

function GetNote($login) {
    session_start();
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
    $oUser       = new users;
	$nTargetId   = $oUser->GetUid( $sError, $login );
    
    $objResponse = new xajaxResponse();
    $nuid = get_uid(false);

    $note = notes::GetNoteInt($nuid, $nTargetId, $err);
    $text = htmlspecialchars_decode($note['n_text']);
    $text = str_replace("&#039;", "'", $text);

    $s = "
        $('note_rating').set('value', '{$note['rating']}');
        f = document.getElement('div.uprj-note.form-templ');
        f.getElements('input,textarea').set('disabled', false);
    ";
    $objResponse->script($s);
    $objResponse->assign('f_n_text', 'value', $text);
    return $objResponse;
}

$xajax->processRequest();
?>