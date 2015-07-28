<?php 
/**
 * Модерирование пользовательского контента. Фреймы. Шаблон.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined("IN_STDF") || !defined('IS_SITE_ADMIN') ) {
    header("HTTP/1.1 403 Forbidden");
    header("location: /403.html");
	die();
}

require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/static_compress.php' );
$stc  = new static_compress(); // общий.
$stc2 = new static_compress(); // для подключаемых модулей.
$js_file = array( 'user_content.js', 'banned.js', 'adm_edit_content.js', 'swfobject.js', 'player.js', 'warning.js', 'mootools-new.js', 'mootools-more.js', 'new_site.js', 'nav.js', 'navigate.js', 'new.js', 'ajax_blocks.js', 'csrf.js', 'b-combo/b-combo-dynamic-input.js', '/css/block/b-textarea/b-textarea.js', 'b-combo/b-combo-multidropdown.js', 'b-combo/b-combo-autocomplete.js', 'b-combo/b-combo-calendar.js', 'b-combo/b-combo-manager.js', '/css/block/b-page/b-page.js', '/css/block/b-menu/b-menu.js', '/css/block/b-input-hint/b-input-hint.js', '/css/block/b-ext-filter/b-ext-filter.js', '/css/block/b-catalog/b-catalog.js', '/scripts/b-bar.js', '/css/block/b-ext-filter/b-ext-filter.js', '/css/block/b-shadow/b-shadow.js', 'highlight.min.js', 'highlight.init.js', 'attachedfiles.js', 'polls.js', 'ibox.js', 'contest.js', 'admin_log.js', 'projects.js', 'calendar.js' );
$bInFrames = true;

$sOnload = '';

if ( !empty($_COOKIE['my_streams_content_id']) && !empty($_COOKIE['my_streams_stream_id']) ) {
    $sOnload = ' onload="xajax_chooseStream(\''. $_COOKIE['my_streams_content_id'] .'\', \''. $_COOKIE['my_streams_stream_id'] .'\', 1);Cookie.dispose(\'my_streams_content_id\');Cookie.dispose(\'my_streams_stream_id\');" ';
}
?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
    <title>Фреймы</title>
    <script type="text/javascript">
        var _TOKEN_KEY = '<?=$_SESSION['rand']?>';
        var _UID = <?=(int) $_SESSION['uid']?>;
        var _QUICK_CHAT_ON = <?=intval($_SESSION['chat'])?>;
    </script>
    <link href="/css/block/style.css" rel="stylesheet" type="text/css" />
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
    <style type="text/css">
				    html,body{ height:100%;}
								html{overflow-y:hidden}
        .box-frame{box-sizing: border-box; height:100%;}
        .notice-table { width:100%}
        .notice-table td{ color:#666; border-bottom:1px solid #f0efed; padding:5px }
        .notice-table .cell-number{ width:20px; color:#999}
        .notice-table .cell-who{ text-align:right; width:auto}
        .notice-table .cell-date{ color:#999}
        .bun-button {padding: 10px 0 0;}
        .bun-button button {margin-right: 5px;}
        .cell-uwarn {
            background: url("/images/fade.png") repeat-y scroll right 0 transparent;
            overflow: hidden;
            width: 420px;
        }
        .msie .box-frame, .opera .box-frame{padding-bottom:120px;}
    </style>
</head>
    <body id="frames_body" class="b-page b-layout <?= BROWSER_NAME;?>" <?=$sOnload?>>
<?/*
<h3 id="" class="b-layout__h3 b-layout__one_bg_f7 b-layout__h3_nowrap b-layout__h3_margright_55 b-layout__h3_relative" style="display: <?=( is_array($aStreams) && count($aStreams) ? 'none' : 'block' )?>">
    <center>Нет захваченных потоков</center>
</h3>
*/?>
<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/user_content.common.php' );
$xajax->printJavascript( '/xajax/' );

if ( is_array($aStreams) && count($aStreams) ) { ?>
<table class="b-layout__table b-layout__table_height_99ps b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
<tr class="b-layout__tr" id="tr_header">
    <?php 
    $nContent  = -1;
    $aCounters = array();
    $bFirstIn  = false;
    
    foreach ( $aStreams as $aOne ) { 
        $sContentName = '';
        
        foreach ( $user_content->contents as $aContent ) {
            if ( $aContent['id'] == $aOne['content_id'] ) {
                $sContentName = $aContent['name'];
                break;
            }
        }
        
        if ( $nContent != $aOne['content_id'] ) { 
            $aCounters = array();
            $nContent  = $aOne['content_id'];
            $bFirstIn  = true;
            
            if ( $user_content->isStreamCounters( $nContent ) ) {
                $aCounters = $user_content->getStreamCounters( $nContent, false, $bShow );
            }
        }
        ?>
    <td id="th_<?=$aOne['stream_id']?>" class="b-layout__one b-layout__one_bg_f7 b-layout__one_pad_10 b-layout__one_width_330 b-layout__one_bordright_ccc b-layout__one_bordbot_ccc b-layout__one_height_100">
        <?php include( 'frames_header.php' ); ?>
    </td>
    <?php 
        $bFirstIn  = false;
    } 
    ?>
</tr>
<tr class="b-layout__tr" id="tr_frames">
    <?php foreach ( $aStreams as $aOne ) { ?>
    <td id="td_<?=$aOne['stream_id']?>" class="b-layout__one b-layout__one_height_100ps b-layout__one_width_350  b-layout__one_bordright_ccc">
    <div class="box-frame">
        <iframe id="<?=$aOne['stream_id']?>" src="/siteadmin/user_content/?site=stream&cid=<?=$aOne['content_id']?>&sid=<?=$aOne['stream_id']?>" frameborder="0" width="100%" height="100%"></iframe>
    </div>
    </td>
    <?php } ?>
</tr>
</table>
<?php
}
?>
<? /*
<script type="text/javascript">
function wSize(){
var wSize = window.getSize();
$$('#tr_frames td').setStyle('height', wSize.x-100)
};
wSize();
</script>
*/ ?>   
<?php if ( $bChooseErr ) {?><script type="text/javascript">alert('Захват потока не удался');</script><?php } ?>

<script type="text/javascript">
banned.addContext( 'admin', -1, '', '' );
user_content.currUid = <?=$uid?>;
user_content.spinner = new Spinner('my_div_all');
adm_edit_content.prj_specs = new Array();
adm_edit_content.WDCPREFIX = '<?=WDCPREFIX?>';
setTimeout('xajax_otherCounters();', <?=user_content::MODER_OTHER_CNT_REFRESH?> * 1000 );
<?=$sSpecs?>
<?php if ( is_array($aStreams) && count($aStreams) ) {
    foreach ( $aStreams as $aOne ) { 
?>user_content.addSoundControl('<?=$aOne['stream_id']?>');<?php
    }
} ?>
</script>

<?php
include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
include_once( $_SERVER['DOCUMENT_ROOT'] . '/siteadmin/admin_log/warn_overlay.php' );
include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/del_overlay.php' );
include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_overlay.php' );
?>
    
</body>
</html>
