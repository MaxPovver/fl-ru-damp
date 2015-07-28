<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
$commune = new commune();
$uid = get_uid();
if (!($communes_my = commune::GetCommunes(NULL, $uid, NULL, commune::OM_CM_MY))) {
    $communes_my = array();
}
if (!($communes = commune::GetCommunes(NULL, NULL, $uid, commune::OM_CM_JOINED, $uid))) {
    $communes = array();
}
foreach ($communes_my as $cm) {
    $communes[] = $cm;
}
$c_signed = $commune->getSubscribedCommunes($uid);

?>
<form action="." method="post" id="subscr_form">
    <div class="b-layout b-layout_padtop_20">
        <h2 class="b-layout__title">Настройки уведомлений</h2>
        <?=view_info2($info_msg)?>
        <p class="b-layout__txt b-layout__txt_padbot_10">Присылать по электронной почте следующие уведомления:</p>
        <input type="hidden" name="action" value="update_subscr" />
        <ul class="settings-subscribe">
            
            <li><label><input type="checkbox" id="ch13" name="spm" value="1" <? if ($user->subscr[12]) 
            print " checked='checked'" ?> /> Получать ежедневную рассылку</label></li>           
            
            <li><label><input type="checkbox" id="ch1" name="newmsgs" value="1" <? if ($user->subscr[0]) 
            print " checked='checked'" ?> /> Уведомления о новых сообщениях</label></li>
            <li><label><input type="checkbox" id="ch6" name="commune" class="i-chk" value="1" <? if ($user->subscr[5])
            print " checked='checked'" ?> /> Уведомления о новых действиях в сообществах</label></li>
            <li><label><input type="checkbox" class="i-chk" id="ch7" name="commune_topics" value="1" <? if ($user->subscr[6])
            print " checked='checked'" ?> onclick="this.getParent('li').getElements('ul input').set('checked', this.get('checked'))" /> Уведомления о новых темах в сообществах</label>
                <? if (is_array($communes) && count($communes)) { ?>
                    <ul>
                        <? foreach ($communes as $ckey => $comm) { ?>
                            <? if ($comm['id'] != 0) { ?>
                                <li>
                                    <label>
                                        <input type="checkbox" class="i-chk" name="comm[]" value="<?= $comm['id'] ?>" id="chcomm<?= $comm['id'] ?>" <?= $user->subscr[6] && in_array($comm['id'], $c_signed) ? 'checked="checked"' : '' ?> onclick="this.get('checked') && $('ch7').set('checked', 1)" /> <?= $comm['name'] ?>
                                    </label>
                                </li>
                            <? } ?>
                        <? } ?>
                    </ul>
                <? } ?>
            </li>
            <li><label><input type="checkbox" class="i-chk" id="ch2" name="vacan" value="1" <? if ($user->subscr[1])
            print " checked='checked'" ?> /> Уведомления об ответе на опубликованный проект</label></li>
            <li><label><input type="checkbox" class="i-chk" id="ch5" name="prj_comments" value="1" <? if ($user->subscr[4])
            print " checked='checked'" ?> /> Комментарии к сообщениям/комментариям в проектах</label></li>
            <? if (BLOGS_CLOSED == false) { ?>
            <li><label><input type="checkbox" class="i-chk" id="ch3" name="comments" value="1" <? if ($user->subscr[2])
            print " checked='checked'" ?> /> Комментарии к сообщениям/комментариям в блогах</label></li>
            <? } ?>
            <li><label><input type="checkbox" class="i-chk" id="ch9" name="contest" value="1" <? if ($user->subscr[8])
            print " checked='checked'" ?> /> Уведомления в конкурсах</label></li>
            <li><label><input type="checkbox" class="i-chk" id="ch8" name="adm_subscr" value="1" <? if ($user->subscr[7])
            print " checked='checked'" ?> /> Новости от команды FL.ru</label></li>
            <li><label><input type="checkbox" class="i-chk" id="ch10" name="team" value="1" <? if ($user->subscr[9])
            print " checked='checked'" ?> /> Уведомления о добавлении в избранные</label></li>
            <li><label><input type="checkbox" class="i-chk" id="ch16" name="payment" value="1" <? if ($user->subscr[15])
            print " checked='checked'" ?> /> Уведомления личного счета</label></li>
        </ul>      
        <button class="b-button b-button_flat b-button_flat_green" onclick="$('subscr_form').submit()">Изменить</button> 
    </div>
</form>
