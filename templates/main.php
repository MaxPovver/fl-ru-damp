<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/projects.common.php");
$xajax->printJavascript('/xajax/');
/*
if(!is_emp() && get_uid(false) && $kind != 8 && $filter_show) {
    $cls = "b-page__lenta_padtop_440";
} else if(!is_emp() && get_uid(false) && $kind == 8 && $filter_show) {
    $cls = "b-page__lenta_padtop_210";
} elseif($kind == 8) {
    $cls = "b-page__lenta_padtop_10";
} else {
    $cls = "b-page__lenta_padtop_115";
}
*/
?>

<div class="b-layout__left b-layout__left_width_25ps b-layout__right_float_right">
    <?php $banner_outer_class = 'b-layout b-layout_padtop_10 b-layout_padbot_20 b-layout_overflow_hidden b-page__desktop'; ?>
    <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/banner_promo.php"); ?>
     
<?php 
        if (!isset($new_project_button_is_visible) && 
            (is_emp() || !get_uid(false))): 
?>
        <?php if (in_array($kind, array(1, 5, 6))): ?>
            <a class="b-button b-button_flat b-button_flat_orange2 b-button_block b-button_margbot_20 __ga__sidebar__add_project" href="/public/?step=1&kind=1">Бесплатно опубликовать задание</a>
        <?php endif; ?>
	
        <?php if ($kind == 4): ?>
            <a class="b-button b-button_flat b-button_flat_orange2 b-button_block b-button_margbot_20 __ga__sidebar__add_vacancy" href="/public/?step=1&kind=4&red=">Опубликовать вакансию</a>
		<?php endif; ?>
    
        <?php if ($kind == 2): ?>
            <a class="b-button b-button_flat b-button_flat_orange2 b-button_block b-button_margbot_20 __ga__sidebar__add_contest" href="/public//?step=1&kind=7&red=">Опубликовать конкурс</a>
		<?php endif; ?>
    <?php endif; ?>
            
    <?= printBanner240(false); ?>



</div><!--b-layout__right -->
  
<div class="b-layout__right b-layout__right_relative b-layout__left_width_72ps">
		
<?php /*if(get_uid(false)) {*/ ?>
	<?php  if($kind == 8) {	?>
        <h1 class="b-page__title b-page__title_padnull">Сделаю</h1>
    
        <?php 
        if($kind == 8 && get_uid(false)) {
            include($_SERVER['DOCUMENT_ROOT'] . '/public/offer/tpl.filter-offers.new.php');
        }
        ?>
	<?php } else { ?>
    <?php include( $_SERVER['DOCUMENT_ROOT'] . "/projects/tpl.filter_head.php"); ?> 
    <h1 class="b-page__title">
	<?php if($kind == 6||$kind == 1) {?>
       Проекты
				<?php } ?>
    <?php if($kind == 5) {?>
       Все проекты, конкурсы и вакансии
                <?php } ?>
    <?php if($kind == 4) {?>
       Вакансии
				<?php } ?>
    <?php if($kind == 2) {?>
       Конкурсы для фрилансеров
				<?php } ?>
	<?php if (!($kind == 8)): ?>
        <span class="b-layout__txt_nowrap"><a id="post-rolling" class="b-icon b-icon__pt b-icon__pt_dis" href="" title="Показывать только заголовки проектов в списке."></a>&#160;<a id="post-opening" class="b-icon b-icon__pf" href="" title="Показывать заголовки и описания проектов в списке."></a></span>
    <?php endif ?>
</h1>
<?php } ?>
        
    <?php if($kind != 8) include( $_SERVER['DOCUMENT_ROOT'] . "/projects/tpl.filter.new.php"); ?> 
        
    <div class="b-page__filter fornavmenu" >
        <div class="b-menu b-menu_line b-menu_relative b-menu_padbot_25">
                <div class="b-menu__filter">
                    <?php
                    if ($kind == 2 || $kind == 7) {
                        $prjWord_1 = 'закрепленный конкурс';
                        $prjWord_2 = 'закрепленных конкурса';
                        $prjWord_5 = 'закрепленных конкурсов';
                    } elseif ($kind == 4) {
                        $prjWord_1 = 'закрепленная вакансия';
                        $prjWord_2 = 'закрепленных вакансии';
                        $prjWord_5 = 'закрепленных вакансий';
                    } else {
                        $prjWord_1 = 'закрепленный проект';
                        $prjWord_2 = 'закрепленных проекта';
                        $prjWord_5 = 'закрепленных проектов';
                    }
                    ?>
                    <? if ($_SESSION['top_payed'] && $kind != 8) { ?>
                    <span class="b-layout__txt b-layout__txt_color_323232 b-layout__txt_valign_top b-layout__txt_float_left"><?= $_SESSION['top_payed']?> <?= ending($_SESSION['top_payed'], $prjWord_1, $prjWord_2, $prjWord_5)?>&#160;&#160; <? if($_SESSION['hidetopprjlenta_more']==1 && $hidetopprjlenta==1) { ?><a class="b-menu__link b-menu__link_bordbot_dot_0f71c8" id="hide_top_project_lnk2" cmd="hide" onclick="this.hide(); xajax_HideTopProjects(this.get('cmd')); return false;">Скрыть все</a><? } ?>&#160;&#160; <a class="b-menu__link b-menu__link_bordbot_dot_0f71c8" id="hide_top_project_lnk" cmd="<?= $hidetopprjlenta==1 ? 'show' : 'hide' ?>" onclick="xajax_HideTopProjects(this.get('cmd')); return false;"><?= $hidetopprjlenta==1 ? 'Показать' : 'Скрыть' ?> все</a></span>
                    <? } ?>
                </div>
                <ul class="b-menu__list"  data-menu="true" data-menu-descriptor="nav">
                        <li class="b-menu__item <?= ($kind == 5 ? 'b-menu__item_active' : '') ?>"  <?= ($kind == 5 ? 'data-menu-opener="true" data-menu-descriptor="nav"' : '') ?>><a href="/projects/?kind=5" class="b-menu__link"><span class="b-page__desktop">Вся работа</span><span class="b-page__ipad b-page__iphone">Все</span></a></li>
                        <li class="b-menu__item <?= ($kind <= 1 ? 'b-menu__item_active' : '') ?>" <?= ($kind <= 1 ? 'data-menu-opener="true" data-menu-descriptor="nav"' : '') ?>><a class="b-menu__link" href="/projects/?kind=1">Проекты</a></li>
                        <li class="b-menu__item <?= ($kind == 4 ? 'b-menu__item_active' : '') ?>" <?= ($kind == 4 ? 'data-menu-opener="true" data-menu-descriptor="nav"' : '') ?>><a class="b-menu__link" href="/projects/?kind=4">Вакансии</a></li>
                        <li class="b-menu__item <?= (($kind == 2 || $kind == 7) ? 'b-menu__item_active' : '') ?>" <?= (($kind == 2 || $kind == 7)? 'data-menu-opener="true" data-menu-descriptor="nav"' : '') ?>><a class="b-menu__link" href="/konkurs/">Конкурсы</a></li>
                </ul>
            </div><!-- b-menu_tabs -->
            
    </div><!--b-page__filter-->
    <?
    // блок ответов на проекты
    if (get_uid(0) && !is_emp() && !is_pro()) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_answers.php");

        $user_answers = new projects_offers_answers;
        $user_answers->GetInfo($_SESSION['uid']);
        $free_answers = $user_answers->free_offers;
        
        $op_codes = $user_answers->GetOpCodes();
        $is_block_pro = true;
        include(TPL_ANSWERS_DIR."/tpl.answers-item.php");
    }
    ?>
<!--<div class="b-page__lenta <?=$cls?>">-->
    <div class="b-page__lenta ">
        <?php 
        if($kind == 8) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer_offers.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/teams.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
            $frl_offers = new freelancer_offers();

            $page   = __paramInit('int', 'page', 'page', 1);
            if(!$page || $page < 0) $page = 1;
            $all_cnt = $frl_offers->getCountFreelancerOffers($filter_apply?$filter:false);
            $pages = ceil($all_cnt / freelancer_offers::FRL_COUNT_PAGES);

            $f_offers = $frl_offers->getFreelancerOffers($filter_apply?$filter:false, ($page-1)*freelancer_offers::FRL_COUNT_PAGES, freelancer_offers::FRL_COUNT_PAGES, $filter_only_my_offs);
                
        ?>
            <?php
            if($f_offers) {
                
                include($_SERVER['DOCUMENT_ROOT'] . '/public/offer/tpl.offers-item.php');    

            } else { ?>
            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_center b-layout__txt_padtop_30 b-layout__txt_fontsize_15">Попробуйте изменить критерии поиска</div>
            <?php } //if?>               
        <?php } else { //if?>
        <div id="projects-list"><?= $prj_content ?></div>
        <?php }//else?>
    </div><!-- b-page__lenta -->
					
</div><!-- b-layout__left-->
				

<?php
if ( $kind == 8 && $_SESSION['uid'] ) { // залогиненый юзер смотри предложения фрилансеров
?>
<script type="text/javascript">
var complainBusy    = false;
var complainOfferId = 0;
var complainUserId  = <?=$_SESSION['uid']?>;

function complainPopup( offer_id ) {
    complainOfferId = offer_id;
    $('b-radio__input4').set('checked',true);
    $('complain_fmsg').set('value','');
    $('complain_s_send').set('html','Отправить');
    $$('.b-popup').setStyle('display', 'block');
}

function complainSend() {
    if ( !complainBusy ) {
        complainBusy     = true;
        var complainType = $$('input[name=complain_type]:checked').map(function(e) { return e.value; });
        var complainMsg  = $('complain_fmsg').get('value');
        xajax_sendOfferComplain( complainOfferId, complainUserId, complainType[0], complainMsg );
    }
}
</script>
<div class="b-popup b-popup_center b-popup_width_600" style="display:block">
    <div class="b-popup__c1"></div>
    <div class="b-popup__c2"></div>
    <div class="b-popup__t"></div>
    <div class="b-popup__r">
        <div class="b-popup__l">
            <form class="b-popup__body b-popup__body_padbot_50" action="">
            <div class="b-layout">
                <table class="b-layout_table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_width_270">
                        <h4 class="b-layout__h4">Укажите нарушения</h4>
                        <div class="b-radio b-radio_layout_vertical">
                            <div class="b-radio__item b-radio__item_padbot_5">
                                <input id="b-radio__input4" class="b-radio__input" name="complain_type" type="radio" value="4" />
                                <label class="b-radio__label b-radio__label_fontsize_13" for="b-radio__input4">Контактные данные</label>
                            </div>
                            <div class="b-radio__item b-radio__item_padbot_5">
                                <input id="b-radio__input5" class="b-radio__input" name="complain_type" type="radio" value="5" />
                                <label class="b-radio__label b-radio__label_fontsize_13" for="b-radio__input5">Реклама, ссылки на сторонние ресурсы</label>
                            </div>
                            <div class="b-radio__item b-radio__item_padbot_5">
                                <input id="b-radio__input6" class="b-radio__input" name="complain_type" type="radio" value="6" />
                                <label class="b-radio__label b-radio__label_fontsize_13" for="b-radio__input6">Мат, ругань, оскорбления</label>
                            </div>
                            <div class="b-radio__item b-radio__item_padbot_5">
                                <input id="b-radio__input3" class="b-radio__input" name="complain_type" type="radio" value="3" />
                                <label class="b-radio__label b-radio__label_fontsize_13" for="b-radio__input3">Другое</label>
                            </div>
                        </div>
                    </td>
                    <td class="b-layout__right ">
                        <div class="b-form">
                            <label class="b-form__name b-form__name_fontsize_13 b-form__name_padbot_5">Опишите нарушение (необязательно)</label>
                            <div class="b-textarea">
                                <textarea class="b-textarea__textarea b-textarea__textarea__height_140" name="complain_fmsg" id="complain_fmsg" cols="80" rows="5"></textarea>
                            </div>
                        </div>
                    </td>
                </tr>
                </table>
            </div>
            <div class="b-popup__foot">
                <div class="b-buttons">
                    <a onclick="complainSend();" id="complain_a_send" class="b-button b-button_rectangle_transparent" href="javascript:void(0);">
                        <span class="b-button__b1">
                            <span class="b-button__b2 b-button__b2_padlr_5">
                                <span id="complain_s_send" class="b-button__txt">Отправить</span>
                            </span>
                        </span>
                    </a>
                    <a class="b-buttons__link b-buttons__link_margleft_10 b-buttons__link_dot_039 b-popup__close" href="javascript:void(0);">Отменить</a>
                </div>
            </div>
            </form>
        </div>
    </div>
    <div class="b-popup__b"></div>
    <div class="b-popup__c3"></div>
    <div class="b-popup__c4"></div>
</div>
<?php } ?>

<div id="specialis" class="b-layout b-layout_clear_both b-layout_overflow_hidden <? if ( !$_SESSION['uid'] && $page <=1 ) { ?> b-layout_padbot_50 b-layout_top_100<? } else { ?> b-layout_padtop_30<? } ?>">
    <?php $groups_repeat = array(); ?>
    <?php if (isset($profs) && $profs): ?>    
        <h2 class="b-layout__title">Фрилансеры по специализациям</h2>
        <div class="b-layout b-layout_col_4 b-layout_col_2_ipad b-layout_col_1_iphone">
        <?php foreach ($profs as $prof): ?>
            <?php if (!isset($groups_repeat[$prof['grouplink']]) && ($groups_repeat[$prof['grouplink']] = 1)): ?>
                    <div class="b-layout__txt b-layout__txt_inline-block"><a class="b-layout__link b-layout__link_fontsize_11" href="/freelancers/<?=$prof['grouplink']?>"><?=$prof['groupname']?></a></div><br>
            <?php endif; ?>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>





<a id="upper" class="b-page__up" href="#" style=" visibility:hidden;"></a>


<? if($_GET['quickprj_ok']==1) { ?>
    <script type="text/javascript">yaCounter6051055.reachGoal('project_bill_win');</script>
    <? require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_buy_prj.php"); ?>
<? } ?>