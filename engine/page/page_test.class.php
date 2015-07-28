<?php

class page_test extends page_base {    
    function indexAction() {
        front::og("tpl")->text = static_pages::get("about_history"); 
        //front::og("tpl")->script = array( '/js/ext-core-3.0/ext-core-debug.js', '/js/simpWin.js' );
        front::og("tpl")->display("test.tpl");
      
    }
    function historyAction() {
        die("D");
    }
}
?>