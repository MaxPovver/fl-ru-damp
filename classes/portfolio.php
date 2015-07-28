<?
/**
 * Подключаем файл для работы с профессиями пользователя 
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");

$portf_cost[0] = 100000;	 //usd
$portf_cost[1] = 100000;	 //euro
$portf_cost[2] = 5000000;    //rur
$portf_cost[3] = 100000;	 //fm


define ('PORTF_TIME', 100);
/**
 * Класс для работы с портфолио
 *
 */
class portfolio 
{
  	/**
  	 * Максимальное количество лучших работ
  	 *
  	 */
    const MAX_BEST_WORKS = 12;
    
    /**
     * максимальные размеры для превью
     */
    const PREVIEW_MAX_WIDTH = 200;
    const PREVIEW_MAX_HEIGHT = 200;
    
    /**
     * Максимальное количество времени в поле "Потрачено времени"
     */ 
    const MAX_TIME_VALUE = 100;
    
    /**
     * Размер названия файла в режиме редактирования
     */
    const FILE_NAME_LENGTH_EDIT = 8;
    
    public static $portf_cost = array(
        0 => 100000,  //usd
        1 => 100000,  //euro
        2 => 5000000, // rub
        3 => 100000   //fm
    );
    
	/**
	 * Добавить работу в порфолио
	 *
	 * @param integer $fid     		 ИД пользоввателя
	 * @param string  $name    		 Название работы
	 * @param string  $pict    		 Основное изображение
	 * @param string  $sm_pict 		 Уменьшенное изображение
	 * @param string  $link    		 Ссылка на работу
	 * @param string  $descr   		 Описание работы
	 * @param integer $prof    		 Специализация работы
	 * @param integer $cost    		 Стоимость работы
	 * @param integer $cost_type 	 Тип стоимости (USD, EUR, RUB, FM) (см. self::GetSpecPortf())
	 * @param integer $time_type 	 Тип времени работы
	 * @param integer $time_value 	 Время работы
	 * @param integer $prev_type  	 Тип превью (0 - Графическое превью, 1 - Текстовое превью)
	 * @param string  $file_error    Возвращает сообщение об ошибке файла
	 * @param string  $preview_error Возвращает Превью ошибка
	 * @param integer $new_position  Новая позиция
	 * @param integer $in_shop       Работа в магазине  
	 * @param integer $is_video      Есть ли видео 
	 * @param string  $video_link    Ссылка на видео
	 * @return string Сообщение об ошибке
	 */
    function AddPortf($fid, $name, $pict, $sm_pict, $link, $descr, $prof, $cost, $cost_type, $time_type, $time_value, $prev_type, &$file_error, &$preview_error,$new_position,$in_shop=0,$is_video='f',$video_link='')
    {
        global $DB; 
        $sp = 'f';
        $filename = '';
        $dir = get_login($fid);

        /**
         * Отдельно загруженное превью.
         */
        if ($sm_pict->size > 0 && $sm_pict->id <= 0)
        {
            $sm_pict->max_image_size = array('width'=>200,'height'=>200, 'less' => 0);
            $sm_pict->resize = 1;
            $sm_pict->proportional = 1;
            $sm_pict->prefix_file_name = "sm_";
            $sm_pict->max_size = 102400;
            //$sm_pict->quality = 80;
            $filename = $sm_pict->MoveUploadedFile($dir."/upload");
            $preview_error = $sm_pict->StrError();
            if ($preview_error) {
                return $preview_error;
            } else {
                $mp = true;
            }
            
            // генерируем неанимированное gif превью для ответов в проектах (только для gif файлов)
            // файл будет такой: st_sm_f_имя файла.gif
            if ($sm_pict->image_size['type'] == 1) {
                $static_preview = 'st_' . $sm_pict->name;
                $sm_pict->resizeImage( $sm_pict->path . $static_preview, 200, 200, 'auto', true );
            } else {
                $static_preview = $sm_pict->name;
            }

            //$cfile = new CFile();
            //$cfile->Delete(0,"users/{$l_dir}/upload/",$filename_original); // удаляем оригинал превьюшки

            $sql = "SELECT show_preview FROM portf_choise WHERE user_id=?i AND prof_id=?i";
            $res = $DB->row($sql, $fid, $prof);
            $show_preview = $res['show_preview'];
            if ($show_preview == "t")
            {
                $sp = 't';
            }
        } else {
            $mp = true;
            $filename = $sm_pict->name;
        }        

        if ($pict->size > 0 && $pict->id <= 0)
        {
            $pict->max_size = 10485760;
            $pictname = $pict->MoveUploadedFile($dir."/upload");
            if (!isNulArray($pict->error))
            {
                $file_error = true;
                return $pict->StrError;
            }
            /*if (is_array($pictname))
                list($pictname, $filename) = $pictname;*/

            if (isNulArray($pict->error) && in_array($pict->getext(), $GLOBALS['graf_array']) && !$filename)
            {
                //print "2";exit;
               /**
                * Делаем превью.
                */
                $pict->proportional = 1;
                //$pict->imgtosmall()

                if (!$pict->img_to_small("sm_".$pictname,array('width'=>200,'height'=>200)))
                {
                    $mp = false;
                }
                else
                {
                    $mp = true;
                    $filename = "sm_".$pictname;
                    $static_preview = $filename;
                }
                
                $sql = "SELECT show_preview FROM portf_choise WHERE user_id=?i AND prof_id=?i";
                $res = $DB->row($sql, $fid, $prof);
                $show_preview = $res['show_preview'];
                if ($show_preview == "t")
                {
                    $sp = 't';
                    if (!$mp)
                    {
                        $file_error .= "Невозможно уменьшить картинку.";
                        $sp = 'f';
                    }
                }
            }
        } else {
            $pictname = $pict->name;
        }
        
        // для картинок: если превью загружено отдельно - делаем отдельное привью из оригинальной большой картинки        
        if ( $pictname && $filename && $pictname != substr($filename, 3, strlen($filename)) ) {
            $sm_pict = new CFile();
            $fullDir = 'users/'. substr($dir, 0 ,2) . '/' . $dir . '/upload/';
            $sm_pict->GetInfo( $fullDir . $pictname );
            
            if ( $sm_pict->size > 0 ) {
                $ext = $sm_pict->getext();
                
                if ( in_array($ext, $GLOBALS['graf_array']) && $ext != 'swf' ) {
                    $sm_pict->table   = 'file_template';
                    $sm_pict->quality = 100;
                    $sm_pict->resizeImage( $fullDir . 'tn_' . $sm_pict->name, 200, 200, 'auto', true );
                }
            }
        }
        
        if($in_shop==1) { $in_shop='t'; } else { $in_shop = 'f'; }
        $prof = professions::GetProfessionOrigin($prof);
        $sModVal = is_pro() ? '-2' : '0';
        $sql = "INSERT INTO portfolio (name, link, pict, prev_pict, static_preview, descr, cost, cost_type, time_type, time_value, prof_id, user_id, show_preview, prev_type, norder, in_shop, is_video, video_link, moderator_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, $sModVal) RETURNING id";
        $sId = $DB->val($sql, $name, $link, $pictname, $filename, $static_preview, $descr, $cost, $cost_type, $time_type, $time_value, $prof, $fid, $sp, $prev_type, $new_position, $in_shop, $is_video, $video_link);
        $error = ($DB->error ? 'Ошибка сохранения' : false);
        
        if ( $sId && !$error && !is_pro() ) {
            /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            
            $stop_words    = new stop_words();
            $nStopWordsCnt = $stop_words->calculate( $name, $descr );
            
            $DB->insert( 'moderation', array('rec_id' => $sId, 'rec_type' => user_content::MODER_PORTFOLIO, 'stop_words_cnt' => $nStopWordsCnt) );*/
        }
        
        return ($error);
    }
    
    function editWork($uid, $params) {
        global $DB;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/uploader/uploader.php");
        
        $params  = self::prepareWork($params);
        $error   = self::validateWork($params);
        $work_id = $params['id'];
        if(!empty($error)) {
            return $error;
        }
        $update = array(
            'link'       => $params['link'],
            'name'       => antispam( $params['work_name'] ),
            'descr'      => antispam( $params['work_descr'] ),
            'cost'       => $params['work_cost'],
            'cost_type'  => $params['work_cost_type_db_id'],
            'time_value' => $params['time_cost'],
            'time_type'  => $params['work_time_type_db_id'],
            'prev_type'  => $params['work_preview_type'],
            'prof_id'    => $params['work_category_db_id']
        );
        
        if($params['video'] != '') {
            $video_link = video_validate($params['video']);
            $video_link = preg_replace("/^http:\/\//","",$video_link);
            $update['video_link'] = $video_link;
            $update['is_video']   = true;
        } else {
            $update['video_link'] = '';
            $update['is_video']   = false;
        }
        
        if ( $uid == $_SESSION['uid'] && !hasPermissions('users')) {
            // автор, не админ, не про меняет заголовок либо текст - отправить на модерирование
            $update['moderator_status'] = ( !is_pro() ? '0' : '-2' );
        }
        
        if($params['position_num'] !== NULL) {
            $update['norder'] = intval($params['position_num']);
        }
        
        $dir = "users/".substr($_SESSION['login'], 0, 2)."/{$_SESSION['login']}/upload/";
        
        if (isset($params['IDResource']) && is_array($params['IDResource'])) {
        
            foreach($params['IDResource'] as $resource) {
                $resources[uploader::sgetTypeUpload($resource)] = $resource;
            }

            if (isset($resources['portfolio'])) {
                $mainFile = current( uploader::sgetFiles($resources['portfolio']) );
                if(!empty($mainFile)) { // Если что-то загрузили и не удаляли
                    $MFile = uploader::remoteCopy($mainFile['id'], 'file', $dir, false);
                    uploader::sclear($resources['portfolio']);
                    // Делаем отдельное привью из оригинальной большой картинки  -- @todo ХЗ зачем но раньше так делали 
                    if ( in_array($MFile->getext(), $GLOBALS['graf_array']) && $MFile->getext() != 'swf' ) {
                        $MFile->resizeImage( $dir . 'tn_' . $MFile->name, self::PREVIEW_MAX_WIDTH, self::PREVIEW_MAX_HEIGHT, 'auto', true );
                    }
                    $update['pict'] = $MFile->name;
                    $is_remove_main_file = true;
                }
            }
            
            if (isset($resources['pf_preview'])) {
                $preview = current( uploader::sgetFiles($resources['pf_preview']) );
                if(!empty($preview)) { // Если что-то загрузили и не удаляли
                    $PFile = uploader::remoteCopy($preview['id'], 'file', $dir, false, 'sm_f_');
                    uploader::sclear($resources['pf_preview']);
                    $update['prev_pict'] = $PFile->name;
                    $is_remove_preview_file = true;
                }
            }
        }
        
        
        if($params['main_file'] == '' && !isset($MFile)) { // Удаляем файл
            $update['pict'] = '';
            $is_remove_main_file = true;
        }
        
        if($params['preview_file'] == '' && !isset($PFile)) { // Удаляем файл
            $update['prev_pict'] = '';
            $is_remove_preview_file = true;
        }
        
        if($is_remove_main_file) {
            $cf = new CFile();
            $cf->Delete(0, $dir, $params['old_main_file']); // удаляем ранее загруженный файл
        }
        
        if($is_remove_preview_file) {
            $cf = new CFile();
            $cf->Delete(0, $dir, $params['old_preview_file']); // удаляем ранее загруженный файл
        }
        
        if(empty($error)) {
            $wmodeAllowValues = array("direct", "gpu", "window");
            if (!in_array($params["wmode"], $wmodeAllowValues) ) {
               $params["wmode"] = "window";
            }
            if ($work_id) {
                $update['edit_date'] = 'NOW()';
                $result = $DB->update('portfolio', $update, 'id = ?i AND user_id = ?i', $work_id, $uid);
            } else {
                $update['user_id'] = $uid;
                $result  = $DB->insert('portfolio', $update, 'id');
                $work_id = $result;
            } 
            if ($MFile && $MFile->getext() == 'swf' && $MFile->id) {
                $res = $DB->query("UPDATE swf_file_params SET wmode = ?, table_name = ? WHERE fid = ?i", $params["wmode"], $MFile->table, $MFile->id);
                if (pg_affected_rows($res) == 0) {
                    $res = $DB->query("INSERT INTO swf_file_params (fid, wmode, table_name) VALUES(?i, ?, ?)", $MFile->id, $params["wmode"], $MFile->table);
                }
            } else if ( !$MFile ){
                $ext = preg_replace("#.*(\.[a-zA-Z0-9]*)$#", "$1", $params["main_file"]);
                if ( strtolower($ext) == ".swf" ) {
                    $res = $DB->query("UPDATE swf_file_params SET wmode = ? WHERE fid = (SELECT id FROM file WHERE fname = ?)", $params["wmode"], $params["main_file"]);
                    if (pg_affected_rows($res) == 0) {
                        $res = $DB->query("INSERT INTO swf_file_params (fid, wmode, table_name) VALUES( ( SELECT id  FROM file WHERE fname = ?) , ?, ?)", $params["main_file"], $params["wmode"], "file");
                    }
                }
            }
        } else {
            return $error;
        }
        
        
        if ($work_id && $result && !is_pro()) {
            /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );

            $stop_words = new stop_words();
            $nStopWordsCnt = $stop_words->calculate($update['name'], $update['descr']);
            $DB->insert('moderation', array('rec_id' => $work_id, 'rec_type' => user_content::MODER_PORTFOLIO, 'stop_words_cnt' => $nStopWordsCnt));*/
        }
        
        return $result;
    }
    
    /**
     * Приводим данные к тому же что и в файле index.php
     * @see users/setup/index.php 717 строка
     * @param array $params
     */
    public function prepareWork($params) {
        foreach($params as $name=>$value) {
            switch($name) {
                case 'old_main_file':
                    $params[$name] = str_replace(array("/", "\\"), "", $value);
                    break;
                case 'position_num':
                    $params[$name] = intval($value);
                    if ( $params[$name] < 0 ) {
                        $params[$name] = 1;
                    }
                    break;
                case 'id':
                case 'work_category_db_id':
                case 'work_preview_type':
                case 'work_cost_type_db_id':
                case 'work_time_type_db_id':
                case 'time_cost':
                    $params[$name] = intval($value);
                    break;
                case 'position':
                    $params[$name] = __paramValue('string', $value);
                    break;
                case 'work_name':
                    $params[$name] = stripslashes( __paramValue('string', $value, 120) );
                    break;
                case 'work_cost':
                    $params[$name] = intval(str_replace(" ", "", $value) * 100) / 100;
                    break;
                case 'video':
                    $params[$name] = stripslashes(trim($value));
                    break;
                case 'link':
                    $params[$name] = addhttp(trim(stripslashes(__paramValue('string', $value, 150))));
                    if(!$params[$name]) $params[$name] = '';
                    break;
                case 'work_descr':
                    $value = __paramValue('html_save_ul_li_b_p_i', $value, 1500, true);
                    $params[$name] = $value;//html_entity_decode($value, ENT_QUOTES);
                    break;
            }
        }
        if (isset($params['position'])) {    
            switch ($params['position']) {
                case 'first': $params['position_num'] = 1;
                    break;
                case 'last': $params['position_num']  = 0;
                    break;
            }
        }
        return $params;
    }
    
    public function validateWork($params) {
        $portf_cost = self::$portf_cost;
        foreach ($params as $name => $value) {
            switch ($name) {
                case 'video':
                    if($value != '') {
                        if(!video_validate($value)) {
                            $error[$name] = "Поле заполнено некорректно. Введите корректную ссылку на видео.";
                        }
                    }
                    break;
                case 'work_name':
                    if ($value == '') {
                        $error[$name] = "Поле заполнено некорректно. Введите название.";
                    }
                    
                    if( strlen(trim(stripslashes($value))) > 120 ) {
                        $error[$name] = "Поле заполнено некорректно. Название должно содержать не более 120 символов.";
                    }
                    break;
                case 'link':
                    if ($value != '' && !url_validate($value, true)) {
                        $error[$name] = "Поле заполнено некорректно. Введите корректную ссылку.";
                    }
                    break;
                case 'work_cost':
                    $cost_type = $params['work_cost_type_db_id'];
                    if ($value < 0 || $value > self::$portf_cost[$cost_type]) {
                        $error[$name] = 'Поле заполнено некорректно. Стоимость должна быть в пределе от 0 ' . view_range_cost2(0, self::$portf_cost[$cost_type], '', '', false, $cost_type) . ($cost_type != 2 ? '.' : '');
                    }
                    break;
                case 'time_cost':
                    if ($value < 0 || $value > self::MAX_TIME_VALUE) {
                        $error[$name] = 'Поле заполнено некорректно. Временные затраты должны быть в пределе от 0 до ' . self::MAX_TIME_VALUE . '.';
                    }
                    break;
            }
        }
        
        return $error;
    }
	
    /**
     * генерирует статическое превью, то есть gif будет без анимации
     * имя файла сохраняется в поле static_preview
     * @param array $date данные о работе в портфолио (необходимы ключи id, prev_pict)
     * @param integer $login логин пользователя которому принадлежит работа
     */
    function GenerateStaticPreview (&$work, $login) {
        global $DB;
        
        $maxWidth = self::PREVIEW_MAX_WIDTH;
        $maxHeight = self::PREVIEW_MAX_HEIGHT;

        if ($work['static_preview']) {
            return true;
        }
        
        $isGIF = (bool)preg_match('~\.gif$~', $work['prev_pict']);
        if (!$isGIF) {
            $staticName = $work['prev_pict'];
        } else {
            $image = new cFile();
            $dir = 'users/' . substr($login, 0 ,2) . '/' . $login . '/upload/';
            $image->getInfo($dir . $work['prev_pict']);

            $imageSize = $image->image_size;

            if ($imageSize['width'] > $maxWidth || $imageSize['height'] > $maxHeight) {
                $staticName = 'st_' . $image->name;
                $image->resizeImage( $dir . $staticName, $maxWidth, $maxHeight, 'auto', true );
            } else {
                $staticName = $work['prev_pict'];
            }
        }

        $work['static_preview'] = $staticName;
        
        $sql = "UPDATE portfolio SET static_preview = ? WHERE id = ?i";
        $DB->query($sql, $staticName, $work['id']);
        
        if ($DB->error) {
            return false;
        } else {
            return true;
        }
    }
    
	/**
	 * Взять портфолио определенного пользователя (+ если необходимо по определенной специализации)
	 *
	 * @param integer $uid  ИД пользователя
	 * @param integer $prof Специализация если нужно
	 * @param boolean $all  Всю информацию или частично
	 * @return array Данные выборки
	 */
    function GetPortf($uid, $prof = "NULL", $all = false)
    {
      global $DB; 
      
      $sel_blocked = ', pb.reason as blocked_reason, pb.blocked_time, COALESCE(pb.src_id::boolean, false) as is_blocked, 
          admins.login as admin_login, admins.uname as admin_uname, admins.usurname as admin_usurname';
      $join_blocked = 'LEFT JOIN portfolio_blocked pb ON pf.id = pb.src_id 
          LEFT JOIN users as admins ON pb.admin = admins.uid ';
      
      if ($prof != "NULL" && $prof) {
          if($all) {
              $sql = "SELECT pf.id, pf.name, pf.link, pf.descr, pf.cost AS prj_cost, pf.time_type AS prj_time_type, pf.moderator_status, pf.pict, pf.prev_pict, 
                            pf.time_value AS prj_time_value, pf.prev_type AS prj_prev_type, pf.cost_type AS prj_cost_type, pf.in_shop, pf.is_video, pf.video_link,
                            pc.prof_id, p.id as prof_origin, pc.ordering, pc.show_comms as gr_comms, pc.show_preview as gr_prevs,
                            pc.cost_from, pc.cost_to, pc.time_type, pc.time_from, pc.time_to, pc.cost_hour,
                            pc.cost_1000, pc.cost_type, pc.cost_type_hour, pc.portf_text, pf.edit_date 
                            $sel_blocked 
                      FROM portfolio pf 
                      $join_blocked
                      INNER JOIN portf_choise pc ON pc.user_id = pf.user_id AND pc.prof_id = pf.prof_id
                      INNER JOIN professions p ON p.id = COALESCE(pc.prof_origin, pc.prof_id)
                      WHERE pf.user_id = {$uid}
                        AND pf.prof_id = {$prof}
                      ORDER BY pf.prof_id, pf.norder ";
          } else {
        $sql = 
        "SELECT pf.id, pf.name, pf.link, pf.descr, pf.cost AS prj_cost, pf.time_type AS prj_time_type, pf.moderator_status, pf.pict, pf.prev_pict, 
                pf.time_value AS prj_time_value, pf.prev_type AS prj_prev_type, pf.cost_type AS prj_cost_type, pf.in_shop, pf.is_video, pf.video_link 
                $sel_blocked 
          FROM portfolio pf 
          $join_blocked 
          WHERE user_id = {$uid}
            AND prof_id = {$prof}
          ORDER BY prof_id, norder ";
          }
      }
      else {

        if ($all) {
          $sql =
          "SELECT pf.id, pf.name, pf.link, pf.descr, pf.norder, pf.cost as prj_cost, pf.cost_type as prj_cost_type, pf.in_shop, pf.is_video, pf.video_link, 
                  pf.time_type as prj_time_type, pf.time_value as prj_time_value, pf.prev_type as prj_prev_type,
                  pf.show_comms, pf.show_preview as preview, pf.pict, pf.prev_pict, 
                  pg.name as mainprofname, pg.id AS prof_group_id, pf.moderator_status,
                  p.name as profname, p.is_text as proftext, p.link AS proflink,
                  pc.prof_id, p.id as prof_origin, pc.ordering, pc.show_comms as gr_comms, pc.show_preview as gr_prevs,
                  pc.cost_from, pc.cost_to, pc.time_type, pc.time_from, pc.time_to, pc.cost_hour,
                  pc.cost_1000, pc.cost_type, pc.cost_type_hour, pc.portf_text, m.on_moder, pf.user_id, pf.edit_date   
                  $sel_blocked 
             FROM portf_choise pc
           INNER JOIN
             professions p
               ON p.id = COALESCE(pc.prof_origin, pc.prof_id)
           INNER JOIN
             prof_group pg
               ON pg.id = p.prof_group
           LEFT JOIN
             portfolio pf 
               ON pf.user_id = pc.user_id
              AND pf.prof_id = pc.prof_id
           $join_blocked 
           LEFT JOIN (
            SELECT user_id, prof_id, COUNT(id) AS on_moder FROM portf_choise_change WHERE ucolumn = 'text' AND (moderator_status = 0 OR moderator_status = -1) GROUP BY user_id, prof_id 
           ) AS m ON m.user_id = pc.user_id AND m.prof_id = pc.prof_id 
            WHERE pc.user_id = {$uid}
            ORDER BY pc.ordering, pc.prof_id, pf.norder , pf.id";
        }
        else {
          $sql =
          "SELECT pf.id, pf.name, pf.link, pf.descr, pf.norder, pf.cost as prj_cost, pf.time_type as prj_time_type, pf.in_shop, pf.is_video, pf.video_link, 
                  pf.time_value as prj_time_value, pf.prev_type as prj_prev_type, pf.cost_type as prj_cost_type, pf.show_comms,
                  pf.show_preview as preview, pf.pict, pf.prev_pict, pf.moderator_status,
                  p.name as profname, p.is_text as proftext, pc.prof_id, p.link AS proflink, m.on_moder, pf.edit_date  
                  $sel_blocked 
             FROM portf_choise pc
           INNER JOIN
             professions p
               ON p.id = pc.prof_id
           LEFT JOIN
             portfolio pf
               ON pf.user_id = pc.user_id
              AND pf.prof_id = pc.prof_id
           $join_blocked 
           LEFT JOIN (
            SELECT user_id, prof_id, COUNT(id) AS on_moder FROM portf_choise_change WHERE ucolumn = 'text' AND (moderator_status = 0 OR moderator_status = -1) GROUP BY user_id, prof_id 
           ) AS m ON m.user_id = pc.user_id AND m.prof_id = pc.prof_id 
            WHERE pc.user_id = {$uid}
            ORDER BY pc.ordering, pc.prof_id, pf.norder , pf.id";
        }
      }
      return $DB->rows($sql);
    }
    
    /**
     * Возвращает работу из портфолио по ее ID
     * 
     * @param  int $portf_id ID работы
     * @return array
     */
    function GetPortfById( $portf_id = 0 ) {
        return $GLOBALS['DB']->row( "SELECT pf.id, pf.user_id, pf.name, pf.link, pf.descr, pf.norder, pf.cost AS prj_cost, 
                pf.cost_type AS prj_cost_type, pf.in_shop, pf.is_video, pf.video_link, 
                pf.time_type AS prj_time_type, pf.time_value AS prj_time_value, pf.prev_type AS prj_prev_type,
                pf.show_comms, pf.show_preview AS preview, pf.pict, pf.prev_pict, 
                pg.name AS mainprofname, pf.moderator_status,
                p.name AS profname, p.is_text AS proftext, p.link AS proflink,
                pc.prof_id, p.id AS prof_origin, pc.ordering, pc.show_comms AS gr_comms, pc.show_preview AS gr_prevs,
                pc.cost_from, pc.cost_to, pc.time_type, pc.time_from, pc.time_to, pc.cost_hour,
                pc.cost_1000, pc.cost_type, pc.cost_type_hour, pc.portf_text, 
                pb.reason AS blocked_reason, pb.blocked_time, COALESCE(pb.src_id::boolean, false) AS is_blocked, 
                admins.login AS admin_login, admins.uname AS admin_uname, admins.usurname as admin_usurname,
                swf.wmode
            FROM portfolio pf
            INNER JOIN portf_choise pc ON pf.user_id = pc.user_id AND pf.prof_id = pc.prof_id 
            INNER JOIN professions p ON p.id = COALESCE(pc.prof_origin, pc.prof_id) 
            INNER JOIN prof_group pg ON pg.id = p.prof_group 
            LEFT JOIN portfolio_blocked pb ON pf.id = pb.src_id 
            LEFT JOIN file ON file.fname = pf.pict 
            LEFT JOIN swf_file_params AS swf ON swf.fid = file.id 
            LEFT JOIN users AS admins ON pb.admin = admins.uid 
            WHERE pf.id = ?i", $portf_id );
    }
    
    /**
	 * Получение списка работ фрилансера с подгруженными файлами для конкретной профессии.
	 *
	 * @param integer $fid id фрилансера
	 * @param integer $prof id  профессии
	 * @param boolean $onlyWithPreview выбирать работы только с превью
	 * @param boolean $skip_blocked не брать заблокированные
	 * @return array массив работ данного юзера в данном разделе
	 */
    function GetPortfProf( $fid, $prof, $onlyWithPreview = false, $skip_blocked = true )
    {
        global $DB; 
        $fileFilter = $onlyWithPreview ? "p.prev_pict<>''" : "(p.pict<>'' OR p.prev_pict<>'')";
        $join_blocked  = $skip_blocked ? ' LEFT JOIN portfolio_blocked pb ON p.id = pb.src_id ' : '';
        $where_blocked = $skip_blocked ? ' AND pb.src_id IS NULL ' : '';

    	$prof = professions::GetProfessionOrigin($prof);
        $sql = "SELECT p.id, p.name, p.link, p.descr, p.pict, p.prev_pict, p.user_id, p.static_preview 
            FROM portfolio p 
            $join_blocked
            WHERE (p.user_id=?i AND p.prof_id=?i AND $fileFilter $where_blocked) ORDER BY p.norder";
        return $DB->rows($sql, $fid, $prof);
    }
    
	/**
	 * Получение списка работ фрилансера с подгруженными файлами для конкретной специализации
	 *
	 * @param integer $prof_id ИД професии
	 * @param integer $count   Вовзвращает количество 
	 * @param integer $size    Размер выборки
	 * @param integer $frl_pp  Количество портфолио на страницу @see PRF_PP (classes/globals.php) 
	 * @param integer $offset  Позиция выборки
	 * @param string  $order   Сортировка выборки
	 * @param integer $direction Тип сортировки (DESC, ASC)
	 * @param integer $favorite  Избранное (вкл, откл) 
	 * @param boolean $filter_apply Применить фильмтр или нет
	 * @param array   $filter    Фильтр  
	 * @return array Данные по выборке
	 */
    function GetSpecPortf($prof_id = 0, &$count, &$size, $frl_pp = PRF_PP, $offset = 0, $order = "random", $direction = 0, $favorite = 0, $filter_apply = false, $filter = null)
    {
    global $DB; 
	// START rates for convert

	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");

	$project_exRates = project_exrates::GetAll();

	if ($filter['cost_type'] == 1)		//euro
	{
		$rates = array(0 => $project_exRates[32], 1 => $project_exRates[33], 2 => $project_exRates[34], 3 => $project_exRates[31]);
	}
	elseif ($filter['cost_type'] == 2)	//rur
	{
		$rates = array(0 => $project_exRates[42], 1 => $project_exRates[43], 2 => $project_exRates[44], 3 => $project_exRates[41]);
	}
	elseif ($filter['cost_type'] == 3)	//FM
	{
		$rates = array(0 => $project_exRates[12], 1 => $project_exRates[13], 2 => $project_exRates[14], 3 => $project_exRates[11]);
	}
	else					//usd
	{
		$rates = array(0 => $project_exRates[22], 1 => $project_exRates[23], 2 => $project_exRates[24], 3 => $project_exRates[21]);
	}

	$rates_sql = "	CASE WHEN p.cost_type=0
			THEN p.cost / ".$rates[0]."
			WHEN p.cost_type=1
			THEN p.cost / ".$rates[1]."
			WHEN p.cost_type=2
			THEN p.cost / ".$rates[2]."
			WHEN p.cost_type=3
			THEN p.cost / ".$rates[3]."
			END AS convert_cost";

	//print_r($filter);
	//print_r($rates);
	//echo $rates_sql;

	// END rates for convert

        $ret = array();
        $uid = get_uid(false);
        $dir_sql = ($direction == 0) ? 'DESC' : 'ASC';
        /**
		 * Сортировка
		 */
        switch ($order)
        {
            default:
            case "rating":
                $order = "rating DESC, norder";
                break;
            case "random":
                $order = "random()";
                break;
            case "costs":
                $order = "((cost = 0) OR (cost IS NULL)) ASC, convert_cost " . $dir_sql;
                break;
            case "opinions":
                $order = "ssum DESC, uid, norder";
                $user_counters_join = 'LEFT JOIN users_counters uc ON uc.user_id = fu.uid';
                $ssum = ", zin(uc.ops_emp_plus) - zin(uc.ops_emp_minus) as ssum";
                break;
        }
        /**
		 * Фильтр.
		 */
        if ($filter_apply)
        {
            $filter_sql = '';
            if (($filter['cost_from'] > 0) || ($filter['cost_to'] > 0))
            {
                $cost_field = 'convert_cost';

                if (($filter['cost_from'] > 0) && ($filter['cost_to'] > 0))
                {
                    $filter_sql .= (($filter_sql != '') ? ' AND ' : '') . '(' . $cost_field . '>=' . $filter['cost_from'] . ' AND ' . $cost_field . '<=' . $filter['cost_to'] . ')';
                }
                else
                {
                    if ($filter['cost_from'] > 0)
                    {
                        $filter_sql .= (($filter_sql != '') ? ' AND ' : '') . '(' . $cost_field . '>=' . $filter['cost_from'] . ')';
                    }
                    elseif ($filter['cost_to'] > 0)
                    {
                        $filter_sql .= (($filter_sql != '') ? ' AND ' : '') . '(' . $cost_field . '<=' . $filter['cost_to'] . ')';
                    }
                }
            }
            if ($filter_sql != '')
            {
                $filterw_sql = ' WHERE' . $filter_sql;
            }
        }
        else
        {
            $filtera_sql = $filterw_sql = "";
        }

        if(!$filter_sql && $order_orig == 'costs')
            $filter_sql = $cost_field . ' > 0 ';

        $size = 0;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        $or_prof = professions::GetProfessionOrigin($prof_id);
        $fav_sql = ($favorite == 0) ? '' : ' INNER JOIN portfolio_fav AS pf ON p.id=pf.prf_id AND pf.user_id=' . $uid; // . ' AND p.prof_id=' . $prof_id;
        $sql = 
        "SELECT * FROM (SELECT p.id, p.name, p.descr, p.norder, p.pict, p.prev_pict, p.show_preview, p.cost, p.time_type, p.time_value,
                p.prev_type, p.cost_type, p.is_video, fu.uid, fu.login, fu.uname, fu.usurname, rating_get(fu.rating, fu.is_pro, fu.is_verify, fu.is_profi) as rating, $rates_sql
                $ssum
           FROM fu
         {$user_counters_join}  
         INNER JOIN
           portfolio p
             ON p.user_id = fu.uid
            AND p.prof_id = ".professions::BEST_PROF_ID."
            AND p.first3 = true
         $fav_sql
          WHERE fu.spec_orig = $or_prof
            AND fu.is_pro = true
            AND fu.is_banned = '0') as u" . (($filter_sql)?" WHERE $filter_sql":"").
            " ORDER BY $order
          LIMIT ?i OFFSET ?i";
        $ret = $DB->rows($sql, $frl_pp, $offset);


        if ($DB->error)
          $error = $DB->error;
        else
        {
          $size = count($ret);
          $sql = 
          "SELECT COUNT(*) FROM (SELECT id, $rates_sql
             FROM fu
           INNER JOIN
             portfolio p
               ON p.user_id = fu.uid
              AND p.prof_id = ".professions::BEST_PROF_ID."
              AND p.first3 = true
           $fav_sql
            WHERE fu.spec_orig = $or_prof
              AND fu.is_pro = true
              AND fu.is_banned = '0') as u" . (($filter_sql)?" WHERE $filter_sql":"");
          $count = $DB->val($sql);
        }

        return $ret;
    }
    
	/**
	 * Выделяем главную специализацию по его портфолио
	 *
	 * @param integer $count        Вовзрашает Количество
	 * @param integer $size         Вовзращает Размер выборки
	 * @param integer $frl_pp       Количество портфолио на страницу
	 * @param integer $offset       С какой позиции брать из БД
	 * @param string  $order        Сортировка
	 * @param integer $direction    Тип сортировки (DESC, ASC)
	 * @param integer $favorite     Избранное (1, 0)
	 * @param boolean $filter_apply Фильтр включен или нет
	 * @param integer $filter       Фильтр
	 * @return array Данные по выборке
	 */
    function GetSpecPortfMain(&$count, &$size, $frl_pp = PRF_PP, $offset = 0, $order = "rating", $direction = 0, $favorite = 0, $filter_apply = false, $filter = null)
    {
    global $DB; 
	// START rates for convert

	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");

	$project_exRates = project_exrates::GetAll();

	if ($filter['cost_type'] == 1)		//euro
	{
		$rates = array(0 => $project_exRates[32], 1 => $project_exRates[33], 2 => $project_exRates[34], 3 => $project_exRates[31]);
	}
	elseif ($filter['cost_type'] == 2)	//rur
	{
		$rates = array(0 => $project_exRates[42], 1 => $project_exRates[43], 2 => $project_exRates[44], 3 => $project_exRates[41]);
	}
	elseif ($filter['cost_type'] == 3)	//FM
	{
		$rates = array(0 => $project_exRates[12], 1 => $project_exRates[13], 2 => $project_exRates[14], 3 => $project_exRates[11]);
	}
	else					//usd
	{
		$rates = array(0 => $project_exRates[22], 1 => $project_exRates[23], 2 => $project_exRates[24], 3 => $project_exRates[21]);
	}

	$rates_sql = "	CASE WHEN p.cost_type=0
			THEN p.cost / ".$rates[0]."
			WHEN p.cost_type=1
			THEN p.cost / ".$rates[1]."
			WHEN p.cost_type=2
			THEN p.cost / ".$rates[2]."
			WHEN p.cost_type=3
			THEN p.cost / ".$rates[3]."
			END AS convert_cost";

	//print_r($filter);
	//print_r($rates);
	//echo $rates_sql;

	// END rates for convert

        $ret = array();
        $uid = get_uid(false);
        /**
		 * Сортировка
		 */
        $order_orig =  $order;
        switch ($order)
        {
            default:
            case "rating":
                $order = "rating DESC, uid, norder";
                break;
            case "random":
                $order = "random()";
                break;
            case "costs":
                $order = "((cost = 0) OR (cost IS NULL)) ASC, convert_cost " . $dir_sql;
                break;
            case "opinions":
                $order = "ssum DESC, uid, norder";
                $user_counters_join = 'LEFT JOIN users_counters uc ON uc.user_id = fu.uid';
                $ssum = ", zin(uc.ops_emp_plus) - zin(uc.ops_emp_minus) as ssum";
                break;
        }
        /**
		 * Фильтр.
		 */
        $cost_field = 'convert_cost';
        if ($filter_apply)
        {
            $filter_sql = '';
            if (($filter['cost_from'] > 0) || ($filter['cost_to'] > 0))
            {
                if (($filter['cost_from'] > 0) && ($filter['cost_to'] > 0))
                {
                    $filter_sql .= (($filter_sql != '') ? ' AND ' : '') . '(' . $cost_field . '>=' . $filter['cost_from'] . ' AND ' . $cost_field . '<=' . $filter['cost_to'] . ')';
                }
                else
                {
                    if ($filter['cost_from'] > 0)
                    {
                        $filter_sql .= (($filter_sql != '') ? ' AND ' : '') . '(' . $cost_field . '>=' . $filter['cost_from'] . ')';
                    }
                    elseif ($filter['cost_to'] > 0)
                    {
                        $filter_sql .= (($filter_sql != '') ? ' AND ' : '') . '(' . $cost_field . '<=' . $filter['cost_to'] . ')';
                    }
                }
            }

            if ($filter_sql != '')
            {
//                $filter_sql = ' WHERE' . $filter_sql;
                $filter_sql = $filter_sql;
            }
        }
        else
        {
            $filter_sql = "";
        }

        if(!$filter_sql && $order_orig == 'costs')
            $filter_sql = $cost_field . ' > 0 ';

        $size = 0;
        $dir_sql = ($direction == 0) ? 'DESC' : 'ASC';
        $fav_sql = ($favorite == 0) ? '' : ' INNER JOIN portfolio_fav AS pf ON p.id=pf.prf_id AND pf.user_id=' . $uid;
        $sql =
        "SELECT * FROM (SELECT p.id, p.name, p.descr, p.norder, p.pict, p.prev_pict, p.show_preview, p.cost, p.time_type, p.time_value, p.prev_type, p.cost_type, p.is_video,
                               fu.uid, fu.login, fu.uname, fu.usurname, rating_get(fu.rating, fu.is_pro, fu.is_verify, fu.is_profi) as rating, $rates_sql
                               $ssum
           FROM fu
         {$user_counters_join}  
         INNER JOIN
           portfolio p
             ON p.user_id = fu.uid
            AND p.prof_id = ".professions::BEST_PROF_ID."
            AND p.first3 = true
         $fav_sql
          WHERE fu.is_pro = true
            AND fu.is_banned = '0') as t" . (($filter_sql)?" WHERE $filter_sql":"").
            " ORDER BY $order
          LIMIT ?i OFFSET ?i";

        $ret = $DB->rows($sql, $frl_pp, $offset);

        if ($DB->error)
            $error = $DB->error;
        else
        {
            $size = count($res);
            $sql =
            "SELECT COUNT(*) FROM (SELECT  id, $rates_sql
               FROM fu
             INNER JOIN
               portfolio p
                 ON p.user_id = fu.uid
                AND p.prof_id = ".professions::BEST_PROF_ID."
                AND p.first3 = true
             $fav_sql
              WHERE fu.is_pro = true
                AND fu.is_banned = '0') as t" . (($filter_sql)?" WHERE $filter_sql":"");
            $count = $DB->val($sql);
        }

        return $ret;
    }
    
	/**
	 * Проекты рядом (выдача проектов рядом, справа от данного проекта или слева)
	 * возвращает следующую или предыдущую работу, согласно порядку, определенному в портфолио фрилансера
	 * 
	 * @param integer $uid ид. юзера, чьи работы смотрим.
	 * @param integer $prjid Данный проект
	 * @param int  $sign  В какую сторону рядом (1:вправо, -1:влево)
     * @param boolean $skip_blocked не брать заблокированные
	 * @return array
	 */
    function GetPrjNear( $uid, $prjid, $sign = 1, $skip_blocked = true )
    {
        global $DB; 
        
        $join_blocked  = $skip_blocked ? ' LEFT JOIN portfolio_blocked pb ON p.id = pb.src_id ' : '';
        $where_blocked = $skip_blocked ? ' AND pb.src_id IS NULL ' : '';
            
        $sql = "
          SELECT p.id
            FROM portf_choise pc
          INNER JOIN
            portfolio p
              ON p.user_id = pc.user_id
             AND p.prof_id = pc.prof_id
           $join_blocked
           WHERE pc.user_id = ?i $where_blocked 
             AND (pc.prof_id NOT IN (?i, ?i)
                  OR EXISTS (SELECT 1 FROM freelancer WHERE uid = pc.user_id AND is_pro = true))
           ORDER BY pc.ordering, pc.prof_id, p.norder
        ";
        
        // тут обязательно кэш.
        $prjs = $DB->cache(420)->col($sql, $uid, professions::BEST_PROF_ID, professions::CLIENTS_PROF_ID);
        $prjkey = array_search($prjid, $prjs);
        if($prjkey !== false) {
            if( !($res = $prjs[$prjkey + $sign]) ) {
                $res = $prjs[$sign < 0 ? count($prjs) - 1 : 0];
            } 
        }
        
        return $res;
    }
    
	/**
	 * Возвращает работу портфолио по ее ид
	 *
	 * @param integer $prjid ИД проекта
         * @param boolean $uid учитывать ли связь пользователя с данным образцом
	 * @return array
	 */
    function GetPrj($prjid, $uid = false){
        global $DB; 
        $sel_blocked = ', pb.reason as blocked_reason, pb.blocked_time, COALESCE(pb.src_id::boolean, false) as is_blocked, 
            admins.login as admin_login, admins.uname as admin_uname, admins.usurname as admin_usurname';
        $join_blocked = 'LEFT JOIN portfolio_blocked pb ON p.id = pb.src_id 
            LEFT JOIN users as admins ON pb.admin = admins.uid ';
        
        $sql = 
        "SELECT f.portf_comments, p.id, p.name, p.user_id, p.link, p.descr, p.pict, p.prev_pict, p.cost, p.time_type, p.time_value, p.prev_type, p.cost_type, p.prof_id, p.is_video, p.video_link, 
                f.spec, f.login, f.uname, f.usurname, f.photo, f.last_time, f.spec as user_spec, 
                (SELECT COUNT(*) FROM blogs_portf WHERE item_id = p.id) as comms,
                to_char(p.post_date, 'DD.MM.YY в HH24:MI') as post_date, p.moderator_status, 
                CASE WHEN 
                p.edit_date IS NULL
                THEN ''
                ELSE                
                to_char(p.edit_date, 'DD.MM.YY в HH24:MI')
                END as edit_date,
                COALESCE(swf.wmode, 'window') as wmode
                $sel_blocked
                 
           FROM portfolio p
           LEFT JOIN file ON p.pict = file.fname
           LEFT JOIN swf_file_params swf ON swf.fid = file.id
         INNER JOIN
           freelancer f
             ON f.uid = p.user_id
             ".($uid ? ' AND f.uid = '.(int)$uid : '')."
          $join_blocked 
          WHERE p.id = ?i";
        return $DB->row($sql, $prjid);
    }
    
    /**
     * Блокирует работу портфолио
     *
     * @param  integer $portfolio_id ID работы в портфолио
     * @param  string $reason причина
     * @param  string $reason_id id причины, если она выбрана из списка
     * @param  integer $uid uid администратора (если 0, используется $_SESSION['uid'])
     * @param  boolean $from_stream true - блокирование из потока, false - на сайте
     * @return integer ID блокировки
     */
    function Blocked( $portfolio_id = 0, $reason, $reason_id = null, $uid = 0, $from_stream = false ) {      
        if (!$uid && !($uid = $_SESSION['uid'])) return 'Недостаточно прав';
        $sql = "INSERT INTO portfolio_blocked (src_id, \"admin\", reason, reason_id, blocked_time) VALUES(?i, ?i, ?, ?, NOW()) RETURNING id";
        $sId = $GLOBALS['DB']->val( $sql, $portfolio_id, $uid, $reason, $reason_id );        
        $sql = "UPDATE portfolio SET moderator_status = ?i WHERE id = ?i";
        $sId = $GLOBALS['DB']->val( $sql, $uid, $portfolio_id);
        
        //---------------------------------------
        
        if(!$from_stream) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );

            messages::SendBlockedPortfolio( $portfolio_id, $reason );

            $GLOBALS['DB']->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $portfolio_id, user_content::MODER_PORTFOLIO );
            $GLOBALS['DB']->val( 'UPDATE portfolio SET moderator_status = ?i WHERE id = ?i', $uid, $portfolio_id );
        }
        
        return $sId;
    }
        
    /**
     * Разблокирует работу портфолио
     *
     * @param integer $portfolio_id ID работы в портфолио
     * @return string Сообщение об ошибке
     */
    function UnBlocked( $portfolio_id ) {
        $GLOBALS['DB']->query( 'DELETE FROM portfolio_blocked WHERE src_id = ?i', $portfolio_id );
        return $GLOBALS['DB']->error;
    }
    
	/**
	 * Редактирование работы в портфолио 
	 *
	 * @see self::addPortf();
	 * 
	 * @param integer $fid     		 ИД 
	 * @param string  $name    		 Название работы
	 * @param string  $pict    		 Основное изображение
	 * @param string  $sm_pict 		 Уменьшенное изображение
	 * @param string  $link    		 Ссылка на работу
	 * @param string  $descr   		 Описание работы
	 * @param integer $prof    		 Специализация работы
	 * @param integer $cost    		 Стоимость работы
	 * @param integer $cost_type 	 Тип стоимости
	 * @param integer $time_type 	 Тип времени работы
	 * @param integer $time_value 	 Время рабты
	 * @param integer $prev_type  	 Тип превью
	 * @param mixed   $file_error    Ошибка файла если есть 
	 * @param mixed   $preview_error Превью ошибка
	 * @param integer $new_position  Новая позиция
	 * @param integer $in_shop       Работа в магазине  
	 * @param integer $is_video      Есть ли видео 
	 * @param string  $video_link    Ссылка на видео
     * @param boolean $upd_prev      Нужно ли обновить превью из основного изображения
     * @param integer $moduser_id    UID изменяющего пользователя (админа). если null - то берется $fid
     * @param string  $pict_filename Основное изображение для новой загрузки файлов
     * @param string  $prev_pict_filename Уменьшенное изображение для новой загрузки файлов
     * @param string  $login логин пользователя на случай если редактирует админ
     * @param string  $modified_reason причина редактирования
	 * @return string Сообщение об ошибке
	 */
    function EditPortf($fid, $name, $pict, $sm_pict, $link, $descr, $prof, $cost,
                       $cost_type, $time_type, $time_value, $prev_type,
                       $prj_id, &$file_error, &$preview_error,
                       $new_position, $in_shop=0, $video_link='', $upd_prev=false, 
            $moduser_id = null, $pict_filename = '', $prev_pict_filename = '', $login = '', $modified_reason = '' )
    {
        global $DB; 
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        $prfs = new professions();
        $profs = $prfs->GetAllProfessionsPortf($fid);
        foreach($profs as $pf) if($pf['checked']) $check_prof[] = $pf['id'];
        $check_prof[] = professions::CLIENTS_PROF_ID;
        $check_prof[] = professions::BEST_PROF_ID;
        if(!in_array($prof, $check_prof)) return false;
        
        $old_pict = $this->GetField($prj_id, "pict");
        $old_prev_pict = $this->GetField($prj_id, "prev_pict");
        $sp = $this->GetField($prj_id, "show_preview");
        $dir = $login ? $login : get_login($fid);
        $l_dir = substr($dir, 0 ,2)."/".$dir;
        $mp = false;
        $filename = '';
        $fullDir = "users/".$l_dir."/upload/";
        
        $moduser_id = $moduser_id ? $moduser_id : $fid;
        
        // если превью не загружено и требуется обновить превью на основе основного изображения
        if (!$sm_pict->size > 0 && $upd_prev) {
            $sm_pict = new CFile();
            $sm_pict->GetInfo($fullDir.$old_pict); // оригинальное большое изображение
            if ($sm_pict->size > 0) {
                $sm_pict->table = "file_template";
                $sm_pict->quality = 100;
                if ( $sm_pict->resizeImage($fullDir."sm_".$sm_pict->name, 200, 200, 'auto', true) ) { // уменьшаем
                    $filename = "sm_".$sm_pict->name;
                    $static_preview = $filename;
                    $mp = true;
                }
            }
        }
        /**
         * Отдельно загруженное превью
         */
        if ( ($sm_pict->size > 0 || $prev_pict_filename) && !$upd_prev)
        {
            $preview_error = '';
            
            if ( $sm_pict->size > 0 ) {
                $sm_pict->max_image_size = array('width'=>200,'height'=>200, 'less' => 0);
                $sm_pict->resize = 1;
                $sm_pict->proportional = 1;
                $sm_pict->prefix_file_name = "sm_";
                $sm_pict->max_size = 102400;
                $filename = $sm_pict->MoveUploadedFile($dir."/upload");
                $preview_error = $sm_pict->StrError();
                
                // генерируем неанимированное gif превью для ответов в проектах (только для gif файлов)
                // файл будет такой: st_sm_f_имя файла.gif
                if ($sm_pict->image_size['type'] == 1) {
                    $static_preview = 'st_' . $sm_pict->name;
                    $sm_pict->resizeImage( $sm_pict->path . $static_preview, 200, 200, 'auto', true );
                } else {
                    $static_preview = $sm_pict->name;
                }
            }
            else {
                $filename = $prev_pict_filename;
                $static_preview = $prev_pict_filename;
            }
            
            if ($preview_error) {
                return $preview_error;
            } elseif ($old_prev_pict) {
                $cfile = new CFile();
                $cfile->Delete(0,"users/{$l_dir}/upload/",$old_prev_pict); // удаляем ранее загруженное превью
                $mp = true;
            } else {
                $mp = true;
            }
            $sql = "SELECT show_preview FROM portf_choise WHERE user_id=?i AND prof_id=?i";
            $res = $DB->row($sql, $fid, $prof);
            $show_preview = $res['show_preview'];
            if ($show_preview == "t")
            {
                $sp = 't';
            }
        }
        /*else
            $filename = $old_prev_pict;*/

        if ( $pict->size > 0 || $pict_filename )
        {
            $sql = "SELECT prev_pict FROM portfolio WHERE id=?i AND user_id=?i";
            $res = $DB->row($sql, $prj_id, $fid);
            
            if ( $pict->size > 0 ) {
                $pict->max_size = 10485760;
                $pictname = $pict->MoveUploadedFile($dir."/upload");
                if (!isNulArray($pict->error))
                {
                    $file_error = true;
                    return $pict->StrError;
                }
            }
            else {
                $pict = new CFile( $fullDir . $pict_filename );
                $pictname = $pict_filename;
            }
            
            /*if (is_array($pictname))
                list($pictname, $filename) = $pictname;*/

            if($upd_prev && !in_array($pict->getext(), $GLOBALS['graf_array'])) {
                $filename = null;
                $static_preview = null;
                $need_delete_preview = true;
            }
            
            if ((isNulArray($pict->error) && in_array($pict->getext(), $GLOBALS['graf_array']) && !$filename && $res['prev_pict'] == '') || ($upd_prev && in_array($pict->getext(), $GLOBALS['graf_array'])))
            {
                //print "2";exit;
               /**
                * Делаем превью.
                */
                $pict->proportional = 1;
                //$pict->imgtosmall()
                /*
                if (!$pict->img_to_small("sm_".$pictname,array('width'=>200,'height'=>200)))
                {
                    $mp = false;
                }
                else
                {
                    $mp = true;
                    $filename = "sm_".$pictname;
                }
                 */
                $pict->table = "file_template";
                $pict->quality = 100;
                if ( $pict->resizeImage($fullDir."sm_".$pict->name, 200, 200, 'auto', true) ) { // уменьшаем
                    $filename = "sm_".$pict->name;
                    $static_preview = $filename;
                    $mp = true;
                }
                
                $sql = "SELECT show_preview FROM portf_choise WHERE user_id=?i AND prof_id=?i";
                $res = $DB->row($sql, $fid, $prof);
                $show_preview = $res['show_preview'];
                if ($show_preview == "t")
                {
                    $sp = 't';
                    if (!$mp)
                    {
                        $file_error .= "Невозможно уменьшить картинку.";
                        $sp = 'f';
                    }
                }
            }
            if (isNulArray($pict->error) && $res['prev_pict'] == '') {
                if (!$cfile) { 
                    $cfile = new CFile();
                    if ($old_prev_pict && $sm_pict->name) {
                        $cfile->Delete(0,"users/{$l_dir}/upload/",$old_prev_pict); // удаляем ранее загруженное превью
                        $need_delete_preview = true;
                    }
                }
                if ($old_pict) $cfile->Delete(0,"users/{$l_dir}/upload/",$old_pict); // удаляем ранее загруженное превью
            }
        } else
          $pictname = $old_pict;
        
        // для картинок: если превью загружено отдельно - делаем отдельное привью из оригинальной большой картинки
        $sPreview = $filename ? $filename : $old_prev_pict;
        
        if ( $pictname != substr($sPreview, 3, strlen($sPreview)) ) {
            $sm_pict = new CFile();
            
            if ( ($pict->size > 0 || $pict_filename) && $old_pict ) {
                $sm_pict->Delete( 0, "users/{$l_dir}/upload/", 'tn_' . $old_pict );
            }
            
            $sm_pict->GetInfo( $fullDir . $pictname );
            
            if ( $sm_pict->size > 0 ) {
                $ext = $sm_pict->getext();
                
                if ( in_array($ext, $GLOBALS['graf_array']) && $ext != 'swf' ) {
                    $sm_pict->table   = 'file_template';
                    $sm_pict->quality = 100;
                    $sm_pict->resizeImage( $fullDir . 'tn_' . $sm_pict->name, 200, 200, 'auto', true );
                }
            }
        }
        else {
            $sm_pict = new CFile();
            $sm_pict->Delete( 0, "users/{$l_dir}/upload/", 'tn_' . $pictname );
        }

        if($in_shop==1) { $in_shop = 't'; } else { $in_shop = 'f'; }
        $prof = professions::GetProfessionOrigin($prof);
        
        $sql = '';
        
        if ( $fid == $_SESSION['uid'] && !hasPermissions('users') ) {
            // автор, не админ, не про меняет заголовок либо текст - отправить на модерирование
            $sModer = ' , moderator_status = ' . ( !is_pro() ? '0' : '-2' ). ' ';
            if ( !is_pro() ) {
                /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                
                $stop_words    = new stop_words();
                $nStopWordsCnt = $stop_words->calculate( $name, $descr );
                
                $DB->insert( 'moderation', array('rec_id' => $prj_id, 'rec_type' => user_content::MODER_PORTFOLIO, 'stop_words_cnt' => $nStopWordsCnt) );*/
            }
        }
        
        if ($mp)
        {
            $sql .= "UPDATE portfolio SET name='$name', link='$link', descr='$descr', cost='$cost', cost_type='$cost_type', time_type='$time_type', time_value='$time_value', prev_type='$prev_type', pict='$pictname', prev_pict='$filename', static_preview='$static_preview', prof_id='$prof'".($new_position!==NULL ? ", norder='$new_position'" : '').", edit_date = NOW(), edit_id = $moduser_id, modified_reason = '$modified_reason' $sModer WHERE (id='$prj_id' AND user_id='$fid') RETURNING norder";
        }
        else
        {
            $sql .= "UPDATE portfolio SET show_preview = '$sp', name='$name', link='$link', descr='$descr', cost='$cost', cost_type='$cost_type', time_type='$time_type', time_value='$time_value', prev_type='$prev_type', pict='$pictname', ".($need_delete_preview ? "prev_pict='',static_preview=''," : "")." prof_id='$prof'".($new_position!==NULL ? ", norder='$new_position'" : '').", in_shop='$in_shop', video_link='$video_link', edit_date = NOW(), edit_id = $moduser_id, modified_reason = '$modified_reason' $sModer WHERE (id='$prj_id' AND user_id='$fid') RETURNING norder";
        }
        $DB->squery($sql);

        return $error;
    }

    /**
     * Удаляет картинку или превьюху работы.
     *
     * @param string  $login  Логин пользователя
     * @param integer $prj_id ИД превью
     * @param integer $pict_type Тип картинки (1-картинка, 0-портфолио) 
     * @return integer 1 - если все ок, иначе 0
     */
    function DelPict($login, $prj_id, $pict_type = 1)
    {
        global $DB; 
      if($pict_type==1)
        $pict = $this->GetField($prj_id, "pict");
      else
        $pict = $this->GetField($prj_id, "prev_pict");
      if($pict) {
        $sql = "UPDATE portfolio SET ".($pict_type==1 ? 'pict' : 'prev_pict')." = NULL WHERE id = ?i";
        if($DB->query($sql, $prj_id)) {
          $dir = substr($login, 0 ,2)."/".$login;
          $cfile = new CFile();
		  $cfile->Delete(0, "users/{$dir}/upload/", $pict);
          
          if ( $pict_type != 1 ) {
            $sm_pict = new CFile();
            $pict = $this->GetField( $prj_id, 'pict' );
            $sm_pict->GetInfo( "users/{$dir}/upload/" . $pict );
            
            if ( $sm_pict->size > 0 ) {
                $ext = $sm_pict->getext();
                
                if ( in_array($ext, $GLOBALS['graf_array']) && $ext != 'swf' ) {
                    $sm_pict->table   = 'file_template';
                    $sm_pict->quality = 100;
                    $sm_pict->resizeImage( "users/{$dir}/upload/" . 'tn_' . $sm_pict->name, 200, 200, 'auto', true );
                }
            }
        }
        
          return 1;
        }
      }
      return 0;
    }

	/**
	 * Берем определенной поле из портфолио по его ИД
	 *
	 * @param integer $id         ИД портфолио
	 * @param string  $fieldname  Имя поля
	 * @return string Значение поля
	 */
    function GetField($id,$fieldname){
        global $DB; 
        $sql = "SELECT $fieldname FROM portfolio WHERE (id= ?i )";
        $ret = $DB->val($sql, $id);

        $error = $DB->error;
        if ($error) {
            $error = parse_db_error($error);
        }
        return ($ret);
    }

    /**
	 * Удаляет работу из портфолио
	 *
	 * @param integer $fid			UID юзера, который удаляет работу
	 * @param integer $prj_id		id проекта
	 * @param integer $force		1 - удаление админом (забивает на UID), 0 - обычный юзер
	 * @return string				сообщение об ошибке
	 */
    function DelPortf($fid, $prj_id, $force = 0){
        global $DB; 
        $dir = get_login($fid);
        $l_dir = substr($dir, 0 ,2)."/".$dir;
        if ($force == 0) $addit = "AND user_id='$fid'";
        $sql = "SELECT pict, prev_pict, user_id FROM portfolio WHERE (id=?i $addit)";
        $res = $DB->row($sql, $prj_id);
        $fname = $res['pict'];
        $pname = $res['prev_pict'];
        $uid = $res['user_id'];
        if ($uid != $fid && $force){
        	require_once ABS_PATH.'/classes/users.php';
        	$user = new users;
        	$user->GetUserByUID($uid);
        	$l_dir = substr($user->login, 0 ,2)."/".$user->login;
            
            // уведомление об удалении 
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
            messages::portfolioDeletedNotification( $user->uname, $user->usurname, $user->login );
        } else if ($uid != $fid && !$force) return "Вы не можете удалять чужие работы!";
        if ($fname) {
            $cfile = new CFile();
            $cfile->Delete(0,"users/$l_dir/upload/",$fname);
            $cfile->Delete( 0, "users/{$l_dir}/upload/", 'tn_' . $fname );
        }
        if ($pname){
            if (!$cfile) $cfile = new CFile();
            $cfile->Delete(0,"users/$l_dir/upload/",$pname);
        }
        $sql = "DELETE FROM portfolio WHERE id=?i $addit";
        $DB->query($sql, $prj_id); 
        $error = $DB->error;
        
        if ( !$error && $fid == $uid ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            $sql .= $DB->parse( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $prj_id, user_content::MODER_PORTFOLIO );
        }
        
        return $error;
    }
    
	/**
	 * Изменить сортировку выдачи портфолио на странице пользователя
	 *
	 * @param integer $uid       Ид пользователя
	 * @param integer $prof_id   ИД професии
	 * @param integer $direction Позиция
	 * @return integer ИД выдачи
	 */
    function ChangeProfOrder($uid, $prof_id, $direction)
    {
      global $DB; 
      if ($direction > 0) {$d = ' + 1'; $sord = ">";} else {$d = ' - 1'; $sord = "<"; $sdec = "DESC";};

      $sql = 
      "SELECT pc2.prof_id
         FROM portf_choise pc
       CROSS JOIN
         portf_choise pc2
        WHERE pc.user_id = ?i
          AND pc.prof_id = ?i
          AND pc2.user_id = pc.user_id
          AND pc2.ordering {$sord} pc.ordering
        ORDER BY pc2.ordering {$sdec} LIMIT 1";

      $prof_id2 = $DB->val($sql, $uid, $prof_id);
      if($prof_id2) {
        $sql = "UPDATE portf_choise SET ordering = ordering {$d} WHERE prof_id = ?i AND user_id = ?i";
        if($DB->query($sql, $prof_id, $uid)) {
            return $prof_id2;
        };
        
      }

      return 0;
    }
    
	/**
	 * Изменить позицию
	 *
	 * @param integer $uid       Ид пользователя
	 * @param integer $prof_id   ИД портфолио 
	 * @param integer $direction Сортировка
	 * @return integer ID
	 */
    function ChangePos($uid, $proj_id, $direction)
    {
      global $DB; 
      if ($direction > 0) {$d = ' + 1'; $sord = ">";} else {$d = ' - 1'; $sord = "<"; $sdec = "DESC";};

      $sql = 
      "SELECT p2.id
         FROM portfolio p
       CROSS JOIN
         portfolio p2
        WHERE p.id = ?i
          AND p2.prof_id = p.prof_id
          AND p2.user_id = p.user_id
          AND p2.norder {$sord} p.norder
        ORDER BY p2.norder {$sdec} LIMIT 1";

      $proj_id2 = $DB->val($sql, $proj_id);
      if($proj_id2) {
        $sql = "UPDATE portfolio SET norder = norder {$d} WHERE id = ?i AND user_id = ?i";
        if($DB->query($sql, $proj_id, $uid)) {
            return $proj_id2;
        }
      }
  

      return 0;
    }

  /**
   * Изменение признака просмотра превью для текстовой работы.
   * Включает функцию "Показывать превью" для данной работы
   * 
   * @param integer $uid код юзера
   * @param integer $proj_id код проекта
   * @param integer $ch включение (1) / выключение (0) просмотра
   * @return string сообщение об ошибке или пустая строка, если ошибок нет.
   */
    function ChangeTextPrev($uid, $proj_id, $ch)
    {
        global $DB;
        $error = '';
        $show_preview = ($ch == 1)?'TRUE':'FALSE';
        $sql = "UPDATE portfolio SET show_preview = ? WHERE id=?i AND user_id=?i ;";
        $DB->query($sql, $show_preview, $proj_id, $uid);
        return $error;
    }
    
    /**
	 * Включение превью везде
	 *
	 * @param integer $uid ИД ПОльзователя
	 */
    function OnAllPrev ($uid) {
        global $DB;
        $sql="select prof_id from portfolio where user_id=?i group by prof_id";
        $res = $DB->rows($sql, $uid);
        if ($res) {
            foreach($res as $v) {
                $prof_id = $v['prof_id'];
                portfolio::ChangeGrPrev($uid, $prof_id, $projs, 't');
            }
        }
    }
	/**
	 * Позволяет включать в определенном разделе превью (Блок - Уточнение к разделу)
	 * Функция доступна только ПРО аккаунтам
	 *
	 * @param integer  $uid       Ид пользователя
	 * @param integer  $proj_id   Проект ИД
	 * @param array    $projs     Возвращает данные по проектам
	 * @param string   $force_val Доп переменная, для составления запроса
	 * @return string  Сообщение об ошибке
	 */
    function ChangeGrPrev($uid, $prof_id, &$projs, $force_val = NULL)
    {
      global $DB;
      $sql = 
      "SELECT id, CASE WHEN prev_type = 0 THEN prev_pict ELSE descr END as prev_data, prev_type
         FROM portfolio
        WHERE user_id = ?i
          AND prof_id = ?i
          AND COALESCE(CASE WHEN prev_type = 0 THEN prev_pict ELSE descr END,'') <> ''";
        $res = $DB->rows($sql, $uid, $prof_id);
        
        $sql = "UPDATE portf_choise SET show_preview = ".($force_val===NULL ? 'NOT(show_preview)' : "'{$force_val}'")." WHERE user_id=?i AND prof_id=?i ;";
        $DB->query($sql, $uid, $prof_id);
            
        if($res) {
            foreach($res as $row) {
                $projs[$row['id']] = $row;
            }
        }

      return $DB->error;
    }


    /**
	 * Сохраняет стоимость и срок выполнения работы.
	 *
	 * @param integer $uid id пользователя
	 * @param integer $proj_id id работы
	 * @param real $cost стоимость работы
	 * @param integer $time_type тип срока (0 - часы, 1 - дни, 2 - месяцы)
	 * @param integer $time_value срок выполнения работы (в единицах $time_type)
     * @return string текст ошибки или пустая строка
	 */
    function ChangePortfPrice($uid, $proj_id, $cost, $cost_type, $time_type, $time_value)
    {
        global $DB;
        global $portf_cost;

        $uid = intval($uid);
        $proj_id = intval($proj_id);
        $cost = intval($cost * 100) / 100;
        $time_type = intval($time_type);
        $time_value = intval($time_value);

        /**
     * Проверка.
     */
        $error = '';
        if (($cost < 0) || ($cost > $portf_cost[$cost_type]))
        {
            $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость должна быть в пределе от 0 ' . view_range_cost2(0, $portf_cost[$cost_type], '', '', false, $cost_type) . '.';
        }
        if (($time_value < 0) || ($time_value > PORTF_TIME))
        {
            $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Срок должен быть в пределе от 0 до ' . PORTF_TIME . '.';
        }

        if ($uid && $proj_id && ($error == ''))
        {
            $sql = "UPDATE portfolio SET cost = $cost, cost_type='$cost_type', time_type = $time_type, time_value = $time_value WHERE id=?i AND user_id=?i";
            $DB->query($sql, $proj_id, $uid);
            $error = $DB->error;
            if ($error_db != '')
            {
                $error .= (($error_serv == '') ? '' : '<br />') . 'Ошибка сохранения в БД.';
            }
        }
        if ($error != '')
        {
            $error = 'Данные не сохранены<br /><br />' . $error;
        }
        return $error;
    }

    /**
	 * Изменение "избранности" работы - добалвение в избранные, если еще не выбрана и удаление, если уже выбрана.
	 *
	 * @param integer $prf_id код работы
	 * @param integer $prof_id код профессии
	 * @param integer $uid код юзера
	 * @return array результат (0-ой элемент: количество выбранных работ) и тип выполненой операции (1-ый элемент: 0 - удалена, 1 - добавлена)
	 */
    function ChangeFav($prf_id, $prof_id, $uid)
    {
        global $DB;
        $sql = "SELECT * FROM portfolio_fav WHERE (prf_id = ?i AND user_id = ?i)";
        $res = $DB->rows($sql, $prf_id, $uid);
        if ($res)
        {
            if ($prof_id > 0)
            {
                $sql = "INSERT INTO portfolio_fav (prf_id, user_id) VALUES ('$prf_id', '$uid');
        SELECT COUNT(*) FROM portfolio_fav AS pf INNER JOIN portfolio AS p ON (pf.prf_id = p.id) WHERE pf.user_id = '$uid' AND p.prof_id = '$prof_id'";
            }
            else
            {
                $sql = "INSERT INTO portfolio_fav (prf_id, user_id) VALUES ('$prf_id', '$uid');
        SELECT COUNT(*) FROM portfolio_fav WHERE user_id = '$uid'";
            }
            $ret[0] = $DB->val($sql);
            $ret[1] = 1;
        }
        else
        {
            if ($prof_id > 0)
            {
                $sql = "DELETE FROM portfolio_fav WHERE (prf_id = '$prf_id' AND user_id = '$uid');
        SELECT COUNT(*) FROM portfolio_fav AS pf INNER JOIN portfolio AS p ON (pf.prf_id = p.id) WHERE pf.user_id = '$uid' AND p.prof_id = '$prof_id'";
            }
            else
            {
                $sql = "DELETE FROM portfolio_fav WHERE (prf_id = '$prf_id' AND user_id = '$uid');
        SELECT COUNT(*) FROM portfolio_fav WHERE user_id = '$uid'";
            }
            $ret[0] = $DB->val($sql);
            $ret[1] = 0;
        }
        return $ret;
    }

    /**
	 * Изменение профессию работы - перемещение работы из одной профессии в другую
	 *
	 * @param integer 	$prf_id код работы
	 * @param integer 	$prof_id код профессии
	 * @param integer 	$uid код юзера
	 * @return resource Данные выборки
	 * 
	 */
    function ChangeProjectProf($uid, $prof_id, $prj_id)
    {
        global $DB;
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        $prfs = new professions();
        $profs = $prfs->GetAllProfessionsPortf($uid);
        foreach($profs as $pf) if($pf['checked']) $check_prof[] = $pf['id'];
        $check_prof[] = professions::CLIENTS_PROF_ID;
        $check_prof[] = professions::BEST_PROF_ID;
        if(!in_array($prof_id, $check_prof)) return false;
        
        $sql = "UPDATE portfolio SET prof_id = ?i, norder = '0' WHERE id = ?i AND user_id = ?i";
        $res = $DB->query($sql, $prof_id, $prj_id, $uid);

        return $res;
    }

  /**
   * Возвращает список избранных работ.
   *
   * @deprecated 
   * 
   * @param integer $prof_id id профессии
   * @param integer $uid id пользователя
   * @return array массив избранных работ (id работ)
   */
    function GetFavorites($prof_id, $uid)
    {
        global $DB;
        if ($prof_id > 0)
        {
            $sql = "SELECT pf.prf_id FROM portfolio_fav AS pf INNER JOIN portfolio AS p ON (pf.prf_id = p.id) WHERE p.prof_id='$prof_id' AND pf.user_id= ?i";
        }
        else
        {
            $sql = "SELECT pf.prf_id FROM portfolio_fav AS pf INNER JOIN portfolio AS p ON (pf.prf_id = p.id) WHERE pf.user_id= ?i ";
        }
        $ret = $DB->rows($sql, $uid);
        $out = array();
        if ($ret)
        {
            foreach($ret as $ikey => $value)
            {
                $out[] = $value['prf_id'];
            }
        }
        return $out;
    }

  /**
   * Возвращает количество всех работ или всех работ по конкретной профессии.
   *
   * @param integer $prof_id id профессии
   * @param integer $uid id пользователя
   * @return integer количество работ
   */
    function CountAll($uid, $prof_id=NULL, $not_count = false)
    {
        global $DB;
        if($not_count && $prof_id == professions::CLIENTS_PROF_ID ) return 1;
      $join_blocked  = ' LEFT JOIN portfolio_blocked pb ON p.id = pb.src_id ';
      $where_blocked = ' AND pb.src_id IS NULL ';
      $sql = "SELECT COUNT(*) FROM portfolio p $join_blocked WHERE p.user_id=?i $where_blocked ".($prof_id===NULL ? '' : " AND p.prof_id=?i");
      return $DB->val($sql, $uid, $prof_id);
    }
    
    /**
     * Список выбранных профейссий и информация по ним
     * 
     * @global type $DB
     * @param type $prof_id ИД определенной профессии, если хотим взять только ее
     * @param type $uid     ИД пользователя
     * @return type
     */
    public function getPortfolioCategory($prof_id = null, $uid = null) {
        global $DB;
        if(!$uid) $uid = $_SESSION['uid'];
        
        $profession = "";
        if($prof_id) {
            $profession = 'AND pc.prof_id = ?i';
        }
        
        $sql = "SELECT DISTINCT ON (pc.prof_id) pc.prof_id, pc.*, p.name as prof_name, pg.name as group_name, p.is_text as proftext, 
                       p.link AS proflink, pf.id as is_work, pc_prev.prof_id as prev_prof_id, m.on_moder  
                FROM portf_choise pc 
                INNER JOIN professions p ON p.id = COALESCE(pc.prof_origin, pc.prof_id)
                INNER JOIN prof_group pg ON pg.id = p.prof_group
                LEFT JOIN portfolio pf ON pf.user_id = pc.user_id AND pf.prof_id = pc.prof_id
                LEFT JOIN portf_choise pc_prev ON pc_prev.user_id = pc.user_id AND pc_prev.ordering = pc.ordering-1 
                LEFT JOIN (
                 SELECT user_id, prof_id, COUNT(id) AS on_moder FROM portf_choise_change WHERE ucolumn = 'text' AND (moderator_status = 0 OR moderator_status = -1) GROUP BY user_id, prof_id 
                ) AS m ON m.user_id = pc.user_id AND m.prof_id = pc.prof_id 
                WHERE pc.user_id = ?i {$profession} ORDER BY pc.prof_id";
                
        return $DB->rows($sql, $uid, $prof_id);
    }
    
    public function getFirstPortfolioCategory($uid = null) {
        global $DB;
        if(!$uid) $uid = $_SESSION['uid'];
        return $DB->val("SELECT prof_id FROM portf_choise WHERE ordering = 1 AND user_id = ?i", $uid);
    }
    
    /**
     * Рассортировываем портфолио
     * 
     * @param array   $prjs         Работы портфолио @see self::GetPortf();
     * @param integer $uid          Ид пользователя
     * @param object  $stop_words   @see class new stop_words()
     * @param integer $one_select   Костыль для подгрузки работ в определенную категорию профессии, создает переменную $prjs которая необходима в шаблоне 
     */
    public function prepareDataPortfolio($works, $uid, $stop_words, $one_select = false) {
        $i = $block = 0;
        $size_block = 3;
        $is_owner   = ($uid == get_uid(false));
        // Рассортировываем портфолио
        foreach ($works as $prj) {
            if ($prj['is_blocked'] == 't' && $uid != get_uid(false) && !hasPermissions('users'))
                continue;
            
            if ($i >= $size_block || $prj['prof_id'] != $old_prof) {
                $block++;
                $i = 0;
                //$i = $is_owner && !$add_work_block[$prj['prof_id']] ? 1 : 0;
                //$add_work_block[$prj['prof_id']] = true;
            }
            
            if($one_select) {
                $prjs[$block][] = $prj;
            }
            
            $pp[$prj['prof_id']][$block][]  = $prj;
            $pp_noblocks[$prj['prof_id']][] = $prj;

            $sName = /*$prj['moderator_status'] === '0' ? $stop_words->replace($prj['name'], 'plain') :*/ $prj['name'];
            $pt[$prj['prof_id']][$block][$prj['id']] = $sName;
            if (!isset($pname[$prj['prof_id']])) {
                $prj = professions::prepareCostText($prj, $stop_words);
                $pname[$prj['prof_id']] = $prj;
            }

            $i++;
            $old_prof = $prj['prof_id'];

            // Ключевые слова
            if (empty($ukeys[$prj['prof_id']])) {
                $ukeys[$prj['prof_id']] = professions::loadProfessionUserKeyword($uid, $prj['prof_id']);
            }
        }
        
        $result = array(
            'add_work_block' => $add_work_block,
            'pp'             => $pp,
            'pp_noblocks'    => $pp_noblocks,
            'pt'             => $pt,
            'pname'          => $pname,
            'ukeys'          => $ukeys
        );
        
        if($one_select) {
            $result['prjs'] = $prjs;
        }
        
        return $result;
    }
    
    
    /**
     * Список не заблокированных работ 
     * портфолио для указанного пользователя
     * 
     * @global type $DB
     * @param type $uid
     * @param type $page
     * @param type $limit
     * @return type
     */
    public function getList($uid, $page = 1, $limit = 6)
    {
        global $DB;
        
        $page = ($page > 0) ? $page : 1;
        $offset = ($page - 1) * $limit;
        
        return $DB->rows("
            SELECT 
                p.id, 
                p.user_id, 
                p.name AS title, 
                p.descr, 
                p.pict, 
                p.prev_pict, 
                p.show_preview, 
                p.norder, 
                p.prev_type, 
                p.is_video
            FROM portfolio AS p 
            LEFT JOIN portfolio_blocked AS pb ON p.id = pb.src_id
            WHERE 
                p.user_id = ?i 
                AND pb.src_id IS NULL
            ORDER BY p.id DESC
            LIMIT ?i OFFSET ?i
        ", $uid, $limit, $offset);
    }
    
    
    /**
     * Существует и не блокирована ли работа в портфолио у указанного юзера
     * 
     * @global type $DB
     * @param type $uid
     * @param type $id
     * @return type
     */
    public function isExistActive($uid, $id)
    {
        global $DB;
        
        return $DB->rows("
            SELECT 1
            FROM portfolio AS p 
            LEFT JOIN portfolio_blocked AS pb ON p.id = pb.src_id
            WHERE 
                p.id = ?i 
                AND p.user_id = ?i 
                AND p.is_blocked = FALSE 
                AND pb.src_id IS NULL
            LIMIT 1
        ", $id, $uid);
    }
    
    
    
}