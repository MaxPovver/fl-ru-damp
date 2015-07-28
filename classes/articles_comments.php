<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs_proto.php");

/**
 * Класс для работы с комментариями к статьям
 *
 */
class articles_comments extends blogs_proto {

    const MAX_FILE_SIZE = 2097152;
    
    const MAX_FILE_COUNT = 10;
    /**
     * Добавить сообщение(комментарий)
     *
     * @param integer $fid    UID
     * @param integer $reply  Идентификатор сообщения ответом на которое является данное сообщение
     * @param integer $thread Тема
     * @param string  $msg    Сообщение
     * @param string  $yt_link  ССылка на ютюб
     * @param mixed   $files  Вложения файлов
     * @param char    $ip     ИП отправителя
     * @param mixed   $error  Возвращает сообщение об ошибке
     * @param mixed   $small  Тип Вида
     * @return integer  ID нового сообщения
     */
    function Add($fid, $reply, $thread, $msg, $yt_link, $files, $ip, &$error, $small) {
        global $DB;

        if (!$error_flag) {
            $curname = get_class($this);

            $sql = "INSERT INTO articles_comments (from_id, parent_id, from_ip, created_time, article_id, msgtext, youtube_link)
                    VALUES (?i, ?, ?, NOW(), ?, ?, ?) RETURNING id";

            $l_id = $DB->val($sql, $fid, $reply, $ip, $thread, $msg, $yt_link);
            $error = $DB->error;

            if(!$error && !isNulArray($files['f_name'])) {
                $sql = '';
                if (is_array($files)) {
                    $data = array();
                    for($i = 0; $i < count($files['f_name']); $i ++) {
                        if ($files['f_name'][$i]) {
                            $data[] = "('$l_id', '{$files['f_id'][$i]}', '{$files['tn'][$i]}')";
                        }
                    }
                    if (count($data))
                        $sql = implode(', ', $data);
                } else {
                    $sql = "('$l_id', '$files', '$small')";
                }
                $DB->squery("INSERT INTO articles_comments_files (comment_id, file_id, small) VALUES $sql");
            }
        }

        return $l_id;
    }

    /**
     * Обновить комментарий
     *
     * @param integer $id    id коммента
     * @param integer $fid   UID
     * @param string  $msg   Сообщение
     * @param string  $yt_link  ССылка на ютюб
     * @param mixed   $files  Вложения файлов
     * @param mixed   $error  Возвращает сообщение об ошибке
     * @param mixed   $small  Тип Вида
     * @return integer  ID нового сообщения
     */
    function Update($id, $fid, $msg, $yt_link, $files, $files_cnt = 0, &$error, $small) {
        global $DB;

        $curname = get_class($this);

        $sql = "UPDATE articles_comments SET msgtext = ?,
                    modified_id = ?i,
                    modified_time = NOW(),
                    youtube_link = ?
                WHERE id = ?i";

        $DB->query($sql, $msg, $fid, $yt_link, $id);
        $error = $DB->error;
        $l_id = $id;

        if(!$error && !isNulArray($files['f_name'])) {
            $sql = '';
            if (is_array($files)) {
                $data = array();
                for($i = 0; $i < count($files['f_name']); $i ++) {
                    if ($files['f_name'][$i]) {
                        $data[] = "('$l_id', '{$files['f_id'][$i]}', '{$files['tn'][$i]}')";
                    }
                }
                if (count($data))
                    $sql = implode(', ', $data);
            } else {
                $sql = "('$l_id', '$files', '$small')";
            }
            $DB->squery("INSERT INTO articles_comments_files (comment_id, file_id, small) VALUES $sql");
        }

        return $l_id;
    }

    /**
     * Удалить комментарий
     * 
     * @param  int $id ID комментария
     * @param  int $uid UID того кто удаляет
     * @return bool true - успех, false - провал
     */
    function DeleteComment($id, $uid) {
        global $DB;
        $sql = "UPDATE articles_comments SET deleted_id = ?i
                WHERE id = ?i AND deleted_id IS NULL";

        $DB->query($sql, $uid, $id);
        if($DB->error) return false;

        return true;
    }
    
    /**
     * Восстановить комментарий
     * 
     * @param  int $id ID комментария
     * @param  int $uid UID того кто восстанавливает
     * @return bool true - успех, false - провал
     */
    function RestoreComment($id, $uid) {
        global $DB;
        $sql = "UPDATE articles_comments SET deleted_id = NULL
                WHERE id = ?i AND deleted_id IS NOT NULL";

        $DB->query($sql, $id);
        if($DB->error) return false;

        return true;
    }

    /**
     * Выборка тем сообщений
     *
     * @param integer $item_id  ИД треда
     * @param string  $error    Возвращает сообщения об ошибке
     */
    function GetThreads($item_id, &$error) {
        global $DB;
        $curname = get_class($this);
        $sql = "SELECT id, blg.from_id, parent_id, created_time, msgtext,
                    modified_id, deleted_id,
                    modified_time,
                    u.uname, u.usurname, u.is_banned, u.login, u.photo, u.is_pro, u.is_pro_test, u.role,
                    mod.uname as mod_name, mod.usurname as mod_usurname, mod.login as mod_login, mod.role as mod_role,
                    youtube_link
                FROM articles_comments as blg
                INNER JOIN users as u ON u.uid=blg.from_id
                LEFT JOIN users as mod ON mod.uid=blg.modified_id
                WHERE blg.article_id=?i
                ORDER BY created_time";

        $this->thread = $DB->rows($sql, $item_id);        

        $error .= $DB->error;
        if ($error) $error = parse_db_error($error);
        else {
            $this->msg_num = count($this->thread);
            if ($this->msg_num > 0) $this->SetVars(0);
        }
        //return array($name, $id_gr, 99);
    }

    /**
     * Формирует массив в аттачами к выбранным комментариям
     *
     * @param array|int $ids Массив идентификаторов комментариев или ид одного комментария
     */
    function getAttaches($ids, $autoindex = false) {
        global $DB;
        if(!count($ids)) return false;

        $comments = is_array($ids) ? implode(',', $ids) : $ids;
        $sql = "SELECT f.*, af.comment_id FROM articles_comments_files as af
                    INNER JOIN file as f ON f.id = af.file_id
                 WHERE af.comment_id IN ($comments) ORDER BY af.id";

        $rows = $DB->rows($sql);

        if($DB->error) return false;

        $attaches = array();

        if(!$rows) return $attaches;

        if(is_array($ids)) {
            foreach($rows as $attach) {
                if(!$autoindex) {
                    $attaches[$attach['comment_id']][$attach['id']] = $attach;
                } else {
                    $attaches[$attach['comment_id']][] = $attach;
                }
            }
        } else {
            foreach($rows as $attach) {
                if(!$autoindex) {
                    $attaches[$attach['id']] = $attach;
                } else {
                    $attaches[] = $attach;
                }
            }
        }

        return $attaches;
    }

    /**
     * Удаляет вложения по их id
     *
     * @param integer $comment_id ID комментария, из которого удаляются вложения
     * @param array $attaches Массив с идентификаторами файлов, которые нужно удалить
     */
    function removeAttaches($comment_id, $attaches) {
        $comment_attaches = $this->getAttaches($comment_id);

        $file = new CFile();
        foreach($attaches as $attach) {
            if(!isset($comment_attaches[$attach])) continue;
            $file->Delete($attach);
        }
    }


    /**
     * Подгрузка аттачей
     *
     * @param array $attach			массив с элементами типа CFile
     * @param array $max_image_size	массив с максимальными размерами картинки (см. CFile). Один для всех элементов attach
     * @param string $login			логин юзера, которому загрузить картинку. По умолчанию - юзер из $_SESSION['login']
     * @return array				массив ($files, $alert, $error_flag)
     */
    function UploadFiles($attach, $max_image_size, $login = '') {
        $alert = null;
        if ($login == '')
            $login = $_SESSION['login'];
        if ($login == '')
            $login = 'Anonymous';
        if ($attach)
            foreach ($attach as $file) {
                $file->max_size = self::MAX_FILE_SIZE;
                $file->proportional = 1;
                $f_name = $file->MoveUploadedFile($login . "/upload");
                $f_id = $file->id;
                $ext = $file->getext();
                if (in_array($ext, $GLOBALS['graf_array']))
                    $is_image = TRUE;
                else
                    $is_image = FALSE;
                $p_name = '';
                $p_id = '';
                if (! isNulArray($file->error)) {
                    $error_flag = 1;
                    $alert = "Один или несколько файлов не удовлетворяют условиям загрузки.";
                    break;
                } else {
                    if ($is_image && $ext != 'swf' && $ext != 'flv') {
                        if (! $file->image_size['width'] || ! $file->image_size['height']) {
                            $error_flag = 1;
                            $alert = 'Невозможно уменьшить картинку';
                            break;
                        }
                        if (! $error_flag && ($file->image_size['width'] > $max_image_size['width'] || $file->image_size['height'] > $max_image_size['height'])) {
                            if (! $file->img_to_small("sm_" . $f_name, $max_image_size)) {
                                $error_flag = 1;
                                $alert = 'Невозможно уменьшить картинку.';
                                break;
                            } else {
                                $tn = 2;
                                $p_name = "sm_$f_name";
                                $p_id = $file->id;
                            }
                        } else {
                            $tn = 1;
                        }
                    } else
                    if ($ext == 'flv') {
                        $tn = 2;
                    } else {
                        $tn = 0;
                    }
                }
                $files['f_id'][] = $f_id;
                $files['f_name'][] = $f_name;
                $files['p_name'][] = $p_name;
                $files['p_id'][] = $p_id;
                $files['tn'][] = $tn;
            }
        return array($files, $alert, $error_flag);
    }


    /**
     * Получить комментарий по ID
     *
     * @param integer $id ид комментария
     */
    function getComment($id) {
        global $DB;
//        $sql = "SELECT * FROM articles_comments WHERE id = $id";

        $sql = "SELECT id, blg.from_id, parent_id, created_time, msgtext,
                    modified_id, deleted_id,
                    modified_time,
                    u.uname, u.usurname, u.is_banned, u.login, u.photo, u.is_pro, u.is_pro_test, u.role,
                    mod.uname as mod_name, mod.usurname as mod_usurname, mod.login as mod_login, mod.role as mod_role,
                    youtube_link
                FROM articles_comments as blg
                LEFT JOIN users as u ON u.uid=blg.from_id
                LEFT JOIN users as mod ON mod.uid=blg.modified_id
                WHERE blg.id=?i";

        $comment = $DB->row($sql, $id);
        if($DB->error) return false;

        return $comment;
    }


    /**
     * Получить комментарии по ID, для рассылки
     *
     * @param integer $id ид комментария
     */
    function getComments4Sending($message_ids, $connect = NULL) {
        global $DB;
        if(!$message_ids) return NULL;
        if(is_array($message_ids))
          $message_ids = implode(',', array_unique($message_ids));

        $sql = "SELECT c.id, c.from_id, c.parent_id, c.created_time, c.msgtext, c.article_id,
                    u.uid, u.uname, u.usurname, u.is_banned, u.login, u.role,
                    s.uid as s_uid, s.uname as s_name, s.usurname as s_usurname, s.login as s_login, s.role as s_srole, s.subscr as s_subscr, s.email as s_email, s.is_banned as s_banned,
                    art.user_id as a_uid,
                    aa.uname as a_uname,
                    aa.usurname as a_usurname,
                    aa.login as a_login,
                    aa.email as a_email,
                    aa.subscr as a_subscr,
                    aa.is_banned as a_banned
                FROM articles_comments as c
                LEFT JOIN users as u ON u.uid=c.from_id
                LEFT JOIN articles_comments as par ON par.id = c.parent_id
                LEFT JOIN users as s ON s.uid=par.from_id
                LEFT JOIN articles_new as art ON art.id=c.article_id
                LEFT JOIN users as aa ON aa.uid=art.user_id
                WHERE c.id IN ($message_ids) AND 
                    ((c.parent_id IS NOT NULL AND par.from_id != c.from_id) OR c.parent_id IS NULL)";

        return $DB->rows($sql);
    }

    /**
     * Изменить порядок
     *
     * @deprecated
     * @param <type> $id
     * @param <type> $files
     *
     */
    function reorderFiles($id, $files) {
        global $DB;
        foreach($files as $k => $file) {
            $k++;
            $sql = "UPDATE articles_comments_files SET file_order =?
                    WHERE comment_id = ?i AND file_id = ?";

            $DB->query($sql, $k, $id, $file);
            if($DB->error) return false;
        }

    }

}

