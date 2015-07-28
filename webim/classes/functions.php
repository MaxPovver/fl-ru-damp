<?php
/* 
 * 
 * Данный файл является частью проекта Веб Мессенджер.
 * 
 * Все права защищены. (c) 2005-2009 ООО "ТОП".
 * Данное программное обеспечение и все сопутствующие материалы
 * предоставляются на условиях лицензии, доступной по адресу
 * http://webim.ru/license.html
 * 
 */
?>
<?php

require_once('common.php');
require_once('models/generic/class.mapperfactory.php');
require_once('class.visitor.php');
require_once('class.json.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/classes/smtp.php');


function pa($var, $stop = 0) {
  $array = debug_backtrace();
  $file = $array[0]['file'];
  $line = $array[0]['line'];
  $message = $file.':'.$line.'<pre>'.print_r($var, 1).'</pre>';





  if ($stop) exit;
}

function load_db_class($class_name) {
  $db_class_name = $class_name.'DB';
  $file_name = 'class.'.strtolower($class_name).'.db.php';
  include_once($file_name);
  return new $db_class_name();
}

function call_method() {
  $inputarr = func_get_args();
  $obj = array_shift($inputarr);
  $method_name = array_shift($inputarr);
  if (method_exists($obj, $method_name)) {
    $rs = call_user_func_array(array($obj, $method_name), $inputarr);
    return $rs;
  }
}

function verify_param($name, $regexp, $default = null) {
  if (isset($_REQUEST[$name])) {
    $val = $_REQUEST[$name];
    if (preg_match($regexp, $val))
    return $val;
  } else {
    if (isset($default))
    return $default;
  }
}

function smarticonv($in_enc, $out_enc, $string) {
  if ($in_enc == $out_enc) return $string;
  if (function_exists('iconv')) {
    $converted = @iconv($in_enc, $out_enc.'//TRANSLIT', $string);
    return $converted;
  }
  $in_enc = trim($in_enc);
  $out_enc = trim($out_enc);
  if (strtolower($in_enc) == "cp1251" && strtolower($out_enc) == "utf-8") {
    return strtr($string, $GLOBALS['_win1251utf8']);
  }
  if (strtolower($in_enc) == "utf-8" && strtolower($out_enc) == "cp1251") {
    return strtr($string, $GLOBALS['_utf8win1251']);
  }
  return $string;
}

function get_user_agent($user_agent) {
  $known_agents = array("chrome", "opera", "msie", "safari", "firefox", "netscape", "mozilla");
  if (is_array($known_agents)) {
    $user_agent = strtolower($user_agent);
    foreach ($known_agents as $agent) {
      if (strstr($user_agent, $agent)) {
        if (preg_match("/".$agent."[\\s\/]?(\\d+(\\.\\d+(\\.\\d+)?)?)/", $user_agent, $matches)) {
          $ver = $matches[1];
          if ($agent=='safari') {
            if (preg_match("/version\/(\\d+(\\.\\d+(\\.\\d+)?)?)/", $user_agent, $matches)) {
              $ver = $matches[1];
            } else {
              $ver = "1 or 2(build ".$ver.")";
            }
            if (preg_match("/mobile\/(\\d+(\\.\\d+(\\.\\d+)?)?)/", $user_agent, $matches)) {
              $user_agent = "iPhone ".$matches[1]."($agent $ver)";
              break;
            }
          }
          if ($agent == 'opera') {
            // Since Opera 10 version is set in the format: Version/10.00
            if (preg_match("/version\/(\\d+(\\.\\d+(\\.\\d+)?)?)/", $user_agent, $matches)) {
              $ver = $matches[1];
            }
          }
          $user_agent = ucfirst($agent)." ".$ver;
          break;
        }
      }
    }
  }
  return $user_agent;
}

function is_agent_opera95() {
  $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
  if (strstr($useragent, "opera")) {
    if (preg_match("/opera[\\s\/]?(\\d+(\\.\\d+)?)/", $useragent, $matches)) {
      $ver = $matches[1];

      if ($ver >= "9.5")
      return true;
    }
  }
  return false;
}

function get_mandatory_param($name) {
  $value = "";
  if (isset($_REQUEST[$name])) {
    $value = trim($_REQUEST[$name]);
  }
  return $value;
}

function is_valid_email($email) {
  return preg_match("/^[^@]+@[^\.]+(\.[^\.]+)*$/", $email);
}

function is_valid_name( $name ) {
    $sPattern1251 = iconv( "UTF-8", "CP1251", "a-zA-Zа-яА-ЯёЁ0-9-_ " );
    $sName1251    = iconv( "UTF-8", "CP1251", $name );
    return preg_match( '#^['. $sPattern1251 .']+$#', $sName1251 );
}

function webim_mail($toaddr, $reply_to, $subject, $body, $wrap=70) {
  $subject  = iconv(WEBIM_ENCODING, "WINDOWS-1251//IGNORE", $subject);
  $body     = iconv("UTF-8", "WINDOWS-1251", $body);
  $mail = new smtp;
  $mail->subject = $subject;
  $mail->message = $body;
  $mail->recipient = $toaddr;
  $mail->from = $reply_to;
  $mail->send('text/plain');
}

function encodeForEmailAddress($string, $encoding) {
  $pos = strpos($string, '<');
  if ($pos > 0) {
    $name = trim(substr($string, 0, $pos));
    return encodeForEmail($name, $encoding).substr($string, $pos);
  }
  return $string;
}

function encodeForEmail($string, $encoding) {
  return "=?".$encoding."?B?".base64_encode($string)."?=";
}

function div($a, $b) {
  return($a -($a % $b)) / $b;
}

function generate_get($params) {

  $servlet = $params['servlet_root'].$params['servlet'];
  $p = $params['path_vars'];
  $infix = '?';

  if (strstr($servlet, $infix) !== FALSE) {
    $infix = '&';
  }

  if(is_array($p)) {
	  foreach ($p as $k => $v) {
	    $servlet .= $infix.$k."=".urlencode($v);
	    $infix = '&';
	  }
  }

  return $servlet;
}

function get_months_list($fromtime, $totime) {
  $start = getdate($fromtime);
  $month = $start['mon'];
  $year = $start['year'];
  $result = array();
  do {
    $current = mktime(0, 0, 0, $month, 1, $year);
    $result[date("m.y", $current)] = strftime("%B, %Y", $current);
    $month++;
    if ($month > 12) {
      $month = 1;
      $year++;
    }
  } while ($current < $totime);
  return $result;
}

function get_form_date($day, $month) {
  if (preg_match('/^(\d{2}).(\d{2})$/', $month, $matches)) {
    return mktime(0, 0, 0, $matches[1], $day, $matches[2]);
  }
  return 0;
}


function get_month_selection($fromtime, $totime) {
  $start = getdate($fromtime);
  $month = $start['mon'];
  $year = $start['year'];
  $result = array();

  do {
    $current = mktime(0, 0, 0, $month, 1, $year);    
    $result[date("m.y", $current)] = strftime("%B, %Y", $current);
    $result[date("m.y", $current)] = iconv("ISO-8859-5", "UTF-8//IGNORE", $result[date("m.y", $current)]);
    $month++;
    if ($month > 12) {
      $month = 1;
      $year++;
    }
  } while ($current < $totime);


  return $result;
}

function get_popup($href, $message, $title, $wndName, $options, $realHref = false) {
  $title_string = !empty($title) ? "title=\"$title\" " : "";
  $popup = get_popup_onclick($href, $wndName, $options);
  $linkHref = $realHref ? $href : '#';
  return "<a href=\"$linkHref\" target=\"_blank\" $title_string onclick=\"$popup\">$message</a>";
}

function get_popup_onclick($href, $wndName, $options, $ainvite=false) {
  $href .=strstr($href, '?') ? '&' : '?';
  $pos = strpos($href, '/', 1);
  $href1 = substr($href, 0, $pos);
  $href2 = substr($href, $pos);

  return "if (navigator.userAgent.toLowerCase().indexOf('opera') != -1 && window.event.preventDefault) window.event.preventDefault();this.newWindow = window.open('$href1'+'$href2'+'opener='+encodeURIComponent(document.location.href) + '&openertitle='+encodeURIComponent(document.title)".($ainvite ? "+'&autoinviteid=' + autoinviteid" : "")." , '$wndName', '$options');if (this.newWindow==null)return false;this.newWindow.focus();this.newWindow.opener=window;return false";
}

function getTrackerCode($location, $theme, $isSecure=false) {
  $code = "<!-- webim visitors tracker code -->\n".
  //"<style media=\"all\" type=\"text/css\">@import \"$location/invite.css\";</style>\n".
  "<script language=\"JavaScript\" type=\"text/javascript\"><!--\n".
  "var titles = document.getElementsByTagName('title');".
  "var title = null;".
  "if(titles.length > 0 && titles[0].firstChild && titles[0].firstChild.nodeType == 3) title = titles[0].firstChild.nodeValue;". 
  "document.write('<scr' + 'ipt language=\"JavaScript\" type=\"text/javascript\" ".
  "src=\"$location/track.php?theme=$theme&event=init&url=' + escape(document.location.href) +"  .
  " '&from=' + escape(document.referrer) + '&title=' + encodeURIComponent(title) + '&issecure=$isSecure' + '\"></scr' + 'ipt>');\n".
  "//-->\n".
  "</script>\n".
  "<!-- /webim visitors tracker code -->";

  return $code;
}

function getAutoInviteCode($location, $theme) {
  $code = "<style media=\"all\" type=\"text/css\">@import \"$location/themes/$theme/css/invite.css\";</style>\n";
  $code .= "<script language=\"JavaScript\" type=\"text/javascript\"><!--\n".
  "var titles = document.getElementsByTagName('title');".
  "var url = window.location.href;".
  "url = url.substring(url.indexOf('://') + 3);".
  "url = url.substring(url.indexOf('/'));".
  "var str = url+':';" .
  "if(titles.length > 0 && titles[0].firstChild && titles[0].firstChild.nodeType == 3)". 
  "{str += titles[0].firstChild.nodeValue;}".
  "document.write('<scr' + 'ipt language=\"JavaScript\" type=\"text/javascript\" ".
  "src=\"$location/js/ainvite.php?title=' + encodeURIComponent(str) + '\"></scr' + 'ipt>');\n".
  "//-->\n".
  "</script>\n";
  
  return $code;
}



function webim_date_diff($seconds) {
  $minutes = div($seconds, 60);
  $seconds = $seconds % 60;
  if ($minutes < 60) {
    return sprintf("%02d:%02d", $minutes, $seconds);
  } else {
    $hours = div($minutes, 60);
    $minutes = $minutes % 60;
    return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
  }
}

function escape_with_cdata($text) {
  return "<![CDATA[" . str_replace("]]>", "]]>]]&gt;<![CDATA[", $text) . "]]>";
}


function get_app_location($showhost, $issecure) {
  if ($showhost) {
    $protocol = $issecure ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'] . WEBIM_ROOT;
  } else {
    return WEBIM_ROOT;
  }
}



function cleanup_visit_logs() {

  MapperFactory::getMapper("VisitSession")->cleanupVisitLogs();

  return "cleanup_visit_logs();";
}


 

function GetVisitorFromRequestAndSetCookie($setcookie = true) { // TODO consider the name
  $res = array();
  $forceSet = false;
   
   if (isset($_REQUEST['visitorname'])) {
    $res['name'] = getSecureText($_REQUEST['visitorname']);
    $forceSet = true;
  }  elseif (isset($_COOKIE[WEBIM_COOKIE_VISITOR_NAME])) {
    $res['name'] = getSecureText($_COOKIE[WEBIM_COOKIE_VISITOR_NAME]);
  } else {
    $res['name'] = Resources::Get("chat.default.visitorname");
  }

  $canChangeName = Visitor::getInstance()->canVisitorChangeName();

  if ($canChangeName) {
    if (!isset($_COOKIE[WEBIM_COOKIE_VISITOR_NAME]) || $forceSet) {
      if ($setcookie) {
        Visitor::getInstance()->setVisitorNameCookie($res['name']);
      }
    }
  }

   
  if (isset($_SESSION['WEBIM_VISITOR_ID'])) {
    $res['id'] = $_SESSION['WEBIM_VISITOR_ID'];
  } elseif (isset($_COOKIE[WEBIM_COOKIE_VISITOR_ID])) {
    $res['id'] = $_COOKIE[WEBIM_COOKIE_VISITOR_ID];
  } else {
    $res['id'] = generateVisitorId();
  }

  if(isset($_POST['captcha'])) {
      $res['captcha'] = $_POST['captcha'];
  }
  
  if (!isset($_SESSION['WEBIM_VISITOR_ID']) ) {
    $_SESSION['WEBIM_VISITOR_ID'] = $res['id'];
  }

  if (!isset($_COOKIE[WEBIM_COOKIE_VISITOR_ID])) {

    if ($setcookie) {
      setcookie(WEBIM_COOKIE_VISITOR_ID, $res['id'], time()+60*60*24*365, '/');
    }
  }

  $res['partnerref'] = isset($_COOKIE[WEBIM_COOKIE_PARTNER_REF]) ? $_COOKIE[WEBIM_COOKIE_PARTNER_REF] : null;


  return $res;
}

function generateVisitorId() {
  mt_srand();
  return str_replace(',', '.', time() + microtime()).rand(0, 99999999);
}


function IsValidKey($pkey) {

  $key = trim($pkey);
   
  
  $ver = 'P';
  
   
  $url = "htt"."p://k"."ey.w"."ebi"."m."."ru/ch"."ec"."k_k"."ey.p"."hp?ver=".urlencode($ver)."&key=".urlencode($key);
  $arr = file($url);
  if ((strpos($pkey, $ver) === 0) && ("TRUE" == strtoupper(trim($arr[0])))) {
    return true;
  } else {
    return false;
  }
}

function isValidEmail($email) {
  return preg_match("/^[^@]+@[^\.]+(\.[^\.]+)*$/", $email);
}

function SilentGetOperator() {
  
  return isset($_SESSION['operator']) ? $_SESSION['operator'] : null;
  
   
}

function subst($orig, $params) {


  $res = $orig;
  foreach ($params as $p => $value) {
    $res = preg_replace('/@'.$p.'@/mi', $value, $res);
  }
  return $res;
}

function listConvertableFiles($dir, &$arFiles = array()) {
  $convertable = array("php", "tpl", "txt");

  if($handler = opendir($dir)) {
    while (($sub = readdir($handler)) !== FALSE) {
      if ($sub != "." && $sub != "..") {
        if(is_file($dir."/".$sub)) {
          $pathParts = pathinfo($sub);
          if (isset($pathParts["extension"]) && in_array($pathParts['extension'], $convertable)) {
            $arFiles[] = $dir."/".$sub;
          }
        } elseif (is_dir($dir."/".$sub)) {
          listConvertableFiles($dir."/".$sub, $arFiles);
        }
      }
    }
    closedir($handler);
  }

  return $arFiles;
}

function getWindowNameSuffix() {
  return preg_replace('/[-:.\/]/',"_", $_SERVER['HTTP_HOST']);
}


function updateContacts($name, $email, $phone, $threadid, $visitsessionid) {
    $visitSession = MapperFactory::getMapper("VisitSession")->getById($visitsessionid);
    if ( $visitSession[ 'visitorname' ] != $name ) {
        Thread::getInstance()->RenameVisitor(array("threadid"=> $threadid), $name);
        Visitor::getInstance()->setVisitorNameCookie($name);
        MapperFactory::getMapper("VisitSession")->save(array(
          'visitsessionid' => $visitsessionid,
          'visitorname' => $name
        ));
    }
}


function enumAvailableThemes() {

  $themes = array();
  $path = getThemesRoot();  

  if ($handle = opendir($path)) {
    while (false !==($file = readdir($handle))) {
      if (!preg_match('/^\./', $file)) {
        $themes[] = $file;
      }
    }
    closedir($handle);
  }
  
  sort($themes);
  return $themes;
}


function visitorHasAccess($thread) {
  if ($thread == null) {
    return false;
  }
  
  $visitor = GetVisitorFromRequestAndSetCookie();
  $visitSession = VisitSession::GetInstance()->GetVisitSessionById($thread['visitsessionid']);
  $token = isset($_REQUEST['token']) ? $_REQUEST['token'] : null;

  $res = $thread['token'] == $token && $visitSession['visitorid'] == $visitor['id'];



  return $res;
}

function removeSpecialSymbols($text) {
  return preg_replace("/[\\x0-\\x9\\xb\\xc\\xe-\\x1f]+/", "", $text);
}


// Need separate function versions for "pro", "bitrix" and "pro-service"
function getThemesRoot() {
  return '../themes';
}


 


function uploadFile($requestFile, $dir, $fileName) {
  $valid_types = array("gif", "jpg", "png", "jpeg");

  $filename = $requestFile['name'];
  $ext = pathinfo($filename, PATHINFO_EXTENSION);
  $tmp_filename = $requestFile['tmp_name'];

  if ($requestFile['size'] > MAX_UPLOADED_FILE_SIZE) {
    return Resources::Get('errors.failed.uploading.file', array($filename, Resources::Get('errors.file.size.exceeded')));
  } elseif (!in_array(strtolower($ext), $valid_types)) {
    return Resources::Get('errors.failed.uploading.file', array($filename, Resources::Get('errors.invalid.file.type')));
  }

  $destFilename = strtolower($fileName . "." . $ext);
  $path = $dir.$destFilename;
  if (file_exists($path)) {


    if (!unlink($path)) {
      return Resources::Get('errors.failed.uploading.file', array($destFilename, Resources::Get('errors.file.remove')));
    }
  }

  if (!move_uploaded_file($requestFile['tmp_name'], $path)) {


    return Resources::Get('errors.failed.uploading.file', array($destFilename, Resources::Get('errors.file.move')));
  }
}

function constructFileNameFromUploadedFile($requestFile, $fileNamePrefix) {
    $filename = $requestFile['name'];
    $ext = substr($filename, 1 + strrpos($filename, "."));	
    return strtolower($fileNamePrefix . "." . $ext);
}

function getCurrentTime() {
  return MapperFactory::getMapper("Time")->getCurrentTime();
}



function create_basedir($file) {
	$dir = dirname($file);
	return create_dir($dir);
}

function create_dir($dir)  {
    umask(0000);
	if(!is_dir($dir) && !is_file($dir)) {
        mkdir($dir, 0777, true);	
		return true;	
	}
	
	return false;
}
function touch_online_file($file) {
	create_basedir($file);
	touch($file);
	chmod($file, 0777);
}

function get_modified_time($file) {
	$stat = @stat($file);
	if($stat) {
		return $stat[9];
	}
	
	return -1;
}

function write_threads($file, $value) {
	create_basedir($file);
	$fd = fopen($file,'w');

	if(!$fd)
		return false;
	
	if(!flock($fd, LOCK_EX)) {
		fclose($fd);
		return false;
	}
	
	if(!fwrite($fd, $value)) {
		$result = false;
	}
	
	flock($fd, LOCK_UN);
	fclose($fd);
	
	$result = true;
	return $result;
}

function is_has_threads($file) {
  // папка online в мэмкэш --------------------
  //$v = @file_get_contents($file);
  $v = $GLOBALS['mem_buff']->get( $file );
	
  return $v == "1" || $v === false;
}

function set_has_threads($file) {
    // папка online в мэмкэш --------------------
	//return write_threads($file, "1");
	return $GLOBALS['mem_buff']->set( $file, "1", 3600 );
}

function unset_has_threads($file) {
    // папка online в мэмкэш --------------------
	//return write_threads($file, "0");
	return $GLOBALS['mem_buff']->set( $file, "0", 3600 );
}

function texttranslit ($cp1251_str) {
	$table= array(
		168 => 'E', 184 => 'e', 192 => 'A', 193 => 'B', 194 => 'V', 195 => 'G', 
		196 => 'D', 197 => 'E', 198 => 'J', 199 => 'Z', 200 => 'I', 201 => 'I', 
		202 => 'K', 203 => 'L', 204 => 'M', 205 => 'N', 206 => 'O', 207 => 'P', 
		208 => 'R', 209 => 'S', 210 => 'T', 211 => 'Y', 212 => 'F', 213 => 'H', 
		214 => 'C', 215 => 'CH', 216 => 'SH', 217 => 'SH', 218 => '', 219 => 'Y', 
		220 => '', 221 => 'E', 222 => 'U', 223 => 'IA', 224 => 'a', 225 => 'b', 
		226 => 'v', 227 => 'g', 228 => 'd', 229 => 'e', 230 => 'j', 231 => 'z', 
		232 => 'i', 233 => 'i', 234 => 'k', 235 => 'l', 236 => 'm', 237 => 'n', 
		238 => 'o', 239 => 'p', 240 => 'r', 241 => 's', 242 => 't', 243 => 'y', 
		244 => 'f', 245 => 'h', 246 => 'c', 247 => 'ch', 248 => 'sh', 249 => 'sh', 
		250 => '', 251 => 'y', 252 => '', 253 => 'e', 254 => 'u', 255 => 'ia'
	);
	
	$result = $cp1251_str;
	foreach ($table as $k => $v) {
		$result = str_replace(chr($k), $v, $result);
	}
	
	return $result;
}

function parseReferrer($ref, $code) {
	if(
    	strpos($ref, "yandex.ru") !== false && 
      	preg_match("/text=([^&]+)/", $ref, $m)
    ) {
    	$query = urldecode($m[1]);
    	if(WEBIM_ENCODING != 'UTF-8') {
	  		$query = smarticonv('utf-8', 'cp1251', $query);
	  	}
	  	
      	return Resources::Get($code.'.yandex', array($query, $ref));
      	
	} else if(
    	strpos($ref, "rambler.ru") !== false && 
      	preg_match("/query=([^&]+)/", $ref, $m)
    ) {
    	$query = urldecode($m[1]);
    	if(WEBIM_ENCODING != 'UTF-8') {
	  		$query = smarticonv('utf-8', 'cp1251', $query);
	  	}
	      	
	  	return  Resources::Get($code.'.rambler',  array($query, $ref));
	  	
	} else if(
    	(strpos($ref, "google.ru") !== false || strpos($ref, "google.com") !== false) && 
      	preg_match("/q=([^&]+)/", $ref, $m)
    ) {
    	$query = urldecode($m[1]);
    	if(WEBIM_ENCODING != 'UTF-8') {
	  		$query = smarticonv('utf-8', 'cp1251', $query);
	  	}
      	
	  	return Resources::Get($code.'.google',  array($query, $ref));
      			 
	} else if(
    	(strpos($ref, "bing.com") !== false) && 
      	preg_match("/q=([^&]+)/", $ref, $m)
    ) {
        $query = urldecode($m[1]);
    	if(WEBIM_ENCODING != 'UTF-8') {
			$query = smarticonv('utf-8', 'cp1251', $query);
    	}
    	
    	return Resources::Get($code.'.bing',  array($query, $ref));
    } 
    
    return  $ref;
}

function getUsersStatsFromCookie() {
  $json = new Json(SERVICES_JSON_LOOSE_TYPE);
  $statistics = isset($_COOKIE[WEBIM_COOKIE_AUTOINVITE_STATS]) ? $json->decode($_COOKIE[WEBIM_COOKIE_AUTOINVITE_STATS]) : null;
  return $statistics;
}

function getSecureText($val) {
  $out = htmlspecialchars($val, ENT_QUOTES);
  return $out;
 }
 

/**
 * Отправка диалога в службу поддержки Free-lance.ru
 * 
 * @param  int $dept номер отдела службы поддержки
 * @param  string $name имя пользователя, который обращается в службу поддержки
 * @param  string $email email пользователя, который обращается в службу поддержки
 * @param  string $msg текст обращения в службу поддержки
 * @param  int $uid UID пользователя Free-lance.ru, если он сам обращается в службу поддержки
 * @return array id и уникальный код обрашения в службу поддержки - успех, пустой массив - провал
 */
function feedbackAdd( $dept = 0, $name = '', $email = '', $msg = '', $uid = 0 ) {
    $return  = array();
    $conf    = $GLOBALS['pg_db']['master'];    
    $connect = @pg_connect("host={$conf['host']} port={$conf['port']} dbname={$conf['name']} user={$conf['user']} password={$conf['pwd']}");
    
    if ( $connect ) {
    	mt_srand();
    	
        $uc    = md5(microtime(1).mt_rand()); // уникальный код обрашения в службу поддержки
        $uc    = substr($uc, 0, 6).substr($uc, 12, 6);
        $dept  = intval( $dept );
        $uid   = intval( $uid );
        $name  = smarticonv( 'UTF-8', 'CP1251', $name );
        $email = smarticonv( 'UTF-8', 'CP1251', $email );
        $msg   = smarticonv( 'UTF-8', 'CP1251', $msg );
        
        if ( $uid ) {
        	$name = $_SESSION['login'];
        }
        
        $sql = "INSERT INTO feedback ( uc, dept_id, user_id, user_login, email, question, request_time ) 
                VALUES ( '%s', '%s', '%s', '%s', '%s', '%s', NOW() ) RETURNING id";
        
        $sql = sprintf( 
            $sql, 
            pg_escape_string( (string) $uc ), 
            pg_escape_string( (string) $dept ), 
            pg_escape_string( (string) $uid ), 
            pg_escape_string( (string) $name ), 
            pg_escape_string( (string) $email ), 
            pg_escape_string( (string) $msg ) 
        );
        
        $res = @pg_query( $connect, $sql );
        
        if ( $res ) {
            $id = pg_fetch_assoc( $res );
			$id = @current( $id );
			
            if ( $id ) {
                $return = array( 'id' => $id, 'uc' => $uc );
                
                // Пишем статистику ображений в feedback
                $date = date('Y-m-d H:01:00');
                $sql  = "INSERT INTO stat_feedback( date, type, count) VALUES( '$date', '$dept', 1 )";
                $res  = @pg_query( $connect, $sql );
            }
        }
    }
    
    return $return;
}

?>
