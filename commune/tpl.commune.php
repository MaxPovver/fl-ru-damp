<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/commune.common.php");
$xajax->printJavascript('/xajax/');

global $id, $comm, $uid, $page, $om, $user_mod, $message_id, $request, $alert, $action, $cat;

$author_id = $uid;
$adminCnt = 0;

// Но нам нужно вычесть забаненные топики, для вывода "Всего X сообщений".
$bannedCount = 0;
if ($user_mod & commune::MOD_ADMIN) {
    $thCnt = $communeThemesCounts['count'];
} elseif ($user_mod & (commune::MOD_COMM_AUTHOR | commune::MOD_COMM_MANAGER)) { // если модераторы сообщества
    $thCnt = $communeThemesCounts['count'] - $communeThemesCounts['admin_hidden_count'];
} else {
    $thCnt = $communeThemesCounts['count'] - $communeThemesCounts['hidden_count'];
}



// Все админы (модераторы, упрявляторы).
if (!($admins = commune::GetMembers($id, commune::MEMBER_ADMIN | commune::JOIN_STATUS_ACCEPTED))) // Хотя модераторы всегда is_accepted.
    $admins = array();

foreach($admins as $admin) {
    if($admin['is_moderator'] == 't') $mod[$admin['user_id']] = true;
    if($admin['is_manager'] == 't') $man[$admin['user_id']] = true;
}
    
// Трое последних простых участников.
if (!($members = commune::GetMembers($id, commune::MEMBER_SIMPLE | commune::JOIN_STATUS_ACCEPTED, 0, 10)))
    $members = array();

// Темы сообщества.
if ((!$uid && $om == commune::OM_TH_MY)
        || !($topics = commune::GetTopMessages($id,
                ($om == commune::OM_TH_MY ? $uid : NULL), $uid, $user_mod, $om, ($page - 1) * commune::MAX_TOP_ON_PAGE, commune::MAX_TOP_ON_PAGE)))
    $topics = array();

// Стили закладок.
$bmCls = getBookmarksStyles(commune::OM_TH_COUNT, $om);

// Сколько участников (вместе с админами тут).
$mCnt = ($comm['a_count'] - $comm['w_count'] + 1) . ' участник' . getSymbolicName(($comm['a_count'] - $comm['w_count'] + 1), 'man'); // +1

//if ($thCnt = $themesCount - $bannedCount)
//$thCnt = $themesCount - $bannedCount;
if($om == commune::OM_TH_MY) {
    $thCnt = $themesCount;
}
$thCntS = ending($thCnt, 'пост', 'поста', 'постов');

$sort = $_COOKIE['commune_fav_order'] != "" ? $_COOKIE['commune_fav_order'] : "date";

$favs = commune::GetFavorites($uid, NULL, $sort, $comm['id']);

// ACL
$is_site_admin = (hasPermissions('communes'));
$is_comm_admin = $user_mod & (commune::MOD_COMM_ADMIN | commune::MOD_COMM_MODERATOR);
$is_author = $user_mod & (commune::MOD_COMM_AUTHOR);
$categories = commune::getCategories($id, hasPermissions('communes'));

/*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
$stop_words = new stop_words( hasPermissions("communes") );*/
?>
<script>
    var blockBoxContent = '';
    function __commDT(b,t,e,m,p,o,s,f)
    {
        if(warning(1)) {
            var tc=document.getElementById('idThCnt');
            xajax_DeleteTopic(b,t,e,m,p,o,s,f,(tc?tc.innerHTML:0));
        }
    }
</script>
<script type="text/javascript">

    var yt_link = false;

    function toggle_yt_link() {
        if (yt_link) {
            $('yt_link').style.display = 'none';
            yt_link = false;
        } else {
            $('yt_link').style.display = 'block';
            yt_link = true;
        }
        return false;
    }

    function toggle_settings() {
        var a=$('settings').style;
        if(a.display!='block') a.display='block';
        else a.display='none';
    }

    function toggle_pool() {
        if ($$('.poll-line')[0].style.display != 'none') {
            $$('.poll-line').setStyle('display', 'none');
            $$('.poll-st').setStyle('display', 'none');
        } else {
            $$('.poll-line').setStyle('display', '');
            $$('.poll-st').setStyle('display', '');
        }
    }

    function toggle_attach() {var a=$('attach').style;if(a.display!='block') a.display='block';else a.display='none';}

    function domReady( f ) {
        if ( domReady.done ) return f();

        if ( domReady.timer ) {
            domReady.ready.push( f );
        } else {
            if (window.addEventListener)
                window.addEventListener('load',isDOMReady, false);
            else if (window.attachEvent)
                window.attachEvent('onload',isDOMReady);

            domReady.ready = [ f ];
            domReady.timer = setInterval( isDOMReady, 13 );
        }
    }

    function isDOMReady(){
        if ( domReady.done ) return false;

        if ( document && document.getElementsByTagName && document.getElementById && document.body ) {
            clearInterval( domReady.timer );
            domReady.timer = null;

            for ( var i = 0; i < domReady.ready.length; i++ )
                domReady.ready[i]();

            domReady.ready = null;
            domReady.done = true;
        }
    }

    function goToAncor(name) {
        domReady(function(name) {
            return function() {
                var a = document.getElementsByTagName('A');
                for (var i = 0, len = a.length; i < len; i++) {
                    if (a[i].name == name) {
                        a[i].scrollIntoView(true);
                        break;
                    }
                }
            }
        }(name));
    }


    function maxChars(textarea, box, max) {
        if (typeof textarea == 'string') textarea = document.getElementById(textarea);
        if (typeof box == 'string') box = $(box);
        if (typeof textarea != 'object' || typeof box != 'object') return false;
        textarea.onchange = textarea.onkeyup = textarea.onkeydown = function() {
            if (textarea.value.length > max) {
                if(box.getElement('div div div span strong')) {
                    box.getElement('div div div span strong').set('html', 'Максимальная длина сообщения ' + max + ' символов!');
                } else {
                    box.innerHTML = 'Максимальная длина сообщения ' + max + ' символов!';
                }
                box.style.display = 'block';
                textarea.value = textarea.value.substr(0, max + 1);
            } else {
                if(box.getElement('div div div span strong')) {
                    box.getElement('div div div span strong').set('html', '&nbsp;');
                } else {
                    box.innerHTML = '&nbsp;';
                }
                box.style.display = 'none';
            }
        }
    }
    //-->
</script>
<script type="text/javascript">
    /*function hlSort(sort){
        $('sort_date').removeClass('active');
        $('sort_priority').removeClass('active');
        $('sort_abc').removeClass('active');
        $('sort_'+sort).addClass('active');
    }*/
</script>

<? $cur_user = new users(); $cur_user->GetUserByUID(get_uid(false)); ?>
<? if (!commune::isBannedCommune($user_mod) && !$comm['is_blocked'] && $user_mod & (commune::MOD_COMM_AUTHOR | commune::MOD_ADMIN | commune::MOD_MODER | commune::MOD_COMM_ACCEPTED | commune::MOD_COMM_ADMIN | commune::MOD_COMM_MODERATOR)) { ?>
<?// seo_start();?>
<? if( ($comm['id']!=5100 && $comm['id']!=1008) || ($cur_user->is_team=='t' && ($comm['id']==5100 || $comm['id']==1008)) ) { ?>
<a href="?site=Newtopic" class="b-button b-button_flat b-button_flat_green b-button_float_right b-button_margtop_15 __ga__commune__new_post_<?=is_emp()?'emp':'frl'?>" id="new_post_msg">Написать пост</a>
<?//= seo_end();?>
<? } ?>
<? } ?>
<? include ('in_out_dialog.php');?>

<?php

$aGroup = commune::getGroupById( $comm['group_id'] );
$sGroup = $aGroup['name'];
$crumbs = array();
$crumbs[] = array("title"=>"Сообщества", "url"=>"/commune/");
if($comm['id'] != commune::COMMUNE_BLOGS_ID) $crumbs[] = array("title"=>$sGroup, "url"=>getFriendlyURL('commune_group', $comm['group_id']));
if($comm['id'] == commune::COMMUNE_BLOGS_ID) {
    $category_id = __paramInit('int', 'cat', 'cat');
    $category = commune::getCategory($category_id);
    $crumbs[] = array("title"=>$comm['name'], "url"=> $category_id > 0 ? getFriendlyURL('commune_commune', $comm['id']) : "");
    if($category_id) $crumbs[] = array("title"=>$category['name'], "url"=> "");
}


?>
<div class="b-community-article">
<?=getCrumbs($crumbs, $comm['id'] != commune::COMMUNE_BLOGS_ID ? "commune" : "new_blogs")?>
<h1 class="b-page__title"><?= $comm['name']?></h1>

<div class="b-menu b-menu_line b-menu_relative b-menu_padbot_20">
    <ul class="b-menu__list b-menu__list_padleft_28ps" data-menu="true" data-menu-descriptor="community-discussions">
            <? seo_start();?>
            <li class="b-menu__item <?=($bmCls[commune::OM_TH_NEW] ? 'b-menu__item_active' : '')?>" <?=($bmCls[commune::OM_TH_NEW] ? 'data-menu-opener="true" data-menu-descriptor="community-discussions"' : '')?>>        
                <a href="?om=<?=__paramInit("int", "cat")?"&cat=".__paramInit("int", "cat"):'' ?>" class="b-menu__link"><span class="b-menu__b1">Новые посты</span></a>
            </li>
            <li class="b-menu__item <?=($bmCls[commune::OM_TH_POPULAR] ? 'b-menu__item_active' : '')?>" <?=($bmCls[commune::OM_TH_POPULAR] ? 'data-menu-opener="true" data-menu-descriptor="community-discussions"' : '')?>>        
                <a href="?om=<?= commune::OM_TH_POPULAR ?><?=__paramInit("int", "cat")?"&cat=".__paramInit("int", "cat"):'' ?>" class="b-menu__link"><span class="b-menu__b1">Популярные</span></a>
            </li>
            <li class="b-menu__item <?=($bmCls[commune::OM_TH_ACTUAL] ? 'b-menu__item_active' : '')?>" <?=($bmCls[commune::OM_TH_ACTUAL] ? 'data-menu-opener="true" data-menu-descriptor="community-discussions"' : '')?>>        
                <a href="?om=<?= commune::OM_TH_ACTUAL ?><?=__paramInit("int", "cat")?"&cat=".__paramInit("int", "cat"):'' ?>" class="b-menu__link"><span class="b-menu__b1">Актуальные</span></a>
            </li>
            <? if (get_uid(0)) { ?>
            <li class="b-menu__item <?=($bmCls[commune::OM_TH_MY] ? 'b-menu__item_active' : '')?>" <?=($bmCls[commune::OM_TH_MY] ? 'data-menu-opener="true" data-menu-descriptor="community-discussions"' : '')?>>        
                <a href="?om=<?= commune::OM_TH_MY ?><?=__paramInit("int", "cat")?"&cat=".__paramInit("int", "cat"):'' ?>" class="b-menu__link"><span class="b-menu__b1">Мои</span></a>
            </li>
            <? } ?>
            <li class="b-menu__item b-menu__item_promo b-page__desktop"><?php require_once($_SERVER['DOCUMENT_ROOT'] . "/banner_promo.php"); ?></li>
            <?= seo_end();?>
    </ul>
</div>


<div class="b-layout ">
    <div class="b-layout__left b-layout__left_width_25ps b-layout__left_float_left">
            <? seo_start();?>
            <?= __commPrntImage($comm, 'author_', 'b-layout__link', 'b-layout__pic b-layout__pic_center') ?>
            <?= seo_end();?>
            <? if($comm['id']!=5100) { ?>
            <div class="b-voting b-voting_center b-voting_padtb_10">
                <div id="idCommRating_<?= $comm['id'] ?>">
                    <?= __commPrntRating($comm, get_uid(false)) ?>
                </div>
            </div>
            <? } ?>
            <div class="b-free-share b-free-share_padbot_10">
                <div class="b-free-share__body b-free-share__body_center ">
                    <?= ViewSocialButtons('small_block', $comm['name'])?>
                </div>
            </div>
            <? if ($uid = get_uid(false)) { ?>
            <? // кнопка ВСУПИТЬ В СООБЩЕСТВО ?>
            <div id="join_btn_<?= $comm['id'] ?>">
                <? if ($uid == $comm['author_uid']) { ?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 <?= commune::isBannedCommune($user_mod)?"b-layout__txt_padbot_20":""?>">
                        Вы создатель сообщества
                    </div>
                <? } else {?>
                    <? if ($comm['current_user_join_status'] == commune::JOIN_STATUS_NOT || $comm['current_user_join_status'] == commune::JOIN_STATUS_DELETED) { ?>
                        <?= __commPrntJoinButton($comm, $uid, null, 2, "b-button b-button_flat b-button_flat_green b-button_block b-button_margbot_20", "b-button__txt b-button__txt_center"); ?>    
                    <? } elseif(!commune::isBannedCommune($user_mod)) { ?>
                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_overflow_hidden <?= commune::isBannedCommune($user_mod)?"b-layout__txt_padbot_20":""?>">
                            <a onclick="xajax_OutCommune(<?= $comm["id"] ?>, 1); return false;" class="b-layout__link b-layout__link_float_right b-layout__link_dot_c10600 b-layout__link_fontsize_11" href="javascript:void(0)">Покинуть</a>
                            Вы член сообщества
                        </div>
                    <? } ?>
                <? } ?>
            </div>
            <?php if(commune::isBannedCommune($user_mod)) { ?>
            <div class="b-fon b-fon_padbot_20">
                <div class="b-fon__body b-fon__body_pad_5 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
                    <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-20"></span>Вы заблокированы в сообществе. 
                    <?php if($comm['id'] == commune::COMMUNE_BLOGS_ID) { ?>
                    <br><a class="b-layout__link" href="https://feedback.free-lance.ru" target="_blank">Обратиться в службу поддержки</a>
                    <?php }//if?>
                </div>
            </div>
            <?php }//if?>
            <? // кнопка ПОДПИСАТЬСЯ/ОТПИСАТЬСЯ ?>
            <div id="commSubscrButton_<?= $comm['id'] ?>">
                <?//= __commPrntSubmitButton($comm, $uid, null, false, 'b-button b-button_flat b-button_flat_green b-button_block b-button_margbot_20', 'b-button__txt b-button__txt_center') ?>
                <? if (!(commune::isUserBanned($comm['id'],$uid)) && !($comm['current_user_join_status'] != commune::JOIN_STATUS_ACCEPTED && $comm['author_uid']!=$uid)) {
                    if (commune::isCommuneSubscribed($comm['id'],$uid)) { ?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20 ">
                        <a onclick="xajax_SubscribeCommune(<?= $comm['id'] ?>, false, 1, true);" class="b-layout__link b-layout__link_float_right b-layout__link_dot_c10600 b-layout__link_fontsize_11" href="javascript:void(0)">Отписаться</a>
                        и подписаны на новые посты
                    </div> 
                    <? } else { ?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20 ">
                        <a onclick="xajax_SubscribeCommune(<?= $comm['id'] ?>, true, 1, true);" class="b-layout__link b-layout__link_float_right b-layout__link_dot_c10600 b-layout__link_fontsize_11" href="javascript:void(0)">Подписаться</a>
                        и не подписаны на новые посты
                    </div>
                    <? } ?>
                <? } ?>
            </div>
            <? } ?>

            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20 ">
                <?= commune::GetJoinAccessStr($comm['restrict_type'], TRUE) ?><br>
                <?= $mCnt ?><br>
                <?= $thCnt ?> <?= $thCntS ?><br>
                <?= __commPrntAge($comm) ?><br>
            </div>
            
            <div class="b-menu b-menu_vertical b-menu_padbot_20">
                <ul class="b-menu__list">
                    <?php $href = get_uid() ? "?site=Members" : "?site=Members";//'/fbd.php'; ?>
                    <li class="b-menu__item b-menu__item_padbot_5">
                        <a href="<?php echo $href; ?>" class="b-menu__link b-menu__link_fontsize_11 b-menu__link_color_c10600">Все</a>
                    </li>
                    
                    <?php if($is_author || hasPermissions('communes')){ ?>
                        <li class="b-menu__item b-menu__item_padbot_5">
                            <a href="?site=Edit" class="b-menu__link b-menu__link_fontsize_11 b-menu__link_color_c10600">Настройки сообщества</a>
                        </li>
                    <?php } ?>
                    
                    <? if(isset($man[$_SESSION['uid']]) || $is_author || hasPermissions('communes')){
                        $href = "?site=Admin.members"; ?>
                        <li class="b-menu__item b-menu__item_padbot_5">
                            <a href="<?= $href; ?>" class="b-menu__link b-menu__link_fontsize_11 b-menu__link_color_c10600">Управление людьми</a>
                        </li>
                    <?php } ?>                    

                    <?php if(hasPermissions('communes')){ ?>                    
                        <?php if($comm['is_blocked'] != 't'){ ?>
                            <li class="b-menu__item b-menu__item_padbot_5">
                                <span id="blocked-button-<?= $id;?>">
                                    <a class="b-menu__link b-menu__link_fontsize_11 b-menu__link_color_c10600" href="javascript:void(0);" onclick="banned.blockedCommune(<?=$id?>)">Заблокировать сообщество</a>
                                </span>
                            </li>
                        <?php } else{ ?>
                            <li class="b-menu__item b-menu__item_padbot_5">
                                <span id="blocked-button-<?= $id;?>">
                                    <a class="b-menu__link b-menu__link_fontsize_11 b-menu__link_color_c10600" href="javascript:void(0);" onclick="banned.unblockedCommune(<?=$id?>)">Разблокировать сообщество</a>
                                </span>
                            </li>
                        <?php } ?>
                        <div id="commune-reason-<?=$id?>" class="block-box" style="display: none; overflow:hidden;">&nbsp;</div>                    
                    <?php } ?>
                </ul>
            </div>
            
            <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_lineheight_18">
                <?$comm['descr'] = str_replace("\r\n", "\r", $comm['descr']);?>
                <?= reformat2($comm['descr'], 25, 1) ?>
            </div>
            
            <? // разделы ?>
            <?php //include_once(dirname(__FILE__).'/categories.php');?>
            <div class="b-menu b-menu_padbot_20 b-menu_vertical">
                <h3 class="b-menu__title b-menu__title_bold b-menu__title_padbot_10">Разделы</h3>
                
                
                <? if (!$comm['is_blocked'] && $user_mod & (commune::MOD_COMM_AUTHOR | commune::MOD_COMM_ADMIN | commune::MOD_COMM_MODERATOR) || hasGroupPermissions('administrator')) { ?>
                    <ul id="commune_categories_list" class="b-menu__list">
                        <?= __commPrintCategoriesList($comm['id'], $om, intval($cat), intval($page)) ?>
                    </ul>
                   
                    <span id="categories_edit_edit" class="b-layout__txt b-layout__txt_padright_10">
                        <a id="categories_edit" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_dot_c10600" href="javascript:void()">Редактировать</a>
                    </span>
                    <div id="categories_edit_add" class="b-menu__txt b-menu__txt_inline i-shadow">
                        <a id="add_category" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_dot_c10600 b-menu__add" href="javascript:void()">Добавить</a>
                        <div id="add_category_block" class="b-shadow b-shadow_width_220 b-shadow_top_15 b-shadow_left_-77 b-shadow_hide">
                            <div class="b-shadow__right">
                                <div class="b-shadow__left">
                                    <div class="b-shadow__top">
                                        <div class="b-shadow__bottom">
                                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                                <form id="commune_form_add_category" action="">
                                                    <input type="hidden" name="commune_id" value="<?= $comm['id'] ?>" />
                                                    <input type="hidden" name="om" value="<?= $om ?>" />
                                                    <div class="b-input b-input_margbot_10 b-input_height_24">
                                                        <input id="commune_fld_add_category_name" rel="<?= commune::MAX_CATEGORY_NAME_SIZE?>" name="commune_fld_add_category_name" type="text" size="80" class="b-input__text b-input__text_color_81">
                                                        <textarea  class="b-textarea__textarea b-textarea__textarea__height_70" cols="" rows=""><?= hyphen_words($category['name']) ?></textarea>
                                                    </div>
                                                    <div class="b-check b-check_padbot_10">
                                                        <input id="commune_fld_add_category_only_for_admin" name="commune_fld_add_category_only_for_admin" class="b-check__input" type="checkbox" value="1" />
                                                        <label for="b-check1" class="b-check__label b-check__label_fontsize_13">Публикации только<br>администрации</label>
                                                    </div>
                                                    <a id="category_add_submit" href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green b-button_block">Создать раздел</a>															
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <span id="close_add_category" class="b-shadow__icon b-shadow__icon_close"></span>
                            <span class="b-shadow__icon b-shadow__icon_nosik"></span>								
                        </div>							
                    </div>
                    <span id="categories_edit_save" class="b-layout__txt b-layout__txt_padright_10" style="display:none">
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_dot_c10600" href="javascript:void(0)">Сохранить</a>
                    </span>
                    <span id="categories_edit_cancel" class="b-layout__txt" style="display:none">
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_dot_c10600" href="javascript:void(0)">Отмена</a>
                    </span>
                    
                <? } else { ?>
                    <ul id="commune_categories_list" class="b-menu__list">
                        <?= __commPrintCategoriesList($comm['id'], $om, intval($cat), intval($page)) ?>
                    </ul>
                <? } ?>
            </div>

            <? // закладки ?>
            <?php if(get_uid(false)) { ?>
            <? seo_start();?>
            <div class="b-menu b-menu_padbot_20 b-menu_vertical">
                <h3 class="b-menu__title b-menu__title_bold b-menu__title_padbot_5">Закладки</h3>
                <div class="b-menu b-menu_rubric" id="fav_order_menu"<?= !$favs ? ' style="display:none"' : '' ?>>
                    <ul class="b-menu__list b-menu__list_margleft_0" style="padding-bottom:5px;">
                        <li style=" <?= $sort != 'date' ? ' display:none !important' : '' ?>" id="favs_date_sorted1" class="b-menu__item b-menu__item_active">
                            <span class="b-menu__b1">
                                <span class="b-menu__b2">по дате</span>
                            </span>
                        </li>
                        <li style=" <?= $sort != 'date' ? 'display:none !important' : '' ?>" id="favs_date_sorted2" class="b-menu__item">
                            <a id="favs_sort_abc" href="javascript:void(0)" class="b-menu__link" data-cid="<?= $comm['id']?>">по алфавиту</a>
                        </li>
                        <li style=" <?= $sort == 'date' ? 'display:none !important' : '' ?>" id="favs_abc_sorted1" class="b-menu__item b-menu__item_padleft_10">
                            <a id="favs_sort_date" href="javascript:void(0)" class="b-menu__link" data-cid="<?= $comm['id']?>">по дате</a>
                        </li>
                        <li style=" <?= $sort == 'date' ? 'display:none !important' : '' ?>" id="favs_abc_sorted2" class="b-menu__item b-menu__item_active">
                            <span class="b-menu__b1">
                                <span class="b-menu__b2">по алфавиту</span>
                            </span>
                        </li>
                    </ul>
                </div>
                <ul class="b-menu__list" id="favBlock">
                    <?= __commPrntFavs($favs, $uid, $om) ?>
                </ul>
                <div<?= $favs ? ' style="display:none"' : '' ?> id="no_favs">Нет закладок</div>
                <div id="favs_edit_edit" class="b-menu__txt b-menu__txt_inline" <?= !$favs ? ' style="display:none"' : '' ?>>
                    <a id="favs_edit_edit_btn" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_dot_c10600" href="javascript:void(0)">Редактировать</a>
                </div>
                <div id="favs_edit_save" class="b-menu__txt b-menu__txt_inline" style="display:none">
                    <a id="favs_edit_save_btn" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_dot_c10600" href="javascript:void(0)">Закончить редактирование</a>
                </div>
                <input id="favs_user_id" type="hidden" value="<?= $uid ?>" />
                <input id="favs_om" type="hidden" value="<?= $om ?>" />
            </div>
            <?= seo_end();?>

            <script type="text/javascript">
                //hlSort('<?= $sort;?>');
           </script>
           <?php  } //if?>
           <!-- члены сообщества
           <? if (($menCnt = ($comm['a_count'] - $comm['w_count'] - $adminCnt))) { ?>
                <h4>Участники:</h4>
                <ul>
                    <?php foreach ($members as $memb) { ?>
                    <li class="">
                        <?= __commPrntUsrAvtr($memb) ?>
                        <?= __commPrntUsrInfoMain($memb, '', '', '', true, ($is_author||$is_comm_admin), $is_comm_admin||$is_author) ?>
                    </li>
                    <?php } //foreach ?>
                </ul>
            <?} //if ?>
            -->
        </div>
    <div class="b-layout__right b-layout__right_relative b-layout__right_width_72ps b-layout__right_margleft_3ps b-layout__right_float_left">
        <div id="blocked-reason-<?= $id;?>">
            <?php if($comm['is_blocked'] == 't'){
                echo __commPrntBlockedBlock($comm['blocked_reason'], $comm['blocked_time'], $comm['admin_login'], "{$comm['admin_name']} {$comm['admin_uname']}", $comm['id']);
            }?>
        </div>
        <? foreach ($topics as $top) {
        	    if ($top["msgtext"]) {
        	       validate_code_style($top["msgtext"]);
        	    }
                if  ( ($top['user_is_banned'] && !($user_mod & commune::MOD_ADMIN))
                    || ($top['member_is_banned'] && $top['user_id'] != get_uid(false) && !($user_mod & (commune::MOD_ADMIN | commune::MOD_COMM_AUTHOR | commune::MOD_COMM_MANAGER)))
                    || ( intval($top["deleted_id"]) != 0 && !hasPermissions("adm") )
                    || ($top['is_private'] == 't' && $top['user_id'] != $uid && !($user_mod & (commune::MOD_ADMIN | commune::MOD_COMM_AUTHOR | commune::MOD_COMM_MANAGER))) ) {
                    continue;
        } ?>
            <? // печатаем топик
            if (!$comm['is_blocked'] || $user_mod & commune::MOD_MODER) { ?>
                <a name="o<?= ($alert && $message_id ? '' : $top['id']) ?>"></a>
                <div id='idTop_<?= $top['id'] ?>'>
                    <?= __commPrntTopic($top, $uid, $user_mod, $om, $page, NULL, (isset($favs[$top['id']]) ? 1 : 0), $favs) ?>
                </div>
            <? } ?>
        <? } ?>
            
        <?php
        // пагинатор
        $url_p = "%s?page=%d".($cat?'&cat='.$cat:'').($om?'&om='.$om:'')."%s";
        echo (!$comm['is_blocked'] || $user_mod & commune::MOD_MODER) ? new_paginator($page, $pages, 3,$url_p, 'href') : '';

        if(($comm['restrict_type'] === '00') || $user_mod & (commune::MOD_COMM_AUTHOR | commune::MOD_ADMIN | commune::MOD_MODER | commune::MOD_COMM_ACCEPTED | commune::MOD_COMM_ADMIN | commune::MOD_COMM_MODERATOR | commune::MOD_COMM_ADMIN)){ ?>
            <div style="padding:15px 0 20px 0;" class="commune-rss">
                <a href="/rss/commune.php?id=<?= $id;?>"><img src="/images/ico_rss.gif" border="0" hspace="4"></a><a class="blue" href="/rss/commune.php?id=<?= $id;?>">Фри-ланс</a>
            </div>
        <? } ?>
    </div>
</div>

<script type="text/javascript">
    commune_config = {
        poll_id: 'editmsg',
        poll_max: <?= commune::POLL_ANSWERS_MAX ?>,
        session: '<?= $_SESSION['rand'] ?>',
        question_max_char: <?= commune::POLL_QUESTION_CHARS_MAX ?>,
        new_post_id: <?=$id?>,
        new_post_om: <?=$om?>
    };
</script>

<?php 
if ( $user_mod & (
        commune::MOD_COMM_ADMIN
        | commune::MOD_ADMIN
        | commune::MOD_MODER
        | commune::MOD_COMM_MODERATOR
        | commune::MOD_COMM_AUTHOR
    ) 
) {
	include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
	include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
}
?>

<a id="upper" class="b-page__up" href="#" style=" visibility:hidden;"></a>

</div>
