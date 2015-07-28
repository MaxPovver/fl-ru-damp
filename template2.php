<?
// @todo Переключение версток сайта, после всех тестов удалить
// #0017167
//if($_COOKIE['template_site'] == 'template3.php') {
    include "template3.php";
    return;
//}
if(!defined('IN_STDF')) return "";    
if (!$page_title)
    $page_title = "Удаленная работа (фри-ланс) на FL.ru";
if (!$page_keyw)
    $page_keyw = "работа, ищу работу, поиск работы, удаленная работа, фри-ланс";
if (!$page_descr)
    $page_descr = "FL.ru это профессиональный ресурс, предназначенный для поиска работы или исполнителя (фрилансера) на удаленную работу (фри-ланс).";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
$stc = new static_compress;

$_promo_block = !(isset($_COOKIE['nfastpromo']) && strstr($_COOKIE['nfastpromo'], 'close'));
if($_SESSION['uid'] && $_promo_block) {
    $_promo_block = $_SESSION['promo_block_hide'] == '0' ? false : true;
}

/*@mark_0013129*/
$page_keyw  = $page_title;
$page_descr = $page_title;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
    <head>
        <meta name="description" lang="ru" content="<?= $page_descr ?>" />
        <meta name="keywords" lang="ru" content="<?= $page_keyw ?>" />
        <?php if(!empty($FBShare)) { ?>
        <?= printMetaFBShare($FBShare); ?>
        <?php }//if?>
        <meta content="text/html; charset=windows-1251" http-equiv="Content-Type" />
        <title><?= $page_title ?></title>
        <link rel="shortcut icon" href="/favicon.ico" />
		
        
        <? $stc->Add("/css/main.css"); ?>
        <? $stc->Add("/css/nav.css"); ?>
        <? $stc->Add("/css/fl2.css"); ?>
        <? $stc->Add("/scripts/swfobject.js"); ?>
        <? $stc->Add("/scripts/player.js"); ?>
        <? $stc->Add("/scripts/warning.js"); ?>
        <? $stc->Add("/scripts/mootools-new.js"); ?>
        <? $stc->Add("/scripts/mootools-more.js"); ?>
        <? $stc->Add("/scripts/new_site.js"); ?>
        <? $stc->Add("/scripts/nav.js"); ?>
        <? $stc->Add("/scripts/navigate.js"); ?>
        <? $stc->Add("/scripts/new.js"); ?>
        <? $stc->Add("/scripts/ajax_blocks.js"); ?>
        <? $stc->Add("/scripts/csrf.js"); ?>
        <? $stc->Add("/css/block/b-input-hint/b-input-hint.js"); ?>
        <?php if( !is_array($css_file) && $css_file ) { $stc->Add( ($css_file[0]=='/' ? '' : '/css/') . $css_file ); }
        elseif ( $css_file ) { 
            foreach ($css_file as $css) { $stc->Add( ($css[0]=='/' ? '' : '/css/') . $css ); }
        } ?>
        
        <?php
        if ( !empty($js_file) ) {
        	if ( !is_array($js_file) ) { 
        	    $js_file = array( $js_file );
        	}
        	
        	foreach ( $js_file as $sStcFile ) { 
                $stc->Add( ($sStcFile[0]=='/' ? '' : '/scripts/') . $sStcFile );
            }
        }
        ?>
        
        <?= parse_additional_header($additional_header, $stc); ?>
        <? $stc->addBem(); ?>
        <? $stc->Send(); ?>
        
        
        <script type="text/javascript">
           var ___WDCPREFIX = '<?=WDCPREFIX?>';
           var _TOKEN_KEY = '<?=$_SESSION['rand']?>';
           var _UID = <?=(int) $_SESSION['uid']?>;
           <?php if(get_uid(false)) { ?>
           window.addEvent('domready', function() {
               CSRF(_TOKEN_KEY);    
           });
           <?php }//?>
        </script>
        
        <? if ($rss_file) { ?>
        <link rel="alternate" type="application/rss+xml" title="Проекты/Предложения" href="<?= $rss_file ?>" />
        <? } ?>
    </head>
    <body<?php if (isset($onload)) { ?> onload="<?= $onload ?>"<?php } ?> class="<?= cssClassBody($body_class) ?> <?= BROWSER_NAME;?> <?= $body_additional_class?> <?php if(getOS()=='Macintosh') { ?>mac<?php } ?>">
        <div class="container">
            <? if (!$no_banner) include_once (ABS_PATH . "/banner100pct.php"); ?>
            <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/consultant.php'); ?>
            <div class="header">
            <?php include (ABS_PATH . "/header.php") ?>
            <?php if (!$no_personal) include (ABS_PATH . "/personal.php") ?>
            </div>
            <?php include (ABS_PATH . "/greymenu.php") ?>
            <?php if($grey_main && $_promo_block) {?>
            <span id="qaccess_top"></span>
            <script type="text/javascript">qaccess();</script>
            <?php }//if?>
            <?php if($grey_main) {?>
            <div id="pay_place_top"></div>
            <script type="text/javascript">pay_place_top(0);</script>
            <?php } //if?>
            <div class="body c">
                <div class="main c">
                    <a name="top"></a>
                    <?php include ($content); ?>
                </div>
            </div>
            <?php
            if($seo_catalog_data) {
                include (ABS_PATH . "/catalog/main-page-block.php");
            }
            ?>
            <?php include (ABS_PATH . "/footer.html") ?>
        </div>
    <?php include_once(ABS_PATH . '/user/sex_demand.php'); ?>

    </body>
    <!-- <?= $_ENV["HOSTNAME"] ?> -->
</html>
