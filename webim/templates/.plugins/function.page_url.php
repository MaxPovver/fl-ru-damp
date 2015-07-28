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
    function smarty_function_page_url($params, &$smarty) {
        if (!isset($params['url'])) $params['url'] = $_SERVER['REQUEST_URI'];
        if (!isset($params['get'])) $params['get'] = array();
        if (!is_array($params['get'])) $params['get'] = array($params['get']);
        if (!isset($params['remove_get'])) $params['remove_get'] = false;
        
        $params['get']['lang'] = LANGUAGE_ID;
        
        $result = '';
        
        $uparts = parse_url($params['url']);
        if (empty($uparts['scheme'])) {
            $url = $uparts['path'];
        } else {
            $url = $uparts['scheme'].'://'.$uparts['host'].$uparts['path'];
        }
        $get = $uparts['query'];
        $anchor = $uparts['fragment'];
        
        if ($params['remove_get']) {
            $get = '';
        }
        
        $result .= $url . '?' . $get;
        if (!empty($params['get'])) {
            $chunks = array();
            foreach ($params['get'] as $key => $value) {
                $chunks[] = $key . '=' . $value;    
            }
            if (!empty($get)) $result .= '&';
            $result .= join('&', $chunks);
        }
        if (!empty($anchor)) $result .= '#' . $anchor;
        
        return $result;
    }
?>