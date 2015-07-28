<?php
class page_admin_flash_upload extends page_base {    
    function getFileValue(&$str, $dir) {  
        $pic = false;
        if($str != "") {
            $b_file = new CFile("temp/".$str);
            if($b_file->id > 0) {
                $b_file->Rename($dir.$str);
                $pic = $str;
            } else {
                $b_file2 = new CFile($dir.$str);
                if($b_file2->id > 0) {
                    $pic = $str;    
                } else {
                    $pic = false;
                }
            }
        }
        
        return $pic;
    }      
    function testAction() {
        die("OK");
    }
    function openFileAction() {        
        $b_file = new CFile("temp/".$this->uri[0]);
        if($b_file->id > 0) {
            header("Location: ".WDCPREFIX."/temp/".$this->uri[0]); exit();
        } else {
            $b_file2 = new CFile(front::$_req["altDir"].$this->uri[0]);
            if($b_file2->id > 0) {
                header("Location: ".WDCPREFIX."/".front::$_req["altDir"].$this->uri[0]); exit();    
            }
        }
    }
    
    function saveFileAction() {
        if (!($img = $_FILES['Filedata']))
        {
            print(json_encode(array ('success' => false, 'text'=>"Не получены данные", 'debug' => $_FILES['Filedata'])));
            return false;
        }
        
        if(front::$_req["type"] == "img") {
            list($width, $height, $type, $attr) = getimagesize( $img['tmp_name'] );
            $ext = array ('gif', 'jpeg', 'png');
            $ext_format = array('gif' => 1, 'jpeg' => 2,
                'png' => 3);
            
            if ($type < 1 || $type > 3) {
                 print(json_encode(array ('success' => false, 'text'=>"Формат не поддерживается", 'debug' => $_FILES['Filedata'])));
                 return false;
            }
            
            if( 
                (front::$_req["width"] > 0 && $width > front::$_req["width"])
                || (front::$_req["height"] > 0 && $height > front::$_req["height"])
                ) {
                    print(json_encode(array ('success' => false, 'text'=>"Слишком большое разрешение", 'debug' => $_FILES['Filedata'])));
                    return false;
            }
        }

        $b_file = new CFile($_FILES['Filedata']);
        $b_file->max_size = 1048576;
        $b_file->server_root = 1;
        $f_name = $b_file->MoveUploadedFile("temp/");
            
        echo json_encode(array("success"=>true, "id"=>$f_name, "path"=>WDCPREFIX."/temp/".$f_name));
    }
}
?>