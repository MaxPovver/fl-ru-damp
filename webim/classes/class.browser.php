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

require_once('functions.php');

class Browser {
  private static $knownAgents = array("chrome", "opera", "msie", "safari", "firefox", "netscape", "mozilla");

  static function GetRemoteLevel($puseragent) {
    $useragent = strtolower($puseragent);

    foreach (Browser::$knownAgents as $agent) {
      if (strstr($useragent, $agent)) {
        if (preg_match("/".$agent."[\\s\/]?(\\d+(\\.\\d+)?)/", $useragent, $matches)) {
          $ver = $matches[1];

          if (Browser::isAjaxBrowser($agent, $ver, $useragent)) {
            return "ajaxed";
          } elseif (Browser::isOldBrowser($agent, $ver)) {
            return "old";
          }

          return "simple";
        }
      }
    }
    return "simple";
  }

  private static function isAjaxBrowser($browserid, $ver, $useragent) {
    if ($browserid == "opera")
    return $ver >= 8.02;
    if ($browserid == "safari")
    return $ver >= 125;
    if ($browserid == "msie")
    return $ver >= 5.5 && !strstr($useragent, "powerpc");
    if ($browserid == "netscape")
    return $ver >= 7.1;
    if ($browserid == "mozilla")
    return $ver >= 1.4;
    if ($browserid == "firefox")
    return $ver >= 1.0;
    if ($browserid == "chrome")
    return $ver >= 0.1;

    return false;
  }

  private static function isOldBrowser($browserid, $ver) {
    if ($browserid == "opera")
    return $ver < 7.0;
    if ($browserid == "msie")
    return $ver < 5.0;

    return false;
  }

  static function GetBrowserAndVersion($userAgent) {
    $userAgent = strtolower($userAgent);
    foreach (Browser::$knownAgents as $agent) {
      if (strstr($userAgent, $agent)) {
        if (preg_match("/".$agent."[\\s\/]?(\\d+(\\.\\d+(\\.\\d+)?)?)/", $userAgent, $matches)) {
          $ver = $matches[1];
          if ($agent=='safari') {
            if (preg_match("/version\/(\\d+(\\.\\d+(\\.\\d+)?)?)/", $userAgent, $matches)) {
              $ver = $matches[1];
            } else {
              $ver = "1 or 2(build ".$ver.")";
            }
            if (preg_match("/mobile\/(\\d+(\\.\\d+(\\.\\d+)?)?)/", $userAgent, $matches)) {
              $userAgent = "iPhone ".$matches[1]."($agent $ver)";
              break;
            }
          }

          $userAgent = ucfirst($agent)." ".$ver;
          break;
        }
      }
    }
    return $userAgent;
  }

  static function SendNoCache() {
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: no-cache");
  }

  static function SendHtmlHeaders() {
    Browser::SendNoCache();
    header("Content-type: text/html; charset=".BROWSER_CHARSET);
  }


  static function SendXmlHeaders() {
    Browser::SendNoCache();
    header("Content-type: text/xml; charset=".BROWSER_CHARSET);
    echo "<"."?xml version=\"1.0\" encoding=\"".BROWSER_CHARSET."\"?".">";
  }

  static function ChangeLocation($url, $params = null) {
    if (!empty($get)) {
      $url = $url.(strstr($url, '?') ? '&': '?').'lang='.WEBIM_CURRENT_LOCALE;

      $chunks = array();
      foreach ($get as $key => $value) {
        $chunks[] = $key . '=' . urlencode($value);
      }
      $url .= '&'.join('&', $chunks);
    }
    header("Location: ".$url);
    if ($force) exit;
  }


  static function displayAjaxError($text) {
    $message = Resources::Get('agent.not_logged_in');
    $message = Browser::AddCdata($message);
    echo "<error type=\"1\"><descr>".$message."</descr></error>";
  }
  

  static function AddCdata($text) {
    return "<![CDATA[" . str_replace("]]>", "]]>]]&gt;<![CDATA[", $text) . "]]>";
  }

  static function GetExtAddr() {
   	if (USE_X_FORWARDED_FOR && isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != $_SERVER['REMOTE_ADDR']) {
   		return $_SERVER['HTTP_X_FORWARDED_FOR'];
   	}

    return $_SERVER['REMOTE_ADDR'];
  }

  static function getCurrentTheme() {
    $theme = verify_param("theme", "/^\w+$/", "default");
    return $theme;
  }
  
  public static function getOpener() {
  if (isset($_REQUEST['opener'])) {
    $referer = $_REQUEST['opener'];
  } elseif (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
  } else {
    $referer = null;
  }
  return $referer;
}


}
?>