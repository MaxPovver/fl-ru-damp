<?php
global $id, $comm, $site, $page;

$user_login = __paramInit('string', 'search', NULL);

// Все админы (модераторы, упрявляторы).
if (!$page || (int) $page == 1)
    if (!($admins = commune::GetMembers($id, commune::MEMBER_ADMIN | commune::JOIN_STATUS_ACCEPTED))) // Хотя модераторы всегда is_accepted.
        $admins = array();


$search_string = preg_replace("/\s/i", "|", $user_login);
if (!($members = commune::GetMembers($id, $user_login == NULL ? commune::MEMBER_SIMPLE | commune::JOIN_STATUS_ACCEPTED : commune::MEMBER_ANY,
                ($page - 1) * commune::MAX_MEMBERS_ON_PAGE,
                commune::MAX_MEMBERS_ON_PAGE, $search_string)))
    $members = array();

//  if(!($members_t = commune::GetMembers($id,
//                                      commune::MEMBER_ANY | commune::JOIN_STATUS_ACCEPTED,
//                                      //commune::MEMBER_SIMPLE | commune::JOIN_STATUS_ACCEPTED,
//                                      ($page-1) * commune::MAX_MEMBERS_ON_PAGE,
//                                      commune::MAX_MEMBERS_ON_PAGE,
//                                      $user_login
//                                     )))
//    $members_t = array();
//
//  $members = array();
//  foreach ($members_t as $member){
//      if($comm['user_id'] == $member['user_id']){
//          $members['creator'][] = $member;
//      }elseif($member['is_admin'] == 't' || $member['is_manager'] == 't' || $member['is_moderator'] == 't'){
//          $members['admins'][] = $member;
//      }else{
//          $members['users'][] = $member;
//      }
//  }
//
//var_dump($members);
//$adminCnt = commune::GetAdminCount($id);
?>

    <h1 class="b-page__title">Участники сообщества &laquo;<a href="/commune/?id=<?= $comm['id']; ?>" class="b-layout__link"><?= $comm['name'] ?></a>&raquo;</h1>
    <div class="page-commune-users c">
        <div class="page-in">
				<div class="b-fon b-fon_bg_d0f2a5 b-layout_pad_5 b-fon_margbot_30 b-fon__border_radius_3">
            <form method="GET" class="" id="search_user_form">
                <input type="hidden" name="id" value="<?=$comm['id']?>">
                <input type="hidden" name="site" value="Members">
										<table cellspacing="0" cellpadding="0" class="b-layout__table"><tbody><tr class="b-layout__tr"><td class="b-layout__td b-layout__td_width_full">
												<div class="b-input b-input_height_24">
													<input type="text" autocomplete="off" name="search" value="<?= stripslashes($user_login)?>"placeholder='Поиск по участникам: логин, имя, фамилия пользователя' class="b-input__text" id="b-input">
												</div>
										</td><td class="b-layout__td">
										<a onclick="$('search_user_form').submit();" href="javascript:void(0)" class="b-button b-button_flat b-button_flat_grey b-button_margleft_5">Найти</a>
										</td></tr></tbody></table>
            </form>
        </div>				
				
				
				
				
				
            <? if ((!$page || (int) $page == 1) && $user_login == "") {
 ?>
                <h3>Создатель:</h3>
                        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" border="0" cellpadding="0" cellspacing="0">
							<tr class="b-layout__tr">
								<td class="b-layout__left b-layout__left_width_50"><?= __commPrntUsrAvtr($comm, "author_") ?></td>
								<td class="b-layout__right b-layout__right_padleft_10"><?= __commPrntUsrInfo($comm, 'author_', '', '', true) ?></td>
                            </tr>
                        </table>
            <? } ?>
<? if (!empty($admins) && $user_login == "") { ?>
                        <h3>Администрация:</h3>
                        <ul class="c">
<? foreach ($admins as $memb) { ?>
                            <li>
                                <div>
                        <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
							<tr class="b-layout__tr">
								<td class="b-layout__left b-layout__left_width_50"><?= __commPrntUsrAvtr($memb) ?></td>
								<td class="b-layout__right b-layout__right_padleft_10"><div style="word-wrap:break-word; width:220px;"><?= __commPrntUsrInfo($memb) ?></div></td>
                            </tr>
                        </table>
                        </div>
                    </li>
<? } ?>
                    </ul>
            <? } ?>
<? if (!empty($members)) { ?>
                        <h3><?= ($user_login != ""?'Результат поиска:':'Участники:')?></h3>
                        <ul class="c">
<? foreach ($members as $memb) { ?>
                            <li>
                                <div>
                        <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
							<tr class="b-layout__tr">
								<td class="b-layout__left b-layout__left_width_50"><?= __commPrntUsrAvtr($memb) ?></td>
								<td class="b-layout__right b-layout__right_padleft_10"><div style="word-wrap:break-word; width:220px;"><?= __commPrntUsrInfo($memb) ?></div></td>
                            </tr>
                        </table>
                        </div>
                    </li>
<? } ?>
                    </ul>
                    <?= ($user_login != ""?'<a href="/commune/?id='.$id.'&site='.$site.'">Остальные пользователи</a>':'')?>
<? } elseif ($user_login != "")  { ?><br/>Ничего не найдено<br/><br/><a href="/commune/?id=<?=$id?>&site=<?=$site?>">Остальные пользователи</a><?php } //elseif?>

        </div>
    </div>
<!-- пейджер с главной  -->
<?php if($user_login == "") { ?>
    <?= new_paginator($page, (int)$pages, 4, "%s?id={$id}&site={$site}&page=%d%s")?>
<?php  } ?>
<style type="text/css">.b-icon__ver{position:relative; top:2px;} .msie .b-icon__ver{ top:1px;} </style>
