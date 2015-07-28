<?php
class page_admin extends page_base {    
    function indexAction() {
        front::og("tpl")->display("admin.tpl");
    }
}
?>