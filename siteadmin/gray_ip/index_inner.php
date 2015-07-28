<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/gray_ip.common.php' );
$xajax->printJavascript( '/xajax/' );
?>
<script type="text/javascript">
banned.addContext( 'gray_ip', -1, '', '' );
banned.reload = 1;
gray_ip.adminId = <?=$_SESSION['uid']?>;
</script>
<h3>IP-адреса / Серый список IP</h3>
<!-- Добавить IP старт -->
<div class="form form-acnew add-ip">
    <b class="b1"></b>
    <b class="b2"></b>
    <div class="form-in">
        <h4 class="toggle"><a href="#" class="lnk-dot-666">Добавить IP</a></h4>
        <div class="slideBlock filtr-hide">
            <form name="frm_gray_ip_add" id="frm_gray_ip_add">
            <input type="hidden" name="add_uid" id="add_uid" value="">
            <div class="form-block first">
                <div class="form-el">
                    <label class="form-l">Для юзера:</label>
                    <div class="form-value fvs">
                        <span class="login-input">
                            <input type="text" name="add_login" id="add_login" class="i-txt" />
                        </span>
                        <span class="login-view" style="display: none;">
                            <a href="#"></a>&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="gray_ip.changeLogin()">изменить</a>
                        </span>
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">IP-адреса:</label>
                    <div class="form-value">
                        <textarea name="add_ip" id="add_ip" cols="" rows=""></textarea>
                    </div>
                </div>
            </div>
            <div class="form-block last">
                <div class="form-el form-btns">
                    <button type="button" onclick="gray_ip.submitAdd()" disabled>Добавить</button>
                    <a href="javascript:void(0);" onclick="gray_ip.clearAdd()" class="lnk-dot-grey">Очистить</a>
                </div>
            </div>
            </form>
        </div>
    </div>
    <b class="b2"></b>
    <b class="b1"></b>
</div>
<!-- Добавить IP стоп -->

<!-- Фильтр старт -->
<?php if ( !$primary_id ) { ?>
<div class="form form-acnew">
    <b class="b1"></b>
    <b class="b2"></b>
    <div class="form-in">
        <h4 class="toggle"><a href="#" class="lnk-dot-666">Фильтр</a></h4>
        <div class="slideBlock filtr-hide">
            <form name="frm_gray_ip_filter" id="frm_gray_ip_filter" method="GET" onsubmit="return gray_ip.submitFilter();">
            <input type="hidden" name="cmd" value="filter" />
            <input type="hidden" id="log_pp" name="log_pp" value="<?=$log_pp?>">
            <div class="form-block first">
                <div class="form-el">
                    <label class="form-l">Фильтр:</label>
                    <div class="form-value form-filtr">
                        <input value="<?=$f_ip?>" name="f_ip" id="f_ip" type="text" maxlength="15" />&#160;&#160;&mdash;&#160;&#160;
                        <input value="<?=$t_ip?>" name="t_ip" id="t_ip" type="text" maxlength="15" />
                        <?=$error?>
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">Модератор:</label>
                    <div class="form-value fvs">
                        <select name="adm" id="adm" class="sw205">
                            <option value="0">Все</option>
                            <?php 
                            $sAdmin = '';
                            foreach ( $admins as $aOne ) { 
                                $sSel = ($aOne['uid'] == $adm) ? ' selected' : '';
                                if ( $aOne['uid'] == $adm ) $sAdmin = $aOne['login'];
                            ?>
                            <option value="<?=$aOne['uid']?>" <?=$sSel?>><?=$aOne['login']?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">Логин:</label>
                    <div class="form-value fvs">
                        <input value="<?=$search_name?>" name="search_name" id="search_name" type="text" class="i-txt fvsi" />
                    </div>
                </div>
            </div>
            <div class="form-block last">
                <div class="form-el form-btns">
                    <button type="submit">Отфильтровать</button>
                    <a href="javascript:void(0);" onclick="gray_ip.clearFilter()" class="lnk-dot-grey">Очистить</a>
                </div>
            </div>
            </form>
        </div>
    </div>
    <b class="b2"></b>
    <b class="b1"></b>
</div>
<?php } ?>
<!-- Фильтр стоп -->

<!-- Массовые действия старт -->
<div class="form form-check">
    <b class="b1"></b>
    <b class="b2"></b>
    <div class="form-in c">
        <div class="form-el form-btns">
            <div class="form-value">
                <input onchange="gray_ip.checkUsers(this.checked);" name="chk_all" id="chk_all" type="checkbox" value="1" />
            </div>
            
            <button type="button" onclick="getMassBanUser('gray_ip')">Заблокировать</button>
            <button type="button" onclick="if(confirm('Прекратить отслеживать выбранных пользователей?')){gray_ip.submitDel();}">Удалить</button>
        </div>
    </div>
    <b class="b2"></b>
    <b class="b1"></b>
</div>
<!-- Массовые действия стоп -->

<?php if ( $filter['ip_from'] || $filter['ip_to'] || $sAdmin || $search_name ) {
	$aParts = array();
	
	if ( $filter['ip_from'] || $filter['ip_to'] ) {
		$aParts[] = 'IP:' . ( $filter['ip_from'] ? ' c '.$filter['ip_from'] : '' ).( $filter['ip_to'] ? ' по '.$filter['ip_to'] : '' );
	}
	
	if ( $sAdmin ) {
		$aParts[] = 'Модератор: '.hyphen_words($sAdmin, true);
	}
	
	if ( $search_name ) {
		$aParts[] = 'Логин: '.hyphen_words($search_name, true);
	}
	
	echo '<div class="date-list name-ip"><span>'.implode(', ', $aParts ).'</span></div>';
} ?>
<?php ?>


<div class="date-list">
    <?php if ( $grayIp ) { 
        echo '<form name="frm_gray_ip" id="frm_gray_ip" method="post"><input type="hidden" name="task" id="task" value="sdel">';
        if ( $primary_id ) {
        	echo '<input type="hidden" name="primary_id" id="primary_id" value="'. $primary_id .'">';
        }
        
        $sList = $sList2 = $sDate = $sPid = '';
        $i = $j = $pid = $sid = 0;
        
        for ( $cnt = 0; $cnt < count($grayIp); $cnt++ ) {
            $aOne = $grayIp[$cnt];
            
        	if ( $aOne['p_id'] != $sPid ) {
        	    if ( $sid > 10 && !$primary_id ) {
        	    	$sList .= grayIpParseMore( $sPid, $sid );
        	    }
        	    
        	    $sid    = 0;
        	    $bCheck = ( isset($grayIp[$cnt+1]) && $grayIp[$cnt+1][p_id] == $aOne['p_id'] );
        	    $sPid   = $aOne['p_id'];
        	    $sList .= ( $j ) ? '</table></div>' : '';
        		$sList .= '<div class="gray-list">
                    <table>
                    <tr>
                        <td colspan="4">
                            <div class="gray-list-head">
                                <ul class="c">
                                    '.( $bCheck ? '<li class="edit-ip"><a href="javascript:void(0);" onclick="gray_ip.MassDelSecondaryUser('.$aOne['p_id'].', \''.$aOne['ip'].'\')"><img src="/images/icons/del.png"></a></li>' : '').'
                                    '.( $bCheck ? '<li class="edit-ip"><a href="javascript:void(0);" onclick="gray_ip.MassBanSecondaryUser('.$aOne['p_id'].')"><img src="/images/icons/block.png"></a></li>' : '').'
                                    <li class="edit-ip"><a onclick="if(confirm(\'Прекратить отслеживать пользователя '.$aOne['p_login'].'?\')){window.location=\'?task=pdel&puid='.$aOne['p_uid'].($primary_id ? '&primary_id='.$primary_id : '').'\';}" href="javascript:void(0);" class="lnk-dot-999">Убрать из "Cерого списка IP"</a></li>
                                    <li class="edit-ip"><a href="javascript:void(0);" onclick="xajax_getPrimaryIpForm('.$i.', '.$aOne['p_uid'].', gray_ip.adminId);" class="lnk-dot-999">Редактировать IP-адреса</a></li>
                                    <li class="name-ip">
                                        '.( $bCheck ? '<input onclick="gray_ip.checkSecondaryUsers(this.value, this.checked);" name="chk_prim_'.$aOne['p_id'].'" id="chk_prim_'.$aOne['p_id'].'" value="'.$aOne['p_id'].'" type="checkbox">&nbsp;' : '' ).'
                                        <span>'.$aOne['ip'].'</span> <a href="/users/'.$aOne['p_login'].'">['.$aOne['p_login'].']</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="edit-list-ip" id="edit_ip_'.$i.'">
                            </div>
                        </td>
                    </tr>';
        		
        		$pid++;
        	}
        	
        	if ( $sid < 10 || $primary_id ) {
            	$sList .= grayIpParseSecondary( $aOne, $primary_id );
        	}
        	/*
        	elseif ( $pid == 1 ) {
        	    $sList2 .= grayIpParseSecondary( $aOne, $primary_id );
        	}
        	*/
            
            $sid++;
        	$i++;
        	$j++;
        }
        
        //$sList .= (($pid == 1) ? $sList2 : '') . '</table></div>';
        $sList .= ( ($sid > 10 && !$primary_id) ? grayIpParseMore($sPid, $sid) : '' ) . '</table></div>';
        
        echo $sList;
        echo '</form>';
        echo '<script>gray_ip.bOne = '. ( $pid > 1 ? 'false' : 'true' ) .';</script>';
        
        if ( $pages > 1 ) {
            $sHref = e_url( 'page', null );
            $sHref = e_url( 'page', '', $sHref );
            echo get_pager2( $pages, $page, $sHref );
        }
        
        if ( !$primary_id ) {
            // не нужен на странице с полным списком логинов по определенному IP
            echo printPerPageSelect( $log_pp );
        }
        
        // редактирование бана старт
        include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
        
    }
    else {
        echo 'Нет пользователей, удовлетворяющих условиям выборки';
    }
    ?>

</div>

<?php 
if ( $_SESSION['gray_ip_parent_reload'] && $primary_id ) {
?>
    <script type="text/javascript">
    if ( window.opener ) {
        window.opener.window.location.reload(true);
    }
    </script>
    <?php
}
$_SESSION['gray_ip_parent_reload'] = '';
?>

<?php
if ( $bWindowClose && $primary_id ) {
?>
    <script type="text/javascript">
	window.close();
	</script>
<?php
}
?>

<?php 
/**
 * Генерирует HTML со строкой вторичной регистрации
 * 
 * @param  array $aOne массив информации о вторичной регистрации
 * @return string HTML
 */
function grayIpParseSecondary( $aOne, $primary_id = 0 ) {
    $sEmpS = ( $aOne['is_emp'] == 't' ) ? '<em>' : '';
    $sEmpE = ( $aOne['is_emp'] == 't' ) ? '</em>' : '';
    return '
    <tr>
        <td class="first">&#160;</td>
        <td class="gray-list-name" id="td_sec_'.$aOne['p_id'].'_'.$aOne['s_uid'].'">
            <input name="chk_users[]" id="chk_users'.$aOne['s_uid'].'" value="'.$aOne['s_uid'].'" type="checkbox" class="check" />'
	       .$sEmpS.'<b><a href="/users/'.$aOne['s_login'].'">['.$aOne['s_login'].']</a></b>'.$sEmpE.'
        </td>
        <td style="text-align:right;">'. date('d.m.Y', strtotime($aOne['reg_date'])) .'</td>
        <td class="cell-btn">
            &nbsp;<a href="javascript:void(0);" onclick="banned.userBan('.$aOne['s_uid'].', \'gray_ip\',0)"><img src="/images/icons/block.png"></a>
            &nbsp;<a href="javascript:void(0);" onclick="if(confirm(\'Прекратить отслеживать пользователя '.$aOne['s_login'].'??\')){window.location=\'?task=sdel&chk_users='.$aOne['s_uid'].($primary_id ? '&primary_id='.$primary_id : '').'\';}"><img src="/images/icons/del.png"></a>
        </td>
    </tr>';
}

/**
 Генерирует HTML со строкой "Еще Х пользователь"
 * 
 * @param  int $sPid id первичного IP
 * @param  int $sid количество вторичных IP
 * @return string HTML
 */
function grayIpParseMore( $sPid, $sid ) {
    $sHref  = '/siteadmin/gray_ip/?cmd=filter&primary_id=' . $sPid;
	return '<tr>
        <td class="first">&#160;</td>
        <td colspan="3">
        <a href="'.$sHref.'" target="_blank" onClick="popupWin = window.open(this.href, \'secondary\'); popupWin.focus(); return false;">Еще '.($sid-10).' '.ending(($sid-10), 'пользователь', 'пользователя', 'пользователей').'</a>
        </td>
    </tr>';
}
?>