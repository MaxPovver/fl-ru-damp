<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/admin_log.common.php' );
$xajax->printJavascript( '/xajax/' );
?>

<?php include_once('comments.php') ?>

<?php if ( $sViweId ) { 
    
    // сюда попадаем из email уведомления о комментарии 
    // чтобы не искать по всей ленте кудабы админа направить
    
?>

<h3>Действия / <?=$aOne['act_name']?></h3>

<div class="admin-lenta">
    <table>
    <?php 
    $aOne['object_name'] = ( $aOne['obj_code'] != admin_log::OBJ_CODE_OFFER) ? $aOne['object_name'] : htmlspecialchars($aOne['object_name']);
    $sObjName  = $aOne['object_name'] ? hyphen_words(reformat($aOne['object_name'], 60), true) : '<без названия>';
    $sObjLink  = ( $aOne['object_link'] && $aOne['object_deleted'] != 't' ) ? $aOne['object_link'] : '';
    $sObjClass = $aClass[$aOne['obj_code']];
    $sActClass = '';
    $sComments = '';
    
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
    }
    ?>
    <tr>
        <td class="cell-action cell-top <?=$sActClass?>"><?=$aOne['act_name']?></td>
        <td class="cell-what cell-top"><?=admin_log::$aObj[$aOne['obj_code']]['short']?></td>
        <td class="cell-name">
            <?php if ( $sObjLink ) { ?><a target="_blank" href="<?=$sObjLink?>" class="<?=$sObjClass?>"><?=$sObjName?></a><?php } else { ?><?=$sObjName?><?php } ?>
            <?php if ( in_array($aOne['act_id'], $aReasonData) ) { echo '<br/>', $aOne['admin_comment']; } ?>
            <?php if ( in_array($aOne['act_id'], $aLogShowAuthor) ) { echo '<br/><a href="/users/'. $aOne['aut_login'] .'" target="_blank">'. $aOne['aut_uname']. ' '. $aOne['aut_usurname'] .' ['. $aOne['aut_login'] .']</a>'; } ?>
        </td>
        <td class="cell-who cell-top"><?php if ( $aOne['adm_login'] ) { ?><a target="_blank" href="/users/<?=$aOne['adm_login']?>">[<?=$aOne['adm_login']?>]</a><?php } else { ?>[не известно]<?php } ?></td>
        <td class="cell-date cell-top"><?php if ( $aOne['act_time'] ) { ?><?=date('d.m.Y H:i', strtotime($aOne['act_time']))?><?php } else { ?>не известно<?php } ?></td>
        <td class="cell-com"><?=$sComments?></td>
    </tr>
    <tr>
        <td colspan="6" style="padding: 0px;">
            <div style="padding-left: 20px;"><?=$comments_html?></div>
        </td>
    </tr>
    </table>
</div>
<?php
    
}
else {
    
    // сюда попадаем когда просто смотрим ленту
    
?>

<h3>Действия / Лента всех действий</h3>

<!-- Фильтр старт -->
<div class="form form-acnew">
	<b class="b1"></b>
	<b class="b2"></b>
	<div class="form-in">
        <h4 class="toggle"><a href="javascript:void(0);" onclick="var mySlide = new Fx.Slide('slideBlock').toggle();" class="lnk-dot-666">Фильтр</a></h4>
        <div id="slideBlock" class="slideBlock">
            <form name="frm_filter" id="frm_filter" method="GET" onsubmit="return checkDateFilter();">
            <input type="hidden" id="cmd" name="cmd" value="filter">
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
                        </select>
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
                
<div class="b-radio b-radio_layout_vertical b-radio_clear_both b-radio_padleft_80">
    <div class="b-radio__item b-radio__item_padbot_10">
        <input <?=$shiftChecked ?> type="radio" value="shift" name="period" class="b-radio__input" id="shift" onchange="switchTimeInputsEnable('shift')">
        <label for="shift" class="b-radio__label b-radio__label_valign_bot b-radio__label_width_50" style="cursor:pointer">Смена</label>
        <div class="b-select b-select_width_160 b-select_inline-block b-select_valign_bot b-select_top_2">
        <?           
            $timeFrom = $fromH&&$fromI ? "$fromH:$fromI" : "";
            $timeTo   = $toH&&$toI     ? "$toH:$toI"     : "";
        ?>
            <select <?=$shiftEnabled ?> class="b-select__select" name="shifts_list" id="shifts_list" onchange="setTimeInterval()"><?php 
            foreach ($shifts as $i=>$shift) {
                ?><option value="<?=$shift["id"] ?>" <?=($shift["id"] == $shiftId?'selected="selected"':'') ?>time_from="<?=substr($shift["time_from"], 0, 5) ?>" time_to="<?=substr($shift["time_to"], 0, 5) ?>" >Смена <?=($i + 1) ?></option><?
                if ( (($i == 0) || ($shift["id"] == $shiftId)) && ($shiftChecked) ) {
                    $fromH = substr($shift["time_from"], 0, 2);
                    $fromI = substr($shift["time_from"], 3, 2);
                    $toH   = substr($shift["time_to"], 0, 2);
                    $toI   = substr($shift["time_to"], 3, 2);
                }
            }
            ?></select>
            <input type="hidden" name="from_h" id="from_h" value="<?=$fromH?>"/>
            <input type="hidden" name="from_i" id="from_i" value="<?=$fromI?>"/>
            <input type="hidden" name="to_h" id="to_h" value="<?=$toH?>"/>
            <input type="hidden" name="to_i" id="to_i" value="<?=$toI?>"/>
        </div>
    </div>
    <div class="b-radio__item b-radio__item_padbot_20">
        <input <?=$timeChecked ?> type="radio" value="time" name="period" class="b-radio__input" id="time" onchange="switchTimeInputsEnable('time')">
        <label for="time" class="b-radio__label b-radio__label_valign_bot b-radio__label_width_50" style="cursor:pointer">Период</label>
        <div class="b-input b-input_inline-block b-input_width_60 b-input_valign_bot b-input_top_2">
            <input <?=$timeEnabled ?> type="text" value="<?=$timeFrom ?>" class="b-input__text b-input__text_align_center" name="timeFrom" id="timeFrom" maxlength="5">
        </div>&nbsp;&nbsp;&mdash;&nbsp;&nbsp;<div class="b-input b-input_inline-block b-input_width_60 b-input_valign_bot b-input_top_2">
            <input <?=$timeEnabled ?> type="text" value="<?=$timeTo ?>" class="b-input__text b-input__text_align_center" name="timeTo" id="timeTo" maxlength="5">
        </div>
    </div>
</div>                
                <div class="form-el">
                    <label class="form-l">Действие:</label>
                    <div class="form-value fvs">
                        <select name="act" id="act" class="sw205" style="width: 395px;">
                            <option value="0">Все</option>
                            <?php foreach ( $actions as $aOne ) { 
                                $sSel = ($aOne['id'] == $act) ? ' selected' : '';
                            ?>
                            <option value="<?=$aOne['id']?>" <?=$sSel?>><?=admin_log::$aObj[$aOne['obj_code']]['name']?>: <?=$aOne['act_name']?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">Модератор:</label>
                    <div class="form-value fvs">
                        <select name="adm" id="adm" class="sw205" style="width: 395px;">
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
        $aAdminLogNewId = array();
    ?>
    <table>
        <?php foreach ( $log as $aOne ) { 
            $aOne['object_name'] = ( $aOne['obj_code'] != admin_log::OBJ_CODE_OFFER) ? $aOne['object_name'] : htmlspecialchars($aOne['object_name']);
            $sObjName  = $aOne['object_name'] ? hyphen_words(reformat($aOne['object_name'], 60), true) : '<без названия>';
            $sObjLink  = ( $aOne['object_link'] && $aOne['object_deleted'] != 't' ) ? $aOne['object_link'] : '';
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
            
            if ( $aOne['is_new'] ) {
            	$sActClass .= ' cell-action-bold';
            }
        ?>
        <tr id="tr_<?=$aOne['id']?>" onclick="window.location.href='<?=$sHref?>'">
            <td class="cell-action cell-top <?=$sActClass?>"><a name="lid_<?=$aOne['id']?>"></a><?=$aOne['act_name']?></td>
            <td class="cell-what cell-top"><a href="<?=$sHref?>" class="lnk-dot-666"><?=admin_log::$aObj[$aOne['obj_code']]['short']?></a></td>
            <td class="cell-name">
                <?php if ( $sObjLink ) { ?><a target="_blank" href="<?=$sObjLink?>" class="<?=$sObjClass?>"><?=$sObjName?></a><?php } else { ?><?=$sObjName?><?php } ?>
                <?php if ( in_array($aOne['act_id'], $aReasonData) ) { echo '<br/>', $aOne['admin_comment']; } ?>
                <?php if ( in_array($aOne['act_id'], $aLogShowAuthor) ) { echo '<br/><a href="/users/'. $aOne['aut_login'] .'" target="_blank">'. $aOne['aut_uname']. ' '. $aOne['aut_usurname'] .' ['. $aOne['aut_login'] .']</a>'; } ?>
            </td>
            <td class="cell-who cell-top"><?php if ( $aOne['adm_login'] ) { ?><a target="_blank" href="/users/<?=$aOne['adm_login']?>">[<?=$aOne['adm_login']?>]</a><?php } else { ?>[не известно]<?php } ?></td>
            <td class="cell-date cell-top"><?php if ( $aOne['act_time'] ) { ?><?=date('d.m.Y H:i', strtotime($aOne['act_time']))?><?php } else { ?>не известно<?php } ?></td>
            <td class="cell-com"><?=$sComments?></td>
        </tr>
        <tr>
            <td colspan="6" style="padding: 0px;">
                <div style="padding-left: 20px;" id="div_comments_<?=$aOne['id']?>"><?php if ( $sLogId == $aOne['id'] && $comments_html ) { echo $comments_html; } ?></div>
            </td>
        </tr>
        <?php } ?>
    </table>
    
    <?php  
    if ( $pages > 1 ) {
        $sHref = e_url( 'lid', null );
        $sHref = e_url( 'page', null, $sHref );
        $sHref = e_url( 'page', '', $sHref );
        echo get_pager2( $pages, $page, $sHref ); 
    } 
    
    echo printPerPageSelect( $log_pp );
    ?>
    
    <?php 
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
}
?>