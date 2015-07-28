<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/admin_log.common.php' );
$xajax->printJavascript( '/xajax/' );

$sZeroClipboard = '';
?>
<script type="text/javascript">
banned.addContext( 'admin_log_users', -1, '', '' );
banned.addContext( 'admin_log_page', -1, '', '' );
banned.addContext( 'admin', -1, '', '' );
banned.zero = true;
</script>
<?php include_once('comments.php') ?>

<?php if ( $sUid ) { // просмотр истории конкретного пользователя старт

    $sObjName = hyphen_words( reformat($user['uname'] .' '. $user['usurname'] .' ['. $user['login'].']', 60), true );
    $sObjLink = "/users/{$user['login']}";
    $sLoginSt = $session->view_online_status( $user['login'], false, '' );
    $ago      = $session->ago;
    
    if ( !$session->is_active ) {
        $oUser = new users();
        $oUser->GetUserByUID( $user['uid'] );
        
        if ( $oUser->last_time ) {
            $fmt = 'ynjGi';
            
            if ( time() - ($lt = strtotime($oUser->last_time)) > 24 * 3600 ) {
                $fmt = 'ynjG';
                
                if (time() - $lt > 30 * 24 * 3600)
                    $fmt = 'ynj';
            }
            
            $ago = ago_pub( $lt, $fmt );
        }
    }
    
    $ago = ( $ago ) ? $ago : 'меньше минуты';
    
    if ( $user['safety_bind_ip'] ) {
    	$safety_ip = users::GetSafetyIP( $user['uid'] );
    }
    
    // права админа
    $bHasAll      = hasPermissions( 'all' );
    $bHasPayments = hasPermissions( 'payments' );
?>
    <h3>Действия / История пользователя</h3>
    
    <div class="plashka">
        <span><a href="<?=$_SESSION['admin_log_user']?>">Назад</a></span>
    </div>
    
    <div class="transgressor_inner">
        <a target="_blank" href="<?=$sObjLink?>"><?=view_avatar( $user['login'], $user['photo'], 1 )?></a>
        <div class="transgressor-info">
            <div class="transgressor-right">
                <p>Последний IP: <span><span id="last_ip_<?=$user['uid']?>" style="display: inline;"><?=$user['last_ip']?></span></span></p>
                
                <?php $sZeroClipboard .=  "clip_last_{$user['uid']} = new ZeroClipboard.Client();
                clip_last_{$user['uid']}.setHandCursor( true );
                clip_last_{$user['uid']}.addEventListener('mouseOver', function (client) {
                    clip_last_{$user['uid']}.setText( $('last_ip_{$user['uid']}').get('html') );
                });
                clip_last_{$user['uid']}.glue('last_ip_{$user['uid']}');
                
                clip_reg_{$user['uid']} = new ZeroClipboard.Client();
                clip_reg_{$user['uid']}.setHandCursor( true );
                clip_reg_{$user['uid']}.addEventListener('mouseOver', function (client) {
                    clip_reg_{$user['uid']}.setText( $('reg_ip_{$user['uid']}').get('html') );
                });
                clip_reg_{$user['uid']}.glue('reg_ip_{$user['uid']}');"; ?>
                
                <button onclick="banned.userBan(<?=$user['uid']?>, 'admin_log_page',0)" name="btn_ban" type="button" value="btn_ban">Блокировать/Разблок.</button>
                <?php if ( !$user['is_banned'] ) { ?>
                <div id="div_warn">
                <?php if ( $user['warn'] < 3 ): ?>
                <button onclick="banned.warnUser(<?=$user['uid']?>, 0, 'admuserpage', 'admin_log_page', 0); return false;" name="btn_warn" type="button" value="btn_warn">Сделать предупреждение</button>
                <?php else: ?>
                <button onclick="adminLogWarnMax()" name="btn_warn" type="button" value="btn_warn">Сделать предупреждение</button>
                <?php endif; ?>
                </div>
                <?php } ?>
            </div>
            <h4><a target="_blank" href="<?=$sObjLink?>" class="<?=(is_emp($user['role']) ? 'employer' : 'freelancer')?>-name"><?=$sObjName?></a><?=view_mark_user(array("login"      => $user['login'],
                                    "is_pro"      => $user['is_pro'],
									"is_pro_test" => $user['is_pro_test'],
									"is_team"     => $user['is_team'],
									"role"        => $user['role']), '', true, '');
            ?>
            </h4>
            <span><?=$sLoginSt?>Последняя активность: <?=$ago?> назад</span>
            <p><b>Эл.почта:</b><?=$user['email']?>   <span>&#160; &#160;|&#160; &#160;</span>  <a onclick="xajax_getLastEmails(<?=$user['uid']?>);" href="javascript:void(0);" class="lnk-dot-999">Последние 10 e-mail</a> </p>
            
            <?php if ( $user['safety_phone'] ): ?>
            <input type="hidden" name="safety_phone_hidden<?=$user['uid']?>" id="safety_phone_hidden<?=$user['uid']?>" value="<?=$user['safety_phone']?>">
            <div id="safety_phone_show<?=$user['uid']?>" class="safety">
            <b>Телефон:</b> <span id="safety_phone_value<?=$user['uid']?>" class="safetyvalue"><?=$user['safety_phone']?></span><span id="safety_only_phone_show<?=$user['uid']?>" style="display: <?=( $user['safety_only_phone'] == 't' ? 'inline' : 'none' )?>">&nbsp;Только по SMS</span> <a href="javascript:void(0);" onclick="setSafetyPhoneForm(<?=$user['uid']?>)" class="lnk-dot-999">Изменить</a>
            </div>
            <div  id="safety_phone_edit<?=$user['uid']?>" class="safety" style="display: none;">
            <table>
            <tr><td><b>Телефон:</b> <input type="text" name="safety_phone<?=$user['uid']?>" id="safety_phone<?=$user['uid']?>" value="<?=$user['safety_phone']?>"></td></tr>
            <tr>
                <td><input type="checkbox" name="safety_only_phone<?=$user['uid']?>" id="safety_only_phone<?=$user['uid']?>" value="1" <?=( $user['safety_only_phone'] == 't' ? ' checked' : '' )?>><span> Только по SMS</span></td>
            </tr>
            <tr>
                <td><input type="checkbox" name="safety_mob_phone<?=$user['uid']?>" id="safety_mob_phone<?=$user['uid']?>" value="1" <?=( $user['is_safety_mob'] == 't' ? ' checked' : '' )?>> <span>Входить в финансы только по СМС</span></td>
            </tr>
            </table>
            &nbsp;
            <a href="javascript:void(0);" onclick="updateSafetyPhone(<?=$user['uid']?>)" class="lnk-dot-999">Да</a>&nbsp;
            <a href="javascript:void(0);" onclick="unsetSafetyPhoneForm(<?=$user['uid']?>)" class="lnk-dot-999">Нет</a>
            </div>
            <?php endif; ?>
            
            <?php if ( $user['safety_bind_ip'] && $safety_ip ): ?>
            <div id="safety_ip_show<?=$user['uid']?>" class="safety"><b>IP адрес:</b> <span id="safety_ip_value<?=$user['uid']?>" class="safetyvalue"><?=implode(', ', $safety_ip)?></span> <a href="javascript:void(0);" onclick="setSafetyIpForm(<?=$user['uid']?>)" class="lnk-dot-999">Изменить</a></div>
            <div  id="safety_ip_edit<?=$user['uid']?>" class="safety" style="display: none;">
            <b>IP адрес:</b> <input type="text" name="safety_ip<?=$user['uid']?>" id="safety_ip<?=$user['uid']?>" value="<?=implode(', ', $safety_ip)?>">&nbsp;
            <a href="javascript:void(0);" onclick="updateSafetyIp(<?=$user['uid']?>)" class="lnk-dot-999">Да</a>&nbsp;
            <a href="javascript:void(0);" onclick="unsetSafetyIpForm(<?=$user['uid']?>)" class="lnk-dot-999">Нет</a>
            </div>
            <?php endif; ?>
            
            <p class="reg-ip"><b>IP при регистрации:</b>  <span id="reg_ip_<?=$user['uid']?>" style="display: inline; padding-bottom:15px;"><?=$user['reg_ip']?></span>  <span>&#160; &#160;|&#160; &#160;</span>  <a onclick="xajax_getLastIps(<?=$user['uid']?>);" href="javascript:void(0);" class="lnk-dot-999">Последние 10 IP</a></p>
            
            <ul class="c">
                <?php /*
                <li class="active"><a href="#">Редактировать данные</a></li>
                */ ?>
                <?php if ( $bHasAll || $bHasPayments ) { ?>
                <li class="color-45a300"><a href="/siteadmin/bill/?login=<?=$user['login']?>" target="_blank">Счет пользователя</a></li>
                <?php /*
                $sTitle  = ( $user['is_block_money'] != 't' ) ? 'Заблокировать деньги' : 'Разблокировать деньги'; 
                $sAction = ( $user['is_block_money'] != 't' ) ? 'block'                : 'unblock'; 
                ?>
                <li id="money_<?=$user['uid']?>" class="color-a30000"> | <a onclick="if (confirm('Вы уверены, что хотите <?=mb_strtolower($sTitle)?>?')) xajax_updateMoneyBlock(JSON.encode([<?=$user['uid']?>]),'<?=$sAction?>')" href="javascript:void(0);"><?=$sTitle?></a></li>
                <?php*/ } ?>
                <?php /*if ( $bHasAll ) { ?>
                <li class="color-a30000"> | <a onclick="if (confirm('Вы уверены, что хотите обнулить рейтинг?')) xajax_nullRating(<?=$user['uid']?>)" href="javascript:void(0);">Обнулить рейтинг</a></li>
                <?php }*/ ?>
                <?php if ( $log && ($log[0]['warn_cnt'] || $log[0]['log_warn_cnt']) ) { ?>
                <li class="color-e37101"><?php if ( $bHasAll || $bHasPayments ) { ?> | <?php } ?><a onclick="xajax_getUserWarns(<?=$user['uid']?>,'admin_log_users','admuserpage');" href="javascript:void(0);">Предупреждения</a></li>
                <?php } ?>
            </ul>
            
            <div id="warnreason-<?=$user['uid']?>" style="margin-bottom: 15px; display: none">&nbsp;</div>
            
        </div>
    </div>
    
    <div class="admin-lenta">
        <?php /*
        <h4 class="history">История пользователя: <a href="#" class="lnk-dot-666">Предупреждений: 1</a>  <a  href="#" class="lnk-dot-666">Блокировок: 2</a>  <a  href="#" class="lnk-dot-666">Разблокировок: 1</a></h4>
        */ ?>
        <?php if ( $log ) { 
            $sTrClass = ' class="active"';
        ?>
        <h4 class="history">История пользователя: <?php 
            $sHref = e_url( 'act', null );
            $sHistoryLinks = '';
            $nHistoryTotal = 0;
        
            foreach ( $stat as $aOne ) {
            	if ( $aOne['cnt'] ) {
            	    $sHistoryClass  = ( $aOne['act_id'] == $act ) ? 'lnk-dot-red' : 'lnk-dot-666';
            		$sHistoryLinks .= '<a href="'.$sHref.'&act='.$aOne['act_id'].'" class="'.$sHistoryClass.'">'.$aOne['act_name'].': '.$aOne['cnt'].'</a>';
            		$nHistoryTotal += $aOne['cnt'];
            	}
            }
            
            if ( $nHistoryTotal ) {
                $sHistoryClass  = ( !$act ) ? 'lnk-dot-red' : 'lnk-dot-666';
                echo '<a href="'.$sHref.'" class="'.$sHistoryClass.'">Все: '.$nHistoryTotal.'</a>';
                echo $sHistoryLinks;
            }
        ?></h4>
        
        <table>
            <?php foreach ( $log as $aOne ) { 
                $sTrClass  = (!$sTrClass) ? ' class="active"' : '';
                $sObjName  = $aOne['object_name'] ? hyphen_words(reformat($aOne['object_name'], 60), true) : '<без названия>';
                $sObjLink  = $aOne['object_link'] ? $aOne['object_link'] : 'javascript:void(0);';
                $sObjClass = $aClass[$aOne['obj_code']];
                $sActClass = '';
                $sComments = '';
                $sHref     = ( $sLogId == $aOne['id'] ) ? e_url( 'lid', null ).'#lid_'.$aOne['id'] : e_url( 'lid', $aOne['id'] ).'#lid_'.$aOne['id'];
                
                if ( in_array($aOne['act_id'], $aRed) ){
                    $sActClass = 'color-a30000';
                }
                elseif ( in_array($aOne['act_id'], $aYellow) ) {
                    $sActClass = 'color-e37101';
                }
                elseif ( in_array($aOne['act_id'], $aGreen) ) {
                    $sActClass = 'color-45a300';
                }
                
                if ( $aOne['comments_cnt'] ) {
                    $sNew = ($aOne['last_comment'] > $aOne['last_comment_view']) ? 'new-' : '';
                    $sComments = '<a href="'.$sHref.'"><img id="ico_comm_' . $aOne['id'] . '" src="/images/'. $sNew .'comm.gif" alt="" /></a>';
                }
                
                $sContextName = ( $aOne['context_code'] > 0 ) ? admin_log::$aObj[$aOne['context_code']]['short'] : 'Админка';
            ?>
            <tr id="tr_<?=$aOne['id']?>" onclick="window.location.href='<?=$sHref?>'" <?=$sTrClass?>>
                <td class="cell-from"><?=$sContextName?></td>
                <td class="cell-action <?=$sActClass?>"><a name="lid_<?=$aOne['id']?>"></a><?=$aOne['act_name']?></td>
                <td class="cell-descript"><a href="<?=$sHref?>" class="lnk-dot-666"><?=hyphen_words(reformat($aOne['admin_comment'], 45), true)?></a></td>
                <td class="cell-who"><?php if ( $aOne['adm_login'] ) { ?><a target="_blank" href="/users/<?=$aOne['adm_login']?>">[<?=$aOne['adm_login']?>]</a><?php } else { ?>[не известно]<?php } ?></td>
                <td class="cell-date"><?php if ( $aOne['act_time'] ) { ?><?=date('d.m.Y H:i', strtotime($aOne['act_time']))?><?php } else { ?>не известно<?php } ?></td>
                <td class="cell-com"><?=$sComments?></td>
            </tr>
            <tr <?=$sTrClass?>>
                <td colspan="6" style="padding: 0px;">
                    <div style="padding-left: 20px;" id="div_comments_<?=$aOne['id']?>"><?php if ( $sLogId == $aOne['id'] && $comments_html ) { echo $comments_html; } ?></div>
                </td>
            </tr>
            <?php } ?>
        </table>
        
        
        <?php 
        }
        else {
        ?>
        Нет действий
        <?php
        }
        ?>
    </div>
    
    <!-- список последних 10 IP/email пользователя старт -->
    <?php
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/siteadmin/admin_log/last10_overlay.php' );
    ?>
    <!-- список последних 10 IP/email пользователя стоп -->
    
    <!-- редактирование бана старт -->
    <?php
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
    ?>
    <!-- редактирование бана стоп -->

<?php

} // просмотр истории конкретного пользователя стоп
else { // список действий над пользователями старт
?>
<h3>Действия / Нарушители (бан и предупреждения)</h3>
<!-- Фильтр старт -->
<div class="form form-acnew">
	<b class="b1"></b>
	<b class="b2"></b>
	<div class="form-in">
        <h4 class="toggle"><a href="javascript:void(0);" onclick="var mySlide = new Fx.Slide('slideBlock').toggle();" class="lnk-dot-666">Фильтр</a></h4>
        <div id="slideBlock" class="slideBlock">
            <form name="frm_filter" id="frm_filter" method="GET" onsubmit="return checkDateFilter();">
            <input type="hidden" id="cmd" name="cmd" value="filter">
            <input type="hidden" id="site" name="site" value="user">
            <input type="hidden" id="log_pp" name="log_pp" value="<?=$log_pp?>">
            <div class="form-block first">
                <div class="form-el form-date">
                    <label class="form-l">Дата:</label>
                    <div class="form-value">
                        <select name="from_d" id="from_d" class="sel-year">
                            <option value=""></option>
                            <?php foreach ( $aDays as $nDay ) { 
                                $sSel = ($nDay == $fromD) ? ' selected' : '';
                            ?>
                            <option value="<?=$nDay?>" <?=$sSel?>><?=$nDay?></option>
                            <?php } ?>
                        </select>&nbsp;
                        <select name="from_m" id="from_m" class="sel-month" onchange="UpdateDays('from');">
                            <option value=""></option>
                            <?php foreach ( $aMounth as $key => $name ) { 
                                $sSel = ($key == $fromM) ? ' selected' : '';
                            ?>
                            <option value="<?=$key?>" <?=$sSel?>><?=$name?></option>
                            <?php } ?>
                        </select>&nbsp;
                        <select name="from_y" id="from_y" class="sel-year" onchange="UpdateDays('from');">
                            <option value=""></option>
                            <?php foreach ( $aYears as $nYear ) { 
                                $sSel = ($nYear == $fromY) ? ' selected' : '';
                            ?>
                            <option value="<?=$nYear?>" <?=$sSel?>><?=$nYear?></option>
                            <?php } ?>
                        </select>&#160;&#160;&mdash;&#160;&#160;
                    </div>
                    
                    <div class="form-value">
                        <select name="to_d" id="to_d" class="sel-year">
                            <?php foreach ( $aDays as $nDay ) { 
                                $sSel = ($nDay == $toD) ? ' selected' : '';
                            ?>
                            <option value="<?=$nDay?>" <?=$sSel?>><?=$nDay?></option>
                            <?php } ?>
                        </select>&nbsp;
                        <select name="to_m" id="to_m" class="sel-month" onchange="UpdateDays('to');">
                            <?php foreach ( $aMounth as $key => $name ) { 
                                $sSel = ($key == $toM) ? ' selected' : '';
                            ?>
                            <option value="<?=$key?>" <?=$sSel?>><?=$name?></option>
                            <?php } ?>
                        </select>&nbsp;
                        <select name="to_y" id="to_y" class="sel-year" onchange="UpdateDays('to');">
                            <?php foreach ( $aYears as $nYear ) { 
                                $sSel = ($nYear == $toY) ? ' selected' : '';
                            ?>
                            <option value="<?=$nYear?>" <?=$sSel?>><?=$nYear?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">Действие:</label>
                    <div class="form-value fvs">
                        <select name="act" id="act" style="width:250px">
                            <option value="0" >Все</option>
                            <?php foreach ( $actions as $aOne ) { 
                                $sSel = ($aOne['id'] == $act) ? ' selected' : '';
                            ?>
                            <option value="<?=$aOne['id']?>" <?=$sSel?> ><?=$aOne['act_name']?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">Модератор:</label>
                    <div class="form-value fvs">
                        <select name="adm" id="adm" style="width:250px">
                            <option value="0">Все</option>
                            <?php foreach ( $admins as $aOne ) { 
                                $sSel = ($aOne['uid'] == $adm) ? ' selected' : '';
                            ?>
                            <option value="<?=$aOne['uid']?>" <?=$sSel?>><?=$aOne['login']?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">Поиск:</label>
                    <div class="form-value fvs">
                        <input value="<?=$search?>" name="search" id="search" type="text" class="i-txt fvsi" />
                    </div>
                </div>
            </div>
            <div class="form-block last">
                <div class="form-el form-btns">
                    <button type="submit">Отфильтровать</button>
                    <a href="javascript:void(0);" onclick="adminLogClearFilter('<?=date('j')?>', '<?=date('m')?>', '<?=date('Y')?>');" class="lnk-dot-grey">Очистить</a>
                </div>
            </div>
            </form>
        </div>
	</div>
	<b class="b2"></b>
	<b class="b1"></b>
</div>
<!-- Фильтр стоп -->

<div class="admin-lenta">
    <?php if ( $log ) { 
        $sHref = e_url( 'page', null );
        $sHref = e_url( 'sort', null, $sHref );
        $sHref = e_url( 'dir',  null, $sHref );
        $sSrc  = $direction == 'asc' ? '/images/cell-top.gif' : '/images/cell-bot.gif';
        $nCnt  = 1;
    ?>
    <table class="lenta-project">
    <tr>
        <?php $href = $sHref . '&sort=name&dir='. ($order=='name' ? ($direction == 'asc' ? 'desc' : 'asc') : 'asc'); ?>
    	<th class="cell-user"><a href="<?=$href?>" class="lnk-dot-666">Пользователь</a><? if ($order == 'name') { ?><a href="<?=$href?>"><img src="<?=$sSrc?>" alt=""  /></a><? } ?></th>
    	<?php $href = $sHref . '&sort=act&dir='. ($order=='act' ? ($direction == 'asc' ? 'desc' : 'asc') : 'asc'); ?>
    	<th class="cell-act"><a href="<?=$href?>" class="lnk-dot-666">Действие</a><? if ($order == 'act') { ?><a href="<?=$href?>"><img src="<?=$sSrc?>" alt=""  /></a><? } ?></th>
    	<?php $href = $sHref . '&sort=date&dir='. ($order=='date' ? ($direction == 'asc' ? 'desc' : 'asc') : 'asc'); ?>
    	<th class="cell-blocking"><a href="<?=$href?>" class="lnk-dot-666">Дата</a><? if ($order == 'date') { ?><a href="<?=$href?>"><img src="<?=$sSrc?>" alt=""  /></a><? } ?></th>
    </tr>
    </table>
    
    <?php foreach ( $log as $aOne ) {  
        $sObjName  = $aOne['object_name'] ? hyphen_words(reformat($aOne['object_name'], 60), true) : '<без имени>';
        $sObjLink  = $aOne['object_link'] ? $aOne['object_link'] : 'javascript:void(0);';
        $sActClass = '';
        
        if ( in_array($aOne['act_id'], $aRed) ){
            $sActClass = 'color-a30000';
        }
        elseif ( in_array($aOne['act_id'], $aYellow) ) {
            $sActClass = 'color-e37101';
        }
        elseif ( in_array($aOne['act_id'], $aGreen) ) {
            $sActClass = 'color-45a300';
        }
    ?>
    <table class="lenta-project">
    <tr>
    	<td class="cell-user">
    	   <div class="div-user">
        	<a target="_blank" href="<?=$sObjLink?>"><?=view_avatar($aOne['user_login'], $aOne['photo'], 1)?></a><br>
        	</div>
       	  	<h4><?=view_mark_user(array("login" => $aOne['login'],
                                    "is_pro"  => $aOne['is_pro'],
									"is_pro_test" => $aOne['is_pro_test'],
									"is_team"     => $aOne['is_team'],
									"role"        => $aOne['role']), '', true, '');
            ?><a target="_blank" href="<?=$sObjLink?>" class="<?=(is_emp($aOne['role']) ? 'employer' : 'freelancer')?>-name"><?=$sObjName?></a></h4>
            <span class="span-user"><?=$session->view_online_status($aOne['user_login'], false, '')?><?=($session->is_active ? 'На сайте' : 'Нет на сайте')?>
            
            </span>
            <p>Зарегистрирован: <?=date('d.m.Y', strtotime($aOne['reg_date']))?></p>
            <p>Последний IP: <span id="last_ip_<?=$nCnt?>" style="display: inline;"><?=$aOne['last_ip']?></span></p>
            <p class="user-notice">
            <?php if ( $aOne['warn_cnt'] ): ?>
            <a onclick="xajax_getUserWarns(<?=$aOne['user_id']?>,'admin_log_users','admalluserspage');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_<?=$aOne['user_id']?>_<?=$aOne['id']?>"><?=$aOne['warn_cnt']?></span></a>
            <?php elseif ( $aOne['log_warn_cnt'] ): ?>
            <a onclick="xajax_getUserWarns(<?=$aOne['user_id']?>,'admin_log_users','admalluserspage');" href="javascript:void(0);" class="lnk-dot-666">Действующих предупреждений нет</a>
            <?php else: ?>
            Предупреждений нет
            <?php endif; ?>
            </p>
        </td>
    	<td class="cell-act">
            <?php 
            $sContextLink  = ($aOne['context_link'] && $aOne['object_deleted'] != 't' ) ? $aOne['context_link'] : ''; 
            $sContextName  = ( in_array($aOne['act_id'], array_merge($aRed, $aYellow)) ) ? (($aOne['context_code'] > 0 ) ? admin_log::$aObj[$aOne['context_code']]['short'] . ': ' . ($aOne['context_name'] ? $aOne['context_name'] : '<без названия>') : 'Админка') : ' ';
            $sAdminComment = $aOne['admin_comment'] ? hyphen_words(reformat($aOne['admin_comment'], 45), true) : '<без причины>';
            ?>
        	<h4 class="<?=$sActClass?>"><?=($sContextLink ? '<a href="'.$sContextLink.'" target="_blank">' : '')?><?=$aOne['act_name']?><?=($sContextLink ? '</a>' : '')?></h4>
        	
            <span class="theme-blog"><?=hyphen_words($sContextName, true)?></span>
            
            <p class="reason" id="reason<?=$aOne['act_id']?>_<?=$aOne['src_id']?>"><?=$sAdminComment?></p>
            
            <p>
                <ul class="admin-links">
                    <?php if ( $aOne['src_id'] ) { 
                        // что снимаем: бан или поредупреждение
                        $sOnclickPref = ( in_array($aOne['act_id'], $aRed) ) ? "adminLogOverlayClose();banned.userBan({$aOne['object_id']}, 'admin_log_page'" : "adminLogOverlayClose();banned.warnUser({$aOne['object_id']},{$aOne['src_id']},'admalluserspage','admin_log_page'";
                    ?>
                    <li><a href="javascript:void(0);" onclick="<?=$sOnclickPref?>,1);" class="lnk-dot-red">Редактировать</a></li>
                    <li><a href="javascript:void(0);" onclick="<?=$sOnclickPref?>,0);" class="lnk-dot-red">Снять</a></li>
                    <?php } ?>
                    <li><a href="/siteadmin/admin_log/?site=user&uid=<?=$aOne['object_id']?>" class="lnk-dot-666">История</a></li>
                </ul>
            </p>
        </td>
    	<td class="cell-blocking"><span><?php if ( $aOne['act_time'] ) { ?><?=date('d.m.Y H:i', strtotime($aOne['act_time']))?><?php } else { ?>не известно<?php } ?></span>[<?php if ( $aOne['adm_login'] ) { ?><a target="_blank" href="/users/<?=$aOne['adm_login']?>"><?=$aOne['adm_login']?></a><?php } else { ?>не известно<?php } ?>]</td>
    </tr>
    </table>
    
    <?php 
        $sZeroClipboard .=  "clip_last_{$nCnt} = new ZeroClipboard.Client();
                clip_last_{$nCnt}.setHandCursor( true );
                clip_last_{$nCnt}.addEventListener('mouseOver', function (client) {
                    clip_last_{$nCnt}.setText( $('last_ip_{$nCnt}').get('html') );
                });
                clip_last_{$nCnt}.glue('last_ip_{$nCnt}');"; 
        $nCnt++;
     } ?>
    
    <?php 
        if ( $pages > 1 ) {
            $sHref = e_url( 'page', null );
            $sHref = e_url( 'page', '', $sHref );
            echo get_pager2( $pages, $page, $sHref );
        }
        
        echo printPerPageSelect( $log_pp );
    }
    else {
    ?>
    Нет действий, удовлетворяющих условиям выборки
    <?php
    }
    ?>
</div>

<?php if ( $error ) { ?>
<script type="text/javascript">
alert('<?=$error?>');
</script>
<?php } ?>

<?php
} // список действий над пользователями стоп
?>

<!-- редактирование предупреждения старт -->
<?php
include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
?>
<!-- редактирование предупреждения стоп -->
 
<!-- список предупреждений пользователя старт -->
<?php
include_once( $_SERVER['DOCUMENT_ROOT'] . '/siteadmin/admin_log/warn_overlay.php' );
?>
<!-- список предупреждений пользователя стоп -->
 
 <!-- редактирование бана старт -->
 <?php
 include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
 ?>
 <!-- редактирование бана стоп -->
 
<script type="text/javascript">
window.addEvent('domready', function() {
    <?
    if ( $sZeroClipboard ) {
        echo 'ZeroClipboard.setMoviePath("'.$GLOBALS['host'].'/scripts/zeroclipboard/ZeroClipboard.swf");';
        echo $sZeroClipboard;
    }
    ?>
});
</script>