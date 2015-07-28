<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if(!hasPermissions('adm') && hasPermissions('masssending')) {
  exit;
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/masssending.common.php");
$xajax->printJavascript('/xajax/');

require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );

$stop_words = new stop_words( true );

  if(!($pss = masssending::Get(NULL,$om, ($page-1)*$per_page, $per_page)))
    $pss=array();

  $newCnt      = masssending::GetCount(masssending::OM_NEW);
  $acceptedCnt = masssending::GetCount(masssending::OM_ACCEPTED);
  $deniedCnt   = masssending::GetCount(masssending::OM_DENIED);

  $pages = 1;
  if ($om==masssending::OM_NEW) {
    $pages = ceil($newCnt/$per_page);
  }
  else if ($om==masssending::OM_ACCEPTED) {
    $pages = ceil($acceptedCnt/$per_page);  
  }
  else if ($om==masssending::OM_DENIED) {
    $pages = ceil($deniedCnt/$per_page);  
  }
  
  function chel($num){return('человек'.((($num%100>=11&&$num%100<=14)||$num%10>4||!($num%10)||$num%10==1)?'':'а'));}
/*
  function __prntUsrInfo(
   $user,
   $pfx='',
   $cls='',
   $sty='')
  {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
    global $session;
    if($sty)
      $sty = " style='$sty'";

    if(!$cls)
      $cls = (is_emp($user[$pfx.'role']) ? 'emp' : 'frl').'name11';

    return (
             (payed::CheckPro($user[$pfx.'login']) ? view_pro().'&nbsp;' : '').
             "<font class='{$cls}'{$sty}>".
             $session->view_online_status($user[$pfx.'login']).
             "&nbsp;<a class='{$cls}'{$sty} href='/users/".$user[$pfx.'login']."' title='".$user[$pfx.'uname']." ".$user[$pfx.'usurname']."'>".
                  $user[$pfx.'uname']." ".$user[$pfx.'usurname'].
             "  </a>\n".
             "  [<a class='{$cls}'{$sty} href='/users/".$user[$pfx.'login']."' title='".$user[$pfx.'login']."'>".$user[$pfx.'login']."</a>]".
             "</font>"
           );
  }
*/
?>
<script type="text/javascript">
function masssendingEdit(id) {
  xajax_MasssendingEdit(id);
}
function masssendingSave() {
  xajax_MasssendingSave($('popup_masssending_edit_id').get('value'), $('popup_masssending_edit_txt').get('value'));
  window.location = "/siteadmin/masssending/#mass_"+$('popup_masssending_edit_id').get('value');
  $('popup_masssending_edit_id').set('value', '');
  $('popup_masssending_edit_txt').set('value', '');
  $('popup_masssending_edit').setStyle('display', 'none');
}
</script>
<style type="text/css">
  .bm a { color:#003399; text-decoration:underline }
  .bm-active { font-weight:700; background:#f0f0f0 }
  .bm-active a { color:#666666 }
  .bm-num { font-weight:normal; color:#666666 }
  .black div { color:black } 
</style>


<? if ($_GET['result']=='success') { ?>
  <div>
    <img src="/images/ico_ok.gif" alt="" border="0" height="18" width="19"/>&nbsp;&nbsp;Готово!
    <br/>
    <br/>
  </div>
<? } ?>
<? if ($masssending->error) { ?>
  <div>
    <?=view_error($masssending->error)?>
    <br/>
    <br/>
  </div>
<? } ?>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr valign="top">
    <td>
      <table border="0" cellspacing="0" cellpadding="4">
        <tr>
          <td class="bm<?=($om==masssending::OM_NEW?'-active':'')?>">
            <a href="?om=<?=masssending::OM_NEW?>">Новые</a>&nbsp;<span class="bm-num"><?=$newCnt?></span>
          </td>
          <td style="width:5px">&nbsp;</td>
          <td class="bm<?=($om==masssending::OM_ACCEPTED?'-active':'')?>">
            <a href="?om=<?=masssending::OM_ACCEPTED?>">Разрешенные</a>&nbsp;<span class="bm-num"><?=$acceptedCnt?></span>
          </td>
          <td style="width:5px">&nbsp;</td>
          <td class="bm<?=($om==masssending::OM_DENIED?'-active':'')?>">
            <a href="?om=<?=masssending::OM_DENIED?>">Отказанные</a>&nbsp;<span class="bm-num"><?=$deniedCnt?></span>
          </td>
        </tr>
      </table>
    </td>
    <td style="width:190px;height:100px" class="black">
      <form action="/siteadmin/masssending/" method="post">
        <div style="position:absolute;margin-top:-10px;margin-left:-4px;background:#f0f0f0;text-align:right;padding:10px 40px 10px 40px">
          <div align="left">Цена за человека</div>
          <div style="margin-top:10px">
            <label for="idNoPro">без <img src="/images/icons/f-pro.png" class="pro"></label>
            <input id="idNoPro" type="text" name="no_pro" value="<?=preg_replace('/\.00$/','',$tariff['no_pro'])?>" style="width:40px;text-align:right;padding-right:3px" />
            руб.
          </div>
          <div style="margin-top:2px">
            <label for="idPro">с <img src="/images/icons/f-pro.png" class="pro"></label>
            <input id="idPro" type="text" name="pro" value="<?=preg_replace('/\.00$/','',$tariff['pro'])?>" style="width:40px;text-align:right;padding-right:3px" />
            руб.
          </div>
          <div style="margin:10px 15px 0 0">
            <input type="submit" value="Сохранить" style="width:90px" />
          </div>
        </div>
        <input name="om" type="hidden" value="<?=$om?>"/>
        <input name="action" type="hidden" value="Change.tariff"/>
      </form>
    </td>
  </tr>
</table>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
  <col style="width:10px" />
  <? foreach($pss as $ps) { ?>
    <tr valign="top">
      <td>
        <a name="mass_<?=$ps['id']?>"></a>
        <?=view_avatar($ps['user_login'], $ps['user_photo'])?>
      </td>
      <td style="padding:0 0 0 10px">
        <?=__prntUsrInfo($ps, 'user_').'&nbsp;&nbsp;'.dateFormat("[d.m.Y | H:i]", $ps['posted_time'])?>
        <div id="mass_txt_<?=$ps['id']?>" style="padding-top:5px">
          <?php $msg_text = !$om && $ps['user_is_pro'] != 't' ? $stop_words->replace( $ps['msgtext'] ) : $ps['msgtext']; ?>
          <?=reformat($msg_text,30,0,0,1);?>
        </div>
		<div style="margin-top: 10px">
		<?
		if (!empty($ps['files'])) {
			$fl = '<div class="attachments attachments-p">';
			foreach ($ps['files'] as $file) {
				$fl .= '<div class = "flw_offer_attach">'.viewattachLeft( null, $file['fname'], $file['path'], $file, 0, 0, 0, 0, 0, 0, $nn )."</div>";
			}
      $fl .= '</div>';
			echo "<b>Файлы:</b></br>" . $fl;
		}
		?>
		</div>
      </td>
    </tr>
    <tr valign="top">
      <td>&nbsp;</td>
      <td style="padding:15px 0 0 10px">
        <table border="0" cellspacing="0" cellpadding="0" width="100%">
          <tr valign="middle">
            <td  style="padding:15px 0 15px 0;width:160px;background:#f0f0f0;font-size:18px; text-align:center">
              <span>Сумма:</span>
              <span style="color:#6bb24b"><?=preg_replace('/\.00$/','',round($ps['pre_sum'],2))?>&nbsp;руб.</span>
              <div style="padding-top:2px"><?=$ps['all_count'].'&nbsp;'.chel($ps['all_count'])?></div>
            </td>
            <td style="width:12px">&nbsp;</td>
            <td style="padding:5px 5px 8px 10px;background:#f0f0f0">
              <div style="padding-bottom:5px"><b>Рассылка в разделы:</b></div>
              <ul style="margin:0;padding-left:15px">
                <?
                  if($ps['prof_names'] && ($prof_names = explode(',', $ps['prof_names']))) {
                    foreach($prof_names as $name)
                      print("<li>{$name}</li>");
                  }
                  else
                    print("<li>Все разделы</li>");
                ?>
              </ul>
            </td>
          </tr>
        </table>
        <? if($om==masssending::OM_NEW) { ?>
          <form action="/siteadmin/masssending/" method="post">
		  <input type="hidden" id="status-<?=$ps['id']?>" name="status" value="123">
            <table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-top:30px">
              <tr valign="middle">
                <td style="padding-left:5px;width:340px;background:#f4fde1;color:black">
                  Все нормально
                </td>
                <td style="padding-left:10px">
                  <input type="image" name="Accept" src="/images/accept_rassilka.png" onclick="if(confirm('Разрешаем рассылку?')){document.getElementById('status-<?=$ps['id']?>').value='Accept';}else{return false;}" style="cursor: hand;" />
                </td>
              </tr>
              <tr valign="top">
                <td style="padding-top:5px">
									<div class="b-textarea">
                  	<textarea class="b-textarea__textarea" name="denied_reason"><?=$denied_reason?></textarea>
									</div>
                  <?=(isset($alert[$ps['id']]['denied_reason']) ? view_error($alert[$ps['id']]['denied_reason']) : '')?>
                </td>
                <td style="padding-top:5px; padding-left:10px;">
                  <input type="image" name="Deny" src="/images/deny_rassilka.jpg" onclick="if(confirm('Отменяем рассылку?')){document.getElementById('status-<?=$ps['id']?>').value='Deny';}else{return false;}" style="cursor: hand;" />
                  <input type="image" name="Change" src="/images/edit-mass.gif" style="margin-top:5px !important; display:block; cursor: hand;" onClick="masssendingEdit(<?=$ps['id']?>); return false;">
                </td>
              </tr>
            </table>
            <input name="id" type="hidden" value="<?=$ps['id']?>"/>
            <input name="action" type="hidden" value="Decide"/>
          </form>
        <? } else if($om==masssending::OM_DENIED && $ps['denied_reason']) { ?>
          <div style="margin:25px 0 2px 0"><b>Причина отказа:</b></div>
          <div><?=reformat2($ps['denied_reason'],30,0,1)?></div>
        <? } ?>
      </td>
    </tr>
    <tr valign="top">
      <td colspan="2" style="border-bottom:1px solid #c0c0c0;padding-top:15px">&nbsp;</td>
    </tr>
    <tr valign="top">
      <td colspan="2" style="padding-top:30px">&nbsp;</td>
    </tr>
  <? } ?>
</table>

<?php
  if ( $pages > 1 ) {
      $sHref = e_url( 'page', null );
      $sHref = e_url( 'page', '', $sHref );    
      echo get_pager2( $pages, $page, $sHref );
  }
?>

<div id="popup_masssending_edit" class="b-popup b-popup_center b-popup_width_560" >
   <input type="hidden" id="popup_masssending_edit_id" value="">
   <b class="b-popup__c1"></b>
   <b class="b-popup__c2"></b>
   <b class="b-popup__t"></b>
   <div class="b-popup__r">
     <div class="b-popup__l">
       <div  class="b-popup__body ">
        <div class="b-textarea">
          <textarea id="popup_masssending_edit_txt" rows="5" cols="80" name="" class="b-textarea__textarea b-textarea__textarea__height_140"></textarea>
        </div>
         <div class="b-popup__foot">
          <div class="b-buttons b-buttons_padtop_10">
            <a href="#" onclick="masssendingSave(); return false;" class="b-button b-button_flat b-button_flat_green">Сохранить изменения</a>
            <a href="#" class="b-buttons__link b-buttons__link_dot_0f71c8 b-popup__close">Закрыть без изменений</a>
          </div>
         </div>
       </div>
     </div>
   </div>
   <b class="b-popup__b"></b>
   <b class="b-popup__c3"></b>
   <b class="b-popup__c4"></b>
</div>
