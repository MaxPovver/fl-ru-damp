<?

$rates = array
(
0 => 1,
1 => 1.27,
2 => 0.0385
);

function str_ago_pub($from_date){
    switch (date("z") - date("z",$from_date) + (date("Y") - date("Y",$from_date))*365){
        case 0 : $out = "Сегодня"; break;
        case 1 : $out = "Вчера"; break;
        default : $out = ""; break;
    }
    return $out;
}


function LenghtFormatEx($string, $length, $etc = "...",
$break_words = false)
{
    if ($length == 0)
    return '';
    if (strlen($string) >= $length) {
        $lnt = $length - strlen($etc);
        if (!$break_words)
        $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $lnt));

        return substr($string, 0, $length).$etc;
    } else
    return $string;
}

function CurToChar($val, $ind){
    switch ($ind) {
        case 1: $out = $val." &euro;"; break;
        case 2: $out = $val." руб"; break;
        case 3: $out = $val." FM"; break;
        default: $out = "$".$val;
    }
    return $out;
}

function textWrap($body, $size, $separator="\\r\\n" ) {
    foreach(explode(" ", strip_tags($body)) as $key => $line) {
        if (strlen($line) > $size) {
            $body = preg_replace("/([^\s]{".$size."})/","$1 ",$body);
        }
    }
    //$body = preg_replace("|(\S{".$size."})|","$1 ",$body);
    return $body;
}

function reformat($input, $max_word_size = 100, $cut = 0, $nolink=0){
    $pat = array("/([^(http:\/\/)])(www\.[^\040\<\;\r\(\)\.\,]*)([\;\040\<\r\(\)\,]?)/","/\040\-\040/","/\040\--\040/","/^(www\.[^\040\<\;\r\(\)\.\,]*)([\;\040\<\r\(\)\,]?)/","/\&nbsp\;/","/\n/");
    $repl = array("$1http://$2$3", " &#150; ", " &mdash; ", "http://$1$2", " ", "<br>");
    $out = preg_replace($pat,$repl,$input);
    if (!$nolink) { $out = preg_replace_callback("/(http:\/\/([^\040\<\;\r\(\)\,]*)([\040\<\r\;\(\)\.\,]?))/",'reformat_callback', $out); }
    if ($cut)
    $out = preg_replace("'<cut[^>]*?>.*?</cut>'si","",$out);
    else
    $out = preg_replace("'<[\/\!]*?cut[^>]*?>'si","",$out);
    if ($max_word_size) $out = textWrap($out, $max_word_size," ");
    return $out;
}

  function reformat_callback($matches){
      return "<a href=\"http://".$matches[2]."\" target=\"_blank\" rel=\"nofollow\" title=\"http://".$matches[2]."\">".LenghtFormatEx($matches[2], 25, "..", true)."</a>".$matches[3]."";
  }
  
    
function DBConnect(){
    if (!$GLOBALS['connection'])
    $GLOBALS['connection'] = pg_connect("host=".$GLOBALS['dbhost']." port=".$GLOBALS['dbport']." dbname=".$GLOBALS['dbname']." user=".$GLOBALS['dbuser']." password=".$GLOBALS['dbpwd']) or die ("could not connect to base");
    return $GLOBALS['connection'];
}

function DBMyConnect(){
    $link = mysql_connect($GLOBALS['dbmyhost'], $GLOBALS['dbmyuser'], $GLOBALS['dbmypwd']) or $error="Could not connect to database<br>";
    if (!mysql_select_db($GLOBALS['dbmyname'],$link)) return false;
    return $link;
}

function getActivityByLogin($login){
    if (!$login) return 0;
    $res = mysql_query("SELECT session_last_refresh AS d FROM sessions
                           WHERE session_login = '".$login."'", DBMyConnect());
    if($row = mysql_fetch_assoc($res))
    return $row['d'];
    return 0;
}

function GetSession($length=32) {
    $str='1234567890abcdefghijklmnopqrstuvwxyz';
    $ret_str='';
    mt_srand();
    for ($i=1;$i<=$length;$i++) {
        $ret_str .= substr($str,mt_rand(0,35),1);
    }
    return $ret_str;
}

function ago_pub($from_date, $format = "ynjGi"){
    $date_diff = ($from_date < time())?(time() - $from_date - (3*60*60)):($from_date - time() - (3*60*60));
    //года
    if (($val = date("y", $date_diff)-70) && strpos($format, "y") !== false){
        $mod1 = $val%10;
        $mod2 = $val%100;
        if ($mod1 == 1 && ($mod2 < 10 || $mod2 > 20)) $out[] = intval($val)." год";
        elseif ($mod1 < 5 && ($mod2 < 10 || $mod2 > 20)) $out[] = intval($val)." года";
        else $out[] = $val." лет";
    }
    //месяцы
    if (($val = date("n", $date_diff)-1) && strpos($format, "n") !== false){
        $mod1 = $val%10;
        if ($mod1 == 1 && ($val < 10 || $val > 20)) $out[] = intval($val)." месяц";
        elseif ($mod1 < 5 && ($val < 10 || $val > 20)) $out[] = intval($val)." месяца";
        else $out[] = $val." месяцев";
    }
    //дни
    if (($val = date("j", $date_diff)-1) && strpos($format, "j") !== false){
        $mod1 = $val%10;
        if ($mod1 == 1 && ($val < 10 || $val > 20)) $out[] = intval($val)." день";
        elseif ($mod1 < 5 && ($val < 10 || $val > 20)) $out[] = intval($val)." дня";
        else $out[] = $val." дней";
    }
    //часы
    if (($val = date("G", $date_diff)) && strpos($format, "G") !== false){
        $mod1 = $val%10;
        if ($mod1 == 1 && ($val < 10 || $val > 20)) $out[] = intval($val)." час";
        elseif ($mod1 < 5 && ($val < 10 || $val > 20)) $out[] = intval($val)." часа";
        else $out[] = $val." часов";
    }
    //минуты

    if (($val = date("i", $date_diff)) && strpos($format, "i") !== false){
        $mod1 = $val%10;
        if ($val<1)  $out[] = "менее минуты";
        elseif ($val==1) $out[] = intval($val)." минуту";
        elseif ($mod1 == 1 && ($val < 10 || $val > 20)) $out[] = intval($val)." минута";
        elseif ($mod1 < 5 && ($val < 10 || $val > 20)) $out[] = intval($val)." минуты";
        else $out[] = $val." минут";
    }
    if ($out) $ret = implode(" ", $out);
    else  $ret = "";
    return $ret;
}

function strtotimeEx($strInput) {
    $pos = strpos($strInput,".");
    if ($pos !== false)
    $strInput = substr_replace($strInput, "", $pos);

    $iVal = -1;
    for ($i=1900; $i<=1969; $i++) {
        # Check for this year string in date
        $strYear = (string)$i;
        if (!(strpos($strInput, $strYear)===false)) {
            $replYear = $strYear;
            $yearSkew = 1970 - $i;
            $strInput = str_replace($strYear, "1970", $strInput);
        };
    };
    if ($strInput) $iVal = strtotime($strInput); else $iVal = strtotime("this");
    if ($yearSkew > 0) {
        $numSecs = (60 * 60 * 24 * 365 * $yearSkew);
        $iVal = $iVal - $numSecs;
        $numLeapYears = 0;        # Work out number of leap years in period
        //print $replYear.$yearSkew;
        for ($j=$replYear; $j<=1970; $j++) {
            $thisYear = $j;
            $isLeapYear = false;
            # Is div by 4?
            if (($thisYear % 4) == 0) {
                $isLeapYear = true;
            };
            # Is div by 100?
            if (($thisYear % 100) == 0) {
                $isLeapYear = false;
            };
            # Is div by 1000?
            if (($thisYear % 1000) == 0) {
                $isLeapYear = true;
            };
            if ($isLeapYear == true) {
                if ($replYear == $j && date("n",$iVal) > 2) $numLeapYears = $numLeapYears-1;
                $numLeapYears++;
            };

        };
        //print " " . $numLeapYears; exit;
        $iVal = $iVal - (60 * 60 * 24 * $numLeapYears);//+ 60 * 60 * 24 ;
    };
    return($iVal);
};


function view_online_status($login, $full=false){
    if ($login)
    $last_ref = getActivityByLogin($login);
    if ($last_ref){
        $ago = ago_pub(strtotimeEx($last_ref));
        if (intval($ago) == 0) $ago = "менее минуты";
        return "<img src=\"".$GLOBALS["host"]."/images/dot_active.gif\" style=\"width:10px;height:10px;padding-right:3px;vertical-align:middle;\" alt=\"Последняя активность была ".$ago." назад\" title=\"Последняя активность была ".$ago." назад\" border=\"0\">" . ($full ? "<span class='u_active'>На сайте</span>" : "");
    }
    return "<img src=\"".$GLOBALS["host"]."/images/dot_inactive.gif\" style=\"width:8px;height:9px;padding-right:3px;vertical-align:middle;\" alt=\"Нет на сайте\" title=\"Нет на сайте\" border=\"0\">" . ($full ? "<span class='u_inactive'>Нет на сайте</span>" : "");
}

function Session ($session, &$return) {
    $DB = new DB('master');
    $res=mysql_query("SELECT session_uid FROM sessions WHERE session_id = '".trim($session)."' LIMIT 1", DBMyConnect());
    if (mysql_num_rows($res)) {
        list($uid)=mysql_fetch_row($res);
        $res_pass = $DB->query("SELECT uid, login, is_banned, is_pro, active FROM users WHERE uid=?  LIMIT 1", $uid);
        if (pg_numrows($res_pass) > 0){
            if (pg_numrows($res_pass)){
                // ок
                $user_arr=pg_fetch_assoc($res_pass);
                if ($user_arr["is_pro"]=="f") { $return = 'Доступно только для PRO'; return 0; }
                if ($user_arr["active"]=="f") { $return = 'А активировать аккаунт Пушкин будет?';  return 0; }
                if ($user_arr["is_banned"]) { $return ='Забанены вы нафих'; return 0; }
                $uid=$user_arr["uid"];
                $login=$user_arr["login"];
                $res = mysql_query("SELECT * FROM sessions WHERE session_data = 'TrayPrj' AND session_login='".$login."'", DBMyConnect());
                if (@mysql_num_rows($res)>1) {
                    $res = mysql_query("DELETE FROM sessions WHERE session_data = 'TrayPrj' AND session_login='".$login."'", DBMyConnect());
                    $return =  'Две сессии. Возможно еще кто-то под Вашим именем в сети. Перелогиньтесь с введением логина и пароля';  return 0;
                }
                mysql_query("UPDATE sessions
                SET session_last_refresh = now(),
                session_uid = '".$uid."',
                session_login = '".$login."'
                WHERE session_id = '".trim($session)."'", DBMyConnect());

                return array("uid" => $uid, "login" => $login);
            }

        }
        else { $return = 'Не могу вас найти'; return 0; }
    }
    else { $return = 'Нет такой сессии. Перелогиньтесь с введением логина и пароля'; return 0; }
}

function GetNewMessages ($uid, &$id, &$login, &$uname, &$usurname, &$text, &$picname, &$thread, &$pro, &$online, &$time, &$head, &$budget, &$b_type, &$type, &$role, &$lastmess, $maxmess=100) {
    $DB = new DB('master');
    $log=fopen("sql.log","a");
    $res = $DB->query("SELECT
 messages.id, messages.from_id, messages.msg_text, users.login, users.uname, users.usurname, users.role, users.photo, users.is_pro, messages.post_time 
 FROM messages, users 
 WHERE messages.to_id='".$uid."'  AND messages.read_time='1970-01-01 00:00:00' AND messages.to_visible=true AND users.uid=messages.from_id AND messages.id>".$lastmess." ORDER BY messages.post_time DESC LIMIT ".$maxmess);  

    fwrite($log,"\n".date("Y.m.d h:i:s")." "."SELECT
 messages.id, messages.from_id, messages.msg_text, users.login, users.uname, users.usurname, users.role, users.photo, users.is_pro, messages.post_time 
 FROM messages, users 
 WHERE messages.to_id='".$uid."'  AND messages.read_time='1970-01-01 00:00:00' AND messages.to_visible=true AND users.uid=messages.from_id AND messages.id>".$lastmess." ORDER BY messages.post_time DESC LIMIT ".$maxmess); 
    $mess = array();
    $mess_id = array();
    $ok=0;
    while (list ($messages_id, $messages_from_id, $messages_msg_text, $users_login, $users_uname, $users_usurname, $users_role, $users_photo, $users_is_pro ,$messages_post_time)=@pg_fetch_row($res)) {
        $ok=1;
        if ($users_is_pro=="t") { $u_pro='<a href="'.$GLOBALS["host"].'/payed/"><img src="'.$GLOBALS["host"].'/images/icons/f-pro.png" alt="" class="pro"></a>';}
        else { $u_pro='';  }
        //"2007-12-14 13:34:15.16763"
        array_push($id,$messages_id);
        array_push($login,$users_login);
        array_push($uname,mb_convert_encoding($users_uname, "UTF-8", "windows-1251"));
        array_push( $usurname,mb_convert_encoding($users_usurname, "UTF-8", "windows-1251"));
        array_push($text,mb_convert_encoding(preg_replace("|[\n\r\t]|si"," ",strip_tags(reformat(substr($messages_msg_text,0,1000),25).(strlen($messages_msg_text)> 1000 ? "..." : ""))), "UTF-8", "windows-1251"));
        array_push($picname,($users_photo ? $users_photo : "no_foto_b.png" ));
        array_push($thread,"");
        array_push($pro,$u_pro);
        array_push($online,mb_convert_encoding(view_online_status($users_login), "UTF-8", "windows-1251"));
        array_push($time,substr($messages_post_time,17,2).substr($messages_post_time,14,2).substr($messages_post_time,11,2).substr($messages_post_time,8,2).substr($messages_post_time,5,2).substr($messages_post_time,0,4));
        array_push($head,'');
        array_push($budget,'');
        array_push($b_type,0);
        array_push($type,1);
        array_push($role,$users_role);
        /*
        $res_count = @pg_query(DBConnect(),"SELECT count(messages.id) FROM messages WHERE messages.to_id='".$uid."'  AND messages.from_id='".$messages_from_id."' ");
        list ($messages_count)=@pg_fetch_row($res_count);
        $mess_id[]=$messages_id;
        $tmp_mess ='<table border="0" cellpadding="0" cellspacing="0" >
        <tbody><tr>
        <td>
        <table border="0" cellpadding="0" cellspacing="0">
        <tbody><tr valign="top">
        <td align="center" width="70"><a href="'.$GLOBALS["host"].'/users/'.$users_login.'/" class="frlname11">';

        if ($users_photo) $tmp_mess .='<img src="'.$GLOBALS["host"].'/users/'.$users_login.'/foto/'.$users_photo.'" alt="'.$users_login.'" border="0" height="50" width="50">'; else $tmp_mess .='<img src="'.$GLOBALS["host"].'/images/no_foto.gif" alt="'.$users_login.'" border="0" height="50" width="50">';


        $tmp_mess .='</a></td><td class="frlname11">';
        if ($users_is_pro=="t") $tmp_mess .='<a href="'.$GLOBALS["host"].'/payed/"><img src="'.$GLOBALS["host"].'/images/icons/f-pro.png" alt="" class="pro"></a>';
        $tmp_mess .=view_online_status($users_login);
        $tmp_mess .='<a href="'.$GLOBALS["host"].'/users/'.$users_login.'" class="frlname11">'.$users_uname.' '.$users_usurname.'</a> [<a href="'.$GLOBALS["host"].'/users/'.$users_login.'" class="frlname11">'.$users_login.'</a>]<br>
        <font class="cl9">Всего сообщений: '.$messages_count.'</font><br>
        <a  target="_blank" href="'.$GLOBALS["host"].'/contacts/?from='.$users_login.'#form" class="blue">Написать новое сообщение</a><br>
        </td>

        </tr>
        </tbody></table>';

        $tmp_mess .='<table border="0" cellpadding="0" cellspacing="0" width="100%">

        <tbody><tr valign="top">
        <td align="center" width="20">&nbsp;</td>
        <td style="padding-right: 20px;">';
        $tmp_mess .='<a target="_blank" href="'.$GLOBALS["host"].'/contacts/?from='.$users_login.'" class="c_grey">'.reformat(substr($messages_msg_text,0,1000),25).(strlen($messages_msg_text)> 1000 ? "..." : "").'</a>';
        $tmp_mess .='</td>
        </tr>
        </table><br>';
        $mess[]=mb_convert_encoding($tmp_mess, "UTF-8", "windows-1251");

        */
        if ($messages_id>$lastmess) $lastmess=$messages_id;
    }
    if (!$ok)  return 0;
    else return 1;
}
/*
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

*/
function GetNewProjects ($uid, $filter,  &$id, &$login, &$uname, &$usurname, &$text, &$picname, &$thread, &$pro, &$online, &$time, &$head, &$budget, &$b_type, &$type, &$role, &$lastprj=0, $maxprj=100) {
    $DB = new DB('master');
    $log=fopen("sql.log","a");
    $prjs = array();
    $prjs_id = array();
    $filter_str='';
    if ($filter[0]) {
        if (intval($filter[1])>0) {
            $filter_str .= " projects.currency >=".intval($filter[1])." AND";
        }
        if (intval($filter[2])>0) {
            $filter_str .= " projects.currency <=".intval($filter[2])." AND";
        }
        $filter_str = preg_replace("|AND$|","",$filter_str);

        if ($filter[3]) {
            if (intval($filter[1])>0 || intval($filter[2])>0) {
                $filter_str ="(".preg_replace("|AND$|","",$filter_str).") OR ";
            }
            else { $filter_str=''; }
            $filter_str = " (".$filter_str." projects.currency =0) AND";
        }




        $kind_filter = "";

        if (!$filter[4] ) {
            $kind_filter .= " projects.category<>2 AND";
        }
        if (!$filter[5] ) {
            $kind_filter .= " projects.category<>1 AND";
        }
        if (!$filter[6] ) {
            $kind_filter .= " projects.category<>3 AND";
        }
        if (!$filter[7] ) {
            $kind_filter .= " projects.category<>4 AND";
        }
        if (!$filter[8] ) {
            $kind_filter .= " projects.category<>5 AND";
        }
        if (!$filter[9] ) {
            $kind_filter .= " projects.category<>6 AND";
        }

        //$kind_filter = preg_replace("|AND$|","",$kind_filter);

        if (strlen($kind_filter)>2) { $filter_str .= " ".$kind_filter." "; }


        $category_filter = "";

        if (!$filter[10] ) {
            $category_filter .= " projects.kind<>0 AND";
        }

        if (!$filter[11] ) {
            $category_filter .= " projects.kind<>1 AND";
        }

        if (!$filter[12] ) {
            $category_filter .= " projects.kind<>2 AND";
        }

        if (!$filter[13] ) {
            $category_filter .= " projects.kind<>3 AND";
        }

        if (strlen($category_filter)>2) { $filter_str .= " ".$category_filter." "; }

    }

    $res = $DB->query("SELECT projects.*, blogs_themes.thread_id, (messages_cnt -1) as comm_count, users.login, users.uname, users.usurname, users.role as role FROM projects LEFT JOIN blogs_themes ON projects.id = blogs_themes.id_gr LEFT JOIN users ON users.uid=projects.user_id WHERE ".($filter_str ? $filter_str : "")."  id>".$lastprj." AND closed=false ORDER BY post_date DESC LIMIT ".$maxprj.";");


    fwrite($log,"\n".date("Y.m.d h:i:s")." "."SELECT projects.*, blogs_themes.thread_id, (messages_cnt -1) as comm_count, users.login, users.uname, users.usurname, users.role as role FROM projects LEFT JOIN blogs_themes ON projects.id = blogs_themes.id_gr LEFT JOIN users ON users.uid=projects.user_id WHERE ".($filter_str ? $filter_str : "")."  id>".$lastprj." AND closed=false ORDER BY post_date DESC LIMIT ".$maxprj.";");


    if (@pg_num_rows($res)) {
        while ($prj_arr=pg_fetch_assoc($res)) {

            // if ($users_is_pro=="t") { $u_pro='<a href="'.$GLOBALS["host"].'/payed/"><img src="'.$GLOBALS["host"].'/images/icons/f-pro.png" alt="" class="pro"></a>';}
            // else { $u_pro='';  }
            //"2007-12-14 13:34:15.16763"
            array_push($id,$prj_arr["id"]);
            array_push($login,$prj_arr["login"]);
            array_push($uname,mb_convert_encoding($prj_arr["uname"], "UTF-8", "windows-1251"));
            array_push( $usurname,mb_convert_encoding($prj_arr["usurname"], "UTF-8", "windows-1251"));
            array_push($text,mb_convert_encoding(preg_replace("|[\n\r\t]|si"," ",strip_tags(reformat(substr($prj_arr["descr"],0,1000),25).(strlen($prj_arr["descr"])> 1000 ? "..." : ""))), "UTF-8", "windows-1251"));
            array_push($picname, '');
            array_push($thread,$prj_arr["thread_id"]);
            array_push($pro,'');
            array_push($online,mb_convert_encoding(view_online_status($prj_arr["login"]), "UTF-8", "windows-1251"));
            array_push($time,substr($prj_arr["post_date"],17,2) .substr($prj_arr["post_date"],14,2). substr($prj_arr["post_date"],11,2). substr($prj_arr["post_date"],8,2). substr($prj_arr["post_date"],5,2). substr($prj_arr["post_date"],0,4));
            array_push($head,mb_convert_encoding(preg_replace("|[\n\r\t]|si"," ",strip_tags(reformat(substr($prj_arr["name"],0,1000),25).(strlen($prj_arr["name"])> 1000 ? "..." : ""))), "UTF-8", "windows-1251"));
            array_push($budget,$prj_arr["cost"]);
            array_push($b_type,$prj_arr["currency"]);
            array_push($type,2);
            array_push($role,$prj_arr["role"]);
            /*
            $prjs_id[]=$prj_arr["id"];
            $prj_desc=preg_replace("|[\n\r]|is", "",reformat(strip_tags(LenghtFormatEx($prj_arr['descr'], 300),"<br>"), 96, 1));
            $prjs[]=mb_convert_encoding('<div class="prj_bold">'.preg_replace("|[\n\r]|is", "",reformat($prj_arr["name"],60,0,1)).($prj_arr["cost"] ? '<br>Бюджет: '.CurToChar($prj_arr['cost'], $prj_arr['currency']) : "").'</div> <a target="_blank" class="prj_a" href="'.$GLOBALS["host"].'/blogs/view.php?tr='.$prj_arr["thread_id"].'">'.substr($prj_desc,0,1000).(strlen($prj_desc)> 1000 ? "..." : "").'</a><br><a target="_blank" class="user_blue" href="'.$GLOBALS["host"].'/users/'.$prj_arr["login"].'/">'.$prj_arr["uname"].' '.$prj_arr["usurname"].' ['.$prj_arr["login"].']</a> <div  class="cl9"> Прошло '.ago_pub(strtotimeEx($prj_arr['post_date'])).'</div> ', "UTF-8", "windows-1251");

            */
            if ($prj_arr["id"]>$lastprj) { $lastprj=$prj_arr["id"]; }
        }
        return 1;
    }
    else {
        return 0;
    }

}


