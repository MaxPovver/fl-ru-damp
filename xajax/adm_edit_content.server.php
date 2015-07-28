<?php
/**
 * Редатирование пользовательского контента модератором
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */

require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/adm_edit_content.common.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );

session_start();
get_uid(false);

/**
 * Редактирование Личные сообщения
 * 
 * @param  string $sId идентификатор записи
 * @param  int $nEdit флаг редактирования
 * @param  array $aForm данные формы редактирования
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  string $sParams JSON кодированные дополнительные параметры с UID отправителя
 * @return xajaxResponse 
 */
function admEditContacts( $sId = '', $nEdit = 0, $aForm = array(), $sDrawFunc = '', $sParams = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        list( $rec_id, $rec_type ) = explode( '_', $sId );
        
        $aParams = _jsonArray( $sParams );
        
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) {
            if ( !_admEditBeforeStreams($objResponse, $nEdit, $aForm, $aParams, user_content::MODER_MSSAGES, $sId, $sDrawFunc) ) {
                return $objResponse;
            }
        }
        
        if ( $nEdit ) {
            _admEditContactsSaveForm( $objResponse, $rec_id, $rec_type, $aForm, $sDrawFunc );
        }
        else {
            if (is_array($aParams) && isset($aParams['from_id']) && !empty($aParams['from_id']) ) {
                _admEditContactsParseForm( $objResponse, $rec_id, $rec_type, $aParams );
            }
        }
    }
    
    return $objResponse;
}

/**
 * Сохранение Личные сообщения
 * 
 * @param object $objResponse xajaxResponse
 * @param string $rec_id идентификатор записи
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditContactsSaveForm( &$objResponse, $rec_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    
    $bValid = true;
    $msg   = change_q_x( $aForm['msg_text'], false, true, null, false, false );
    
    $attachedfiles_session = change_q_x( $aForm['attachedfiles_session'], true );
    $attachedfiles         = new attachedfiles( $attachedfiles_session );
    $attachedfiles_info    = $attachedfiles->calcFiles();
    $attachedfiles_files   = $attachedfiles->getFiles(array(1,3,4));
    
    if ( (!$msg || trim($msg) == '') && !$attachedfiles_info['count'] ) {
        $bValid = false;
        $objResponse->script( "$('adm_edit_err_msg').set('html', 'Поле заполнено некорректно.'); ");
        $objResponse->script( "$('div_adm_edit_err_msg').setStyle('display', '');" );
    } 
    elseif ( $msg && strlen($msg) > messages::MAX_MSG_LENGTH ) {
        $bValid = false;
        $objResponse->script( "$('adm_edit_err_msg').set('html', 'Вы ввели слишком большое сообщение. Текст сообщения не должен превышать 20 000 символов.'); ");
        $objResponse->script( "$('div_adm_edit_err_msg').setStyle('display', '');" );
    }
    
    $sParent = ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) ? 'parent.' : '';
    
    if ( $bValid ) {
        $sReason = _parseReason( $aForm['p_from_id'], $aForm['adm_edit_text'] );
        messages::Update( $aForm['p_from_id'], $_SESSION['uid'], $rec_id, $msg, $attachedfiles_files, $sReason );
        $attachedfiles->clear();
        messages::messageModifiedNotification( $aForm['p_from_id'], $aForm['to_id'], $msg, $sReason );
        $objResponse->script( $sParent.'adm_edit_content.cancel();' );
        
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) { // если случаев будет больше - вынести в отдельную функцию
            if ( $sDrawFunc != 'stream1' ) {
                resolveContent( user_content::MODER_MSSAGES, $aForm['p_stream_id'], user_content::MODER_MSSAGES .'_'. $rec_id .'_0', 1, $aForm['p_from_id'], $aForm['p_content_cnt'], $aForm['p_status'], $aForm['p_is_sent'], '', $objResponse );
            }
            else {
                $objResponse->script( 'window.location.reload(true)' );
            }
        }
        elseif ( $sDrawFunc == 'blocked' ) { // из админки "заблокированные"
            global $user_content;
            
            $user_content->resolveMessages( $_SESSION['uid'], $aForm['p_from_id'], $rec_id, 0, 1 );
            $objResponse->script( "$('my_div_content_{$rec_id}').destroy();" );
            $objResponse->script('user_content.spinner.hide();');
        }
        else { // действие после редактирования по умолчанию
            $objResponse->script( 'window.location.reload(true)' );
        }
    }
    else {
        $objResponse->script("{$sParent}adm_edit_content.disabled = false; {$sParent}adm_edit_content.button();");
    }
}

/**
 * Отдает HTML для Редактирование Личные сообщения
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $rec_id идентификатор записи
 * @param  string $rec_type тип записи
 * @param  array $aParams дополнительные параметры с UID отправителя. остальные - опционально
 * @return string
 */
function _admEditContactsParseForm( &$objResponse, $rec_id = '', $rec_type = '', $aParams = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    
    $msg = messages::Get( $aParams['from_id'], $rec_id );
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/contacts.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // аттачи
    $sAttach = getAttachedFilesJs( $msg['files'], messages::MAX_FILES, messages::MAX_FILE_SIZE, 'contacts' );
    
    // текст
    $sOnReady = "if(document.getElementById('adm_edit_msg')) document.getElementById('adm_edit_msg').value = ($('adm_edit_msg_source')? $('adm_edit_msg_source').value : null);";
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', 'Редактировать сообщение' );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    $objResponse->script( $sAttach );
    $objResponse->script( $sOnReady );
    $objResponse->script( "$('div_adm_reason').setStyle('display', 'none');" );
    $objResponse->script( "adm_edit_content.editMenuItems = ['', 'Сообщение', 'Файлы'];" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_MSSAGES .');');
}

/**
 * Редактирование Блоги: посты и комментарии
 * 
 * @param  string $sId идентификатор записи
 * @param  int $nEdit флаг редактирования
 * @param  array $aForm данные формы редактирования
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  string $sParams JSON кодированные дополнительные параметры
 * @return xajaxResponse 
 */
function admEditBlogs( $sId = '', $nEdit = 0, $aForm = array(), $sDrawFunc = '', $sParams = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('blogs') ) {
        list( $rec_id, $rec_type ) = explode( '_', $sId );
        $aParams                   = _jsonArray( $sParams );
        
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) {
            if ( !_admEditBeforeStreams($objResponse, $nEdit, $aForm, $aParams, user_content::MODER_BLOGS, $sId, $sDrawFunc) ) {
                return $objResponse;
            }
        }
        
        if ( $nEdit ) {
            _admEditBlogsSaveForm( $objResponse, $rec_id, $rec_type, $aForm, $sDrawFunc );
        }
        else {
            _admEditBlogsParseForm( $objResponse, $rec_id, $rec_type, $aParams );
        }
    }
    
    return $objResponse;
}

/**
 * Сохранение Блоги: посты и комментарии
 * 
 * @param object $objResponse xajaxResponse
 * @param string $rec_id идентификатор записи
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditBlogsSaveForm( &$objResponse, $rec_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/blogs.php' );
    
    $mod            = hasPermissions('blogs') ? 0 : 1;
    $alert          = array();
    $olduserlogin   = $aForm['olduserlogin'];                 // логин автора блога
    $close_comments = $aForm['close_comments'] ? 't' : 'f';   // запретить комментирование
    $is_private     = $aForm['is_private']     ? 't' : 'f';   // показывать только мне
    $ontop          = $aForm['ontop']          ? 't' : 'f';   // закрепить тему наверху
    $categ          = change_q_x( $aForm['category'], true ); // раздел
    $close_comments = $rec_type == 2 ? 'n' : $close_comments;
    $is_private     = $rec_type == 2 ? 'n' : $is_private;
    list( $gr, $t ) = explode( '|', $categ );
    
    // название, текст
    if ( strlen($aForm['msg']) > blogs::MAX_DESC_CHARS ) {
        $error_flag = 1;
        $alert[1]   = 'Максимальный размер сообщения '. blogs::MAX_DESC_CHARS .' символов!';
    }
    
    $msg  = change_q_x(antispam($aForm['msg']), false, false, 'b|br|i|p|ul|li|cut|s|h[1-6]{1}', false, false);
    $name = substr_entity(change_q_x(antispam($aForm['name']), true, false), 0, 96, true);
    
    // ссылка на youtube
    $yt_link = $aForm['yt_link'] ? $aForm['yt_link'] : '';

    if ( $yt_link != '' ) {
        $v_yt_link = video_validate( $yt_link );
        
        if ( !$v_yt_link ) {
            $alert[4]   = 'Неверная ссылка.';
        } 
        else {
            $yt_link = $v_yt_link;
        }
    }
    
    // опросы 
    $question = substr_entity(change_q_x( antispam( trim((string) $aForm['question']) ), false, false, ''), 0, blogs::MAX_POLL_CHARS, true);
    $multiple = (bool) $aForm['multiple'];
    $answers  = array();
    $answers_exists = array();
    $i = 0;
    
    if ( is_array($aForm['answers']) && !empty($aForm['answers']) ) {
        foreach ( $aForm['answers'] as $pa ) {
            if ( trim((string) $pa) !== '' ) {
                $answers[] = substr_entity(change_q_x(antispam( preg_replace('/&/','&amp;',(string) trim($pa)) ), false, false, ''), 0, blogs::MAX_POLL_ANSWER_CHARS * 2, true);
                $i++;
            }
        }
    }
    
    if ( is_array($aForm['answers_exists']) && !empty($aForm['answers_exists']) ) {
        foreach ( $aForm['answers_exists'] as $key => $pa ) {
            if (trim((string) $pa) !== '') {
                $answers_exists[$key] = substr_entity(change_q_x(antispam( preg_replace('/&/','&amp;',(string) trim($pa)) ), false, false, ''), 0, blogs::MAX_POLL_ANSWER_CHARS * 2, true);
                $i++;
            }
        }
    }
    
    if ( $i > 0 && $question === '' ) {
        $alert[5] = 'Введите текст вопроса';
    } else if ( $i > blogs::MAX_POLL_ANSWERS ) {
        $alert[5] = 'Вы можете указать максимум ' . blogs::MAX_POLL_ANSWERS . ' отетов';
    } else if ( $i < 2 && $question !== '' ) {
        $alert[5] = 'Нужно указать минимум 2 варианта ответа в голосовании';
    }
    
    // файлы 
    $files_session = $aForm['attachedfiles_session'];
    
    if ( !$files_session ) {
        $attachedfiles = new attachedfiles( '', true );
        $asid = $attachedfiles->createSessionID();
        $attachedfiles->addNewSession( $asid );
        $files_session = $asid;
    } 
    else {
        $attachedfiles = new attachedfiles( $files_session );
        $asid = $files_session;
    }
    
    $files_info = $attachedfiles->calcFiles();
    
    if ( $msg === '' && $question === '' && empty($alert[5]) && !$files_info['count'] && $yt_link === '' ) {
        $alert[1] = 'Сообщение не должно быть пустым';
    }
    
    if ( !$alert ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        
        $blogs = new blogs();
        
        $blogs->Edit( $_SESSION['uid'], $rec_id, $msg, $name, array(), getRemoteIP(), $err, $mod, '', 
            $gr, $t , array(), $olduserlogin, $yt_link, $close_comments, $is_private, $ontop, null, 
            $question, $answers, $answers_exists, $multiple 
        );
        
        $files = $attachedfiles->getFiles(array(1,3,4));
        $blogs->addAttachedFiles( $files, $rec_id, $olduserlogin, false ); 
        $attachedfiles->clear();
        
        $sReason = _parseReason( $aForm['user_id'], $aForm['adm_edit_text'] );
        messages::blogModifiedNotification( $rec_type, $aForm['oldusertitle'], $aForm['post_time'], $aForm['user_name'], $aForm['user_surname'], $olduserlogin, $sReason );
        
        $content_id = user_content::MODER_BLOGS;
        
        _admEditAfterAll( $objResponse, $content_id, $rec_id, $rec_type, $sDrawFunc, $aForm );
    }
    else {
        _setErrors( $objResponse, $alert, array(1 => 'msg', 4 => 'yt_link', 5 => 'question'), $sDrawFunc );
    }
}

/**
 * Отдает HTML для Редактирование Блоги: посты и комментарии
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $rec_id идентификатор записи
 * @param  string $rec_type тип записи
 * @param  array $aParams дополнительные параметры. остальные
 * @return string
 */
function _admEditBlogsParseForm( &$objResponse, $rec_id = '', $rec_type = '', $aParams = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/blogs.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    
    $error    = $perm = null;
    $blogs    = new blogs();
    $edit_msg = $blogs->GetMsgInfo( $rec_id, $error, $perm );
    $groups   = $blogs->GetThemes( $error, 1 );
    $answers  = $edit_msg['poll'] ? $edit_msg['poll'] : array( array('id' => 0, 'answer' => '') );
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/blogs.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // аттачи
    $sAttach = getAttachedFilesJs( blogs::getAttachedFiles($rec_id), blogs::MAX_FILES, blogs::MAX_FILE_SIZE, 'blog' );
    
    // текст блога и опрос
    $sOnReady = "if (document.getElementById('adm_edit_question')) {
        document.getElementById('adm_edit_question').value = document.getElementById('adm_edit_question_source').value;
        if(document.getElementById('adm_edit_msg')) 
            document.getElementById('adm_edit_msg').value = ($('adm_edit_msg_source')? $('adm_edit_msg_source').value : null);
        poll.init('Blogs', document.getElementById('div_adm_edit'), ". blogs::MAX_POLL_ANSWERS .", '". $_SESSION['rand'] ."');
        maxChars('adm_edit_question', 'adm_edit_question_warn', ". blogs::MAX_POLL_CHARS .");
    }
    else {
        if(document.getElementById('adm_edit_msg')) 
            document.getElementById('adm_edit_msg').value = ($('adm_edit_msg_source')? $('adm_edit_msg_source').value : null);
    }";
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', 'Редактировать ' . ($rec_type == '2' ? 'комментарий' : 'сообщение') );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    $objResponse->script( "$('div_adm_reason').setStyle('display', 'none');" );
    $objResponse->script( "adm_edit_content.editMenuItems = ['', 'Основное', 'Файлы'" . ($rec_type == 1 ? ", 'Опрос'" : '')."];" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( $sAttach );
    $objResponse->script( $sOnReady );
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_BLOGS .');');
}

/**
 * Редактирование постов и комментариев в сообществах
 * 
 * @param  string $sId идентификатор записи
 * @param  int $nEdit флаг редактирования
 * @param  array $aForm данные формы редактирования
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  string $sParams JSON кодированные дополнительные параметры
 * @return xajaxResponse 
 */
function admEditCommunity( $sId = '', $nEdit = 0, $aForm = array(), $sDrawFunc = '', $sParams = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    $aParams     = _jsonArray( $sParams );
    
    if ( hasPermissions('communes') ) {
        list( $rec_id, $rec_type ) = explode( '_', $sId );
        
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) {
            if ( !_admEditBeforeStreams($objResponse, $nEdit, $aForm, $aParams, user_content::MODER_COMMUNITY, $sId, $sDrawFunc) ) {
                return $objResponse;
            }
        }
        
        if ( $rec_type == 1 ) { // посты
            if ( $nEdit ) {
                _admEditCommunityPostSaveForm( $objResponse, $rec_id, $rec_type, $aForm, $sDrawFunc );
            }
            else {
                _admEditCommunityPostParseForm( $objResponse, $rec_id, $rec_type, $aParams );
            }
        }
        else { // комментарии
            if ( $nEdit ) {
                _admEditCommunityCommSaveForm( $objResponse, $rec_id, $rec_type, $aForm, $sDrawFunc );
            }
            else {
                _admEditCommunityCommParseForm( $objResponse, $rec_id, $rec_type, $aParams );
            }
        }
    }
    
    return $objResponse;
}

/**
 * Сохранение поста в сообществах
 * 
 * @param object $objResponse xajaxResponse
 * @param string $rec_id идентификатор записи
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditCommunityPostSaveForm( &$objResponse, $rec_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    // инициализация
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/commune.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    
    $aForm['title']          = antispam( change_q_x($aForm['title'], true, false) );
    $aForm['title']          = substr( $aForm['title'], 0 , 256 );
    $aForm['title']          = $aForm['title'] === false ? '' : $aForm['title'];
    $aForm['youtube_link']   = change_q_x( $aForm['youtube_link'], false, true, null, false, false );
    $aForm['question']       = trim( change_q_x($aForm['question'], true) );
    $aForm['close_comments'] = isset( $aForm['close_comments'] ) ? $aForm['close_comments'] : 0;
    $aForm['is_private']     = isset( $aForm['is_private'] )     ? $aForm['is_private']     : 0;
    $aForm['answers']        = is_array( $aForm['answers'] )        ? $aForm['answers']        : array();
    $aForm['answers_exists'] = is_array( $aForm['answers_exists'] ) ? $aForm['answers_exists'] : array();
    $question                = change_q_x_a( antispam(preg_replace('/&/','&amp;', $aForm['question'])), false, false, '' );
    $multiple                = (bool) $aForm['multiple'];
    $answers                 = array();
    $answers_exists          = array();
    $acount                  = 0;
    $alert                   = array();
    $attachedfiles           = new attachedfiles( $aForm['attachedfiles_session'] );
    
    if ( commune::IS_NEW_WYSIWYG ) {
        $aForm['msgtext'] = __paramValue( 'ckedit', antispam($aForm['msgtext']) );
        $aForm['msgtext'] = stripslashes( $aForm['msgtext'] );
    } 
    else {
        $aForm['msgtext'] = __paramValue( 'wysiwyg_tidy', antispam($aForm['msgtext']) );
    }
    
    if ( $aForm['answers'] && is_array($aForm['answers']) ) {
        foreach ( $aForm['answers'] as $key=> $answer ) {
            if ( ($t = substr_entity( change_q_x_a(antispam( preg_replace('/&/','&amp;',trim((string) $answer)) ) , false, false, ''), 0, commune::POLL_ANSWER_CHARS_MAX * 2, true )) != '' ) {
                $answers[] = $t;
                ++$acount;
            } 
            else {
                unset($aForm['answers'][$key]);
            }
        }
    }
    
    if ( $aForm['answers_exists'] && is_array($aForm['answers_exists']) ) {
        foreach ( $aForm['answers_exists'] as $key => $answer ) {
            if ( intval($key) && ($t = substr_entity(change_q_x_a(antispam( preg_replace('/&/','&amp;',trim((string) $answer)) ) , false, false, ''), 0, commune::POLL_ANSWER_CHARS_MAX * 2, true)) != '' ) {
                $answers_exists[intval($key)] = $t;
                ++$acount;
            }
        }
    }
    
    if ( strlen_real($question) > commune::POLL_QUESTION_CHARS_MAX ) {
        $len      = strlen( $question );
        $rlen     = strlen_real( $question );
        $question = substr( $question, 0, $len - ($rlen - commune::POLL_QUESTION_CHARS_MAX) );
    }
    
    // валидация
    if( strlen($_POST['title']) > commune::MSG_TITLE_MAX_LENGTH ) {
        $alert[1] = 'Количество символов превышает допустимое ('.commune::MSG_TITLE_MAX_LENGTH.')';
    }
    
    if ( $aForm['youtube_link'] != '' ) {
        if ( $video = video_validate($aForm['youtube_link']) ) {
            $aForm['youtube_link'] = $video;
        } 
        else {
            $alert[2] = 'Неверная ссылка';
        }
    }
    
    if ( $acount > 0 && $question == '' ) {
        $alert[3] = 'Введите текст вопроса';
    } 
    elseif ( $acount > commune::POLL_ANSWERS_MAX && $question != '' ) {
        $alert[3] = 'Вы можете указать максимум '.commune::POLL_ANSWERS_MAX.' ответов';
    } 
    elseif ( $acount < 2 && $question != '' ) {
        $alert[3] = 'Нужно указать минимум 2 варианта ответа';
    }
    
    $files_info = $attachedfiles->calcFiles();
    
    if ( is_empty_html($aForm['msgtext']) && $question == '' && empty($alert) && !$files_info['count'] && $aForm['youtube_link'] == '' ) {
        $alert[4] = 'Поле заполнено некорректно';
        $aForm['msgtext'] = '';
    }
    elseif ( strlen($aForm['msgtext']) > commune::MSG_TEXT_MAX_LENGTH ) {
        $alert[4] = 'Количество символов превышает допустимое';
    }
    
    if ( !$alert ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        
        commune::CreateMessage( $aForm, $aForm['commune_id'], $_SESSION['uid'], $rec_id, null, $question, $answers, $answers_exists, $multiple );
        commune::DeleteMarkedAttach( $rec_id );

        // прикрепленные файлы
        $attachedfiles_files = $attachedfiles->getFiles( array(1, 3, 4) );
        commune::addAttachedFiles( $attachedfiles_files, $rec_id, $aForm['user_login'], false );
        $attachedfiles->clear();
        
        // !!!TODO: https://beta.free-lance.ru/mantis/view.php?id=19174

        $sReason = _parseReason( $aForm['user_id'], $aForm['adm_edit_text'] );
        messages::communityModifiedNotification( $rec_id, $rec_type, $aForm['user_login'], $aForm['user_uname'], $aForm['user_usurname'], $sReason );

        $content_id = user_content::MODER_COMMUNITY;

        _admEditAfterAll( $objResponse, $content_id, $rec_id, $rec_type, $sDrawFunc, $aForm );
    }
    else {
        _setErrors( $objResponse, $alert, array(1 => 'title', 2 => 'youtube_link', 3 => 'question', 4 => 'msg'), $sDrawFunc );
    }
}

/**
 * Отдает HTML для Редактирование поста в сообществах
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $rec_id идентификатор записи
 * @param  string $rec_type тип записи
 * @param  array $aParams дополнительные параметры с UID отправителя. остальные - опционально
 * @return string
 */
function _admEditCommunityPostParseForm( &$objResponse, $rec_id = '', $rec_type = '', $aParams = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/commune.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    
    $mess = commune::GetMessage( intval($rec_id) );
    $answers       = $mess['answers'] ? $mess['answers'] : array( array('id' => 0, 'answer' => '') );
    $sub_cat       = commune::getCategories( $mess['commune_id'], true );
    $uid           = $_SESSION['uid'];
    $id            = $mess['commune_id'];
    $top_id        = $rec_id;
    $site          = 'Topic';
    $reloc         = __commShaolin($error, $comm, $top, $restrict_type, $user_mod);
    $is_comm_admin = $user_mod & ( commune::MOD_COMM_ADMIN | commune::MOD_COMM_MODERATOR );
    $is_author     = $user_mod & ( commune::MOD_COMM_AUTHOR );
    
    $_SESSION['wysiwyg_inline_files'] = array(); // !!!TODO: что то сделать, когда будут готовы новые сообщества
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/community.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // аттачи 
    $sAttach = getAttachedFilesJs( commune::getAttachedFiles($rec_id), commune::MAX_FILES, commune::MAX_FILE_SIZE, 'commune' );
    
    // опрос
    $sOnReady = "
        if(document.getElementById('adm_edit_msg')) document.getElementById('adm_edit_msg').value = ($('adm_edit_msg_source')? $('adm_edit_msg_source').value : null);
        parent.window['adm_edit_ckeditor'] = CKEDITOR.replace('adm_edit_msg');
        document.getElementById('adm_edit_question').value = document.getElementById('adm_edit_question_source').value;
        poll.init('Blogs', document.getElementById('div_adm_edit'), ". commune::POLL_ANSWERS_MAX .", '". $_SESSION['rand'] ."');
        maxChars('adm_edit_question', 'adm_edit_question_warn', ". commune::POLL_ANSWER_CHARS_MAX .");";
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', 'Редактировать сообщение' );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    $objResponse->script( "$('div_adm_reason').setStyle('display', 'none');" );
    $objResponse->script( "adm_edit_content.editMenuItems = ['', 'Основное', 'Файлы', 'Опрос'];" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( $sAttach );
    $objResponse->script( $sOnReady );
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_COMMUNITY .');');
}

/**
 * Сохранение комментария в сообществах
 * 
 * @param object $objResponse xajaxResponse
 * @param string $rec_id идентификатор записи
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditCommunityCommSaveForm( &$objResponse, $rec_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/comments/CommentsCommune.php' );
    
    $attachedfiles = new attachedfiles( $aForm['attachedfiles_session'] );
    $oComments     = new CommentsCommune();
    $attachedfiles_files = $attachedfiles->getFiles( array(1, 3, 4) );
    $oComments->addAttachedFiles( $attachedfiles_files, $rec_id, $aForm['user_login'] );
    $attachedfiles->clear();
    
    $sReason = _parseReason( $aForm['user_id'], $aForm['adm_edit_text'] );
    messages::communityModifiedNotification( $rec_id, $rec_type, $aForm['user_login'], $aForm['user_uname'], $aForm['user_usurname'], $sReason, $aForm['parent_id'] );
    
    $content_id = user_content::MODER_COMMUNITY;
    
    _admEditAfterAll( $objResponse, $content_id, $rec_id, $rec_type, $sDrawFunc, $aForm );
}

/**
 * Отдает HTML для Редактирование комментария в сообществах
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $rec_id идентификатор записи
 * @param  string $rec_type тип записи
 * @param  array $aParams дополнительные параметры с UID отправителя. остальные - опционально
 * @return string
 */
function _admEditCommunityCommParseForm( &$objResponse, $rec_id = '', $rec_type = '', $aParams = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/comments/CommentsCommune.php' );
    
    // получение данных комментария
    $oComments = new CommentsCommune();
    $mess      = $oComments->getData( $rec_id );
    $aModel    = $oComments->model();
    
    $mess['parent_id'] = $mess['parent_id2'];
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/comments.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // аттачи 
    $aAttach = _getCommentFilesIds( $mess, $aModel );
    $sAttach = getAttachedFilesJs( $aAttach, TComments::MAX_FILE_COUNT, TComments::MAX_FILE_SIZE, 'commune' );
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', 'Редактировать комментарий' );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    $objResponse->script( "$('div_adm_reason').setStyle('display', 'none');" );
    $objResponse->script( "adm_edit_content.editMenuItems = ['', 'Файлы'];" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( $sAttach );
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_COMMUNITY .');');
}

/**
 * Редактирование проектов и конкурсов
 * 
 * @param  string $sId идентификатор записи
 * @param  int $nEdit флаг редактирования
 * @param  array $aForm данные формы редактирования
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  string $sParams JSON кодированные дополнительные параметры
 * @return xajaxResponse 
 */
function admEditProjects( $sId = '', $nEdit = 0, $aForm = array(), $sDrawFunc = '', $sParams = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        list( $rec_id, $rec_type ) = explode( '_', $sId );
        $aParams                   = _jsonArray( $sParams );
        
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) {
            if ( !_admEditBeforeStreams($objResponse, $nEdit, $aForm, $aParams, user_content::MODER_PROJECTS, $rec_id.'_0', $sDrawFunc) ) {
                return $objResponse;
            }
        }
        
        if ( $nEdit ) {
            _admEditProjectsSaveForm( $objResponse, $rec_id, $rec_type, $aForm, $sDrawFunc );
        }
        else {
            _admEditProjectsParseForm( $objResponse, $rec_id, $rec_type, $aParams );
        }
    }
    
    return $objResponse;
}

/**
 * Сохранение проектов и конкурсов
 * 
 * @param object $objResponse xajaxResponse
 * @param string $rec_id идентификатор записи
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditProjectsSaveForm( &$objResponse, $rec_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    
    $alert  = array();
    $tmpPrj = new tmp_project( $aForm['temp_key'] );
    $prj    = $tmpPrj->init( 2 );
    $kind   = isset( $aForm['kind'] ) ? intvalPgSql($aForm['kind']) : $prj['kind'];
    $sLink  =  change_q_x($aForm['link'], false, true, null, false, false);
    
    if ( !empty($sLink) ) {
        if ( !preg_match('/http:\/\//', $sLink) ) {
            $sLink = 'http://' . $sLink;
        }
    }
    
    $tmpPrj->setProjectField( 'kind', $kind );
    $tmpPrj->setProjectField( 'descr', antispam(change_q_x($aForm['descr'], FALSE, TRUE, "", false, false)) );
    $tmpPrj->setProjectField( 'name',  substr(antispam(change_q_x($aForm['name'], TRUE)), 0, 512) );
    setlocale( LC_ALL, 'en_US.UTF-8' ); 
    $tmpPrj->setProjectField( 'cost',        isset( $aForm['cost'] )        ? floatval($aForm['cost'])           : 0 );
    $tmpPrj->setProjectField( 'currency',    isset( $aForm['currency'] )    ? intvalPgSql($aForm['currency'])    : 0 );
    $tmpPrj->setProjectField( 'folder_id',   isset( $aForm['folder_id'] )   ? intvalPgSql($aForm['folder_id'])   : 0 );
    $tmpPrj->setProjectField( 'budget_type', isset( $aForm['budget_type'] ) ? intvalPgSql($aForm['budget_type']) : 0 );
    $tmpPrj->setProjectField( 'priceby',     isset( $aForm['priceby'] )     ? intvalPgSql($aForm['priceby'])     : 0 );
    $tmpPrj->setProjectField( 'agreement',   isset( $aForm['agreement'] )   ? intvalPgSql($aForm['agreement'])   : 0 );
    $tmpPrj->setProjectField( 'country',     isset( $aForm['country'] )     ? intvalPgSql($aForm['country'])     : 0 );
    $tmpPrj->setProjectField( 'city',        isset( $aForm['pf_city'] )     ? intvalPgSql($aForm['pf_city'])     : 0 );
    $tmpPrj->setProjectField( 'pro_only',    isset( $aForm['pro_only'] )    ? 't' : 'f');
    $tmpPrj->setProjectField( 'is_color',    isset( $aForm['is_color'] )    ? 't' : 'f');
    $tmpPrj->setProjectField( 'is_bold',     isset( $aForm['is_bold'] )     ? 't' : 'f');
    $tmpPrj->setProjectField( 'link',        $sLink );
    
    if ( $kind == 7 ) {
        $tmpPrj->setProjectField('end_date', change_q_x($aForm['end_date'], TRUE) );
        $tmpPrj->setProjectField('win_date', change_q_x($aForm['win_date'], TRUE) );
    }
    
    // разделы
    $c  = $aForm['categories'];
    $sc = $aForm['subcategories'];
    
    if ( empty($c) || (sizeof($c)==1 && $c[0] == 0) ) {
        $alert[3] = 'Не выбран раздел';
    } 
    else {
        $cats = array();
        
        foreach ( $c as $sKey => $value ) { 
            if ( $value == 0 ) continue;
            $check[] = $value."_".$sc[$sKey];
        }
        
        $uniq = array_unique( $check );

        foreach ( $uniq as $val ) {
            list( $cat, $subcat ) = explode( '_', $val );
            $check_array[$cat][] = $subcat;
        }

        foreach ( $check_array as $k=>$val ) {
            if ( count($val) > 1 && (array_search(0, $val) !== false) ) {
                $cats[] = array( 'category_id' => $k, 'subcategory_id' => 0 );
                unset($check_array[$k]);
            } 
            else {
                foreach($val as $m=>$v) {
                    $cats[] = array('category_id' => $k, 'subcategory_id' => $v);    
                }
            }
        }

        $tmpPrj->setCategories( $cats );
    }
    
    $prj = $tmpPrj->getProject();
    $descr_limit = 5000;
    
    if ( $prj['cost'] < 0 ) $alert[7] = 'Введите положительную сумму';
    if ( $prj['cost'] > 999999 ) $alert[7] = 'Слишком большая сумма';
    if ( $prj['cost'] > 0 && ($prj['currency'] < 0 || $prj['currency'] > 3) ) $alert[7] = 'Валюта не определена';
    if ( is_empty_html($prj['name']) ) $alert[1] = 'Поле не заполнено';
    if ( is_empty_html($prj['descr']) ) $alert[2] = 'Поле не заполнено';
    if ( strlen_real($prj['descr']) > $descr_limit ) $alert[2] = "Исчерпан лимит символов ($descr_limit)";
    
    if ( $prj['kind'] == 7 ) {
        if ( !preg_match("/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/", $prj['end_date'], $o1) || !checkdate($o1[2], $o1[1], $o1[3]) )
            $alert[5] = 'Неправильная дата';
        
        if ( !preg_match("/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/", $prj['win_date'], $o2) || !checkdate($o2[2], $o2[1], $o2[3]) )
            $alert[6] = 'Неправильная дата';
        
        if ( !$alert[5] && mktime(0, 0, 0, $o1[2], $o1[1], $o1[3]) <= mktime(0, 0, 0) )
            $alert[5] = 'Дата окончания конкурса не может находиться  в прошлом';
        
        if ( !$alert[6] && mktime(0, 0, 0, $o2[2], $o2[1], $o2[3]) <= mktime(0, 0, 0, $o1[2], $o1[1], $o1[3]) )
            $alert[6] = 'Дата определения победителя должна быть больше даты окончания конкурса';
    }
    /*elseif ( $prj['kind'] == 4 && ($prj['country'] == 0 || $prj['city'] == 0) ) {
        $alert[4] = 'Укажите местонахождение';
    }*/
    
    if ( isset($aForm['top_ok']) ) {
        $nDays = intval( $aForm['top_days'] );
        
        if ( ctype_digit($aForm['top_days']) && $nDays > 0 ) {
            $tmpPrj->setAddedTopDays( $nDays );
        }
        else {
            $alert[8] = 'Укажите корректное количество дней нверху';
        }
    }
    else {
        $tmpPrj->setAddedTopDays( 0 );
    }
    
    if ( !isset($alert[8]) && isset($aForm['logo_ok']) ) {
        if ( empty($aForm['logo_id']) ) {
            $alert[8] = 'Необходимо выбрать файл';
        }
    }
    
    if ( isset($aForm['del_logo']) ) {
        $tmpPrj->delLogo();
    }
    
    if ( !$alert ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        
        $attachedfiles = new attachedfiles( $aForm['attachedfiles_session'] );
        $attachedfiles_files = $attachedfiles->getFiles( array(1, 3, 4) );
        $tmpPrj->addAttachedFiles( $attachedfiles_files );
        $attachedfiles->clear();
        
        $sError = $tmpPrj->saveProject( $prj['user_id'], $prj );
        
        if ( !$sError ) {
            if ( $prj['agreement'] == 1 || $prj['cost'] == 0 ) {
                projects::updateBudget( $rec_id, 0, 0, 0, true );
            }
            else {
                projects::updateBudget( $rec_id, $prj['cost'], $prj['currency'], $prj['priceby'], false );
            }
            
            $sReason = _parseReason( $aForm['user_id'], $aForm['adm_edit_text'] );
            messages::projectsModifiedNotification( $rec_id, $rec_type, $aForm['user_login'], $aForm['user_uname'], $aForm['user_usurname'], $sReason );

            $content_id = user_content::MODER_PROJECTS;

            _admEditAfterAll( $objResponse, $content_id, $rec_id, $rec_type, $sDrawFunc, $aForm );
        }
        else {
            $objResponse->alert( $sError );
            $sParent = ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) ? 'parent.' : '';
            $objResponse->script("{$sParent}adm_edit_content.disabled = false; {$sParent}adm_edit_content.button();");
        }
    }
    else {
        _setErrors( $objResponse, $alert, array(1 => 'name', 2 => 'descr', 3 => 'categories', 4 => 'country', 5 => 'end_date', 6 => 'win_date', 7 => 'cost', 8 => 'paid'), $sDrawFunc );
    }
}

/**
 * Отдает HTML для Редактирование проектов и конкурсов
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $rec_id идентификатор записи
 * @param  string $rec_type тип записи
 * @param  array $aParams дополнительные параметры с UID отправителя. остальные - опционально
 * @return string
 */
function _admEditProjectsParseForm( &$objResponse, $rec_id = '', $rec_type = '', $aParams = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/city.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/country.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    
    $sTmpKey = md5( uniqid($_SESSION['uid']) );
    $tmpPrj  = new tmp_project( $sTmpKey );
    $prj     = $tmpPrj->init( 1, $rec_id );
    $tmpPrj->fix();
    
    // $aFolders   = projects::getUserFolders( $prj['user_id'] ); // папки
    $remTPeriod = $tmpPrj->getRemainingTopPeriod( $remTD, $remTH, $remTM, $remtverb ); // закрепление
    
    // страны и города
    $countries = country::GetCountries();
    
    if( $prj['country'] ) {
        $cities = city::GetCities( $prj['country'] );
    }
    
    // разделы
    $categories  = professions::GetAllGroupsLite();
    $professions = professions::GetAllProfessions();
    array_group( $professions, 'groupid' );
    $professions[0] = array();
    $project_categories = new_projects::getSpecs( $rec_id );
    
    if ( empty($project_categories) ) {
        $project_categories[] = array('category_id' => 0, 'subcategory_id' => 0 );
    }
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/projects.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // текст
    $sOnReady = "if(document.getElementById('adm_edit_descr')) 
        document.getElementById('adm_edit_descr').value = ($('adm_edit_descr_source')? $('adm_edit_descr_source').value : null);";
        
    // аттачи 
    $sAttach = getAttachedFilesJs( projects::GetAllAttach($rec_id), tmp_project::MAX_FILE_COUNT, tmp_project::MAX_FILE_SIZE, 'project' );
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', 'Редактировать ' . ($rec_type == '7' ? 'конкурс' : 'проект') );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    $objResponse->script( "$('div_adm_reason').setStyle('display', 'none');" );
    $objResponse->script( "adm_edit_content.editMenuItems = ['', 'Основное', 'Файлы', 'Платные услуги'];" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( $sAttach );
    $objResponse->script( $sOnReady );
    $objResponse->script("var mx = new MultiInput('adm_edit_professions','category_line'); mx.init();");
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_PROJECTS .');');
    
    // для конкурса даты окончания и определения победителей
    if ( $prj['kind'] == 7 ) {
        $objResponse->script( "new tcal ({ 'formname': 'adm_edit_frm', 'controlname': 'adm_edit_end_date', 'iconId': 'end_date_btn', 'clickEvent': function(){ adm_edit_content.hideError('end_date'); } });" );
        $objResponse->script( "new tcal ({ 'formname': 'adm_edit_frm', 'controlname': 'adm_edit_win_date', 'iconId': 'win_date_btn', 'clickEvent': function(){ adm_edit_content.hideError('win_date'); } });" );
    }
}

/**
 * Редактирование предложений по проектам и конкурсам
 * 
 * @param  string $sId идентификатор записи
 * @param  int $nEdit флаг редактирования
 * @param  array $aForm данные формы редактирования
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  string $sParams JSON кодированные дополнительные параметры
 * @return xajaxResponse 
 */
function admEditPrjOffers( $sId = '', $nEdit = 0, $aForm = array(), $sDrawFunc = '', $sParams = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    $aParams     = _jsonArray( $sParams );
    
    if ( hasPermissions('projects') ) {
        list( $rec_id, $rec_type ) = explode( '_', $sId );
        
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) {
            if ( !_admEditBeforeStreams($objResponse, $nEdit, $aForm, $aParams, user_content::MODER_PRJ_OFFERS, $sId, $sDrawFunc) ) {
                return $objResponse;
            }
        }
        
        if ( $rec_type == 7 ) { // предложения по конкурсам
            if ( $nEdit ) {
                _admEditContestOfferSaveForm( $objResponse, $rec_id, $rec_type, $aForm, $sDrawFunc );
            }
            else {
                _admEditContestOfferParseForm( $objResponse, $rec_id, $rec_type, $aParams );
            }
        }
        else { // предложения по проектам
            if ( $nEdit ) {
                _admEditPrjOfferSaveForm( $objResponse, $rec_id, $rec_type, $aForm, $sDrawFunc );
            }
            else {
                _admEditPrjOfferParseForm( $objResponse, $rec_id, $rec_type, $aParams );
            }
        }
    }
    
    return $objResponse;
}

/**
 * Сохранение предложения по конкурсам
 * 
 * @param object $objResponse xajaxResponse
 * @param string $rec_id идентификатор записи
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditContestOfferSaveForm( &$objResponse, $rec_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/contest.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
    
    $comm_blocked = isset( $aForm['comm_blocked'] );
    $comment      = change_q_x( antispam(substr($aForm['descr'], 0, 30000)), false, true, 'b|br|i|p|ul|li|cut|h[1-6]{1}', false, false );
    
    $contest = new contest( $aForm['p_project_id'], $aForm['p_user_id'], false, false, true, ($aForm['p_is_pro'] == 't') );
    $contest->ChangeOffer( $aForm['id'], $comment, $aForm['files'], $comm_blocked );
    
    $sReason = _parseReason( $aForm['user_id'], $aForm['adm_edit_text'] );
    messages::contestOfferModifiedNotification( $rec_id, $aForm['p_project_id'], $aForm['user_login'], $aForm['user_uname'], $aForm['user_usurname'], $sReason );
    
    $content_id = user_content::MODER_PRJ_OFFERS;
    
    _admEditAfterAll( $objResponse, $content_id, $rec_id, $rec_type, $sDrawFunc, $aForm );
}

/**
 * Отдает HTML для Редактирование предложения по конкурсам
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $rec_id идентификатор записи
 * @param  string $rec_type тип записи
 * @param  array $aParams дополнительные параметры с UID отправителя. остальные - опционально
 * @return string
 */
function _admEditContestOfferParseForm( &$objResponse, $rec_id = '', $rec_type = '', $aParams = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/contest.php' );
    
    $contest = new contest( $aParams['project_id'], $aParams['user_id'], false, false, true, ($aParams['is_pro'] == 't') );
    $edit = $contest->GetOffer( $rec_id );
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/contest_offers.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // аттачи -----------------------------------
    $sAttach = 'files = [];';
    
    if ($contest->offer['attach']) { 
        $sAttach .= 'i = 0;';
        
        foreach ($contest->offer['attach'] as $file) { 
            $sAttach .= "files[i++] = {
                filename: '{$file['fname']}',
                displayname: '".addslashes($file['orig_name'])."',
                preview: '{$file['prev_fname']}',
                time: '".date('Добавлено d.m.Y в H:i', strtotime($file['modified']))."',
                dir: '{$file['upload_login']}',
                fileID: 'o{$file['id']}'
            };";
        }
	}
    
    $sAttach .= 'time_limit = '. ini_get('max_input_time') .';';
	$sAttach .= "iboxes = new IBoxes('/projects/upload.php', 'ps_attach', {uid: '".$contest->offer['user_id']."', action: 'add_pic', pid: '{$aParams['project_id']}', u_token_key: _TOKEN_KEY} );";
	$sAttach .= "boxes = new Boxes(document.getElementById('ca-iboxes'), files, 15);";
	$sAttach .= "boxes.path = '".WDCPREFIX."/users/{$contest->offer['login']}/upload/';";
	$sAttach .= "boxes.WDCPERFIX = '".WDCPREFIX."';";
	$sAttach .= 'boxes.add();';
    //-------------------------------------------
    
    // текст
    $sOnReady = "if(document.getElementById('adm_edit_descr')) 
        document.getElementById('adm_edit_descr').value = ($('adm_edit_descr_source')? $('adm_edit_descr_source').value : null);";
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', 'Редактировать конкурсную работу' );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    $objResponse->script( "$('div_adm_reason').setStyle('display', 'none');" );
    $objResponse->script( "adm_edit_content.editMenuItems = ['', 'Основное', 'Файлы'];" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( $sAttach );
    $objResponse->script( $sOnReady );
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_PRJ_OFFERS .');');
}

/**
 * Сохранение предложения по проектам
 * 
 * @param object $objResponse xajaxResponse
 * @param string $rec_id идентификатор записи
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditPrjOfferSaveForm( &$objResponse, $rec_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects_offers.php' );
    
    $obj_offer   = new projects_offers();
    $payed_items = $aForm['ps_payed_items'];
    $payed_color = ($aForm['ps_payed_items'][0] == '1'); 
    
    if ( $aForm['is_color'] && !$payed_color ) {
        $account = new account;
        $transaction_id = $account->start_transaction(get_uid());
        $error_buy = $account->Buy($billing_id, $transaction_id, $answers->color_op_code, get_uid(), "Выделение ответа на проект цветом", "Выделение <a href='". (getFriendlyURL("project", $project['id'])) . "#freelancer_{$_SESSION['uid']}' target='_blank'>предложения</a> цветом", 1, 1);
        $payed_items = '1';
        
        if ( $error_buy ) {
            $aForm['is_color'] = false;
            $payed_items = '0';
        }
    }
    
    $sReason = _parseReason( $aForm['user_id'], $aForm['adm_edit_text'] );
    $error   = $obj_offer->AddOffer($aForm['user_id'], $aForm['pid'], $aForm['ps_cost_from'], $aForm['ps_cost_to'], $aForm['ps_cost_type'],
        $aForm['ps_time_from'], $aForm['ps_time_to'], $aForm['ps_time_type'],  antispam(stripslashes($aForm['ps_text'])),
        $aForm['ps_work_1_id'], $aForm['ps_work_2_id'], $aForm['ps_work_3_id'],
        $aForm['ps_work_1_link'], $aForm['ps_work_2_link'], $aForm['ps_work_3_link'],
        $aForm['ps_work_1_name'], $aForm['ps_work_2_name'], $aForm['ps_work_3_name'],
        $aForm['ps_work_1_pict'], $aForm['ps_work_2_pict'], $aForm['ps_work_3_pict'],
        $aForm['ps_work_1_prev_pict'], $aForm['ps_work_2_prev_pict'], $aForm['ps_work_3_prev_pict'],
        isset($aForm['ps_for_customer_only']), $aForm['edit'], 0, isset($aForm['prefer_sbr']), $aForm['is_color'], null, $payed_items, 0, $_SESSION['uid'], $sReason 
    );

    if ( !$error && !$error_buy && !$payed_color && $account ) {
        $account->commit_transaction( $transaction_id, get_uid(), $billing_id );
        $is_payed_color = true;
    }
    
    if ( $error ) {
        $objResponse->alert('Ошибка сохранения предложения'.$error);
        $sParent = ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) ? 'parent.' : '';
        $objResponse->script("{$sParent}adm_edit_content.disabled = false; {$sParent}adm_edit_content.button();");
    }
    else {
        messages::prjOfferModifiedNotification( $aForm['user_id'], $aForm['pid'], $sReason );
        
        $content_id = user_content::MODER_PRJ_OFFERS;
        
        _admEditAfterAll( $objResponse, $content_id, $rec_id, $rec_type, $sDrawFunc, $aForm );
    }
}

/**
 * Отдает HTML для Редактирование предложения по проектам
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $rec_id идентификатор записи
 * @param  string $rec_type тип записи
 * @param  array $aParams дополнительные параметры с UID отправителя. остальные - опционально
 * @return string
 */
function _admEditPrjOfferParseForm( &$objResponse, $rec_id = '', $rec_type = '', $aParams = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/account.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/portfolio.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects_offers.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects_offers_answers.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects_offers_dialogue.php' );
    
    $offer       = projects_offers::GetPrjOfferById( $rec_id );
    $dialogue    = new projects_offers_dialogue();
    $user        = new users;
    $account     = new account;
    $portfolio   = new portfolio();
    $professions = professions::GetSelFilProf( $offer['user_id'] );
    $professions = $professions ? $professions : array();
    $cur_prof    = $professions ? $professions[0]['id'] : 0;
    $op_sum      = projects_offers_answers::COLOR_FM_COST;
    
    if ( !($portf_works = $portfolio->GetPortfProf($offer['user_id'], $cur_prof)) ) {
        $portf_works = array();
    }
    
    $user->GetUserByUID( $offer['user_id'] );
    $account->GetInfo( $offer['user_id'] );
    
    $offer['dialogue'] = $dialogue->GetDialogueForOffer( $offer['id'] );
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/projects_offers.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // текст
    $sOnReady = "if(document.getElementById('adm_edit_descr')) document.getElementById('adm_edit_descr').value = ($('adm_edit_descr_source')? $('adm_edit_descr_source').value : null);";
    
    // работы -----------------------------------
    $offer['portfolio_work_1_id'] = $offer['portf_id1'];
    $offer['portfolio_work_2_id'] = $offer['portf_id2'];
    $offer['portfolio_work_3_id'] = $offer['portf_id3'];

    $offer['portfolio_work_1'] = $offer['pict1'];
    $offer['portfolio_work_2'] = $offer['pict2'];
    $offer['portfolio_work_3'] = $offer['pict3'];

    $offer['portfolio_work_1_prev_pict'] = $offer['prev_pict1'];
    $offer['portfolio_work_2_prev_pict'] = $offer['prev_pict2'];
    $offer['portfolio_work_3_prev_pict'] = $offer['prev_pict3'];

    $offer['cost_from'] = round( $offer['cost_from'] , 2 );
    $offer['cost_to']   = round( $offer['cost_to'] , 2 );
    
    $use    = array();
    $sWorks = 'adm_edit_content.works_ids   = new Array();
        adm_edit_content.works_names = new Array();
        adm_edit_content.works_prevs = new Array();
        adm_edit_content.works_picts = new Array();
        adm_edit_content.works_links = new Array();';
    
    if ( $portf_works ) {
        foreach ( $portf_works as $key => $value ) { 
            $use[$value['id']] = 1; 
            $sWorks .= "adm_edit_content.works_ids[{$value['id']}] = '{$value['id']}';
                adm_edit_content.works_names[{$value['id']}] = '". htmlspecialchars(addslashes(trim($value['name']))) ."';
                adm_edit_content.works_prevs[{$value['id']}] = '". trim($value['prev_pict']) ."';
                adm_edit_content.works_picts[{$value['id']}] = '". trim($value['pict']) ."';
                adm_edit_content.works_links[{$value['id']}] = '". trim($value['link']) ."';";
        }
    }

    for ( $i=1; $i < 4; $i++ ) { 
        if ( $user_offer['portf_id'.$i] > 0 && !isset($use[$user_offer['portf_id'.$i]]) ) {
            $sId     = $user_offer['portf_id'.$i];
            $sWorks .= "adm_edit_content.works_ids[$sId] = '$sId';
                adm_edit_content.works_prevs[$sId] = '". trim($user_offer['prev_pict'.$i]) ."';
                adm_edit_content.works_picts[$sId] = '". trim($user_offer['pict'.$i]) ."';";
        } 
    }
    
    if ( $offer['portfolio_work_1'] != '' ) { $sWorks .= "adm_edit_content.prjOfferAddWork({$offer['portfolio_work_1_id']}, '{$offer['portfolio_work_1']}', '{$offer['portfolio_work_1_prev_pict']}');"; } 
    if ( $offer['portfolio_work_2'] != '' ) { $sWorks .= "adm_edit_content.prjOfferAddWork({$offer['portfolio_work_2_id']}, '{$offer['portfolio_work_2']}', '{$offer['portfolio_work_2_prev_pict']}');"; } 
    if ( $offer['portfolio_work_3'] != '' ) { $sWorks .= "adm_edit_content.prjOfferAddWork({$offer['portfolio_work_3_id']}, '{$offer['portfolio_work_3']}', '{$offer['portfolio_work_3_prev_pict']}');"; } 
    //-------------------------------------------
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', 'Редактировать предложения по проекту' );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    $objResponse->script( "$('div_adm_reason').setStyle('display', 'none');" );
    $objResponse->script( "adm_edit_content.editMenuItems = ['', 'Основное', 'Файлы'];" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( "adm_edit_content.userLogin = '{$user->login}';" );
    $objResponse->script( $sOnReady );
    $objResponse->script( $sWorks );
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_PRJ_OFFERS .');');
}

/**
 * Отдает список работ пользователя для прикрепления к предложению 
 * 
 * @param int $prof_id ID профессии
 * @param int $user_id UID пользователя
 * @return xajaxResponse 
 */
function admEditPrjOffersLoadWorks( $prof_id = 0, $user_id = 0 ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/portfolio.php' );

        $obj_portfolio = new portfolio();
        $portf_works   = $obj_portfolio->GetPortfProf( $user_id, $prof_id );
        $js_works      = "adm_edit_content.works_ids = new Array();\n";
        $js_works     .= "adm_edit_content.works_names = new Array();\n";
        $js_works     .= "adm_edit_content.works_prevs = new Array();\n";
        $js_works     .= "adm_edit_content.works_picts = new Array();\n";
        $js_works     .= "adm_edit_content.works_links = new Array();\n";
        $sSelect       = '<select id="adm_edit_works" name="works" class="b-select__select b-select__select_width_220" tabindex="300">';

        foreach ( $portf_works as $key => $value ) {
            $sSelect  .= '<option value="'. $value['id'] .'">'. $value['name'] .'</option>';
            $js_works .= "adm_edit_content.works_ids[" . $value['id'] . "] = '" . $value['id'] . "';\n";
            $js_works .= "adm_edit_content.works_names[" . $value['id'] . "] = '" . htmlspecialchars(trim(addslashes($value['name']))) . "';\n";
            $js_works .= "adm_edit_content.works_prevs[" . $value['id'] . "] = '" . $value['prev_pict'] . "';\n";
            $js_works .= "adm_edit_content.works_picts[" . $value['id'] . "] = '" . $value['pict'] . "';\n";
            $js_works .= "adm_edit_content.works_links[" . $value['id'] . "] = '" . $value['link'] . "';\n";
        }

        $sSelect .= '</select>';

        $objResponse->script( $js_works );
        $objResponse->assign( 'adm_edit_works_div', 'innerHTML', $sSelect );
    }
    
    return $objResponse;
}


/**
 * Редактирование Комментарии к статьям
 * 
 * @param  string $sId идентификатор записи
 * @param  int $nEdit флаг редактирования
 * @param  array $aForm данные формы редактирования
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  string $sParams JSON кодированные дополнительные параметры
 * @return xajaxResponse 
 */
function admEditArtCom( $sId = '', $nEdit = 0, $aForm = array(), $sDrawFunc = '', $sParams = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('articles') || hasPermissions('comments') ) {
        list( $rec_id, $rec_type ) = explode( '_', $sId );
        $aParams = _jsonArray( $sParams );
        
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) {
            if ( !_admEditBeforeStreams($objResponse, $nEdit, $aForm, $aParams, user_content::MODER_ART_COM, $sId, $sDrawFunc) ) {
                return $objResponse;
            }
        }
        
        if ( $nEdit ) {
            _admEditArtComSaveForm( $objResponse, $rec_id, $rec_type, $aForm, $sDrawFunc );
        }
        else {
            _admEditArtComParseForm( $objResponse, $rec_id, $rec_type, $aParams );
        }
    }
    
    return $objResponse;
}

/**
 * Сохранение комментария к статьям
 * 
 * @param object $objResponse xajaxResponse
 * @param string $rec_id идентификатор записи
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditArtComSaveForm( &$objResponse, $rec_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/comments/CommentsArticles.php' );
    
    $oComments     = new CommentsArticles();
    $attachedfiles = new attachedfiles( $aForm['attachedfiles_session'] );
    $attachedfiles_files = $attachedfiles->getFiles( array(1, 3, 4) );
    $oComments->addAttachedFiles( $attachedfiles_files, $rec_id, $aForm['user_login'] );
    $attachedfiles->clear();
    
    $sReason = _parseReason( $aForm['user_id'], $aForm['adm_edit_text'] );
    messages::artComModifiedNotification( $rec_id, $aForm['user_login'], $aForm['user_uname'], $aForm['user_usurname'], $sReason, $aForm['resource'] );
    
    $content_id = user_content::MODER_ART_COM;
    
    _admEditAfterAll( $objResponse, $content_id, $rec_id, $rec_type, $sDrawFunc, $aForm );
}

/**
 * Отдает HTML для комментариев к статьям
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $rec_id идентификатор записи
 * @param  string $rec_type тип записи
 * @param  array $aParams массив дополнительных параметров
 * @return string
 */
function _admEditArtComParseForm( &$objResponse, $rec_id = '', $rec_type = '', $aParams = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/comments/CommentsArticles.php' );
    
    // получение данных комментария
    $oComments = new CommentsArticles();
    $mess      = $oComments->getData( $rec_id );
    $aModel    = $oComments->model();
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/comments.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // аттачи 
    $aAttach = _getCommentFilesIds( $mess, $aModel );
    $sAttach = getAttachedFilesJs( $aAttach, TComments::MAX_FILE_COUNT, TComments::MAX_FILE_SIZE, 'commune' );
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', 'Редактировать комментарий' );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    $objResponse->script( "$('div_adm_reason').setStyle('display', 'none');" );
    $objResponse->script( "adm_edit_content.editMenuItems = ['', 'Файлы'];" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( $sAttach );
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_ART_COM .');');
}

/**
 * Редактирование профиля юзера
 * 
 * @param  string $sId идентификатор записи
 * @param  int $nEdit флаг редактирования
 * @param  array $aForm данные формы редактирования
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  string $sParams JSON кодированные дополнительные параметры
 * @return xajaxResponse 
 */
function admEditProfile( $sId = '', $nEdit = 0, $aForm = array(), $sDrawFunc = '', $sParams = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        list( $rec_id, $rec_type ) = explode( '_', $sId );
        
        $aParams   = _jsonArray( $sParams );
        $change_id = isset( $aForm['p_change_id'] ) ? $aForm['p_change_id'] : ( isset($aParams['change_id']) ? $aParams['change_id'] : '' );
        $ucolumn   = isset( $aForm['p_ucolumn'] )   ? $aForm['p_ucolumn']   : ( isset($aParams['ucolumn'])   ? $aParams['ucolumn']   : '' );
        $utable    = isset( $aForm['p_utable'] )    ? $aForm['p_utable']    : ( isset($aParams['utable'])    ? $aParams['utable']    : '' );
        
        if ( $ucolumn && $utable && in_array($utable, array('employer', 'freelancer', 'users')) ) {
            if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) {
                if ( !_admEditBeforeStreams($objResponse, $nEdit, $aForm, $aParams, user_content::MODER_PROFILE, $change_id.'_0', $sDrawFunc) ) {
                    return $objResponse;
                }
            }
            
            if ( $nEdit ) {
                _admEditProfileSaveForm( $objResponse, $rec_id, $rec_type, $aForm, $sDrawFunc );
            }
            else {
                _admEditProfileParseForm( $objResponse, $rec_id, $rec_type, $aParams );
            }
        }
    }
    
    return $objResponse;
}

/**
 * Сохранение профиля юзера
 * 
 * @param object $objResponse xajaxResponse
 * @param string $rec_id идентификатор записи
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditProfileSaveForm( &$objResponse, $rec_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    $error = '';
    $bNew  = true;
    
    setlocale( LC_ALL, 'ru_RU.CP1251' );
    
    switch ( $aForm['p_ucolumn'] ) {
        case 'uname':
            $new_val = change_q( substr(trim($aForm['new_val']), 0, 21), true );
            if ( !preg_match("/^[-a-zA-Zа-яёА-ЯЁ]+$/", $new_val) ) { $error = 'Поле заполнено некорректно'; }
            break;
        case 'usurname':
            $new_val = change_q( substr(trim($aForm['new_val']), 0, 21), true );
            if ( !preg_match("/^[-a-zA-Zа-яёА-ЯЁ]+$/", $new_val) ) { $error = 'Поле заполнено некорректно'; }
            break;
        case 'pname':
            $new_val = change_q( substr(trim(stripslashes($aForm['new_val'])), 0, 100), true );
            break;
        case 'spec_text':
            $ab_text_max_length = 500;
            
            $new_val       = stripslashes( trim($aForm['new_val']) );
            $new_val       = preg_replace( "|[\t]+|", " ", $new_val );
            $new_val       = preg_replace( "|[ ]+|", " ", $new_val );
            $original_text = $new_val;
            $newlines      = intval( substr_count($new_val, "\r") );
            $new_val       = change_q_x_a( substr($new_val, 0, $ab_text_max_length + $newlines), false, false, "b|i|p|ul|li{1}" );
            
            if ( strlen($original_text) > $ab_text_max_length + $newlines ) {
                $error = 'Допустимо максимум '.$ab_text_max_length.' знаков.';
            }
            break;
        case 'resume_file':
        case 'photo':
        case 'logo':
            $del_file = intval( $aForm['del_file'] );
            $dir      = $aForm['login'];
            $dir2     = $aForm['p_ucolumn'] == 'resume_file' ? 'resume' : ($aForm['p_ucolumn'] == 'photo' ? 'foto' : 'logo');
            
            if ( $del_file || $aForm['new_val'] ) {
                $new_val = $del_file ? '' : substr( change_q_new(trim(stripslashes($aForm['new_val']))), 0, 1500 );
                
                if ( $aForm['old_val'] ) {
                    $oCFile = new CFile();
                    $oCFile->Delete( 0, 'users/' . substr($dir, 0, 2) . '/' . $dir . '/'. $dir2 .'/', $aForm['old_val'] );
                    
                    if ( $aForm['p_ucolumn'] == 'photo' || $aForm['p_ucolumn'] == 'logo' ) {
                        $oCFile->Delete( 0, 'users/' . substr($dir, 0, 2) . '/' . $dir . '/'. $dir2 .'/', 'sm_'.$aForm['old_val'] );
                    }
                }
            }
            else {
                // админ нажал "Сохранить" не зааплоадив файл - считаем что утвердил тот что есть
                $bNew = false;
            }
            break;
        case 'resume':
            $new_val = str_replace( "\r\n", "\r", $aForm['new_val'] );
            
            if ( strlen($new_val) > 4000 ) { $error = 'Допустимо максимум 4000 знаков.'; }
            
            $new_val = change_q( substr(trim($new_val), 0, 4000), false, 25 );
            break;
        case 'konk':
            if ( strlen($aForm['new_val']) > 4000 ) { $error = 'Допустимо максимум 4000 знаков.'; }
            
            $new_val = change_q( substr(trim($aForm['new_val']), 0, 4000), false, 90 );
            break;
        case 'company':
            if ( strlen($aForm['new_val']) > 500 ) { $error = 'Допустимо максимум 500 знаков.'; }
            
            $new_val = substr( change_q_x($aForm['new_val'], false, true, null, false, false), 0, 500 );
            break;
        case 'status_text':
            $new_val = addslashes( substr(stripslashes(trim($aForm['new_val'])), 0, 200) );
            close_tags( $new_val, 's' );
            $new_val = htmlspecialchars(htmlspecialchars_decode(change_q_x(trim($new_val), true, false), ENT_QUOTES), ENT_QUOTES);
            break;
        case 'compname':
            $new_val = change_q_x( $aForm['new_val'], true );
            break;
        default:
            setlocale( LC_ALL, 'en_US.UTF-8' );
            return false;
            break;
    }
    
    setlocale( LC_ALL, 'en_US.UTF-8' );
    
    if ( !$error ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        
        if ( $bNew ) {
            $sReason = _parseReason( $rec_id, $aForm['adm_edit_text'] );
            messages::profileModifiedNotification( $rec_id, $aForm['p_ucolumn'], $aForm['p_utable'], $sReason );
            
            if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) {
                user_content::editProfile( $aForm['p_change_id'], $new_val );
            }
        }
        
        $objResponse->script( 'adm_edit_content.cancel();' );
        
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) {
            $objResponse->script( 'parent.adm_edit_content.cancel();' );
            resolveContent( $aForm['p_content_id'], $aForm['p_stream_id'], user_content::MODER_PROFILE .'_'. $aForm['p_change_id'] .'_0', 1, $rec_id, $aForm['p_content_cnt'], $aForm['p_status'], $aForm['p_is_sent'], '', $objResponse );
        }
        else { // действие после редактирования по умолчанию
            if ( $bNew ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/'. $aForm['p_utable'] .'.php' );

                $user = new $aForm['p_utable'];

                $user->$aForm['p_ucolumn'] = $new_val;
                $user->moduser_id = $_SESSION['uid'];

                $user->Update( $rec_id, $res );
            }
            
            if ( $sDrawFunc == 'suspect' ) { // шерстим все профили на наличие контактов в админке
                $objResponse->script( "window.location = '/siteadmin/suspicious_contacts/?site={$aForm['p_site']}&action=resolve&sid={$aForm['p_sid']}&page={$aForm['p_page']}'" );
                return 0;
            }
            
            $objResponse->script( 'window.location.reload(true)' );
        }
    }
    else {
        $sParent = ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) ? 'parent.' : '';
        $objResponse->script( "$sParent$('adm_edit_err_new_val').set('html', '{$error}');" );
        $objResponse->script( "$sParent$('div_adm_edit_err_new_val').setStyle('display', '');" );
        $objResponse->script("{$sParent}adm_edit_content.disabled = false; {$sParent}adm_edit_content.button();");
    }
}

/**
 * Отдает HTML для Редактирование профиля юзера
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $rec_id идентификатор записи
 * @param  string $rec_type тип записи
 * @param  array $aParams дополнительные параметры
 * @return string
 */
function _admEditProfileParseForm( &$objResponse, $rec_id = '', $rec_type = '', $aParams = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/'. $aParams['utable'] .'.php' );
    
    $user = new $aParams['utable'];
    $user->GetUserByUID( $rec_id );
    
    $ucolumn   = $aParams['ucolumn'];
    $aFile     = array( 'resume_file', 'photo', 'logo' );
    $aText     = array( 'uname', 'usurname', 'pname', 'compname' );
    $aTextArea = array( 'spec_text', 'resume', 'konk', 'company', 'status_text' );
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/profile.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // текст
    if ( in_array($ucolumn, $aTextArea) ) {
        $sOnReady = "if(document.getElementById('adm_edit_msg')) document.getElementById('adm_edit_msg').value = ($('adm_edit_msg_source')? $('adm_edit_msg_source').value : null);";
    }
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', 'Редактировать профиль' );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    
    if ( in_array($ucolumn, $aTextArea) ) {
        $objResponse->script( $sOnReady );
    }
    
    $objResponse->script( "adm_edit_content.userLogin = '{$user->login}';" );
    $objResponse->script( "$('div_adm_reason').setStyle('display', '');" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_PROFILE .');');
}

/**
 * Редактирование комментариев к предложениям по проектам
 * 
 * @param  string $sId идентификатор записи
 * @param  int $nEdit флаг редактирования
 * @param  array $aForm данные формы редактирования
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  string $sParams JSON кодированные дополнительные параметры
 * @return xajaxResponse 
 */
function admEditPrjDialog( $sId = '', $nEdit = 0, $aForm = array(), $sDrawFunc = '', $sParams = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        list( $rec_id, $rec_type ) = explode( '_', $sId );
        $aParams                   = _jsonArray( $sParams );
        
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) {
            if ( !_admEditBeforeStreams($objResponse, $nEdit, $aForm, $aParams, user_content::MODER_PRJ_DIALOG, $sId, $sDrawFunc) ) {
                return $objResponse;
            }
        }
        
        if ( $nEdit ) {
            _admEditPrjDialogSaveForm( $objResponse, $rec_id, $rec_type, $aForm, $sDrawFunc );
        }
        else {
            _admEditPrjDialogParseForm( $objResponse, $rec_id, $rec_type, $aParams );
        }
    }
    
    return $objResponse;
}

/**
 * Сохранение комментария к предложениям по проектам
 * 
 * @param object $objResponse xajaxResponse
 * @param string $rec_id идентификатор записи
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditPrjDialogSaveForm( &$objResponse, $rec_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects_offers_dialogue.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
    
    $bValid = true;
    
    if ( !trim($aForm['post_text']) ) {
        $bValid = false;
        $sParent = ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) ? 'parent.' : '';
        $objResponse->script( "$sParent$('adm_edit_err_msg').set('html', 'Поле заполнено некорректно.'); ");
        $objResponse->script( "$sParent$('div_adm_edit_err_msg').setStyle('display', '');" );
        $objResponse->script("{$sParent}adm_edit_content.disabled = false; {$sParent}adm_edit_content.button();");
    }
    
    if ( $bValid ) {
        $sReason = _parseReason( $aForm['user_id'], $aForm['adm_edit_text'] );
        $pod     = new projects_offers_dialogue;
        $po_text = antispam( trim($aForm['post_text']) );
        $po_text = preg_replace( "/(\r\n|\r|\n){3,100}/i", "\r\n\r\n", $po_text );
        $error   = $pod->SaveDialogueMessage( $aForm['user_id'], $po_text, $rec_id, $aForm['po_id'], false, $_SESSION['uid'], $sReason );
        
        messages::prjDialogModifiedNotification( $aForm['user_id'], $aForm['project_id'], $po_text, $sReason );
        
        $content_id = user_content::MODER_PRJ_DIALOG;
        
        _admEditAfterAll( $objResponse, $content_id, $rec_id, $rec_type, $sDrawFunc, $aForm );
    }
}

/**
 * Отдает HTML для комментариев к предложениям по проектам
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $rec_id идентификатор записи
 * @param  string $rec_type тип записи
 * @param  array $aParams массив дополнительных параметров
 * @return string
 */
function _admEditPrjDialogParseForm( &$objResponse, $rec_id = '', $rec_type = '', $aParams = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects_offers_dialogue.php' );
    
    $msg = projects_offers_dialogue::getDialogueMessageById( $rec_id );
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/projects_dialog.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // текст
    $sOnReady = "if(document.getElementById('adm_edit_msg')) document.getElementById('adm_edit_msg').value = ($('adm_edit_msg_source')? $('adm_edit_msg_source').value : null);";
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', 'Редактировать комментарий' );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    $objResponse->script( $sOnReady );
    $objResponse->script( "$('div_adm_reason').setStyle('display', '');" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_PRJ_DIALOG .');');
}

/**
 * Редактирование комментариев к работе в конкурсе
 * 
 * @param  string $sId идентификатор записи
 * @param  int $nEdit флаг редактирования
 * @param  array $aForm данные формы редактирования
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  string $sParams JSON кодированные дополнительные параметры
 * @return xajaxResponse 
 */
function admEditContestCom( $sId = '', $nEdit = 0, $aForm = array(), $sDrawFunc = '', $sParams = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        list( $rec_id, $rec_type ) = explode( '_', $sId );
        $aParams                   = _jsonArray( $sParams );
        
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) {
            if ( !_admEditBeforeStreams($objResponse, $nEdit, $aForm, $aParams, user_content::MODER_CONTEST_COM, $sId, $sDrawFunc) ) {
                return $objResponse;
            }
        }
        
        if ( $nEdit ) {
            _admEditContestComSaveForm( $objResponse, $rec_id, $rec_type, $aForm, $sDrawFunc );
        }
        else {
            _admEditContestComParseForm( $objResponse, $rec_id, $rec_type, $aParams );
        }
    }
    
    return $objResponse;
}

/**
 * Сохранение комментария к работе в конкурсе
 * 
 * @param object $objResponse xajaxResponse
 * @param string $rec_id идентификатор записи
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditContestComSaveForm( &$objResponse, $rec_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    $alert   = array();
    $comment = $aForm['msg'];
    
    if ( !trim($comment) ) {
        $alert[1] = 'Комментарий не может быть пустым';
    }
    
    if ( !$alert ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/contest.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        
        $contest = new contest( 0, $uid, is_emp(), false, true );
        $comment = change_q_x( antispam(substr(rtrim(ltrim($comment, "\r\n")), 0, 5000)), false, true, 'b|br|i|p|ul|li|cut|h[1-6]{1}', false, false );
        $sReason = _parseReason( $aForm['user_id'], $aForm['adm_edit_text'] );
        
        $contest->ChangeComment( $rec_id, $comment );
        
        messages::contestComModifiedNotification( $aForm['project_id'], $aForm['offer_id'], $rec_id, $aForm['login'], $aForm['uname'], $aForm['usurname'], $sReason );
        
        $content_id = user_content::MODER_CONTEST_COM;
        
        _admEditAfterAll( $objResponse, $content_id, $rec_id, $rec_type, $sDrawFunc, $aForm );
    }
    else {
        _setErrors( $objResponse, $alert, array(1 => 'msg'), $sDrawFunc );
    }
}

/**
 * Отдает HTML для комментариев к работе в конкурсе
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $rec_id идентификатор записи
 * @param  string $rec_type тип записи
 * @param  array $aParams массив дополнительных параметров
 * @return string
 */
function _admEditContestComParseForm( &$objResponse, $rec_id = '', $rec_type = '', $aParams = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/contest.php' );
    
    $msg = contest::GetComment( $rec_id );
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/contest_commment.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // текст
    $sOnReady = "if($('adm_edit_msg')) $('adm_edit_msg').set('value', ($('adm_edit_msg_source')? $('adm_edit_msg_source').get('value') : null));";
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', 'Редактировать комментарий' );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    $objResponse->script( $sOnReady );
    $objResponse->script( "$('div_adm_reason').setStyle('display', '');" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_CONTEST_COM .');');
}

/**
 * Редактирование уточнения к разделам в портфолио
 * 
 * @param  string $sId идентификатор записи
 * @param  int $nEdit флаг редактирования
 * @param  array $aForm данные формы редактирования
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  string $sParams JSON кодированные дополнительные параметры с ID профессии
 * @return xajaxResponse 
 */
function admEditPortfChoice( $sId = '', $nEdit = 0, $aForm = array(), $sDrawFunc = '', $sParams = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        list( $rec_id, $rec_type ) = explode( '_', $sId );
        $aParams                   = _jsonArray( $sParams );
        
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) {
            $change_id = isset( $aForm['p_change_id'] ) ? $aForm['p_change_id'] : ( isset($aParams['change_id']) ? $aParams['change_id'] : '' );
            
            if ( !_admEditBeforeStreams($objResponse, $nEdit, $aForm, $aParams, user_content::MODER_PORTF_CHOISE, $change_id.'_0', $sDrawFunc) ) {
                return $objResponse;
            }
        }
        
        if ( $nEdit ) {
            _admEditPortfChoiceSaveForm( $objResponse, $rec_id, $rec_type, $aForm, $sDrawFunc );
        }
        else {
            if (is_array($aParams) && isset($aParams['sProfId']) && !empty($aParams['sProfId']) ) {
                _admEditPortfChoiceParseForm( $objResponse, $rec_id, $rec_type, $aParams );
            }
        }
    }
    
    return $objResponse;
}

/**
 * Сохранение уточнения к разделам в портфолио
 * 
 * @param object $objResponse xajaxResponse
 * @param  string $user_id UID пользователя
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditPortfChoiceSaveForm( &$objResponse, $user_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/kwords.php' );
    
    $kwords  = new kwords();
    $aOldIds = array_keys( $kwords->getUserKeys($user_id, $aForm['prof_id']) );
    $ids     = array();

    $kwords->delUserKeys( $user_id, $aForm['prof_id'] );
    if ( trim($aForm['prof_keys']) ) {
        $ukey = explode( ',', $aForm['prof_keys'] );
        
        if ( count($ukey) > 0 ) {
            $ids  = $kwords->add( $ukey, true );
            $kwords->addUserKeys( $user_id, $ids, $aForm['prof_id'] );
        }
    }

    $kwords->moderUserKeys( $user_id, $aForm['prof_id'], $aOldIds, $ids, $_SESSION['uid'] );
    
    $sReason    = _parseReason( $aForm['user_id'], $aForm['adm_edit_text'] );
    $obj_prof   = new professions();
    $error_prof = $obj_prof->UpdateProfDesc( $user_id, $aForm['prof_id'], str_replace(" ", "", $aForm['prof_cost_from']),
        str_replace(" ", "", $aForm['prof_cost_to']), str_replace(" ", "", $aForm['prof_cost_hour']), 
        str_replace(" ", "", $aForm['prof_cost_1000']), $aForm['prof_cost_type'], $aForm['prof_cost_type_hour'], 
        $aForm['prof_time_type'], $aForm['prof_time_from'], $aForm['prof_time_to'], $aForm['prof_text'], $errorProfText, 
        $_SESSION['uid'], $sReason 
    );
    
    if ( !$error_prof ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/portfolio.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $user  = new users();
        $projs = array();
        
        $user->GetUserByUID( $user_id );
        
        if ( $user->is_pro == 't' ) {
            $show_preview = ( isset($aForm['show_preview']) && $aForm['show_preview'] ) ? $aForm['show_preview'] : 0;
            portfolio::ChangeGrPrev( $user_id, $aForm['prof_id'], $projs, $show_preview );
        }
        
        messages::portfChoiceModifiedNotification( $user_id, $aForm['prof_id'], $sReason );
        $objResponse->script( 'adm_edit_content.cancel();' );
        
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) { // если случаев будет больше - вынести в отдельную функцию
            $objResponse->script( 'parent.adm_edit_content.cancel();' );
            $objResponse->script('user_content.getContents()');
        }
        elseif ( $sDrawFunc == 'suspect' ) { // шерстим все профили на наличие контактов в админке
            $objResponse->script( "window.location = '/siteadmin/suspicious_contacts/?site={$aForm['p_site']}&action=resolve&sid={$aForm['p_sid']}&page={$aForm['p_page']}'" );
        }
        else { // действие после редактирования по умолчанию
            $objResponse->script( 'window.location.reload(true)' );
        }
    }
    else {
        $sParent = ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) ? 'parent.' : '';
        $error_prof = str_replace( array('<br />', '<br/>', '<br>'), "\n", $error_prof );
        $objResponse->alert( $error_prof );
        $objResponse->script("{$sParent}adm_edit_content.disabled = false; {$sParent}adm_edit_content.button();");
    }
}

/**
 * Отдает HTML для Редактирование Личные сообщения
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $user_id UID пользователя
 * @param  string $rec_type тип записи
 * @param  array $aParams ID профессии
 * @return string
 */
function _admEditPortfChoiceParseForm( &$objResponse, $user_id = '', $rec_type = '', $aParams = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/kwords.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
    
    $prof_id = $aParams['sProfId'];
    
    $prj  = professions::GetProfDesc( $user_id, $prof_id );
    $keys = kwords::getUserKeys( $user_id, $prof_id );
    $user = new users();
    
    $user->GetUserByUID( $user_id );
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/portf_choise.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // текст
    $sOnReady = "if(document.getElementById('adm_edit_msg')) document.getElementById('adm_edit_msg').value = ($('adm_edit_msg_source')? $('adm_edit_msg_source').value : null);";
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', 'Редактировать уточнения к разделу в портфолио' );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    $objResponse->script( $sOnReady );
    $objResponse->script( "$('div_adm_reason').setStyle('display', 'none');" );
    $objResponse->script( "adm_edit_content.editMenuItems = ['', 'Основное'];" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_PORTF_CHOISE .');');
}

/**
 * Редактирование работы в портфолио
 * 
 * @param  string $sId идентификатор записи
 * @param  int $nEdit флаг редактирования
 * @param  array $aForm данные формы редактирования
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  string $sParams JSON кодированные дополнительные параметры
 * @return xajaxResponse 
 */
function admEditPortfolio( $sId = '', $nEdit = 0, $aForm = array(), $sDrawFunc = '', $sParams = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        list( $rec_id, $rec_type ) = explode( '_', $sId );
        $aParams                   = _jsonArray( $sParams );
        
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) {
            if ( !_admEditBeforeStreams($objResponse, $nEdit, $aForm, $aParams, user_content::MODER_PORTFOLIO, $sId, $sDrawFunc) ) {
                return $objResponse;
            }
        }
        
        if ( $nEdit ) {
            _admEditPortfolioSaveForm( $objResponse, $rec_id, $rec_type, $aForm, $sDrawFunc );
        }
        else {
            _admEditPortfolioParseForm( $objResponse, $rec_id, $rec_type, $aParams );
        }
    }
    
    return $objResponse;
}

/**
 * Сохранение работы в портфолио
 * 
 * @param object $objResponse xajaxResponse
 * @param string $rec_id идентификатор записи
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditPortfolioSaveForm( &$objResponse, $rec_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/portfolio.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
    
    $max_time_value = 100;
    
    //стоимость работы из портфолио
    $max_portf_cost[0] = 100000;  // usd
    $max_portf_cost[1] = 100000;  // euro
    $max_portf_cost[2] = 5000000; // rur
    $max_portf_cost[3] = 100000;  // fm
    
    $user = new users();
    $user->GetUserByUID( $aForm['user_id'] );
    
    // инициализация
    $aPortf            = portfolio::GetPortfById( $rec_id );
    $alert             = array();
    $maxlen            = $aForm['is_video'] ? 80 : 120;
    $name              = substr( $aForm['pname'], 0, $maxlen );
    $name              = change_q_x( $name, false, true, '', false, false );
    $name              = $name ? $name : '';
    $descr             = substr( change_q_new(trim(stripslashes($aForm['descr']))), 0, 1500 );
    $prof              = intval( $aForm['prof'] );
    $new_prof          = intval( $aForm['new_prof'] );
    $prj_id            = $rec_id;
    $cost              = intval( str_replace(' ', '', $aForm['pcost']) * 100 ) / 100;
    $cost_type         = intval( $aForm['pcosttype'] );
    $time_value        = intval( trim($aForm['ptime']) );
    $time_type         = intval( $aForm['ptimeei'] );
    $is_video          = $aForm['is_video'] ? 't' : 'f';
    $video_link        = $aForm['is_video'] ? stripslashes(trim($aForm['v_video_link'])) : '';
    $link              = $aForm['is_video'] ? '' : addhttp(trim(substr(change_q_x($aForm['link'], true), 0, 150)));
    $link              = $link ? $link : '';
    $make_position     = $aForm['make_position'];
    $make_position_num = trim( $aForm['make_position_num'] );
    $update_prev       = intval( $aForm['upd_prev'] );
    $prev_type         = intval( $aForm['prev_type'] );
    $del_prev          = intval( $aForm['del_prev'] );
    $new_position      = NULL;
    $pict              = substr( change_q_new(trim(stripslashes($aForm['pict']))), 0, 1500 );
    $prev_pict         = substr( change_q_new(trim(stripslashes($aForm['prev_pict']))), 0, 1500 );
    
    if ( $new_prof != $prof ) {
        $new_position = 0;
    }
    
    if ( isset($make_position) ) {
        switch ($make_position) {
            case 'first': 
                $new_position = 1; 
                break;
            case 'last': 
                $new_position = 0;
                break;
            case 'num':
            default:
                $new_position = intval( $make_position_num );
                $new_position = ( $new_position <= 0 ) ? 1 : $new_position;
                break;
        }
    } 
    
    // валидация (нумерация алертов как в первоначальном варианте радактирования и новый нулевой)
    
    if ( !$name || (strlen(trim(stripslashes($aForm['pname']))) > 80) ) { $alert[1] = 'Поле заполнено некорректно'; }
    
    if ( $link!='' && !url_validate($link, true) ) { $alert[6] = 'Поле заполнено некорректно'; }
    
    if ( $is_video == 't' ) {
        $v_video_link = video_validate( $video_link );
        
        if ( !$v_video_link ) {
            $alert[206] = "Поле заполнено некорректно";
        } else {
            $video_link = preg_replace( "/^http:\/\//", '', $v_video_link );
        }
    }
    
    if ( $cost < 0 || $cost > $max_portf_cost[$cost_type] ) {
        $alert[4] = 'Стоимость должна быть в пределе от 0 ' . view_range_cost2(0, $max_portf_cost[$cost_type], '', '', false, $cost_type) . ($cost_type != 2 ? '.' : '');
    }
    
    if ( $time_value < 0 || $time_value > $max_time_value ) {
        $alert[5] = 'Временные затраты должны быть в пределе от 0 до ' . $max_time_value . '.';
    }
    
    if ( $new_prof != $prof && ( $new_prof == professions::CLIENTS_PROF_ID || $new_prof == professions::BEST_PROF_ID ) 
        && portfolio::CountAll($aForm['user_id'], $new_prof, true) >= portfolio::MAX_BEST_WORKS 
    ) {
        $alert[0] = 'Превышено количество работ в этом разделе';
    }
    
    // сохраняем
    if ( !$alert ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        
        $sReason = _parseReason( $aForm['user_id'], $aForm['adm_edit_text'] );
        $portf   = new portfolio();
        
        $portf->EditPortf( $aForm['user_id'], $name, $img, $sm_img, $link, $descr, $new_prof, $cost, $cost_type, $time_type, $time_value, 
            $prev_type, $prj_id, $file_error, $preview_error, $new_position, 0, $video_link, $update_prev, $_SESSION['uid'],
            $pict, $prev_pict, $user->login, $sReason 
        );
        
        if ( $del_prev ) {
            $portf->DelPict( $user->login, $prj_id, 0 );
        }
        
        messages::portfolioModifiedNotification( $aPortf, $user, $sReason );
        
        $content_id = user_content::MODER_PORTFOLIO;
        
        _admEditAfterAll( $objResponse, $content_id, $rec_id, $rec_type, $sDrawFunc, $aForm );
    }
    else {
        _setErrors( $objResponse, $alert, array(0 => 'prof', 1 => 'pname', 2 => 'descr', 4 => 'pcost', 5 => 'ptime', 6 => 'link', 206 => 'video_link'), $sDrawFunc );
    }
}

/**
 * Отдает HTML для Редактирование работы в портфолио
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $rec_id идентификатор записи
 * @param  string $rec_type тип записи
 * @return string
 */
function _admEditPortfolioParseForm( &$objResponse, $rec_id = '', $rec_type = '', $aParams = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/portfolio.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
    
    $portf = portfolio::GetPortfById( $rec_id );
    $user  = new users();
    $sH4   = $portf['is_video'] == 't' ? 'Изменить видео' : 'Редактировать работу';

    $user->GetUserByUID( $portf['user_id'] );
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/portfolio.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // текст
    $sOnReady   = "if(document.getElementById('adm_edit_msg')) document.getElementById('adm_edit_msg').value = ($('adm_edit_msg_source')? $('adm_edit_msg_source').value : null);";
    $sMenuItems = "['', 'Основное', 'Файлы']";
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', $sH4 );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    $objResponse->script( $sOnReady );
    $objResponse->script( "adm_edit_content.userLogin = '{$user->login}';" );
    $objResponse->script( "$('div_adm_reason').setStyle('display', 'none');" );
    $objResponse->script( "adm_edit_content.editMenuItems = $sMenuItems;" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_PORTFOLIO .');');
}

/**
 * Редактирование предложений фрилансеров Сделаю
 * 
 * @param  string $sId идентификатор записи
 * @param  int $nEdit флаг редактирования
 * @param  array $aForm данные формы редактирования
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  string $sParams JSON кодированные дополнительные параметры
 * @return xajaxResponse 
 */
function admEditSdelau( $sId = '', $nEdit = 0, $aForm = array(), $sDrawFunc = '', $sParams = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        list( $rec_id, $rec_type ) = explode( '_', $sId );
        $aParams                   = _jsonArray( $sParams );
        
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) {
            if ( !_admEditBeforeStreams($objResponse, $nEdit, $aForm, $aParams, user_content::MODER_SDELAU, $sId, $sDrawFunc) ) {
                return $objResponse;
            }
        }
        
        if ( $nEdit ) {
            _admEditSdelauSaveForm( $objResponse, $rec_id, $rec_type, $aForm, $sDrawFunc );
        }
        else {
            _admEditSdelauParseForm( $objResponse, $rec_id, $rec_type, $aParams );
        }
    }
    
    return $objResponse;
}

/**
 * Сохранение предложений фрилансеров Сделаю
 * 
 * @param object $objResponse xajaxResponse
 * @param string $rec_id идентификатор записи
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditSdelauSaveForm( &$objResponse, $rec_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer_offers.php' );
    
    $alert = array();
    
    if ( trim($aForm['name']) == '' ) {
        $alert[1] = 'Поле не заполнено';
    }
    elseif ( strlen($aForm['name']) > freelancer_offers::MAX_SIZE_TITLE ) {
        $alert[1] = 'Максимальное количество символов ' . freelancer_offers::MAX_SIZE_TITLE;
    }
    
    if ( trim($aForm['msg']) == '' ) {
        $alert[2] = 'Поле не заполнено';
    }
    elseif ( strlen_real($aForm['msg']) > freelancer_offers::MAX_SIZE_DESCRIPTION ) {
        $alert[2] = 'Максимальное количество символов ' . freelancer_offers::MAX_SIZE_DESCRIPTION;
    }

    if ( $aForm['categories'] == 0 ) {
        $alert[3] = 'Не выбран раздел и подраздел';
    }
    
    if ( !$alert ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        
        $oFreelancerOffers = new freelancer_offers();
        $update = array(
            'title'          => $aForm['name'], 
            'descr'          => $aForm['msg'], 
            'category_id'    => intval( $aForm['categories'] ), 
            'subcategory_id' => intval( $aForm['subcategories'] ),
            'modify_date'    => date( 'Y-m-d H:i:s' )
        );

        $oFreelancerOffers->Update( intval($rec_id), $update );
        
        $sReason = _parseReason( $aForm['user_id'], $aForm['adm_edit_text'] );
        
        messages::sdelauModifiedNotification( $aForm['msg'], $aForm['login'], $aForm['uname'], $aForm['usurname'], $sReason );
        
        $content_id = user_content::MODER_SDELAU;
        
        _admEditAfterAll( $objResponse, $content_id, $rec_id, $rec_type, $sDrawFunc, $aForm );
    }
    else {
        _setErrors( $objResponse, $alert, array(1 => 'name', 2 => 'msg', 3 => 'categories'), $sDrawFunc );
    }
}

/**
 * Отдает HTML для предложений фрилансеров Сделаю
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $rec_id идентификатор записи
 * @param  string $rec_type тип записи
 * @param  array $aParams массив дополнительных параметров
 * @return string
 */
function _admEditSdelauParseForm( &$objResponse, $rec_id = '', $rec_type = '', $aParams = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer_offers.php' );
    
    $oFreelancerOffers = new freelancer_offers();
    $offer   = $oFreelancerOffers->getOfferById( $rec_id, false );
    $objUser = new users();
    $objUser->GetUserByUID( $offer['user_id'] );
    
    // разделы
    $categories  = professions::GetAllGroupsLite();
    $professions = professions::GetAllProfessions();
    array_group( $professions, 'groupid' );
    $professions[0] = array();
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/sdelau.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // текст
    $sOnReady = "if($('adm_edit_msg')) $('adm_edit_msg').set('value', ($('adm_edit_msg_source')? $('adm_edit_msg_source').get('value') : null));";
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', 'Редактировать предложение' );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    $objResponse->script( $sOnReady );
    $objResponse->script( "$('div_adm_reason').setStyle('display', 'none');" );
    $objResponse->script( "adm_edit_content.editMenuItems = ['', 'Основное'];" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_SDELAU .');');
}


/**
 * Сохранение Платные места
 * 
 * @param object $objResponse xajaxResponse
 * @param string $rec_id идентификатор записи
 * @param string $rec_type тип записи
 * @param array $aForm массив данных
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditCarouselSaveForm( &$objResponse, $rec_id = '', $rec_type = '', $aForm = array(), $sDrawFunc = '' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pay_place.php' );
    
    if ( $sDrawFunc == 'adm_first_page' ) {
        if ( !pay_place::checkModeration($rec_id) ) {
            $objResponse->alert( 'Пользователь удалил или изменил данные.' );
            $objResponse->script( 'adm_edit_content.cancel();' );
            $objResponse->script( "$('my_div_content_{$rec_id}').destroy();" );
            return false;
        }
    }
    
    $alert   = array();
    $sHeader = change_q_x( $aForm['header'],   true );
    $sText   = change_q_x( $aForm['txt'],      true );
    $sNewImg = change_q_x( $aForm['new_val'],  true );
    $sDelImg = change_q_x( $aForm['del_prev'], true );
    
    if ( !$sHeader ) {
        $alert[1] = 'Заполните заголовок объявления';
    }
    elseif ( strlen($sHeader) > pay_place::MAX_HEADER_SIZE ) {
        $alert[1] = 'Превышен максимальный размер заголовка';
    }
    
    if ( !$sText ) {
        $alert[2] = 'Заполните текст объявления';
    }
    elseif ( strlen($sText) > pay_place::MAX_TEXT_SIZE ) {
        $alert[2] = 'Превышен максимальный размер текста';
    }
    
    if ( !$alert ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        
        $content_id = user_content::MODER_CAROUSEL;
        $sReason    = _parseReason( $aForm['user_id'], $aForm['adm_edit_text'] );
        $aData      = array( 'ad_header' => $sHeader, 'ad_text' => $sText );
        
        if ( $sNewImg || $sDelImg ) {
            $aData['ad_img_file_name'] = $sNewImg;
        }
        
        pay_place::updatePaidPlace( $rec_id, $aData, $sDelImg );
        messages::carouselModifiedNotification( $aForm['login'], $aForm['uname'], $aForm['usurname'], $sReason );
        
        if ( $sDrawFunc == 'adm_first_page' ) {
            pay_place::setModeration( $rec_id, 0, $_SESSION['uid'] );
            $objResponse->script( 'adm_edit_content.cancel();' );
            $objResponse->script( "$('my_div_content_{$content_id}_{$rec_id}').destroy();" );
        }
    }
    else {
        _setErrors( $objResponse, $alert, array(1 => 'header', 2 => 'txt'), $sDrawFunc );
    }
}

/**
 * Отдает HTML для Платные места
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $rec_id идентификатор записи
 * @param  string $rec_type тип записи
 * @param  array $aParams массив дополнительных параметров
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 * @return string
 */
function _admEditCarouselParseForm( &$objResponse, $rec_id = '', $rec_type = '', $aParams = array(), $sDrawFunc = '' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pay_place.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
    
    if ( $sDrawFunc == 'adm_first_page' ) {
        if ( !pay_place::checkModeration($rec_id) ) {
            $objResponse->alert( 'Пользователь удалил или изменил данные.' );
            $objResponse->script( 'adm_edit_content.cancel();' );
            $objResponse->script( "$('my_div_content_". user_content::MODER_CAROUSEL ."_{$rec_id}').destroy();" );
            return false;
        }
    }
    
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/attachedfiles.php' );
    
    $place = pay_place::getPaidPlace( $rec_id );
    $attFiles = new attachedfiles;
    
    ob_start();
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_tpl/carousel.php' );
    $sHtml = ob_get_contents();
    ob_end_clean();
    
    // текст
    $sOnReady = "if(document.getElementById('adm_edit_txt')){document.getElementById('adm_edit_txt').value=($('adm_edit_txt_source')?$('adm_edit_txt_source').value:null);}";
    
    $objResponse->assign( 'h4_adm_edit', 'innerHTML', 'Редактировать карусель' );
    $objResponse->assign( 'div_adm_edit', 'innerHTML', $sHtml );
    $objResponse->script( $sOnReady );
    $objResponse->script( "$('div_adm_reason').setStyle('display', 'none');" );
    $objResponse->script( "adm_edit_content.editMenuItems = ['', 'Основное'];" );
    $objResponse->script( "adm_edit_content.userLogin = '{$place['login']}';" );
    $objResponse->script( 'adm_edit_content.edit();' );
    $objResponse->script( 'xajax_getAdmEditReasons('. admin_log::ACT_ID_EDIT_FIRST_PAGE .');');
}

/**
 * Возвращает массив id файлов, прикрепленных к комментарию (которыне на общем движке)
 * 
 * @param  array $aComment массив с данными по комментарию
 * @param  array $aModel Конфиг данных для комментариев @see TComments::model();
 * @return array 
 */
function _getCommentFilesIds( $aComment = array(), $aModel = array() ) {
    $aReturn  = array();
    $sFldName = isset($aModel['attaches']['fields']['file']) ? $aModel['attaches']['fields']['file'] : '';
    
    if ( $sFldName && isset($aComment['attach']) && is_array($aComment['attach']) && $aComment['attach'] ) {
        foreach ($aComment['attach'] as $aOne) {
            $aReturn[] = $aOne[$sFldName];
        }
    }
    
    return $aReturn;
}

/**
 * Дополнительные действия, выполняемые перед редактированием в потоках
 * 
 * @param  object $objResponse xajaxResponse
 * @param  int $nEdit флаг редактирования
 * @param  array $aForm данные формы редактирования
 * @param  string $aParams дополнительные параметры
 * @param  int $nContentId идентификатор сущности из admin_contents
 * @param  string $sId идентификатор записи
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 */
function _admEditBeforeStreams( &$objResponse, $nEdit = 0, $aForm = array(), $aParams = array(), $nContentId = 0, $sId = '', $sDrawFunc = '' ) {
    global $user_content;
    
    list( $rec_id, $rec_type ) = explode( '_', $sId );
    
    $sContentId  = $nEdit ? $aForm['p_content_id'] : $aParams['content_id'];
    $sStreamId   = $nEdit ? $aForm['p_stream_id']  : $aParams['stream_id'];
    $sParent     = $nEdit ? 'parent.'                     : '';
    $sChild      = $nEdit ? '' : "$('{$sStreamId}').contentWindow.";

    if ( !$user_content->checkStream($sContentId, $sStreamId, $_SESSION['uid']) ) {
        $objResponse->script( $sParent.'adm_edit_content.cancel();' );
        $objResponse->script( "{$sChild}$('my_div_all').set('html','<div class=\"b-post b-post_pad_10_15_15\"><span style=\"color: #cc4642; font-weight: bold;\">Поток потерян либо перехвачен</span></div>');" );

        return false;
    }
    
    if ( !$user_content->checkContent( $nContentId, $sStreamId, $rec_id) ) {
        $objResponse->script( $sParent.'adm_edit_content.cancel();' );
        $objResponse->alert( "Пользователь удалил или изменил данные.\nЛибо запись заблокирована." );

        if ( $sDrawFunc == 'stream0' ) {
            $objResponse->script( $sChild . 'user_content.getContents();' );
        }
        else {
            $objResponse->script( "{$sChild}$('my_div_content_{$nContentId}_{$sId}').destroy();" );
            $objResponse->script( $sChild . 'user_content.spinner.hide();' );
        }

        return false;
    }
    
    return true;
}

/**
 * Дополнительные действия, выполняемые после редактирования
 * 
 * @param  object $objResponse xajaxResponse
 * @param  int $content_id идентификатор сущности из admin_contents
 * @param  int $rec_id идентификатор записи
 * @param  int $rec_type тип записи 
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  array $aForm массив данных
 */
function _admEditAfterAll( &$objResponse, $content_id = 0, $rec_id = 0, $rec_type = 0, $sDrawFunc = '', $aForm = array() ) {
    $objResponse->script( 'adm_edit_content.cancel();' );

    if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) { // из потоков
        $objResponse->script( 'parent.adm_edit_content.cancel();' );

        if ( $sDrawFunc != 'stream1' ) {
            resolveContent( $aForm['p_content_id'], $aForm['p_stream_id'], $content_id .'_'. $rec_id .'_'.$rec_type, 1, $aForm['user_id'], $aForm['p_content_cnt'], $aForm['p_status'], $aForm['p_is_sent'], '', $objResponse );
        }
        else {
            $objResponse->script( 'window.location.reload(true)' );
        }
    }
    elseif ( $sDrawFunc == 'blocked' ) { // из админки "заблокированные"
        global $user_content;

        $sid = $content_id .'_'. $rec_id .'_'. $rec_type;

        $user_content->unblock( $content_id, $aForm['user_id'], $rec_id, $rec_type, $aForm['p_is_sent'] );
        $objResponse->script( "$('my_div_content_{$sid}').destroy();" );
        $objResponse->script('user_content.spinner.hide();');
    }
    elseif ( $sDrawFunc == 'suspect' ) { // шерстим все профили на наличие контактов в админке
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        
        if ( $content_id == user_content::MODER_PORTFOLIO ) {
            $objResponse->script( "window.location = '/siteadmin/suspicious_contacts/?site={$aForm['p_site']}&action=resolve&sid={$aForm['p_sid']}&page={$aForm['p_page']}'" );
        }
    }
    else { // действие после редактирования по умолчанию
        $objResponse->script( 'window.location.reload(true)' );
    }
}

/**
 * Устанавливает сообщения об ошибках в окне редактирования
 * 
 * @param object $objResponse xajaxResponse
 * @param array $alert массив с сообщениями об ошибках
 * @param array $aError массив соответствий ошибок элементам на странице
 * @param string $sDrawFunc имя функции для выполнения после сохранения
 */
function _setErrors( &$objResponse, $alert = array(), $aError = array(), $sDrawFunc = '' ) {
    $sParent = ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) ? 'parent.' : '';
    
    foreach ( $aError as $key => $value ) {
        if ( isset($alert[$key]) ) {
            $objResponse->script( "{$sParent}$('adm_edit_err_$value').set('html', '{$alert[$key]}');" );
            $objResponse->script( "{$sParent}$('div_adm_edit_err_$value').setStyle('display', '');" );
        }
    }
    
    $objResponse->script("{$sParent}adm_edit_content.disabled = false; {$sParent}adm_edit_content.button();");
    $objResponse->alert('Не все поля заполнены верно');
}

/**
 * Устанавливает опции в селекте выбора причины действия администратора
 * 
 * @param  int $actId код действия
 * @return object xajaxResponse
 */
function getAdmEditReasons( $actId ) {
    $objResponse = new xajaxResponse();
    
    $hasPermission = false;
    $uid = get_uid(false);
    $permissions = admin_log::getPermissionsRights();
    foreach($permissions as $permission) {
        if(hasPermissions($permission, $uid)) $hasPermission = 1;
    }
    if($hasPermission) {
        $sOut  = '<select id="adm_edit_sel" name="adm_edit_sel" onchange="adm_edit_content.setReason();" class="b-select__select b-select__select_width_full" disabled>';
        $sOut .= _getAdmEditReasonOptions( $actId );
	    $sOut .= "</select>";
	
	    $objResponse->assign( "div_adm_edit_sel", "innerHTML", $sOut );
	    $objResponse->script( "adm_edit_content.setReason();" );
	    $objResponse->assign( "adm_edit_sel", 'disabled', false );
	    $objResponse->assign( "ban_btn", 'disabled', false );
    }
    
    return $objResponse;
}

/**
 * Возвращает HTML код с опциями причины действия администратора
 * 
 * @param  int $actId код действия
 * @return string HTML код 
 */
function _getAdmEditReasonOptions( $actId ) {
    $sOut = '<option value="" style="color: #777;" selected>Указать вручную</option>';
    
    $aReasons = admin_log::getAdminReasons( $actId );
    
    if ( $aReasons ) {
    	foreach ( $aReasons as $aOne ) {
            $sBold = $aOne['is_bold'] == 't' ? ' style="background-color: #cdcdcd;"' : ' style="color: #777;"';
    		$sOut .= '<option value="' . $aOne['id'] . '" '. $sBold .'>' . $aOne['reason_name'] . '</option>';
    	}
	}
	
	return $sOut;
}

/**
 * Устанавливает полный текст причины действия администратора в поле формы
 * 
 * @param  int $reasonId ID причины, полный текст которой нужно установить
 * @return object xajaxResponse
 */
function getAdmEditReasonText( $reasonId ) {
    $objResponse = new xajaxResponse();

    $hasPermission = false;
    $uid = get_uid(false);
    $permissions = admin_log::getPermissionsRights();
    foreach($permissions as $permission) {
        if(hasPermissions($permission, $uid)) $hasPermission = 1;
    }
    if($hasPermission) {
        $sReason = admin_log::getAdminReasonText( $reasonId );
    
        $objResponse->assign( "adm_edit_text", "value", $sReason );
        $objResponse->script( "adm_edit_content.reasons['$reasonId'] = '$sReason';" );
    }
    
    return $objResponse;
}

/**
 * Парсит текст причины редактирования для отравки уведомления
 * 
 * @param  int $user_id UID пользователя
 * @param  string $adm_edit_text текст причины
 * @return string 
 */
function _parseReason( $user_id = 0, $adm_edit_text = '' ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );

    $objUser = new users();
    $objUser->GetUserByUID( $user_id );
    
    $sReason = str_replace( '%USERNAME%', $objUser->uname . ' ' . $objUser->usurname, $adm_edit_text );
    $sReason = change_q( $sReason, FALSE, 0, TRUE );
    
    return $sReason;
}

/**
 * Парсит скрытые поля с дополнительными параметрами
 * 
 * @param  array $aParams массив с дополнительными параметрами
 * @return string
 */
function _parseHiddenParams( $aParams = array() ) {
    $sReturn = '';
    
    if ( is_array($aParams) && count($aParams) ) {
        foreach ( $aParams as $key => $value ) {
            $sReturn .= '<input type="hidden" id="adm_edit_p_'. $key .'" name="p_'. $key .'" value="'. $value .'">' . "\n";
        }
    }
    
    return $sReturn;
}

/**
 * Перемещает проект в вакансии
 * @global type $user_content
 * @param type $sid
 * @return \xajaxResponse
 */
function makeVacancy($stream_id = '', $sid = '') {
    global $user_content;

    $objResponse = new xajaxResponse();
    list( $content_id, $rec_id, $rec_type ) = explode( '_', $sid );
    
    if ( $user_content->hasContentPermissions($content_id) ) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/smtp.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/template.php';
        

        $project = new_projects::initData(new_projects::getPrj($rec_id));
        if ($project->isAllowMovedToVacancy()) {
            // Делаем проект вакансией
            $project->movedToVacancy();

            $user_content->markProjectBlocked($stream_id, $rec_id);
            
            // Отсылаем письмо заказчику о переносе его проекта в раздел вакансии
            $mail = new smtp();
            $mail->subject   = 'Ваш проект перенесен в раздел Вакансии и ожидает публикации';  // заголовок письма
            $mail->message = Template::render($_SERVER['DOCUMENT_ROOT'] . '/templates/mail/projects/makevacancy.tpl.php',array(
                'title' => $project->_project['name'],
                'project_id' => $project->_project['id']
            ));

            $mail->recipient = "{$project->_project['email']} <{$project->_project['email']}>"; // получатель
            $mail->SmtpMail('text/html');
        }
        
        $objResponse->script('user_content.spinner.hide(true);');
        $objResponse->script( "$('my_div_content_{$sid}').destroy();" );
        $objResponse->script('user_content.spinner.resize();');
    }
    else {
        _parsePermissionsDenied( $objResponse );
    }
    
    return $objResponse;
}

$xajax->processRequest();
