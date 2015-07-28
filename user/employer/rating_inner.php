<?
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");

    $name = trim($_GET['user']);
    $user = new users();
    $user->GetUser($name);

    if(!$rating || !($rating instanceof rating) || $rating->data['user_id']!=$user->uid)
        $rating = new rating($user->uid, $user->is_pro, $user->is_verify, $user->is_profi, 1);

    $r_data = $rating->data;
    $r_data['o_oth_factor'] += $r_data['o_articles_factor'];
    $r_data['o_oth_factor'] += $r_data['o_contest_ban'];
    if(!$r_data['max']) {
        $r_data['max'] = $rating->get_max_of('total', true);
    }
    $is_owner = ($user->uid == $_SESSION['uid']);
?>
<div class="rating">
<div class="rate-page">
    
    <? if($user->uid==get_uid(false)) { ?>
    <script>
        window.addEvent('domready', function() {
            
            xajax_GetRating('month', '<?= $user->login ?>', <?= !is_pro() ? '600' : 'null' ?>);
            document.getElement('select[name=ratingmode]').addEvent('change', function() {
                xajax_GetRating(this.get('value'), '<?= $user->login ?>', <?= !is_pro() ? '600' : 'null' ?>);
            });
            
        });
    </script>
    <? } ?>
        
    <div class="month-rate-graph">
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_15 b-layout__table_margbot_40">
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__td_width_240 b-layout__one_padbot_20">
        			<p class="b-layout__txt_fontsize_20"><?= ( !$is_owner ? "Общий рейтинг" : "Ваш рейтинг")?></p></td>
        		<td colspan="2" class="b-layout__one_padbot_20">
        			<p class="b-layout__txt_float_right b-layout__mail-icon_top_4"><noindex><a rel="nofollow" href="https://feedback.fl.ru/topic/397655-opisanie-sistemyi-rejtinga-rabotodatel/" class="b-layout__link" target="_blank">Подробнее о рейтинге</a></noindex></p>
        			<p class="b-layout__txt_float_left b-layout__txt_fontsize_20 b-text__bold"><?= rating::round($r_data['total']) ?></p>
        			<p class="b-layout_clear_both b-layout__title_color_4e"><?= rating::round($r_data['max']) ?> максимальный</p>
        		</td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Заполненность профиля</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <? if(get_uid(false)!=$user->uid && !hasPermissions('users')) { ?>
                    закрытая информация
                    <? } else { $feature_inf_factor = 100 - rating::round($r_data['o_inf_factor']); ?>
                    <?= rating::round($r_data['o_inf_factor']) ?>
                    <? } ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    <noindex><a rel="nofollow" href="https://feedback.fl.ru/topic/397551-zakladka-informatsiya-opisanie-razdelov-instruktsiya-po-zapolneniyu-frilanser-i-rabotodatel/" class="b-layout__link">Подробнее о заполнении профиля</a></noindex>
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Общие опубликованные проекты</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= rating::round($r_data['o_prj_posted'])?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                   &nbsp;
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Опубликованные платные проекты</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?=rating::round($r_data['o_prj_payed'])?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    &nbsp;
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Опубликованные конкурсы</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?=rating::round($r_data['o_contest_posted'])?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    &nbsp;
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Успешно закрытые проекты</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?=rating::round($r_data['o_prj_closed'])?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    &nbsp;
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Безопасные сделки</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?=rating::round($r_data['o_sbr_factor'])?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle">
                </td>
        	</tr>
            <?php if($r_data['o_opi_factor'] > 0) { ?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Мнения пользователей</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= rating::round($r_data['o_opi_factor'])?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    &nbsp;
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
            <?php } ?>
            <?php if($r_data['o_manager_contacts'] > 0) { ?>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Услуга «Подбор фрилансеров»</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?=rating::round($r_data['o_manager_contacts'])?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    &nbsp;
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
            <?php } ?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Посещение сайта fl.ru</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <? if(get_uid(false)!=$user->uid && !hasPermissions('users')) { ?>
                    закрытая информация
                    <? } else { ?>
                    <?= rating::round($r_data['o_act_factor']) ?>
                    <? } ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) {?>
                    1 балл в день
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Деньги, потраченные на сервис</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <? if(get_uid(false)!=$user->uid && !hasPermissions('users')) { ?>
                    закрытая информация
                    <? } else { ?>
                    <?= rating::round($r_data['o_mny_factor']) ?>
                    <? } ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    &nbsp;
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Другие факторы </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= ( rating::round($r_data['o_oth_factor']) + rating::round($r_data['o_commune_entered']) );?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">&nbsp;</td>
        	</tr>
            <? if ($user->is_pro=='t' || $user->is_pro_test=='t') { ?>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">
                    Аккаунт &nbsp;<span class="b-icon b-icon__pro b-icon__pro_f b-icon_top_4" title="PRO"></span> x 1.2
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= abs(rating::round(abs(($r_data['f_total']*rating::PRO_FACTOR)) - abs($r_data['f_total']))) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">&nbsp;</td>
        	</tr>
            <? } ?>
            <? if ($user->is_verify=='t') { ?>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">
                    Верификация &nbsp;<span class="b-icon b-icon__ver b-icon_top_2"></span> x 1.2
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= abs(rating::round(abs(($r_data['f_total']*rating::VERIFY_FACTOR)) - abs($r_data['f_total']))) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">&nbsp;</td>
        	</tr>
            <? } ?>
        </table>
        
        <?php
        if($is_owner) {
            $feature_total = rating::round($r_data['total']);
            if($feature_inf_factor != 0) {
                $feature_total += $feature_inf_factor;
            }
            $feature_total_after_pro_verify = $feature_total;
            if ($user->is_pro != 't' && $user->is_pro_test != 't') {
                $feature_total += abs(rating::round(abs(($feature_total_after_pro_verify*rating::PRO_FACTOR)) - abs($feature_total_after_pro_verify)));
            }
            if($user->is_verify != 't') {
                $feature_total += abs(rating::round(abs(($feature_total_after_pro_verify*rating::VERIFY_FACTOR)) - abs($feature_total_after_pro_verify)));
            }
        ?>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_15 b-layout__table_margbot_40">
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__td_width_240 b-layout__one_padbot_20">
        			<p class="b-layout__txt_fontsize_20">А хотите &rarr;</p></td>
        		<td colspan="2" class="b-layout__one_padbot_20">
        			<p class="b-layout__txt_float_left b-layout__txt_fontsize_20 b-text__bold b-layout__txt_color_6db335"><?= $feature_total;?></p>
        			<p class="b-layout_clear_both b-layout__title_color_4e">такой рейтинг как минимум</p>
        		</td>
        	</tr>
            <? if ($user->is_pro != 't' && $user->is_pro_test != 't') { ?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Аккаунт &nbsp;<span class="b-icon b-icon__pro b-icon__pro_f b-icon_top_4" title="PRO"></span> x 1.2</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold b-layout__txt_color_6db335">
                    <?= abs(rating::round(abs(($feature_total_after_pro_verify*rating::PRO_FACTOR)) - abs($feature_total_after_pro_verify))) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/payed/" target="_blank" class="b-layout__link">Купить</a> &nbsp;<?= view_pro(); ?></td>
        	</tr>
            <? }//if?>
            <? if($user->is_verify != 't') { ?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Верификация &nbsp;<span class="b-icon b-icon__ver b-icon_top_2"></span> x 1.2</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold b-layout__txt_color_6db335">
                    <?= abs(rating::round(abs(($feature_total_after_pro_verify*rating::VERIFY_FACTOR)) - abs($feature_total_after_pro_verify))) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/promo/verification/" target="_blank" class="b-layout__link">Пройти верификацию</a></td>
        	</tr>
            <? }//if?>
            <? if($feature_inf_factor != 0) {?>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Заполненность профиля</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold b-layout__txt_color_6db335">
                    <?= $feature_inf_factor;?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/users/<?=$user->login; ?>/setup/info/" target="_blank" class="b-layout__link">Заполнить профиль</a></td>
        	</tr>
            <? }//if?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Безопасные Сделки</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"></td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Общие опубликованные проекты</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/public/?step=1&kind=1&red=" target="_blank" class="b-layout__link">Опубликовать проект</a></td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Опубликованные платные проекты</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/public/?step=1&kind=4&red=" target="_blank" class="b-layout__link">Опубликовать платный проект</a></td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Опубликованные конкурсы</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/public/?step=1&kind=7" target="_blank" class="b-layout__link">Опубликовать конкурс</a></td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Успешно закрытые проекты</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/user/<?= $user->login; ?>/setup/projects/" target="_blank" class="b-layout__link">Перейти в раздел Мои проекты</a></td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Деньги, потраченные на сервис</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"></td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Посещение сайта fl.ru</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">1 балл в день</td>
        	</tr>
        </table>
<!-- // таблица с рейтингами -->
        <? }//if?>
         
        
        <? if($user->uid==get_uid(false)) { ?>
        <select name="ratingmode">
            <option value="month">в этом месяце</option>
            <option value="prev">в прошлом месяце</option>
            <option value="year">за год</option>
        </select>
        <h3>График изменений рейтинга</h3>
            
        <div id="raph"></div>
        <? } ?>
    </div>
        
        
    <div>
        <div class="page-rate-info">
            <p>В разделе помощи подробно описано, <noindex><a rel="nofollow" href="https://feedback.fl.ru/topic/397655-opisanie-sistemyi-rejtinga-rabotodatel/" target="_blank">как считается рейтинг</a></noindex>.</p>
            <p>Если у вас возникли вопросы – обратитесь в <noindex><a rel="nofollow" href="https://feedback.fl.ru/" target="_blank">Службу поддержки</a></noindex>. С удовольствием ответим.</p>
        </div>
    </div>
        
</div>
</div>
