<?php if($sbr->isAdmin() || $sbr->isAdminFinance()) { ?>
<div class="b-username b-username_bold b-username_padbot_35">
    <span class="b-username__txt">Исполнитель</span>&#160;
    <?= $session->view_online_status($sbr->data['frl_login'], false, '&nbsp;', $activity) ?><a href="/users/<?= $sbr->data['frl_login'] ?>/" class="b-username__link b-username__link_color_f2922a" target="_blank"><?= ($sbr->data['frl_uname'] . ' ' . $sbr->data['frl_usurname']); ?></a> 
    <span class="b-username__login b-username__login_color_f2922a">
        [<a href="/users/<?= $sbr->data['frl_login'] ?>/" target="_blank" class="b-username__link"><?= $sbr->data['frl_login'] ?></a>] 
        <span class="b-username__marks"><?= view_mark_user_div($sbr->data["frl_is_pro"] === "t", false, $sbr->data["frl_is_team"] === 't', "") ?><?= $sbr->data['frl_is_verify'] == 't' ? view_verify() : '';?>
        </span>
    </span>
    <? 
    $user = new users();
    $user_banned = ($user->GetField($sbr->data['frl_id'], $ban_error, "is_banned", false) > 0) ? true : false;
    if ($user_banned) { ?>
        <span style="color:#000" ><b>Пользователь заблокирован.</b></span>
    <? } ?>
    <br/>
    <span class="b-username__txt">Заказчик</span>&#160;
    <?= $session->view_online_status($sbr->data['emp_login'], false, '&nbsp;', $activity) ?><a href="/users/<?= $sbr->data['emp_login'] ?>/" class="b-username__link b-username__link_color_6db335" target="_blank"><?= ($sbr->data['emp_uname'] . ' ' . $sbr->data['emp_usurname']); ?></a> 
    <span class="b-username__login b-username__login_color_6db335 ">
        [<a href="/users/<?= $sbr->data['emp_login'] ?>/" target="_blank" class="b-username__link"><?= $sbr->data['emp_login'] ?></a>] 
        <span class="b-username__marks"><?= view_mark_user_div($sbr->data["emp_is_pro"] === "t", true, $sbr->data["emp_is_team"] === 't', "") ?><?= $sbr->data['emp_is_verify'] == 't' ? view_verify() : '';?>
        </span>
    </span>
    <? 
    $user_banned = ($user->GetField($sbr->data['emp_id'], $ban_error, "is_banned", false) > 0) ? true : false;
    if ($user_banned) { ?>
        <span style="color:#000" ><b>Пользователь заблокирован.</b></span>
    <? } ?>
</div>
<?php } else {?>
<div class="b-username b-username_bold b-username_padbot_35">
    <span class="b-username__txt"><?= $sbr->isEmp() ? "Исполнитель" : "Заказчик"?></span>&#160;
    <a href="/users/<?= $sbr->data[$sbr->apfx . 'login'] ?>/" class="b-username__link <?= $sbr->isEmp() ? "b-username__link_color_f2922a" : "b-username__link_color_6db335"?>" target="_blank"><?= ($sbr->data[$sbr->apfx . 'uname'] . ' ' . $sbr->data[$sbr->apfx . 'usurname']); ?></a> 
    <span class="b-username__login <?= $sbr->isEmp() ? "b-username__login_color_f2922a" : "b-username__login_color_6db335"?>">
        [<a href="/users/<?= $sbr->data[$sbr->apfx . 'login'] ?>/" target="_blank" class="b-username__link"><?= $sbr->data[$sbr->apfx . 'login'] ?></a>] 
        <span class="b-username__marks"><? $apfx = $sbr->apfx; ?><?= view_mark_user_div($sbr->data[$apfx . "is_pro"] === "t", $apfx === "emp_", $sbr->data[$apfx . "is_team"] === 't', "") ?><?= $sbr->data[$sbr->apfx . 'is_verify'] == 't' ? view_verify() : '';?>
        </span>
    </span>
</div>
<?php }//?>

