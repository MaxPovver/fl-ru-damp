<?php
class page_admin_work extends page_base {    
    function getlistAction() {
        $order_dir = front::$_req["dir"];
        if(!in_array($order_dir, array("ASC", "DESC"))) $order_dir = "ASC";
        
        $order_col = front::$_req["sort"];
        if(!in_array($order_col, array("id", "name", "login", "occupation", "email", "icq", "skype", "login", "groupid", "additional"))) $order_col = "id";
        
        $limit = front::$_req["limit"];
        if(!$limit) $limit = 20;
        $offset = front::$_req["start"];
        if(!$offset) $offset = 0;
        
        $news = front::og("db")->select("SELECT id, name, login, occupation, email, icq, skype, login, groupid, additional FROM team_people ORDER BY ?v ?v LIMIT ?n OFFSET ?n;", $order_col, $order_dir, $limit, $offset)->fetchAll();
        
        $news = front::toUtf($news);
        
        $totalCount = front::og("db")->select("SELECT COUNT(*) FROM team_people;")->fetchOne();
        
        echo json_encode(array("data"=>$news , "totalCount"=>$totalCount));
    }
    function getinfoAction() {
        $db = front::og("db"); 
        $one_news = $db->select("SELECT id, name, login, occupation, email, icq, skype, login, groupid, additional FROM team_people WHERE id = ?n LIMIT 1;", front::$_req["id"])->fetchRow();
        $one_news = front::toUtf($one_news);   
        echo json_encode(array("form"=>$one_news));
    }
    function deleteAction() {
        $db = front::og("db"); 
        
        if(intval($id = front::$_req["id"]) > 0) {
            $affected_rows = $db->delete("DELETE FROM team_people WHERE id = ?n;", 
                $id
            );
        }
        echo json_encode(array("success"=>$affected_rows));
    }
    function saveAction() {
        $db = front::og("db"); 
        
        $save = front::toWin(array(
            "name" => front::$_req["form"]["name"], 
            "login" => front::$_req["form"]["login"], 
            "occupation" => front::$_req["form"]["occupation"], 
            "email" => front::$_req["form"]["email"],
            "icq" => front::$_req["form"]["icq"],
            "skype" => front::$_req["form"]["skype"],
            "login" => front::$_req["form"]["login"],
            "groupid" => front::$_req["form"]["groupid"],
            "additional" => front::$_req["form"]["additional"], 
        ));
        if(intval($id = front::$_req["id"]) > 0) {
            $aff = $db->update("UPDATE team_people SET ?s WHERE (id = ?n)", $save, $id);
        } else {
            $id = $db->insert("team_people", $save);
        }
        
        echo json_encode(array("success"=>true, "id"=>$id));
    }
}    
    
?>    