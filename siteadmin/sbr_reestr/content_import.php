<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
?>

<h2 class="b-layout__title b-layout__title_padbot_20">Реестры для сайта</h3>

<?php require_once ('form_upload.php');?>

<?php echo $list ?>