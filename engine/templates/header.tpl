<?php if(defined('NEO')) { xFront::creaker()->property()->engine_vars = front::og('tpl')->gets(); } ?>
<?php if(!defined('NEO')) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<? 
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/adriver.php");
$g_page_id = $$g_page_id;
$no_banner = $$no_banner;
$stc = new static_compress;
/*@mark_0013129*/
if(!$$page_title) $page_title = "Удаленная работа (фри-ланс) на Free-lance.ru";
else $page_title = $$page_title;
$page_keyw  = $page_title;
$page_descr = $page_title;
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
     <head>
          <title><?= $page_title; ?></title>
          <meta name="description" lang="ru" content="<?= $page_descr; ?>" />
          <meta name="keywords" lang="ru" content="<?= $page_keyw; ?>" />
          <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
          <?php if(!empty($$FBShare)) { ?>
          <?= printMetaFBShare($$FBShare); ?>
          <?php }//if?>
          <link rel="shortcut icon" href="/favicon.ico" />
          <script type="text/javascript">
              var CKEDITOR_BASEPATH = '/scripts/ckedit/';
              var _TOKEN_KEY = '<?=$_SESSION['rand']?>';
              var _UID = <?=(int) $_SESSION['uid']?>;
              var _QUICK_CHAT_ON = <?=intval($_SESSION['chat']) ?>;
          </script>
          <? if(hasPermissions('about')) : ?>
          <script type="text/javascript"> var INLINE_ADMIN = true; </script>
          {{include "admin_include.tpl"}}
          <? endif; ?>
          <? if($$main_css): ?>
          <? $stc->Add($$main_css); ?>
          <? endif; ?>
          <? if(!is_array($$css) && $$css != ""): ?>
            <? $stc->Add( ($$css[0]=='/' ? '' : '/css/') . $$css ); ?>
          <? elseif($$css): ?>
          <? foreach($$css as $k=>$css):?>
            <? $stc->Add( ($css[0]=='/' ? '' : '/css/') . $css ); ?>
          <? endforeach; ?>
          <? endif; ?>
          <? $stc->Add("/css/nav.css"); ?>
          <? $stc->addBem(); ?>
		  
		      <? $stc->Add("/scripts/adriver.core.2.js"); ?>
          <? $stc->Add("/scripts/swfobject.js"); ?>
          <? $stc->Add("/scripts/warning.js"); ?>
          <? $stc->Add("/scripts/mootools-new.js"); ?>
          <? $stc->Add("/scripts/mootools-more.js"); ?>
          <? $stc->Add("/scripts/new_site.js"); ?> 
          <? $stc->Add("/scripts/nav.js"); ?>
          <? $stc->Add("/scripts/navigate.js"); ?>
          <? $stc->Add("/scripts/ajax_blocks.js"); ?>
          <? $stc->Add("/scripts/new.js"); ?>   
          <? $stc->Add("/scripts/csrf.js"); ?> 
          <? $stc->Add("/scripts/b-bar.js"); ?>
          <? $stc->Add("/css/block/b-input-hint/b-input-hint.js"); ?>
          <? $stc->Add("/css/block/b-menu/b-menu.js"); ?>
          <? $stc->Add("/scripts/kwords.js"); ?>
          <? $stc->Add("/kword_js.php"); ?>
          <? $stc->Add("/kword_search_js.php?type=" . ( is_emp() || !get_uid(false) ? 'users' : 'projects'));?>
          <? $stc->Add("/css/block/b-chat/b-chat.css"); ?>
          <? $stc->Add("/scripts/b-chat2.js"); ?>
          <? $stc->Add("/scripts/jquery.js"); ?>
          <? $stc->Add("/scripts/bar.js"); ?>
          <? $stc->Add("/scripts/bar_ext.js"); ?>
          <?php
           if ( !empty($$script) ) {
                if ( !is_array($$script) ) {
                    $$script = array( $$script );
                }
    
                foreach ( $$script as $sStcFile ) {
     	            $stc->Add( ($sStcFile[0]=='/' ? '' : '/scripts/') . $sStcFile );
     	        }
           }
            if (!empty($$script_utf8)) {
                if (!is_array($$script_utf8)) {
                    $$script_utf8 = array($$script_utf8);
                }

                foreach ($$script_utf8 as $sStcFile) {
                    $stc->Add(($sStcFile[0] == '/' ? '' : '/scripts/') . $sStcFile, true);
                }
            }
          ?>
          
          <? $stc->Send(); ?>
          
          
          
          <? switch ($kind) {
               case 0: case 1: ?><link rel="alternate" type="application/rss+xml" title="Проекты/Предложения" href="/rss/projects.xml" /><? break;
               case 2: ?><link rel="alternate" type="application/rss+xml" title="Проекты/Предложения" href="/rss/competition.xml" /><? break;
               case 3: ?><link rel="alternate" type="application/rss+xml" title="Проекты/Предложения" href="/rss/partnership.xml" /><? break;
               case 4: ?><link rel="alternate" type="application/rss+xml" title="Проекты/Предложения" href="/office.xml" /><? break;
          } ?>
          <script type="text/javascript">
          var ___WDCPREFIX = '<?=WDCPREFIX?>';
           <?php if(get_uid(false)) { ?>
           window.addEvent('domready', function() {
               CSRF(_TOKEN_KEY);    
           });
           <?php }//?>
           <? // индекс страницы
           if (!isset($g_help_id) && isset($g_page_id)) {
               $page_index = explode('|', $g_page_id);
               if ($page_index[1]) {
                   $g_help_id = $page_index[1];
               }
           }
           if (!isset($g_help_id)) { 
           	   $g_help_id = 0;
           }?>
                var _G_HELP_ID = <?= (int)$g_help_id ?>;
                <?= adriver::getInstance()->target(); ?>
        </script>
        <script type="text/javascript">
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-163162-1']);
            _gaq.push(['_addOrganic', 'images.yandex.ru', 'text', true]);
            _gaq.push(['_addOrganic', 'blogsearch.google.ru', 'q', true]);
            _gaq.push(['_addOrganic', 'blogs.yandex.ru', 'text', true]);
            _gaq.push(['_addOrganic', 'go.mail.ru',  'q']);
            _gaq.push(['_addOrganic', 'nova.rambler.ru', 'query']);
            _gaq.push(['_addOrganic', 'nigma.ru', 's']);
            _gaq.push(['_addOrganic', 'webalta.ru', 'q']);
            _gaq.push(['_addOrganic', 'aport.ru', 'r']);
            _gaq.push(['_addOrganic', 'poisk.ru', 'text']);
            _gaq.push(['_addOrganic', 'km.ru', 'sq']);
            _gaq.push(['_addOrganic', 'liveinternet.ru', 'ask']);
            _gaq.push(['_addOrganic', 'quintura.ru', 'request']);
            _gaq.push(['_addOrganic', 'search.qip.ru', 'query']);
            _gaq.push(['_addOrganic', 'gde.ru', 'keywords']);
            _gaq.push(['_addOrganic', 'gogo.ru', 'q']);
            _gaq.push(['_addOrganic', 'ru.yahoo.com', 'p']);
            _gaq.push(['_addOrganic', 'akavita.by', 'z']);
            _gaq.push(['_addOrganic', 'tut.by', 'query']);
            _gaq.push(['_addOrganic', 'all.by', 'query']);
            _gaq.push(['_addOrganic', 'meta.ua', 'q']);
            _gaq.push(['_addOrganic', 'bigmir.net', 'q']);
            _gaq.push(['_addOrganic', 'i.ua', 'q']);
            _gaq.push(['_addOrganic', 'online.ua', 'q']);
            _gaq.push(['_addOrganic', 'a.ua', 's']);
            _gaq.push(['_addOrganic', 'ukr.net', 'search_query']);
            _gaq.push(['_addOrganic', 'search.com.ua', 'q']);
            _gaq.push(['_addOrganic', 'search.ua', 'query']);
            _gaq.push(['_addOrganic', 'search.ukr.net', 'search_query']);
            _gaq.push(['_trackPageview']);

            (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
            })();
        </script>
         <meta content="initial-scale=1, width=device-width, user-scalable=no" name="viewport"> 
         <link charset="utf-8" href="/css/portable.css" type="text/css" rel="stylesheet">
         <script src="/scripts/jquery.js"></script>
         <script>jQuery.noConflict();</script>
         <script charset="utf-8" src="/scripts/portable.js"></script>
     </head>
     <body class="<?= BROWSER_NAME?> b-page">
        <?php //как только все, что в engine растянем удалю это ?>
        <div class="b-page__wrapper">
            <div class="b-page__inner">
                <div class="b-page__page b-page__page_padtop_<?=(!$no_banner?'125':'30')?>">
                    <?  $extraMarginTop = 0;
                        // блок привязки к телефону
                        if ($_SESSION['uid'] && !$_SESSION['safety_phone_hide'] && users::isSafetyPhoneShow($_SESSION['uid']) && strpos($_SERVER['REQUEST_URI'], "/registration") === false) { 
                            include_once($_SERVER['DOCUMENT_ROOT'].'/user/safety_phone.php');
                            $extraMarginTop += 210;
                        }
                        // блок устаревший браузер
                        if (!isset($_COOKIE['browserCompatWrn']) && !BROWSER_COMPAT && !$browser_outdated_page) {
                            include_once($_SERVER['DOCUMENT_ROOT'].'/templates/browser.php');
                            $extraMarginTop += 51;
                        }
                        // подарки и переводы
                        if ($_SESSION['uid'] && !$no_personal) {
                            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/present.php");
                            $gifts = present::GetLastGiftByUid($_SESSION['uid']);
                            $accountCredited = 0; 
                            foreach ($gifts as $gift) {
                                if ($gift['op_code'] == 23) { //перевод средств от пользователя
                                    $accountCredited = 1;
                                }
                                if ($gift['op_code'] == 38) { //перевод за сделку без риска 
                                    $accountCredited = 1;
                                }
                                if ($gift['op_code'] == 12) { //возврат денег за рассылку и прочие зачисление
                                    $accountCredited = 1;
                                }
                            }
                            if (!$accountCredited) {
                              $accountCredited = account::GetNewMoneyBack($_SESSION['uid'], $lastId, $currentId);
                              if ($accountCredited) {
                                  $accountToolTip = "Возврат средств за рассылку";
                                  account::SetNewMoneyBack($_SESSION['uid'], $currentId);
                              }
                            }
                        }
                        $countGifts = count($gifts);
                        if ($countGifts > 0) {
                            include($_SERVER['DOCUMENT_ROOT'].'/templates/gift.php');
                            $extraMarginTop += 60;
                        }
                        //if(is_emp()) include(ABS_PATH . "/templates/splash/splash-emp-bill.tpl.php");
                    ?>
                    <div class="b-layout b-layout__page">
                    <? $host = $$host; ?>
                    <div class="body">
                        <div class="main">
<?php } // if(!defined('NEO')) ?>
