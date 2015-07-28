<?
/**
 * Подключем файл с основными функциями
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
/**
 * Подключаем файл для работы с мемкешем 
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");


/**
 * Класс для работы с черновиками
 *
 */
class drafts
{
	/**
	 * Сохранение поста в сообществе в черновики
	 *
	 * @param   array     $msg    Информация опосте
     * @return  array             ['id'] - ID черновика, ['date'] - дата сохранения черновика
	 */
	function SaveCommune($msg){
		global $DB;
        $msg['draft_id'] = intval($msg['draft_id']);
        $msg['id'] = intval($msg['id']);
        if(!intval($msg['draft_post_id'])) $msg['draft_post_id']=NULL;
        $msg['category_id'] = intval($msg['category_id']);
        if(!$msg['category_id']) $msg['category_id'] = NULL;
        if($msg['title']===false) $msg['title']='';
        if($msg['msgtext']===false) $msg['msgtext']='';
        if($msg['youtube_link']===false) $msg['youtube_link']='';
        if($msg['question']===false) $msg['question']='';
        $msg['multiple'] = intval($msg['multiple']);


        $answers = array();
        if(is_array($msg['answers_exists'])) {
            foreach($msg['answers_exists'] as $answer) {
                if(!is_array($answer)) {
                    if($answer) array_push($answers, $answer);
                }
            }
        }
        if(is_array($msg['answers'])) {
            foreach($msg['answers'] as $answer) {
                if(!is_array($answer)) {
                    if($answer) array_push($answers, $answer);
                }
            }
        }
        $msg['answers'] = $answers;

        if($msg['close_comments']==1) { $msg['close_comments']='t'; } else { $msg['close_comments']='f'; }
        if($msg['is_private']==1) { $msg['is_private']='t'; } else { $msg['is_private']='f'; }
        if($msg['pos']==1) { $msg['pos']='t'; } else { $msg['pos']='f'; }
        $date = date("Y-m-d H:i:s");
        if(self::isDraftExists($msg['draft_id'], $msg['uid'], 4)) {
            $sql = "UPDATE draft_communes SET 
                        category = ?,
                        title = ?,
                        msg = ?,
                        yt_link = ?,
                        poll_question = ?,
                        poll_type = ?i,
                        poll_answers =?au,
                        close_comments = ?,
                        is_private = ?,
                        ontop = ?,
                        uid = ?i,
                        post_id = ?i,
                        commune_id = ?i,
                        date = ?
                    WHERE id=? AND uid=?i";
            $DB->query($sql, $msg['category_id'], $msg['title'], $msg['msgtext'], $msg['youtube_link'], $msg['question'], $msg['multiple'], $msg['answers'], $msg['close_comments'], $msg['is_private'], $msg['pos'], $msg['uid'], $msg['draft_post_id'], $msg['id'], $date, $msg['draft_id'], $msg['uid']);
            $id = $msg['draft_id'];
        } else {
            $sql = "INSERT INTO draft_communes (
                            category,
                            title,
                            msg,
                            yt_link,
                            poll_question,
                            poll_type,
                            poll_answers,
                            close_comments,
                            is_private,
                            ontop,
                            uid,
                            post_id,
                            commune_id,
                            date
                        ) VALUES (
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?i,
                            ?au,
                            ?,
                            ?,
                            ?,
                            ?i,
                            ?i,
                            ?i,
                            ?
                        ) RETURNING id;";
            $id = $DB->val($sql, $msg['category_id'], $msg['title'], $msg['msgtext'], $msg['youtube_link'], $msg['question'], $msg['multiple'], $msg['answers'], $msg['close_comments'], $msg['is_private'], $msg['pos'], $msg['uid'], $msg['draft_post_id'], $msg['id'], $date);
        }

        // - BEGIN атачи
        $sql = "SELECT * FROM draft_attaches WHERE draft_id = ?i AND draft_type = 2;";
        $files = $DB->rows($sql, $id);
        if($files) {
            foreach($files as $f) {
                $cf = new CFile($f['file_id']);
                $cf->table = 'file';
                $cf->Delete($cf->id);
            }
        }
        $sql = "DELETE FROM draft_attaches WHERE draft_id = ?i AND draft_type = 2;";
        $DB->query($sql, $id);

        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
        $attachedfiles = new attachedfiles($msg['attachedfiles_session']);
        $attachedfiles_files = $attachedfiles->getFiles();
        if($attachedfiles_files) {
            foreach($attachedfiles_files as $attachedfiles_file) {
                $f = new CFile($attachedfiles_file['id']);
                $f->table = 'file';
                $f->makeLink();
                $sql = "INSERT INTO draft_attaches(draft_id, draft_type, file_id) VALUES(?i, 2, ?i)";
                $DB->hold()->query($sql, $id, $f->id);
            }
            $DB->query();
        }
        // - END атачи

        $_SESSION['drafts_count'] = drafts::getCount($msg['uid']);

        return array('id'=>$id, 'date'=>$date);
    }

	/**
	 * Сохранение поста блога в черновики
	 *
	 * @param   array     $msg    Информация опосте
     * @return  array             ['id'] - ID черновика, ['date'] - дата сохранения черновика
	 */
	function SaveBlog($msg){
		global $DB;
        $msg['draft_id'] = intval($msg['draft_id']);
        if(!intval($msg['draft_post_id'])) $msg['draft_post_id']=NULL;

        if(isset($msg['msg_name'])) {
            if($msg['msg_name']===false) { $msg['name']=''; } else { $msg['name']=$msg['msg_name']; }
        } else {
            if($msg['name']===false) $msg['name']='';
        }
        
        if($msg['yt_link']==false) $msg['yt_link']='';
        $msg['msgtext'] = $msg['msg'];
        if($msg['msgtext']===false) $msg['msgtext']='';
        if(!$msg['close_comments']) { $msg['close_comments']='f'; } else { $msg['close_comments']='t'; }
        if(!$msg['is_private']) { $msg['is_private']='f'; } else { $msg['is_private']='t'; }
        if($msg['question']==false) $msg['question']='';
        if(!intval($msg['draft_post_id'])) { $msg['draft_post_id'] = NULL; }
        list($msg['category']) = explode("|", $msg['category']);
        $msg['category'] = (int)$msg['category'];
        if($msg['ontop']!='t') { $msg['ontop'] = 'f'; }
        $msg['multiple'] = intval($msg['multiple']);

        $answers = array();
        if(is_array($msg['answers_exists'])) {
            foreach($msg['answers_exists'] as $answer) {
                if(!is_array($answer)) {
                    if($answer) array_push($answers, $answer);
                }
            }
        }
        if(is_array($msg['answers'])) {
            foreach($msg['answers'] as $answer) {
                if(!is_array($answer)) {
                    if($answer) array_push($answers, $answer);
                }
            }
        }
        $msg['answers'] = $answers;

        $date = date("Y-m-d H:i:s");
        if(self::isDraftExists($msg['draft_id'], $msg['uid'], 3)) {
            $sql = "UPDATE draft_blogs SET 
                        category = ?,
                        title = ?,
                        msgtext = ?,
                        yt_link = ?,
                        is_close_comments = ?,
                        is_private = ?,
                        poll_question = ?,
                        poll_type = ?i,
                        poll_answers =?au,
                        uid = ?i,
                        post_id = ?i,
                        date = ?
                    WHERE id=? AND uid=?i";
            $DB->query($sql, $msg['category'], $msg['name'], $msg['msgtext'], $msg['yt_link'], $msg['close_comments'], $msg['is_private'], $msg['question'], $msg['multiple'], $msg['answers'], $msg['uid'], $msg['draft_post_id'], $date, $msg['draft_id'], $msg['uid']);
            $id = $msg['draft_id'];
        } else {
            $sql = "INSERT INTO draft_blogs (
                        category,
                        title,
                        msgtext,
                        yt_link,
                        is_close_comments,
                        is_private,
                        poll_question,
                        poll_type,
                        poll_answers,
                        uid,
                        post_id,
                        date
                        ) VALUES (
                            ?i,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?i,
                            ?au,
                            ?i,
                            ?i,
                            ?
                        ) RETURNING id;";
            $id = $DB->val($sql, $msg['category'], $msg['name'], $msg['msgtext'], $msg['yt_link'], $msg['close_comments'], $msg['is_private'], $msg['question'], $msg['multiple'], $msg['answers'], $msg['uid'], $msg['draft_post_id'], $date, $msg['answers_a']);
        }

        // - BEGIN атачи
        $sql = "SELECT * FROM draft_attaches WHERE draft_id = ?i AND draft_type = 1;";
        $files = $DB->rows($sql, $id);
        if($files) {
            foreach($files as $f) {
                $cf = new CFile($f['file_id']);
                $cf->table = 'file';
                $cf->Delete($cf->id);
            }
        }
        $sql = "DELETE FROM draft_attaches WHERE draft_id = ?i AND draft_type = 1;";
        $DB->query($sql, $id);

        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
        $attachedfiles = new attachedfiles($msg['attachedfiles_session']);
        $attachedfiles_files = $attachedfiles->getFiles();
        if($attachedfiles_files) {
            foreach($attachedfiles_files as $attachedfiles_file) {
                $f = new CFile($attachedfiles_file['id']);
                $f->table = 'file';
                $f->makeLink();
                $sql = "INSERT INTO draft_attaches(draft_id, draft_type, file_id) VALUES(?i, 1, ?i)";
                $DB->hold()->query($sql, $id, $f->id);
            }
            $DB->query();
        }
        // - END атачи

        $_SESSION['drafts_count'] = drafts::getCount($msg['uid']);

        return array('id'=>$id, 'date'=>$date);
    }

	/**
	 * Сохранение личного сообщения в черновики
	 *
	 * @param   array     $msg       Информация о сообщении
     * @return  array               ['id'] - ID черновика, ['date'] - дата сохранения черновика
	 */
	function SaveContacts($msg){
		global $DB;

        if($msg['msg']===false) $msg['msg']='';
        $msg['draft_id'] = intval($msg['draft_id']);
        $date = date("Y-m-d H:i:s");

        if(self::isDraftExists($msg['draft_id'], $msg['uid'], 2)) {
            $sql = "UPDATE draft_contacts SET 
                            msg = ?u,
                            to_login = ?u,
                            date = ?
                    WHERE id=? AND uid=?i";
            $DB->query($sql, $msg['msg'], $msg['to_login'], $date, $msg['draft_id'], $msg['uid']);
            $id = $msg['draft_id'];
        } else {
            $sql = "INSERT INTO draft_contacts (
                            msg,
                            to_login,
                            date,
                            uid
                        ) VALUES (
                            ?u,
                            ?u,
                            ?,
                            ?i
                        ) RETURNING id;";
            $id = $DB->val($sql, $msg['msg'], $msg['to_login'], $date, $msg['uid']);
        }

        // - BEGIN атачи
        $sql = "SELECT * FROM draft_attaches WHERE draft_id = ?i AND draft_type = 3;";
        $files = $DB->rows($sql, $id);
        if($files) {
            foreach($files as $f) {
                $cf = new CFile($f['file_id']);
                $cf->table = 'file';
                $cf->Delete($cf->id);
            }
        }
        $sql = "DELETE FROM draft_attaches WHERE draft_id = ?i AND draft_type = 3;";
        $DB->query($sql, $id);

        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
        $attachedfiles = new attachedfiles($msg['attachedfiles_session']);
        $attachedfiles_files = $attachedfiles->getFiles();
        if($attachedfiles_files) {
            foreach($attachedfiles_files as $attachedfiles_file) {
                $f = new CFile($attachedfiles_file['id']);
                $f->table = 'file';
                $f->makeLink();
                $sql = "INSERT INTO draft_attaches(draft_id, draft_type, file_id) VALUES(?i, 3, ?i)";
                $DB->hold()->query($sql, $id, $f->id);
            }
            $DB->query();
        }
        // - END атачи

        $_SESSION['drafts_count'] = drafts::getCount($msg['uid']);

        return array('id'=>$id, 'date'=>$date);
    }

    /**
	 * Сохранение проекта в черновики
	 *
	 * @param   array     $prj    Информация о проекте
     * @return  array             ['id'] - ID черновика, ['date'] - дата сохранения черновика
	 */
	function SaveProject($prj, $attachedfiles_files = false){
		global $DB;

        $categories = "";
        if(is_array($prj['categories'])) {
            foreach($prj['categories'] as $key => $value) {
                if(!is_array($value)) {
                    $categories = $categories.$value."|".intval($prj['subcategories'][$key]).",";
                }
            }
            $categories = preg_replace("/,$/", "", $categories);
        }
        if($prj['name']===false) $prj['name']='';
        if($prj['descr']===false) $prj['descr']='';
        if(!$prj['end_date']) $prj['end_date'] = NULL;
        if(!$prj['win_date']) $prj['win_date'] = NULL;
        $prj['budget_type'] = intval($prj['budget_type']);
        if(!intval($prj['draft_prj_id'])) { $prj['prj_id'] = NULL; } else { $prj['prj_id'] = $prj['draft_prj_id']; }
        $prj['draft_id'] = intval($prj['draft_id']);
        if(!isset($prj['kind'])) $prj['kind']=7;
        $prj['cost'] = floatval($prj['cost']);
        $date = date("Y-m-d H:i:s");
        $prj['strong_top'] = hasPermissions('projects') ? (int) $prj['strong_top'] : 0;
        if(self::isDraftExists($prj['draft_id'], $prj['uid'], 1)) {
            $sql = "UPDATE draft_projects SET 
                            name = ?u,
                            descr = ?u,
                            cost = ?,
                            currency = ?i,
                            kind = ?i,
                            pro_only = ?,
                            strong_top = ?i,
                            end_date = ?,
                            win_date = ?,
                            country = ?i,
                            city = ?i,
                            categories = ?,
                            date = ?,
                            prj_id = ?,
                            priceby = ?i,
                            prefer_sbr = ?,
                            budget_type = ?i,
                            verify_only = ?,
                            contacts = ?
                    WHERE id=? AND uid=?i";
            $DB->query($sql, $prj['name'], $prj['descr'], $prj['cost'], $prj['currency'], $prj['kind'], ($prj['pro_only']==1?'t':'f'), $prj['strong_top'], $prj['end_date'], $prj['win_date'], (int)$prj['country'], (int)$prj['city'], $categories, $date, $prj['prj_id'], intval($prj['priceby']), ($prj['prefer_sbr']==1?'t':'f'), $prj['budget_type'], $prj['verify_only'],  $prj['contacts'], $prj['draft_id'], $prj['uid']);
            $id = $prj['draft_id'];
        } else {
            $new_draft = true;
            $sql = "INSERT INTO draft_projects (
                            name,
                            descr,
                            cost,
                            currency,
                            kind,
                            pro_only,
                            end_date,
                            win_date,
                            country,
                            city,
                            categories,
                            date,
                            uid,
                            prj_id,
                            priceby,
                            prefer_sbr,
                            budget_type,
                            strong_top,
                            verify_only,
                            contacts
                        ) VALUES (
                            ?u,
                            ?u,
                            ?,
                            ?i,
                            ?i,
                            ?,
                            ?,
                            ?,
                            ?i,
                            ?i,
                            ?,
                            ?,
                            ?i,
                            ?,
                            ?i,
                            ?,
                            ?i,
                            ?i,
                            ?,
                            ?
                        ) RETURNING id;";
            $id = $DB->val($sql, $prj['name'], $prj['descr'], $prj['cost'], $prj['currency'], $prj['kind'], ($prj['pro_only']==1?'t':'f'), $prj['end_date'], $prj['win_date'], $prj['country'], $prj['city'], $categories, $date, $prj['uid'], $prj['prj_id'], intval($prj['priceby']), ($prj['prefer_sbr']==1?'t':'f'), $prj['budget_type'], (int)$prj['strong_top'], $prj['verify_only'], $prj['contacts']);
        }

        // - BEGIN атачи
        if(!$attachedfiles_files) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes//uploader/uploader.php");
            $mask_files = array(1,3);
            $uploader = new uploader(current($prj['IDResource']));
            $attachedfiles_files = $uploader->getFiles($mask_files);
        }
        
        $file_id = array();
        // массив с ID файлов которые не надо удалять
        $noDeletedFiles = array();
        foreach($attachedfiles_files as $attachedfiles_file) {
            $noDeletedFiles[] = $attachedfiles_file['id'];
            // старые файлы не трогаем
            if ($attachedfiles_file['status'] == 3) {
                continue;
            }
            if(in_array($f->id, $file_id)) continue; // Чтобы не было дублей
            $file_id[] = $f->id;
            $f = new CFile($attachedfiles_file['id']);
            $f->table = 'file';
            $f->makeLink();
            $sql = "INSERT INTO draft_attaches(draft_id, draft_type, file_id) VALUES(?i, 4, ?i)";
            $DB->hold()->query($sql, $id, $f->id);
        }
        if ($DB->sqls) {
            $DB->query();
        }
        
        if ($uploader && $attachedfiles_files) {
            $uploader->setStatusFiles(uploader::STATUS_CREATE, uploader::STATUS_ADDED);
        }

        $sqlNoDeletedFiles = count($noDeletedFiles) ? $DB->parse('AND file_id NOT IN (?l)', $noDeletedFiles) : '';
        $sql = "SELECT * FROM draft_attaches WHERE draft_id = ?i AND draft_type = 4 $sqlNoDeletedFiles;";
        $files = $DB->rows($sql, $id, $noDeletedFiles);
        if($files) {
            foreach($files as $f) {
                $cf = new CFile($f['file_id']);
                $cf->table = 'file';
                $cf->Delete($cf->id);
            }
        }
        $sql = "DELETE FROM draft_attaches WHERE draft_id = ?i AND draft_type = 4 $sqlNoDeletedFiles;";
        $DB->query($sql, $id, array());
            
        // - END атачи

        $_SESSION['drafts_count'] = drafts::getCount($msg['uid']);

        return array('id'=>$id, 'date'=>$date);
	}
    
	/**
	 * Сохранение проекта в черновики (новый шаблон)
	 *
	 * @param   array     $prj    Информация о проекте
     * @return  array             ['id'] - ID черновика, ['date'] - дата сохранения черновика
	 */
	function SaveProjectNew($prj, $attachedfiles_files = false){
		global $DB;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
        
        $categories = "";
        $check = array();
        for ($i = 0; $i <= 2; $i++) {
            $catID = __paramValue('int', $prj['project_profession' . $i . '_columns'][0]);
            $subcatID = __paramValue('int', $prj['project_profession' . $i . '_spec_columns'][0]);
            if ($catID || $subcatID) {
                $categories .= $catID . '|' . $subcatID . ',';
            }
        }
        $categories = preg_replace("/,$/", "", $categories);
        
        
        if($prj['name']===false) $prj['name']='';
        if($prj['descr']===false) $prj['descr']='';
        if(!$prj['end_date']) $prj['end_date'] = NULL;
        if(!$prj['win_date']) $prj['win_date'] = NULL;
        $prj['budget_type'] = intval($prj['budget_type']);
        if(!intval($prj['draft_prj_id'])) { $prj['prj_id'] = NULL; } else { $prj['prj_id'] = $prj['draft_prj_id']; }
        $prj['draft_id'] = intval($prj['draft_id']);
        if(!isset($prj['kind'])) $prj['kind']=7;
        $prj['cost'] = $prj['agreement'] ? 0 : floatval($prj['cost']);
        $date = date("Y-m-d H:i:s");
        $prj['strong_top'] = hasPermissions('projects') ? (int) $prj['strong_top'] : 0;
        $prj['verify_only'] = (bool)$prj['verify_only'];

        // платные опции
        $prj['urgent'] = (bool)$prj['urgent'];
        $prj['hide'] = (bool)$prj['hide'];
        $topDays = $prj['top_ok'] ? $prj['top_days'] : 0;
        if ($prj['logo_ok']) {
            $logoAttach = new attachedfiles($prj['logo_attachedfiles_session']);
            $logoFiles = $logoAttach->getFiles();
            if (is_array($logoFiles) && count($logoFiles)) {
                $logoFile = array_pop($logoFiles); // файлов может быть несколько, берем последний
                $logoAttach->setStatusTo3($logoFile['id']);
                $logoFileID = $logoFile['id'];
            } elseif ($prj['logo_file_id']) {
                $logoFileID = $prj['logo_file_id'];
            }
            $logoLink = $prj['link'];
            
        }
        
        if(self::isDraftExists($prj['draft_id'], $prj['uid'], 1, $prj['prj_id'])) {
            $sql = "UPDATE draft_projects SET 
                            name = ?u,
                            descr = ?u,
                            cost = ?,
                            currency = ?i,
                            kind = ?i,
                            pro_only = ?,
                            strong_top = ?i,
                            end_date = ?,
                            win_date = ?,
                            country = ?i,
                            city = ?i,
                            categories = ?,
                            date = ?,
                            prj_id = ?,
                            priceby = ?i,
                            prefer_sbr = ?,
                            budget_type = ?i,
                            verify_only = ?,
                            urgent = ?,
                            hide = ?,
                            top_days = ?i,
                            logo_id = ?,
                            logo_link = ?,
                            contacts = ?
                    WHERE (id=? OR prj_id=?)AND uid=?i";
            $DB->query($sql, $prj['name'], $prj['descr'], $prj['cost'], $prj['currency_db_id'], $prj['kind'], ($prj['pro_only']==1?'t':'f'), $prj['strong_top'], $prj['end_date'], $prj['win_date'], (int)$prj['project_location_columns'][0], (int)$prj['project_location_columns'][1], $categories, $date, $prj['prj_id'], intval($prj['priceby_db_id']), ($prj['prefer_sbr']==1?'t':'f'), $prj['budget_type'], $prj['verify_only'], $prj['urgent'], $prj['hide'], $topDays, $logoFileID, $logoLink, $prj['contacts'], $prj['draft_id'], $prj['prj_id'], $prj['uid']);
            $id = $prj['draft_id'];
        } else {
            $new_draft = true;
            $sql = "INSERT INTO draft_projects (
                            name,
                            descr,
                            cost,
                            currency,
                            kind,
                            pro_only,
                            end_date,
                            win_date,
                            country,
                            city,
                            categories,
                            date,
                            uid,
                            prj_id,
                            priceby,
                            prefer_sbr,
                            budget_type,
                            strong_top,
                            verify_only,
                            urgent,
                            hide, 
                            top_days,
                            logo_id,
                            logo_link,
                            contacts
                        ) VALUES (
                            ?u,
                            ?u,
                            ?,
                            ?i,
                            ?i,
                            ?,
                            ?,
                            ?,
                            ?i,
                            ?i,
                            ?,
                            ?,
                            ?i,
                            ?,
                            ?i,
                            ?,
                            ?i,
                            ?i,
                            ?,
                            ?,
                            ?,
                            ?i,
                            ?,
                            ?,
                            ?
                        ) RETURNING id;";
            $id = $DB->val($sql, $prj['name'], $prj['descr'], $prj['cost'], $prj['currency_db_id'], $prj['kind'], ($prj['pro_only']==1?'t':'f'), $prj['end_date'], $prj['win_date'], (int)$prj['project_location_columns'][0], (int)$prj['project_location_columns'][1], $categories, $date, $prj['uid'], $prj['prj_id'], intval($prj['priceby_db_id']), ($prj['prefer_sbr']==1?'t':'f'), $prj['budget_type'], (int)$prj['strong_top'], $prj['verify_only'], $prj['urgent'], $prj['hide'], $topDays, $logoFileID, $logoLink, $prj['contacts']);
        }

        // - BEGIN атачи
        if(!$attachedfiles_files) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes//uploader/uploader.php");
            $mask_files = array(1,3);
            $uploader = new uploader(current($prj['IDResource']));
            $attachedfiles_files = $uploader->getFiles($mask_files);
        }
        
        $file_id = array();
        // массив с ID файлов которые не надо удалять
        $noDeletedFiles = array();
        foreach($attachedfiles_files as $attachedfiles_file) {
            $noDeletedFiles[] = $attachedfiles_file['id'];
            // старые файлы не трогаем
            if ($attachedfiles_file['status'] == 3) {
                continue;
            }
            if(in_array($f->id, $file_id)) continue; // Чтобы не было дублей
            $file_id[] = $f->id;
            $f = new CFile($attachedfiles_file['id']);
            $f->table = 'file';
            $f->makeLink();
            $sql = "INSERT INTO draft_attaches(draft_id, draft_type, file_id) VALUES(?i, 4, ?i)";
            $DB->hold()->query($sql, $id, $f->id);
        }
        if ($DB->sqls) {
            $DB->query();
        }
        
        if ($uploader && $attachedfiles_files) {
            $uploader->setStatusFiles(uploader::STATUS_CREATE, uploader::STATUS_ADDED);
        }

        $sqlNoDeletedFiles = count($noDeletedFiles) ? $DB->parse('AND file_id NOT IN (?l)', $noDeletedFiles) : '';
        $sql = "SELECT * FROM draft_attaches WHERE draft_id = ?i AND draft_type = 4 $sqlNoDeletedFiles;";
        $files = $DB->rows($sql, $id, $noDeletedFiles);
        if($files) {
            foreach($files as $f) {
                $cf = new CFile($f['file_id']);
                $cf->table = 'file';
                $cf->Delete($cf->id);
            }
        }
        $sql = "DELETE FROM draft_attaches WHERE draft_id = ?i AND draft_type = 4 $sqlNoDeletedFiles;";
        $DB->query($sql, $id, array());
            
        // - END атачи

        $_SESSION['drafts_count'] = drafts::getCount($msg['uid']);

        return array('id'=>$id, 'date'=>$date);
	}

    /**
     * Проверяет есть ли запрашиваемый черновик
     *
     * @param   integer     $id       ID черновика
     * @param   integer     $uid      ID пользователя
     * @param   integer     $type     Тип черновика
     * @param   integer     $prj_id   Идентификатор проекта на случай, если сохраняем уже опубликованый проект
     * @return  boolean             true - есть, false - нет
     */
    function isDraftExists($id, $uid, $type, $prj_id = 0) {
        global $DB;
        switch($type) {
            case 1:
                // Проекты
                $sql = "SELECT id FROM draft_projects WHERE (id=?i OR prj_id = ?i) AND uid=?i";
                return $DB->val($sql, $id, $prj_id, $uid);
            case 2:
                // Личка
                $sql = "SELECT id FROM draft_contacts WHERE id=?i AND uid=?i";
                break;
            case 3:
                // Блоги
                $sql = "SELECT id FROM draft_blogs WHERE id=?i AND uid=?i";
                break;
            case 4:
                // Сообщества
                $sql = "SELECT id FROM draft_communes WHERE id=?i AND uid=?i";
                break;
        }
        return $DB->val($sql, $id, $uid);
    }

    /**
     * Получает список приатаченных файлов
     *
     * @param   integer $draft_id   ID черновика
     * @param   integer $draft_type Тип черновика
     * @return  array               Информация о файлах
     *
     */
    function getAttachedFiles($draft_id, $draft_type, $uid = null) {
        global $DB;
        switch($draft_type) {
            case 1:
                $table = "blogs";
                break;
            case 2:
                $table = "communes";
                break;
            case 3:
                $table = "contacts";
                break;
            case 4:
                $table = "projects";
                break;
            default:
                $table = "";
                break;
        }
        $check = false;
        if($table) {
            $sql = "SELECT id FROM draft_{$table} WHERE id=?i AND uid=?i";
            $check = $DB->val($sql, $draft_id, $uid  === null ? get_uid(false) : $uid);
        }
        if($check) {
            $sql = "SELECT file_id FROM draft_attaches WHERE draft_id = ?i AND draft_type = ?i";
            $attaches = $DB->rows($sql, $draft_id, $draft_type);
            if($attaches) {
                $fAttaches = array();
                foreach($attaches as $attach) { array_push($fAttaches, $attach['file_id']); }
            }
        }

        return $fAttaches;
    }


    /**
     * Удаление черновика
     *
     * @param   integer     $id     ID черновика
     * @param   integer     $uid    ID пользователя
     * @param   integer     $type   Тип черновика
     */
    function DeleteDraft($id, $uid, $type, $delete_files=false) {
        global $DB;
        switch($type) {
            case 1:
                // Проекты
                $sql = "DELETE FROM draft_projects WHERE id=?i AND uid=?i";
                break;
            case 2:
                // Личка
                if($delete_files) {
                    $sql = "SELECT * FROM draft_attaches WHERE draft_id = ?i AND draft_type = 3;";
                    $attaches = $DB->rows($sql, $id);
                    if($attaches) {
                        foreach($attaches as $attach) {
                            $f = new CFile($attach['file_id']);
                            $f->Delete($f->id);
                        }
                        $sql = "DELETE FROM draft_attaches WHERE draft_id = ?i AND draft_type = 3;";
                        $DB->query($sql, $id);
                    }
                }
                $sql = "DELETE FROM draft_contacts WHERE id=?i AND uid=?i";
                break;
            case 3:
                // Блоги
                if($delete_files) {
                    $sql = "SELECT * FROM draft_attaches WHERE draft_id = ?i AND draft_type = 1;";
                    $attaches = $DB->rows($sql, $id);
                    if($attaches) {
                        foreach($attaches as $attach) {
                            $f = new CFile($attach['file_id']);
                            $f->Delete($f->id);
                        }
                        $sql = "DELETE FROM draft_attaches WHERE draft_id = ?i AND draft_type = 1;";
                        $DB->query($sql, $id);
                    }
                }
                $sql = "DELETE FROM draft_blogs WHERE id=?i AND uid=?i";
                break;
            case 4:
                // Сообщества
                $sql = "DELETE FROM draft_communes WHERE id=?i AND uid=?i";
                break;

        }
        $DB->query($sql, $id, $uid);

        $_SESSION['drafts_count'] = drafts::getCount($uid);
    }

    /**
     * Удаление черновика проекта по ID проекта
     *
     * @param   integer $prj_id     ID проекта
     */
    function DeleteDraftByPrjID($prj_id) {
        global $DB;
        $sql = "DELETE FROM draft_projects WHERE prj_id = ?i";
        $DB->query($sql, $prj_id);
    }

    /**
     * Проверка наличия ранее сохраненных черновиков для лички
     *
     * @param       string  $to_login   Логин получателя
     * @param       integer $uid        ID пользователя   
     * @return      integer             Кол-во черновиков
     */
    function CheckContacts($to_login, $uid) {
        global $DB;
        $sql = "SELECT COUNT(id) FROM draft_contacts WHERE uid=?i AND to_login=?u";
        $count = $DB->val($sql, $uid, $to_login);
        return (int) $count;
    }

    /**
     * Проверка наличия ранее сохраненных черновиков для проектов
     *
     * @param       integer $uid        ID пользователя   
     * @return      integer             Кол-во черновиков
     */
    function CheckProjects($uid) {
        global $DB;
        $sql = "SELECT COUNT(id) FROM draft_projects WHERE uid=?i";
        $count = $DB->val($sql, $uid);
        return (int) $count;
    }

    /**
     * Проверка наличия ранее сохраненных черновиков для блогов
     *
     * @param       integer $uid        ID пользователя   
     * @return      integer             Кол-во черновиков
     */
    function CheckBlogs($uid) {
        global $DB;
        $sql = "SELECT count(draft_blogs.id)
                FROM draft_blogs 
                JOIN blogs_groups ON draft_blogs.category = blogs_groups.id 
                LEFT JOIN blogs_msgs ON blogs_msgs.id = draft_blogs.post_id
                LEFT JOIN blogs_blocked b ON blogs_msgs.thread_id = b.thread_id 
                WHERE draft_blogs.uid = ?i";
        $count = $DB->val($sql, $uid);
        return (int) $count;
    }

    /**
     * Проверка наличия ранее сохраненных черновиков для сообществ
     *
     * @param       integer $uid        ID пользователя   
     * @return      integer             Кол-во черновиков
     */
    function CheckCommune($uid) {
        global $DB;
        $sql = "SELECT COUNT(id) FROM draft_communes WHERE uid=?i";
        $count = $DB->val($sql, $uid);
        return (int) $count;
    }

    /**
     * Получение всех черновиков пользователя
     *
     * @param   integer     $uid    ID пользователя
     * @param   integer     $type   Тип черновика
     * @param   integer     $limit  количество черновиков (пока только для проектов)
     * @return  array               Информация о черновиках
     */
    function getUserDrafts($uid, $type, $limit = null) {
        global $DB;
        switch($type) {
            case 1:
                // Проекты
                $limitSql = $limit ? ' LIMIT ' . $limit . ' ' : '';
                $sql = "SELECT *, to_char(date, 'DD.MM.YYYY HH24:MI') as pdate FROM draft_projects WHERE uid=?i ORDER BY date DESC $limitSql";
                break;
            case 2:
                // Личка
                $sql = "SELECT draft_contacts.*, users.uname, users.usurname, to_char(draft_contacts.date, 'DD.MM.YYYY HH24:MI') as pdate FROM draft_contacts JOIN users ON draft_contacts.to_login = users.login WHERE draft_contacts.uid = ?i ORDER BY draft_contacts.date DESC";
                break;
            case 3:
                // Блоги
                $sql = "
                    SELECT draft_blogs.*, blogs_groups.t_name as category_title, to_char(draft_blogs.date, 'DD.MM.YYYY HH24:MI') as pdate, b.thread_id AS is_blocked 
                    FROM draft_blogs 
                    JOIN blogs_groups ON draft_blogs.category = blogs_groups.id 
                    LEFT JOIN blogs_msgs ON blogs_msgs.id = draft_blogs.post_id
                    LEFT JOIN blogs_blocked b ON blogs_msgs.thread_id = b.thread_id 
                    WHERE draft_blogs.uid = ?i 
                    ORDER BY draft_blogs.date DESC";
                break;
            case 4:
                // Сообщества
                $sql = "SELECT draft_communes.*, commune.name as commune_title, to_char(draft_communes.date, 'DD.MM.YYYY HH24:MI') as pdate, (author_id = {$uid} OR m.id IS NOT NULL) AS is_member
                    FROM draft_communes 
                    JOIN commune ON draft_communes.commune_id = commune.id 
                    LEFT JOIN commune_members m ON m.commune_id = draft_communes.commune_id AND m.user_id = {$uid} AND is_banned = FALSE AND is_deleted = FALSE
                    WHERE draft_communes.uid = ?i ORDER BY draft_communes.date DESC";
                break;
        }
        return $DB->rows($sql, $uid);
    }

    /**
    * Получить информацию о черновике
    *
    * @param    integer     $draft_id   ID черновика
    * @param    integer     $uid        ID пользователя
    * @param    integer     $type       Тип черновика
    * @return   array                   Информация о черновике
    */
    function getDraft($draft_id, $uid, $type) {
        global $DB;
        switch($type) {
            case 1:
                // Проекты
                $sql = "SELECT *, to_char(end_date, 'DD-MM-YYYY') as p_end_date, to_char(win_date, 'DD-MM-YYYY') as p_win_date FROM draft_projects WHERE id=?i AND uid=?i";
                break;
            case 2:
                // Личка
                $sql = "SELECT * FROM draft_contacts WHERE id=?i AND uid = ?i";
                break;
            case 3:
                // Блоги
                $sql = "SELECT * FROM draft_blogs WHERE id=?i AND uid = ?i";
                break;
            case 4:
                // Сообщества
                $sql = "SELECT * FROM draft_communes WHERE id=?i AND uid = ?i";
                break;
        }
        $ret = $DB->row($sql, $draft_id, $uid);
        if($ret) {
            switch($type) {
                case 3:
                    $ret['poll_answers'] = $DB->array_to_php($ret['poll_answers']);
                    break;
                case 4:
                    $ret['poll_answers'] = $DB->array_to_php($ret['poll_answers']);
                    break;
            }
        }
        return $ret;
    }
    
    /**
     * Возвращает количество черновиков пользователя
     * @param  ingeter   $uid   uid пользователья
     * @return integer          количество имеющихся черновиков
     * 
     */
    function getCount($uid) {
        global $DB;
        $b = $DB->val("SELECT COUNT(*) FROM draft_blogs WHERE uid = ?", $uid);
        $c = $DB->val("SELECT COUNT(*) FROM draft_communes WHERE uid = ?", $uid);
        $m = $DB->val("SELECT COUNT(*) FROM draft_contacts WHERE uid = ?", $uid);
        $p = $DB->val("SELECT COUNT(*) FROM draft_projects WHERE uid = ?", $uid);
        $count = $b + $c + $m + $p;
        return $count;
    }
    
    /**
     * Возвращает количество черновиков пользователя отдельно по каждому разделу
     * @param  ingeter   $uid   uid пользователья
     * @return array            массив со счетчиками
     * 
     */
    function getCounts($uid) {
        global $DB;
        $b = $DB->val("SELECT COUNT(*) FROM draft_blogs WHERE uid = ?", $uid);
        $c = $DB->val("SELECT COUNT(*) FROM draft_communes WHERE uid = ?", $uid);
        $m = $DB->val("SELECT COUNT(*) FROM draft_contacts WHERE uid = ?", $uid);
        $p = $DB->val("SELECT COUNT(*) FROM draft_projects WHERE uid = ?", $uid);
        return array('blogs'=>$b, 'communes'=>$c, 'contacts'=>$m, 'projects'=>$p);
    }

}

?>
