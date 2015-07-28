<?php 
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/notes.php"); 
$oNotes = new notes();
$aNote  = $oNotes->GetNoteInt( $_SESSION['uid'], $user->uid, $error );
?>

<?php if ( $aNote ) {
?>
<div class="bBD" id="zametkaBD">
    <div id="zametka" class="b-layout b-layout_pad_10 b-layout_bord_ffeda9 b-layout_bordrad_1 b-fon_bg_fff9bf_hover b-layout_hover ">
      <?php /*<a  href="javascript:void(0);" onclick="if(confirm('¬ы действительно хотите удалить заметку?')){xajax_saveHeaderNote('<?=$name?>','');}"><img src="/images/btn-remove2.png" width="11" height="11" alt="" /></a>*/ ?>
      <a class="b-icon b-icon__edit b-icon_float_right  b-layout_hover_show" href="javascript:void(0);" onclick="headerNoteForm();"></a>
      <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">¬аша заметка</div>
      <div class="b-layout__txt b-layout__txt_fontsize_11"><?=reformat($aNote['n_text'], 24, 0, 0, 1, 24)?></div>
    </div>
</div>
<?php
}
else {
?>
    <div id="zametka" class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_inline-block"><span class="b-icon b-icon__cont b-icon__cont_note" ></span><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_fontsize_13" href="javascript:void(0);" onclick="$('zametka_fmr').toggleClass('b-layout_hide');">ќставить заметку</a></div>
<?php
}
?>
    
    <div id="zametka_fmr" class="b-layout b-layout_pad_15 b-layout_bord_ffeda9 b-layout_bordrad_1 b-layout_hide b-fon_bg_fff9bf b-fon">
          <form action="">
             <input type="hidden" name="rating" id="note_rating" value="<?= (int)$aNote['rating']?>">
                 <div class="b-textarea">
                  <textarea class="b-textarea__textarea" id="header_note" name="header_note" cols="70" rows="5" onkeyup="(checknote(this))"></textarea>
                 </div>
                 <div class="b-buttons b-buttons_padtop_10">
                  <a href="javascript:void(0);" onclick="xajax_saveHeaderNote('<?=$name?>',$('header_note').get('value'), $('note_rating').get('value'));" class="b-button b-button_flat b-button_flat_grey">—охранить</a>
                  &#160;&#160; или &#160;<a href="javascript:void(0);" onclick="headerNoteText();" class="b-layout__link b-layout__link_fontsize_11">отменить</a>
                  </div>
          </form>
    </div>
    
    
<script type="text/javascript">
<?php
// !!! тут нужен htmlspecialchars_decode - эта переменна€ хранит исходный код заметки который в текстарию подставл€етс€
// !!! изза htmlspecialchars_decode на странице по€вл€етс€ XSS см. http://beta.free-lance.ru/mantis/view.php?id=12887
// @todo Ќеобходимо привести все заметки на сайте к общей системе отображени€, сохранени€
?>
var headerNote = '<?=input_ref_scr(($aNote['n_text']))?>';
</script>
