<?
  require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/projects.common.php");
  $xajax->printJavascript('/xajax/');

  $uid = get_uid();
  // Предложения по проектам.
  $obj_offer = new projects_offers();
  $po_waste = projects_offers::GetFrlOffersWaste($uid);
  $pocnt[0] = $po_summary['total'] - (int)$po_waste['total'];
  $pocnt[2] = $po_summary['selected'] - (int)$po_waste['selected'];
  $pocnt[3] = $po_summary['executor_2'] - (int)$po_waste['executor'];
  $pocnt[4] = $po_summary['refused'] - (int)$po_waste['refused'];
  $pocnt[5] = (int)$po_waste['total'];
  $pocnt[1] = $pocnt[0] - $pocnt[2] - $pocnt[3] - $pocnt[4] - $pocnt[6];
  
  $page   = __paramInit('int', 'page', 'page', 1);
  $pages = 1;
  $prj_count = 0;
  
  //Получаем количество заказов по ТУ
  require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
  $tu_cnts = TServiceOrderModel::model()->getCounts($uid, FALSE);
?>
    <h1 class="b-page__title" id="prj_name_<?=$project['id']?>"><?=$sBox1?><?=reformat($sTitle,30,0,1); ?></h1>
		<ul class="frl-prj-sort">
      <?php if($tu_cnts['total'] > 0){ ?><li><a href="/tu-orders/">Все заказы</a> <span><?=$tu_cnts['total']?></span></li><?php } ?>
      <li class="fp-s1<?=(!$folder ? ' active' : '')?>"><a href="?p=list"><strong>Все проекты</strong></a> <span id="prjfld_cnt0"><?=$pocnt[0]?></span></li>
      <li class="fp-s2<?=($folder==1 ? ' active' : '')?>"><a href="?p=list&fld=1">Не определен</a> <span id="prjfld_cnt1"><?=$pocnt[1]?></span></li>
      <li class="fp-s3<?=($folder==2 ? ' active' : '')?>"><a href="?p=list&fld=2">Кандидат</a> <span id="prjfld_cnt2"><?=$pocnt[2]?></span></li>
      <li class="fp-s4<?=($folder==3 ? ' active' : '')?>"><a href="?p=list&fld=3">Исполнитель</a> <span id="prjfld_cnt3"><?=$pocnt[3]?></span></li>
      <li class="fp-s5<?=($folder==4 ? ' active' : '')?>"><a href="?p=list&fld=4">Отказали</a> <span id="prjfld_cnt4"><?=$pocnt[4]?></span></li>
      <li class="fp-s6<?=($folder==5 ? ' active' : '')?>"><a href="?p=list&fld=5">Корзина</a> <span id="prjfld_cnt5"><?=$pocnt[5]?></span></li>
		</ul>
    
    <?
      $all_count = -1; // -1, если не нужно получать количество.
      if(!($prjs = projects::GetFrlMenuProjects($uid, $folder, ($page-1)*40, 40, $all_count)))
        $prjs = array();

      $all_count = (int)$all_count >= 0 ? $all_count : $pocnt[$folder];
      $pages = ceil($all_count / 40);
    ?>

	<div class="b-layout b-layout_relative">
      <? 
      if (sizeof($prjs))
      {
        foreach($prjs as $prj)
        {
            $prj['name'] = htmlspecialchars($prj['name'], ENT_QUOTES, 'CP1251', false);
            $prj['descr'] = htmlspecialchars($prj['descr'], ENT_QUOTES, 'CP1251', false);
            
          $prj['role'] = '1'; // авторы проектов все работодатели поголовно
          $prj_count++;

          $msg_cnt = $prj['msg_count'] ? $prj['msg_count'].' '.getSymbolicName($prj['msg_count'], (($prj['kind']==7)? 'comments': 'messages')) : '';
		  if ($prj['kind'] != 7) {
		      $nmsg_cnt = $prj['frl_new_msg_count'] ? $prj['frl_new_msg_count'].' '.ending($prj['frl_new_msg_count'], 'новое сообщение', 'новых сообщения', 'новых сообщений')
                                                    : ($prj['emp_new_msg_count'] ? 'Сообщение не прочитано' : 'Сообщение прочитано');
		  } else {
		      $nmsg_cnt = $prj['frl_new_msg_count'] ? $prj['frl_new_msg_count'].' '.ending($prj['frl_new_msg_count'], 'новый', 'новых', 'новых')
                                                    : '';
		  }

          $link = "/projects/{$prj['id']}?f=3";
          
          $is_personal = ($prj['kind'] == 9);
          $is_contest = in_array($prj['kind'], array(projects::KIND_CONTEST, projects::KIND_CONTEST7));

          $is_not_payed_vacancy = $prj['kind'] == projects::KIND_VACANCY && $prj['state'] == projects::STATE_MOVED_TO_VACANCY;
          
        ?>

        <div id="prjoffer_box<?=$prj['offer_id']?>" class="b-post b-post_padtop_10 b-post_padbot_15 b-post_relative b-post_overflow_hidden">
            <div class="b-page__desktop b-page__ipad">
            <? if((int)$prj['cost'] > 0) { ?>
            <div class="b-post__price b-post__price_padleft_10 b-post__price_padbot_5 b-post__price_fontsize_15 b-post__price_bold b-post__price_float_right"><?=CurToChar($prj['cost'], $prj['currency'])?></div>
            <? } else { ?>
			<div class="b-post__price b-post__price_padleft_10 b-post__price_padbot_5 b-post__price_fontsize_13 b-post__price_float_right">По договоренности</div>
            <? } ?>
            </div>
            <h2 class="b-post__title b-post__title_inline">
            <? if($prj['sbr_id']) { ?>
               <a class="b-post__link" href="/bezopasnaya-sdelka/?id=<?=$prj['sbr_id']?>" title="Безопасная сделка"><img src="/images/shield_sm.gif" alt="Безопасная сделка" class="ico-prepay" /></a>
            <? } if($prj['ico_closed']=='t') { ?>
               <img src="/images/ico_closed.gif" alt="Проект закрыт" width="21" height="21" class="ico-closed" />
            <? } ?>

            <?php if($is_not_payed_vacancy): ?>
                <span class="b-layout__txt b-layout__txt_fontsize_20 b-layout__txt_color_808080"><?=reformat2($prj['name'], 50,0,1)?></span>
            <?php else: ?>
                <a class="b-post__link" href="<?=$link?>"><?=reformat2($prj['name'], 50,0,1)?></a>
            <?php endif; ?>
                    
              <a class="b-post__link" href="javascript:void(0);" onclick="xajax_WstProj(<?=$prj['offer_id']?>, <?=$prj['folder']?>)">
                <? if($folder!=5) { ?>
                  <img src="/images/frl-prj-del.png" alt="Убрать в корзину" class="frl-prj-del" />
                <? } else { ?>
                  <img src="/images/frl-prj-restore.png" alt="Восстановить" class="frl-prj-del" />
                <? } ?></a>
            </h2>
            <?php if($is_not_payed_vacancy): ?>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold">Вакансия временно скрыта и будет восстановлена после оплаты ее публикации заказчиком.</div>
            <?php endif; ?>
            <?php if($is_personal): ?>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold">Персональный проект для вас</div>
            <?php endif; ?>
            <div class="b-page__iphone">
            <? if((int)$prj['cost'] > 0) { ?>
            <div class="b-post__price b-post__price_padleft_10 b-post__price_padbot_5 b-post__price_fontsize_15 b-post__price_bold b-post__price_float_right"><?=CurToChar($prj['cost'], $prj['currency'])?></div>
            <? } else { ?>
			<div class="b-post__price b-post__price_padleft_10 b-post__price_padbot_5 b-post__price_fontsize_13 b-post__price_float_right">По договоренности</div>
            <? } ?>
            </div>
            <? if ($prj['logo_name']) { ?>
                  <? if($prj['link']) { ?>
                    <a class="b-post__link" href="http://<?=formatLink($prj['link'])?>" target="_blank" nofollow ><img class="b-post__pic b-post__pic_float_right b-post__pic_clear_right b-post__pic_margleft_10" src="<?=WDCPREFIX.'/'.$prj['logo_path'].$prj['logo_name']?>" /></a>
                  <? } else { ?>
                    <img class="b-post__pic b-post__pic_float_right b-post__pic_clear_right b-post__pic_margleft_10" src="<?=WDCPREFIX.'/'.$prj['logo_path'].$prj['logo_name']?>" />
                  <? } ?>
            <? } ?>
          <div class="b-post__body b-post__body_padtop_15 b-post__body_padbot_10">
            <div class="b-post__txt">
               <?=reformat2(LenghtFormatEx($prj['descr'],300), 50, 1, 0, 1)?>
            </div>
          </div>
          <? 
             if ($prj['pro_only'] != 't' || $is_pro) {
               $i = 0;
               if($attach = projects::GetAllAttach($prj['id'])) {
                 foreach($attach as $a) {
                   if ($a['name']) { 
                     ?><div class="flw_offer_attach"><a href="<?=WDCPREFIX.'/'.$a['path'].$a['name']?>" target="_blank">Загрузить</a> (<?=$a['ftype']?>; <?=ConvertBtoMB($a['size'])?>)</div><?
                   }
                   $i++;
                 }
               }
               if ($prj['attach']) {
                   $cfile = new CFile("users/".substr($prj['login'], 0, 2)."/".$prj['login']."/upload/".$prj['attach']);
                 ?><div class="flw_offer_attach">
                     <a href="<?=WDCPREFIX?>/users/<?=$prj['login']?>/upload/<?=$prj['attach']?>">Загрузить</a>
                     (<?=strtolower($cfile->getext())?>; <?=ConvertBtoMB($cfile->size)?>)
                   </div><?
                 $i++;
               }
               if($i) print('<br/>');
             }
          ?>
          <div class="frl-prj-status b-page__desktop">
            <? if($prj['folder']==1) { ?>
              <span class="fps1"><?=(($prj['contest_end']=='t')? 'Конкурс окончен, вы не победили': 'Ваша кандидатура на рассмотрении')?></span>
            <? } elseif ($prj['folder']==1) { ?>
			  <span class="fps4">Конкурс окончен, вы не победили</span>
			<? } elseif($prj['folder']==2) { ?>
              <span class="fps3">Заказчик определил вас в кандидаты</span>
            <? } elseif($prj['folder']==3) { ?>
              <span class="fps2">Заказчик определил вас в исполнители</span>
            <? } elseif($prj['folder']==4) { ?>
              <span class="fps4">Вы получили отказ</span>
  		    <? } ?>
            <? if($prj['sbr_id']) { ?>
              <span class="frl-prj-cbr"><a class="b-layout__link" href="/bezopasnaya-sdelka/?id=<?=$prj['sbr_id']?>">Заказчик предложил провести БС</a></span>
            <? } ?>
              
            <?php if(!$is_not_payed_vacancy): ?>
            <span class="frl-prj-mess">
              <? if($msg_cnt) { ?>
                <span>
                  <? if($prj['frl_new_msg_count']) { ?>
                    <a class="b-layout__link b-layout__link_bold" href="<?=$link?>"><?=$nmsg_cnt?></a>
                  <? } else print($nmsg_cnt) ?>
                </span>
                <a href="<?=$link?>" class="fpm-all"><?=$msg_cnt?></a>
              <? } ?>
            </span>
            <?php endif; ?>
          </div>
            <div class="b-layout__txt b-layout__txt_fontsize_11">Прошло времени с момента публикации: <?=ago_pub_x(strtotimeEx($prj['post_date']))?></div>
            
            <?php if (!$is_not_payed_vacancy): ?>
                <?php if (($prj['folder'] == 3) || $is_pro || $is_contest): ?> 
                    <div class="b-layout__txt b-layout__txt_fontsize_11">
                        Автор: 
                        <a href="/users/<?=$prj['login']?>" class="b-layout__link b-layout__link_color_6db335 b-layout__link_bold">
                            <?=$prj['uname']?> <?=$prj['usurname']?> [<?=$prj['login']?>]
                        </a> <?= view_mark_user($prj);?>
                    </div>
                <?php else: ?>
                    <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bg_fff7ee">
                        Контакты заказчика видны только пользователям с аккаунтом <?= view_pro() ?>
                    </span>            
                <?php endif; ?>
            <?php endif; ?>
            

             <?php if(!$is_personal): ?>  
             <div class="b-layout__txt b-layout__txt_fontsize_11 <?=($prj['pro_only'] == 't' || $prj['verify_only'] == 't' ? '' : ' class="last"')?>">Раздел: <?=projects::getSpecsStr($prj['id'],' / ', ', ')?>
             <? if($prj['pro_only'] == 't' || $prj['verify_only'] == 't' || $prj['prefer_sbr'] == 't') { ?>
             <div class="b-layout__txt b-layout__txt_fontsize_11">
                 <span style="background-color:#fff7ee">
					 <? if($prj['pro_only'] == 't' && $prj['verify_only'] != 't') { ?>
                       Отвечать на проект могут только пользователи с аккаунтом <?=view_pro()?>
                     <? }elseif($prj['pro_only'] != 't' && $prj['verify_only'] == 't') { ?>
                       Отвечать на проект могут только пользователи с верифицированным аккаунтом <span class="b-icon b-icon__ver b-icon_valign_bot"></span>
                     <? }elseif($prj['pro_only'] == 't' && $prj['verify_only'] == 't') { ?>
                       Отвечать на проект могут только пользователи с аккаунтом <?=view_pro()?> и верифицированным аккаунтом <span class="b-icon b-icon__ver b-icon_valign_bot"></span>
                     <? } ?>
                      <? if(($prj['pro_only'] == 't' || $prj['verify_only'] == 't') && $prj['prefer_sbr'] == 't') { ?>&#160;<? } ?>
                      <? if($prj['prefer_sbr'] == 't') { ?>Предпочитаю работать через БС <a class="b-layout__link b-layout__link_lineheight_1" href="/promo/bezopasnaya-sdelka/"><span class="b-icon b-icon__shield"></span></a><? } ?>
                 </span>
             </div>
             <? } ?>

            <?php if(!$is_not_payed_vacancy): ?>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_10">
              <a class="b-layout__link" href="<?=$link?>">Ответы (<?=$prj['offers_count']?>)</a>
            </div>
            <?php endif; ?>
                 
          <div class="frl-prj-status b-page__iphone b-page__ipad">
            <? if($prj['folder']==1) { ?>
              <span class="fps1"><?=(($prj['contest_end']=='t')? 'Конкурс окончен, вы не победили': 'Ваша кандидатура на рассмотрении')?></span>
            <? } elseif ($prj['folder']==1) { ?>
			  <span class="fps4">Конкурс окончен, вы не победили</span>
			<? } elseif($prj['folder']==2) { ?>
              <span class="fps3">Заказчик определил вас в кандидаты</span>
            <? } elseif($prj['folder']==3) { ?>
              <span class="fps2">Заказчик определил вас в исполнители</span>
            <? } elseif($prj['folder']==4) { ?>
              <span class="fps4">Вы получили отказ</span>
  		    <? } ?>
            <? if($prj['sbr_id']) { ?>
              <span class="frl-prj-cbr"><a class="b-layout__link" href="/bezopasnaya-sdelka/?id=<?=$prj['sbr_id']?>">Заказчик предложил провести БС</a></span>
            <? } ?>
            <?php if(!$is_not_payed_vacancy): ?>
            <span class="frl-prj-mess">
              <? if($msg_cnt) { ?>
                <span>
                  <? if($prj['frl_new_msg_count']) { ?>
                    <a class="b-layout__link b-layout__link_bold" href="<?=$link?>"><?=$nmsg_cnt?></a>
                  <? } else print($nmsg_cnt) ?>
                </span>
                <a href="<?=$link?>" class="fpm-all"><?=$msg_cnt?></a>
              <? } ?>
            </span>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <?
      
        }
      }
      else
      {
        echo "<div class=\"project-preview\">Ничего не найдено</div>";
      }
       
      ?>
    </div>
    <?
      print(get_pager($pages, $page, "?p=list&fld={$folder}&page="));
    ?>
<script><? if($pages<=$page||!$prj_count) { ?>function dprj(){}<? }
     else { ?>var prjc=<?=$prj_count?>;function dprj(){if(!(--prjc))window.location.href='?p=list&fld=<?=$folder?>&page=<?=$page?>';}<? } ?></script>
