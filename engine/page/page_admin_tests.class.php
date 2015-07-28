<?php
class page_admin_tests extends page_base {    
    function getlistAction() {
        $data[] = array("title"=>"Контроллеры", "_is_leaf"=>false,"testable"=>false, "_parent"=>null, "_id"=>"1");     
        $data = $this->readMap(front::$map, $data);
        
        
        $data[] = array("title"=>"Модели", "_is_leaf"=>false, "_parent"=>null, "_id"=>"2");     
        $dir = opendir (ROOT_DIR . "engine/models/");
        while ($file = readdir ($dir))
        {
            if (( $file != ".") && ($file != "..") && ($file != ".svn")) {
                $testable = true;
                
                $class_name = array_shift(explode(".", $file));
                
                $test_path = "tests/models/" . $class_name . ".model.test.php";
                if(!file_exists(ROOT_DIR."engine/" .$test_path)) {
                    $test_path = "<b>Нет ".$test_path. "</b>";
                    $testable = false;
                }
                $data[] = array("title"=>$file, "path"=>$test_path . "<i> для ". $file ."</i>", "testable"=>$testable, "result"=>$testable?0:4, "run_path"=>"tests/models/" . $class_name . ".model.test.php", "_is_leaf"=>true, "_parent"=>"2", "_id"=>"3:".$class_name); 
            }
        }
        closedir ($dir); 
        
        
        $data[] = array("title"=>"Другие", "_is_leaf"=>false, "_parent"=>null, "_id"=>"3");     
        $dir = opendir (ROOT_DIR . "engine/tests/");
        while ($file = readdir ($dir))
        {
            if (($file != ".") && ($file != "..") && is_file(ROOT_DIR . "engine/tests/" . $file)) {
                $testable = true;
                
                $data[] = array("title"=>$file, "path"=>$file, "testable"=>$testable, "result"=>$testable?0:4, "run_path"=>"tests/" . $file, "_is_leaf"=>true, "_parent"=>"3", "_id"=>"4:".$file); 
            }
        }
        closedir ($dir); 
        
        $data = front::toUtf($data);
        echo json_encode(array("data"=>$data));
    }
    
    private function readMap($arr, $data, $pre = array()) {
        if(is_array($arr)) {
            foreach($arr as $key=>$ar) {
                if($key == "class") continue;
                $this_pre = array_merge($pre,  array($key));
                
                $test_path = "tests/page/" . $ar["class"] . ".page.test.php";
                $testable = true;
                if(!file_exists(ROOT_DIR."engine/" .$test_path)) {
                    $test_path = "<b>Нет ".$test_path. "</b>";
                    $testable = false;
                }
                
                $data[] = array("title"=>implode("/", $this_pre) . "/", "path"=>$test_path . "<i> для "."page/" . $ar["class"] . ".page.php"."</i>", "testable"=>$testable, "result"=>$testable?0:4, "run_path"=>"tests/page/" . $ar["class"] . ".page.test.php", "_is_leaf"=>true, "_parent"=>"1", "_id"=>"2:".$ar["class"]);         
                $data = $this->readMap($ar, $data, $this_pre);
            }
        }
        return $data;
    }
}
?>