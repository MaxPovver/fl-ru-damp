<?php
class page_admin_static_pages extends page_base {    
    function getlistAction() {
        $pages = front::og("db")->select("SELECT * FROM static_pages;")->fetchAll();
        
        $pages = front::toUtf($pages);
        echo json_encode(array("data"=>$pages));
    }
    function getinfoAction() {
        $db = front::og("db"); 
        $one_page = $db->select("SELECT * FROM static_pages WHERE alias = ? LIMIT 1;", front::$_req["id"])->fetchRow();
        $one_page = front::toUtf($one_page);   
        $one_page["n_text"] = $this->nl2br2($one_page["n_text"]);
        echo json_encode(array("form"=>$one_page));
    }
    function deleteAction() {
        $db = front::og("db"); 
        
        if(intval($id = front::$_req["id"]) > 0) {
            $affected_rows = $db->delete("DELETE FROM news WHERE id = ?n;", 
                $id
            );
        }
        echo json_encode(array("success"=>$affected_rows));
    }
    function saveAction() {
        $db = front::og("db"); 
        
        $save = front::toWin(array(
            "title" => front::$_req["form"]["title"], 
            "n_text" => front::$_req["form"]["n_text"], 
        ));
        if(($id = front::$_req["id"])) {
            $aff = $db->update("UPDATE static_pages SET ?s WHERE (alias = ?)", $save, $id);
            if(!$aff) {
                $save["alias"] = $id;
                $db->insert("static_pages", $save, false);
            }
        }
        
        echo json_encode(array("success"=>true, "id"=>$id));
    }
    
    // сохранение из визивига
    function savewysiwygAction() {
        $db = front::og("db"); 
        
        $alias = front::$_req["alias"];

        // проверяем права (могут быть разные для разных страниц)
        switch ($alias) {
            default:
                $permission = null;
        }

        if (!$permission || !hasPermissions($permission)) {
            return;
        }
        
        $text = front::$_req["form"]["n_text"];
        $text = iconv('UTF-8', 'CP1251', $text);
        $text = __paramValue('ckedit', $text);
        
        $title = front::$_req["form"]["title"];
        $title = iconv('UTF-8', 'CP1251', $title);
        $title = __paramValue('string', $title);

        $save = array(
            "title" => $title,
            "n_text" => $text,
        );
        if($alias) {
            $aff = $db->update("UPDATE static_pages SET ?s WHERE (alias = ?)", $save, $alias);
            if(!$aff) {
                $save["alias"] = $alias;
                $db->insert("static_pages", $save, false);
            }
        }
        
        echo json_encode(array("success"=>true, "alias"=>$alias));
    }
    
    function nl2br2($string) {
      //  $string = str_ireplace("\\r\\n", "<br />", $string);
       // $string = str_ireplace("\\r\\n", "<br>", $string);
       // $string = str_ireplace("\\r\\n", "<br/>", $string);
       
       if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 9.0") === false) return $string;
       $string = str_replace("<", "&lt;", $string);
       $string = str_replace(">", "&gt;", $string);
       /*$string = preg_replace("/(\r\n|\n|\r)/", "", $string);
       return preg_replace("=<br * /?>=i", "\n", $string);*/
       return $string;
    }
}
?>