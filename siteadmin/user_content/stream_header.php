<?php
if (!defined("IN_STDF")){
    header("HTTP/1.1 403 Forbidden");
    header("location: /403.html");
	die();
}
if (!$page_title) {
    $page_title = "Удаленная работа (фри-ланс) на Free-lance.ru";
}
if (!$page_keyw) {
    $page_keyw = "работа, ищу работу, поиск работы, удаленная работа, фри-ланс";
}
if (!$page_descr) {
    $page_descr = "Free-lance.ru это профессиональный ресурс, предназначенный для поиска работы или исполнителя (фрилансера) на удаленную работу (фри-ланс).";
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
$stc  = new static_compress(); // общий.
$stc2 = new static_compress(); // для подключаемых модулей.

/*@mark_0013129*/
$page_keyw  = $page_title;
$page_descr = $page_title;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
        <meta name="description" lang="ru" content="<?= $page_descr ?>" />
        <meta name="keywords" lang="ru" content="<?= $page_keyw ?>" />
        <title><?= $page_title ?></title>
        <script type="text/javascript">
            var _TOKEN_KEY = '<?=$_SESSION['rand']?>';
            var _UID = <?=(int) $_SESSION['uid']?>;
            var _QUICK_CHAT_ON = <?=intval($_SESSION['chat'])?>;
        </script>
        <link rel="shortcut icon" href="/favicon.ico" />
        <? $stc->Add("/css/nav.css"); ?>
        <? $stc->Add("/scripts/swfobject.js"); ?>
        <? $stc->Add("/scripts/warning.js"); ?>
        <? $stc->Add("/scripts/mootools-new.js"); ?>
        <? $stc->Add("/scripts/mootools-more.js"); ?>
        <? $stc->Add("/scripts/new_site.js"); ?>   
        <? $stc->Add("/scripts/nav.js"); ?>
        <? $stc->Add("/scripts/navigate.js"); ?>
        <? $stc->Add("/scripts/new.js"); ?>
        <? $stc->Add("/scripts/ajax_blocks.js"); ?>
        <? $stc->Add("/scripts/csrf.js"); ?>
        <? $stc->Add("/scripts/kwords.js"); ?>
        <? $stc->Add("/kword_js.php"); ?>
        <? $stc->Add("/professions_js.php"); ?>
        <? $stc->Add("/cities_js.php"); ?>
        <? $stc->Add("/kword_search_js.php?type=projects");?>
        <? $stc->Add("/scripts/b-combo/b-combo-dynamic-input.js"); ?>
        <? $stc->Add("/css/block/b-textarea/b-textarea.js"); ?>
        <? $stc->Add("/scripts/b-combo/b-combo-multidropdown.js"); ?>
        <? $stc->Add("/scripts/b-combo/b-combo-autocomplete.js"); ?>
        <? $stc->Add("/scripts/b-combo/b-combo-calendar.js"); ?>
        <? $stc->Add("/scripts/b-combo/b-combo-manager.js"); ?>      
        <? $stc->Add("/css/block/b-page/b-page.js"); ?>
        <? $stc->Add("/css/block/b-menu/b-menu.js"); ?>
        <? $stc->Add("/css/block/b-input-hint/b-input-hint.js"); ?>
        <? $stc->Add("/css/block/b-ext-filter/b-ext-filter.js"); ?>
        <? $stc->Add("/css/block/b-catalog/b-catalog.js"); ?>
        <? $stc->Add("/scripts/b-bar.js"); ?>
        <? $stc->Add("/css/block/b-ext-filter/b-ext-filter.js"); ?>
        <? $stc->Add("/css/block/b-chat/b-chat.css"); ?>
        <? $stc->Add("/scripts/b-chat2.js"); ?>        
        
        <? if($css_file) { foreach ((array)$css_file as $css) { $stc2->Add( ($css[0]=='/' ? '' : '/css/') . $css ); } } ?>
        <? if($js_file) { foreach ((array)$js_file as $js) { $stc2->Add( ($js[0]=='/' ? '' : '/scripts/') . $js ); } } ?>
        <? if($js_file_utf8) { foreach ((array)$js_file_utf8 as $js) { $stc2->Add( ($js[0]=='/' ? '' : '/scripts/') . $js, true); } } ?>
            
        <?= parse_additional_header($additional_header, $stc2); ?>
        <? $stc->addBem(); ?>
        <? $stc->Send(); ?>
        <? $stc2->Send(); ?>
        
        <script type="text/javascript">
           var ___WDCPREFIX = '<?=WDCPREFIX?>';
           var _NEW_TEMPLATE = true;
           <?php if(get_uid(false)) { ?>
           window.addEvent('domready', function() {
               CSRF(_TOKEN_KEY);    
           });
           <?php }//?>
        </script>
        
        <?php include($_SERVER['DOCUMENT_ROOT'].'/templates/include/counters.php'); ?>
        
        <?php // не нашелся в какой файл стилей это приткнуть - оно вроде только тут нужно ?>
        <style>
            .notice {
                border-bottom: 1px dotted #FF7200;
                color: #FF7200;
                font-weight: normal;
                font-size: 11px;
                text-decoration: none;
            }
            
            .user-notice {
                color: #666666;
                padding-top: 5px;
                font-weight: normal;
                font-size: 11px;
            }
        </style>
    </head>
    <body class="b-page">
