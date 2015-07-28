<?php
// Основной шаблон сайта

if (!defined("IN_STDF")){
    header("HTTP/1.1 403 Forbidden");
    header("location: /403.html");
	die();
}
if (!$page_title) {
    $page_title = "Удаленная работа (фри-ланс) на FL.ru";
}
if (!$page_keyw) {
    $page_keyw = "работа, ищу работу, поиск работы, удаленная работа, фриланс, фри-ланс";
}
if (!$page_descr) {
    $page_descr = "FL.ru это профессиональный ресурс, предназначенный для поиска работы или исполнителя (фрилансера) на удаленную работу (фриланс).";
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/user_phone.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/Helpers/SubBarNotificationHelper.php");

$stc = new static_compress(); // общий.
$stc2 = new static_compress(); // для подключаемых модулей.

$stc_js = new static_compress(); // JS-файлы
$stc2_js = new static_compress(); // JS-файлы для подключаемых модулей

if (!isset($promo)) $promo = true;

//Глобальные переменные которые могут несуществовать и нужно это проверить
$main_page = isset($main_page)?$main_page:false;
$freelancers_catalog = isset($freelancers_catalog)?$freelancers_catalog:false;

$_user = new users();
$_user->GetUserByUID(get_uid(false));

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head><?php
            $yaBaseTestpage = "/interview/86/";
            if ( strpos($_SERVER['REQUEST_URI'], $yaBaseTestpage) !== false ) { ?>
            <?php $pattern = "#Mozilla/5.0 \(compatible; Yandex[\w]*/?([0-9]+\.[0-9]+)?;(\srobot|\sDyatel|\s?);? \+http://yandex.com/bots\)#"; // http://help.yandex.ru/webmaster/?id=995329
                  if ( preg_match($pattern, $_SERVER['HTTP_USER_AGENT'] ) ) {
                      ?><base href="http://<?=$_SERVER['HTTP_HOST'] ?>" /><?
                  }
            ?>
        <?php }?>
        <meta charset="windows-1251" />
        <meta content="initial-scale=1, width=device-width, user-scalable=no" name="viewport" />
        <meta name="description" lang="ru" content="<?= change_q_x($page_descr) ?>" />
        <meta name="keywords" lang="ru" content="<?= change_q_x($page_keyw) ?>" />
        <?php if($main_page) { ?><meta name="cmsmagazine" content="85293268c28a6790c0611c744d47631b" /><? } ?>
        <?php if($main_page) { ?>
          <meta name='yandex-verification' content='408722b52391250b' />
          <meta name="google-site-verification" content="gO1LczHLkd33btoSSOdJeq4VRVYb2g--uwpAsLQD8Ms" />
		<? } ?>
        <?php if(!empty($FBShare)) { ?>
        <?= printMetaFBShare($FBShare); ?>
        <?php }//if?>
        <?php if (isset($canonical_url) && $canonical_url): // канонический URL для SEO ?>
            <link rel="canonical" href="<?=$canonical_url?>" />
        <?php endif; ?>
        <title><?= $page_title ?></title><?php $quick_chat_on = intval($_SESSION['chat']);?>
        <script type="text/javascript">
            var CKEDITOR_BASEPATH = '/scripts/ckedit/';
            var _TOKEN_KEY = '<?=$_SESSION['rand']?>';
            var _UID = <?=(int) $_SESSION['uid']?>;
            var _QUICK_CHAT_ON = <?=$quick_chat_on ?>;
        </script>
        <link rel="icon" href="/favicon.ico" type="image/x-icon">
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
            
            
        <? $stc_js->Add("/scripts/adriver.core.2.js"); ?>
        <? $stc_js->Add("/scripts/swfobject.js"); ?>
        <? $stc_js->Add("/scripts/warning.js"); ?>
        <? $stc_js->Add("/scripts/mootools-new.js"); ?>
        <? $stc_js->Add("/scripts/mootools-more.js"); ?>
        <? $stc_js->Add("/scripts/mootools-Assets.js"); ?>
        <? $stc_js->Add("/css/block/b-banner/b-banner.js"); ?>
        <? $stc_js->Add("/scripts/swfobject_launcher.js"); ?>
        <? $stc_js->Add("/scripts/new_site.js"); ?>   
        <? $stc_js->Add("/scripts/nav.js"); ?>
        <? $stc_js->Add("/scripts/navigate.js"); ?>
        <? $stc_js->Add("/scripts/new.js"); ?>
        <? $stc_js->Add("/scripts/ajax_blocks.js"); ?>
        <? $stc_js->Add("/scripts/csrf.js"); ?>
        <? $stc_js->Add("/scripts/kwords.js"); ?>
        <? $stc_js->Add("/kword_js.php"); ?>
        <? $stc_js->Add("/professions_js.php"); ?>
        <? $stc_js->Add("/cities_js.php"); ?>
        <? $stc_js->Add("/kword_search_js.php?type=projects");?>
        <? $stc_js->Add("/scripts/b-combo/b-combo-dynamic-input.js"); ?>
        <? $stc_js->Add("/css/block/b-textarea/b-textarea.js"); ?>
        <? $stc_js->Add("/scripts/b-combo/b-combo-multidropdown.js"); ?>
        <? $stc_js->Add("/scripts/b-combo/b-combo-autocomplete.js"); ?>
        <? $stc_js->Add("/scripts/b-combo/b-combo-calendar.js"); ?>
        <? $stc_js->Add("/scripts/b-combo/b-combo-manager.js"); ?>      
        <? $stc_js->Add("/scripts/banners.js"); ?>   
				
        <? $stc_js->Add("/css/block/b-page/b-page.js"); ?>
        <? $stc_js->Add("/css/block/b-menu/b-menu.js"); ?>
        <? $stc_js->Add("/css/block/b-ext-filter/b-ext-filter.js"); ?>
        <? $stc_js->Add("/css/block/b-catalog/b-catalog.js"); ?>
        <? $stc_js->Add("/css/block/b-ext-filter/b-ext-filter.js"); ?>

        <? $stc_js->Add("/scripts/uploader_launcher.js"); ?>
        

        <? if($css_file) { foreach ((array)$css_file as $css) { $stc2->Add( ($css[0]=='/' ? '' : '/css/') . $css ); } } ?>
        <? if($js_file) { foreach ((array)$js_file as $js) { $stc2_js->Add( ($js[0]=='/' ? '' : '/scripts/') . $js ); } } ?>
        <? if($js_file_utf8) { foreach ((array)$js_file_utf8 as $js) { $stc2_js->Add( ($js[0]=='/' ? '' : '/scripts/') . $js, true); } } ?>
            
        <?
           //Новая шапка
           //уже со встроенной в стилях адаптивностью
           //которую получается нельзя отключить
           $stc_js->Add("/scripts/jquery.js");
           $stc_js->Add("/scripts/bar.js");
           $stc_js->Add("/scripts/bar_ext.js");
        ?>

        <?php $stc_js->Add("/scripts/ga_actions.js"); ?>
        <?php    
            //Парсим и добавляем css
            parse_additional_header($additional_header, $stc2, 'css');
            //Парсим и добавляем js + выводим другие заголовки
            echo parse_additional_header($additional_header, $stc2_js, 'js');
        ?>
        <? $stc->addBem(); ?>
        <?php
           //Адаптивность которую видимо можно выключать
           if(@$_COOKIE['full_site_version'] != 1 && 
              !isset($show_full_site_version) && 
              @$_SESSION['pda'] == 1):
                $stc->Add("/css/portable.css");
                $stc_js->Add("/scripts/portable.js");
           endif; 
           
           //@todo: для Локала и Беты принудительно включаем 
           //адаптивность для всех чтобы было проще проверять верстку
           if (is_local() || is_beta()) {
               $stc->Add("/css/portable.css");
               $stc_js->Add("/scripts/portable.js");
           }
           
        ?>
        <? $stc->Send(); ?>
        <? $stc2->Send(); ?>
        
        <?php if (!defined('JS_BOTTOM')): // Отображаем JS в хедере страниц @TODO: Убрать на всех страницах JS в нижнюю часть ?>            
            <?php $stc_js->Send(); ?>
            <?php $stc2_js->Send(); ?>

            <?php
                // Подключаем код Google Analytics 
                include($_SERVER['DOCUMENT_ROOT'].'/templates/include/ga.php'); 
            ?>
        <?php endif ?>

        <script type="text/javascript">
           var ___WDCPREFIX = '<?=WDCPREFIX?>';
           var _NEW_TEMPLATE = true;
           <?php if(!$main_page) { ?>
           var _SHORT_CAROUSEL = true;           
           <?php } ?>
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
        
        <? if ($rss_file) { ?>
        <link rel="alternate" type="application/rss+xml" title="Проекты/Предложения" href="<?= $rss_file ?>" />
        <? } ?>
        <?php
            $freelance = "#^[a-w]*\.?free\-lance\.ru/?.?#";
            $fl = "#^[a-w]*\.?fl\.ru/?.?#";
        ?>
    </head>
    <?
    // высоты различных блоков
    define('HEIGHT_PHONE', 190); // высота блка привязки телефона
    ?>
    <body <?php if (isset($onload)) { ?> onload="<?= $onload ?>"<?php } ?> class="b-page <?= cssClassBody($body_class) ?> <?= BROWSER_NAME;?> <?= $body_additional_class?> <?php if(getOS()=='Macintosh') { ?>mac<?php } ?>">
        <?php 
        // подарки и переводы
        if ($_SESSION['uid'] && !$no_personal) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/present.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
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
                }
            }
        }
        if (!$no_personal) include (ABS_PATH . "/templates/personal.php") ?>
<?php
        // флаг, указывает что сплэш уже определен, чтобы не показывать больше одного сплэша за раз
        $splashDefined = false;
        
        $no_phone_block = !(user_phone::getInstance()->checkAllow() && user_phone::getInstance()->_use_header == true);
        $no_tu_block = !(get_uid(false) && @$_SESSION['has_new_tservices_orders']);
        unset($_SESSION['has_new_tservices_orders']);
        $is_not_show_notification = !SubBarNotificationHelper::getInstance()->isShow();
        
        $padTop = ($no_phone_block && $no_tu_block && $is_not_show_notification) ? 60 : 105;//80 : 125;
        if (isset($landing_page) && $landing_page) {
            $padTop = 80;
            if(isset($content_landing_image) && !empty($content_landing_image)) {
                $padTop = 35;
                include ($content_landing_image);
            }
        }
        
        
        //Контентая область на всю ширину страницы
        if (isset($full_content)):
?>
        <div class="b-page__page b-page__page_padtop_0_r600 b-page__page_padtop_50_r1000 b-page__page_padtop_<?=$padTop-10?>">
            <?php include ($full_content); ?>
        </div>
        <?php if(!isset($hide_footer)): ?>
        <div class="b-page__wrapper">
            <div class="b-page__inner">
                <div class="b-page__page">
                    <?php include (ABS_PATH . "/footer.new.html");?>
                </div>    
            </div>
        </div>
        <?php endif; ?>
<?php            
        else:
        //Обычная фиксированная контентная область    
?>
        <div class="b-page__wrapper">
            <div class="b-page__inner">
                <div class="b-page__page b-page__page_padtop_20_r600 b-page__page_padtop_75_r1000 b-page__page_padtop_<?=$padTop?>">
                    <?php if (!isset($landing_page) && !isset($hide_banner_top) && !defined('IS_SITE_ADMIN')): ?>
                    <div class="b-layout b-layout_relative b-layout_padbot_20 b-layout__desktop">
                        <div id="banner_top" data-sid="<?=BANNER_ADRIVER_SID?>"></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!isset($landing_page) && !defined('IS_SITE_ADMIN')): ?>
                        <?php include (ABS_PATH . '/templates/top/new_project_button.php'); ?>
                    <?php endif; ?>
                    
                    <?php
                        
                        $extraMarginTop = 0;
                        
                        // блок устаревший браузер
                        if (!isset($_COOKIE['browserCompatWrn']) && !BROWSER_COMPAT && !$browser_outdated_page) {
                            include_once($_SERVER['DOCUMENT_ROOT'].'/templates/browser.php');
                            $extraMarginTop += 51;
                        }
                        
                        $countGifts = count($gifts);
                        if ($countGifts > 0) {
                            include($_SERVER['DOCUMENT_ROOT'].'/templates/gift.php');
                            $extraMarginTop += 60;
                        }
                        
                        // отступ для основного блока
                        $mainMarginTop = 0;
                    ?>
                    <div class="b-layout b-layout__page" style="margin-top:<?= $mainMarginTop ?>px">
                    <?php if(!$main_page && !$browser_outdated_page && (!$stretch_page || $showMainDiv == true)) { ?>
                    <div class="body">
                        <div class="main">
                    <?php }//if?>
                        <a name="top"></a>
                        <?php include ($content); ?>
                    <?php if(!$main_page && !$browser_outdated_page && (!$stretch_page || $showMainDiv == true)) { ?>
                        </div>
                    </div>
                    <?php }//if?>
                    </div>
                    
                    <?php if(!isset($hide_footer)): ?>
                        <?php include (ABS_PATH . "/footer.new.html");?>
                    <?php endif; ?>
                </div><!--b-page__page-->
            </div>
        </div>
<?php

    endif;
    
?>
        
        
        
        
        
        
        <? // этот банер показывается в режиме демонстрации ПРО для НЕПРО
        if ($iWantPro) { ?>
            <div class="b-fix b-fix_bordbot_solid_e7cca5 b-fix_top_30 b-fix_bg_ffebbf b-fix_width_full b-fix_padtb_10">
                <div class="b-layuot b-layout_center b-layuot_max-width_1280 b-layuot_min-width_1000">
                    <table class="b-layout__table b-layout__table_width_full">
                        <tr class="b-layout__tr">
                            <td class="b-layout__one b-layout__one_width_2ps">&#160;</td>
                            <td class="b-layout__one b-layout__one_valign_middle">
                                <span title="PRO" class="b-icon b-icon__mpro b-icon__mpro_f"></span>
                            </td>
                            <td class="b-layout__one b-layout__one_valign_middle">
                                <div class="b-layout__txt b-layout__txt_padleft_10 b-layout__txt_bold">Если бы у вас был<br />PRO-аккаунт, то:</div>
                            </td>
                            <td class="b-layout__one b-layout__one_valign_middle">
                                <div class="b-layout__txt b-layout__txt_padleft_15 b-layout__txt_indent_-15">1. Неограниченное количество<br />ответов на проекты.</div>
                            </td>
                            <td class="b-layout__one b-layout__one_valign_middle">
                                <div class="b-layout__txt b-layout__txt_padleft_15 b-layout__txt_indent_-15">2. Бесплатная реклама вашего<br />профиля в проектах работодателей.</div>
                            </td>
                            <td class="b-layout__one b-layout__one_valign_middle">
                                <a href="/payed/" class="b-button b-button_float_right b-button_round_green">
                                    <span class="b-button__b1">
                                        <span class="b-button__b2">
                                            <span class="b-button__txt">Купить PRO</span>
                                        </span>
                                    </span>
                                </a>
                            </td>
                            <td class="b-layout__one b-layout__one_width_2ps">&#160;</td>
                        </tr>
                    </table>
                </div>
            </div>
        <? } ?>

        <div id="popups_container">
            <?=user_phone::getInstance()->renderPopup() ?>
        </div>

        <?php if (defined('JS_BOTTOM')): // Формиирование JS (склеивание) ?>
            <?php $stc_js->Send(); ?>
            <?php $stc2_js->Send(); ?>

            <?php
                // Подключаем код Google Analytics 
                include($_SERVER['DOCUMENT_ROOT'].'/templates/include/ga.php'); 
            ?>
        <?php endif; ?>
        
        <script type="text/javascript">
            window.addEvent('domready', function() {
                CSRF(_TOKEN_KEY);
                <?php if (get_uid(false)): ?>
                    $$('.b-dropdown-concealment-options-switch-clause').getElement('.b-dropdown-concealment-options-clause-link').addEvent('click',function(){window.scrollTo(0,0);})
                <?php endif ;?>
            });
        </script>

        <?php include(dirname(__FILE__).'/templates/include/counters.php'); ?>
        
        <?php if (isset($use_livetex)): ?>
            <?php include(dirname(__FILE__).'/templates/include/livetex.php'); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['reg_role']) && in_array($_SESSION['reg_role'], array('Employer', 'Freelancer'))): ?>
            <?php include(ABS_PATH . '/templates/include/reg_event.php'); ?>
            <?php unset($_SESSION['reg_role']);?>
        <?php endif; ?>
    </body>
</html>