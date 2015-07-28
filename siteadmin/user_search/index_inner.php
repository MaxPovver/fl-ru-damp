<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } 
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/admin_log.common.php' );
$xajax->printJavascript( '/xajax/' );

$sZeroClipboard = '';

// права админа
$bHasAll      = hasPermissions( 'all' );
$bHasPayments = hasPermissions( 'payments' );
?>
<script type="text/javascript">
banned.addContext( 'admin_user_search', -1, '', '' );
banned.zero = true;
</script>
<h3>IP-адреса / Поиск пользователей</h3>
<!-- Фильтр старт -->
<a name="a_user_search_filter" id="a_user_search_filter"></a>
<div class="form form-acnew">
	<b class="b1"></b>
	<b class="b2"></b>
	<div class="form-in">
        <h4 class="toggle"><a href="javascript:void(0);" onclick="var mySlide = new Fx.Slide('slideBlock').toggle();" class="lnk-dot-666">Фильтр</a></h4>
        <div id="slideBlock" class="slideBlock">
            <form name="frm_user_search_filter" id="frm_user_search_filter" method="GET" onsubmit="return user_search.submitFilter();">
            <input type="hidden" id="cmd" name="cmd" value="filter">
            <input type="hidden" id="log_pp" name="log_pp" value="<?=$log_pp?>">
            <div class="form-block first">
                <div class="form-el">
                    <label class="form-l">IP:</label>
                    <div class="form-value form-filtr">
                        <input value="<?=$f_ip?>" name="f_ip" id="f_ip" type="text" maxlength="15" />&#160;&#160;&mdash;&#160;&#160;
                        <input value="<?=$t_ip?>" name="t_ip" id="t_ip" type="text" maxlength="15" />
                        <?=$error?>
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">Роль:</label>
                    <div class="form-value fvs">
                        <input type="hidden" name="who" id="role" value="<?=$who?>">
                        <ul class="ulradio" id="ulrole">
                            <li <?=($who == '' ? 'class="active"' : '')?>>
    							<a rel="" onclick="user_search.setUlradio(this,'role');" href="javascript:void(0);" class="lnk-dot-red">Все</a>
    						</li>
    						<li <?=($who == 'frl' ? 'class="active"' : '')?>>
    							<a rel="frl" onclick="user_search.setUlradio(this,'role');" href="javascript:void(0);" class="lnk-dot-666">Фрилансеры</a>
    						</li>
    						<li <?=($who == 'emp' ? 'class="active"' : '')?>>
    							<a rel="emp" onclick="user_search.setUlradio(this,'role');" href="javascript:void(0);" class="lnk-dot-green">Работодатели</a>
    						</li>
                        </ul>
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">Статус:</label>
                    <div class="form-value fvs usrstatus">
                        <input type="hidden" name="status" id="status" value="<?=$status?>">
                        <ul class="ulradio" id="ulstatus">
                            <li <?=($status == 0 ? 'class="active"' : '')?>>
                                <a rel="0" onclick="user_search.setUlradio(this,'status');" href="javascript:void(0);" class="lnk-dot-666">Все</a>
                            </li>
                            <li <?=($status == 1 ? 'class="active"' : '')?>>
                                <a rel="1" onclick="user_search.setUlradio(this,'status');" href="javascript:void(0);" class="lnk-dot-red">Забаненные</a>
                            </li>
                            <li <?=($status == 5 ? 'class="active"' : '')?>>
                                <a rel="5" onclick="user_search.setUlradio(this,'status');" href="javascript:void(0);" class="lnk-dot-666">Незабаненные</a>
                            </li>
                            <li <?=($status == 2 ? 'class="active"' : '')?>>
                                <a rel="2" onclick="user_search.setUlradio(this,'status');" href="javascript:void(0);" class="lnk-dot-666">Не активированные</a>
                            </li>
                            <li class="color-e37101<?=($status == 3 ? ' active' : '')?>">
                                <a rel="3" onclick="user_search.setUlradio(this,'status');" href="javascript:void(0);">С предупреждениями</a>
                            </li>
                            <li class="color-e37101<?=($status == 4 ? ' active' : '')?>">
                                <a rel="4" onclick="user_search.setUlradio(this,'status');" href="javascript:void(0);">Удаленные самостоятельно</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php /* // #0016722 Совместить поля "Телефон" и "Поиск"
                <div class="form-el">
                    <label class="form-l">Телефон:</label>
                    <div class="form-value fvs">
                        <input value="<?=$search_phone?>" name="search_phone" id="search_phone" type="text" class="i-txt fvsi" /><br/>
						<div class="b-check">
							<input id="search_phone_exact" class="b-check__input" name="search_phone_exact" type="checkbox" value="1" <?=($search_phone_exact ? ' checked="checked"' : '')?> />
							<label for="search_phone_exact" class="b-check__label b-check__label_fontsize_13">точное совпадение</label>
						</div>
                    </div>
                </div>
                */ ?>
                <div class="form-el">
                    <label class="form-l">ID:</label>
                    <div class="form-value">
                        <input value="<?=$filter_uid?>" name="t_uid" id="t_uid" type="text" />
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">Поиск:</label>
                    <div class="form-value fvs">
                        <input value="<?=$search_name?>" name="search_name" id="search_name" type="text" class="i-txt fvsi" /><br/>
                        <div class="b-check">
							<input id="search_name_exact" class="b-check__input" name="search_name_exact" type="checkbox" value="1" <?=($search_name_exact ? ' checked="checked"' : '')?> />
							<label for="search_name_exact" class="b-check__label b-check__label_fontsize_13">точное совпадение</label>
						</div>
                    </div>
                </div>
            </div>
            <div class="form-block last">
                <div class="form-el form-btns">
                    <button type="submit">Отфильтровать</button>
                    <a href="javascript:void(0);" onclick="user_search.clearFilter();" class="lnk-dot-grey">Очистить</a>
                </div>
            </div>
            </form>
        </div>
	</div>
	<b class="b2"></b>
	<b class="b1"></b>
</div>
<!-- Фильтр стоп -->

<!-- Массовые действия старт -->
<div class="form form-check">
	<b class="b1"></b>
	<b class="b2"></b>
	<div class="form-in c">
            <div class="form-el form-btns">
            	<div class="form-value">
								<div class="b-check">
            			<input id="chk_all" class="b-check__input" onchange="adminLogCheckUsers(this.checked);" name="chk_all" type="checkbox" value="1" />
								</div>
                </div>
                <button onclick="getMassBanUser('admin_user_search')" type="button">Блокировка/Разблок.</button>
                <button onclick="getMassWarnUser()" type="button" <?=($status == '1' ? 'style="display:none;"' : '' )?>>Сделать предупреждение</button>
                <?php /*if ( $bHasPayments ) { ?>
                <button onclick="adminLogMassMoneyBlock('block');" type="button">Заблокировать деньги</button>
                <?php }*/ ?>
                <button onclick="adminLogMassActivate(<?=($status == '2' ? 1 : 0)?>);" type="button">Активировать</button>
            </div>
	</div>
	<b class="b2"></b>
	<b class="b1"></b>
</div>
<!-- Массовые действия стоп -->
<style type="text/css">.search-item img{ float:none;}</style>
<div class="search-lenta">

<?php if ( $users ) { 
    foreach ( $users as $aOne ) {  
        $sObjName = $aOne['uname'] .' '. $aOne['usurname'] .' ['. $aOne['login'].']';
        $sObjLink = "/users/{$aOne['login']}";
?>
    <div class="search-item c">
        <div class="form-check">
            <input name="chk_users[]" id="chk_users<?=$aOne['uid']?>" value="<?=$aOne['uid']?>" type="checkbox" <? if ((hasGroupPermissions('administrator', $aOne['uid']) || hasGroupPermissions('moderator', $aOne['uid']))) { ?>disabled<? } ?>/>
        </div>
        <div class="div-user">
        	<a target="_blank" href="<?=$sObjLink?>"><?=view_avatar($aOne['login'], $aOne['photo'], 1)?></a><br>
        	</div>
        <div class="search-item-info">
        	<div class="search-right" id="search_right_<?=$aOne['uid']?>"> 
                <?php if ( $bHasAll || $bHasPayments ) { ?>
            	<span><a target="_blank" href="/siteadmin/bill/?login=<?=$aOne['login']?>" class="color-45a300">Счет пользователя</a></span>
            	<?php } ?>
            	<?php $sOnclick = ' onclick="xajax_getUserWarns(' . $aOne['uid'] . ',\'admin_user_search\',\'user_search\');" href="javascript:void(0);"'; ?>
            	<?php if ( $aOne['warn'] > 0 ): ?>
            	<span class="color-e37101"><a <?=$sOnclick?>>Предупреждений: <div id="warn_<?=$aOne['uid']?>" class="warncount-<?=$aOne['uid']?>"><?=$aOne['warn']?></div></a></span>
            	<?php else: ?>
            	<span><a <?=$sOnclick?> class="lnk-dot-666">Нет предупреждений</a></span>
            	<?php endif; ?>
            </div>
            <h4 id="user<?=$aOne['uid']?>"><a target="_blank" href="<?=$sObjLink?>" class="<?=(is_emp($aOne['role']) ? 'employer' : 'freelancer')?>-name user-name"><?=$sObjName?></a> <?=view_mark_user(array(
                                    "login"      => $aOne['login'],
                                    "is_pro"  => $aOne['is_pro'],
									"is_pro_test" => $aOne['is_pro_test'],
									"is_team"     => $aOne['is_team'],
									"role"        => $aOne['role']), '', true, '');
            ?></h4>
            
            <?php
            // редактирование отношения пользователей старт
            if ( !$aOne['role'][0] ) { // только для фрилансеров
                $sClass = $aOne['pop'] < 0  ? 'b-voting__link_dot_red' : 'b-voting__link_dot_green';
                $sPop   = $aOne['pop'] != 0 ? $aOne['pop'] : '0';
            ?>
            <div id="pop_show<?=$aOne['uid']?>" class="b-voting b-voting_float_right">
                <a onclick="upPopValue(<?=$aOne['uid']?>);" href="javascript:void(0);" class="b-button b-button_poll_plus b-voiting__right"></a>
                <a onclick="downPopValue(<?=$aOne['uid']?>);" href="javascript:void(0);" class="b-button b-button_poll_minus b-voiting__left "></a>
                <span class="b-voting__mid b-voting__mid_color_green"><a onclick="setPopForm(<?=$aOne['uid']?>);" id="pop<?=$aOne['uid']?>" class="b-voting__link <?=$sClass?>" href="javascript:void(0);"><?=$sPop?></a></span>
            </div>
            <div id="pop_edit<?=$aOne['uid']?>" class="b-input b-input_float_right b-input_width_90" style="display: none;">
                <input class="b-input__text" name="pop_input_<?=$aOne['uid']?>" id="pop_input_<?=$aOne['uid']?>" type="text" size="80" value="<?=$sPop?>">
                <a href="javascript:void(0);" onclick="updatePop(<?=$aOne['uid']?>)" class="lnk-dot-999">Да</a>&nbsp;
                <a href="javascript:void(0);" onclick="unsetPopForm(<?=$aOne['uid']?>)" class="lnk-dot-999">Нет</a>
            </div>
            <?php
            }
            // редактирование отношения пользователей стоп
            ?>
            
            <? if (!(hasGroupPermissions('administrator', $aOne['uid']) || hasGroupPermissions('moderator', $aOne['uid']))) { ?>
            <div class="form">
                  <b class="b1"></b>
                  <b class="b2"></b>
                  <div class="form-in">
                      <ul class="c">
                          <?php $sBanTitle = (!$aOne['is_banned'] && !$aOne['ban_where']) ? 'Заблокировать' : 'Разблокировать'; ?>
                          <li class="color-a30000 comm-ban-<?=$aOne['uid']?>"><a href="javascript:void(0);" onclick="banned.userBan(<?=$aOne['uid']?>, 'admin_user_search',0)"><?=$sBanTitle?></a></li>
                          
                          <li class="color-a30000 warnbutton-<?= $aOne['uid'] ?>" id="warn-<?=$aOne['uid']?>" <?php if ( $aOne['is_banned'] ) { ?>style="display: none;"<?php } ?>>
                          <?php if ( $aOne['warn'] < 3 ): ?>
                          <a onclick="banned.warnUser(<?=$aOne['uid']?>, 0, 'user_search', 'admin_user_search', 0); return false;" href="javascript:void(0);">Сделать предупреждение</a>
                          <?php else: ?>
                          <a onclick="adminLogWarnMax()" href="javascript:void(0);">Сделать предупреждение</a>
                          <?php endif; ?>
                          </li>
                          
                          <?php /*if ( $bHasPayments ) { ?>
                          <?php 
                          $sTitle  = ( $aOne['is_block_money'] != 't' ) ? 'Заблокировать деньги' : 'Разблокировать деньги'; 
                          $sAction = ( $aOne['is_block_money'] != 't' ) ? 'block'                : 'unblock'; 
                          ?>
                          <li id="money_<?=$aOne['uid']?>" class="color-a30000"><a onclick="if (confirm('Вы уверены что хотите <?=mb_strtolower($sTitle)?>?')) xajax_updateMoneyBlock(JSON.encode([<?=$aOne['uid']?>]),'<?=$sAction?>')" href="javascript:void(0);"><?=$sTitle?></a></li>
                          <?php }*/ ?>
                          <?php if ( $aOne['active'] == 'f' ) { ?>
                          <li id="activate_<?=$aOne['uid']?>" class="color-a30000"><a onclick="if (confirm('Вы уверены, что хотите активировать пользователя?')) xajax_activateUser(JSON.encode([<?=$aOne['uid']?>]),<?=($status == '2' ? 1 : 0)?>)" href="javascript:void(0);">Активировать</a></li>
                          <?php } ?>
                      </ul>
                  </div>
                  <b class="b2"></b>
                  <b class="b1"></b>
             </div>
             <? } ?>
             
             <div id="warnreason-<?=$aOne['uid']?>" style="margin-bottom: 15px; display: none">&nbsp;</div>
             
             <div style="height: 25px; overflow:hidden;">
            <div id="email_show<?=$aOne['uid']?>" class="safety"><b>Email:</b> <span id="email_value<?=$aOne['uid']?>" class="safetyvalue"><?=$aOne['email']?></span>&#160;<a href="javascript:void(0);" onclick="setEmailForm(<?=$aOne['uid']?>)" class="lnk-dot-999">Изменить</a>&#160;&#160;&#160;<a onclick="xajax_getLastEmails(<?=$aOne['uid']?>);" href="javascript:void(0);" class="lnk-dot-999">Последние 10 e-mail</a></div>
            <div id="email_edit<?=$aOne['uid']?>" class="safety" style="display: none;">
            <b>Email:</b> <input type="text" name="email<?=$aOne['uid']?>" id="email<?=$aOne['uid']?>" value="<?=$aOne['email']?>">&nbsp;
            <a href="javascript:void(0);" onclick="updateEmail(<?=$aOne['uid']?>)" class="lnk-dot-999">Да</a>&nbsp;
            <a href="javascript:void(0);" onclick="unsetEmailForm(<?=$aOne['uid']?>)" class="lnk-dot-999">Нет</a>
            &#160;&#160;&#160;<a onclick="xajax_getLastEmails(<?=$aOne['uid']?>);" href="javascript:void(0);" class="lnk-dot-999">Последние 10 e-mail</a>
            </div>
            </div>
            
            <?php 
            if ( $aOne['phone'] || $aOne['phone_1'] || $aOne['phone_2'] || $aOne['phone_3'] ) { 
                $aPhone = array();
                
                if ( $aOne['phone'] ) {
                    $aPhone[] = $aOne['phone'];
                }
                
                for ( $i=1; $i<=3; $i++ ) {
                    if ( $aOne['phone_'.$i] ) {
                        $aPhone[] = $aOne['phone_'.$i];
                    }
            	}
            ?>
            <div class="safety">
            <b>Телефон:</b> <?=implode(', ', $aPhone)?>
            </div>
            <?php } ?>
            
            <?php if ( $aOne['safety_phone'] ): ?>
            <input type="hidden" name="safety_phone_hidden<?=$aOne['uid']?>" id="safety_phone_hidden<?=$aOne['uid']?>" value="<?=$aOne['safety_phone']?>">
            <div id="safety_phone_show<?=$aOne['uid']?>" class="safety">
            <b>Привязка к телефону:</b> <span id="safety_phone_value<?=$aOne['uid']?>" class="safetyvalue"><?=$aOne['safety_phone']?></span><span id="safety_only_phone_show<?=$aOne['uid']?>" style="display: <?=( $aOne['safety_only_phone'] == 't' ? 'inline' : 'none' )?>">&nbsp;Только по SMS</span> <span id="is_safety_mob_show<?=$aOne['uid']?>" style="display: <?=( $aOne['is_safety_mob'] == 't' ? 'inline' : 'none' )?>">&nbsp;Вход в финансы</span> <a href="javascript:void(0);" onclick="setSafetyPhoneForm(<?=$aOne['uid']?>)" class="lnk-dot-999">Изменить</a>
            </div>
            <div  id="safety_phone_edit<?=$aOne['uid']?>" class="safety" style="display: none;">
            <b>Привязка к телефону:</b> 
            <input type="text" name="safety_phone<?=$aOne['uid']?>" id="safety_phone<?=$aOne['uid']?>" value="<?=$aOne['safety_phone']?>" disabled="disabled">  
            <input type="checkbox" name="safety_only_phone<?=$aOne['uid']?>" id="safety_only_phone<?=$aOne['uid']?>" value="1" <?=( $aOne['safety_only_phone'] == 't' ? ' checked' : '' )?>><span>Только по SMS</span>
            <input type="checkbox" name="safety_mob_phone<?=$aOne['uid']?>" id="safety_mob_phone<?=$aOne['uid']?>" value="1" <?=( $aOne['is_safety_mob'] == 't' ? ' checked' : '' )?>><span>Вход в финансы</span>
            &nbsp;
            <a href="javascript:void(0);" onclick="updateSafetyPhone(<?=$aOne['uid']?>)" class="lnk-dot-999">Да</a>&nbsp;
            <a href="javascript:void(0);" onclick="unsetSafetyPhoneForm(<?=$aOne['uid']?>)" class="lnk-dot-999">Нет</a>
            </div>
            <?php endif; ?>
            
            <p><a href="javascript:void(0);" onclick="user_search.setIpFilter('reg_ip_<?=$aOne['uid']?>');" class="lnk-dot-666" title="Добавить IP в фильтр"><b>IP при регистрации:</b></a>  <span id="reg_ip_<?=$aOne['uid']?>" style="display: inline; padding-bottom:15px;"><?=$aOne['reg_ip']?></span> &#160;&#160;&#160;   <a href="javascript:void(0);" onclick="user_search.setIpFilter('last_ip_<?=$aOne['uid']?>');" class="lnk-dot-666" title="Добавить IP в фильтр"><b>Последний IP:</b></a> <span id="last_ip_<?=$aOne['uid']?>" style="display: inline; padding-bottom:15px;"><?=$aOne['last_ip']?></span> &#160;&#160;&#160; <a onclick="xajax_getLastIps(<?=$aOne['uid']?>);" href="javascript:void(0);" class="lnk-dot-999">Последние 10 IP</a></p>
            
            <p><a href="javascript:void(0);" onclick="user_search.stopNotifications(<?=$aOne['uid']?>, '<?=(is_emp($aOne['role']) ? 'emp' : 'flr')?>');" class="lnk-dot-666" title="Отключить все уведомления"><b>Отключить все уведомления</b></a></p>
            <p id="verify<?=$aOne['uid']?>">
                <?php if($aOne['is_verify'] == 't') { ?>
                    <a href="javascript:void(0);" onclick="user_search.setVerification(<?=$aOne['uid']?>, false);" class="lnk-dot-666" title="Снять верификацию"><b>Снять верификацию</b></a>
                <?php } else {//if ?>
                    <a href="javascript:void(0);" onclick="user_search.setVerification(<?=$aOne['uid']?>, true);" class="lnk-dot-666" title="Дать верификацию"><b>Дать верификацию</b></a>
                <?php }//else?>
            </p>
            
            <?php $sZeroClipboard .=  "clip_last_{$aOne['uid']} = new ZeroClipboard.Client();
                clip_last_{$aOne['uid']}.setHandCursor( true );
                clip_last_{$aOne['uid']}.addEventListener('mouseOver', function (client) {
                    clip_last_{$aOne['uid']}.setText( $('last_ip_{$aOne['uid']}').get('html') );
                });
                clip_last_{$aOne['uid']}.glue('last_ip_{$aOne['uid']}');
                
                clip_reg_{$aOne['uid']} = new ZeroClipboard.Client();
                clip_reg_{$aOne['uid']}.setHandCursor( true );
                clip_reg_{$aOne['uid']}.addEventListener('mouseOver', function (client) {
                    clip_reg_{$aOne['uid']}.setText( $('reg_ip_{$aOne['uid']}').get('html') );
                });
                clip_reg_{$aOne['uid']}.glue('reg_ip_{$aOne['uid']}');"; ?>
            
            <ul class="admin-links">
                <li style="padding: 0 0 10px;">
                    <a href="/siteadmin/admin_log/?site=user&uid=<?=$aOne['uid']?>" class="lnk-dot-666">История</a>
                </li>
            </ul>
            
            <?php /*
            // !!! пока редактирование данных юзера не будем реализовывать
            // !!! если будем - эту форму нужно показывать user_search.js + аякс, 
            // !!! чтобы она под каждым юзером не торчала
            <p class="toggle"><a href="#" class="lnk-dot-999">Редактировать данные</a></p>
            <fieldset class="edit-data form-hide">
            	<div class="form-el">
                	<label class="form-l" for="r1">Права:</label>
                    <div class="form-value">
                    	<select name="" id="r1"><option>Пользователь</option></select>
                    </div>
                </div>
            	<div class="form-el">
                	<label class="form-l" for="r2">Эл.почта:</label>
                    <div class="form-value">
                    	<input name="" id="r2" type="text" value="konstantin@conctantinopolsky.co.uk " class="i-txt" />
                    </div>
                </div>
            	<div class="form-el">
                	<label class="form-l" for="r3">Телефон:</label>
                    <div class="form-value">
                    	<input name="" type="text" id="r3" value="+7 (098) 765-43-21" class="i-txt" />
                    </div>
                    <div class="form-value form-check">
                    	<input id="r4" name="" type="checkbox" value="" />
                        <label for="r4">Только по SMS</label>
                    </div>
                </div>
            	<div class="form-el">
                	<label class="form-l" for="r5">IP адрес:</label>
                    <div class="form-value">
                    	<input name="" type="text" id="r5" value="255.255.255.25, 10.10.10.1, 10.10.10.2-10.10.10.5" class="i-txt" />
                    </div>
                </div>
                <div class="form-btn">
                	<button>Сохранить</button>
                </div>
            </fieldset>
            */ ?>
        </div>
    </div>
    
    

<?php
    }
      
    if ( $pages > 1 ) {
        $sHref = e_url( 'page', null );
        $sHref = e_url( 'page', '', $sHref );
        echo get_pager2( $pages, $page, $sHref );
    }
    
    echo printPerPageSelect( $log_pp );
    
?>
    <!-- массовые предупреждения старт -->
    <div id="ov-notice6" class="overlay ov-out" style="display: none;">
        <b class="c1"></b>
        <b class="c2"></b>
        <b class="ov-t"></b>
        <div class="ov-r">
            <div class="ov-l">
                <div class="ov-in">
                    <a class="close" style="float: right;" href="javascript:void(0);" onclick="adminLogOverlayClose();return false;"><img height="21" width="21" alt="" src="/images/btn-close.png"></a>
                    <h4>Предупреждение для выбранных пользователей!!!</h4>
                    <div class="form-el">
                        <label class="form-l">Причина:</label>
                        <div class="form-value reason">
                            <select name="reason_id" id="bfrm_sel_0" onchange="banned.setReason(0);">
                                <option value="">Указать вручную</option>
                                <?php if ( $aReasons ) {
                                	foreach ( $aReasons as $aOne ) { 
                                        $sBold = $aOne['is_bold'] == 't' ? ' style="background-color: #cdcdcd;"' : ' style="color: #777;"';
                                ?>
                                	<option value="<?=$aOne['id']?>" <?=$sBold?>><?=$aOne['reason_name']?></option>
                                	<?php
                                	}
                            	} ?>
    						</select>
                            <textarea name="admin_comment" id="bfrm_0" cols="" rows=""></textarea>
                        </div>
                    </div>
                    <div class="ov-btns" id="ov_notice_btns">
                        <input type="button" id="adminLogSetUserWarn" onclick="setMassWarnUser();" class="i-btn i-bold" value="Сохранить" />
                        <a href="javascript:void(0);" onclick="adminLogOverlayClose();" class="lnk-dot-grey">Отмена</a>
                    </div>
                </div>
            </div>
        </div>
        <b class="ov-b"></b>
        <b class="c3"></b>
        <b class="c4"></b>
    </div>
    <!-- массовые предупреждения стоп -->
    
    <!-- редактирование предупреждения старт -->
    <?php
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
    ?>
    <!-- редактирование предупреждения стоп -->
    
    <!-- редактирование бана старт -->
    <?php
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
    ?>
    <!-- редактирование бана стоп -->
    
    <!-- список предупреждений пользователя старт -->
    <?php
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/siteadmin/admin_log/warn_overlay.php' );
    ?>
    <!-- список предупреждений пользователя стоп -->
    
    <!-- список последних 10 IP/email пользователя старт -->
    <?php
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/siteadmin/admin_log/last10_overlay.php' );
    ?>
    <!-- список последних 10 IP/email пользователя стоп -->
<?php
    
}
elseif ( $cmd == 'filter' ) {
?>
    Нет пользователей, удовлетворяющих условиям выборки
<?php
}

?>

</div>

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