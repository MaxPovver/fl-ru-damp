<?php
class page_error404 extends page_base {    
    function indexAction() {
        header("Location: /error404/"); exit();    
    }
}
?>