<?php
if(!defined('IN_STDF')) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 * –азметка дл€ типовых услуг в формате "черепица"
 *
 *
 * @var TServiceCatalogController $this
 * @var array $category выбранна€ категори€
 * @var int $total общее количество карточек по параметрам поиска
 * @var bool $nothing_found true если искали, но ничего не нашли
 * @var array $tservices найденные карточки типовых услуг
 * @var int $page номер страницы
 * @var int $limit количество карточек на странице
 * @var string $paging_base_url базовый URL без номера текущей страницы дл€ пейджинга
 */

/**
 * ¬озвращает форматированное число, например: 123 456,78
 *
 * @var mixed $number
 * @var int $precition число знаков после зап€той
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

$bind_teaser = $this->renderClip('bind_teaser', array(), true);
?>

<?php $this->renderClip('bind_teaser_short', array()); ?>
<aside class="b-layout__side b-layout__side_content " id="tservices_tile">
    <?php echo $bind_teaser ?>
	<?php if ($nothing_found) {?>
		<div class="b-post b-post_padtop_60">
			<h4 class="b-post__h4 b-post__h4_padbot_5 b-post__h4_center">”слуг не найдено</h4>
			<div class="b-post__txt b-post__txt_padbot_10 b-post__txt_center">ѕопробуйте изменить параметры фильтра</div>
            <div class="b-layout__txt b-post__txt_padbot_20 b-layout__txt_center b-layout__txt_bold">или</div> 
            <div class="b-layout__txt b-layout__txt_center"><a href="/public/?step=1&amp;kind=1&amp;red=/<?php 
			if(is_emp()&&is_pro()) print '&utm_source=tu_catalog&utm_medium=emp&utm_content=pro&utm_campaign=btn_new_project';
			elseif(is_emp()&&!is_pro()) print '&utm_source=tu_catalog&utm_medium=emp&utm_content=unpro&utm_campaign=btn_new_project';
			elseif(!is_emp()&&is_pro()&&get_uid()) print '&utm_source=tu_catalog&utm_medium=frl&utm_content=pro&utm_campaign=btn_new_project';
			elseif(!is_emp()&&!is_pro()&&get_uid()) print '&utm_source=tu_catalog&utm_medium=frl&utm_content=unpro&utm_campaign=btn_new_project';
			elseif(!get_uid()) print '&utm_source=tu_catalog&utm_medium=uauth&utm_content=unpro&utm_campaign=btn_new_project';
			?>" class="b-button b-button_flat b-button_flat_orange b-button_width_190">ќпубликуйте проект</a></div>
		</div>
	<?php } ?>

    <?php $tservices_unique = array(); ?>
	<?php foreach($tservices as $key=>$tservice) { ?>

		<?php
        if (in_array($tservice['id'], $tservices_unique)) {
            continue;
        }
        $tservices_unique[] = $tservice['id'];
        
		$user = $tservice['user'];
		$user_url = sprintf('/users/%s', $user['login']);
		$tservice_url = sprintf('/tu/%d/%s.html', $tservice['id'], tservices_helper::translit($tservice['title']));
        $tservice_title = LenghtFormatEx(reformat($tservice['title'], 20, 0, 1),80);
		$avatar_url = tservices_helper::photo_src($user['photo'], $user['login']);

		$hasVideo = false;
        
        $alt = $tservice_title;
        $title = sprintf("”слуги фрилансера %s: %s", $user['login'], $tservice_title);
		if ($hasVideo)
		{
			$video = current($tservice['videos']);
			$video_thumbnail_url = tservices_helper::setProtocol($video['image']);
			$thumbnail200x150 = '<img width="200" height="150" class="b-pic" src="'.$video_thumbnail_url.'" alt="'.$alt.'" title="'.$title.'">';
		} elseif(!empty($tservice['file']))
		{
			$image_url = tservices_helper::image_src($tservice['file'],$user['login']);
			$thumbnail200x150 = '<img width="200" height="150" class="b-pic" src="'.$image_url.'" alt="'.$alt.'" title="'.$title.'">';
		} else
		{
			$thumbnail200x150 = '<div class="b-pic b-pic_no_img b-pic_w200_h150 b-pic_bg_f2"></div>';
		}
                
        $hasVideo = !empty($tservice['videos']) && count($tservice['videos']);
                
        $sold_count = isset($tservice['count_sold']) ? $tservice['count_sold'] : $tservice['total_feedbacks']; // ѕока сфинск не считает все покупки, будем брать отзывы. #0026584
                
        $is_owner = $tservice['user_id'] == $uid;
        
        $hide_block = (bool)$bind_teaser && count($tservices_unique) >= $limit;
	?>

<?php if ($tservice['is_binded']): ?>
    <div class="i-pic i-pic_port i-pic_width_225 i-pic_margbot_30<?php if($hide_block): ?><?=' b-layout_hide'?><?php endif;?>">
        <div class="b-pay-tu b-pay-tu_payed<?php if($is_owner):?>-my<?php endif; ?>">
            <div class="b-pay-tu__inner">
<?php endif; ?>
    
    <div class="i-pic i-pic_port i-pic_port_z-index_inherit i-pic_pad_10 i-pic_height_265<?php if (!$tservice['is_binded']):?> i-pic_margbot_30 i-pic_bord_green_hover<?php if($hide_block): ?><?=' b-layout_hide'?><?php endif;?><?php endif; ?>">
        <div class="b-layout b-layout_relative">
            <a href="<?=$tservice_url?>" class="b-pic__lnk b-pic__lnk_relative">
                <?php if($hasVideo) { ?><div class="b-icon b-icon__play b-icon_absolute b-icon_bot_4 b-icon_left_4"></div><?php } ?>
                <?=$thumbnail200x150?>
            </a>
            <a class="b-pic__price-box b-pic__price-box_pay b-pic__price-box b-pic__price-box_noline" href="javascript:void(0);" data-url="<?=$tservice_url?>" onclick="TServices_Catalog.orderNow(this);"><?=tservices_helper::cost_format($tservice['price'],true)?>			
			<?php if ($sold_count!=0) { ?>
              <span title=" оличество продаж услуги"><span class="b-icon b-icon__tu2 b-icon_top_2"></span> <?=number_format($sold_count, 0, '', ' ')?></span>
            <?php } ?>
            </a>
        </div>
		<div class="b-layout__txt b-layout__txt_padtop_10 b-layout_overflow_hidden">
			<a href="<?=$tservice_url?>" class="b-layout__link b-layout__link_no-decorat b-layout__link_color_000 b-layout__link_inline-block"><?=LenghtFormatEx(reformat($tservice['title'], 20, 0, 1),80)?></a>
		</div>
			<div class="b-user b-user_padtop_10">
                <?php $fullname = view_fullname($user, true); ?>
				<a title="<?=$fullname?>" href="<?=$user_url?>/tu/" class="b-user__link b-user__link_color_ec6706">
                    <img width="15" height="15" class="b-user__pic b-user__pic_15" src="<?=$avatar_url?>" alt="<?=$fullname?>">
                    <?=$fullname?></a>
                <span class="b-user_nowrap">
                    <a title="<?=$fullname?>" href="<?=$user_url?>/tu/" class="b-user__link b-user__link_color_ec6706">[<?=$user['login']?>]</a><?=view_user_label($user)?>
                </span> 
			</div>
<?php if($is_adm): ?>
            <span class="b-txt b-txt_fs_14 b-txt_color_000">
                <?php echo sprintf('%s',$tservice['payed_tax']); ?>
            </span>
<?php endif; ?>   
	</div>
                
<?php if ($tservice['is_binded']): ?>
            <?php if($is_owner):?>
                <?php $this->renderClip('bind_links_'.$tservice['id']) ?>
            <?php endif; ?>
            </div>
            <span class="b-pay-tu__mpin"></span>
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
			// удал€ем параметр 'page=1' у первой страницы дл€ более чистого url
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
