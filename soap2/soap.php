<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/DB.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/soap2/classes/stdf.php");

class FLTrayService {

    function AuthUser ($login, $password, $session) {
        $DB = new DB('master');
        // $html_temp ='<html><head><style> td, span, div, .std{ font-family: Tahoma; font-size: 11px; color: #666666; font-weight: normal; } .frlname11{ font-size: 11px; color: #666666; font-weight: bold; } img.pro{ background-color:none;	width: 26px; height: 11px; border-width:0px; margin-right: 3px; } .freelancerU img.pro{ width: 26px; height: 11px; border-width:0px; } .cl9{ color: #909090; } .c_grey{ color: #909090; font-weight:bold; display:block; } .freelancerU_content a.blue { font-weight:bold; display:block; color:#003399; } .u_active{ font-size: 80%; color: #ff6b3d; margin-right:16px; } .u_inactive{ font-size: 80%; color: #477ad9; margin-right:16px; } .prj_bold { font-weight:bold; color: #000000; } .prj_a { color: #000000; text-decoration: none; font-family: Tahoma; font-size: 11px; color: #666666; font-weight: normal; } .user_blue { font-weight:bold; color:#003399; } </style></head><body>@</body></html>';

        $message_temp='<html><head><style> td, span, div, .std{ font-family: Tahoma; font-size: 11px; color: #666666; font-weight: normal; } .frlname11{ font-size: 11px; color: #666666; font-weight: bold; } img.pro{ background-color:none;	width: 26px; height: 11px; border-width:0px; margin-right: 3px; } .freelancerU img.pro{ width: 26px; height: 11px; border-width:0px; } .cl9{ color: #909090; } .c_grey{ color: #909090; font-weight:bold; display:block; } .freelancerU_content a.blue { font-weight:bold; display:block; color:#003399; } .u_active{ font-size: 80%; color: #ff6b3d; margin-right:16px; } .u_inactive{ font-size: 80%; color: #477ad9; margin-right:16px; } .prj_bold { font-weight:bold; color: #000000; } .prj_a { color: #000000; text-decoration: none; font-family: Tahoma; font-size: 11px; color: #666666; font-weight: normal; } .user_blue {  font-family: Tahoma; font-size: 10px; font-weight:bold;  color:#003399; } </style></head><body><table border="0" cellpadding="0" cellspacing="0" ><tbody><tr><td><table border="0" cellpadding="0" cellspacing="0"><tbody><tr valign="top"><td align="center" width="70"><a href="'.$GLOBALS["host"].'/users/@LOGIN@/" class="frlname11"> <img src="'.$GLOBALS["host"].'/users/@LOGIN@/foto/@PIC@" alt="@LOGIN@" border="0" height="50" width="50"></a></td><td class="frlname11">@PRO@ @ONLINE@ <a href="'.$GLOBALS["host"].'/users/@LOGIN@" class="frlname11">@UNAME@ @USURNAME@</a> [<a href="'.$GLOBALS["host"].'/users/@LOGIN@" class="frlname11">@LOGIN@</a>]</td></tr></tbody></table><table border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr valign="top"><td align="center" width="20">&nbsp;</td><td style="padding-right: 20px;"><a target="_blank" href="'.$GLOBALS["host"].'/contacts/?from=@LOGIN@" class="c_grey">@TEXT@</a></td></tr></table><br></body></html>';

        $project_temp='<html><head><style> td, span, div, .std{ font-family: Tahoma; font-size: 11px; color: #666666; font-weight: normal; } .frlname11{ font-size: 11px; color: #666666; font-weight: bold; } img.pro{ background-color:none;	width: 26px; height: 11px; border-width:0px; margin-right: 3px; } .freelancerU img.pro{ width: 26px; height: 11px; border-width:0px; } .cl9{ color: #909090; } .c_grey{ color: #909090; font-weight:bold; display:block; } .freelancerU_content a.blue { font-weight:bold; display:block; color:#003399; } .u_active{ font-size: 80%; color: #ff6b3d; margin-right:16px; } .u_inactive{ font-size: 80%; color: #477ad9; margin-right:16px; } .prj_bold { font-weight:bold; color: #000000; } .prj_a { color: #000000; text-decoration: none; font-family: Tahoma; font-size: 11px; color: #666666; font-weight: normal; } .user_blue {   font-family: Tahoma; font-size: 10px; font-weight:bold; color:#003399; } </style></head><body><div class="prj_bold">@HEAD@</div><div class="prj_bold">@BUDGET@ @BUDGETB@</div>  <a target="_blank" class="prj_a" href="'.$GLOBALS["host"].'/blogs/view.php?tr=@THREAD@">@TEXT@</a><br><a target="_blank" class="user_blue" href="'.$GLOBALS["host"].'/users/@LOGIN@/">@UNAME@ @USURNAME@ [@LOGIN@]</a><br></body></html>';

        $log=fopen("sql.log","a");
        if (!$login) { return array('result' => mb_convert_encoding('Нет логина', "UTF-8", "windows-1251"), 'cookie'=> '', 'message_template'=>'', 'project_template'=>'');  }
        $login= trim($login);
        $login = mysql_real_escape_string($login);
        $password= trim(mb_convert_encoding($password, "windows-1251", "UTF-8"));
        $session = trim($session);
        $auth=0;
        $update_sess=0;
        // проверяем наличие других сессий
        $res = mysql_query("SELECT * FROM sessions WHERE is_tray=1 AND session_login='".$login."'", DBMyConnect());
        fwrite($log,"\n".date("Y.m.d h:i:s")." "."SELECT * FROM sessions WHERE is_tray=1 AND session_login='".$login."'");
        $sess_rows=mysql_num_rows($res);
        // сессий больше чем одна выбиваем обоих
        if ($sess_rows>1)  {
            $res = mysql_query("DELETE FROM sessions WHERE is_tray=1 AND session_login='".$login."'", DBMyConnect());
            fwrite($log,"\n".date("Y.m.d h:i:s")." "."DELETE FROM sessions WHERE is_tray=1 AND session_login='".$login."'");
            return array('result' => mb_convert_encoding('Две сессии. Возможно еще кто-то под Вашим именем в сети. Перелогиньтесь с введением логина и пароля', "UTF-8", "windows-1251"), 'cookie'=> '', 'message_template'=>'', 'project_template'=>'');
        }
        elseif (!$session) {

            // удаляем все предыдущие сессии
            $res = mysql_query("DELETE FROM sessions WHERE is_tray=1 AND session_login='".$login."'", DBMyConnect());
            fwrite($log,"\n".date("Y.m.d h:i:s")." "."DELETE FROM sessions WHERE is_tray=1 AND session_login='".$login."'");
            //  проверяем логин-пароль и все остальное
            $res_pass = $DB->query("SELECT uid, login, is_pro, is_banned, active FROM users WHERE lower(login)=? AND passwd=? LIMIT 1", strtolower($login), $password);
            fwrite($log,"\n".date("Y.m.d h:i:s")." "."SELECT uid, login, is_pro, is_banned, active FROM users WHERE lower(login)='".strtolower($login)."' AND passwd='".$password."' LIMIT 1");
            if (pg_numrows($res_pass)){
                // ок
                $user_arr=pg_fetch_assoc($res_pass);
                if ($user_arr["is_pro"]=="f") { return array('result' => mb_convert_encoding('Доступно только для PRO', "UTF-8", "windows-1251"), 'cookie'=> '', 'message_template'=>'', 'project_template'=>''); }
                if ($user_arr["active"]=="f") { return array('result' => mb_convert_encoding('А активировать аккаунт Пушкин будет?', "UTF-8", "windows-1251"), 'cookie'=> '', 'message_template'=>'', 'project_template'=>''); }
                if ($user_arr["is_banned"]) { return array('result' => mb_convert_encoding('Забанены вы нафих', "UTF-8", "windows-1251"), 'cookie'=> '', 'message_template'=>'', 'project_template'=>''); }
                $auth=1;
                $uid=$user_arr["uid"];
                $login=$user_arr["login"];
            }
            else { return array('result' => mb_convert_encoding('Не правильный логин-пароль', "UTF-8", "windows-1251"), 'cookie'=> '', 'message_template'=>'', 'project_template'=>''); }
        }
        else {
            // если передана сессия - пытаемся  по ней зарегится
            $res_sess = mysql_query("SELECT * FROM sessions WHERE is_tray=1 AND session_id='".$session."'", DBMyConnect());
            fwrite($log,"\n".date("Y.m.d h:i:s")." "."SELECT * FROM sessions WHERE is_tray=1 AND session_id='".$session."'");

            if (@mysql_num_rows($res_sess)) {
                $sess_auth=mysql_fetch_assoc($res_sess);
                $res_pass = $DB->query("SELECT uid, login, is_pro, is_banned, active FROM users WHERE uid=? LIMIT 1", $sess_auth["session_uid"]);
                fwrite($log,"\n".date("Y.m.d h:i:s")." "."SELECT uid, login, is_pro, is_banned, active FROM users WHERE uid='".$sess_auth["session_uid"]."' LIMIT 1");
                if (pg_numrows($res_pass)){
                    // ок
                    $user_arr=pg_fetch_assoc($res_pass);
                    if ($user_arr["is_pro"]=="f") { return array('result' => mb_convert_encoding('Доступно только для PRO', "UTF-8", "windows-1251"), 'cookie'=> '', 'message_template'=>'', 'project_template'=>''); }
                    if ($user_arr["active"]=="f") { return array('result' => mb_convert_encoding('А активировать аккаунт Пушкин будет?', "UTF-8", "windows-1251"), 'cookie'=> '', 'message_template'=>'', 'project_template'=>''); }
                    if ($user_arr["is_banned"]) { return array('result' => mb_convert_encoding('Забанены вы нафих', "UTF-8", "windows-1251"), 'cookie'=> '', 'message_template'=>'', 'project_template'=>''); }
                    $auth=1;
                    $uid=$user_arr["uid"];
                    $login=$user_arr["login"];
                }
                else { return array('result' => mb_convert_encoding('Не могу вас найти', "UTF-8", "windows-1251"), 'cookie'=> '', 'message_template'=>'', 'project_template'=>''); }
                $update_sess=1;
            }
            else { return array('result' => mb_convert_encoding('Ошибка авторизации по сессии. Перелогиньтесь с введением логина и пароля', "UTF-8", "windows-1251"), 'cookie'=> '', 'message_template'=>'', 'project_template'=>''); }
        }
        // все оки - логиним
        if ($auth) {
            if  ($update_sess) {
                mysql_query("UPDATE sessions
                SET session_last_refresh = now(),
                session_uid = '".$uid."',
                session_login = '".$login."',
                is_tray=1
                WHERE session_id = '".$session."'", DBMyConnect());
                return array('result' => '', 'cookie'=> $session, 'message_template'=>$message_temp, 'project_template'=>$project_temp );
            }
            else {
                //надо сессию сгенерить
                do {
                    $session=GetSession();
                    $res = mysql_query("SELECT * FROM sessions WHERE session_id = '".$session."' LIMIT 1", DBMyConnect());
                    fwrite($log,"\n".date("Y.m.d h:i:s")." "."SELECT * FROM sessions WHERE session_id = '".$session."' LIMIT 1");
                }
                while (mysql_num_rows($res));
                mysql_query("INSERT INTO sessions (
                         session_id,
                         session_uid,
                         is_tray,
                         session_login)
                         VALUES(
                         '".$session."',
                         '".$uid."',
                         1,
                         '".$login."'
                         )", DBMyConnect());
                return array('result' => '', 'cookie'=> $session, 'message_template'=>$message_temp, 'project_template'=>$project_temp );
            }
        }
    }

    /*
    function GetUserInfo($session) {
    if ($sess_ar=Session($session,$error)) {
    $mescount=0;
    $money=0;
    $res = @pg_query(DBConnect(),"select count(id) from messages where to_id='".$sess_ar["uid"]."' AND read_time='1970-01-01 00:00:00' AND to_visible=true;");
    list ($mescount)=@pg_fetch_row($res);
    $res = @pg_query(DBConnect(),"SELECT account.sum FROM account WHERE account.uid='".$sess_ar["uid"]."' LIMIT 1");
    list ($money)=@pg_fetch_row($res);
    return array('result' => '', 'money' =>$money , 'unreadmessage'=> $mescount );
    }
    else return array('result' => mb_convert_encoding('Ошибка:'.$error, "UTF-8", "windows-1251"), 'money' => 0 , 'unreadmessage'=> 0 );
    }

    формат фильтра
    $filter
    [0] - включен? = 0
    [1] - бюджет от = 0
    [2] - бюджет до = 0
    [3] - Показывать с неуказанным бюджетом = 1
    [4] - разработка сайтов = 1 (2 Разработка сайта)
    [5] - программирование = 1 (1 Программирование)
    [6] - Переводы тексты = 1 (3 Тексты, переводы)
    [7] - Дизайнарт = 1 (4 Дизайн)
    [8] - реклама-маркетинг = 1 (5 Реклама, маркетинг)
    [9] - прочее = 1 (6 Прочее)
    [10] - 0 - free
    [11] - 1 - office
    [12] - 2 - koncurs
    [13] - 3 - partnership


    get
    [0] messages
    [1] projects
    */

    function GetAllInfo($session, $lastprj, $get, $filter, $lastmes) {
        $DB = new DB('master');
        $id=array();
        $login=array();
        $uname=array();
        $usurname=array();
        $text=array();
        $picname=array();
        $thread=array();
        $pro=array();
        $online=array();
        $time=array();
        $head=array();
        $budget=array();
        $b_type=array();
        $type=array();
        $role=array();
        if ($sess_ar=Session($session,$error)) {
            $mescount=0;
            $money=0;
            $log=fopen("sql.log","a");
            $res = $DB->query("select users.hits, users.rating + 0.5*(CURRENT_DATE-reg_date) + freelancer.portf_rating as rating, account.sum from users 
LEFT JOIN account ON account.uid=users.uid LEFT JOIN freelancer ON freelancer.fid=users.uid  where users.uid=? LIMIT 1", $sess_ar["uid"]);
            //fwrite($log,"\n".date("Y.m.d h:i:s")." ".join(" ",$filter));
            list ($hits, $rating, $money)=@pg_fetch_row($res);



            if ($get[0]) { $result_mess = GetNewMessages ($sess_ar["uid"],  $id, $login, $uname, $usurname, $text, $picname, $thread, $pro, $online, $time, $head, $budget, $b_type, $type, $role, $lastmes); }
            if ($get[1]) { $result_prj = GetNewProjects ($sess_ar["uid"], $filter, $id, $login, $uname, $usurname, $text, $picname, $thread, $pro, $online, $time, $head, $budget, $b_type, $type, $role, $lastprj); }
            return array ('result'=>'', 'id'=>$id, 'login'=>$login, 'uname'=>$uname,'usurname'=> $usurname,'text'=> $text, 'picname'=>$picname, 'thread'=>$thread, 'pro'=>$pro, 'online'=>$online, 'time'=>$time, 'head'=>$head, 'budget'=>$budget, 'b_type'=>$b_type, 'money'=>$money,  'lastprj'=>$lastprj, 'rating' =>round($rating*10), 'hits'=>$hits, 'type'=>$type, 'role'=>$role, 'lastmes' =>$lastmes);
        }
        else return array('result' => mb_convert_encoding('Ошибка: '.$error, "UTF-8", "windows-1251"), 'id'=>$id, 'login'=>$login, 'uname'=>$uname,'usurname'=> $usurname,'text'=> $text, 'picname'=>$picname, 'thread'=>$thread, 'pro'=>$pro, 'online'=>$online, 'time'=>$time, 'head'=>$head, 'budget'=>$budget, 'b_type'=>$b_type, 'money'=>0,  'lastprj'=>0, 'rating' =>0, 'hits'=>0, 'type'=>$type,'role'=>$role, 'lastmes' =>$lastmes  );
    }

    function ReadMess ($session,$mess_id) {
        $DB = new DB('master');
        //return mb_convert_encoding('3223423423 ', "UTF-8", "windows-1251");
        if ($sess_ar=Session($session,$error)) {
            if ($mess_id && $mess_id[0]) {
                $me="( ";
                foreach ($mess_id as $temp) {
                    $me .= " id='".intval($temp)."' OR";
                }
                $me=preg_replace("|OR$|","",$me)." ) ";
                $sql ="UPDATE messages SET read_time=NOW() WHERE $me AND to_id=?; ";
                $DB->query($sql, $sess_ar["uid"]);
                return '';
            }
        }
        else return  mb_convert_encoding('Ошибка: '.$error, "UTF-8", "windows-1251");
    }
    function CheckVersion($version) {
        GLOBAL $lversion;
        if ($version==$lversion) {
            return '';
        }
        else {
            return $lversion;
        }
    }

    function SendMess($session, $message, $type, $id) {
        $DB = new DB('master');
        if ($sess_ar=Session($session,$error)) {
            $message=strip_tags(mb_convert_encoding($message, "windows-1251", "UTF-8"));
            $message=preg_replace("|\n|Uis","<br>",$message);
            switch ($type) {
                // ответ на личное сообщение
                case 1:
                    // get id
                    $sql = "SELECT from_id from messages WHERE id=?";
                    $to_id=$DB->val($sql, intval($id));
                    if ($to_id) {
                        // ignor
                        $sql = "SELECT target_id from ignor WHERE (user_id=? AND target_id=? )";
                        $res = $DB->query($sql, $sess_ar["uid"], $to_id);
                        if (@pg_num_rows($res) > 0  || $to_id == 103) {
                            return  mb_convert_encoding('Пользователь запретил отправлять ему сообщения', "UTF-8", "windows-1251");
                        }
                        else {
                            $user_id= $sess_ar["uid"];
                            $tar_id = $to_id;
                            $text = $message;
                            $error .= $DB->error;
                            $sql = "UPDATE messages SET from_visible=true WHERE (from_id='$user_id' AND to_id = '$tar_id');
					   UPDATE messages SET to_visible=true WHERE (to_id='$user_id' AND from_id = '$tar_id');
			     INSERT INTO messages (from_id, to_id, msg_text, attach) VALUES ('$user_id', '$tar_id', '$text', '')";
                            $res = $DB->query($sql);
                            $error .= $DB->error;
                        }
                    }
                    else return mb_convert_encoding('Ошибка', "UTF-8", "windows-1251");

                    break;
                    // ответ на проект в блоги
                case 2:
                    $sql = "SELECT blogs_msgs.id,blogs_themes.thread_id from blogs_themes LEFT JOIN blogs_msgs ON blogs_msgs.thread_id=blogs_themes.thread_id AND blogs_msgs.reply_to is NULL WHERE id_gr=?";
                    $res = $DB->query($sql, intval($id));
                    list ($id,$thread_id)=@pg_fetch_row($res);
                    $ip=$_SERVER['REMOTE_ADDR'];
                    $fid= $sess_ar["uid"];
                    $msg= $message;
                    if ($thread_id) {
                        $sql = "INSERT INTO blogs_msgs (fromuser_id, reply_to, from_ip, post_time, thread_id, msgtext, title, attach, small) VALUES ('$fid', '$id', '$ip', NOW(), '$thread_id', '$msg', '', NULL, 0);";
                        $res = $DB->query($sql);

                    }
                    else return mb_convert_encoding('Ошибка', "UTF-8", "windows-1251");
                    break;
                    // ответ на проект в личку
                case  3:
                    $sql = "SELECT user_id from projects WHERE id=?";
                    $to_id = $DB->val($sql, intval($id));
                    if ($to_id) {
                        // ignor
                        $sql = "SELECT target_id from ignor WHERE (user_id=? AND target_id=? )";
                        $res = $DB->query($sql, $sess_ar["uid"], $to_id);
                        if (@pg_num_rows($res) > 0  || $to_id == 103) {
                            return  mb_convert_encoding('Пользователь запретил отправлять ему сообщения', "UTF-8", "windows-1251");
                        }
                        else {
                            $user_id= $sess_ar["uid"];
                            $tar_id = $to_id;
                            $text = $message;
                            $error .= $DB->error;
                            $sql = "UPDATE messages SET from_visible=true WHERE (from_id='$user_id' AND to_id = '$tar_id');
					   UPDATE messages SET to_visible=true WHERE (to_id='$user_id' AND from_id = '$tar_id');
			     INSERT INTO messages (from_id, to_id, msg_text, attach) VALUES ('$user_id', '$tar_id', '$text', '')";
                            $res = $DB->query($sql);
                            $error .= $DB->error;
                        }
                    }
                    else return mb_convert_encoding('Ошибка', "UTF-8", "windows-1251");
                    break;
                    // ответ в  блоги
                case  4:
                    $sql = "SELECT user_id from projects WHERE id=?";
                    $to_id = $DB->val($sql, intval($id));
                    if ($to_id) {
                        // ignor
                        $sql = "SELECT target_id from ignor WHERE (user_id=? AND target_id=? )";
                        $res = $DB->query($sql, $sess_ar["uid"], $to_id);
                        if (@pg_num_rows($res) > 0  || $to_id == 103) {
                            return  mb_convert_encoding('Пользователь запретил отправлять ему сообщения', "UTF-8", "windows-1251");
                        }
                        else {
                            $user_id= $sess_ar["uid"];
                            $tar_id = $to_id;
                            $text = $message;
                            $error .= $DB->error;
                            $sql = "UPDATE messages SET from_visible=true WHERE (from_id='$user_id' AND to_id = '$tar_id');
					   UPDATE messages SET to_visible=true WHERE (to_id='$user_id' AND from_id = '$tar_id');
			     INSERT INTO messages (from_id, to_id, msg_text, attach) VALUES ('$user_id', '$tar_id', '$text', '')";
                            $res = $DB->query($sql);
                            $error .= $DB->error;
                        }
                    }
                    else return mb_convert_encoding('Ошибка', "UTF-8", "windows-1251");
                    break;
            }
            return '';
        }
        else return  mb_convert_encoding('Ошибка: '.$error, "UTF-8", "windows-1251");
    }
}

$server = new SoapServer("fltray.wsdl");
$server->setClass("FLTrayService");
$server->handle();
