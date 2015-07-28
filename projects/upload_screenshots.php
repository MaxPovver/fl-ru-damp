<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

$ret = 'ok:::-!-:::';

if(isset($_POST['project_id']) && isset($_POST['emp_id']) && is_array($_FILES['attach'])) {
    $prj_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
    $project = new projects();
    $prj = $project->GetPrj(0, $prj_id, 1);

    if($prj['id']==$prj_id && $_POST['emp_id']==$prj['user_id']) {
        $attaches = array();
		if (is_array($_FILES['attach']) && !empty($_FILES['attach']['name'])) {
			foreach ($_FILES['attach']['name'] as $key=>$v) {
				if (!$_FILES['attach']['name'][$key]) continue;
				$attaches[] = new CFile(array(
					'name'     => $_FILES['attach']['name'][$key],
					'type'     => $_FILES['attach']['type'][$key], 
					'tmp_name' => $_FILES['attach']['tmp_name'][$key], 
					'error'    => $_FILES['attach']['error'][$key], 
					'size'     => $_FILES['attach']['size'][$key]
				));
			}
		}
        if($attaches) {
            $files = array();
            $error = false;
            $err = '';
            $emp = new users();
            $emp->GetUser($emp->GetField($prj['user_id'],$ee,'login')); 
            $dir = $emp->login;

            foreach($attaches as $attach) {
                $attach->max_size = 2097152;
                $attach->proportional = 1;
                $fname = $attach->MoveUploadedFile($dir."/upload");
                if($attach->error) {
                    $err = $attach->error;
                    $error = true;
                    if($attach->size > $attach->max_size) {
                        $err = 'Недопустимый размер файла';
                    }
                } else {
                    if(!in_array($attach->getext(), array_merge($GLOBALS['graf_array'], array('doc', 'docx', 'txt', 'xls', 'xlsx')))) {
                        $err = 'Недопустимый тип файла';
              		    $error = true;
                        continue;
                    } else {
                        array_push($files, $fname);
                    }
                }
            }

            if($error) {
                if($files) {
                    $f = new CFile();
                    foreach($files as $file) {
                        $f->Delete(0, "users/".substr($dir, 0,2)."/".$dir."/upload/", $file);
                    }
                }
                $ret = 'error:::-!-:::'.$err;
            } else {
                $files_str = '';
                if($files) {
                    foreach($files as $file) {
                        $files_str .= $file.',';
                    }
                    $files_str = preg_replace("/,$/", "", $files_str);
                }
                $ret = 'ok:::-!-:::'.$files_str;
            }
        }
    }
}
echo $ret;
?>
