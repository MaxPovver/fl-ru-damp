<?
$ban_href = '';
$ban_title = '';
$ban_class = '';
$ban_onclick = '';

$edit_href = '';
$edit_title = '';
$edit_onclick = '';

$delete_href = '';
$delete_title = '';
$delete_onclick = '';

$block_title   = '';
$block_onclick = '';
$is_admin_site = hasPermissions('communes');
    if (($mod & (
            commune::MOD_COMM_ADMIN
            | commune::MOD_ADMIN
            | commune::MOD_MODER
            | commune::MOD_COMM_MODERATOR
            | commune::MOD_COMM_AUTHOR))
      || $top['user_id'] == get_uid(false)) {


        if ( $top['user_id'] != $user_id
            && !$top['member_is_admin']
            && $top['commune_author_id'] != $top['user_id']
            && !hasPermissions('communes',$top['user_id'])
            && $top['member_is_accepted']
            && (
                !($top['user_is_banned'] || $top['member_is_banned']) 
                || (($mod & ( commune::MOD_ADMIN | commune::MOD_MODER )) && ($top['user_is_banned'] || $top['user_ban_where']))
               )
        ) {
            
        // предупреждения и бан -----------------
        $ban_href = 'javascript:void(0)';
        
        if ( $mod & (commune::MOD_ADMIN | commune::MOD_MODER) ) {
            if ( $top['user_warn_cnt'] >= 3 || $top['user_is_banned'] || $top['user_ban_where'] ) {
                $ban_onclick = "banned.userBan({$top['user_id']}, '{$top['id']}',0)";
                $ban_title   = ($top['user_is_banned'] || $top['user_ban_where']) ? 'Разбанить' : "Забанить!";
                $ban_count   = '';
                $ban_class   = 'lnk-red';
            }
            else {
                $ban_onclick = "banned.warnUser({$top['user_id']}, 0, 'std', '{$top['id']}', 0)";
                $ban_title   = "Сделать предупреждение ";
                $ban_count   = " — ".($top['user_warn_cnt'] ? $top['user_warn_cnt'] : 0);
                $ban_class   = 'lnk-dred';
            }
        }
        else {
            $ban_onclick = "if(warning()) xajax_BanMemberForTopic('{$box_id}', {$msg_id}, '{$top['member_id']}', {$user_id}, {$mod}, '{$page}','{$om}', '{$site}','{$is_fav}');";
            
            if ( $top['member_warn_count'] >= 3 || $top['member_is_banned'] ) {
            	$ban_title = ($top['member_is_banned']) ? 'Разбанить' : "Забанить!";
                $ban_class = 'lnk-red';
            }
            else {
                $ban_title = "Сделать предупреждение ({$top['member_warn_count']})";
                $ban_class = 'lnk-dred';
            }
        }
        //---------------------------------------
        
        // блокировка/разблокировка топика ------
        if ( $top['is_blocked_s'] == 't' || $top['is_blocked_c'] == 't' ) {
        	$block_title = 'Разблокировать';
        	
        	if ( $top['is_blocked_c'] == 't' ) {
        		$block_onclick = "if(warning()) xajax_BlockedTopic({$top['commune_id']},{$top['theme_id']},{$top['id']},'unblock')";
        	}
        	else {
        	    if ( $mod & ( commune::MOD_ADMIN | commune::MOD_MODER ) ) {
                    $block_onclick = "banned.unblockedCommuneTheme({$top['commune_id']},{$top['theme_id']},{$top['id']})";
        	    }
        	}
        }
        else {
            $block_title = 'Заблокировать';
            
            if ( $mod & ( commune::MOD_ADMIN | commune::MOD_MODER ) ) {
                $block_onclick = "banned.blockedCommuneTheme({$top['commune_id']},{$top['theme_id']},{$top['id']})";
    	    }
    	    else {
    	        $block_onclick = "if(warning()) xajax_BlockedTopic({$top['commune_id']},{$top['theme_id']},{$top['id']},'block')";
    	    }
        }
        //---------------------------------------
            }




        $delete_onclick = "__commDT('$box_id', $msg_id, $user_id, $mod, '$page','$om', '$site','$is_fav');";
        if ( intval($top["deleted_id"]) ) {
            $delete_onclick = "xajax_restoreDeletedPost('$box_id', $msg_id, $user_id, $mod, '$page','$om', '$site','$is_fav');";
        }
        $delete_href = 'javascript:void(0)';


        $edit_href = 'javascript:void(0)';
        $edit_onclick = "/*var m=document.getElementById('idEditCommentForm_$msg_id');  if(__commLastOpenedForm!=m|| __commLastOpenedForm.action!='Edit.post')*/
           xajax_CreateCommentForm('$edit_id', {$top['id']}, $msg_id, $commune_id, $om, ".($site == 'Topic' ? 0 : 1).", 'Edit.post', $mod, ".($top['cnt_files']).", ".($site=='Topic'?intval(__paramInit('int', 'draft_id', 'draft_id')):0).", '".__paramInit('string', 'attachedfiles_session', 'attachedfiles_session')."');";

$ul_attrs = '';
if ($is_admin_site) {
    $params = array(
        'uid' => $top['id'],
        'code' => 4,
        'link' => $GLOBALS['host'] . '/commune/?id=' . $top['commune_id'] . '&site=Topic&post=' . $top['id'],
        'name' => $top['title']
    );
    foreach ($params as $key => $value) {
        $ul_attrs .= ' data-banned-' . $key . '="' . $value . '"';
    }
            
}
?>

<ul class="b-post__links"<?=$ul_attrs?>>
<?php if(($mod & (commune::MOD_COMM_MODERATOR | commune::MOD_COMM_AUTHOR | commune::MOD_ADMIN | commune::MOD_MODER) || $top['user_id'] == get_uid(false)) && ($top['is_blocked_s'] != 't' && $top['is_blocked_c'] != 't' || hasPermissions('communes') || $top['admin_login_c'] == $_SESSION['login'])) { ?>
    <?php if ($top['category_id'] && $top['category_name']) {?>
        <li class="b-post__links-item b-post__links-item_padright_10">
            Раздел: <a class="b-post__link b-post__link_color_000" href="<?=getFriendlyURL('commune_commune', $top['commune_id'])?>?om=<?=__paramInit("int", "om")?__paramInit("int", "om"):'0' ?>&cat=<?=$top['category_id'] ?>"><?=$top['category_name'] ?></a>
       </li>
    <?}?>
    <li class="b-post__links-item b-post__links-item_padright_10">
        <a href="<?=getFriendlyURL('commune', $msg_id)?><?= ($page>1?'?bp='.$page : '')?><?= "?taction=edit" ?>" class="b-post__link b-post__link_color_c10601" id="c_edit_lnk">
            Редактировать
        </a>
    </li>
    <li class="b-post__links-item b-post__links-item_padright_10">
        <a href="<?= $delete_href;?>" onclick="<?= $delete_onclick;?>" class="b-post__link b-post__link_color_c10601" id="c_edit_lnk">
            <? if( !intval($top["deleted_id"]) ) {?>Удалить<? } else { ?>Восстановить<?} ?>
        </a>
    </li>
    <?php if ( $block_title && $block_onclick ) { ?>
    <li id="theme-button-<?=$top['theme_id']?>" class="b-post__links-item b-post__links-item_padright_10">
        <a href="javascript:void(0)" onclick="<?=$block_onclick?>" class="b-post__link b-post__link_color_c10601">
            <?=$block_title?>
        </a>
    </li>
    <?php } ?>
    <? if($ban_href || $ban_onclick){ ?>
    <li class="b-post__links-item b-post__links-item_padright_10 warnlink-<?=$top['user_id']?>">
        <a class="b-post__link b-post__link_color_c10601 <?= $ban_class;?>" onclick="<?= $ban_onclick;?>" href="<?= $ban_href;?>" class="lnk-dred">
            <?= $ban_title;?>
        </a>
        <?= $ban_count ?>
    </li>
    <? } ?>
<?php }?>
<? } else if ($top['category_id'] && $top['category_name']) {?>
<li class="b-post__links-item b-post__links-item_padright_10">
    Раздел: <a class="b-post__link b-post__link_color_000" href="<?=getFriendlyURL('commune_commune', $top['commune_id'])?>?om=<?=__paramInit("int", "om")?__paramInit("int", "om"):'0' ?>&cat=<?=$top['category_id'] ?>"><?=$top['category_name'] ?></a>
</li>
<?} ?>
</ul>
