<?php
/*
 * Загрузка файлов для работы в портфолио, аватар, лого работодателя и резюме фрилансера (пока)
 */
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php' );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");

session_start();

$type   = InGetPost( 'type' );
$stc    = new static_compress;
$uid    = get_uid( false );
$is_adm = $pict_added = false;
$err    = '';
$pkey   = InGetPost( 'pkey' );
$imageTypes = array();

// чтобы админ мог редактировать
if ( hasPermissions('users') && InGetPost('uid') ) {
     $uid    = InGetPost('uid');
     $is_adm = true;
}

if ( isset($_POST['action']) && ($_POST['action'] == 'add_pic') && is_array($_FILES['attach']) ) {
    $file = new CFile( $_FILES['attach'] );
    
    if ( $file->error[0] && $file->name ) { $err = 'Слишком большой файл'; }
    
    if ( $file->size > 0 ) {
        $pict_added = true;
        
        if ( $type == 'work_prev' && $file->size > 102400 ) { 
            $err = 'Слишком большой файл превью. Загрузите превью меньшего объема.'; 
        }
        
        if ( !$err && $type != 'prj_logo' ) {
            $dir = get_login( $uid );

            // чтобы админ мог редактировать
            if ( $is_adm ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
                $user = new users;
                $user->GetUserByUID( $uid );
                $dir = $user->login;
            }
            
            $sFullDir = $dir . '/upload';
            
            if ( $type == 'work_pict' ) {
                $file->max_size = 10485760;
            }
            elseif ( $type == 'resume_file' ) {
                $sFullDir       = $dir . '/resume';
                $file->max_size = 5242880;
            }
            elseif ( $type == 'photo' ) {
                $sFullDir       = $dir . '/foto';
                $file->max_size = 102400;
                $file->max_image_size = array( 'width' => 100, 'height' => 100 );
                $file->resize = 1;
                $file->proportional = 1;
                $file->topfill = 1;
                $file->allowed_ext = array_diff( $GLOBALS['graf_array'], array('swf', 'gif') );
            }
            elseif ( $type == 'logo' ) {
                $sFullDir       = $dir . '/logo';
                $file->max_size = 50000;
                $file->max_image_size = array('width'=>150, 'height'=>100);
                $file->resize = 0;
                $file->proportional = 1;
                $file->topfill = 1;
                $file->allowed_ext = $GLOBALS['graf_array'];
            }
            elseif ( $type == 'carusellogo' ) {
                $file->max_size = 1024 * 1024; // 1 мб
                $imageTypes = array( 2, 3 );
                $file->max_image_size = array('width' => 50, 'height' => 50, 'less' => 0);
                $file->resize = 1;
                $file->proportional = 1;
                $cFile->crop = 1;
                $sFullDir = $dir . '/foto';
            }
            else {
                $file->max_image_size = array( 'width' => 200, 'height' => 200, 'less' => 0 );
                $file->resize           = 1;
                $file->proportional     = 1;
                $file->prefix_file_name = "sm_";
                $file->max_size         = 102400;
            }
            
            // если заданы типы графических файлов
            if ( $imageTypes ) {
                // то файл должен быть графическим
                $file->_getImageSize( $file->tmp_name );
                
                if ( !$file->image_size['type'] || !in_array($file->image_size['type'], $imageTypes) ) {
                    $err = 'Недопустимый формат файла';
                }                    
            }
            
            if ( !$err ) {
                $filename = $file->MoveUploadedFile( $sFullDir );
                $fileid   = $file->id;
                $err      = $file->StrError();

                if ( !$err && $type == 'work_prev' && (
                        !in_array($file->getext(), $GLOBALS['graf_array']) 
                        || strtolower($file->getext()) == 'swf' 
                        || strtolower($file->getext()) == 'flv' ) 
                ) {
                    $err = 'Недопустимый тип файла';
                }

                if ( $type == 'photo' || $type == 'logo' ) {
                    if ( !$err && !$file->img_to_small('sm_'.$filename, array('width' => 50, 'height' => 50)) ) {
                        $err .= 'Невозможно уменьшить картинку.';
                    }
                }
            }
        }
        elseif ( !$err ) {
            // логотип проекта
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
            $tmpPrj = new tmp_project( $pkey );
            $prj    = $tmpPrj->init( 1 );
            $err    = $tmpPrj->setLogo( $file );
            
            $tmpPrj->fix();
            
            $logo    = $tmpPrj->getLogo();
            $logourl = WDCPREFIX.'/'.$logo['path'].$logo['name'];
        }
    }
}
?>
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<?php $stc->Add( '/css/projects3.css' ); ?>
<?php $stc->Add( '/scripts/warning.js' ); ?>
<?php $stc->Send(); ?>
</head>
<body style="margin:0px; padding:0px"<?php 
    if ( $pict_added && !$err ) { 
        if ( $type == 'work_pict' ) {
    ?> onload="parent.adm_edit_content.portfolioAddFile('pict', '<?=$filename?>', '<?=$fileid?>');"<?php 
        }
        elseif ( $type == 'prj_logo' ) {
    ?> onload="parent.adm_edit_content.prjAddLogo('<?=$logo['id']?>', '<?=$logourl?>');"<?php 
        }
        elseif ( in_array($type, array('resume_file', 'photo', 'logo', 'carusellogo')) ) {
    ?> onload="parent.adm_edit_content.profileAddFile('<?=$type?>', '<?=$filename?>', '<?=$fileid?>');"<?php 
        }
        else {
    ?> onload="parent.adm_edit_content.portfolioAddFile('prev_pict', '<?=$filename?>', '<?=$fileid?>');"<?php 
        }
    } ?>>
  <form id="form_add_pict" name="form_add_pict" action="/upload.php" method="POST" enctype="multipart/form-data" onsubmit="return parent.allowedExt(this['attach'].value);">
      <input type="hidden" name="type" value="<?=  htmlspecialchars($type);?>">
  <?php if ( $is_adm ) { ?>
  <input type="hidden" name="uid" value="<?= htmlspecialchars($uid);?>">
  <?php } ?>
  <?php if ( $pkey ) { ?>
  <input type="hidden" name="pkey" value="<?= htmlspecialchars($pkey);?>">
  <?php } ?>
  <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>">
  <input type="hidden" name="action" value="add_pic" />
  <input type="file" name="attach" />
  <input type="submit" value="Подгрузить" />
  <?php if ( $err ) { ?>
  <?=view_error($err)?><br/>
  <?php } ?>
  </form>
</body>
</html>