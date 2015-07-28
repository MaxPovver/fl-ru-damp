<?php
  require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/commune.common.php");
  $xajax->printJavascript('/xajax/');

  global $id, $comm, $site, $page, $mode, $user_mod, $order_by;

  $user_login = htmlspecialchars(stripslashes(trim(strip_tags($_GET['search']))));//__paramInit('string', 'search', NULL);
  $user_filter = __paramInit('int', 'type', 'type', 0);
  if($mode == 'Asked') $user_filter = 0;
  $utype = commune::MEMBER_ANY | ( $mode=='Asked' ? commune::JOIN_STATUS_ASKED : commune::JOIN_STATUS_ACCEPTED );
  if($user_filter){
      switch($user_filter){
          case 0:
              $utype = commune::MEMBER_ANY | ( $mode=='Asked' ? commune::JOIN_STATUS_ASKED : commune::JOIN_STATUS_ACCEPTED );
              break;
          case 1:
              $utype = commune::MEMBER_ADMIN | commune::MEMBER_MANAGER | commune::MEMBER_MODERATOR;
              break;
          case 2:
              $utype = commune::MEMBER_SIMPLE | ( $mode=='Asked' ? commune::JOIN_STATUS_ASKED : commune::JOIN_STATUS_ACCEPTED );
              break;
     }
  }



  if(!($members = commune::GetMembers($id,
                                      $utype,//commune::MEMBER_SIMPLE | ( $mode=='Asked' ? commune::JOIN_STATUS_ASKED : commune::JOIN_STATUS_ACCEPTED ),
                                      ($page-1) * commune::MAX_MEMBERS_ON_PAGE,
                                      commune::MAX_MEMBERS_ON_PAGE,
                                      pg_escape_string($user_login),
                                      $order_by
                                     )))
    $members = array();

  
  $uri_joined = '/commune/?id='.$id.'&site=Admin.members'.($user_login ? '&search='.$user_login : '').($user_filter ? '&type='.$user_filter : '');
  $uri_ask = $uri_joined.'&mode=Asked';


  list($field,$direction) = explode('_', $order_by);
$user_sort = $order_by == 'name_asc' ? 'name_desc' : 'name_asc';
$date_sort = $order_by == 'date_asc' ? 'date_desc' : 'date_asc';
$asked_sort = $order_by == 'asked_asc' ? 'asked_desc' : 'asked_asc';
$name_link = ($mode == 'Asked' ? $uri_ask : $uri_joined)."&order=$user_sort";
$date_link = ($mode == 'Asked' ? $uri_ask : $uri_joined)."&order=$date_sort";
$asked_link = ($mode == 'Asked' ? $uri_ask : $uri_joined)."&order=$asked_sort";
$arrow_name = $field == 'name' ? ($direction == 'asc' ? '<img src="/images/sort-asc2.png" alt="">' : '<img src="/images/sort-desc2.png" alt="">') : '';
$arrow_date = $field == 'date' ? ($direction == 'asc' ? '<img src="/images/sort-asc2.png" alt="">' : '<img src="/images/sort-desc2.png" alt="">') : '';
$arrow_asked = $field == 'asked' ? ($direction == 'asc' ? '<img src="/images/sort-asc2.png" alt="">' : '<img src="/images/sort-desc2.png" alt="">') : '';
?>



					<h2 class="b-layout__title b-layout__title_padbot_30">Участники сообщества &laquo;<a href="/commune/?id=<?= $comm['id'];?>" class="inherit"><?=$comm['name']?></a>&raquo;</h2>
					<div class="b-menu b-menu_line">
						<ul class="b-menu__list">
							<li class="b-menu__item <?= $mode == 'Asked' ? '' : ' b-menu__item_active';?>">
								<a class="b-menu__link" href="<?= $uri_joined;?>" title="Все участники">Все участники (<?=($joinedCnt + $adminCnt + 1)?>)</a>
							</li>
							<li class="b-menu__item <?= $mode == 'Asked' ? ' b-menu__item_active' : '';?>">
								<a class="b-menu__link" href="<?= $uri_ask;?>" title="Хотят вступить">Хотят вступить (<?=$comm['w_count']?>)</a>
							</li>
						</ul>
					</div>
					<div class="page-commune-ausers c">
						<div class="page-in">
							<div class="form cau-search">
								<b class="b1"></b>
								<b class="b2"></b>
								<div class="form-in">
                                                                    <form action="." method="get">
                                                                        <input type="hidden" name="id" value="<?=$id?>"/>
                                                                        <input type="hidden" name="site" value="Admin.members"/>
                                                                        <input type="hidden" name="mode" value="<?=$mode?>"/>
									<label>Показать:</label>
									<?php if($mode == 'Asked') {?>
									<input type="hidden" name="type" value="0">
									<?php } else { //if?>
                                                                        <select name="type">
                                                                            <option value="0" <?= !$user_filter ? 'selected="selected"' : '';?>>Все пользователи</option>
                                                                            <option value="2" <?= $user_filter == 2 ? 'selected="selected"' : '';?>>Пользователи</option>
                                                                            <option value="1" <?= $user_filter == 1 ? 'selected="selected"' : '';?>>Администраторы</option>
                                                                        </select>
                                    <?php }//else?>                                   
                                                                        <input type="text" name="search" value="<?=$user_login?>"/>
                                                                        <input type="submit" value="Найти" class="i-btn"/>
                                                                    </form>





								</div>
								<b class="b2"></b>
								<b class="b1"></b>
							</div>
<? if($mode == 'Asked') include(TPL_COMMUNE_PATH.'/admin_asked.php');
else include(TPL_COMMUNE_PATH.'/admin_assigned.php'); ?>
						</div>
					</div>


<?php
if(!$user_login){
$url_p = "%s".($mode == 'Asked' ? $uri_ask.($order_by ? "&order=$order_by" : '').'&page=%d' : $uri_joined.($order_by ? "&order=$order_by" : '').'&page=%d')."%s";
echo new_paginator($page, $pages, 3, $url_p);
}
?>


