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
    function smarty_function_get_locale_links($params, &$smarty) {
        if (empty($params['locales'])) return '';
        if (empty($params['current_locale'])) $params['current_locale'] = '';

        $localeLinks = "";
        foreach ($params['locales'] as $k) {
            if (strlen($localeLinks) > 0) {
                $localeLinks .= " &bull; ";
            }
            if ($k == $params['current_locale']) {
                $localeLinks .= $k;
            } else {
                $args = isset($params['link_arguments']) ? $params['link_arguments'] : '';

                $currentLink = $_SERVER['REQUEST_URI'];

                $newLink = _substParam($currentLink.$args, 'lang', $k);

                $localeLinks .= "<a href=\"$newLink\">$k</a>";
            }
        }
        return $localeLinks;
    }

function _substParam($url, $param, $value) {
  $expr = $param.'='.urlencode($value);
  if (preg_match('/(.+)('.$param.'=[^&]*)(.*)/', $url, $matches)) {
    $newUrl = $matches[1].$expr.$matches[3];
//    var_dump($matches);
  } else {
    if (strstr($url, '&') || (strstr($url, '?') && !preg_match('/\\?$/', $url))) {
      $newUrl = $url.'&'.$expr;
    } elseif (strstr($url, '?') && preg_match('/\\?$/', $url)) {
      $newUrl = $url.$expr;
    } else {
      $newUrl = $url.'?'.$expr;
    }
  }

  return $newUrl;
}
?>