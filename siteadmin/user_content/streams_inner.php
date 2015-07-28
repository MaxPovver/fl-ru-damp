<?php
/**
 * Модерирование пользовательского контента. Количество потоков в сменах. Шаблон.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
?>

<h2 class="b-layout__title b-layout__title_padbot_30">Пользовательский контент / Потоки</h2>

<?php if ($_SESSION['admin_streams_success']) { 
    unset( $_SESSION['admin_streams_success'] );
?>
  <div>
    <img src="/images/ico_ok.gif" alt="" border="0" height="18" width="19"/>&nbsp;&nbsp;Изменения внесены.
  </div>
  <br/><br/>
<?php } if ($error) print(view_error($error).'<br/>'); ?>

<?php

if ( $nShifts ) {
    
?>
<form method="post" name="form_streams" id="form_streams">
    <input type="hidden" name="cmd" value="go">
<div style="overflow:auto; width:760px;">
<table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0" style="width:<?=(120+100*$nShifts)?>px;"><!-- ширину вычислять так: 120 + 100 x число столбцов -->
    <tr class="b-layout__tr">
        <td class="b-layout__left b-layout__left_valign_bottom b-layout__left_width_120 b-layout__left_padbot_30">
            <div class="b-layout__txt b-layout__txt_fontsize_11">Смены</div>
        </td>
<?php
        $nCnt = 1;
        foreach ( $aShifts as $aOne ) {
?>
        <td class="b-layout__one b-layout__one_width_100 b-layout__one_padbot_30">
            <div class="b-layout__txt b-layout__txt_fontsize_11"><span class="b-layout__txt b-layout__txt_fontsize_13 b-layout__txt_bold"><?=$nCnt?>,</span> <?=  substr( $aOne['time_from'], 0, 5 ) . ' &mdash; ' . substr( $aOne['time_to'], 0, 5 )?></div>
        </td>
<?php
            $nCnt++;
        }
?>
    </tr>
<?php
        foreach ( $aContents as $aOne ) {
?>
    <tr class="b-layout__tr">
        <td class="b-layout__left" colspan="<?=($nShifts+1)?>">
            <div class="b-layout__h3"><?=$aOne['name']?></div>
        </td>
    </tr>
    <tr class="b-layout__tr">
        <td class="b-layout__left b-layout__left_width_120 b-layout__left_padbot_30">
            <div class="b-layout__txt">Кол-во потоков</div>
        </td>
<?php
            foreach ( $aStreams[$aOne['id']] as $nShiftId => $nStreams ) {
?>
        <td class="b-layout__one b-layout__one_width_100 b-layout__one_padbot_30 i-button">
            <a onclick="user_content.downShiftsContents(this);" href="javascript:void(0);" class="b-button b-button_padright_3 b-button_poll_minus"></a>&#160;&#160;<div class="b-combo b-combo_inline-block b-combo_margtop_-5">
                <div class="b-combo__input b-combo__input_width_35">
                    <input type="text" name="streams[<?=$aOne['id']?>][<?=$nShiftId?>]" value="<?=$nStreams?>" class="b-combo__input-text" />
                </div>
            </div>&#160;&#160;<a onclick="user_content.upShiftsContents(this);" href="javascript:void(0);" class="b-button b-button_padright_3 b-button_poll_plus"></a>
        </td>
<?php
            }
?>
    </tr>
<?php
        }
?>
</table>
</div>

<div class="b-buttons b-buttons_padtop_20">
    <a href="javascript:void(0);" onclick="return user_content.submitShiftsContents();" class="b-button b-button_flat b-button_flat_green">Сохранить</a>
	<span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
	<a href="/siteadmin/user_content/?site=streams" class="b-buttons__link b-buttons__link_color_c10601">отменить изменения</a>
</div>

</form>
<?php

}
else {
    
?>
Перед настройкой потоков необходимо настроить <a href="/siteadmin/user_content/?site=shifts">смены</a>.
<?php

}

?>