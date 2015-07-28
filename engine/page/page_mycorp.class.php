<?php
class page_mycorp extends page_base {  
    public $name_page = "about2";
    
    function post($id) {            
        front::og("tpl")->blog = front::og("db")->select("SELECT cb.*, u.login, u.uname, u.usurname, u.role, u.is_pro, u.is_pro_test, u.boss_rate FROM corporative_blog as cb, users as u WHERE cb.id = ? AND u.uid = cb.id_user;", $id)->fetchRow();
        front::og("tpl")->count_comment = front::og("db")->select("SELECT COUNT(id_blog) as count FROM corporative_blog WHERE id_blog =? GROUP BY id_blog", $id)->fetchOne();
        $comments = front::og("db")->select("SELECT cb.*, u.login, u.uname, u.usurname, u.photo, u.role, u.is_pro, u.is_pro_test, u.boss_rate FROM corporative_blog as cb, users as u WHERE cb.id_blog = ? AND u.uid = cb.id_user;", $id)->fetchAll();
        
        if($comments) foreach($comments as $k=>$v) {
            if($v['id_modified']) $mod[$v['id_modified']] = $v['id_modified'];
            if($v['id_deleted']) $mod[$v['id_deleted']] = $v['id_deleted'];
            $cid[$v['id']] = $v['id'];
        }
        
        if($cid) $attach = front::og("db")->select("SELECT * FROM corporative_blog_attach WHERE msg_id IN(?a)", $cid)->fetchAll();
        if($mod) $moders = front::og("db")->select("SELECT login, uname, usurname, uid, role, is_pro, is_pro_test, boss_rate FROM users WHERE uid IN(?a)", $mod)->fetchAll();
        
        if($moders) {
            foreach($moders as $key=>$val) $res_mod[$val['uid']] = $val;
            front::og("tpl")->moders = $res_mod;
            //print_r($res_mod);
        }
        if($attach) {
            foreach($attach as $key=>$val) {
                $res_attach[$val['msg_id']][] = $val;
            }
            front::og("tpl")->attach = $res_attach;
        }
        if($comments) {
            $sortComm = $this->sortTreeComment($comments);
            front::og("tpl")->sortComm = $sortComm;
        }
        
        $attach_blog = front::og("db")->select("SELECT * FROM corporative_blog_attach WHERE msg_id = ?", $id)->fetchAll();
        if($attach_blog) {
            front::og("tpl")->attach_blog = $attach_blog;
        }    
        
        front::og("tpl")->script = array( 'mAttach.js' );
        front::og("tpl")->comments = $comments;
        front::og("tpl")->display("my_corporative_post.tpl");    
    }
    function deleteAction() {
        $db = front::og("db"); 
        
        if(intval($id = front::$_req["id"]) > 0) {
            $affected_rows = $db->delete("DELETE FROM corporative_blog WHERE id = ?n;", 
                $id
            );
        }
        echo json_encode(array("success"=>$affected_rows));   
    }
    function saveAction() {
        $db = front::og("db"); 
        $form = front::$_req["form"];
        $id_comm = front::$_req["comment"];
        $parent = front::$_req["parent"];
        if(!$id_comm) $id_comm = false;
    //    global $session;
        $validate_errors = array();
        
        $save = array();
        
        if(($str = trim($form['title'])) && mb_strlen($str)>=3) {
            $save['title'] = change_q_x_a(antispam($str), 0, 96);
        } else {
            $validate_errors['title'] = 'Заголовок короче 3 символов';            
        }
        
        if(($str = trim($form['msg'])) && mb_strlen($str)>=3) {
            $save['msg'] = change_q_x_a(antispam($str), false, false);
        } else {
            $validate_errors['msg'] = 'Текст короче 3 символов';            
        }
        
        if (strlen($form['msg']) > blogs::MAX_DESC_CHARS) {
            $validate_errors['msg'] = "Максимальный размер сообщения ".blogs::MAX_DESC_CHARS." символов!";;            
        } else {
            $save['msg'] = change_q_x_a(antispam($form['msg']), false, false);
        }
        
        $yt_link = substr(change_q_x(antispam(str_replace('watch?v=', 'v/', $form['yt_link'])), true), 0, 128);
        if ($yt_link != '') {
            if ((strpos($yt_link, 'http://ru.youtube.com/v/') !== 0)
            && (strpos($yt_link, 'http://youtube.com/v/') !== 0)
            && (strpos($yt_link, 'http://www.youtube.com/v/') !== 0)) {
                $validate_errors['yt_link'] = "Неверная ссылка.";
            }
        }
        
        
        if(sizeof($validate_errors)>0) {
            echo json_encode(array("success"=>0, "validate"=>$validate_errors));    
            exit(1);
        }
        
        $save = front::toWin(array(
            "title" => $form["title"], 
            "msg" => $form["msg"], 
            "yt_link" => $yt_link, 
            //"id_blog" => 0
        ));
        
        $id = intval($id = front::$_req["id"]);
        
        if($id_comm) {
           // if($parent > 0) {
          //   
          //  } else {
                $save["id_blog"] = $parent;
                $save["id_reply"] = $id;
                 $save["id_user"] = get_uid();
                $id = $db->insert("corporative_blog", $save);
          //  }
            //if($id_comm && $id > 0) {
                  //  $save["id_blog"] = $id;
              //      $save["id_reply"] = $id;
//                }
        } else {
            if($id > 0) {
                $save["id_modified"] = get_uid();
                $save["id_deleted"] = 0;
                $save["date_change"] = date("Y-m-d H:i:s");
                $aff = $db->update("UPDATE corporative_blog SET ?s WHERE (id = ?n)", $save, $id);
            } else {
                $save["id_user"] = get_uid();
                $id = $db->insert("corporative_blog", $save);
            }
        }
        
        
         
        if($form["files_deleted"] != "") {
            $form["files_deleted"] = preg_replace('/\\\\\"/', '"', $form["files_deleted"]);
            $filesBefore = json_decode($form["files_deleted"]);
            $login = $_SESSION['login'];
            foreach($filesBefore as $file) {
                if(!$file->db_id) continue;
                front::og("db")->delete("DELETE FROM corporative_blog_attach WHERE id = ?n", $file->db_id);               
            }
        }
        if($form["files"] != "") {
            //$filesBefore = explode(";", $form["files"]);
          //  vardump($form["files"]);
            $form["files"] = preg_replace('/\\\\\"/', '"', $form["files"]);
            $filesBefore = json_decode($form["files"]);
            
            if ($group == 7) {
                $max_image_size = array('width' => 400, 'height' => 600, 'less' => 0);
            } else {
                $max_image_size = array('width' => 470, 'height' => 1000, 'less' => 0);
            }
            
            
            $login = $_SESSION["login"];
            
            if($filesBefore)
            foreach($filesBefore as $file) {
                if(!$file->temp) continue;
                $b_file = new CFile("temp/".$file->id);
                if($b_file->id > 0) {
                    $b_file->Rename("users/".substr($login, 0, 2)."/".$login. "/upload" ."/". $file->id);
                    
                    $ext = $b_file->getext();
                    if (in_array($ext, $GLOBALS['graf_array']))
                        $is_image = TRUE;
                    else
                        $is_image = FALSE;
                        
                    $b_file->max_size = blogs::MAX_FILE_SIZE;
                    $b_file->proportional = 1;
                    
                    if (! isNulArray($file->error)) {
                      //  $error_flag = 1;
                        //print_r($file->error);
                        $alert[3] = "Один или несколько файлов не удовлетворяют условиям загрузки.";
                       // break;
                    } else {
                        if ($is_image && $ext != 'swf' && $ext != 'flv') {
                            if (! $b_file->image_size['width'] || ! $b_file->image_size['height']) {
                               // $error_flag = 1;
                                $alert[3] = 'Невозможно уменьшить картинку';
                                break;
                            }
                            if (! $error_flag && ($b_file->image_size['width'] > $max_image_size['width'] || $b_file->image_size['height'] > $max_image_size['height'])) {
                                if (! $b_file->img_to_small("sm_" . $file->id, $max_image_size)) {
                                  //  $error_flag = 1;
                                    $alert[3] = 'Невозможно уменьшить картинку.';
                                    break;
                                } else {
                                    $b_file->tn = 2;
                                    $b_file->p_name = "sm_".$file->id;
                                }
                            } else {
                                $b_file->tn = 1;
                            }
                        } else 
                            if ($ext == 'flv') {
                                $b_file->tn = 2;
                            } else {
                                $b_file->tn = 0;
                            }
                        if($alert[3]) $validate_errors['files'] = $alert[3];            
                        $files[] = $b_file;
                    }
                    
                    
                }                
            }
        }
  
        //global $session;
        
        if (is_array($files) && sizeof($files)) {
            $asql = '';
            foreach($files as $file) {//currval('corporative_blog_id_seq')
                if ($file->name) $asql .= ", ({$id}, '{$file->name}', '{$file->tn}')";
            }
            if ($asql) $asql = substr($asql, 2);
        }
        //echo $asql;
        if ($asql) pg_query(DBConnect(), "INSERT INTO corporative_blog_attach(msg_id, \"name\", small) VALUES $asql");
        
        $htmlMode = front::$_req["htmlMode"];
        if($htmlMode == "inPostPage") {
            
            front::og("tpl")->blog = front::og("db")->select("SELECT cb.*, u.login, u.uname, u.usurname, u.role, u.is_pro, u.is_pro_test, u.boss_rate FROM corporative_blog as cb, users as u WHERE cb.id = ? AND u.uid = cb.id_user;", $id)->fetchRow();
            
            $attach_blog = front::og("db")->select("SELECT * FROM corporative_blog_attach WHERE msg_id = ?", $id)->fetchAll();
            if($attach_blog) {
                front::og("tpl")->attach_blog = $attach_blog;
            }    
            
           // front::og("tpl")->usbank  = $usr;
          //  front::og("tpl")->comment = $comm;
            //front::og("tpl")->blog   = $blog;
            $html = front::og("tpl")->fetch("my_corporative_post_item.tpl");
            
        } elseif($htmlMode == "normal") {
            $blog = front::og("db")->select("SELECT * FROM corporative_blog WHERE id_blog = 0 AND (id_deleted IS NULL OR id_deleted = 0) AND id = ?n", $id)->fetchRow();
            $bids  = array($id=>$id);
            $uids  = array($blog["id_user"]=>$blog["id_user"]);
            $comm  = front::get_hash(front::og("db")->select("SELECT COUNT(id_blog) as count, id_blog FROM corporative_blog WHERE id_blog IN(?a) GROUP BY id_blog", $bids)->fetchAll(), "id_blog", "count");
            $user  = front::og("db")->select("SELECT uname, usurname, login, uid, role, is_pro, is_pro_test, boss_rate FROM users WHERE uid IN(?a)", $uids)->fetchAll();//, "uid", "usname");
            
            $cid[$blog['id']] = $blog['id'];
            
            
            if($cid) $attach = front::og("db")->select("SELECT * FROM corporative_blog_attach WHERE msg_id IN(?a)", $cid)->fetchAll();
            if($attach) {
                foreach($attach as $key=>$val) {
                    $res_attach[$val['msg_id']][] = $val;
                }
                front::og("tpl")->attach = $res_attach;
            }    
            
            foreach($user as $k=>$v)  $usr[$v['uid']]= $v;
        
            front::og("tpl")->usbank  = $usr;
            front::og("tpl")->comment = $comm;
            front::og("tpl")->blog   = $blog;
            $html = front::og("tpl")->fetch("my_corporative_item.tpl");
        }
        
        echo json_encode(array("success"=>true, "id"=>$id, "html"=>front::toUtf($html)));
    }
    function getinfoAction() {
        $db = front::og("db"); 
        $one_news = $db->select("SELECT id, title, msg, yt_link, id_user FROM corporative_blog WHERE id = ?n LIMIT 1;", front::$_req["id"])->fetchRow();
        
        $user  = front::og("db")->select("SELECT login FROM users WHERE uid = ?n", $one_news['id_user'])->fetchRow();
        
        $attach = front::og("db")->select("SELECT * FROM corporative_blog_attach WHERE msg_id = ?n", front::$_req["id"])->fetchAll();
        if($attach) {
            foreach($attach as $key=>$val) {
                $res_attach[] = array("db_id"=>$val["id"],"id"=>$val["name"], "path"=>WDCPREFIX."/users/".$user["login"]. "/upload" ."/". $val["name"]);
            }
            $one_news["files"] = json_encode($res_attach);
        }    
        
        $one_news = front::toUtf($one_news);   
        echo json_encode(array("form"=>$one_news));
    }
    function checkYoutubeAction() {
        $yt_link = front::$_req["value"];
        $yt_link = substr(change_q_x(antispam(str_replace('watch?v=', 'v/', $yt_link)), true), 0, 128);
        if ($yt_link != '') {
            if ((strpos($yt_link, 'http://ru.youtube.com/v/') !== 0)
            && (strpos($yt_link, 'http://youtube.com/v/') !== 0)
            && (strpos($yt_link, 'http://www.youtube.com/v/') !== 0)) {
                echo json_encode(array("valid"=>false, "reason"=>"Неверная ссылка"));
                return;
            }
        }
        echo json_encode(array("valid"=>true));
    }
    function corporativeAction() {
        //error_reporting(E_ALL);
        global $session;
        if($this->uri[0] && $this->uri[0] == "post") {
            $this->post($this->uri[1]);
            exit(0);   
        }
        front::og("tpl")->session   = $session;
        front::og("tpl")->name_page = $this->name_page;
        
        self::getCorporateBlog();
        
        front::og("tpl")->script = 'mAttach.js';
        front::og("tpl")->display("my_corporative.tpl");
    }
    function sortTreeComment(&$comments) {
        foreach($comments as $k=>$v) {
            $tree[$v['id']] = $v;
            $lvl[$v['id_reply']][$v['id']] = $v['id'];
        }
        
        $comments = $tree;
        
        $sort = array();
        $level = 0;
        $last_id = 0;
        while(count($sort) < count($tree)) {
            $i++;
            if(array_key_exists((int)$last_id, $lvl)) {
                $min = min($lvl[$last_id]);
                unset($lvl[$last_id][$min]);
                if(count($lvl[$last_id]) == 0) unset($lvl[$last_id]);
                $id = $min;
            } else {
                $id = $last_id;
            }
            
            if(!array_key_exists($id, $sort)) $sort[$id] = $level;
            
            if($last_id === $id) {
                $level--; 
                $last_id = $tree[$id]['id_reply'] ;
            } else {
                $level++;
                $last_id = $id;
            }
            
            if($i>10000) break;
        }
        
        return $sort;
    }
    /**
     * Выборка блогов из таблицы
     *
     * @param int $page страница
     * @param int $count количество на странице
     */
    function getCorporateBlog($page=1, $count=10) {
        $total = front::og("db")->select("SELECT COUNT(*) FROM corporative_blog WHERE id_blog = 0  AND (id_deleted IS NULL OR id_deleted = 0)")->fetchOne();
        
        front::og("tpl")->page_corp  = $page;
        front::og("tpl")->pages_corp = ceil($total/$count);
        front::og("tpl")->total_corp = $total;
        $page--;
        
        $sql_page =$page*$count;
        
        $blogs = front::og("db")->select("SELECT * FROM corporative_blog WHERE id_blog = 0 AND (id_deleted IS NULL OR id_deleted = 0) ORDER BY id DESC LIMIT ? OFFSET ?", $count, $sql_page)->fetchAll();
        $bids  = front::get_hash($blogs, "id", "id");
        $uids  = front::get_hash($blogs, "id_user", "id_user");
        $comm  = front::get_hash(front::og("db")->select("SELECT COUNT(id_blog) as count, id_blog FROM corporative_blog WHERE id_blog IN(?a) GROUP BY id_blog", $bids)->fetchAll(), "id_blog", "count");
        $user  = front::og("db")->select("SELECT uname, usurname, login, uid, role, is_pro, is_pro_test, boss_rate FROM users WHERE uid IN(?a)", $uids)->fetchAll();//, "uid", "usname");
        
        foreach($blogs as $k=>$v) $cid[$v['id']] = $v['id'];
        
        
        if($cid) $attach = front::og("db")->select("SELECT * FROM corporative_blog_attach WHERE msg_id IN(?a)", $cid)->fetchAll();
        if($attach) {
            foreach($attach as $key=>$val) {
                $res_attach[$val['msg_id']][] = $val;
            }
            front::og("tpl")->attach = $res_attach;
        }    
        
        foreach($user as $k=>$v)  $usr[$v['uid']]= $v;
        
        front::og("tpl")->usbank  = $usr;
        front::og("tpl")->comment = $comm;
        front::og("tpl")->blogs   = $blogs;
        //front::og("tpl")->attach  = $attach;
    }
}
?>