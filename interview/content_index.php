<?
$sorting = array(
    'date' => 'по дате добавления',
    'views' => 'по количеству просмотров',
);

if(count($query)) {
    $is_query_uri = true;
    $query_str = "?".http_build_query($query);
}
$uri = "/interview/".$query_str;
?>

<?php
$crumbs = array();
$crumbs[] = array("title"=>"Статьи и интервью", "url"=>"/articles/");
$crumbs[] = array("title"=>"Интервью", "url"=>"");
?>
<div class="b-menu b-menu_crumbs  b-menu_padbot_20"><?=getCrumbs($crumbs)?></div>


<? include($mpath . '/tabs.php'); ?>
<div class="page-interview">
    <a name="page_interview"></a>
    <div style="border-bottom:1px solid #D9D9D9; background:url('../images/bar-interview-sort.png') repeat-x scroll left bottom transparent">
        <table class="b-layout__table b-layout__table_width_full">
           <tr class="b-layout__tr">
             <td class="b-layout__one b-layout__one_width_380 b-layout__one_padleft_20 b-layout__one_bordright_d9 b-layout__one_padtop_9"> 
                <noindex>
                    <span class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_11 b-layout__txt_inline-block">Сортировать:</span>
                        <? foreach($sorting as $k => $label) { ?>
                            <? if($k == $ord) { ?>
                                &#160;&#160;&#160;<span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_pad_1_3" style="background:#E6E6E3"><?=$label?></span>
                            <? } else { ?>
                                &#160;&#160;&#160;<span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block"><a class="b-layout__link b-layout__link_bordbot_dot_41" rel="nofollow" href="<?= url($GET, array('ord' => $k), 0, '?') ?>"><?=$label?></a></span>
                            <? } ?>
                        <? } ?>
                </noindex>
             </td>
             <td class="b-layout__one b-layout__one_padleft_20 b-layout__one_padtop_9"> 
                <noindex>
                        <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_pad_1_3" style="<?=!$year ? 'background:#E6E6E3' : ''?>">
                            <? if($year) { ?>
                            <a class="b-layout__link b-layout__link_bordbot_dot_41" rel="nofollow" href="<?=url($GET, array('ord' => $ord), true, '?')?>">Все</a>
                            <? } else { ?>Все<? } ?>
                        </span>
                        <? if($years) foreach($years as $y) { ?>
                        <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline-block b-layout__txt_pad_1_3" style="<?=$y['yr'] == $year ? 'background:#E6E6E3' : '' ?>">
                            <? if($y['yr'] != $year) { ?>
                            <a class="b-layout__link b-layout__link_bordbot_dot_41" rel="nofollow" href="<?=url($GET, array('yr' => $y['yr']), 0, '?')?>"><?=$y['yr']?></a>
                            <? } else { ?>
                            <?=$y['yr']?>
                            <? } ?>
                        </span>
                        <? } ?>

                </noindex>
             </td>
             <td class="b-layout__one b-layout__one_pad_5_10 b-layout__one_width_150"> 
                <noindex>
                <form action="">
                    <div class="b-select b-select_float_right b-select_width_140">
                        <select class="b-select__select" onchange="location.href = '<?= $uri.($is_query_uri?"&":"?")?>filter=' + this.value;" style="width:140px;">
                            <option value="0" <?= ($filter==0?'selected="selected"':'');?>>Все пользователи</option>
                            <option value="1" <?= ($filter==1?'selected="selected"':'');?>>Фри-лансер</option>
                            <option value="2" <?= ($filter==2?'selected="selected"':'');?>>Работодатель</option>
                        </select>
                    </div>
                </form>
                </noindex>
             </td>
                <? if(hasPermissions('interviews')) { ?>
                     <td class="b-layout__one b-layout__one_width_150"> 
                      <div class="i-add">
                        <div class="b-fon b-fon_bg_74bb54">
                          <b class="b-fon__b1"></b>
                          <b class="b-fon__b2"></b>
                          <div class="b-fon__body b-fon__body_pad_2_5"><a class="b-layout__link b-layout__link_color_fff" href="javascript:void(0)" onclick="toggleAddForm(0,1)">Добавить интервью</a></div>
                          <b class="b-fon__b2"></b>
                          <b class="b-fon__b1"></b>
                        </div>        
                      </div>
                     </td>
                <? } ?>
          </tr>
       </table>
    </div>
    <div class="b-layout__txt b-layout__txt_padtop_20 b-layout__txt_padbot_20">Мы берем интервью у фри-лансеров и работодателей и выкладываем их здесь. А это значит, что теперь вы можете узнать о том, как живут ваши коллеги по цеху, что их интересует и влечет, как они начинали карьеру и кем видят себя в будущем, что читают и чего боятся, для чего живут и что приносит им радость. Фри-лансеры с удовольствием поделятся секретами профессионального успеха и дадут полезные советы новичкам, а работодатели расскажут о том, на что обращают внимание при поиске фри-лансеров, как выбирают исполнителей на проекты и еще много чего интересного. Приятного чтения!</div>
        <? if($list && count($list)) { ?>
        <ul class="interview-list ">
        <? foreach($list as $i => $interview) { ?>
            <li id="interview<?=$interview['id']?>" class="<?=($i+1)%4 == 0 && $i>0 ? 'fourth' : ''?>" >
                 <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_relative b-layout__txt_marglr_auto" style="height:180px; width:180px;">
                    <a class="b-layout__link" href="<?=getFriendlyURL('interview', $interview['id'])?><?=count($query) ? '?' . http_build_query($query) : ''?>">
                        <? if ($interview['fname']) { ?>
                        <?
                        $force_h = null;

                        $ratio_w = 180/$interview['width'];

                        if($interview['height']*$ratio_w > 180) {
                            $force_h = 1;
                        }
                        $alt = $interview['uname'] . ' ' . $interview['usurname'] . ' [' . $interview['login'] . ']';
                        ?>
                        <img class="b-layout__pic b-layout__pic_absolute" style="bottom:10px;" <?= $force_h ? 'height="180"' : 'width="180"'?> src="<?=WDCPREFIX . "/{$interview['path']}".(substr($interview['fname'],0,3)=='sm_' ? '' : 'sm_')."{$interview['fname']}"?>" alt="<?= $alt ?>" title="<?= $alt ?>" />
                        <? } ?>
                    </a>
                  </div>
                <div class="b-layout__txt b-layout__txt_center"><a class="b-layout__link b-layout__link_bold b-layout__link_color_000" href="<?=getFriendlyURL('interview', $interview['id'])?>"><?=$interview['uname'] . ' ' . $interview['usurname'] . ' [' . $interview['login'] . ']'?></a></div>
                <? if(hasPermissions('interviews')) { ?>
                <div class="b-layout__txt b-layout__txt_center">
                    <a class="b-layout__link b-layout__link_color_000" href="javascript:void(0)" onclick="editInterview(<?=$interview['id']?>)">Редактировать</a> |
                    <a class="b-layout__link b-layout__link_color_000" href="./?task=del&id=<?=$interview['id']?>&token=<?=$_SESSION['rand']?>" onclick="return (confirm('Уверены?'))">Удалить</a>
                </div>
                <? } ?>
            </li>
        <? } ?>
        </ul>
        <? } else { ?>
        <div class="b-layout__txt b-layout__txt_padtop_20 b-layout__txt_padbot_20">Интервью не найдено</div>
        <? } //else?>
        <? if(hasPermissions('interviews')) include('form.php'); ?>
</div>
