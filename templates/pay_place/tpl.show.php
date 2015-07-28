<?php 

if (isset($is_show_tizer) && $is_show_tizer == true): 
    
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/quick_payment/quickPaymentPopupCarusel.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");

        $popupId = quickPaymentPopupCarusel::getInstance()->getPopupId();
        
        $payPlace = new pay_place($catalog);
        $payPlaceUserRequest = $payPlace->getUserRequest($uid);
        $pos = array_search($uid, $pp_uids);
        
?>
        <li id="carusel_tizer" class="b-carusel__item">
             <div class="b-pay-tu b-pay-tu_car">
               <a id="carusel_tizer_close" class="b-pay-tu__close" href="javascript:void(0);"></a>
               <a class="b-pay-tu__link b-pay-tu__link_color_6db335" data-popup="<?=$popupId?>" href="javascript:void(0);">
                   <?php if((@$payPlaceUserRequest['num'] == 0) && ($pos === false || $pos >= 5)): ?>
                   <span class="b-pay-tu__decor">Добавьте рекламу своих услуг</span><br/>и заявите о себе за <?=pay_place::getPrice()?> руб.
                   <?php elseif(($payPlaceUserRequest['num'] == 0) && ($pos < 5)): ?>
                   <span class="b-pay-tu__decor">Купите несколько объявлений</span><br/>
                   размещайтесь автоматически
                   <?php else: ?>
                   <span class="b-pay-tu__txt_fontsize_12">
                       <?php
                            $next_date = strtotime($payPlaceUserRequest['next_date_published']);
                       ?>
                       Осталось <?=$payPlaceUserRequest['num']?> <?=ending($payPlaceUserRequest['num'], 'размещение', 'размещения', 'размещений')?> 
                       <span class="b-pay-tu__hidden">(<span class="b-pay-tu__decor">добавить</span>)</span><br/>ближайшее будет в <?=date('H:i',$next_date)?>
                   </span>
                   <?php endif; ?>
               </a>  
            </div>    
        </li>
<?php 

endif;
    
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
$stop_words = new stop_words( hasPermissions('users') );

if (is_array($ppAds)) {
    foreach($ppAds as $ppAd) {
        $ppAd['ad_img_file_name'] = $ppAd['photo'];
        $adUid = $ppAd['uid'];
        $adLogin = $toppay_usr[$adUid]['login'];
        $adLink = '/users/' . $toppay_usr[$adUid]['login'] . '/?f=6&stamp=' . $_SESSION['stamp'];
        $cls = "b-carusel__pic";
        if ($ppAd['ad_img_file_name'] && $ppAd['ad_img_file_name']!='/images/temp/small-pic.gif') {
            $adImg = '<img width=50 height=50 src="' . WDCPREFIX . '/users/' . $adLogin . '/foto/' . $ppAd['ad_img_file_name'] . '" alt="' . $adLogin . '" class="' . $cls . '" border="0" />';
        } else {
            $adImg = '<img width=50 height=50 src="' . WDCPREFIX . '/images/user-default-small.png" class="' . $cls . '" border="0" />';
        }
        if (is_array($pp_h[$adUid])) {
            $adHeader = $ppAd['ad_header'] ? $ppAd['ad_header'] : $pp_h[$adUid]['title'];
            $adHeaderDots = strlen(html_entity_decode($adHeader, ENT_QUOTES)) > 22;
            $adHeader =  htmlentities ( substr(html_entity_decode($adHeader, ENT_QUOTES), 0, 22), ENT_QUOTES, 'CP1251') ;
            $adText = $ppAd['ad_text'] ? $ppAd['ad_text'] : $pp_h[$adUid]['descr'];
            $adText = $pp_h[$adUid]['on_moder'] ? $stop_words->replace($adText) : $adText;
            $adText = reformat2($adText, 22, 1, 1);
        }
        
    ?>
        <li class="b-carusel__item">
            <? if (is_array($pp_h[$adUid])) { ?>
                <a class="b-carusel__piclink" href="<?= $adLink ?>" onClick="<?=$yaM?>">
                    <?= $adImg ?>
                    <span class="b-carusel__title"><?= $adHeader ?><? $adHeaderDots ? '...' : '' ?></span>
                </a>
                <p class="b-carusel__txt b-carusel__txt_padtop_5"><?= $adText ?></p>
            <? }else{ ?>
                <a class="b-carusel__piclink" href="<?= $adLink ?>"  onClick="<?=$yaM?>"><?= $adImg ?></a>
                <h3 class="b-carusel__title">Нет данных</h3>
                <p class="b-carusel__txt">Нет данных</p>
            <? } ?>
        </li>
    <? }
} else { ?>
	<li class="b-carusel__item">
		<p class="b-carusel__txt b-carusel__txt_padtop_5">Нет пользователей</p>
	</li>
<? } ?>