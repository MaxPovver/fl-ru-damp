<?php
if(!defined('IN_STDF')) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 * Разметка для типовых услуг в формате "список"
 *
 *
 * @var TServiceCatalogController $this
 * @var array $category_title название выбранной категории
 * @var int $total общее количество карточек по параметрам поиска
 * @var bool $nothing_found true если искали, но ничего не нашли
 * @var array $tservices найденные карточки типовых услуг
 * @var int $page номер страницы
 * @var int $limit количество карточек на странице
 * @var string $paging_base_url базовый URL без номера текущей страницы для пейджинга
 */

/**
 * Возвращает форматированное число, например: 123 456,78
 *
 * @var mixed $number
 * @var int $precition число знаков после запятой
 */
function _format($number, $precition=2) {
	$number = (string) round($number, $precition);
	preg_match("/([0-9]+)(?:\.([0-9]+))?/", $number, $o);
	$piece = strlen($o[1]) % 3;
	$a = substr($o[1], 0, $piece);
	for ($i=$piece; $i<strlen($o[1]); $i=$i+3) {
		$a .= '&nbsp;'.substr($o[1], $i, 3);
	}
	if ($o[2]) $a .= ','.$o[2];
	return $a;
}
?>
<h2 class="b-layout__txt b-page__iphone">
	<?php if ($total) {?>
    <span class="b-txt b-layout__txt_bold">
        <?=ending($total,'Найдена','Найдены','Найдены')?> 
        <?=number_format($total, 0, '', ' ')?>
        <?=ending($total,'услуга','услуги','услуг')?>
    </span>
	<?php } ?>
</h2>
<?php if(!$nothing_found){ ?>
<div class="b-layout__txt b-layout__txt_float_right b-layout__txt_padtop_5 b-layout__txt_padbot_20 b-layout__txt_float_none_iphone b-layout__txt_bold">
 Сортировать по: 
 <span class="i-shadow">
   <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" onClick="this.getNext().toggleClass('b-shadow_hide');" href="javascript:void(0)">
      <?=$orders[$cur_order]?>
   </a>
   <div class="b-shadow b-shadow_pad_10 b-shadow_width_140 b-shadow_top_20 b-shadow_left_-30 b-shadow_hide">
      <?php $i=0; foreach($orders as $orderKey => $orderTitle) { 
          $i++;
          $padbot = $i == count($orders) ? '' : ' b-layout__txt_padbot_5';
          $useLink = $cur_order != $orderKey;
      ?>
       <div class="b-layout__txt b-layout__txt_weight_normal<?=$padbot?>">
           <?php if($useLink) { ?><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 tu-order-link"  onclick="TServices_Catalog.changeFilterOrder('<?=$orderKey?>');return false;" href="javascript:void(0)"><?php } ?>
           <?=$orderTitle?>
           <?php if($useLink) { ?></a><?php } ?>
       </div>
      <?php } ?>
      <span class="b-shadow__icon b-shadow__icon_nosik"></span>
   </div>
</span>
</div>

<h2 class="b-layout__txt b-layout__txt_padbot_20 b-page__desktop b-page__ipad">
	<?php if ($total) {?>
        <?=ending($total,'Найдена','Найдены','Найдены')?> 
        <?=number_format($total, 0, '', ' ')?>
        <?=ending($total,'услуга','услуги','услуг')?>
	<?php } ?>
</h2>
<?php } ?>

<?php $this->renderClip('bind_teaser_short'); ?>
<aside class="b-layout__side b-layout__side_content b-layout__side_padtop_10">
    <?php echo $this->renderClip('bind_teaser') ?>
	<?php if ($nothing_found) {?>
		<div class="b-post b-post_padtop_60">
			<h4 class="b-post__h4 b-post__h4_padbot_5 b-post__h4_center">Услуг не найдено</h4>
			<div class="b-post__txt b-post__txt_padbot_10 b-post__txt_center">Попробуйте изменить параметры фильтра</div>
            <div class="b-layout__txt b-post__txt_padbot_20 b-layout__txt_center b-layout__txt_bold">или</div> 
            <div class="b-layout__txt b-layout__txt_center"><a href="/public/?step=1&amp;kind=1&amp;red=/<?php 
			if(is_emp()&&is_pro()) print '&utm_source=tu_catalog&utm_medium=emp&utm_content=pro&utm_campaign=btn_new_project';
			elseif(is_emp()&&!is_pro()) print '&utm_source=tu_catalog&utm_medium=emp&utm_content=unpro&utm_campaign=btn_new_project';
			elseif(!is_emp()&&is_pro()&&get_uid()) print '&utm_source=tu_catalog&utm_medium=frl&utm_content=pro&utm_campaign=btn_new_project';
			elseif(!is_emp()&&!is_pro()&&get_uid()) print '&utm_source=tu_catalog&utm_medium=frl&utm_content=unpro&utm_campaign=btn_new_project';
			elseif(!get_uid()) print '&utm_source=tu_catalog&utm_medium=uauth&utm_content=unpro&utm_campaign=btn_new_project';
			?>" class="b-button b-button_flat b-button_flat_orange b-button_width_190">Опубликуйте проект</a></div>
		</div>
	<?php } ?>
    
    <?php $tservices_unique = array(); ?>
	<?php foreach($tservices as $key => $tservice) { ?>

		<?php
        if (in_array($tservice['id'], $tservices_unique)) {
            continue;
        }
        $tservices_unique[] = $tservice['id'];
        
		$user = $tservice['user'];
		$user_url = sprintf('/users/%s', $user['login']);
		$tservice_url = sprintf('/tu/%d/%s.html', $tservice['id'], tservices_helper::translit($tservice['title']));
		$avatar_url = tservices_helper::photo_src($user['photo'], $user['login']);

		//$hasVideo = !empty($tservice['videos']) && count($tservice['videos']);
                $hasVideo = false; //Теперь вместо видео-кадра - превью
		if ($hasVideo)
		{
			$video = current($tservice['videos']);
			$video_thumbnail_url = tservices_helper::setProtocol($video['image']);
			$thumbnail100x75 = '<img width="100" height="75" class="b-pic" src="'.$video_thumbnail_url.'" itemprop="contentUrl">';
		} elseif(!empty($tservice['file']))
		{
			$hasVideo = false;
			$image_url = tservices_helper::image_src($tservice['file'],$user['login']);
			$thumbnail100x75 = '<img width="100" height="75" class="b-pic" src="'.$image_url.'" itemprop="contentUrl">';
		} else
		{
			$thumbnail100x75 = '<div class="b-pic b-pic_no_img b-pic_w100_h75 b-pic_bg_f2"></div>';
		}

		$hasVideo = !empty($tservice['videos']) && count($tservice['videos']);
                
        $sold_count = isset($tservice['count_sold']) ? $tservice['count_sold'] : $tservice['total_feedbacks']; // Пока сфинск не считает все покупки, будем брать отзывы. #0026584
        
        $is_owner = $tservice['user_id'] == $uid;
		?>
        
        <?php if ($tservice['is_binded']): ?>
        <div class="b-post b-post_margbot_20">
            <div class="b-pay-tu b-pay-tu_hor b-pay-tu_payed<?php if($is_owner):?>-my<?php endif; ?>">
                <span class="b-pay-tu__mpin"></span>
        <?php endif; ?>
                
		<article class="b-post <?php if (!$tservice['is_binded']): ?>b-post_margbot_20<?php endif; ?> b-layout_box" itemscope itemtype="http://schema.org/ImageObject">
			<span class="i-pic i-pic_fl">
				<a href="<?=$tservice_url?>" class="b-pic__lnk b-pic__lnk_relative">
					<div class="b-pic__bord_e6e6e6"></div>
					<?php if($hasVideo) { ?><div class="b-icon b-icon__play b-icon_absolute b-icon_bot_4 b-icon_right_4"></div><?php } ?>
					<?=$thumbnail100x75?>
                    <span class="b-layout_hide" itemprop="description"><?=SeoTags::getInstance()->getImageDescription() ?></span>
				</a>
			</span>

			<div class="b-post__body b-post__body_margleft_120">
				<h2 class="b-post__title b-post__title_padbot_10 b-post__title_fontsize_16">
					<span class="b-post__price b-post__price_ptsans b-post__price_fontsize_17 b-post__price_padtop_3 b-page__desktop b-page__ipad">&#160; <?=tservices_helper::cost_format($tservice['price'],true)?></span>
					<a href="<?=$tservice_url?>" class="b-post__link b-post__link_bold b-post__link_ptsans" itemprop="name"><?=reformat($tservice['title'], 20, 0, 1)?></a>
					<span class="b-post__price b-post__price_ptsans b-post__price_fontsize_17 b-page__iphone"><?=tservices_helper::cost_format($tservice['price'],true)?></span>
				</h2>
                                <div class="b-layout__txt b-layout__txt_float_right b-page__desktop">
                                    <a href="javascript:void(0);" data-url="<?=$tservice_url?>" onclick="TServices_Catalog.orderNow(this);" class="b-layout__link b-layout__link_color_6db335 b-layout__link_bold">
                                        Заказать услугу
                                    </a>
                                </div>
				<div class="b-user b-user_inline-block b-user_padright_20">
					<a title="<?=$user['uname'].' '.$user['usurname']?>" href="<?=$user_url?>"
						class="b-user__link b-user__link_color_ec6706"><img
							width="15" height="15" class="b-user__pic b-user__pic_15" src="<?=$avatar_url?>" alt="">
							<?=view_fullname($user)?>
                    </a>
                    <?php if($user['is_profi'] == 't'){ ?>
                        <?=view_profi($class = '')?>&nbsp;
                    <?php } elseif($user['is_pro'] === 't'){?>
                    <a title="Платный аккаунт" target="_blank" href="/payed/" class="b-user__link"><span alt="Платный аккаунт" class="b-icon b-icon__pro b-icon__pro_f b-icon_top_null"></span></a>&nbsp;
                    <?php } ?>
                    <?php if($user['is_verify'] === 't'){?>
                    <a title="Верифицированный пользователь" href="/promo/verification" target="_blank" class="b-user__link"><span alt="Верифицированный пользователь" class="b-icon b-icon__ver b-icon_top_-2"></span></a>
                    <?php } ?>
				</div>
				<div class="b-user b-user_inline-block">
					<?php if ($sold_count!=0) { ?>
						<div class="b-icon b-icon__tu2_gray" title="Количество продаж услуги"></div>
						<span class="b-txt b-txt_lh_1 b-txt_fs_14 b-txt_padright_20" title="Количество продаж услуги"><?=number_format($sold_count, 0, '', ' ')?></span>
                                        <?php } ?>
                                                
                                        <?php if ($tservice['total_feedbacks']!=0) { ?>
						<div class="b-icon b-icon__cat_thumbsup_gray b-icon__cat_thumbsup2_wh_13 b-icon_top_-1" title="Процент положительных отзывов об услуге"></div>
						<span class="b-txt b-txt_lh_1 b-txt_fs_14 b-txt_padright_20" title="Процент положительных отзывов об услуге"><?=_format($tservice['plus_feedbacks'] / $tservice['total_feedbacks'] * 100, 0)?>%</span>
					<?php } ?>

					<div class="b-icon b-icon__time_gray" title="Срок выполнения услуги"></div>
					<span class="b-txt b-txt_lh_1 b-txt_fs_14" title="Срок выполнения услуги"><?=$tservice['days']?> <?=ending($tservice['days'],'день','дня','дней')?></span>
<?php 
    //Если админ показываем 1 параметр по #0026579
    if($is_adm)
    {
?>
                    <span class="b-txt b-txt_lh_1 b-txt_fs_14 b-txt_color_000 b-txt_padleft_20">
                        <?php echo sprintf('%s',$tservice['payed_tax']); ?>
                    </span>
<?php                                
    }
?>    
				</div>
                <div class="b-layout__txt b-page__ipad b-page__iphone"><a href="<?=$tservice_url?>" class="b-layout__link b-layout__link_color_6db335 b-layout__link_bold">Заказать услугу</a></div>
			</div>
		</article>
                
<?php if ($tservice['is_binded']): ?>
    <?php if($is_owner):?>
        <?php $this->renderClip('bind_links_'.$tservice['id']) ?>
    <?php endif; ?>
            </div>
        </div>
<?php endif; ?>
    
    

	<?php } // foreach($tservices as $tservice) ?>

	<?php
		$pages = ceil($total / $limit);
		if ($pages > 1)
		{
			$paging_url = str_replace('%', '%%', $paging_base_url) . (false===strpos($paging_base_url, '?') ? '?' : '&') . 'page=%d';
			$paginator_html = new_paginator2($page, $pages, 4, "%s{$paging_url}%s");
			// удаляем параметр 'page=1' у первой страницы для более чистого url
			$paginator_html = preg_replace('/\?page=1&/', '?', $paginator_html); // "?page=1&c=d" -> "?c=d"
			$paginator_html = preg_replace('/\?page=1"/', '"', $paginator_html); // "?page=1" -> ""
			$paginator_html = preg_replace('/&page=1&/', '&', $paginator_html); // "?a=b&page=1&c=d" -> "?a=b&c=d"
			$paginator_html = preg_replace('/&page=1"/', '"', $paginator_html); // "?a=b&page=1" -> "?a=b"
			echo $paginator_html;
		}
	?>
    
    <?php if (isset($popups) && is_array($popups)): ?>
        <?php foreach ($popups as $popup): ?>
            <?=$popup?>
        <?php endforeach; ?>
    <?php endif; ?>
</aside>

<div class="b-layout b-layout_clear_both b-layout_padtop_30">
    <h2 class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_666 b-layout_top_100 b-layout__txt_weight_normal">
        <?php echo SeoTags::getInstance()->getFooterHead() ?>
    </h2>
    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_666 b-layout_top_100 b-layout__txt_padbot_10 b-layout__txt_weight_normal">
        <?php echo SeoTags::getInstance()->getFooterText() ?>
    </div>
        
        
        
</div>