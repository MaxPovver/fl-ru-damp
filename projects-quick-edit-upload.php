<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");

session_start();
$uid = get_uid(false);

$error = '';

if($uid && hasPermissions('projects') && $_POST['tmpid']) {
	$key = $_POST['tmpid'];
    $tmpPrj = new tmp_project($key);
    $prj = $tmpPrj->init(1);
    if($prj['id']) {
        switch($_POST['tmpaction']) {
            case 'del':
                $tmpPrj->delLogo();
                $tmpPrj->fix();
                break;
            case 'upload':
                if(__paramInit('bool', NULL, 'use_logo')) {
                    if(!$_FILES['logo']['size'])
                        $error = 'Необходимо выбрать файл';
                    elseif($err = $tmpPrj->setLogo(new CFile($_FILES['logo'])))
                        $error = $err;
                    $tmpPrj->fix();
                    $logo = $tmpPrj->getLogo();
                    $logourl = WDCPREFIX.'/'.$logo['path'].$logo['name'];
                }
                break;
        }
    }
}

?>

<script type="text/javascript">
<?php if($_POST['tmpaction']=='del') { ?>
    window.parent.popupQEditPrjDelLogoOk("<?=$error?>");
<?php } ?>

<?php if($_POST['tmpaction']=='upload') { ?>
<?php if($error) { ?>
		window.parent.popupQEditPrjUploadLogoError("<?=$error?>");
<?php } else { ?>
        window.parent.popupQEditPrjUploadLogoOk("<?=$logourl?>");
<?php } ?>
<?php } ?>
</script>