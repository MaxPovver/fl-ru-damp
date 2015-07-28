<?
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
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

<? include ('head.php') ?>

<p>&nbsp;</p>

<? if (empty($communes)) { print "<center><div style='font: bold 16px Tahoma'>Нет сообществ.</div></center>"; return; } ?>


<table width="100%" border="0" cellspacing="0" cellpadding="0">
<? 
$i = 0; 
foreach($communes as $comm) {
    $i++;
    // Название.
    $name = "<a href='/commune/?id={$comm['id']}' class='blue' style='font-size:20px'>".($search!==NULL ? highlight(reformat2(YellowLine($comm['name']), 25, 1), $search) : reformat2(YellowLine($comm['name']), 25, 1))."</a>";
    $descr = ($search!==NULL ? highlight(reformat2($comm['descr'], 25, 1), $search) : reformat2($comm['descr'], 25, 1));
    // Сколько участников.
    $mAcceptedCnt = $comm['a_count'] - $comm['w_count'] + 1; // +1 -- создатель
    $mCnt = $mAcceptedCnt.' участник'.getSymbolicName($mAcceptedCnt, 'man');
?>

<tr valign="top">
    <td rowspan="3" style="padding-top:20px;width:200px">
        <?=__commPrntImage($comm)?>
    </td>
    <td colspan="2" style="padding:20px 0 0 20px">
        <?=$name?>
    </td>
</tr>

<tr valign="top">
    <td style="padding:10px 20px 10px 20px">
        <div><?=$descr?></div>
        <div style="margin:22px 0 24px 0"><?=__commPrntAge($comm)?></div>
    </td>
    <td style="width:170px;padding:10px 10px 10px 0">
        <div>
            <div style="padding:0 0 10px 15px"><b><?=$mCnt?></b></div>
        </div>
    </td>
</tr>

<tr valign="top">
    <td style="padding:0 0 0 20px">
    <div>
        <div style="padding-bottom:10px"><b>Создатель:</b></div>
            <div style="float:left; margin-right:10px">
            <?=__commPrntUsrAvtr($comm, '')?>
        </div>
        <?
            $comm['dsp_login'] = YellowLine($comm['login']);
            $comm['dsp_uname'] = YellowLine($comm['uname']);
            $comm['dsp_usurname'] = YellowLine($comm['usurname']);
            print __commPrntUsrInfo($comm, '');
        ?>
    </div>
    </td>

    <td style="vertical-align:bottom; text-align:right" ><a id="unlock_comm_<?=$comm['id']?>" href="./?mode=<?=$mode?><?=($page? "&p=$page": '')?><?=($search? "&search=$search": '')?><?=($sort? "&sort=$sort": '')?><?=($admin? "&admin=$admin": '')?>&action=unblocked&comm=<?=$comm['id']?>" onclick="return addTokenToLink('unlock_comm_<?=$comm['id']?>', 'Уверены, что хотите разблокировать сообщество?')" class="blue" style="font-weight: bold">Разблокировать</a>&nbsp;</td>
</tr>

<tr>
    <td colspan="3" style="padding-top: 10px"><?=__commPrntBlockedBlock($comm['blocked_reason'], $comm['blocked_time'], $comm['admin_login'], "{$comm['admin_uname']} {$comm['admin_usurname']}")?></td>
</tr>

<tr>
    <td colspan="3" style="padding-top: 5px">
        <hr size="1" color="#c6c6c6" />
    </td>
</tr>

<? } ?>


<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>

    <td align="left" width="100%">
    <div id="fl2_paginator"><?

        // Страницы
        $pages = ceil($nums / commune::MAX_ON_PAGE);
        if ($pages > 1) {
            $maxpages = $pages;
            $i = 1;
            $sHref = './?mode=commune'.($sort? "&sort=$sort": "").($ft? "&ft=$ft": "").($search? "&search=$search": "").($admin? "&admin=$admin": "").'&p=';
            
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