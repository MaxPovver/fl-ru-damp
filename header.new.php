<?
$date = date("Ymd");
$banner_promo_show = false;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banner_promo.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
$ban_promo = new banner_promo();
$ban_promo->setType($ban_promo->getActiveBanner(), 1);
if(($no_banner!=1 || $no_banner!=true) && $date>=date('Ymd', strtotime($ban_promo->info['from_date'])) && $date<=date('Ymd', strtotime($ban_promo->info['to_date']))) {
    $banner_promo_show = false;
    $banner_promo_type_ban =  $ban_promo->info['type_ban'];
    $banner_promo_img = $ban_promo->info['name_img'];
    $banner_promo_code = $ban_promo->info['code_text'];
    $banner_promo_image_style = ($ban_promo->info['img_style']!=""?'style="'.$ban_promo->info['img_style'].'"':'');
    $banner_promo_title       = htmlspecialchars(stripslashes($ban_promo->info['img_title']));
    $banner_promo_text        = htmlspecialchars(stripslashes($ban_promo->info['linktext']));
    $banner_promo_link_style  = htmlspecialchars(stripslashes($ban_promo->info['link_style']));
    $banner_promo_type        = intval($ban_promo->info['id']);
    $banner_link              = $ban_promo->info['banner_link'];
    $ban_promo->writeViewStat();
    if($ban_promo->info['page_target'] == '0|0' || $ban_promo->info['page_target'] == $g_page_id) {
        $banner_promo_show = true;
    }
}

// Типовые услуги - главная страница для неавторизованных и заказчиков
if (get_uid(false) == 0 || is_emp()) {
    $projects_url = '/projects/';
    $projects_active = $grey_main && $kind != 8 && $kind != 2;
    $tservice_url = '/';
    $tservice_active = $_SERVER['REQUEST_URI'] == '/' || preg_match("#^/tu/#", $_SERVER["REQUEST_URI"], $m);
} else {
    $projects_url = '/';
    $projects_active = $grey_main && $kind != 8 && $kind != 2;
    $tservice_url = '/tu/';
    $tservice_active = preg_match("#^/tu/#", $_SERVER["REQUEST_URI"], $m);
}
$konkurs_active = $grey_main && $kind == 2;
?>
<script type="text/javascript">
function writeClickStat(id){
    var link = "/a_promo.php?type=" + id;
    var req = new Request({
		url: link, 
		onSuccess: onBLinkSuccess,
		onFailure: onBLinkFail
	});	
	req.post(/*"u_token_key=" + _TOKEN_KEY*/);
	return false;
}
function onBLinkSuccess() {}
function onBLinkFail() {}

// обработка нажатия клавиши Enter в поле ввода поискового запроса (зеленый поиск и поиск на странице /search/)
window.addEvent('domready', 
function() {
    var searchInput;
    searchInput = $('search_across');
    if (!searchInput) {
        // для страницы /search/
        searchInput = $('search_request');
    }
    if (searchInput) {
        $('search_across').addEvent('keyup', function(e) {
            if(e.key == 'enter') $('form-search').submit();
        }); 
    }
});
</script>
    <table class="b-layout__table b-layout__table_width_full">
       <tr class="b-layout__tr">
          <td class="b-layout__td b-layout__td_width_90">
            <? $partDay = ( date('Ymd') == '20130308' || date('Ymd') == '20130307'); ?>
            <a class="b-header__logo" title="Fl.ru" href="/"></a>
          </td>
          <td class="b-layout__td">
            <div class="b-menu b-menu_head b-menu_relative b-menu_zindex_6" data-accordion="true" data-accordion-descriptor="nav" data-accordion-opener="true">
                <ul class="b-menu__list">
                    <? if ($banner_promo_show == true) { ?>
                        <? ob_start(); ?>                  
                        <? if($banner_promo_type_ban == 'image') { ?>
                            <?php if (trim($banner_promo_img) == '' && trim($banner_promo_text) != '') {?>
                            <li class="b-menu__banner">
                                <a target="_blank" onclick="javascript:writeClickStat(<?=$banner_promo_type?>)" href="<?=$banner_link?>" class="b-menu__link-banner" <? if (trim($banner_promo_link_style) != '') {?>style="<?php print $banner_promo_link_style?>"<? }?>><?php print $banner_promo_text?></a>
                            </li>
                            <?php } elseif (trim($banner_promo_img) != '' && trim($banner_promo_text) == '') {?>
                            <li class="b-menu__banner">
                                <a target="_blank" onclick="javascript:writeClickStat(<?=$banner_promo_type?>)" href="<?=$banner_link?>" class="b-menu__link-pic" <? if (trim($banner_promo_link_style) != '') {?>style="<?php print $banner_promo_link_style?>"<? }?>>
                                    <img <?php if( trim($banner_promo_title) != '') {?>alt="<?php print $banner_promo_title?>" <?php print $banner_promo_image_style?> title="<?php print $banner_promo_title?>" <?php }?>src="<?php print $banner_promo_img ?>" class="b-menu__pic" />
                                    </a>
                            </li>
                            <?php } elseif (trim($banner_promo_img) != '' && trim($banner_promo_text) != '') {?>
                            <li class="b-menu__banner">
                                <a target="_blank" onclick="javascript:writeClickStat(<?=$banner_promo_type?>)" href="<?=$banner_link?>" class="b-menu__link-pic">
                                    <img src="<?php print $banner_promo_img ?>" <?php print $banner_promo_image_style?> class="b-menu__pic" <?php if( trim($banner_promo_title) != '') {?> alt="<?php print $banner_promo_title?>" title="<?php print $banner_promo_title?>" <?php }?> />
                                </a>
                                <a target="_blank" onclick="javascript:writeClickStat(<?=$banner_promo_type?>)" href="<?=$banner_link?>" class="b-menu__link-banner" <? if (trim($banner_promo_link_style) != '') {?>style="<?php print $banner_promo_link_style?>" <?php }?>><?php print $banner_promo_text?></a>
                            </li>
                            <?php }?>
                        <? } elseif($banner_promo_type_ban == 'code') {//if?>
                            <li class="b-menu__banner">
                                <?= $banner_promo_code;?>
                            </li>
                        <? }//else?>
                        <? $bhtml = clearTextForJS(ob_get_clean());?><script type="text/javascript">document.write('<?=$bhtml?>');</script>
                    <? } ?>
                    <li class="b-menu__item b-menu__item_first <?php if ($tservice_active) {?>b-menu__item_active<?php }?>">
                        <a class="b-menu__link" href="<?php echo $tservice_url ?>">Услуги</a>
                    </li>
                    <li
                     class="b-menu__item <?= $projects_active ? "b-menu__item_active":""?>">
                        <?php if($projects_active) {?>
                        <a class="b-menu__link" href="<?php echo $projects_url ?>">Работа</a>
                        <?php } else { //if?>
                        <a class="b-menu__link" href="<?php echo $projects_url ?>">Работа</a>
                        <?php }//else?>
                    </li><li
                     class="b-menu__item <?= $konkurs_active ? "b-menu__item_active":""?>">
                        <?php if($konkurs_active) {?>
                        <a class="b-menu__link" href="/konkurs/">Конкурсы</a>
                        <?php } else { //if?>
                        <a class="b-menu__link" href="/konkurs/">Конкурсы</a>
                        <?php }//else?>
                    </li><li
                     class="b-menu__item <?= $grey_catalog ? "b-menu__item_active":""?>">
                        <?php if($grey_catalog) {?>
                        <a class="b-menu__link" href="/freelancers/">Фрилансеры</a>
                        <?php } else { //if?>
                        <a class="b-menu__link" href="/freelancers/">Фрилансеры</a>
                        <?php }//else?>
                    </li>
                        <? if(BLOGS_CLOSED == false) { ?>
                    <li class="b-menu__item <?= $grey_commune?"b-menu__item_active":""?>">
                            <?php if($grey_blogs) {?>
                            <span class="b-menu__b1"><span class="b-menu__b2"><a class="b-menu__link" href="/blogs/">Блоги</a></span></span>
                            <?php } else { //if?>
                            <a class="b-menu__link" href="/blogs/">Блоги</a>
                            <?php }//else?>
                    </li>
                        <? }?>
<?php /*
                       <li class="b-menu__item <?php if (preg_match("#/promo/bezopasnaya-sdelka/?$#", $_SERVER["REQUEST_URI"], $m)) {?>b-menu__item_active<?php }?>">
                          <?php if (preg_match("#/promo/bezopasnaya-sdelka/?$#", $_SERVER["REQUEST_URI"], $m)) {?>
                          <a  class="b-menu__link" href="/promo/bezopasnaya-sdelka/">Безопасная Сделка</a>
                          <? } else {//if?>
                          <a  class="b-menu__link" href="/promo/bezopasnaya-sdelka/">Безопасная Сделка</a>
                          <? }//else?>
                       </li>
																				
                       <li class="b-menu__item <?php if (preg_match("#/promo/verification/?$#", $_SERVER["REQUEST_URI"], $m)) {?>b-menu__item_active<?php }?>">
																							<?php if (preg_match("#/promo/verification/?$#", $_SERVER["REQUEST_URI"], $m)) {?>
                          <a  class="b-menu__link" href="/promo/verification/">Верификация</a>
                          <? } else {//if?>
                          <a  class="b-menu__link" href="/promo/verification/">Верификация</a>
                          <? }//else?>
                       </li>
*/ ?>                       
                        <li class="b-menu__item <?php if (preg_match("#/commune/?$#", $_SERVER["REQUEST_URI"], $m)) {?>b-menu__item_active<?php }?>">
                            <?php if (preg_match("#/commune/?$#", $_SERVER["REQUEST_URI"], $m)): ?>
                                <a  class="b-menu__link" href="/commune/">Сообщества</a>
                            <?php else: //if?>
                                <a  class="b-menu__link" href="/commune/">Сообщества</a>
                            <?php endif; //else?>
                       </li>

                       <li class="b-menu__item">
                          <a  class="b-menu__link" target="_blank" href="https://feedback.fl.ru/">Помощь</a>
                       </li>
                        
<?php /*
                      <li class="b-menu__item <?php if (preg_match("#/partners/?$#", $_SERVER["REQUEST_URI"], $m)) {?>b-menu__item_active<?php }?>">
                            <?php if (preg_match("#/partners/?$#", $_SERVER["REQUEST_URI"], $m)): ?>
                                <a  class="b-menu__link" href="/partners/">Партнерская программа</a>
                            <?php else: //if?>
                                <a  class="b-menu__link" href="/partners/">Партнерская программа</a>
                            <?php endif; //else?>
                      </li>                       
*/ ?>                       
                       <li class="b-menu__item b-page__ipad b-page__iphone">
                          <a  class="b-menu__link" href="/?full_site_version=1">Полная версия сайта</a>
                       </li>
                </ul>
            </div><!--b-menu-->
            
            <?php if(!$grey_search) { ?>
            
            <? if(!is_emp() && get_uid(false)) { //if 1.2 0014574?>	 
            <script type="text/javascript">	 
            kword = search_kwords;	 
            </script>	 
            <? } ?>
            
              <div class="b-fon b-fon_bg_72bc4e b-fon__border_radius_3 b-fon_padtb_2">
                  <form id="form-search" action="/search/">
                      <? if (defined('ENCODE_UTF8') && ENCODE_UTF8 === true) { ?>
                          <input type="hidden" name="encode" value="utf8" />
                      <? } ?>
                      <div class="b-search">
                          <table class="b-search__table" cellspacing="0" cellpadding="0" border="0">
                              <tbody>
                                  <tr class="b-search__tr">
                                      <td class="b-search__input">
                                              <div id="body_search_across" class="b-input b-input_height_23 b-input_border_none">
                                                  <input id="search_across" class="b-input__text" type="text" name="search_string" placeholder="Поиск<?= is_emp() ? " исполнителя" : (get_uid(false)? " проекта" : " исполнителя")?>. Например, <?=kwords::getRandomSearchHint( (get_uid(false) ? 'users' : (is_emp(get_uid(false)) ? 'projects' : 'users')) )?>" autocomplete="off" />
                                              </div>
                                      </td>
                                      <td class="b-search__button">
                                          <a class="b-button b-button_rectangle_color_transparent" href="/search/" onclick="<? if(is_emp()) {?>_gaq.push(['_trackEvent', 'User', 'Employer', 'button_search']); ga('send', 'event', 'Employer', 'button_search');<? } else { ?>_gaq.push(['_trackEvent', 'User', 'Freelance', 'button_search']); ga('send', 'event', 'Freelance', 'button_search');<? } ?> $('form-search').submit(); return false;">
                                              <span class="b-button__b1">
                                                  <span class="b-button__b2">
                                                      <span class="b-button__txt">Найти</span>
                                                  </span>
                                              </span>
                                          </a>
                                      </td>
                                  </tr>
                              </tbody>
                          </table>
        <input type="hidden" name="action" value="search" />
                      </div>
                  </form>
              </div><!--b-fon -->	
            
            <?php } else { //if?>
            <h1 class="b-page__title b-page__title_padnull">Поиск по сайту</h1>
            <?php } //else?>
          </td>
       </tr>
    </table>
<!--b-header -->