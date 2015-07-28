<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
 require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/rating.common.php");
$xajax->printJavascript('/xajax/');

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");

if (!$rating || !($rating instanceof rating) || $rating->data['user_id'] != $user->uid)
    $rating = new rating($user->uid, $user->is_pro, $user->is_verify, $user->is_profi);

$r_data = $rating->data;
$r_data['kis'] = projects_offers::GetFrlOffersSummary($r_data['user_id']);
$r_data['kis']['refused_3'] = (int) $r_data['kis']['refused'] - (int) $r_data['kis']['refused_1'] - (int) $r_data['kis']['refused_0'] - (int) $r_data['kis']['refused_2'] - (int) $r_data['kis']['refused_4'];
if(!$r_data['max']) {
    $r_data['max'] = $rating->get_max_of('total', false);
}

$sbr_ratings = sbr_meta::getUserRatings($user->uid, is_emp($user->role), 5, 0, $sbr_info['success_cnt']);
//$sbr_info['success_cnt'] = sbr_meta::getCountSuccessRatingSbr($user->uid, is_emp($user->role));
if (!($prjs = projects_offers::GetFrlOffers($r_data['user_id'], 'marked', NULL)))
    $prjs = array();

$kis_per_refused = round($r_data['kis']['total'] ? 100 * $r_data['kis']['refused'] / $r_data['kis']['total'] : 0, 2);
$kis_per_frl_refused = round($r_data['kis']['total'] ? 100 * $r_data['kis']['frl_refused'] / $r_data['kis']['total'] : 0, 2);
$kis_per_selected = round($r_data['kis']['total'] ? 100 * $r_data['kis']['selected'] / $r_data['kis']['total'] : 0, 2);
$kis_per_executor = round($r_data['kis']['total'] ? 100 * $r_data['kis']['executor'] / $r_data['kis']['total'] : 0, 2);
$kis_unknown = (int) $r_data['kis']['total'] - ((int) $r_data['kis']['refused'] + (int) $r_data['kis']['selected'] + (int) $r_data['kis']['executor']) - (int) $r_data['kis']['frl_refused'];
$kis_per_unknown = 100 - ($kis_per_refused + $kis_per_selected + $kis_per_executor + $kis_per_frl_refused);

$o_contest_rating = round($r_data['o_contest_1'] + $r_data['o_contest_2'] + $r_data['o_contest_3']);
$o_contest_ban_rating = round($r_data['o_contest_ban']);
$is_owner = ($user->uid == $_SESSION['uid']);
?>

<style type="text/css">
  .rating .big-s {font-size:17px}
  .rating .lgray-c {color:#b2b2b2}
  .rating .table td {padding:15px 0 15px 0}
  .ac, tr.ac td   {text-align:center}
  .bt, tr.bt td   {border-top:1px solid}
  .br, tr.br td   {border-right:1px solid}
  .bb, tr.bb td   {border-bottom:1px solid}
  .bl, tr.bl td   {border-left:1px solid}
  .ba, tr.ba td   {border:1px solid}
  .gray-bc,  tr.gray-bc  td {border-color: #c6c6c6}
</style>
<div class="rating">
  <table width="100%" cellspacing="0" cellpadding="0">
    <tr>
      <td>
          
<? if ($user->uid == get_uid(false)) { ?>
    <script>
        window.addEvent('domready', function() {
            
            xajax_GetRating('month', '<?= $user->login ?>', <?= !is_pro() ? '600' : 'null' ?>);
            document.getElement('select[name=ratingmode]').addEvent('change', function() {
                xajax_GetRating(this.get('value'), '<?= $user->login ?>', <?= !is_pro() ? '600' : 'null' ?>);
            });
            
        });
    </script>
<? } ?>
            
<div class="rate-page">
    
    <div class="month-rate-graph">
        
        
<!-- таблица с рейтингами -->
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_15 b-layout__table_margbot_40">
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__td_width_240 b-layout__one_padbot_20">
        			<p class="b-layout__txt_fontsize_20"><?= ( !$is_owner ? "Общий рейтинг" : "Ваш рейтинг")?></p></td>
        		<td colspan="2" class="b-layout__one_padbot_20">
        			<p class="b-layout__txt_float_right b-layout__mail-icon_top_4"><noindex><a rel="nofollow" href="https://feedback.fl.ru/topic/397654-opisanie-sistemyi-rejtinga-frilanser/" class="b-layout__link" target="_blank">Подробнее о рейтинге</a></noindex></p>
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
                    <a href="http://feedback.fl.ru/topic/397551-zakladka-informatsiya-opisanie-razdelov-instruktsiya-po-zapolneniyu-frilanser-i-rabotodatel/" class="b-layout__link">Подробнее о заполнении профиля</a>
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Ответы на проекты</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= rating::round($r_data['o_kis_factor']) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    1 балл за добавление в кандидаты или исполнители
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Безопасные сделки</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= rating::round($r_data['o_sbr_factor']) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle">
                </td>
        	</tr>
            <?php if($r_data['o_opi_factor'] > 0) { ?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Мнения пользователей</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= rating::round($r_data['o_opi_factor']) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    +1/-1 балл за положительное/отрицательное мнение
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
            <?php } ?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Конкурсы</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= $o_contest_rating ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    30 баллов за &Iota; место, 20 баллов за &Iota;&Iota; место, 10 баллов за &Iota;&Iota;&Iota; 3 место
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
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
                    1 балл за 30 рублей
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
            <?php if($r_data['o_articles_factor'] > 0) { ?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Статьи</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= rating::round($r_data['o_articles_factor'])?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    30 баллов за публикацию в разделе Статьи
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
            <?php } ?>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Сообщества</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= rating::round($r_data['o_commune_entered'])?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">
                    <? if($is_owner) { ?>
                    50 баллов за добавление &gt; 500 участников в ваше сообщество
                    <? } else {?>
                    &nbsp;
                    <? } ?>
                </td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Работы в портфолио</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <? if(get_uid(false)!=$user->uid && !hasPermissions('users')) { ?>
                    закрытая информация
                    <? } else { $feature_portf = 500 - rating::round($r_data['o_wrk_factor']); ?>
                    <?= rating::round($r_data['o_wrk_factor']) ?>
                    <? } ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">&nbsp;</td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Другие факторы</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= rating::round($r_data['o_oth_factor']) + $o_contest_ban_rating; ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">&nbsp;</td>
        	</tr>
            <?php if($user->isProfi()){ ?>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">
                    Аккаунт &nbsp;<span class="b-icon b-icon__lprofi b-icon_top_2" title="PROFI"></span> x 1.4
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold">
                    <?= abs(rating::round(abs(($r_data['f_total']*rating::PROFI_FACTOR)) - abs($r_data['f_total']))) ?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">&nbsp;</td>
        	</tr>            
            <?php } elseif ($user->is_pro=='t' || $user->is_pro_test=='t') { ?>
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
            if($feature_portf != 0) {
                $feature_total += $feature_portf;
            }
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
            <? if($feature_portf != 0) {?> 
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Работы в портфолио</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt b-text__bold b-layout__txt_color_6db335">
                    <?= $feature_portf;?>
                </td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/users/<?= $user->login; ?>/portfolio" target="_blank" class="b-layout__link">Добавить работы</a></td>
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
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Конкурсы</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/konkurs/" target="_blank" class="b-layout__link">Перейти в раздел Конкурсы</a></td>
        	</tr>
        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Посещение сайта fl.ru</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__txt_color_a7a7a6 b-layout__one_valign_middle">1 балл в день</td>
        	</tr>

        	<tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Сообщества</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/commune/" target="_blank" class="b-layout__link">Перейти в раздел Сообщества</a></td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Деньги, потраченные на сервис</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"></td>
        	</tr>
            <tr class="b-layout__one_bordbot_cec">
        		<td class="b-layout__one_padtb_6 b-layout__txt b-layout__one_valign_middle">Ответы на проекты</td>
        		<td class="b-layout__one_padtb_6 b-layout__one_valign_middle b-layout__txt"><span class="b-layout__infin">&infin;</span></td>
        		<td class="b-layout__one_padtb_6 b-layout__one_right b-layout__one_valign_middle"><a href="/" target="_blank" class="b-layout__link">Перейти в раздел Работа</a></td>
        	</tr>
        </table>
<!-- // таблица с рейтингами -->
        <? }//if?>
                 
            
        <? if ($user->uid == get_uid(false)) { ?>
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
            <p>В разделе помощи подробно описано, <noindex><a rel="nofollow" href="https://feedback.fl.ru/topic/397654-opisanie-sistemyi-rejtinga-frilanser/" target="_blank">как считается рейтинг</a></noindex>.</p>
            <p>Если у вас возникли вопросы – обратитесь в <noindex><a rel="nofollow" href="https://feedback.fl.ru/" target="_blank">Службу поддержки</a></noindex>. С удовольствием ответим.</p>
        </div>
    </div>
        
</div>
          
          
      </td>
    </tr>
    <? if($sbr_info['success_cnt'] && $sbr_ratings) { ?>
      <tr>
        <td class="brdtop" style="padding:0px 20px 0px 20px;height:20px">
          <b>Подробнее по «Безопасным Сделкам»</b> (<?=(int)$sbr_info['success_cnt']?>)
        </td>
      </tr>
      <tr>
        <td style="padding:10px 0 0 0" id="sbr_list">
            <? $i = 0; include ($_SERVER['DOCUMENT_ROOT'] ."/user/tpl.rating-sbr.php"); ?>
            <span id="more_sbr_content"></span>
            <? if((int)$sbr_info['success_cnt']>5) { ?>
                    <p class="last-sbr"><a href="" onClick="xajax_GetMoreSBR(<?=$user->uid?>, <?=$i?>); $(this).hide(); return false;" class="lnk-dot-666">Показать оставшиеся <?=((int)$sbr_info['success_cnt']-$i)?> «Безопасные Сделки»</a></p>
            <? } ?>
        </td>
      </tr>
    <? } ?>
    <? if($r_data['kis']['total']) { ?>
      <tr>
        <td class="brdtop" style="padding:0px 20px 0px 20px;height:20px">
          <b>Подробнее по ответам на проекты</b> (<?=$r_data['kis']['total']?>)
        </td>
      </tr>
      <tr>
        <td style="padding:10px 20px 15px">
			<div class="tbl-ratinginfo">
				<table>
					<colgroup>
						<col width="205" />
						<col />
					</colgroup>
					<tbody><tr>
						<th>Не определился (<?=$kis_unknown?$kis_unknown:'нет'?>)</th>
						<td><?=$kis_unknown?"({$kis_per_unknown}%)":'&nbsp;'?></td>
					</tr>
					<tr>
						<th>Отказался (<?= (int)$r_data['kis']['frl_refused']?(int)$r_data['kis']['frl_refused']:'нет'?>)</th>
						<td><?=(int)$r_data['kis']['frl_refused']?"({$kis_per_frl_refused}%)":'&nbsp;'?></td>
					</tr>
					<tr class="line">
						<th>Отказов (<?=(int)$r_data['kis']['refused']?(int)$r_data['kis']['refused']:'нет'?>)</th>
						<td><?=(int)$r_data['kis']['refused']?"({$kis_per_refused}%)":'&nbsp;'?></td>
					</tr>
					<?/*<tr>
						<td colspan="2">
							<table class="tbl-in">
								<tbody><tr>
									<td>- Не подходят работы: (<?=(int)$r_data['kis']['refused_1']?(int)$r_data['kis']['refused_1']:'нет'?>) <? if((int)$r_data['kis']['refused_1']) {?><span><?=round($r_data['kis']['refused'] ? 100 * $r_data['kis']['refused_1'] / $r_data['kis']['refused'] : 0, 2)?>%</span><? } ?></td>
								</tr>
								<tr>
									<td>- Некорректен: (<?=(int)$r_data['kis']['refused_0']?(int)$r_data['kis']['refused_0']:'нет'?>) <? if((int)$r_data['kis']['refused_0']) { ?><span><?=round($r_data['kis']['refused'] ? 100 * $r_data['kis']['refused_0'] / $r_data['kis']['refused'] : 0, 2)?>%</span><? } ?></td>
								</tr>
								<tr>
									<td>- Не подходит цена: (<?=(int)$r_data['kis']['refused_2']?(int)$r_data['kis']['refused_2']:'нет'?>) <? if((int)$r_data['kis']['refused_2']) { ?><span><?=round($r_data['kis']['refused'] ? 100 * $r_data['kis']['refused_2'] / $r_data['kis']['refused'] : 0, 2)?>%</span><? } ?></td>
								</tr>
								<tr>
									<td>- Выбран другой исполнитель: (<?=(int)$r_data['kis']['refused_4']?(int)$r_data['kis']['refused_4']:'нет'?>) <? if((int)$r_data['kis']['refused_4']) { ?><span><?=round($r_data['kis']['refused'] ? 100 * $r_data['kis']['refused_4'] / $r_data['kis']['refused'] : 0, 2)?>%</span><? } ?></td>
								</tr>
								<tr>
									<td>- Другая причина: (<?=$r_data['kis']['refused_3']?$r_data['kis']['refused_3']:'нет'?>) <? if($r_data['kis']['refused_3']) { ?><span><?=round($r_data['kis']['refused'] ? 100 * $r_data['kis']['refused_3'] / $r_data['kis']['refused'] : 0, 2)?>%</span><? } ?></td>
								</tr>
							</tbody></table>
						</td>
					</tr>*/?>
					<tr class="line">
						<th>Кандидат (<?=(int)$r_data['kis']['selected']?(int)$r_data['kis']['selected']:'нет'?>)</th>
						<td><?=(int)$r_data['kis']['selected']?"({$kis_per_selected}%)":'&nbsp;'?></td>
					</tr>
					<tr class="line">
						<th>Исполнитель (<?=(int)$r_data['kis']['executor']?(int)$r_data['kis']['executor']:'нет'?>)</th>
						<td><?=(int)$r_data['kis']['executor']?"({$kis_per_executor}%)":'&nbsp;'?></td>
					</tr>
				</tbody></table>
			</div>
        </td>
      </tr>
      <? if($prjs) { /*$prjs = projects_offers::GetFrlOffers($r_data['user_id'], 'marked', NULL); // второй раз вызывается функция*/ ?>
      <tr>
        <td class="brdtop" style="padding:0px 20px 0px 20px;height:20px">
          <b>Список проектов, повлиявших на рейтинг</b> (<?=count($prjs)?>)
        </td>
      </tr>
      <tr>
        <td style="padding:10px 20px 15px">
			<div class="list-ratinginfo">
                <?
                $i=0;
                $prj_sum_rating = 0;
                foreach($prjs as $p) {
                    $prj_sum_rating += $p['rating'];
                }
                ?>
				<div>Общее влияние рейтинга: <span class="ops-<?=$prj_sum_rating<0?'minus':'plus'?>"><?=$prj_sum_rating<0?'':'+'?><?=$prj_sum_rating?></span></div>
				<ol id="prj_list">
                    <?php 
                        $uid = get_uid(FALSE);
                        $is_adm = hasPermissions('users');
                        
                        foreach($prjs as $p) 
                        { 
                            $i++;
                            $is_link = (($uid > 0) && (in_array($uid, array($p['exec_id'],$p['project_user_id'],$p['offer_user_id'])) || $is_adm));
                    ?>
					<li>
					   <span class="prj_list_number"><?=$i?>.</span> 
                                           <?php if($p['kind'] == 9): ?>
                                                <?php if($is_link): ?>
                                                <a href="<?=getFriendlyURL("project", $p['project_id'])?>"><?=$p['project_name']?></a>
                                                <?php else: ?>
                                                <?=$p['project_name']?>
                                                <?php endif; ?>
                                           <?php else: ?>
                                                <a href="<?=getFriendlyURL("project", $p['project_id'])?>"><?=$p['project_name']?></a> 
                                           <?/*if($p['position']>0 && $p['is_executor']=='t'){?>(<?=$p['position']?>-е место)<?}*/?>
                                           <?php endif; ?>
                          <? if($p['refused']=='t') { ?>
                            <p>Отказ: <span class="ops-minus"><?=$p['rating']?></span></p>
                          <? } if($p['selected']=='t') { ?>
                            <p>Кандидат: <span class="ops-plus">+<?=$p['rating']?></span></p>
                          <? } if($p['is_executor']=='t' && $p['position'] <= 0) { ?>
                            <p>Исполнитель: <span class="ops-plus">+<?=$p['rating']?></span></p>
                         <? } if($p['position'] > 0) { ?>
                            <p><?=$p['position']?>-е место: <span class="ops-plus">+<?=$p['rating']?></span></p>
                          <? } if($p['blocked'] > 0) { ?>
                            <p>Забанен работодателем: <span class="ops-plus"><?=rating::A_KIS_R_BONUS * 10?></span></p>
                          <? } ?>
					</li>
                    <?
                    if($i>4) break;
                    }
                    ?>
				</ol> 
                <? if(count($prjs)>5) { ?>
				<p id="lnk_more_prj"><a href="" class="lnk-dot-666" onClick="$('lnk_more_prj').setStyle('display', 'none'); xajax_GetMorePrj(<?=$r_data['user_id']?>); return false;">Показать оставшиеся <?=(count($prjs)-5)?> проектов</a></p>
                <? } ?>
			</div>
        </td>
      </tr>
      <? } ?>





    <? } ?>
  </table>
</div>
