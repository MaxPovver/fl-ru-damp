<?
$rpath = "../";
$g_page_id = "0|5";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
session_start();
$uid = get_uid(false);
$is_pro = payed::CheckPro($_SESSION['login']);
$is_adm = false;

// чтобы админ мог редактировать предложения по проектам
if ( hasPermissions('projects') && InGetPost('uid') ) {
     $uid    = InGetPost('uid');
     $is_pro = payed::checkProByUid( $uid );
     $is_adm = true;
}

$error = false; $err = '';
$pict_added = false;

//die ("DUMP: " . var_export($_FILES, true));

if (isset($_POST['action']) && ($_POST['action'] == 'add_pic') && is_array($_FILES['ps_attach']))
{
	$prj_id = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
    $img = new CFile($_FILES['ps_attach']);
	if ($img->size > 0)
    {
      $dir = get_login($uid);
      
      // чтобы админ мог редактировать предложения по проектам
      if ( $is_adm ) {
          require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
          $user = new users;
          $user->GetUserByUID( $uid );
          $dir = $user->login;
      }
      
      $img->max_size = 2097152;
      $img->proportional = 1;
      $pictname = $img->MoveUploadedFile($dir."/upload");
      $mid = $img->id;
      if (!isNulArray($img->error))
      {
          if ($img->size > $img->max_size) $err = 'Недопустимый размер файла';
		  $error = true;
          $pictname = $prevname = '';
      }
      else
      {
          if ( in_array($img->getext(), $GLOBALS['disallowed_array']) )
          {
              $err = 'Недопустимый тип файла';
              $error = true;
          }
          else
          {
              if (in_array($img->getext(), $GLOBALS['graf_array']) && strtolower($img->getext()) != "swf" && strtolower($img->getext()) != "flv")
              {
                  /**
                  * Делаем превью.
                  */
                  $pict_added = $img->img_to_small("sm_".$pictname,array('width'=>200,'height'=>200, 'less' => 0));
                  if (!isNulArray($img->error))
                  {
                      $error = true;
                      $pictname = $prevname = '';
                  }
                  elseif ($pict_added)
                  {
                      $prevname = 'sm_' . $pictname;
                  }
              }
              else
              {
                  $pict_added = true;
              }
          }
      }
    }
	else if ($img->error)
	{
		$err = $img->error[0];
		$error = true;
	}
}
else
{
    if($_GET['do_upload']) {
        $err = 'Недопустимый размер файла';
        $error = true;
        $pictname = $prevname = '';
    }
    $prj_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
}

if ($prj_id > 0)
{
    $project  = new projects();
    $prj      = $project->GetPrj(0, $prj_id, 1);
    $prj_kind = $prj['kind'];
	
	if ($prj_kind == 7) {
		if ($error || !$pictname) {
			echo "
				-- IBox --
				<uploaded>
					<nothing>opera</nothing>
					<status>error</status>
					<message>$err</message>
					<time>".date('Добавлено d.m.Y в H:i', time())."</time>
				</uploaded>
				-- IBox --
			";
		} else if ($prevname) {
			$_SESSION['contest_files'][$mid] = array('prev_id' => $img->id, 'orig_name' => $_FILES['ps_attach']['name']);
			echo "
				-- IBox --
				<uploaded>
					<nothing>opera</nothing>
					<status>success</status>
					<fileid>u$mid</fileid>
					<preview>".WDCPREFIX."/users/$dir/upload/$prevname</preview>
					<filename>".WDCPREFIX."/users/$dir/upload/$pictname</filename>
					<time>".date('Добавлено d.m.Y в H:i', time())."</time>
				</uploaded>
				-- IBox --
			";
		} else {
			$_SESSION['contest_files'][$mid] = array('orig_name' => $_FILES['ps_attach']['name']);
			echo "
				-- IBox --
				<uploaded>
					<nothing>opera</nothing>
					<status>success</status>
					<fileid>u$mid</fileid>
					<displayname>".htmlentities($_FILES['ps_attach']['name'],ENT_COMPAT,'cp1251')."</displayname>
					<filename>".WDCPREFIX."/users/$dir/upload/$pictname</filename>
					<time>".date('Добавлено d.m.Y в H:i', time())."</time>
				</uploaded>
				-- IBox --
			";
		}
		return 0;
	}
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
    $stc = new static_compress;
?>
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<?php $stc->Add("/css/projects3.css"); ?>
<?php $stc->Add("/scripts/warning.js"); ?>
<?php $stc->Send(); ?>
</head>
<body style="margin:0px; padding:0px"<? 
    if ($pict_added && !$error) { 
        if (!$is_adm) {
    ?> onload="parent.add_work(0, '<?=$pictname?>', '<?=$prevname?>');"<? 
        }
        else {
    ?> onload="parent.adm_edit_content.prjOfferAddWork(0, '<?=$pictname?>', '<?=$prevname?>');"<?php
        }
    } ?>>
<? if (($prj_kind != 2) && !$is_pro && !$is_adm) { ?>
  <form>
  <input type="file" size="50" disabled />
  <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>">
  <input type="button" value="Загрузить" disabled />
  </form>
<? } else { ?>
  <form id="form_add_pict" name="form_add_pict" action="/projects/upload.php?pid=<?=$prj_id?>&do_upload=1" method="POST" enctype="multipart/form-data" onsubmit="return parent.allowedExt(this['ps_attach'].value) && parent.filesizeNotNull(this['ps_attach']);">
  <?php if ( $is_adm ) { ?>
  <input type="hidden" name="uid" value="<?=$uid?>">
  <?php } ?>
  <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>">
  <input id="ps_action" name="action" type="hidden" value="add_pic" />
  <input id="ps_pid" name="pid" type="hidden" value="<?=$prj_id?>" />
  <input name="MAX_FILE_SIZE" value="2097152" type="hidden" />
  <input type="file" name="ps_attach" size="50"<? if (($prj_kind != 2) && !$is_pro && !$is_adm) { ?> disabled="disabled"<? } ?> />
  <input type="submit" id="ps_pict_add" name="ps_pict_add" value="Загрузить"<? if (($prj_kind != 2) && !$is_pro && !$is_adm) { ?> disabled="disabled"<? } ?> />
  <? if ($error) { ?>
  <?=view_error($err)?><br/>
  <? } ?>
  </form>
<? } ?>
</body>
</html>
<?
}
else
{
    if ( preg_match('~/projects/\?pid=([\d]+)~i', $_SERVER['HTTP_REFERER'], $aMatches) ) {
    	$project  = new projects();
        $prj      = $project->GetPrj(0, $aMatches[1], 1);
        $prj_kind = $prj['kind'];
    	
    	if ( $prj_kind == 7 ) {
    	    echo '<html>
            <head>
            <title></title>
            </head>
            <body style="margin:0px; padding:0px">
            ERROR
            </body>
            </html>';
    	    die;
    	}
    }
?>
<html>
<head>
<title></title>
</head>
<body style="margin:0px; padding:0px">
</body>
</html>
<?  
}
?>
