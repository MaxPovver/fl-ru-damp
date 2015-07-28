<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/admin_log.common.php' );
$xajax->printJavascript( '/xajax/' );

$oUser  = new users();
$aWarn  = array();
$aWhere = array();
?>
<script type="text/javascript">
banned.addContext( 'admin_messages_spam', -1, '', '' );
banned.reload = 1;
</script>

<!-- Скрытые поля календаря старт -->
<div id="fake_div" style="position: relative; top: -999px; left: -999px;">
    <form name="fake_frm" id="fake_frm">
        <input type="text" name="fake_s_from" id="fake_s_from" value="<?=((!$error && $fromDs && $fromMs && $fromYs) ? $fromDs.'-'.$fromMs.'-'.$fromYs : '')?>">
        <input type="text" name="fake_s_to" id="fake_s_to" value="<?=$toDs?>-<?=$toMs?>-<?=$toYs?>">
        <input type="text" name="fake_c_from" id="fake_c_from" value="<?=((!$error && $fromDc && $fromMc && $fromYc) ? $fromDc.'-'.$fromMc.'-'.$fromYc : '')?>">
        <input type="text" name="fake_c_to" id="fake_c_to" value="<?=$toDc?>-<?=$toMc?>-<?=$toYc?>">
    </form>
</div>
<!-- Скрытые поля календаря стоп -->

<h3>Жалобы / Жалобы на спам</h3>

<!-- Фильтр старт -->
<div class="admin-compliant">
    <div class="form form-acnew">
        <b class="b1"></b>
        <b class="b2"></b>
        <div class="form-in">
            <h4 class="toggle"><a href="javascript:void(0);" onclick="var mySlide = new Fx.Slide('slideBlock').toggle();" class="lnk-dot-666">Фильтр</a></h4>
            <div id="slideBlock" class="slideBlock">
                <form name="frm_filter" id="frm_filter" method="GET" onsubmit="return messages_spam.checkDateFilter();">
                <input type="hidden" id="cmd" name="cmd" value="go">
                <input type="hidden" id="log_pp" name="log_pp" value="<?=$log_pp?>">
                <div class="form-block first">
                    <div class="form-el">
                        <label class="form-l" for="spamer"><b>Логин спамера:</b></label>
                        <div class="form-value fvs">
                            <input type="text" name="spamer" id="spamer" value="<?=$spamer?>" class="i-txt" />
														<div class="b-check b-check_inline-block b-check_valign_bottom">
                            	<input id="spamer_ex" class="b-check__input" type="checkbox" name="spamer_ex" value="1" <?=($spamer_ex ? ' checked' : '')?>><label class="b-check__label b-check__label_fontsize_13" for="spamer_ex">точное совпадение</label>
														</div>
                        </div>
                    </div>
                    <div class="form-el">
                        <label class="form-l" for="kwd"><b>Ключевые слова:</b></label>
                        <div class="form-value fvs">
                            <input type="text" name="kwd" id="kwd" value="<?=$kwd?>" class="i-txt fvsi" />
                        </div>
                    </div>
                    <div class="form-el">
                        <label class="form-l"><b>Дата спама:</b></label>
                        <div class="form-value form-date">
                            <span>с:&#160;&#160;</span>
                            
                            <select name="s_from_d" id="s_from_d" class="sel-year" onchange="messages_spam.onSelectSetDate('s_from');">
                                <option value=""></option>
                                <?php foreach ( $aDays as $nDay ) { 
                                    $sSel = ($nDay == $fromDs) ? ' selected' : '';
                                ?>
                                <option value="<?=$nDay?>" <?=$sSel?>><?=$nDay?></option>
                                <?php } ?>
                            </select>&nbsp;
                            <select name="s_from_m" id="s_from_m" class="sel-month" onchange="UpdateDays('s_from');messages_spam.onSelectSetDate('s_from');">
                                <option value=""></option>
                                <?php foreach ( $aMounth as $key => $name ) { 
                                    $sSel = ($key == $fromMs) ? ' selected' : '';
                                ?>
                                <option value="<?=$key?>" <?=$sSel?>><?=$name?></option>
                                <?php } ?>
                            </select>&nbsp;
                            <select name="s_from_y" id="s_from_y" class="sel-year" onchange="UpdateDays('s_from');messages_spam.onSelectSetDate('s_from');">
                                <option value=""></option>
                                <?php foreach ( $aYears as $nYear ) { 
                                    $sSel = ($nYear == $fromYs) ? ' selected' : '';
                                ?>
                                <option value="<?=$nYear?>" <?=$sSel?>><?=$nYear?></option>
                                <?php } ?>
                            </select>
                            
                            <a id="a_from_s" onclick="if(self.gfPop)gfPop.fStartPop(event, document, document.fake_frm.fake_s_from,document.fake_frm.fake_s_to,$('a_from_s'));return false;" href="javascript:void(0)"><img class="PopcalTrigger" src="/images/calendar.png" alt="" width="23" height="20" /></a>&#160;&#160;по:&#160;&#160;
                        </div>
                        <div class="form-value">
                            
                            <select name="s_to_d" id="s_to_d" class="sel-year" onchange="messages_spam.onSelectSetDate('s_to');">
                                <?php foreach ( $aDays as $nDay ) { 
                                    $sSel = ($nDay == $toDs) ? ' selected' : '';
                                ?>
                                <option value="<?=$nDay?>" <?=$sSel?>><?=$nDay?></option>
                                <?php } ?>
                            </select>&nbsp;
                            <select name="s_to_m" id="s_to_m" class="sel-month" onchange="UpdateDays('s_to');messages_spam.onSelectSetDate('s_to');">
                                <?php foreach ( $aMounth as $key => $name ) { 
                                    $sSel = ($key == $toMs) ? ' selected' : '';
                                ?>
                                <option value="<?=$key?>" <?=$sSel?>><?=$name?></option>
                                <?php } ?>
                            </select>&nbsp;
                            <select name="s_to_y" id="s_to_y" class="sel-year" onchange="UpdateDays('s_to');messages_spam.onSelectSetDate('s_to');">
                                <?php foreach ( $aYears as $nYear ) { 
                                    $sSel = ($nYear == $toYs) ? ' selected' : '';
                                ?>
                                <option value="<?=$nYear?>" <?=$sSel?>><?=$nYear?></option>
                                <?php } ?>
                            </select>
                            
                            <a id="a_to_s" onclick="if(self.gfPop)gfPop.fEndPop(event, document, document.fake_frm.fake_s_from,document.fake_frm.fake_s_to,$('a_to_s'));return false;" href="javascript:void(0)"><img class="PopcalTrigger" src="/images/calendar.png" alt="" width="23" height="20" /></a>
                        </div>
                    </div>
                </div>
    			
                <div class="form-block">
                    <div class="form-el">
                        <label class="form-l" for="user">Пожаловался:</label>
                        <div class="form-value fvs">
                            	<input id="user"  type="text" name="user" value="<?=$user?>" class="i-txt" />
														<div class="b-check b-check_inline-block b-check_valign_bottom">
                            	<input id="user_ex" class="b-check__input" type="checkbox" name="user_ex" value="1" <?=($user_ex ? ' checked' : '')?>><label class="b-check__label b-check__label_fontsize_13" for="user_ex">точное совпадение</label>
														</div>
                        </div>
                    </div>
                    <div class="form-el">
                        <label class="form-l">Дата жалобы:</label>
                        <div class="form-value form-date">
                            <span>с:&#160;&#160;</span>
                            
                            <select name="c_from_d" id="c_from_d" class="sel-year" onchange="messages_spam.onSelectSetDate('c_from');">
                                <option value=""></option>
                                <?php foreach ( $aDays as $nDay ) { 
                                    $sSel = ($nDay == $fromDc) ? ' selected' : '';
                                ?>
                                <option value="<?=$nDay?>" <?=$sSel?>><?=$nDay?></option>
                                <?php } ?>
                            </select>&nbsp;
                            <select name="c_from_m" id="c_from_m" class="sel-month" onchange="UpdateDays('c_from');messages_spam.onSelectSetDate('c_from');">
                                <option value=""></option>
                                <?php foreach ( $aMounth as $key => $name ) { 
                                    $sSel = ($key == $fromMc) ? ' selected' : '';
                                ?>
                                <option value="<?=$key?>" <?=$sSel?>><?=$name?></option>
                                <?php } ?>
                            </select>&nbsp;
                            <select name="c_from_y" id="c_from_y" class="sel-year" onchange="UpdateDays('c_from');messages_spam.onSelectSetDate('c_from');">
                                <option value=""></option>
                                <?php foreach ( $aYears as $nYear ) { 
                                    $sSel = ($nYear == $fromYc) ? ' selected' : '';
                                ?>
                                <option value="<?=$nYear?>" <?=$sSel?>><?=$nYear?></option>
                                <?php } ?>
                            </select>
                            
                            <a id="a_from_c" onclick="if(self.gfPop)gfPop.fStartPop(event, document, document.fake_frm.fake_c_from,document.fake_frm.fake_c_to,$('a_from_c'));return false;" href="javascript:void(0)"><img class="PopcalTrigger" src="/images/calendar.png" alt="" width="23" height="20" /></a>&#160;&#160;по:&#160;&#160;
                        </div>
                        <div class="form-value">
                            <select name="c_to_d" id="c_to_d" class="sel-year" onchange="messages_spam.onSelectSetDate('c_to');">
                                <?php foreach ( $aDays as $nDay ) { 
                                    $sSel = ($nDay == $toDc) ? ' selected' : '';
                                ?>
                                <option value="<?=$nDay?>" <?=$sSel?>><?=$nDay?></option>
                                <?php } ?>
                            </select>&nbsp;
                            <select name="c_to_m" id="c_to_m" class="sel-month" onchange="UpdateDays('c_to');messages_spam.onSelectSetDate('c_to');">
                                <?php foreach ( $aMounth as $key => $name ) { 
                                    $sSel = ($key == $toMc) ? ' selected' : '';
                                ?>
                                <option value="<?=$key?>" <?=$sSel?>><?=$name?></option>
                                <?php } ?>
                            </select>&nbsp;
                            <select name="c_to_y" id="c_to_y" class="sel-year" onchange="UpdateDays('c_to');messages_spam.onSelectSetDate('c_to');">
                                <?php foreach ( $aYears as $nYear ) { 
                                    $sSel = ($nYear == $toYc) ? ' selected' : '';
                                ?>
                                <option value="<?=$nYear?>" <?=$sSel?>><?=$nYear?></option>
                                <?php } ?>
                            </select>
                            
                            <a id="a_to_c" onclick="if(self.gfPop)gfPop.fEndPop(event, document, document.fake_frm.fake_c_from,document.fake_frm.fake_c_to,$('a_to_c'));return false;" href="javascript:void(0)"><img class="PopcalTrigger" src="/images/calendar.png" alt="" width="23" height="20" /></a>
                        </div>
                    </div>
                </div>
                
                <div class="form-block last">
                    <div class="form-el form-btns">
                        <button type="submit">Отфильтровать</button>
                        <a href="javascript:void(0);" onclick="messages_spam.clearFilter('<?=date('j')?>', '<?=date('m')?>', '<?=date('Y')?>');" class="lnk-dot-grey">Очистить</a>
                    </div>
                </div>
                </form>
            </div>
        </div>
        <b class="b2"></b>
        <b class="b1"></b>
    </div>
</div>
<!-- Фильтр стоп -->

<?php if ( $spam ) { ?>
<table class="compliant-table">
<tr>
    <th>Дата жалобы</th>
    <th>Сообщение</th>
    <th></th>
</tr>
<?php foreach ( $spam as $aOne ) { ?>
<tr>
    <td class="first">
        <?=date('d.m.Y', strtotime($aOne['complaint_time']))?><br /><?=date('H:i', strtotime($aOne['complaint_time']))?>
    </td>
    <td>
        <div class="compliant-item">
            <span class="compliant-autor"><a href="/users/<?=$aOne['spamer_login']?>"><?=$aOne['spamer_uname']?> <?=$aOne['spamer_usurname']?> [<?=$aOne['spamer_login']?>]</a>&nbsp;
            
            <?=date('d.m.Y', strtotime($aOne['post_time']))?> в <?=date('H:i', strtotime($aOne['post_time']))?>&nbsp;
            <?php 
            if ( !$aWarn[$aOne['spamer_id']] ) {
            	$aWarn[$aOne['spamer_id']]  = $oUser->GetField( $aOne['spamer_id'], $err, 'warn' );
            	$aWhere[$aOne['spamer_id']] = $oUser->GetField( $aOne['spamer_id'], $err, 'ban_where' );
            }
            
            $nWarn  = intval( $aWarn[$aOne['spamer_id']] );
            $bWhere = $aWhere[$aOne['spamer_id']];
            
            if ( $nWarn ) { 
            ?>
            <span class="color-a30000"><?=$nWarn?> <?=ending($nWarn, 'предупреждение', 'предупреждения', 'предупреждений')?></span>
            <?php 
            }
            else {
            ?>нет предупреждений<?php
            }
            ?>
            
            <?php if ( $aOne['spam_cnt'] > 1 && !$spamer && !$spamer_ex ) { ?>
            <span class="color-a30000"><a href="/siteadmin/messages_spam/?cmd=go&spamer=<?=$aOne['spamer_login']?>&spamer_ex=1">и еще <?=$aOne['spam_cnt']?> <?=ending($aOne['spam_cnt'], 'жалоба', 'жалобы', 'жалоб')?> на этого пользователя</a></span>
            <?php } ?>
            </span>
            
            <p><?=reformat($aOne['msg_text'], 45)?></p>
            
            <? // прикрепленные файлы ------------------
            if ( $aOne['files'] ) {
		    $nn = 1;
		    ?>
		    <div class="filesize1">
                <div class="attachments attachments-p">
    		    <?php
    			foreach ($aOne['files'] as $attach) {
    				$att_ext = CFile::getext($attach['fname']);
    				$aData   = getAttachDisplayData( $aOne['spamer_login'], $attach['fname'], 'contacts', 1000, 600, 307200, 0 );
                    
                    if ( $aData && $aData['success'] ) {
                        if ( $aData['file_mode'] || $aData['virus_flag'] || $att_ext == "swf" ) {
                            $str = viewattachLeft( $aOne['spamer_login'], $attach['fname'], 'contacts', $file, 0, 0, 0, 0, 0, 0, $nn );
                        	echo '<div class = "flw_offer_attach">', $str, '</div>';
                        }
                        else {
                            echo "<div class = \"flw_offer_attach\"><div style=\"float: left; margin-right:7px;\">$nn.</div><img src=\"".WDCPREFIX.'/users/'.$aOne['spamer_login'].'/contacts/'.$aData['file_name']."\" alt=\"{$aData['file_name']}\" title=\"{$aData['file_name']}\" width=\"{$aData['img_width']}\" height=\"{$aData['img_height']}\" /></div>";
                        }
                    }
    				
    				$nn++;
    			}
    			?>
                </div>
			</div>
			<?php } //---------------------------------- ?>
            
        </div>
        
        <div id="div_compliant_<?=$aOne['spamer_id']?><?=$aOne['msg_md5']?>">
            <div class="compliant-item">
                <div class="form fs-o">
                    <b class="b1"></b>
                    <b class="b2"></b>
                    <div class="form-in">
                        <span class="compliant-autor"><?=( $aOne['complaint_text'] ? 'Комментарий от' : 'Пожаловался' )?>&nbsp;
                        <a href="/users/<?=$aOne['user_login']?>"><?=$aOne['user_name']?> <?=$aOne['user_surname']?> [<?=$aOne['user_login']?>]</a> <?=date('d.m.Y', strtotime($aOne['complaint_time']))?> в <?=date('H:i', strtotime($aOne['complaint_time']))?></span>
                        <?php if ( $aOne['complaint_text'] ) { ?>
                        <p><?=reformat($aOne['complaint_text'], 45)?></p>
                        <?php } ?>
                    </div>
                    <b class="b2"></b>
                    <b class="b1"></b>
                </div>
            </div>
        </div>
        
        <div id="div_all_compliants_<?=$aOne['spamer_id']?><?=$aOne['msg_md5']?>" style="display: none;">
        </div>
        
        <?php if ( $aOne['complaint_cnt'] > 1 ) { ?>
        <span id="span_compliants" class="all-compliant"><a onclick="messages_spam.getSpamComplaints(<?=$aOne['spamer_id']?>, '<?=$aOne['msg_md5']?>');" href="javascript:void(0);">Показать все <?=$aOne['complaint_cnt']?> <?=ending($aOne['complaint_cnt'], 'жалобу', 'жалобы', 'жалоб')?></a></span>
        <?php } ?>
    </td>
    <td class="last">
        <a onclick="if (confirm('Вы уверены, что это не спам?')) {window.location.href='/siteadmin/messages_spam/?task=del&sid=<?=$aOne['spamer_id']?>&md5=<?=$aOne['msg_md5']?>'}" class="btnr-mb" href="javascript:void(0);"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Это не спам</span></span></span></a>
        <?php $sEdit = $bWhere ? '1' : '0'; ?>
        <a onclick="banned.userBan(<?=$aOne['spamer_id']?>, 'admin_messages_spam',<?=$sEdit?>)" class="btnr-mb" href="javascript:void(0);"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Заблокировать</span></span></span></a>
        <?php if ( $nWarn < 3 ) {
            $sOnCLick = "banned.warnUser({$aOne['spamer_id']}, 0, 'admin_messages_spam', 'admin_messages_spam', 0); return false;";
        } 
        else {
            $sOnCLick = "adminLogWarnMax()";
        } ?>
        <a onclick="<?=$sOnCLick?>" class="btnr-mb" href="javascript:void(0);"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Предупредить</span></span></span></a>
    </td>
</tr>
<?php } ?>
</table>

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
<div style="margin-top: 15px;">Нет сообщений, удовлетворяющих условиям выборки</div>
<?php
}
?>

<iframe width=132 height=142 name="gToday:contrast" id="gToday:contrast" src="DateRangeCustomMY/ipopeng.php" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>

<?php if ( $error ) { ?>
<script type="text/javascript">
alert('<?=$error?>');
</script>
<?php } ?>