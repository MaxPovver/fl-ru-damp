<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");

if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
if (!$xajax) {
    if ( $_GET['p'] == 'opinion' ) {
        if (strpos($_SERVER['REQUEST_URI'], 'from=norisk')) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.common.php");
        } else {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/opinions.common.php");
        }
    } elseif ( $_GET['p'] == 'tu-orders' ){ 
        require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/tservices_orders.common.php");
    } else {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/commune.common.php");
    }
    $xajax->printJavascript('/xajax/');
}
?>

        <? /* if($p_user->is_pro == 'f' && !is_emp($p_user->role)) {
               if (!is_emp() && $p_user->uid == $_SESSION['uid']) {
                    // 3 случайных проекта для ПРО
                    $pro_projects = projects::getProjectPromo($p_user->uid);
                    // какой был бы рейтинг
                    $proRating = round(rating::GetPredictionPRO($p_user->uid, 't', $p_user->is_verify), 2);
                    $profArray = professions::getProfessionsByUser2($p_user->uid, true);
                    if ($profArray && $profArray['prof']) {
                        $prof = professions::GetCatalogPosition($p_user->uid, $u_spec, $proRating, $profArray[0], true, true);
                        $profText = $prof['prof_name'];
                        $profPos = $prof['pos'];
                    }
               }
        ?>
        <div class="profile-advert">
            <? if (!is_emp() && $p_user->uid == $_SESSION['uid']) { ?>
            <div id="pro_advantage" class="b-promo b-promo_padbot_40">
               <h2 class="b-layout__title b-layout__title_pad_null"><a class="b-layout__link" href="/payed/ ">Купите аккаунт</a> <a class="b-layout__link" href="/payed/ "><span title="PRO" class="b-icon b-icon__spro b-icon__spro_f"></span></a></h2>
               <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">и получите:</div>
               
              <div class="b-promo">
                  <ul class="b-promo__list">
                          <li class="b-promo__item b-promo__item_fontsize_15"><div class="b-icon b-icon__plus b-icon_top_3 b-icon_margleft_-20"></div>неограниченные ответы на проекты;</li>
                          <li class="b-promo__item b-promo__item_fontsize_15"><div class="b-icon b-icon__plus b-icon_top_3 b-icon_margleft_-20"></div>размещение в каталоге в 5 категориях;</li>
                          <li class="b-promo__item b-promo__item_fontsize_15"><div class="b-icon b-icon__plus b-icon_top_3 b-icon_margleft_-20"></div>увеличение рейтинга на 20%.</li>
                  </ul>
              </div>               
               
            
            
                
            </div>
            <? } ?>
            
            <? = printBanner240(0, 0, $g_page_id)  ?>
            
        </div>
        <? } */ ?>
        <div class="page-profile">
            <?php include ($fpath . "header.php") ?>
            <?php include ($fpath . "usermenu.php") ?>
            <div class="blog-tabs">
                <?php if ($inner) include ($fpath . $inner); else print('&nbsp;') ?>
            </div>
        </div>
        <?php 
        /*
        <h2 class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_666 b-layout_top_100 b-layout__txt_padbot_10 b-layout__txt_weight_normal">
            <?php echo SeoTags::getInstance()->getFooterText() ?>
        </h2>
        */ 
        ?>


