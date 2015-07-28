<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/teams.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opinions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");

global $session;


$sbr_info = sbr_meta::getUserInfo($user->uid);



//@todo: упростил но это вообще здесь не нужно впринципе см getHeaderData
$from = is_emp($user->role)?'emp':'frl';
$op_data = opinions::getHeaderData($from, $user, $user->uid);


if (!$rating || !($rating instanceof rating) || $rating->data['user_id'] != $user->uid)
    $rating = new rating($user->uid, $user->is_pro, $user->is_verify, $user->is_profi, 1);

$r_data = $rating->data;

//$samerank = rating::CountByRank($r_data['rank']);
$banblog = $user->GetBan($user->uid, 1);

if ($user->birthday && $user->birthday > "1910-01-01")
    $user_ago = ElapsedYears(strtotime($user->birthday));
$info_for_reg = @unserialize($user->info_for_reg);

$team = new teams();

// срок окончания ПРО - только для админов
if (hasPermissions('users') && $user->is_pro === 't') {
    $proLast = payed::ProLast($user->login);
    $proDate = date('d-m-Y в h:i', strtotime($proLast['cnt']));
}

$access_favorite = $_SESSION['login'] && $user->login != $_SESSION['login'];
$access_contacts = ($user->isCurrent() || is_view_contacts($user->uid) || hasPermissions('users')) 
        && is_contacts_not_empty($user);
$show_contacts_col = $access_favorite || $access_contacts;


$is_allow_messages = true;
if ($uid > 0 && !is_emp() && is_emp($user->role)) {
    require_once(ABS_PATH . "/classes/messages.php");
    $is_allow_messages = messages::isAllowed($user->uid, $uid);
}


?>





<div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_padbot_10 b-layout__txt_lineheight_1 b-layout__txt_color_6db335 b-page__ipad b-page__iphone"><? if($session->view_online_status($user->login)) {?><span class="b-icon b-icon__lamp"></span> <?php } ?><?= (htmlspecialchars($user->uname)." ".htmlspecialchars($user->usurname)." [".htmlspecialchars($user->login)."]" );?>
<?php $sbr = false; 
if ( $sbr_info["completed_cnt"] > 0) {
    $sbr = true;
}?>
                        <?= view_mark_user(array("login"      => $user->login, "is_pro"      => $user->is_pro,
									"is_pro_test" => $user->is_pro_test,
									"is_team"     => $user->is_team,
									"role"        => $user->role), '', true, "");?>
              <?php $sbr = false; 
              if ( $sbr_info["completed_cnt"] > 0) {
                  $sbr = true; ?>
                  <a class="b-layout__link b-layout__link_inline-block" href="/promo/bezopasnaya-sdelka/"  title="Пользователь работал через Безопасную сделку" target="_blank"><span class="b-icon b-icon__shield"></span></a>
              <?php }?>
</div>
<table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_30">
   <tr class="b-layout__tr">
      <td class="b-layout__td b-layout__td_width_100 b-layout__td_padright_20 b-layout__td_ipad">

			  <?php if ($user->login == $_SESSION['login']) { ?>
                 <div class="b-layout b-layout_relative b-layout_float_left b-layout_bord_e6_hover b-layout__hover_b-fon_show">
                      <a class="b-layout__link" href="/users/<?= htmlspecialchars($user->login) ?>/<?= $user->uid == get_uid(0) ? 'setup/foto/' : '' ?>"><?= view_avatar($user->login, $user->photo, 0, 1,'b-layout__pic b-layout__pic_float_left ') ?></a>
                          <div class="b-page__desktop"><div class="b-fon b-fon_bg_64 b-fon_padtb_3 b-fon_center b-layout_bottom_null b-layout_absolute b-layuot_width_full"><a class="b-icon b-icon__edit" href="/users/<?=$user->login?>/setup/foto/"></a> <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_fff b-layout__link_no-decorat b-layout__link_color_fff_hover" href="/users/<?=$user->login?>/setup/foto/">Изменить фото</a></div></div>
                          <div class="b-page__ipad b-page__iphone"><a class="b-icon b-icon__edit b-icon_bot_4 b-icon_margleft_3 b-layout_absolute" href="/users/<?=$user->login?>/setup/foto/"></a></div>
                  </div>
              <?php } else { ?>
                  <div class="b-layout b-layout_relative">
                      <a class="b-layout__link" href="/users/<?= htmlspecialchars($user->login) ?>/<?= $user->uid == get_uid(0) ? 'setup/foto/' : '' ?>"><?= view_avatar($user->login, $user->photo, 0, 1,'b-layout__pic') ?></a>
                  </div>
              <?php } ?>
      </td>
      <td class="b-layout__td b-layout__td_padright_20 b-layout__td_ipad b-layout__td_padbot_10_iphone">
              <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_lineheight_1 b-layout__txt_color_6db335 b-page__desktop"><? if($session->view_online_status($user->login)) {?><span class="b-icon b-icon__lamp"></span> <?php } ?><?= (htmlspecialchars($user->uname)." ".htmlspecialchars($user->usurname)." [".htmlspecialchars($user->login)."]" );?>
                        <?= view_mark_user(array("login"      => $user->login, "is_pro"      => $user->is_pro,
									"is_pro_test" => $user->is_pro_test,
									"is_team"     => $user->is_team,
									"role"        => $user->role), '', true, "");?>
              <?php $sbr = false; 
              if ( $sbr_info["completed_cnt"] > 0) {
                  $sbr = true; ?>
                  <a class="b-layout__link b-layout__link_inline-block" href="/promo/bezopasnaya-sdelka/"  title="Пользователь работал через Безопасную сделку" target="_blank"><span class="b-icon b-icon__shield"></span></a>
              <?php }?>
              </div>
              <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_10">На сайте <?= ElapsedMnths(strtotime($user->reg_date)) ?>
				  <? if ($user->uid != get_uid(0)) { ?>
                     <? if ($session->view_online_status($user->login)=='') { ?> (<?=$online_status?>)<? } ?>
                  <? } ?>
              </div>
              
              
            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10 b-page__iphone"><span class="b-icon b-icon__cont b-icon__cont_op b-icon_top_2"></span> <?= $op_data['total_no_author'];?></div>
            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10 b-page__iphone"><span class="b-icon b-icon__cont b-icon__bs_small b-icon_top_-2"></span><?= (int) $sbr_info['completed_cnt'] ?></div>
            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10 b-page__iphone"><span class="b-icon b-icon__cont b-icon__cont_rate b-icon_top_-2"></span> <?= rating::round($r_data['total']) ?></div>
              
			<?php  if (($_SESSION['login'] && $user->login != $_SESSION['login'])) { ?>
              <div class="b-layout b-layout_padbot_10">
                 <?php include_once($_SERVER['DOCUMENT_ROOT'] . '/user/header_note.php') ?>
              </div>
            <?php }  ?>
			<?php  if ($access_favorite) { ?>
                  <div class="b-layout__txt b-layout__txt_lineheight_1">
                        <span class="b-icon b-icon__cont b-icon_top_-1 b-icon__cont_fav"></span><div
                         class="b-layout__txt b-layout__txt_inline-block b-layout__txt_lineheight_1">
                            <? if (!empty($_SESSION['uid']) && $team->teamsIsInFavorites($_SESSION['uid'], $user->uid)) { ?>
                                  <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__txt_top_1" href="<? if ($_SESSION["uid"]) { ?>/users/<?= htmlspecialchars($_SESSION['login']) ?>/setup/deluser/<?= htmlspecialchars($user->login) ?>" onclick="del('<?= htmlspecialchars($_SESSION['login']) ?>','<?= htmlspecialchars($user->login) ?>'); return false;<? } else { ?>/fbd.php<? } ?>">
                                              <span id="fav_title">У вас в избранных</span>
                                  </a>
                            <? } else { ?>
                                  <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__txt_top_1" href="<? if ($_SESSION["uid"]) { ?>/users/<?= htmlspecialchars($_SESSION['login']) ?>/setup/adduser/<?= htmlspecialchars($user->login) ?>" onclick="add('<?= htmlspecialchars($_SESSION['login']) ?>','<?= htmlspecialchars($user->login) ?>'); return false;<? } else { ?>/fbd.php<? } ?>">
                                              <span id="fav_title">Добавить в избранные</span>
                                  </a>
                            <? } ?>
                        </div>
                  </div>
            <? } ?>
              
              
					<?php if((is_pro(true, $user->uid)||is_pro())&&($_SESSION['login'] && $user->login != $_SESSION['login'])){ ?>
                        <?php if($is_allow_messages): ?>
                       <div class="b-buttons b-buttons_padtop_15">
                            <a href="/contacts/?from=<?= htmlspecialchars($user->login) ?>" class="b-button b-button_flat b-button_flat_green">Оставить сообщение</a> &#160;
                       </div>
                       <?php else: ?>
                       <div class="b-layout__txt b-layout__txt_fontsize_9 b-layout__txt_padtop_10">
                           <?= messages::MESSAGES_NOT_ALLOWED ?>
                       </div>
                       <?php endif; ?>
                    <?php } ?>
                    
					<? if ((!get_uid())&&(!is_pro(true, $user->uid))) {  ?>
                    <div class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padtb_10"><span class="b-icon b-icon_sbr_forb"></span><a class="b-layout__link" href="/registration/">Авторизуйтесь</a> и <a class="b-layout__link" href="<?php if(is_emp()){ ?>/payed-emp/<?php } else {?>/payed/<?php } ?>">купите аккаунт PRO</a>, чтобы увидеть контактные данные пользователя.</div>
                    <?php  } ?>
                    <? if ((get_uid())&&(!is_pro(true, $user->uid))&&!is_pro()) {  ?>
                    <div class="b-layout__txt b-layout__txt_padtb_10 b-layout__txt_color_c10600"><span class="b-icon b-icon_sbr_forb"></span><a class="b-layout__link" href="<?php if(is_emp()){ ?>/payed-emp/<?php } else {?>/payed/<?php } ?>">Купите аккаунт PRO</a>, чтобы видеть контактные данные у всех и открыть свои контакты всем пользователям.</div>
                    <?php  } ?>
              
      </td>
      <?php if($show_contacts_col) { ?>
      <td class="b-layout__td b-layout__td_padleft_10 b-layout__td_bordleft_e6 b-layout__td_padright_10 b-layout__td_width_240 b-layout__td_ipad b-layout__td_block_iphone b-layout__td_width_full_iphone b-layout__td_bord_null_iphone b-layout__td_pad_null_iphone b-layout__td_bordtop_e6_iphone b-layout__td_padbot_10_iphone b-layout__td_padtop_10_iphone">
        <?php if($access_contacts) include $_SERVER['DOCUMENT_ROOT'] . '/user/contacts_info.php'; ?>
      </td>
      <?php } ?>
      <td class="b-layout__td b-layout__td_padleft_10 b-layout__td_bordleft_e6 b-layout__td_padright_20 b-layout__td_width_270 b-layout__td_width_150_ipad b-layout__td_ipad b-layout__td_block_iphone b-layout__td_width_full_iphone b-layout__td_pad_null_iphone b-layout__td_bord_null_iphone">
            <div class="b-layout b-layout__hover_bg_f0ffdf b-layout_pad_3 b-layout_padleft_10 b-layout_margbot_3 b-page__desktop b-page__ipad">
               <div class="b-layout__txt b-layout__txt_padleft_25 b-page__desktop b-layout__txt_lineheight_1 b-layout__txt_bold b-layout__txt_padtop_1"><span class="b-icon b-icon__cont b-icon__cont_rate b-icon_top_-2 b-icon_margleft_-25"></span> Рейтинг<span class="b-layout__txt b-layout__txt_float_right b-layout__txt_float_none_ipad b-layout__txt_bold b-layout__txt_lineheight_1"><?= rating::round($r_data['total']) ?></span></div>
               <div class="b-layout__txt b-page__ipad b-layout__txt_nowrap b-layout__txt_bold b-layout__txt_lineheight_1"><span class="b-icon b-icon__cont b-icon__cont_rate b-icon_top_-2 b-icon_margleft_-15"></span> <?= rating::round($r_data['total']) ?></div>
            </div>
            <div class="b-layout b-layout__hover_bg_f0ffdf b-layout_pad_3 b-layout_padleft_10 b-page__desktop b-page__ipad">
               <div class="b-layout__txt b-layout__txt_padleft_25 b-page__desktop b-layout__txt_lineheight_1 b-layout__txt_bold b-layout__txt_padtop_1"><span class="b-icon b-icon__bs_small b-icon_top_-2 b-icon_margleft_-25"></span><span class="b-layout__txt b-layout__txt_float_right b-layout__txt_float_none_ipad b-layout__txt_bold b-layout__txt_lineheight_1"><?= (int) $sbr_info['completed_cnt'] ?></span><a href="/promo/<?=sbr::NEW_TEMPLATE_SBR?>/" class="b-layout__link b-layout__link_color_000 b-layout__link_no-decorat b-layout__link_bold" target="_blank">Безопасные сделки</a> </div>
               <div class="b-layout__txt b-page__ipad b-layout__txt_nowrap b-layout__txt_bold"><span class="b-icon b-icon__bs_small b-icon_margleft_-15 b-layout__txt_bold"></span> <?= (int) $sbr_info['completed_cnt'] ?></div>
            </div>
            <div class="b-layout b-layout__hover_bg_f0ffdf b-layout_pad_3 b-layout_padleft_10 b-layout_margbot_4 b-page__desktop b-page__ipad">
               <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_bold b-layout__txt_lineheight_1 b-page__desktop"><span class="b-icon b-icon__cont b-icon__cont_op b-icon_margleft_-25"></span> Отзывы<span class="b-layout__txt b-layout__txt_bold b-layout__txt_float_right b-layout__txt_float_none_ipad b-layout__txt_lineheight_1"><?= $op_data['total_no_author'];?></span></div>
               <div class="b-layout__txt b-page__ipad b-layout__txt_nowrap b-layout__txt_bold b-layout__txt_lineheight_1"><span class="b-icon b-icon__cont b-icon__cont_op b-icon_margleft_-15"></span>  <?= $op_data['total_no_author'];?></div>
            </div>
            
            
            
            
            
            
      </td>
   </tr>
</table>






                <? if ($_SESSION["uid"] && hasPermissions('users') && ($_SESSION['uid'] != $user->uid)) { ?>
                    <? if (!(hasGroupPermissions('administrator', $user->uid) || hasGroupPermissions('moderator', $user->uid))) { ?>

                        <script type="text/javascript">
                        banned.addContext( '<?=$user->uid?>', 1, '<?=$GLOBALS['host']?>/users/<?= htmlspecialchars($user->login) ?>', '<?=( htmlspecialchars($user->uname). ' '. htmlspecialchars($user->usurname). ' [' . htmlspecialchars($user->login) . ']' )?>' );
                        </script>
                        
                        <div class="admin-block">
                            <h4>Предупреждений: <span id="warncount-<?= $user->uid ?>"><?= ($user->warn ? $user->warn : 0) ?></span> <?= $user->is_banned?"<em>(Забанен)</em>":""?></h4>

                            <?php
                            if($user->is_banned || $user->ban_where) {
                                $ban = $user->getBan($user->uid, $user->ban_where);
                                $admin_user = new users();
                                $admin_user_info = $admin_user->GetName($ban['admin'], $ee);
                            }
                            ?>
                            <div id="banreasonblock-<?=$user->uid?>" style="display: <?=($user->is_banned || $user->ban_where ? 'block' : 'none')?>;">
                                <table><tbody>
                                    <tr>
                                        <th colspan="3"><strong id="banreasonblock-text-<?=$user->uid?>">Блокировка <?=($ban['where']) ? 'в блогах' : 'везде'?> <?=($ban['to'] ? 'до '.dateFormat("j",$ban['to']).' '.monthtostr(dateFormat("n",$ban['to']), true).' '.dateFormat("Y",$ban['to']) : 'навсегда')?></strong></th>
                                    </tr>
                                    <tr>
                                        <td>
                                            [<a onclick="banned.userBan(<?=$user->uid?>, '<?=$user->uid?>',0); return false;" href="javascript: void(0);" style="font-weight: bold">X</a>]&nbsp;
                                        </td>
                                        <td><span class="admn-line" id="banreasonblock-comment-<?=$user->uid?>"><?=$ban['comment']?></span></td><td> <? 
                                        if($admin_user_info['login']) {
                                        ?> <span style="font-style: italic">(выдан: <a href="/users/<?=$admin_user_info['login']?>" id="banreasonblock-admin-<?=$user->uid?>"><?=$admin_user_info['login']?></a>, </span><span style="font-style: italic" id="banreasonblock-date-<?=$user->uid?>"><?=dateFormat("d.m.Y H:i", $ban['from'])?>)</span><?php 
                                        }?></td>
                                    </tr>
                                </tbody></table>
                            </div>
                            <p> </p>



                            <p>
                                <a class="warnbutton-<?= $user->uid ?>" href="javascript: void(0);" onclick="banned.warnUser(<?= $user->uid ?>, 0, 'userpage', '<?=$user->uid?>', 0); return false;" style="display: <?= (($user->warn >= 3 || $user->is_banned) ? 'none' : '') ?>">
                                    Сделать предупреждение</a>&nbsp;&nbsp;
                                <?php $sBanTitle = (!$user->is_banned && !$user->ban_where) ? 'Забанить!' : 'Разбанить'; ?>
                                <span class='warnlink-<?=$user->uid?>'><a class="admn" href="javascript:void(0);" onclick="banned.userBan(<?=$user->uid?>, '<?=$user->uid?>',0)"><?=$sBanTitle?></a></span>
                            </p>

                            <div style='display:none' id="warnreason-<?= $user->uid ?>">&nbsp;</div>
                            <div style='display:none' id="warnlist-<?=$user->uid?>" class="warnlist-<?=$user->uid?>">&nbsp;</div>
                            <script type="text/javascript">
                                xajax_GetWarns(<?= $user->uid ?>, 'userpage');
                            </script>
                        </div>
                    <? } ?>
                <? } ?>




<? if(hasPermissions('users')) { ?>
<script type="text/javascript">
  function bossNote(close)
  {
    document.getElementById('_idBNLnk').style.display=close?'inline':'none';
    document.getElementById('_idBNBx').style.display=close?'none':'block';
    if(close)
      document.getElementById('_idBNBR').checked=<?=($user->boss_rate?'true':'false')?>;
  }
  
  if (typeof adm_edit_content !== "undefined") {
      adm_edit_content.WDCPREFIX = '<?=WDCPREFIX?>';
  }
</script>
<? } ?>
<?php
if ( hasPermissions('users') ) {
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/adm_edit_overlay.php' );
}
?>