<?php
class page_admin_news extends page_base {    
    function getlistAction() {
        $order_dir = front::$_req["dir"];
        if(!in_array($order_dir, array("ASC", "DESC"))) $order_dir = "ASC";
        
        $order_col = front::$_req["sort"];
        if(!in_array($order_col, array("id", "header", "post_date"))) $order_col = "id";
        
        $limit = front::$_req["limit"];
        if(!$limit) $limit = 20;
        $offset = front::$_req["start"];
        if(!$offset) $offset = 0;
        
        $news = front::og("db")->select("SELECT id, post_date, header, n_text FROM news ORDER BY ?v ?v LIMIT ?n OFFSET ?n;", $order_col, $order_dir, $limit, $offset)->fetchAll();
        
        $news = front::toUtf($news);
        
        $totalCount = front::og("db")->select("SELECT COUNT(*) FROM news;")->fetchOne();
        
        echo json_encode(array("data"=>$news , "totalCount"=>$totalCount));
    }
    function getinfoAction() {
        $db = front::og("db"); 
        $one_news = $db->select("SELECT id, post_date, header, n_text FROM news WHERE id = ?n LIMIT 1;", front::$_req["id"])->fetchRow();
        $one_news = front::toUtf($one_news);   
        echo json_encode(array("form"=>$one_news));
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
            "header" => front::$_req["form"]["header"], 
            "n_text" => front::$_req["form"]["n_text"], 
            "post_date" => front::$_req["form"]["post_date"], 
        ));
        if(intval($id = front::$_req["id"]) > 0) {
            $aff = $db->update("UPDATE news SET ?s WHERE (id = ?n)", $save, $id);
        } else {
            $id = $db->insert("news", $save);
        }
        
        echo json_encode(array("success"=>true, "id"=>$id));
    }
}
?>