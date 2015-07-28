<?php
class page_admin_partners extends page_base {    
    function getinfoAction() {
        $db = front::og("db"); 
        $one_news = $db->select("SELECT * FROM partners WHERE id = ?n LIMIT 1;", front::$_req["id"])->fetchRow();
        $one_news = front::toUtf($one_news); 
      //  foreach($one_news as &$one) { $one = br2nl($one);}  
        echo json_encode(array("form"=>$one_news));
    }
    
    function deleteAction() {
        $db = front::og("db"); 
        
        if(intval($id = front::$_req["id"]) > 0) {
            $affected_rows = $db->delete("DELETE FROM partners WHERE id = ?n;", 
                $id
            );
        }
        echo json_encode(array("success"=>$affected_rows));
    }
    function saveAction() {
        $db = front::og("db"); 
        
        $save = front::toWin(array(
            "msgtext" => front::$_req["form"]["msgtext"], 
            "sign" => front::$_req["form"]["sign"], 
            "link" => front::$_req["form"]["link"], 
            "logo" => page_admin_flash_upload2::getFileValue(front::$_req["form"]["logo"], "about/press/"), 
        ));
        
      //  foreach($save as &$one) { $one = ($one);}  
        
        if(intval($id = front::$_req["id"]) > 0) {
            $aff = $db->update("UPDATE partners SET ?s WHERE (id = ?n)", $save, $id);
        } else {
            $id = $db->insert("partners", $save);
        }
        
        echo json_encode(array("success"=>true, "id"=>$id));
    }
}
?>