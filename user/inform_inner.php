<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/teams.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/notes.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/user_langs.php");
	$recoms = new teams;
	$additinfo = $user->GetAdditInfo($user->login, $error);
	$languages = users::GetUserLangs($user->uid);
	

  if($rating && ($rating instanceof rating) && $rating->data['user_id']==$user->uid)
    $rating_total = rating::round($rating->data['total']);
  else 
    $rating_total = rating::round($additinfo['rating']);

	$info_for_reg = unserialize($user->info_for_reg);
	$reg_string = "только для <A class=\"blue\" href=\"/registration/\">зарегистрированных</A>";
	
	if($_SESSION['uid']) {
    	$note = notes::GetNotes($_SESSION['uid'], null, $error);
    	
    	if(count($note) > 0)
        	foreach($note as $key=>$value) {
        	    $notes[$value['to_id']] = $value;
        	}
	}
    
    $stop_words = new stop_words( hasPermissions('users') );
?>
<!-- NEW -->

<!-- NEW -->
<script type="text/javascript">
window.addEvent('domready',
	function() {
        $$('.izbr-choose li a').addEvent('click', function(){
            this.getParent('li').getParent('.izbr-choose').getElements('li').removeClass('active');
            this.getParent('li').addClass('active');
            return false;
        });
    }
);

</script>
<table width="100%" cellspacing="0" cellpadding="0" class="b-information-summary">
<tbody>
	<tr>
		<td style="width:19px">&nbsp;</td>
		<td>
		<table class="user-info-tbl">
			<colgroup>
				<col width="170" />
				<col />
				<col width="20" />
			</colgroup>
			<tbody>
			<tr class="first">
				<th>Рейтинг:</th>
				<td><?=$rating_total?></td>
				<td></td>
			</tr>
			<tr>
				<th>Посещаемость:</th>
				<td><?=$additinfo['hits']?></td>
				<td></td>
			</tr>
			<? if ($user->birthday && $user->birthday > "1910-01-01") { ?>
			<tr>
				<th>Дата рождения:</th>
				<td>
	
				<?=dateFormat("d.m.y",$user->birthday)?> (Возраст: <?=ElapsedYears(strtotimeEx($user->birthday))?>)
	
				</td>
				<td></td>
			</tr>
		<? } ?>
                        <?php if($val = $user->sex){?>
			<tr>
				<th>Пол:</th>
				<td><?
                			if($user->sex == 't'){
                			    echo 'Мужской';
                			} else if($user->sex == 'f'){
                                            echo 'Женский';
                                        } else {
                                            echo 'не указан';
                                        }
                            ?>
                </td>
				<td>&nbsp;</td>
			</tr>
			<?php }?>
                        
			<tr>
				<th>На сайте:</th>
				<td><?=ElapsedMnths(strtotime($user->reg_date))?></td>
				<td></td>
			</tr>
			<tr>
				<th>Дата регистрации:</th>
				<td><?=date('d.m.Y', strtotime($user->reg_date))?></td>
				<td></td>
			</tr>
			<?php if($user->country){?>
			<tr>
				<th>Местонахождение:</th>
				<td>			

			<?=country::GetCountryName($user->country); if ($user->city) { ?>, <?=city::GetCityName($user->city); } ?>

                </td>
				<td></td>
			</tr>
			<?php }?>
<?php if(is_view_contacts($user->uid)||(($_SESSION["uid"] && hasPermissions('users') && ($_SESSION['uid'] != $user->uid)) && (!(hasGroupPermissions('administrator', $user->uid) || hasGroupPermissions('moderator', $user->uid))))) { ?>
<?php include dirname(__FILE__)."/inform_inner_contacts_fields.php"?>
<?php }//if?>
			<?php
                            //if ( $user->uid == $uid ) {
                                $direct_external_links = $_SESSION['direct_external_links'];
                                $_SESSION['direct_external_links'] = 1;
                            //}
                        ?>
			<tr>
				<th>Языки:</th>
				<td><? if ( is_array($languages) ) {
                           $text = array();
                           $i = 0;
                           foreach($languages as $lang  ) {
                           	    $quality = "средний";
                           	    switch ($lang["quality"]) {
                           	    	case 1:
                           	    	   $quality = "начальный";
                           	    	   break;
                                    case 3:
                                        $quality = "продвинутый";
                                        break;
                                    case 4:
                                        $quality = "родной";
                                        break;
                           	    }
                           	    if ($i > 0) {
                                    $text[] = mb_strtolower($lang["name"], "CP1251")." (".$quality.")";
                           	    } else {
                                    $text[] = $lang["name"]." (".$quality.")";
                           	    }
                                $i++;
                           }
                           echo join(", ", $text);
                      } else {
                            echo "Пользователь не указал языки, которыми владеет";
                      }?></td>
				<td></td>
			</tr>
			
	<? if ($_SESSION['login'] == $user->login) { ?>
	<tr>
	   <td colspan="3" valign="top" style="padding-top: 14px; text-align:right">
	       <div class="change"><div style="padding-right:19px;"><a href=""><img height="9"  width="6" alt="" src="/images/ico_setup.gif" /></a> <a href="/users/<?=$_SESSION['login']?>/setup/info/">Изменить</a></div></div>
	   </td>
    </tr>
	<? } ?>

</tbody>
</table>	
</td>
<td style="width:19px">&nbsp;</td>
</tr>
</tbody>
</table>

<!-- NEW -->

<!-- NEW -->

<?php if(is_view_contacts($user->uid)||(($_SESSION["uid"] && hasPermissions('users') && ($_SESSION['uid'] != $user->uid)) && (!(hasGroupPermissions('administrator', $user->uid) || hasGroupPermissions('moderator', $user->uid))))) { ?>
<?php if (($user->resume || $user->resume_file)) { 
$edit_date = strtotime($user->resume_edit_date);

if($edit_date){
	$alt = "Отредактировано ".date('d.m.Y в H.i', $edit_date);
	$pen_ico = strtotime('+7 days', $edit_date) > time() ? '/images/ico-e-a.png' : '/images/ico-e-o.png';
	$pen_html = '<img src="'.$pen_ico.'" alt="'.$alt.'" title="'.$alt.'" />';
}else{
	$pen_html = false;
}
?>
<table width="100%"  cellspacing="0" cellpadding="0" class="b-information-clause-title" 	>
<tr>
	<td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
	<td class="brdtop">Резюме&nbsp;&nbsp;<?php echo $pen_html?>&nbsp;&nbsp;</td>
	
    <td  style="text-align:right; width:350px" class="brdtop"><a name="resume_file"></a>
        <? if ($user->resume_file) { ?>
        <a href="<?=WDCPREFIX?>/users/<?=$user->login?>/resume/<?=$user->resume_file?>" class="pages">Скачать резюме</a>
        <? } ?>
        <?php if ( hasPermissions('users') ) { ?>
        <?$user->resume_file ? "|":"";?> <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditProfile', '<?=$user->uid?>_0', 0, '', {'change_id': 0, 'ucolumn': 'resume_file', 'utable': 'freelancer'})">Редактировать</a>
        <?php } ?>
    </td>
    
	<td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="19"  class="b-information-clause-content">
<tr>
	<td style="padding:19px">
        <?php $sResume = $user->isChangeOnModeration( $user->uid, 'resume' ) && $user->is_pro != 't' ? $stop_words->replace($user->resume) : $user->resume; ?>
		<?=($user->resume)? reformat($sResume, 90) : "<br />"?>
        <?php if ( hasPermissions('users') ) { ?>
        <br/><br/>
        <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditProfile', '<?=$user->uid?>_0', 0, '', {'change_id': 0, 'ucolumn': 'resume', 'utable': 'freelancer'})">Редактировать</a>
        <?php } ?>
	</td>
</tr>
</table>
<? } ?>
<?php }//if?>

<? if ($user->konk && $user->blocks[1]) { ?>
<table width="100%"  cellspacing="0" cellpadding="0" style="background:#ffe5d5" class="b-information-clause-title" >
<tr>
	<td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
	<td class="brdtop"><a name="konk"></a>Участие в конкурсах и награды</td>
	<td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="19"  class="b-information-clause-content">
<tr>
	<td style="padding:19px">
        <?php $sKonk = $user->isChangeOnModeration( $user->uid, 'konk' ) && $user->is_pro != 't' ? $stop_words->replace($user->konk) : $user->konk; ?>
		<?=reformat($sKonk, 90)?>
        
        <?php if ( hasPermissions('users') ) { ?>
        <br/><br/>
        <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditProfile', '<?=$user->uid?>_0', 0, '', {'change_id': 0, 'ucolumn': 'konk', 'utable': 'freelancer'})">Редактировать</a>
        <?php } ?>
	</td>
</tr>
</table>
<? } ?>

<? if ($user->clients && $user->blocks[2]) { ?>
<table width="100%"  cellspacing="0" cellpadding="0" class="b-information-clause-title" >
<tr>
	<td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
	<td class="brdtop">Крупные клиенты</td>
	<td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="19" class="b-information-clause-content">
<tr>
	<td style="padding:19px">
		<?=$user->clients?>
	</td>
</tr>
</table>
<? } ?>

<?
  
  $limit = 10;

  $recs = $recoms->teamsInEmpFavorites($user->login, $error);
	if ($user->blocks[3] && $recs) { ?>
<table width="100%"  cellspacing="0" cellpadding="0" class="b-information-clause-title" >
<tr>
	<td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
	<td class="brdtop">В избранном у работодателей</td>
	<td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="19" class="b-information-clause-content">
	<tr>
    <td style="padding:19px">
    <div class="izbr">
    <div class="izbr-odd">
    <?php
    $pt=0;
    $k=0;
    $allCnt = $realCnt = count($recs);
    if($allCnt>$limit) $allCnt = $limit;
    $iOdd = ceil($allCnt/2);
    notes::getNotesUsers($recs, $notes, 0, $iOdd, 1);
    ?>
        </div>
        <div class="izbr-even">
    <?php
     notes::getNotesUsers($recs, $notes, $iOdd, $allCnt, 1);
     ?>
    </div><!--izbr-even-->
    </div><!-- izbr -->
    </td>
	</tr>

  <? if($realCnt > $limit) { ?>
    <tr>
      <td style="padding: 0 19px 19px">
        <a class="blue" href='/users/<?=$user->login?>/all/?mode=1'><b>Все (<?=$realCnt?>)</b></a>
      </td>
    </tr>
  <? } ?>
</table>
<? } ?>

<?
  $recs = $recoms->teamsInFrlFavorites($user->login, $error);
	if ($user->blocks[4] && $recs) { ?>
<table width="100%"  cellspacing="0" cellpadding="0" class="b-information-clause-title" >
<tr>
	<td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
	<td class="brdtop">В избранном у фрилансеров</td>
	<td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="19" class="b-information-clause-content">
	<tr>
    <td style="padding:19px">
    <div class="izbr">
    <div class="izbr-odd">
<?php

    //Получаем is_profi
    $ids = array();
    $recsProfi = array();
    foreach($recs as $rec) {
        if(is_emp($rec['role'])) {
            continue;
        }

        $ids[] = $rec['uid'];
    }

    if($ids) {
        $recsProfi = $user->getUsersProfi($ids);
    }    
    
    $pt=0;
    $k=0;
    $allCnt = $realCnt = count($recs);
    if($allCnt>$limit) $allCnt = $limit;
    $iOdd = ceil($allCnt/2);
    notes::getNotesUsers($recs, $notes, 0, $iOdd, 2);
    ?>
        </div>
        <div class="izbr-even">
    <?php
     notes::getNotesUsers($recs, $notes, $iOdd, $allCnt, 2);
     ?>
    </div><!--izbr-even-->
    </div><!-- izbr -->
    </td>
	</tr>
  <? if($realCnt > $limit) { ?>
    <tr>
      <td style="padding: 0 19px 19px;">
        <a class="blue" href='/users/<?=$user->login?>/all/?mode=2'><b>Все (<?=$realCnt?>)</b></a>
      </td>
    </tr>
  <? } ?>
</table>
<? } ?>

<? 
  $recs = $recoms->teamsFavorites($user->login, $error, true);
  //$notes = notes::GetNotes($_SESSION['uid'], "", $error);
  //var_dump($notes);
	if ($user->blocks[5] && $recs) { ?>
<table width="100%"  cellspacing="0" cellpadding="0" class="b-information-clause-title" >
<tr>
	<td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
	<td class="brdtop">Избранные</td>
	<td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="19" class="b-information-clause-content">
	<tr>
		<td style="padding:19px">
		<div class=" izbr">
        <div class="izbr-odd">
<?php
        
        //Получаем is_profi
        $ids = array();
        $recsProfi = array();
        foreach($recs as $rec) {
            if(is_emp($rec['role'])) {
                continue;
            }

            $ids[] = $rec['uid'];
        }

        if($ids) {
            $recsProfi = $user->getUsersProfi($ids);
        }          
        
        
        $pt=0;
        $k=0;
        $allCnt = $realCnt = count($recs);
        if($allCnt>$limit) $allCnt = $limit;
        $iOdd = ceil($allCnt/2);
        notes::getNotesUsers($recs, $notes, 0, $iOdd, 3);?>
            </div>
            <div class="izbr-even">
        <?php
        notes::getNotesUsers($recs, $notes, $iOdd, $allCnt, 3);
        $pt = 15;
        ?>
        </div><!--izbr-even-->
        </div><!-- izbr -->
		</td>
	</tr>
  <? if($realCnt > $limit) { ?>
    <tr>
      <td style="padding: 0 19px 19px">
        <a class="blue" href='<?=$rpath?>all/?mode=3'><b>Все (<?=$realCnt?>)</b></a>
      </td>
    </tr>
  <? } ?>
</table>
<? }
   if ($user->blocks[6])
   { 
     require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");

     if(!($communes = commune::GetCommunes(NULL, $user->uid, NULL, commune::OM_CM_MY, $uid)))
       $communes = array();

     $commCnt = count($communes);
     if ($commCnt) {
?>
<table width="100%" cellspacing="0" cellpadding="0" class="b-information-clause-title" >
<tr>
  <td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
  <td class="brdtop">Создал сообщества (<?=$commCnt?>)</td>
  <td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="19" class="b-information-clause-content">
  <tr>
    <td style="padding:19px">
      <table width="100%"  cellspacing="0" cellpadding="0">
        <col />
        <col />
        <col style="width:10px" />
        <? foreach($communes as $comm) {
              
             $i++;
             // Название.
             $name = "<a href='".getFriendlyURL("commune_commune", $comm['id'])."' class='blue' style='font-size:20px'>".reformat($comm['name'], 25, 1)."</a>";
             $descr = reformat($comm['descr'], 25, 1);
             // Сколько участников.
             $mAcceptedCnt = $comm['a_count'] - $comm['w_count'] + 1;
             $mCnt = $mAcceptedCnt.' участник'.getSymbolicName($mAcceptedCnt, 'man');
        ?>
        
        <!-- NEW -->
        
        
        <tr  style="vertical-align:top">
            <td style="width:200px">

              <?=__commPrntImage($comm, 'author_')?>
            </td>
            <td style="padding:0 0 0 20px">
              <div>
              <?=$name?>
              </div>
              <div><?=$descr?></div>
              <div style="margin-top:10px">
               <?=commune::GetJoinAccessStr($comm['restrict_type'], TRUE)?> 
              </div>
              <div style="margin-top:25px">
                <?=$mCnt?>

              </div>

              <div style="margin-top:4px">
                <?=__commPrntAge($comm)?>
              </div>
             </td>
            <td style="text-align:center" class="commune-lo">
				<div id="idCommRating_<?=$comm['id']?>" class="b-voting b-voting_float_right">
                            <?=__commPrntRating($comm, $uid)?>
                </div>
                <? if (!$comm['is_blocked'] || $user_mod & commune::MOD_ADMIN) { ?>
					<div><?=__commPrntJoinButton($comm, $uid, "users/".$_SESSION['login']."/info/", 2)?></div>
					<div id="commSubscrButton_<?=$comm['id']?>" style="text-align:right !important;"><?=__commPrntSubmitButton($comm, $uid, null, false)?></div> 
				<?php }?>
            </td>
          </tr>
        <tr><td colspan="3"><br /></td></tr>
        
        <!-- NEW -->
        <?php if(false){?>
          <tr  style="vertical-align:top">
            <td style="width:200px">
              <?=__commPrntImage($comm, 'author_')?>
            </td>
            <td style="padding:0 0 0 20px">
              <div>
                <?=$name?>
              </div>
              <div>
                <?=$descr?>
              </div>
              <div style="margin-top:10px">
                <?=commune::GetJoinAccessStr($comm['restrict_type'], TRUE)?>
              </div>
              <div style="margin-top:25px">
                <?=$mCnt?>
              </div>
              <div style="margin-top:4px">
                <?=__commPrntAge($comm)?>
              </div>
              <div style="margin-top:15px">
                <?=__commPrntJoinButton($comm, $user->uid, "users/".$_SESSION['login']."/info/", 2)?>
              </div>
            </td>
            <td style="text-align:right">
              <div>
                <div id="idCommRating_<?=$comm['id']?>">
                  <?=__commPrntRating($comm, $user->uid)?>
                </div>
              </div>
            </td>
          </tr>
          <tr><td colspan="3"><br/></td></tr>
          <?php }?>
       <? } ?>
      </table>
    </td>
  </tr>
</table>
<?     }
   }
   if ($user->blocks[7])
   { 
     require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");

     if(!($communes = commune::GetCommunes(NULL, NULL, $user->uid, commune::OM_CM_JOINED, $uid)))
       $communes = array();

     $commCnt = count($communes);

?>
<table width="100%"  cellspacing="0" cellpadding="0" class="b-information-clause-title" >
<tr>
  <td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
  <td class="brdtop">Состоит в сообществах (<?=$commCnt?>)</td>
  <td  style="width:19px; height:20px" class="brdtop">&nbsp;</td>
</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="19" class="b-information-clause-content">
  <tr>
    <td style="padding:19px">
      <table width="100%" cellspacing="0" cellpadding="0">
        <col />
        <col />
        <col style="width:10px" />
        <? foreach($communes as $comm) {
              
             $i++;
             // Название.
             $name = "<a href='".getFriendlyURL("commune_commune", $comm['id'])."' class='blue' style='font-size:20px'>".reformat($comm['name'], 25, 1)."</a>";
             $descr = reformat($comm['descr'], 25, 1);
             // Сколько участников.
             $mAcceptedCnt = $comm['a_count'] - $comm['w_count'] + 1;
             $mCnt = $mAcceptedCnt.' участник'.getSymbolicName($mAcceptedCnt, 'man');
        ?>
        
        
        
         <tr style="vertical-align:top">
            <td style="width:200px">

              <?=__commPrntImage($comm, 'author_')?>
            </td>
            <td style="padding:0 0 0 20px">
              <div>
              <?=$name?>
              </div>
              <div><?=$descr?></div>
              <div style="margin-top:10px">
               <?=commune::GetJoinAccessStr($comm['restrict_type'], TRUE)?> 
              </div>
              <div style="margin-top:25px">
                <?=$mCnt?>
              </div>

              <div style="margin-top:4px">
                <?=__commPrntAge($comm)?>
              </div>
             </td>
            <td style="text-align:center" class="commune-lo">
				<div id="idCommRating_<?=$comm['id']?>"  class="b-voting b-voting_float_right">
                            <?=__commPrntRating($comm, $uid)?>
                </div>
					<div><?=__commPrntJoinButton($comm, $uid, "users/".$_SESSION['login']."/info/", 2)?></div>
					<div id="commSubscrButton_<?=$comm['id']?>" style="text-align:right !important;"><?=__commPrntSubmitButton($comm, $uid, null, false)?></div> 
            </td>
          </tr>
        <tr><td colspan="3"><br /></td></tr>
        
        
        
        
        
        <?php if(false){?>
          <tr  style="vertical-align:top">
            <td style="width:200px">
              <?=__commPrntImage($comm, 'author_')?>
            </td>
            <td style="padding:0 0 0 20px">
              <div>
                <?=$name?>
              </div>
              <div>
                <?=$descr?>
              </div>
              <div style="margin-top:10px">
                <?=commune::GetJoinAccessStr($comm['restrict_type'], TRUE)?>
              </div>
              <div style="margin-top:25px">
                <?=$mCnt?>
              </div>
              <div style="margin-top:4px">
                <?=__commPrntAge($comm)?>
              </div>
              <div style="margin-top:15px">
                <?=__commPrntJoinButton($comm, $user->uid, "users/".$_SESSION['login']."/info/", 2)?>
              </div>
            </td>
            <td style="text-align:center">
              <div>
                <div id="idCommRating_<?=$comm['id']?>">
                  <?=__commPrntRating($comm, $user->uid)?>
                </div>
              </div>
            </td>
          </tr>
          <?php }?>
          <tr><td colspan="3"><br /></td></tr>
       <? } ?>
      </table>
    </td>
  </tr>
</table>
<? } ?>
<span id="noteFormContent"></span>
                        
