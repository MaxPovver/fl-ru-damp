<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

session_start();

if(!(hasPermissions('adm') && hasPermissions('suspicioususers'))) {
  header ("Location: /404.php");
  exit;
}
$words_login = users::GetSuspiciousWordsLogin();
if($words_login) {
    foreach($words_login as $word) {
        $sword[] = $word['word'];
    }
}
$words_name = users::GetSuspiciousWordsName();
if($words_name) {
    foreach($words_name as $word) {
        $nword[] = $word['word'];
    }
}
?>

<script type="text/javascript">
function suspResolve( action, uid ) {
    var confirmMsg = '';
    
    if ( action == 'activate' || action == 'hide' ) {
        confirmMsg = 'Вы действительно хотите разрешить такие логин, имя и фамилию?';
    }
    else {
        confirmMsg = 'Вы действительно хотите забанить пользователя?';
    }
    
    if ( confirm(confirmMsg) ) {
        var page = ''; 
        var arr = window.location.search.replace("?", "").split("&");
        for (var i = 0; i < arr.length; i++) {
            if (arr[i].indexOf("page=") != -1) {                
                page = '&' + arr[i];
                break;
            }
        }
        window.location.href = window.location.href.split('?')[0] + '?action='+ action +'&uid=' + uid + page;
        var scrollTop = 0;
        if (!Browser.chrome && !Browser.safari) {
		    scrollTop = document.documentElement.scrollTop;
		} else {
		    scrollTop = document.body.scrollTop;
		}
        Cookie.write("siteadminscroll", scrollTop);
        return false; 
    }
    else { 
        return false; 
    }
}
window.addEvent('load', function() {
    var scrollTop = parseInt(Cookie.read("siteadminscroll"));    
    if (scrollTop) {
        if (!Browser.chrome && !Browser.safari) {
            document.documentElement.scrollTop = scrollTop;
        } else {
            document.body.scrollTop = scrollTop;
        }
	}
});
</script>

<div class="ban-razban" style="position:relative">
	<h3>Модерирование \ Подозрительные пользователи</h3>
	<?php if($error_string) print(view_error($error_string))."<br/>"; ?>
    <strong>1. Список слов для проверки логина</strong>
	<p>(Добавте слова через запятую или удалите)</p>
	<br/>
	<form method="POST">
	<input type="hidden" name="action" value="save_words">
	<input type="hidden" name="type" value="1">
	<textarea name="suspicious_words" style="width:600px" rows="10"><?= $sword?implode(", ",$sword):""?></textarea>
	<br/><br/>
	<input type="submit" name="save_words" value="Сохранить">
	</form>
	<br/><br/>
	<strong>2. Список слов для проверки имени и фамилии</strong>
	<p>(Добавте слова через запятую или удалите)</p>
	<br/>
	<form method="POST">
	<input type="hidden" name="action" value="save_words">
	<input type="hidden" name="type" value="2">
	<textarea name="suspicious_words" style="width:600px" rows="10"><?= $nword?implode(", ",$nword):""?></textarea>
	<br/><br/>
	<input type="submit" name="save_words" value="Сохранить">
	</form>
	<br/><br/>
	
    <?php if ( $words_login ) { ?>
    <strong>Слова в логине:</strong>
    <p>
        <?
        $n=1;
        foreach($words_login as $word) {
            echo $word['word'];
            if(count($words_login)!=$n) echo ", ";
            $n++;
        }
        ?>
    </p>
    <br/>
    <? } ?>
    <?php if ( $words_name ) { ?>
    <strong>Слова в имени и фамилии:</strong>
    <p>
        <?
        $n=1;
        foreach($words_name as $word) {
            echo $word['word'];
            if(count($words_name)!=$n) echo ", ";
            $n++;
        }
        ?>
    </p>
    <br/>
    <? } ?>
    <br/>
    
    <? /*[<a href="?action=reset" onClick="if(confirm('Вы действительно хотите перепроверить всех подозрительных пользователей?')) { return true; } else { return false; }">Перепроверить всех подозрительных пользователей</a>]
    <br/><br/> */ ?>

	<? if ( $mRid && pg_num_rows($mRid) ) { ?>
	

        [<a href="?action=clear" onClick="if(confirm('Вы действительно хотите разрешить всех пользователей из списка подозрительных?')) { return true; } else { return false; }">Разрешить всех</a>]
        <br/><br/>
        
        <?php while ( $user = pg_fetch_assoc($mRid) ) {
            $avatar = "/images/no_foto_b.gif";
            if ($user['photo']) {
                $avatar = WDCPREFIX."/users/".substr($user['login'], 0, 2)."/{$user['login']}/foto/{$user['photo']}";
            }?>
            <div class="m-co-u">
            <?php            
            $sAppAction = ( $user['activate'] == 't' ) ? 'activate'   : 'hide'; 
            $sRejAction = ( $user['is_banned'] && !$user['ban_where'] ) ? 'ban' : 'userban';            
            ?>
            [<a href="javascript:void(0);" onClick="return suspResolve('<?=$sAppAction?>', '<?=$user['uid']?>');">Разрешить</a>&nbsp;|&nbsp;<a href="javascript:void(0);" onClick="return suspResolve('<?=$sRejAction?>', '<?=$user['uid']?>');">Забанить</a>]&nbsp;&nbsp;<span class="frlname11">
            <img class="sus_avatar" alt="<?=$user['login'] ?>" src="<?=$avatar ?>" style="width:32px; height:32px;" />
            &nbsp;<a class="frlname11" href="/users/<?=$user['login']?>/" title="<?=$user['uname']?> <?=$user['usurname']?>"><?=$user['uname']?> <?=$user['usurname']?></a> [<a class="frlname11" href="/users/<?=$user['login']?>/" title="<?=$user['login']?>"><?=$user['login']?></a>]</span><br>
            </div>
        <? } ?>
        <?php if ($totalPages > 1) {?>
            <div style="width:100%; padding-top:15px">
            <ul style="width:100%; text-align:center">
            <?php if ($itemBack) {?>
            <li style="float:left; list-style-type: none;padding:0px 5px">
                <a href="/siteadmin/suspicious-users/?page=<?=$page - 1 ?>" > &lt;&lt; </a>
            </li>
            <? } ?>
            <?php for ($i = $pagingStart; $i < $pagingLimit; $i++) {?>
            <li style="float:left; list-style-type: none;padding:0px 5px">
                <?php if ($page != $i) {?>
                    <a href="/siteadmin/suspicious-users/?page=<?=$i ?>" ><?=$i ?></a>
                <? } else {print $i;}?>
            </li>
            <?php }?>
            <?php if ($itemNext) {?>
            <li style="float:left; list-style-type: none;padding:0px 5px">
                <a href="/siteadmin/suspicious-users/?page=<?=$page + 1 ?>" > &gt;&gt; </a>
            </li>
            <? } ?>
            </ul>
            </div>
        <?php }?>
	<? } else { ?>

	<div class="m-so c">
		<h4>Подозрительных пользователей нет</h4>
	</div>

	<? } ?>

</div>
