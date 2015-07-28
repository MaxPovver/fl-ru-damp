<? if($auth_allow) { ?>
<br/>
<? if($error) { ?>
<div><?=$error?></div>
<? } ?>
<form method="post" action="">
    Логин: <input type="text" name="login"/> Пароль: <input type="password" name="passw"/>
    <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>" />
    <input type="submit" value="Войти"/>
</form>
<? } ?>