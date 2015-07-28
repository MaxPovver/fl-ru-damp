<?php
class page_admin_cblog extends page_base {    
    function getlistAction() {
        $order_dir = front::$_req["dir"];
        if(!in_array($order_dir, array("ASC", "DESC"))) $order_dir = "ASC";
        
        $order_col = front::$_req["sort"];
        if(!in_array($order_col, array("id", "title", "date_create"))) $order_col = "id";
        
        $limit = front::$_req["limit"];
        if(!$limit) $limit = 20;
        $offset = front::$_req["start"];
        if(!$offset) $offset = 0;
        
        
        
        
        $totalCount = front::og("db")->select("SELECT COUNT(*) FROM corporative_blog WHERE id_blog = 0")->fetchOne();
                
        $blogs = front::og("db")->select("SELECT c.*, u.uname, u.usurname, u.login FROM corporative_blog c JOIN users u on u.uid = c.id_user  WHERE id_blog = 0 ORDER BY ?v ?v LIMIT ? OFFSET ?",$order_col, $order_dir, $limit, $offset)->fetchAll();
        $bids  = front::get_hash($blogs, "id", "id");
        $uids  = front::get_hash($blogs, "id_user", "id_user");
        $comm  = front::get_hash(front::og("db")->select("SELECT COUNT(id_blog) as count, id_blog FROM corporative_blog WHERE id_blog IN(?a) GROUP BY id_blog", $bids)->fetchAll(), "id_blog", "count");
        $user  = front::og("db")->select("SELECT uname, usurname, login, uid FROM users WHERE uid IN(?a)", $uids)->fetchAll();//, "uid", "usname");
            
        foreach($user as $k=>$v)  $usr[$v['uid']]= $v;
        
        $blogs = front::toUtf($blogs);
        
        echo json_encode(array("data"=>$blogs , "totalCount"=>$totalCount));
    }
    function getinfoAction() {
        $db = front::og("db"); 
        $one_news = $db->select("SELECT id, title, msg FROM corporative_blog WHERE id = ?n LIMIT 1;", front::$_req["id"])->fetchRow();
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
            "title" => front::$_req["form"]["title"], 
            "msg" => front::$_req["form"]["msg"], 
           // "date_create" => date("Y-m-d"), 
            "id_blog" => 0, 
            "id_user" => $_SESSION["uid"], 
        ));
        if(intval($id = front::$_req["id"]) > 0) {
            $aff = $db->update("UPDATE corporative_blog SET ?s WHERE (id = ?n)", $save, $id);
        } else {
            $id = $db->insert("corporative_blog", $save);
        }

        echo json_encode(array("success"=>true, "id"=>$id));
    }
}
?>