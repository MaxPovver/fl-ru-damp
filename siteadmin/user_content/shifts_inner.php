<?php
/**
 * Модерирование пользовательского контента. Смены. Шаблон.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
?>

<h2 class="b-layout__title b-layout__title_padbot_30">Пользовательский контент / Смены</h2>

<?php if ($_SESSION['admin_shifts_success']) { 
    unset( $_SESSION['admin_shifts_success'] );
?>
  <div>
    <img src="/images/ico_ok.gif" alt="" border="0" height="18" width="19"/>&nbsp;&nbsp;Изменения внесены.
  </div>
  <br/><br/>
<?php } if ($error) print(view_error($error).'<br/>'); ?>

<form method="post" name="form_shifts" id="form_shifts">
    <input type="hidden" name="cmd" value="go">
<?php
if (is_array($aDelId) && count($aDelId) ) {
    foreach ($aDelId as $aOne) {
?><input type="hidden" name="del_id[]" value="<?=$aOne?>"><?php
    }
}
?>    
    <div id="div_shifts">
<?php
$nCnt = 1;

if ( !empty($aExId) && !empty($aExFrom) && !empty($aExTo) ) {
    parseShifts( $nCnt, $aExId, $aExFrom, $aExTo, 'ex' );
}

if ( !empty($aAddFrom) && !empty($aAddTo) ) {
    parseShifts( $nCnt, array(), $aAddFrom, $aAddTo, 'add' );
}
?>
    </div>
</form>


<div class="b-layout__txt b-layout__txt_padbot_5 i-button">
    <a href="javascript:void(0);" onclick="user_content.addShift();" class="b-button b-button_margright_5 b-button_poll_plus"></a><a href="javascript:void(0);" onclick="user_content.addShift();" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_inline-block b-layout__link_valign_middle">Добавить <span id="add_shift_cnt"><?=($nShifts+1)?></span> смену</a>
</div>


<div class="b-buttons b-buttons_padtop_40">
    <a href="javascript:void(0);" onclick="return user_content.submitShifts();" class="b-button b-button_flat b-button_flat_green">Сохранить</a>
    
	<span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
	<a href="/siteadmin/user_content/?site=shifts" class="b-buttons__link b-buttons__link_color_c10601">отменить изменения</a>
</div>
  
<script type="text/javascript">
    user_content.shiftCnt = <?=$nShifts?>;
</script>
  
<?php
function parseShifts( &$nCnt = 1, $aId = array(), $aFrom = array(), $aTo = array(), $sPref = '' ) {
    for ( $j = 0; $j < count($aFrom); $j++ ) {
        $sDivId = ( $sPref == 'ex' ) ? 'div_ex_' . $aId[$j] : 'div_add' . $nCnt;
        $sClick = ( $sPref == 'ex' ) ? 'delShiftEx('. $aId[$j] .')' : "delShift('div_add$nCnt')";
        if ( $sPref == 'ex' ) {
?>
        <input type="hidden" name="ex_id[]" id="ex_id<?=$aId[$j]?>" value="<?=$aId[$j]?>">
<?php 
        } 
?>
        <div class="b-layout__txt b-layout__txt_padbot_15 i-button my-shift" id="<?=$sDivId?>">
            <span><?=$nCnt?></span> смена работает с 
            <div class="b-combo b-combo_inline-block b-combo_margtop_-5">
                <div class="b-combo__input b-combo__input_width_35">
                    <input class="b-combo__input-text" name="<?=$sPref?>_from[]" type="text" value="<?=$aFrom[$j]?>" />
                </div>
            </div>
            <span>&#160;до&#160;</span>
            <div class="b-combo b-combo_inline-block b-combo_margtop_-5">
                <div class="b-combo__input b-combo__input_width_35">
                    <input class="b-combo__input-text" name="<?=$sPref?>_to[]" type="text" value="<?=$aTo[$j]?>" />
                </div>
            </div>
            &#160;<a href="javascript:void(0);" onclick="user_content.<?=$sClick?>;" class="b-button b-button_margtop_-5 b-button_admin_del"></a>
        </div>
<?php
        $nCnt++;
    }
}
?>