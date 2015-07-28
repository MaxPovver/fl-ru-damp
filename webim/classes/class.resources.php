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

class Resources {
  private static $AVAILABLE_LOCALES = 'en,ru';

  private static $threadStateKeys = array(
    STATE_QUEUE=> "chat.thread.state_wait",
    STATE_QUEUE_EXACT_OPERATOR => "chat.thread.state_wait_for_exact_agent",
    STATE_CHATTING_OPERATOR_BROWSER_CLOSED_REFRESHED => "chat.thread.state_wait_for_another_agent",
    STATE_CHATTING => "chat.thread.state_chatting_with_agent",
    STATE_CLOSED => "chat.thread.state_closed",
    STATE_LOADING => "chat.thread.state_loading",
    STATE_LOADING_FOR_EXACT_OPERATOR => "chat.thread.state_loading",
    STATE_INVITE => "chat.thread.state_invite",
    STATE_CHAT_VISITOR_BROWSER_CLOSED_REFRESHED => "chat.thread.state_chatting_with_agent",
    STATE_CHATTING_CLOSED_REFRESHED => "chat.thread.state_chatting_with_agent",
    STATE_REDIRECTED => "chat.thread.redirected"
  );

  private function __construct() {
  }

  private function __clone() {
  }

  private static function getAll() {
    static $res = null;
    if (isset($res)) {
      return $res;
    }
    $resources_ru = Resources::readResources('ru');
    $resources_en = Resources::readResources('en');
    //    $var_name = 'resources_'.$locale;
    //    $current_res = $$var_name;

    $res = array('en' => $resources_en,'ru' => $resources_ru);

    return $res;
  }

  static function GetCurrentSet() {
    return Resources::getAll(Resources::getCurrentLocale());
  }
  
  private static function readResources($locale) {
    $hash = array();
    Resources::readResourceFile(dirname(__FILE__)."/../locales/$locale/properties.txt", $hash);

    $fileName = $_SERVER['DOCUMENT_ROOT'].WEBIM_ROOT.'/themes/'.Browser::getCurrentTheme().'/locales/'.$locale.'/resources.txt';
    if (is_file($fileName)) {
      Resources::readResourceFile($fileName, $hash);
    }
    return $hash;
  }

  private static function readResourceFile($fileName, &$hash) {
    $fp = fopen($fileName, "r");
    if ($fp) {
      while (!feof($fp)) {
        $line = fgets($fp, 4096);
        $line = str_replace("\n", '', $line);
        $line = str_replace("\r", '', $line);
        $keyval = split("=", $line, 2);
        if (count($keyval) == 2) {
          $key = $keyval[0];
          $value = $keyval[1];
          $hash[$key] = str_replace("\\n", "\n", $value);
        }
      }
    }
    fclose($fp);
  }

  static function Get($key, $params = array(), $locale = null) {
    if (empty($locale)) {
      $locale = Resources::getCurrentLocale();
    }
    $resources = Resources::getAll();
    $current = $resources[$locale];
    $res = Resources::getResource($current, $key, $params);
    if (isset($res)) {
      return $res;
    }





    






    



    return "!".$key;

  }

  private static function getResource($resources, $key, $params) {
    if (isset($resources[$key])) {
      return Resources::fillPlaceholders($resources[$key], $params);
    }

  }

  private static function fillPlaceholders($str, $params = null) {
    if (empty($params)) {
      return $str;
    }
    if (!is_array($params)) {
      $params = array($params);
    }

    $patterns = array();
    $replacements = array();

    foreach ($params as $key => $value) {
      $patterns[] = "{" . $key . "}";
      $replacements[] = $value;
    }
    return str_replace($patterns, $replacements, $str);
  }

  static function compareEncodings($e1, $e2) {
    $_e1 = str_replace('-', '', strtolower($e1));
    $_e2 = str_replace('-', '', strtolower($e2));

    return $_e1 == $_e2;
  }

  static function ConvertWebIMToEncoding($encoding, $dir) {
    if (Resources::compareEncodings(WEBIM_ORIGINAL_ENCODING, $encoding)) {
      return null;
    }

    $resources = listConvertableFiles($dir);

    foreach ($resources as $item) {
      $content = file_get_contents($item);
      $w_content = smarticonv(WEBIM_ORIGINAL_ENCODING, $encoding, $content);
      $result = file_put_contents($item, $w_content);

      if ($result === FALSE) {
        return Resources::Get("errors.write.failed", array($item));
      }
    }

    return null;
  }

  static function IsLocaleAvailable($locale) {
    $res = in_array($locale, Resources::GetAvailableLocales());


    return $res;
  }

  static function GetAvailableLocales() {
    static $arr;
    $arr = explode(',', Resources::$AVAILABLE_LOCALES);

    return $arr;
  }

  static function SetLocaleLanguage() {
    if (Resources::getCurrentLocale() == 'ru') {
      $locale = 'ru_RU';
    } else {
      $locale = 'en_EN';
    }


    if (setlocale(LC_ALL, $locale.'.'.(defined('WEBIM_ENCODING') ? WEBIM_ENCODING : 'UTF-8')) === false) {
      setlocale(LC_ALL, Resources::getCurrentLocale());
    }
  }

  public static function getCurrentLocale() {
     

    
    $lang = DEFAULT_LOCALE;
    // check get
    if (!empty($_REQUEST['lang']) && Resources::IsLocaleAvailable($_REQUEST['lang'])) {

      $lang = $_REQUEST['lang'];
      $_SESSION['lang'] = $_REQUEST['lang'];
      setcookie('WEBIM_LOCALE', $_REQUEST['lang'], time()+60*60*24*1000, WEBIM_ROOT . "/");
    } elseif (isset($_SESSION['lang']) && Resources::IsLocaleAvailable($_SESSION['lang'])) { // check session
      $lang = $_SESSION['lang'];
    } elseif (isset($_COOKIE['WEBIM_LOCALE']) && Resources::IsLocaleAvailable($_COOKIE['WEBIM_LOCALE'])) { // check cookie
      $lang = $_COOKIE['WEBIM_LOCALE'];
    } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) { // check accept language
      $requested_langs = explode(",", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
      foreach ($requested_langs as $requested_lang) {
        if (strlen($requested_lang) > 2) {
          $requested_lang = substr($requested_lang, 0, 2);
        }
        if (Resources::IsLocaleAvailable($requested_lang)) {
          $lang = $requested_lang;
          break;
        }
      }

    } elseif (Resources::IsLocaleAvailable(DEFAULT_LOCALE)) { // check the default locale
      $lang = DEFAULT_LOCALE;
    } else { // can't find lang
      $lang = 'ru';
    }

    return $lang;
    
  }

  static function GetStateName($state) {
    $key = self::$threadStateKeys[$state];
    return self::Get($key);
  }


}
?>