<?  
//// debug /////////////////////////////////////////////////////////////////////
  if($DEBUG)
    if(!hasPermissions('users') && $_SESSION['login']!='sll' || !$login) { header('Location: /404.php'); exit; }
  // Классы закладок.

  $bmCls = getBookmarksStyles(promotion::BM_COUNT, $bm);

  $fromType = strtolower(__paramInit('string', 'from','from', 'all'));
  if($fromType!='cats' && $fromType!='blogs' && $fromType!='places' && $fromType!='others' && $fromType != 'search') $fromType = 'all';
?>
        <h1 class="b-page__title">Статистика</h1>
    <div id="header">
			<div class="b-menu b-menu_line">
					<ul class="b-menu__list">
							<li class="b-menu__item"><a class="b-menu__link" href="?bm=<?=promotion::BM_PROGNOSES?><?=($DEBUG?"&user={$login}":'')?>">Прогнозы</a></li>
							<li class="b-menu__item b-menu__item_last b-menu__item_active">Посетители</li>
					</ul>
			</div>
		 
			<div class="filtr-user">
					<span>Показать:</span>
					<ul>
							<li <?=($fromType=='all'?'class="active"':'')?>><a href="/promotion/?bm=1&from=all<?= $login ? "&user=$login" : '' ?>">Все</a></li>
							<li <?=($fromType=='cats'?'class="active"':'')?>><a href="/promotion/?bm=1&from=cats<?= $login ? "&user=$login" : '' ?>">Из каталога</a></li>
                            <? if (BLOGS_CLOSED == false) { ?>
							<li <?=($fromType=='blogs'?'class="active"':'')?>><a href="/promotion/?bm=1&from=blogs<?= $login ? "&user=$login" : '' ?>">Из блогов</a></li>
                            <? } ?>
							<li <?=($fromType=='places'?'class="active"':'')?>><a href="/promotion/?bm=1&from=places<?= $login ? "&user=$login" : '' ?>">С платных мест</a></li>
							<li <?=($fromType=='search'?'class="active"':'')?>><a href="/promotion/?bm=1&from=search<?= $login ? "&user=$login" : '' ?>">Из поиска</a></li>
							<li <?=($fromType=='others'?'class="active"':'')?>><a href="/promotion/?bm=1&from=others<?= $login ? "&user=$login" : '' ?>">Другое</a></li>
					</ul>
			</div>
  </div>
      <div class="promotion">
        <?
          if($user->is_pro=='t' || $iAmAdmin)
          {
            $t_guests = promotion::GetGuests($uid,$TODAY,$TOMORROW,$fromType);
            $y_guests = promotion::GetGuests($uid,$YESTERDAY,$TODAY,$fromType);
        ?>      
          <div style="margin:40px 0 0 0">
            <div class="tbl-users">
               <?=t_promotion::guests($t_guests, $HOUR-1, 'Сегодня')?>
            </div>
            <br /><br /><br /><br /><br /><br /><br /><br />
            <div class="tbl-users">
               <?=t_promotion::guests($y_guests, 23, 'Вчера')?>
            </div>
          </div>
        <?
          }
          else
          {
        ?>
          <h1 class="b-layout__title">Функция доступна только пользователям PRO</h1>
          <div class="b-layout__txt b-layout__txt_padbot_10">Чтобы узнать, кто заходил на вашу личную страницу, нужен профессиональный аккаунт.</div>
          <div class="b-layout__txt b-layout__txt_padbot_10">Вы сможете посмотреть, откуда пришли посетители: из Каталога фрилансеров, платных мест, поиска или других разделов сайта. Это поможет оценить эффективность рекламы и платных услуг, которыми вы пользуетесь.</div>
          <div class="b-layout__txt b-layout__txt_padbot_10"><a href="/payed/" class="b-layout__link b-layout__link_bold">Получить аккаунт</span></a> <a href="/payed/" class="b-layout__link"><span class="b-icon b-icon__pro b-icon__pro_f " title="PRO" alt="PRO"></a></div>
          <div class="tbl-users"><img class="b-layout__pic" src="/images/guests_bg.jpg" alt="" /></div>
        <?
          }
        ?>
      </div>
