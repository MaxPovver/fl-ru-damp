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

require_once (dirname(__FILE__) . '/models/generic/class.mapperfactory.php');
require_once (dirname(__FILE__) . '/class.resources.php');




  class Button {
    public static function getParameters($lang = NULL) {
      if ($lang === NULL) {
        $lang = Resources::getCurrentLocale();
      }

      $themes = enumAvailableThemes();

      $images = Button::enumAvailableImages($lang);


      $departments = MapperFactory::getMapper("Department")->enumDepartments(Resources::getCurrentLocale());

      $departmentsParam = array(array('key' => '', 'value' => Resources::Get('page.gen.anydepartment')));



      foreach ($departments as $d) {
        $item = array();
        $item['key'] = $d['departmentkey'];
        $item['value'] = $d['departmentname'];
        $departmentsParam[] = $item;
      }


      $locales = getAvailableLocalesForChat();
      $localesParam = array(array('key' => 'N', 'value' => Resources::Get('page.gen.autolocale')));
//      $localesParam = array();
      foreach ($locales as $l) {
        $item = array();
        $item['key'] = $l['localeid'];
        $item['value'] = $l['localename'];
        $localesParam[] = $item;
      }

      $params =  array(
        "button" => array(
          "name_key" => 'page.gen.choose_image',
          "type" => "list",
          "values" => Button::listToArray($images),
          "default" => "webim"
        ),
        "theme" => array(
          "name_key" => 'page.gen.choose_theme',
          "type" => "list",
          "values" => Button::listToArray($themes),
          "default" => "default"
        ),
         
         
        "include_host_url" => Array(
          "name_key" => 'page.gen.include_site_name',
          "type" => "checkbox",
          "default" => "N",
        ),
        "secure" => Array(
          "name_key" => 'page.gen.secure_links',
          "type" => "checkbox",
          "default" => "N",
        ),
         
        "add_track_code" => Array(
          "name_key" => 'page.gen.include_tracker_code',
          "type" => "checkbox",
          "default" => "N",
        ),
        "choose_department" => Array(
          "name_key" => 'page.gen.choosedepartment',
          "type" => "checkbox",
          "default" => "N",
        ),
        "department_key" => array(
          "name_key" => 'page.gen.department',
          "type" => "list",
          "values" => $departmentsParam,
          "default" => ""
        ),
        "choose_operator" => Array(
          "name_key" => 'page.gen.chooseoperator',
          "type" => "list",
          "values" => array(
            array('key' => 'N', 'value' => Resources::Get('choose_operator.no')),
            array('key' => 'optional', 'value' => Resources::Get('choose_operator.optional')),
            array('key' => 'mandatory', 'value' => Resources::Get('choose_operator.mandatory'))),
          "default" => "N",
        ),
        "chat_immediately" => Array(
          "name_key" => 'page.gen.chatimmediately',
          "type" => "checkbox",
          "default" => "N",
        ),
        "locale" => array(
          "name_key" => 'page.gen.locale',
          "type" => "list",
          "values" => $localesParam,
          "default" => Resources::getCurrentLocale()
        ),
      );

      return $params;
    }

    private static function listToArray($ar) {
        $res = array();
        foreach ($ar as $a) {
          $item['key'] = $a;
          $item['value'] = $a;
          $res[] = $item;
        }
        return $res;
    }

    public static function getImageNameFromParam($pImage, $pDepartmentKey = NULL, $pLang = NULL, $pImagePostfix = NULL, $hasOnline = NULL) {
      if (!preg_match('/\.(gif|jpg|png)$/i', $pImage)) {
        $image = $pImage.'.gif';
      } else {
        $image = $pImage;
      }

      if (empty($pLang) || !Resources::IsLocaleAvailable($pLang)) {
        $lang = Resources::getCurrentLocale();
      } else {
        $lang = $pLang;
      }

      if (empty($pImagePostfix)) {
        if (empty($hasOnline)) {
          $image_postfix = Operator::getInstance()->hasOnlineOperators($pDepartmentKey, $lang)  ? 'on' : 'off';
        } else {
          $image_postfix = $hasOnline ? 'on' : 'off';
        }
      } else {
        $image_postfix = $pImagePostfix;
      }

      $image = preg_replace('/\.(gif|jpg|png)/i', '_'.$image_postfix.'.\\1', $image);


      return "/themes/.buttons/$lang/$image";
    }

    public static function sendButton($pImage, $pDepartmentKey, $pLang, $path, $pImagePostfix = NULL) {
      $imageFileName = self::getImageNameFromParam($pImage, $pDepartmentKey, $pLang, $pImagePostfix);
      $filename = $path. $imageFileName;

      if (!file_exists($filename)) {
        return false;
      }

      $fp = fopen($filename, 'rb');
      if (empty($fp)) {
        return false;
      }
      preg_match('/(gif|jpg|png)$/i', $imageFileName, $matches);
      $fileType = $matches[0];

      Browser::SendNoCache();
      header("Content-Type: image/".$fileType);
      header("Content-Length: ".filesize($filename));
      fpassthru($fp);
      return true;
    }

    public static function enumAvailableImages($locale) {
      
      $path = dirname(__FILE__).'/../themes/.buttons/';
      
       

      $imagesDir = $path . $locale;
      $images = array();
      if ($handle = opendir($imagesDir)) {
        while (false !==($file = readdir($handle))) {

          if (preg_match("/^(\w+)_on.(gif|jpg|png)$/i", $file, $matches) && is_file("$imagesDir/".$matches[1]."_off.".$matches[2])) {
            if (strtolower($matches[2]) == 'gif') {
              $images[$matches[1]] = 1;
            } else {
              $images[$matches[1].'.'.$matches[2]] = 1;
            }
          }
        }
        closedir($handle);
      }
      $res = array_keys($images);
      sort($res);
      return $res;
    }


  }
?>
