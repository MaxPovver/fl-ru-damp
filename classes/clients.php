<?php

/**
 * Подключаем файл основных функиця системы
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы с клиентами
 *
 */
class clients
{
    /**
     * Создать нового клиента
     *
     * @param string $name  Название клиента
     * @param string $link  Ссылка на сайт клиента   
     * @param object $logo  Логотип клиента
     * @param string $error Возвращает сообщение об ошибке если она есть
     */
    function newClient($name, $link, $logo, &$error) {
        global $DB;
        if ($logo) {
            $logo->max_size       = 100000;
            $logo->max_image_size = array('width'=>140, 'height'=>100);
            $logo->resize         = 1;
            $logo->topfill        = 1;
            $logo->server_root    = 1;
            
            $logo_client = $logo->MoveUploadedFile("clients/");
            $error = $logo->StrError('<br />');
        }    
        
        if(!$error) {
            $sql   = "INSERT INTO clients (name_client, link_client, logo) VALUES(?, ?, ?); ";
            $error = $DB->query($sql, $name, $link, $logo_client);
        }
    }
    
    /**
     * Редактирование клиента
     *
     * @param string $name  Название клиента
     * @param string $link  Ссылка на сайт клиента   
     * @param object $logo  Логотип клиента
     * @param string $error Возвращает сообщение об ошибке если она есть
     */
    function editClient($name, $link, $logo, $id, &$error) {
        global $DB;
        if(!$id) { $error = "Ошибка"; return false; }
        
        if ($logo) {
            $logo->max_size       = 100000;
            $logo->max_image_size = array('width'=>140, 'height'=>100);
            $logo->resize         = 1;
            $logo->topfill        = 1;
            $logo->server_root    = 1;
            
            $logo_client = $logo->MoveUploadedFile("clients/");
            $error = $logo->StrError('<br />');
        }    
        
        if(!$error) {
            if($logo) $logo_client =  ", logo = '{$logo_client}'";
            $sql   = "UPDATE clients SET name_client = ?, link_client = ? {$logo_client} WHERE id = ?i;";
            $ret = $DB->query($sql, $name, $link, $id);
            if($ret == null) $error = "Ошибка обработки информации";
        }    
    }
    
    /**
     * Удаление клиента по его ИД
     *
     * @param integer $cid Ид Клиента
     * @return string Ошибка если есть
     */
    function deleteClient($cid) {
        global $DB;
        if(!$cid) return false;
        return $DB->query("DELETE FROM clients WHERE id = ?i", $cid);
    }
    
    /**
     * Берем всех клиентов
     *
     * @param string  $rand    Тип сортировки 
     * @param integer $limit   Лимит показа
     * @return array Данные клиенты
     */
    function getClients($rand = "RANDOM()", $limit = 90) {
        global $DB;
        $sql  = "SELECT * FROM clients ORDER BY {$rand} LIMIT {$limit} OFFSET 0;";  
        return $DB->cache(180)->rows($sql);
    }
    
    /**
     * Берем клиентов для админки (без рандомной сортировки, с пагинацией и количеством клиентов)
     *
     * @param integer $page     Текущая страница
     * @param integer $count    Возвращает общее количество клиентов
     * @param integer $limit    Лимит показа
     * @return array Данные клиентов
     */
    function getAdminClients($page=0, &$count, $limit=10) {
        global $DB;
        $page--;
        if($page<0) $page = 0;
        if($limit<=0) $limit = 10;
        
        $page = $page*$limit;
        
        $sql  = "SELECT * FROM clients ORDER BY id DESC LIMIT {$limit} OFFSET {$page};"; 
        $ret  = $DB->rows($sql);
        
        $csql  = "SELECT COUNT(*) FROM clients;";
        $count = $DB->val($csql);
          
        return $ret;
    }
}

?>