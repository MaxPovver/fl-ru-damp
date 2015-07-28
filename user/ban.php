<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
	$uid = $user->GetUid($error, $login);
	if ($uid) {
	    $ban=$user->GetBan($uid);
?>
<div class="b-layout__right b-layout__right_float_right b-layout__right_width_240 b-page__desktop">
    <!-- Banner 240x400 -->
    <?= printBanner240(false); ?>
    <!-- end of Banner 240x400 -->
</div>
<div class="b-layout__one">
<? if ($ban['reason'] == 4)  {?>
	<h1 class="b-page__title">Аккаунт удален</h1>
   <div class="b-layout__txt b-layout__txt_padbot_20">Если у вас есть вопросы &mdash; напишите в <a href="http://feedback.fl.ru" target="_blank">службу поддержки</a></div>
<? } else { ?>
	<h1 class="b-page__title">Аккаунт заблокирован <?=($ban["to"] ? "до ".date("d.m.Y  H:i",strtotimeEx($ban["to"])) : "")?></h1>
    <div class="b-layout__txt b-layout__txt_padbot_20">
    <?
    switch ($ban["reason"]) {
        case 1:
            // print "<br/>Причина: Крайне некорректное поведение на сайте";
            break;
        case 2:
            print "Причина: Спам в блогах";
            break;
        case 3:
            print "Причина: Спам в проектах";
            break;
        default:
            break;
    }
    ?>
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_20"><?=($ban["comment"] ? "Комментарий администратора: ".$ban["comment"] : "")?></div>
    <div class="b-layout__txt b-layout__txt_padbot_20">Служба поддержки <a href="http://feedback.fl.ru" target="_blank">http://feedback.fl.ru</a></div>
    
<?  } ?>
</div>
<? }?>
