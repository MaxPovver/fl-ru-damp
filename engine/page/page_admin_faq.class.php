<?php
class page_admin_faq extends page_base {    
    function getlistAction() {
        $order_dir = front::$_req["dir"];
        if(!in_array($order_dir, array("ASC", "DESC"))) $order_dir = "ASC";
        
        $order_col = front::$_req["sort"];
        if(!in_array($order_col, array("id", "question", "cat_name"))) $order_col = "id";
        
        $limit = front::$_req["limit"];
        if(!$limit) $limit = 20;
        $offset = front::$_req["start"];
        if(!$offset) $offset = 0;
        
        
        
        
        $totalCount = front::og("db")->select("SELECT COUNT(*) FROM faq")->fetchOne();
                
        $blogs = front::og("db")->select("SELECT f.*, fc.name as cat_name FROM faq f JOIN faq_category fc on f.faqcategory_id = fc.id ORDER BY ?v ?v LIMIT ? OFFSET ?",$order_col, $order_dir, $limit, $offset)->fetchAll();
       
        $blogs = front::toUtf($blogs);
        
        echo json_encode(array("data"=>$blogs , "totalCount"=>$totalCount));
    }
    function getinfoAction() {
        $db = front::og("db"); 
        $one_news = $db->select("SELECT f.*, fc.name as cat_name FROM faq f JOIN faq_category fc on f.faqcategory_id = fc.id WHERE f.id = ?n LIMIT 1;", front::$_req["id"])->fetchRow();
        $one_news = front::toUtf($one_news);   
        echo json_encode(array("form"=>$one_news));
    }  
    function getCategsAction() {
        $db = front::og("db"); //   , 
        $all = $db->select("SELECT id, name FROM faq_category f WHERE (f.name ILIKE ?);", "%".trim(front::toWin(front::$_req["query"]))."%")->fetchAll();
        $all = front::toUtf($all);   
        echo json_encode(array("data"=>$all));
    }    
    function saveAction() {
        $db = front::og("db"); 
        
        $save = front::toWin(array(
            "question" => front::$_req["form"]["question"], 
            "answer" => front::$_req["form"]["answer"], 
            "url" => front::$_req["form"]["url"], 
            "faqcategory_id" => intval(front::$_req["form"]["faqcategory_id"]), 
        ));
        if(intval($id = front::$_req["id"]) > 0) {
            $aff = $db->update("UPDATE faq SET ?s WHERE (id = ?n)", $save, $id);
        } else {
            $id = $db->insert("faq", $save);
        }
        
        echo json_encode(array("success"=>true, "id"=>$id));
    }  
    function editRadzelAction() {
        $db = front::og("db"); 
        
        $save = front::toWin(array(
            "name" => front::$_req["title"],
        ));
        if(intval($id = front::$_req["id"]) > 0) {
            $aff = $db->update("UPDATE faq_category SET ?s WHERE (id = ?n)", $save, $id);
        } else {
            $id = $db->insert("faq_category", $save);
        }
        
        echo json_encode(array("success"=>true, "id"=>$id));
    }
}
?>