<?
$rpath = "../";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");

session_start();

$uid = $_SESSION['WUID'];
if(!$uid) return false;

$type = __paramInit('string', 'type', null, null);

switch($type) {
    case "logo_company":
        if (is_array($_FILES['logo_attach']) && $_SESSION['RUID']) {
            $img = new CFile($_FILES['logo_attach']);
            $img->disable_animate = true;
            if ($img->size > 0) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
                $user = new users();
                $login = $user->GetField($_SESSION['RUID'], $error, "login");
                $dir    = "users/".substr($login, 0, 2)."/".$login."/";
                $img->max_size = 51200;
                $img->proportional = 1;
                $img->topfill = 1;
                $img->server_root = 1;
                $dir .= "/logo/";
                $pictname = $img->MoveUploadedFile($dir);
                if (!isNulArray($img->error)) {
                    if (is_array($img->error)) {
                        $err = $img->error[0];
                    } else {
                        $err = $img->error;
                    }
                    $error = true;
                    $pictname = $prevname = '';
                } else {
                    if(!in_array($img->getext(), $GLOBALS['graf_array'])) {
                        $err = "Недопустимый тип файла";
                        $error = true;
                    }
                    
                    if (in_array($img->getext(), $GLOBALS['disallowed_array'])) {
                        $err = 'Недопустимый тип файла';
                        $error = true;
                    } else {
                        if (in_array($img->getext(), $GLOBALS['graf_array']) && strtolower($img->getext()) != "swf" && strtolower($img->getext()) != "flv") {
                            
                            $image_size = $img->image_size;
                            if ($image_size['width'] > 150 || $image_size['height'] > 100) {
                                $err = 'Размер изображения не должен превышать 150х100 пикселей';
                                $error = true;
                            }
                            // Делаем превью.
                            $pict_added = $img->img_to_small("sm_" . $pictname, array('width' => 150, 'height' => 100, 'less' => 0));
                            if (!isNulArray($img->error)) {
                                $error = true;
                                $pictname = $prevname = '';
                            } elseif ($pict_added) {
                                $prevname = 'sm_' . $pictname;
                            }
                        } else {
                            $pict_added = true;
                        }
                    }
                }
            } else if ($img->error) {
                $err = $img->error[0];
                $error = true;
            }
        }
        
        $link  = WDCPREFIX . '/' . $dir . ( !$prevname ? $pictname : $prevname );
        $js_callback_func = "parent.addIMGLogoCompany('{$link}', '{$img->id}', '{$pictname}')";
        break;
    case "work_example":
        if ( !empty($_FILES['attach']) ) {
            $file = new CFile($_FILES['attach']);
            $file->table = "file_wizard";
            $file->disable_animate = true;
            if ( $file->size > 0 ) {
                $dir = "wizard/";
                $file->max_size     = 2 * 1048576;
                $file->proportional = 1;
                $file->server_root  = 1;
                $filename = $file->MoveUploadedFile($dir);
                if ( !empty($file->error) ) {
                    if (is_array($file->error)) {
                        $err = $file->error[0];
                    } else {
                        $err = $file->error;
                    }
                    $error = true;
                } else {
                    if ( in_array($file->getext(), $GLOBALS['disallowed_array']) ) {
                        $err = 'Недопустимый тип файла';
                        $error = true;
                    } else {
                        $isImg = in_array($file->getext(), $GLOBALS['graf_array']) && strtolower($file->getext()) != "swf";
                        if ( $isImg ) {
                            $file->img_to_small("sm_{$filename}", array('width' => 200, 'height' => 200, 'less' => 0));
                            $filename = "sm_{$filename}";
                        }
                    }
                }
            } else {
                $err = 'Загрузка файла прервалась';
                $error = true;
            }
        }
        $link  = WDCPREFIX . '/' . $dir . $filename;
        $pos   = intval($_POST['position']);
        if ( $error ) {
            $js_callback_err_func = "parent.boxWork{$pos}.error('{$err}')";
        } else {
            $js_callback_func = "parent.boxWork{$pos}.complete({ name: '{$filename}', id: {$file->id}, link: '{$link}' })";
        }
        break;
    case "logo":
        if (is_array($_FILES['logo_attach'])) {
            $img = new CFile($_FILES['logo_attach']);
            $img->disable_animate = true;
            if ($img->size > 0) {
                $dir = "wizard/";
                $img->max_size = 51200;
                $img->proportional = 1;
                $img->server_root  = 1;
                $pictname = $img->MoveUploadedFile($dir);
                if (!isNulArray($img->error)) {
                    if (is_array($img->error)) {
                        $err = $img->error[0];
                    } else {
                        $err = $img->error;
                    }
                    $error = true;
                    $pictname = $prevname = '';
                } else {
                    if (in_array($img->getext(), $GLOBALS['disallowed_array'])) {
                        $err = 'Недопустимый тип файла';
                        $error = true;
                    } else {
                        if (in_array($img->getext(), $GLOBALS['graf_array']) && strtolower($img->getext()) != "swf" && strtolower($img->getext()) != "flv") {
                            $pict_added = $img->img_to_small("sm_" . $pictname, array('width' => 150, 'height' => 150, 'less' => 0));
                            if (!isNulArray($img->error)) {
                                $error = true;
                                $pictname = $prevname = '';
                            } elseif ($pict_added) {
                                $prevname = 'sm_' . $pictname;
                            }
                        } else {
                            $pict_added = true;
                            $err = 'Недопустимый тип файла';
                            $error = true;
                        }
                    }
                }
            } else if ($img->error) {
                $err = $img->error[0];
                $error = true;
            }
        }
        
        $link  = WDCPREFIX . '/' . $dir . ( !$prevname ? $pictname : $prevname );
        
        $js_callback_func = "parent.addIMGLogo('{$link}', '{$img->id}')";
        $js_callback_err_func = "parent.UploadLogoFileError('{$err}')";
        
        break;
   case "upload":
        if (is_array($_FILES['upload_file'])) {
            $img = new CFile($_FILES['upload_file']);
            $img->table = "file_wizard";
            $img->disable_animate = true;
            if ($img->size > 0) {
                $dir = "wizard/";
                $img->max_size = 5242880;
                $img->proportional = 1;
                $img->server_root  = 1;
                $pictname  = $img->MoveUploadedFile($dir);
                $id_upload = $img->id;
                if (!isNulArray($img->error)) {
                    if (is_array($img->error)) {
                        $err = $img->error[0];
                    } else {
                        $err = $img->error;
                    }
                    $error = true;
                    $pictname = $prevname = '';
                } else {
                    if (in_array($img->getext(), $GLOBALS['disallowed_array'])) {
                        $err = 'Недопустимый тип файла';
                        $error = true;
                    } else {
                        if (in_array($img->getext(), $GLOBALS['graf_array']) && strtolower($img->getext()) != "swf" && strtolower($img->getext()) != "flv") {
                            // Делаем превью.
                            $pict_added = $img->img_to_small("sm_" . $pictname, array('width' => 200, 'height' => 200, 'less' => 0));
                            if (!isNulArray($img->error)) {
                                $error = true;
                                $pictname = $prevname = '';
                            } elseif ($pict_added) {
                                $prevname = 'sm_' . $pictname;
                            }
                        } else {
                            $pict_added = true;
                        }
                    }
                }
            } elseif( strlen($img->tmp_name) != 0) {
                $err = "Пустой файл";
                $error = true;
            } elseif ($img->error) {
                $err = $img->error[0];
                $error = true;
            }
       }
       
       
       
       if($error) {
           $js_error_callback_func = "parent.upload.error('{$err}')";
       } else {
           $link  = WDCPREFIX . '/' . $dir . $pictname;
           $name  = __paramValue('string', $_FILES['upload_file']['name']);
           $maxLen = 25;
           // сокращаем название файла, сохраняя расширение
           if (strlen($name) > $maxLen) {
               $arr = explode('.', $name);
               $ext = array_pop($arr);
               $name = preg_replace("/.$ext$/", '', $name);
               $name = substr($name, 0, $maxLen) . '...';
               $name = $name . '.' . $ext;
           }
           $js_callback_func = "parent.upload.view('{$name}', '{$link}', '{$id_upload}')";
       }
       break;     
   default:
       break;
}

$stc = new static_compress;

?>
<html>
<head>
  <title></title>
  <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
  <?php $stc->Add("/scripts/warning.js"); ?>
  <?php $stc->Send(); ?>
</head>

<body onload="<?= (!$err) ? $js_callback_func : ($js_error_callback_func ? $js_error_callback_func : ($js_callback_err_func? $js_callback_err_func: "parent.alert('{$err}')"))?>">
    <?php if ($err) { ?>
    <div id="add_form_error" class="error" style="font-size:11px;"><?= $err?></div>
    <?php }//if ?>
</body>
</html>
