<?php
class page_admin_smi extends page_base {    
    function getlistAction() {
        $order_dir = front::$_req["dir"];
        if(!in_array($order_dir, array("ASC", "DESC"))) $order_dir = "ASC";
        
        $order_col = front::$_req["sort"];
        if(!in_array($order_col, array("id", "title", "post_time"))) $order_col = "id";
        
        $limit = front::$_req["limit"];
        if(!$limit) $limit = 20;
        $offset = front::$_req["start"];
        if(!$offset) $offset = 0;
        
        $totalCount = front::og("db")->select("SELECT COUNT(*) FROM press")->fetchOne();
                
        $blogs = front::og("db")->select("SELECT * FROM press ORDER BY ?v ?v LIMIT ? OFFSET ?",$order_col, $order_dir, $limit, $offset)->fetchAll();
       
        $blogs = front::toUtf($blogs);
        
        echo json_encode(array("data"=>$blogs , "totalCount"=>$totalCount));
    }
    
    function getinfoAction() {
        $db = front::og("db"); 
        $one_news = $db->select("SELECT * FROM press WHERE id = ?n LIMIT 1;", front::$_req["id"])->fetchRow();
        $one_news = front::toUtf($one_news); 
      //  foreach($one_news as &$one) { $one = br2nl($one);}  
        echo json_encode(array("form"=>$one_news));
    }
    
    function deleteAction() {
        $db = front::og("db"); 
        
        if(intval($id = front::$_req["id"]) > 0) {
            $affected_rows = $db->delete("DELETE FROM press WHERE id = ?n;", 
                $id
            );
        }
        echo json_encode(array("success"=>$affected_rows));
    }
    function saveAction() {
        $db = front::og("db"); 
        
        $save = front::toWin(array(
            "title" => front::$_req["form"]["title"], 
            "short" => front::$_req["form"]["short"], 
            "msgtext" => front::$_req["form"]["msgtext"], 
            "sign" => front::$_req["form"]["sign"], 
            "logo" => page_admin_flash_upload2::getFileValue(front::$_req["form"]["logo"], "about/press/"), 
            "link" => front::$_req["form"]["link"], 
        ));
        
       // foreach($save as &$one) { $one = nl2br($one);}  
        
        if(intval($id = front::$_req["id"]) > 0) {
            $aff = $db->update("UPDATE press SET ?s WHERE (id = ?n)", $save, $id);
        } else {
            $id = $db->insert("press", $save);
        }
        
        echo json_encode(array("success"=>true, "id"=>$id));
    }
}
?>