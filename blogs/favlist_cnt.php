<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/links.php");
$GLOBALS[LINK_INSTANCE_NAME] = new links('blogs');

$blog = new blogs();

$is_yt_link = ($edit_msg['yt_link'] || $yt_link);
$uid = get_uid();
?>
<script type="text/javascript">
<!--
var yt_link = <? if ($is_yt_link) { ?>true<? } else { ?>false<? } ?>;
var blogs_max_desc_chars = <?=blogs::MAX_DESC_CHARS?>;
//-->
</script>
<?if ($uid){?>
<?if ($_COOKIE['blogs_favs_order'] != ""){?>
<script type="text/javascript">
<!--
<?
if ($_COOKIE['blogs_favs_order'] == "priority") $currentOrderStr = 1;
elseif ($_COOKIE['blogs_favs_order'] == "abc") $currentOrderStr = 2;
else $currentOrderStr = 0;
?>
order_now = "<?=htmlspecialchars($_COOKIE['blogs_favs_order']);?>";
currentOrderStr = "<?=$currentOrderStr;?>";

//-->
</script>
<?}?>
<?}?>
<?
$answers = $blog->Poll_GetPostAnswers($edit_msg);
if ($err || !$gr_name) {
	include ("../404_inner.php");
} else {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/blogs.common.php");
	$xajax->printJavascript('/xajax/');
    ?>
<table border="0" width="100%" cellpadding="0" cellspacing="0"><tr valign="middle"><td align="left"><h1>Блоги</h1></td><td align="right"><a class="b-button b-button_round_green b-button_float_right" href="<?=($_SESSION['login']? '#bottom': '/fbd.php')?>"><span class="b-button__b1"><span class="b-button__b2"><span class="b-button__txt">Написать сообщение</span></span></span></a></td></tr></table>

<div class="b-menu b-menu_tabs">
    <ul class="b-menu__list b-menu__list_overflow_hidden b-menu__list_padleft_28ps">
        <li class="b-menu__item <?php  ($ord != "best" && $ord != "my" && $ord != "relevant" && $ord != "favs") ? print 'b-menu__item_active': print '';?>">
				<?php if (!($ord != "best" && $ord != "my" && $ord != "relevant" && $ord != "favs")) { ?>
						<a class="b-menu__link" href="<?php print $href.($sHref ? '&' : '?').'ord=new' ?>" title="Новые">
				<?php } else print '<span class="b-menu__b2">'?>
								<span class="b-menu__b1">Новые</span>
				<?php if (!($ord != "best" && $ord != "my" && $ord != "relevant" && $ord != "favs")) { ?>
						</a>
				<?php } else print '</span>' ?>
				</li>
        <li class="b-menu__item <?php  ($ord == "best") ? print 'b-menu__item_active': print '';?>">
				<?php if (!($ord == "best")) { ?>
						<a class="b-menu__link" href="<?php print  $href.($sHref ? '&' : '?').'ord=best' ?>" title="Популярные">
				<?php } else print '<span class="b-menu__b2">'?>
								<span class="b-menu__b1">Популярные</span>
				<?php if (!($ord == "best")) { ?>
						</a>
				<?php } else print '</span>' ?>
				</li>
        <li class="b-menu__item <?php  ($ord == "relevant") ? print 'b-menu__item_active': print '';?>">
				<?php if (!($ord == "relevant")) { ?>
						<a class="b-menu__link" href="<?php print  $href.($sHref ? '&' : '?').'ord=relevant' ?>" title="Актуальные">
				<?php } else print '<span class="b-menu__b2">'?>
								<span class="b-menu__b1">Актуальные</span>
				<?php if (!($ord == "relevant")) { ?>
						</a>
				<?php } else print '</span>' ?>
				</li>
        <li class="b-menu__item <?php  ($ord == "my") ? print 'b-menu__item_active': print '';?>">
				<?php if (!($ord == "my")) { ?>
						<a class="b-menu__link" href="<?php print  $href.($sHref ? '&' : '?').'ord=my' ?>" title="Мои">
				<?php } else print '<span class="b-menu__b2">'?>
								<span class="b-menu__b1">Мои</span>
				<?php if (!($ord == "my")) { ?>
						</a>
				<?php } else print '</span>' ?>
				</li>
        <li class="b-menu__item b-menu__item_last <?php  ($ord == "favs") ? print 'b-menu__item_active': print '';?>">
				<?php if (!($ord == "favs")) { ?>
						<a class="b-menu__link" href="<?php print  $href.($sHref ? '&' : '?').'ord=favs' ?>" title="Закладки">
				<?php } else print '<span class="b-menu__b2">'?>
								<span class="b-menu__b1">Закладки</span>
				<?php if (!($ord == "favs")) { ?>
						</a>
				<?php } else print '</span>' ?>
				</li>
    </ul>
</div>
	

<div id="content">
    <a name="tm-last"></a>
    
    <div id="rightcl"  class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
        <?php
        //$href = '/blogs/viewgroup.php?'.("gr={$gr}".($base ? "&t=prof" : "")).'&ord=favs';
        $href = getFriendlyURL('blog_group', $gr) . "?ord=favs" . ($base ? "&t=prof" : "");
        ?>
        <div class="b-layout__txt b-layout__txt_float_right b-layout__txt_width_225">
        	<span class="b-layout__txt b-layout__txt_padtop_2  b-layout__txt_inline-block">Упорядочить:</span>
         <div class="b-select b-select_width_140 b-select_float_right">
        	<select id="sel_blogs_favs_sort" class="b-select__select" name="sel_blogs_favs_sort" onchange="window.location='<?=$href?>&order='+this.value;">
        		<option value="date" <?php echo (! $order || $order == 'date') ? ' selected="selected"' : ''?>>по дате добавления</option>
        		<option value="priority" <?php echo ($order == 'priority') ? ' selected="selected"' : ''?>>по важности</option>
        		<option value="abc" <?php echo ($order == 'abc') ? ' selected="selected"' : ''?>>по алфавиту</option>
        	</select>
         </div>
        </div>
        
        <ul class="tabs-sort-nav">
            <?php if ( $sub_ord == 'favs_std' ): ?>
        	<li class="active">Стандартный</li>
        	<?php else: ?>
            <li><a href="<?=$href?>&sub_ord=favs_std" class="lnk-dot-666">Стандартный</a></li>
        	<?php endif; ?>
        	
        	<?php if ( $sub_ord == 'favs_list' ): ?>
        	<li class="active">Списком</li>
        	<?php else: ?>
            <li><a href="<?=$href?>&sub_ord=favs_list" class="lnk-dot-666">Списком</a></li>
        	<?php endif; ?>
        </ul>
        
        <?php if ( $themes ): ?>
        
        <div class="ul-fav-list" id="fav_ul">
            <ul>
            <?php foreach ($themes as $theme): ?>
            
                <li id="fav<?=$theme['thread_id']?>">
                    <span class="opt">
            			<img onClick="xajax_EditFavBlog(<?=$theme['thread_id']?>, <?=$gr?>)" src="/images/ico-e-u.png" alt="Редактировать" style="cursor: pointer;">&nbsp;&nbsp;
            			<img onClick="xajax_DelFavBlog(<?=$theme['thread_id']?>, <?=$gr?>)" src="/images/btn-remove2.png" alt="Удалить" style="cursor: pointer;">
            		</span>
            		<span class="stat"><img src="/images/bookmarks/<?=blogs::$priority_img[ $theme['priority'] ]?>" alt=""> <?=blogs::$priority_name[ $theme['priority'] ]?></span>
                    <?php $sTitle  = /*( $theme['calc_title'] && $theme['moderator_status'] === '0' && $theme['payed'] != 't') ? $stop_words->replace($theme['calc_title']) :*/ $theme['calc_title'];  ?>
            		<a href="<?=getFriendlyURL("blog", $theme['thread_id'])?>"><?=( $theme['calc_title'] ? reformat($sTitle, 37, 0, 1) : '<без темы>' )?></a>
            		<input type="hidden" id="favpriority<?=$theme['thread_id']?>" value="<?=$theme['priority']?>">
                </li>
            
            <?php endforeach; ?>
            </ul>
        </div>
        
        <?php else: ?>
        
        <div style="width: 100%; text-align: center; clear:both;"><h2>Сообщений нет</h2></div>
        
        <?php endif;?>

<? require_once($_SERVER['DOCUMENT_ROOT']."/blogs/msgform.php"); ?>

    </div>
   
    <div id="leftcl" class="b-layout__left b-layout__left_width_25ps">
      <form action="/search/" name="searh_frm" id="search_frm" method="GET">
        <div>
          <input type="hidden" name="type" value="blogs" />
          <input type="hidden" name="action" id="search_config_action" value="search" />
          <? seo_start();?>
          <div class="b-search b-search_padbot_20">
              <table class="b-search__table" cellpadding="0" cellspacing="0" border="0"><tr class="b-search__tr"><td class="b-search__input">
              <div class="b-input-hint">
                  <div class="b-input b-input_height_24">
                      <input id="fl2_search_input2" class="b-input__text" name="search_string" type="text" />
                </div>
              </div>
              </td><td class="b-search__button">
              <a id="fl2_search_submit" onclick="$('search_frm').submit();" class="b-button b-button_rectangle_color_transparent" href="#" >
                  <span class="b-button__b1">
                      <span class="b-button__b2">
                          <span class="b-button__txt">Найти</span>
                      </span>
                  </span>
              </a>
              </td></tr></table>
          </div>
          <?= seo_end();?>
          <div style="overflow: hidden; height: 0px;">
            <input type="hidden" id="search_config_type_projects" name="search_type[projects]" value="1" />
            <input type="hidden" id="search_config_type_people" name="search_type[people]" value="1" />
            <input type="hidden" id="search_config_type_works" name="search_type[works]" value="1" />
            <input type="hidden" id="search_config_type_messages" name="search_type[messages]" value="1" />
            <input type="hidden" id="search_config_type_commune" name="search_type[commune]" value="1" />
            <input type="hidden" id="search_config_type_blogs" name="search_type[blogs]" value="1" />
            <input type="hidden" id="search_config_type_articles" name="search_type[articles]" value="1" />
            <input type="hidden" id="search_config_type_shop" name="search_type[shop]" value="1" />
            <input type="hidden" id="search_config_type_notes" name="search_type[notes]" value="1" />
          </div>
        </div>
      </form>
    
    <? $ban_cat = 3; include ($_SERVER['DOCUMENT_ROOT'] . "/banner_under_cat.php")?>
    
        <h2>Раздел</h2>
        <ul class="group">
            <?
            $size = sizeof($groups);
            $sum  = 0;
            
            for ($i = 0; $i < $size; $i++) {
                $theme = $groups[$i];
                if( in_array($theme['id'], blogs::$copiny_group) ) {
                    $feedback_copiny[] = $theme;
                    continue; // Перенесли в архив #0023264
                }
                $love = (int)$theme['id'] == 55;
                
                if($love && !$allow_love) continue;
                $tname = $theme['t_name'];
                //if ($theme['id'] == 7 && $theme['t'] == 0) $tname = "<strong>".$theme['t_name']."</strong>";
                if ($theme['id'] == $gr && $base == $theme['t'])
                $group_line .= $addit."<li".($love ? ' style="background: url(/images/icons/heart.png) no-repeat 2px 7px;" ' : '').(($i == $size - 1)?" class=\"last\"":"").">".($love ? '<span id="love_time_simple" style="float:right">00:00:00</span>' : '').$theme['t_name']." (".zin($theme['num']).")</li>\n";
                else $group_line .= $addit."<li".($love ? ' style="background: url(/images/icons/heart.png) no-repeat 2px 7px;" ' : '').(($i == $size - 1)?" class=\"last\"":"").">".($love ? '<span id="love_time_simple" style="float:right">00:00:00</span>' : '')."<a href=\"".getFriendlyUrl('blog_group', $theme['id']).($theme['t']||$ord?"?":"").(($theme['t'])?"&amp;t=prof":"").(($ord != "new")?"&amp;ord=$ord":"")."\" title=\"".$theme['t_name']."\">".$tname." (".zin($theme['num']).")</a></li>\n";
                $sum = $sum + $theme['num'];
            } 
            ?>
            <li><? if ($gr) {?><a href="/blogs/viewgroup.php<?=($ord)?"?ord=$ord":""?>" title="Все вместе"><? } ?><strong>Все вместе</strong> (<?=$sum?>)<? if ($gr) {?></a><? } ?></li>
            <?=$group_line?>
        </ul>
        <? if(!empty($feedback_copiny)) { ?>
      <div class="group_copini">
        <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_padbot_5 b-layout__txt_padleft_5 b-layout__txt_fontsize_11 b-layout__txt_bold">Сообщество поддержки</div>
        <ul class="group">
            <?php foreach($feedback_copiny as $theme) { ?>
                <li><a href="https://feedback.fl.ru/" target="_blank"><?= $theme['t_name']; ?></a></li>
            <?php }//foreach?>
        </ul>
      </div>
      <? }//if?>
    
    <!-- Banner 240x400 -->
        <?= printBanner240(false, true); ?>
    <!-- end of Banner 240x400 -->

</div>
    



    <div class="clear"></div>
</div>
    <?php if($allow_love){ ?>
    <script type="text/javascript">
var launchdate=new cdLocalTime("love_time_simple", '<?=date("F d, Y H:i:s")?>', 0, '<?=VALENTIN_DATE_END?>');
launchdate.displaycountdown("days", formatresults3)
</script>
        <?php } ?>
    
    <?php
}
?>




<script type="text/javascript">

if (document.getElementById('poll-question')) {
        domReady( function() {
            document.getElementById('poll-question').value = document.getElementById('poll-question-source').value;
            if(document.getElementById('msg'))
                document.getElementById('msg').value = ($('msg_source')? $('msg_source').value : null);
        } );
        poll.init('Blogs', document.getElementById('editmsg'), <?= blogs::MAX_POLL_ANSWERS ?>, '<?= $_SESSION['rand'] ?>');
    maxChars('poll-question', 'poll-warn', <?= blogs::MAX_POLL_CHARS ?>);
}
else {

    domReady( function() {
        if(document.getElementById('msg'))
            document.getElementById('msg').value = ($('msg_source')? $('msg_source').value : null);
    } );
}


<? if (!empty($tr) && $_GET['action'] != 'edit') { ?>goToAncor('tm<?=$tr?>');<? } ?>

<? if (isset($alert) || $action=='edit') { ?>goToAncor('edit');<? } ?>


<? if ($uid) { ?>

function InitHideFav()
{
    HideFavFloat(0,0);
    HideFavOrderFloat(currentOrderStr);
}

document.body.onclick = InitHideFav;

<? } ?>

var gr_value = '<?=$gr_value?>';

</script>

<?php
if ( hasPermissions('blogs') ) {
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
}
?>
