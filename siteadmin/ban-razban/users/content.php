<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

$reasons = array (
    0 => 'Не указана',
    1 => 'Некорректное поведение на сайте',
    2 => 'Спам в блогах',
    3 => 'Спам в проектах'
);

include 'head.php';

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.common.php");
$xajax->printJavascript('/xajax/');

function buildNavigation($iCurrent, $iStart, $iAll, $sHref) {
    $sNavigation = '';
	for ($i=$iStart; $i<=$iAll; $i++) {
		if ($i != $iCurrent) {
			$sNavigation .= "<a href=\"".$sHref.$i."\" >".$i."</a> ";
		}else {
			$sNavigation .= '<b style="margin-right: 5px">'.$i.'</b>';
		}
	}
	return $sNavigation;
}

?>

<script type="text/javascript">
banned.addContext( 'admin', -1, '', '' );
</script>
<script type="text/javascript">

var openBox = null;

function WaitScreen() {
    return '<div style="width: 100%; text-align: center"><br /><img src="/images/load_fav_btn.gif" width="24" height="24" border="0"><br />&nbsp;</div>';
}

function CloseWarns() {
    if (openBox) {
        document.getElementById('warnlist-'+openBox).innerHTML = WaitScreen();
        document.getElementById('u-warn-box-'+openBox).style.display = 'none';
        openBox = null;
    }
}

function OpenWarns(uid) {
    CloseWarns();
    document.getElementById('u-warn-box-'+uid).style.display = 'block';
    openBox = uid;
    xajax_GetWarns(uid, 'siteadmin');
}

</script>

<? if (empty($banned)) { print "<center><div style='font: bold 16px Tahoma'>Нет пользователей.</div></center>"; return; } ?>


<? foreach ($banned as $user) { ?>

<div class="u-line">&nbsp;</div>
<div class="u-uid"><span><?=$user['uid']?></span></div>

<div id="u-warn-box-<?=$user['uid']?>" class="u-warn-box" style="display: none">
<div class="u-warn-box-outset">
    <a href="javascript:;" onclick="CloseWarns();" title="Закрыть" class="u-popup-close">Закрыть</a>

    <div id="warnlist-<?=$user['uid']?>">
    
    <div style="width: 100%; text-align: center">
        <br />
        <img src="/images/load_fav_btn.gif" width="24" height="24" border="0">
        <br />
        &nbsp;
    </div>
    
    </div>
    
</div>
</div>

<table cellpadding="0" cellspacing="0" border="0" class="u-box">
<tr>

    <td class="u-avatar"><?=view_avatar($user['login'], $user['photo'], 1)?></td>

    <td class="u-l-info">
        <div class="u-<?=(is_emp($user['role'])? 'emp': 'frl')?>-login">
            <?=(($user['is_pro'] == 't')? view_pro2($user['is_pro_test'] == 't'): '')?>
            <?=$session->view_online_status($user['login'])?>
            <a href="/users/<?=$user['login']?>/"><?=YellowLine($user['uname'])?> <?=YellowLine($user['usurname'])?></a> [<a href="/users/<?=$user['login']?>/"><?=YellowLine($user['login'])?></a>]
        </div>
        <div>Зарегистрирован: <?=dateFormat("d.m.Y", $user['reg_date'])?></div>
        <div style="margin-top: 4px">Предупреждений: <span id="warncount1-<?=$user['uid']?>"><?=intval($user['warn'])?></span></div>
    <? if ($user['is_banned'] || $user['ban_where'] > 0) { ?>
        <? if ($user['from']) { ?><div><b><?=((($user['ban_where'] == 1)?'Заблокирован в блогах': 'Забанен').': '.dateFormat('d.m.Y H:i', $user['from']))?></b></div><? } ?>
    </td>
    <td>
        <div><b><?=((($user['ban_where'] == 1)? 'Блоги закрыты ': 'Аккаунт заблокирован ').($user['to']? ('до: '.dateFormat('d.m.Y H:i', $user['to'])): 'навсегда'))?></b></div>
        <div style="margin-top: 7px"><b>Причина бана:</b> <?=$reasons[(int) $user['ban_reason']]?></div>
        <? if ($user['admin_comment']) { ?><div><b>Комментарий модератора:</b> <?=$user['admin_comment']?></div><? } ?>
    </td>
    <? } else { ?>
    </td>
    <td>&nbsp;</td>
    <? } ?>
    </td>

</tr>
<tr class="u-bottom">

<td class="u-warns" colspan="2"><div><a href="javascript:;" onclick="OpenWarns(<?=$user['uid']?>);">Предупреждения</a> <span id="warncount2-<?=$user['uid']?>"><?=intval($user['warn'])?></span></div></td>

<td class="u-bans">
<?php
$sBanTitle = (!$user['is_banned'] && !$user['ban_where']) ? 'Забанить!' : 'Разбанить';
?>
<div class="warnlink-<?=$user['uid']?>"><a class="admn" href="javascript:void(0);" onclick="banned.userBan(<?=$user['uid']?>, 'admin',0)"><?=$sBanTitle?></a> <? if ($user['admin']) { ?><span>Заблокировал: <?=$user['admin_login']?></span><? } ?></div>
</td>

</table>

<? } ?>

<table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 20px">
<tr>

    <td align="left" width="100%">
    <div id="fl2_paginator"><?

        // Страницы
        $pages = ceil($nums / USERS_ON_PAGE);
        if ($pages > 1) {
            $maxpages = $pages;
            $i = 1;
            $sHref = './?mode=users'.($sort? "&sort=$sort": "").($ft? "&ft=$ft": "").($search? "&search=$search": "").($admin? "&admin=$admin": "").'&p=';
            
            if ($pages > 32) {
                $i = floor($page/10)*10 + 1;
                if ($i >= 10 && $page%10 < 5) $i = $i - 5;
                $maxpages = $i + 22 - floor(log($page,10)-1)*4;
                if ($maxpages > $pages) $maxpages = $pages;
                if ($maxpages - $i + floor(log($page,10)-1)*4 < 22 && $maxpages - 22 > 0) $i = $maxpages - 24 + floor(log($page,10)-1)*3;
            }

            $sBox = '<table width="100%"><tr>';
            if ($page == 1) {
                $sBox .= '<td><div id="nav_pre_not_active"><span>предыдущая</span></div></td>';
            } else {
                $sBox .= "<input type=\"hidden\" id=\"pre_navigation_link\" value=\"".($sHref.($page-1))."\">";
                $sBox .= "<td><div id=\"nav_pre_not_active\"><a href=\"".($sHref.($page-1))."\" style=\"color: #717171\">предыдущая</a></div></td>";
            }
            $sBox .= '<td width="90%" align="center">';
            //в начале
            if ($page <= 10) {
                $sBox .= buildNavigation($page, 1, ($pages>10)?($page+4):$pages, $sHref);
                if ($pages > 15) {
                    $sBox .= '<span style="padding-right: 5px">...</span>';
                    //$sBox .= buildNavigation($page, $pages-5, $pages, $sHref);
                }
            }
            //в конце
            elseif ($page >= $pages-10) {
                $sBox .= buildNavigation($page, 1, 5, $sHref);
                $sBox .= '<span style="padding-right: 5px">...</span>';
                //$sBox .= buildNavigation($page, $page-5, $pages, $sHref);
            }
            else {
                $sBox .= buildNavigation($page, 1, 5, $sHref);
                $sBox .= '<span style="padding-right: 5px">...</span>';
                $sBox .= buildNavigation($page, $page-4, $page+4, $sHref);
                $sBox .= '<span style="padding-right: 5px">...</span>';
                //$sBox .= buildNavigation($page, $pages-5, $pages, $sHref);
            }
            $sBox .= '</td>';
            if ($page == $pages) {
                $sBox .= "<td><div id=\"nav_next_not_active\"><span>следующая</span></div></td>";
            } else {
                $sBox .= "<input type=\"hidden\" id=\"next_navigation_link\" value=\"".($sHref.($page+1))."\">";
                $sBox .= "<td><div id=\"nav_next_not_active\"><a href=\"".($sHref.($page+1))."\" style=\"color: #717171\">следующая</a></div></td>";
            }
            $sBox .= '</tr>';
            $sBox .= '</table>';
        }
        $sBox .= '</div>';
        echo $sBox;
        // Страницы закончились
    ?></td>

</tr>

</table>

<?php 
include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' ); 
include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
?>