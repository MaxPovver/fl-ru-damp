<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/admin_log.common.php' );
$xajax->printJavascript( '/xajax/' );

$nProjCnt = 0;
?>
<script type="text/javascript">
banned.addContext( 'admin_log_page', -1, '', '' );
var filter_specs = new Array();
<?=$filter_specs?>
</script>
<?php include_once('comments.php') ?>

<?php if ( $sPrjId && $log ) { // просмотр истории конкретного проекта старт

$aOne = $log[0];

$sObjName  = $aOne['object_name'] ? $aOne['object_name'] : '<без названия>';
$sObjLink  = $aOne['object_link'] ? $aOne['object_link'] : 'javascript:void(0);';
$sActClass = '';
$nRowSpan  = $aOne['admin_comment'] ? 6 : 5;

if ( in_array($aOne['act_id'], $aRed) ){
    $sActClass = 'color-a30000';
}
elseif ( in_array($aOne['act_id'], $aGreen) ) {
    $sActClass = 'color-45a300';
}
?>
<h3>Действия / История проекта</h3>
<div class="plashka">
	<span><a href="<?=$_SESSION['admin_log_proj']?>">Назад</a></span>
</div>

<div class="admin-lenta">
    <table class="lenta-project">
	<tr>
    	<td class="cell-number"><h4>#<?=$aOne['object_id']?></h4></td>
    	<td class="cell-body"><h4>Проект: <a href="<?=$sObjLink?>"><?=hyphen_words(reformat($sObjName, 60), true)?></a></h4></td>
    	<td rowspan="<?=$nRowSpan?>" class="cell-blocking">
    	   <span><?php if ( $aOne['act_time'] ) { ?><?=date('d.m.Y H:i', strtotime($aOne['act_time']))?><?php } else { ?>не известно<?php } ?></span>[<?php if ( $aOne['adm_login'] ) { ?><a target="_blank" href="/users/<?=$aOne['adm_login']?>"><?=$aOne['adm_login']?></a><?php } else { ?>не известно<?php } ?>]
    	</td>
    </tr>
    <tr>
        <td class="cell-number">&nbsp;</td>
    	<td class="cell-body">Первоначальная версия: <strong><?=hyphen_words(reformat($project_history['name'], 30, 0, 1), true)?></strong></td>
    </tr>
	<tr>
    	<td class="cell-number">Раздел:</td>
    	<td class="cell-body"><?=projects::getSpecsStr($aOne['object_id'],' / ', ', ', true);?></td>
   	</tr>
	<tr>
    	<td class="cell-number">Автор:</td>
    	<td class="cell-body"><a href="/users/<?=$aOne['aut_login']?>" class="cell-autor"><?=$aOne['aut_uname']?> <?=$aOne['aut_usurname']?> [<?=$aOne['aut_login']?>]</a><?php if ($aOne['warn_cnt']) { ?>  <a onclick="xajax_getUserWarns(<?=$aOne['user_id']?>,'admin_log_page','');" href="javascript:void(0);" class="notice">Предупреждения: <span id="warn_<?=$aOne['user_id']?>_<?=$aOne['id']?>" class="warncount-<?=$aOne['user_id']?>"><?=$aOne['warn_cnt']?></span><?php } ?></a></td>
   	</tr>
   	<tr>
    	<td class="cell-number">Текст:</td>
    	<td class="cell-body"><?=hyphen_words(reformat($project['descr'],30,0,1), true)?></td>
   	</tr>
   	<tr>
        <td class="cell-number">&nbsp;</td>
    	<td class="cell-body"><strong>Первоначальная версия:</strong><br/><br/><?=hyphen_words(reformat($project_history['descr'], 70, 0, 0, 1), true)?></td>
    </tr>
    <?php if ( $project_attach || $project_history['attach'] ) { ?>
    <tr>
    	<td class="cell-number">Файлы:</td>
    	<td class="cell-body">
    	<?php 
    	if ( is_array($project_attach) ) { 
            $nn = 1;
            foreach ( $project_attach as $attach )
            {
            	$str = viewattachLeft( NULL, $attach["name"], $attach['path'], $file, 0, 0, 0, 0, 0, 0, $nn );
            	echo '<div class="flw_offer_attach" style="padding:0;">', $str, '</div>';
                $nn++;
            }
    	} 
    	else {
    	    echo 'Нет прикрепленных файлов';
    	}
    	?>
    	</td>
   	</tr>
   	    <?php if ( is_array($project_history['attach']) ) { ?>
   	<tr>
        <td class="cell-number">&nbsp;</td>
    	<td class="cell-body">
            <strong>Первоначальная версия:</strong><br/><br/>
            <?php
            $nn = 1;
            foreach ($project_history['attach'] as $attach) {
                $str = viewattachLeft( NULL, $attach["name"], $attach['path'], $file, 0, 0, 0, 0, 0, 0, $nn );
                echo '<div class="flw_offer_attach" style="padding:0;">', $str, '</div>';
                $nn++;
            }
            ?>
    	</td>
    </tr>
   	    <?php } ?>
   	<?php } ?>
   	<tr>
    	<td class="cell-number">Действие:</td>
    	<td class="<?=$sActClass?>">
    	<?=$aOne['act_name']?>
    	<?php if ( in_array($aOne['act_id'], $aReasonData) ) { 
    	    echo '<br/>', $aOne['admin_comment']; 
    	    $aOne['admin_comment'] = '';
    	} ?>
    	</td>
   	</tr>
   	<?php if ( $aOne['admin_comment'] ) { ?>
	<tr>
    	<td class="cell-number">Причина:</td>
    	<td class="cell-body"><?=hyphen_words(reformat($aOne['admin_comment'], 45), true)?></td>
   	</tr>
   	<?php } ?>
   	<tr class="last">
    	<td class="cell-number">&nbsp;</td>
    	<td class="cell-body" id="prj_<?=$aOne['object_id']?>_log_<?=$aOne['id']?>">
            <?php if ( $aOne['object_deleted'] != 't' ) { ?>
			<ul class="admin-links">
            	<li><a href="/public/?step=1&public=<?=$aOne['object_id']?>&red=<?=urlencode($_SERVER['REQUEST_URI'])?>" class="lnk-dot-666">Редактировать</a></li>
            	<?php if ( $aOne['last_act'] && !in_array($aOne['last_act'], $aRed) ) { 
            	    // если проект разблокирован - можно блокировать из последней записи ?>
            	<li><a onclick="adminLogGetProjBlock(<?=$aOne['object_id']?>,<?=$nProjCnt?>,10,0,0);" href="javascript:void(0);" class="lnk-dot-red">Заблокировать</a></li>
            	<?php 
            	}
            	elseif ( $aOne['src_id'] ) {
            	    // если проект заблокирован - можно редактировать или снимать только текущую блокировку ?>
            	<li><a onclick="adminLogGetProjBlock(<?=$aOne['object_id']?>,<?=$nProjCnt?>,9,<?=$aOne['src_id']?>,0);" href="javascript:void(0);" class="lnk-dot-red">Разблокировать</a></li>
            	<li><a onclick="adminLogGetProjBlock(<?=$aOne['object_id']?>,<?=$nProjCnt?>,9,<?=$aOne['src_id']?>,1);" href="javascript:void(0);" class="lnk-dot-red">Изменить причину блокировки</a></li>
            	<?php }
                ?>
            </ul>
            <?php } ?>
            
            <script type="text/javascript">
            aAdminLogProjName[<?=$nProjCnt?>] = '<?=clearTextForJS($sObjName)?>';
            </script>
		</td>
   	</tr>
    </table>
    
    <h4 class="history">История проекта:</h4>
    
    <table>
    <?php for ( $i = 0; $i < count($log); $i++ ) { 
        $aOne = $log[$i];
        $sComments = '';
        $sActClass = '';
        $sHref     = ( $sLogId == $aOne['id'] ) ? e_url( 'lid', null ).'#lid_'.$aOne['id'] : e_url( 'lid', $aOne['id'] ).'#lid_'.$aOne['id'];
        
        if ( $aOne['comments_cnt'] ) {
            $sNew = ($aOne['last_comment'] > $aOne['last_comment_view']) ? 'new-' : '';
            $sComments = '<a href="'.$sHref.'"><img id="ico_comm_' . $aOne['id'] . '" src="/images/'. $sNew .'comm.gif" alt="" /></a>';
        }
        
        if ( in_array($aOne['act_id'], $aRed) ){
            $sActClass = 'color-a30000';
        }
        elseif ( in_array($aOne['act_id'], $aGreen) ) {
            $sActClass = 'color-45a300';
        }
    ?>
    <tr id="tr_<?=$aOne['id']?>" onclick="window.location.href='<?=$sHref?>'">
        <td class="cell-action cell-top <?=$sActClass?>"><a name="lid_<?=$aOne['id']?>"></a><?=$aOne['act_name']?></td>
        <td class="cell-descript cell-top"><a href="<?=$sHref?>" class="lnk-dot-666"><?=hyphen_words(reformat($aOne['admin_comment'], 45), true)?></a></td>
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
    
</div>


<?php
    
} // просмотр истории конкретного проекта стоп
else { // список действий над проектами старт
    
?>
<h3>Действия / Проекты и конкурсы</h3>
<!-- Фильтр старт -->
<div class="form form-acnew">
	<b class="b1"></b>
	<b class="b2"></b>
	<div class="form-in">
        <h4 class="toggle"><a href="javascript:void(0);" onclick="var mySlide = new Fx.Slide('slideBlock').toggle();" class="lnk-dot-666">Фильтр</a></h4>
        <div id="slideBlock" class="slideBlock">
            <form name="frm_filter" id="frm_filter" method="GET" onsubmit="return checkDateFilter();">
            <input type="hidden" id="cmd" name="cmd" value="filter">
            <input type="hidden" id="site" name="site" value="proj">
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
                        <select name="act" id="act" class="sw205">
                            <option value="0">Все</option>
                            <?php foreach ( $actions as $aOne ) { 
                                $sSel = ($aOne['id'] == $act) ? ' selected' : '';
                            ?>
                            <option value="<?=$aOne['id']?>" <?=$sSel?>><?=$aOne['act_name']?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">Модератор:</label>
                    <div class="form-value fvs">
                        <select name="adm" id="adm" class="sw205">
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
                    <label class="form-l">Раздел:</label>
                    <div class="form-value fvs">
                        <select name="category" id="category" onChange="adminLogSubCatFilter(this.value, 0)">
                            <option value="0">Все категории</option>
                            <?php foreach ( $categories as $aOne ) { 
                                if ( $aOne['id']<=0) continue;
                                $sSel = ( $aOne['id'] == $category ) ? ' selected' : '';
                            ?>
                            <option value="<?=$aOne['id']?>" <?=$sSel?>><?=$aOne['name']?></option>
                             <?php } ?>
                        </select>&nbsp;
                        <select name="sub_category" id="sub_category" class="flt-p-sel">
                            <option value="0">Все подкатегории</option>
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
    ?>
	<table class="lenta-project">
	<tr>
        <?php $href = $sHref . '&sort=num&dir='. ($order=='num' ? ($direction == 'asc' ? 'desc' : 'asc') : 'asc'); ?>
    	<th class="cell-number"><a href="<?=$href?>" class="lnk-dot-666">Номер</a><? if ($order == 'num') { ?><a href="<?=$href?>"><img src="<?=$sSrc?>" alt=""  /></a><? } ?></th>
    	<?php $href = $sHref . '&sort=name&dir='. ($order=='name' ? ($direction == 'asc' ? 'desc' : 'asc') : 'asc'); ?>
    	<th class="cell-body"><a href="<?=$href?>" class="lnk-dot-666">Название</a><? if ($order == 'name') { ?><a href="<?=$href?>"><img src="<?=$sSrc?>" alt=""  /></a><? } ?></th>
    	<?php $href = $sHref . '&sort=date&dir='. ($order=='date' ? ($direction == 'asc' ? 'desc' : 'asc') : 'asc'); ?>
    	<th class="cell-blocking"><a href="<?=$href?>" class="lnk-dot-666">Дата</a><? if ($order == 'date') { ?><a href="<?=$href?>"><img src="<?=$sSrc?>" alt=""  /></a><? } ?></th>
    </tr>
    </table>
    
    <?php 
    $aCategory = array();
    
    foreach ( $log as $aOne ) {  
        $sObjName  = $aOne['object_name'] ? $aOne['object_name'] : '<без названия>';
        $sObjLink  = $aOne['object_link'] ? $aOne['object_link'] : 'javascript:void(0);';
        $sActClass = '';
        $nRowSpan  = $aOne['admin_comment'] ? 6 : 5;
        
        if ( in_array($aOne['act_id'], $aRed) ){
            $sActClass = 'color-a30000';
        }
        elseif ( in_array($aOne['act_id'], $aGreen) ) {
            $sActClass = 'color-45a300';
        }
        
        if ( !isset($aCategory[$aOne['object_id']]) ) {
            $aCategory[$aOne['object_id']] = projects::getSpecsStr($aOne['object_id'],' / ', ', ', true);
        }
        
        $sCategory = $aCategory[$aOne['object_id']];
    ?>
    <table class="lenta-project">
	<tr>
    	<td class="cell-number"><h4>#<?=$aOne['object_id']?></h4></td>
    	<td class="cell-body"><h4>Проект: <a href="<?=$sObjLink?>"><?=hyphen_words(reformat($sObjName, 60), true)?></a></h4></td>
    	<td rowspan="<?=$nRowSpan?>" class="cell-blocking">
    	   <span><?php if ( $aOne['act_time'] ) { ?><?=date('d.m.Y H:i:s', strtotime($aOne['act_time']))?><?php } else { ?>не известно<?php } ?></span>[<?php if ( $aOne['adm_login'] ) { ?><a target="_blank" href="/users/<?=$aOne['adm_login']?>"><?=$aOne['adm_login']?></a><?php } else { ?>не известно<?php } ?>]
    	</td>
    </tr>
	<tr>
    	<td class="cell-number">Раздел:</td>
    	<td class="cell-body"><?=$sCategory?></td>
   	</tr>
	<tr>
    	<td class="cell-number">Автор:</td>
    	<td class="cell-body"><a href="/users/<?=$aOne['aut_login']?>" class="cell-autor"><?=$aOne['aut_uname']?> <?=$aOne['aut_usurname']?> [<?=$aOne['aut_login']?>]</a><?php if ($aOne['warn_cnt']) { ?>  <a onclick="xajax_getUserWarns(<?=$aOne['user_id']?>,'admin_log_page','');" href="javascript:void(0);" class="notice">Предупреждения: <span id="warn_<?=$aOne['user_id']?>_<?=$aOne['id']?>" class="warncount-<?=$aOne['user_id']?>"><?=$aOne['warn_cnt']?></span></a><?php } ?></td>
   	</tr>
   	<tr>
    	<td class="cell-number">Действие:</td>
    	<td class="<?=$sActClass?>">
    	<?=$aOne['act_name']?>
    	<?php if ( in_array($aOne['act_id'], $aReasonData) ) { 
    	    echo '<br/>', $aOne['admin_comment']; 
    	    $aOne['admin_comment'] = '';
    	} ?>
    	</td>
   	</tr>
   	<?php if ( $aOne['admin_comment'] ) { ?>
	<tr>
    	<td class="cell-number">Причина:</td>
    	<td class="cell-body"><?=hyphen_words(reformat($aOne['admin_comment'], 45), true)?></td>
   	</tr>
   	<?php } ?>
   	<tr class="last">
    	<td class="cell-number">&nbsp;</td>
    	<td class="cell-body" id="prj_<?=$aOne['object_id']?>_log_<?=$aOne['id']?>">
			<ul class="admin-links">
                <li><a href="/siteadmin/admin_log/?site=proj&pid=<?=$aOne['object_id']?>" class="lnk-dot-666">История</a></li>
                <?php if ( $aOne['object_deleted'] != 't' ) { ?>
            	<li><a href="/public/?step=1&public=<?=$aOne['object_id']?>&red=<?=urlencode($_SERVER['REQUEST_URI'])?>" class="lnk-dot-666">Редактировать</a></li>
            	<?php if ( $aOne['last_act'] && !in_array($aOne['last_act'], $aRed) ) { 
            	    // если проект разблокирован - можно блокировать из последней записи ?>
            	<li><a onclick="adminLogGetProjBlock(<?=$aOne['object_id']?>,<?=$nProjCnt?>,10,0,0);" href="javascript:void(0);" class="lnk-dot-red">Заблокировать</a></li>
            	<?php 
            	}
            	elseif ( $aOne['src_id'] ) {
            	    // если проект заблокирован - можно редактировать или снимать только текущую блокировку ?>
            	<li><a onclick="adminLogGetProjBlock(<?=$aOne['object_id']?>,<?=$nProjCnt?>,9,<?=$aOne['src_id']?>,0);" href="javascript:void(0);" class="lnk-dot-red">Разблокировать</a></li>
            	<li><a onclick="adminLogGetProjBlock(<?=$aOne['object_id']?>,<?=$nProjCnt?>,9,<?=$aOne['src_id']?>,1);" href="javascript:void(0);" class="lnk-dot-red">Изменить причину блокировки</a></li>
            	<?php }
                ?>
            	<?php } ?>
            </ul>
            
            <script type="text/javascript">
            aAdminLogProjName[<?=$nProjCnt?>] = '<?=clearTextForJS($sObjName)?>';
            </script>
		</td>
   	</tr>
    </table>
    <?php 
        $nProjCnt++;
    } 
    ?>
    
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

<script type="text/javascript">
adminLogSubCatFilter( <?=$category?>, <?=$sub_category?> );
</script>

<?php if ( $error ) { ?>
<script type="text/javascript">
alert('<?=$error?>');
</script>
<?php } ?>

<?php
} // список действий над проектами стоп
?>



<!-- Блокировка/Разблокировка старт -->
<div id="ov-notice3" class="overlay ov-out" style="display: none;">
     <b class="c1"></b>
     <b class="c2"></b>
     <b class="ov-t"></b>
     <div class="ov-r">
         <div class="ov-l">
             <div class="ov-in">
                <div class="form-el">
                    <label class="form-l" id="block_num"></label>
                    <div class="form-value" style="padding: 3px 10px 0 10px;" id="block_name" title=""></div>
				</div>
             	<div class="form-el">
                    <label class="form-l">Действие:</label>
                    <div class="form-radios" style="padding: 0 10px 0 10px;">
                        <div class="form-value" id="dr1"> 
                            <label id="lr1">Разблокировать</label>
                        </div>
                    </div>
				</div>
				<?php /* // !!!срока блокировки проекта раньше не было. пока оставим
                 <div class="form-el">
                	<label class="form-l">Срок:</label>
                    <div class="form-value form-date">
                       <input type="text" maxlength="2" size="2" /> <select class="sel-month"><option>Сентября</option></select> <select class="sel-year"><option>2010</option></select>
                    </div>
                    <div class="form-value form-check">
                    	<input type="checkbox" value="" name="" id="r3" />
                        <label for="r3">Бессрочно</label>
                    </div>
                </div>
                */ ?>
				<div class="form-el">
					<label class="form-l">Причина:</label>
					<div class="form-value reason" id="prj_ban_div" style="padding: 0 10px 0 10px;">
                        <div id="bfrm_div_sel_0"><select disabled><option>Подождите...</option></select></div>
                        <textarea name="" cols="" rows=""></textarea>
                    </div>
				</div>                                         
                <div class="ov-btns">
                     <input type="button" id="adminLogSetProjBlock" onclick="adminLogSetProjBlock();" class="i-btn i-bold" value="Сохранить" />
                     <a href="javascript:void(0);" onclick="adminLogOverlayClose();" class="lnk-dot-grey">Отмена</a>
                 </div>
             </div>
         </div>
     </div>
     <b class="ov-b"></b>
     <b class="c3"></b>
     <b class="c4"></b>
 </div>
 <!-- Блокировка/Разблокировка стоп -->
 
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

<!-- редактирование предупреждения старт -->
<?php
include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
?>
<!-- редактирование предупреждения стоп -->