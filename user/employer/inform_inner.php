<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/teams.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/notes.php");
	$recoms = new teams;
	$additinfo = $user->GetAdditInfo($user->login, $error);

  if($rating && ($rating instanceof rating) && $rating->data['user_id']==$user->uid)
    $rating_total = rating::round($rating->data['total']);
  else 
    $rating_total = rating::round($additinfo['rating']);

	$info_for_reg = @unserialize($user->info_for_reg);
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

<div class="b-layout b-layout_pad_20 b-layout_box">
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
			
			<?php if($val = $user->compname){
			?>
			<tr>
				<th>Компания:</th>
				<td><a name="compname"></a><?
                                $sResume = $user->isChangeOnModeration( $user->uid, 'compname' ) && $user->is_pro != 't' ? $stop_words->replace($user->compname) : $user->compname;
                                echo $sResume;
                                
                                if ( hasPermissions('users') ) { ?>
                                <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditProfile', '<?=$user->uid?>_0', 0, '', {'change_id': 0, 'ucolumn': 'compname', 'utable': 'employer'})">Редактировать</a>
                                <?php } 
                            ?>
                </td>
				<td></td>
			</tr>
			<?php }?>
			
			<?php
                            //if ( $user->uid == $uid ) {
                                $direct_external_links = $_SESSION['direct_external_links'];
                                $_SESSION['direct_external_links'] = 1;
                            //}
                        ?>
<?php if(is_view_contacts($user->uid)||(($_SESSION["uid"] && hasPermissions('users') && ($_SESSION['uid'] != $user->uid)) && (!(hasGroupPermissions('administrator', $user->uid) || hasGroupPermissions('moderator', $user->uid))))) { ?>
<?php include dirname(__FILE__)."/../inform_inner_contacts_fields.php"?>
<?php }//if?>
	<? if ($_SESSION['login'] == $user->login) { ?>
	<tr>
	   <td colspan="3"  style="padding-top: 14px; vertical-align:top; text-align:right">
	       <div class="change"><a href=""><img height="9" border="0" width="6" alt="" src="/images/ico_setup.gif" /></a> <a href="/users/<?=$_SESSION['login']?>/setup/info/">Изменить</a></div>
	   </td>
    </tr>
	<? } ?>

</tbody>
</table>
</div>

<!-- NEW -->

<?php if(is_view_contacts($user->uid)||(($_SESSION["uid"] && hasPermissions('users') && ($_SESSION['uid'] != $user->uid)) && (!(hasGroupPermissions('administrator', $user->uid) || hasGroupPermissions('moderator', $user->uid))))) { ?>
<? if (($user->resume || $user->resime_file)) { ?>
<div class="b-layout" style="padding:2px 20px;background-color: #E5EAF5;border-top: 1px solid #C6C6C6;color: #666666;font-weight: bold;"><a name="resume_file"></a>Дополнительная информация</div>

<div class="b-layout b-layout_pad_20 b-layout_box">
        <?php $sResume = $user->isChangeOnModeration( $user->uid, 'resume' ) && $user->is_pro != 't' ? $stop_words->replace($user->resume) : $user->resume; ?>
		<?=reformat($sResume)?>
        <?php if ( hasPermissions('users') ) { ?>
        <br/><br/>
        <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditProfile', '<?=$user->uid?>_0', 0, '', {'change_id': 0, 'ucolumn': 'resume', 'utable': 'employer'})">Редактировать</a>
        <?php } ?>
</div>
<? } ?>
<?php }//if?>

<?php if ( hasPermissions('users') && ($user->logo || $user->company) ) { ?>
<div class="b-layout" style="padding:2px 20px;background-color: #E5EAF5;border-top: 1px solid #C6C6C6;color: #666666;font-weight: bold;">О компании</div>


<?php if ( $user->logo ) { ?>
<div class="b-layout b-layout_pad_20 b-layout_box">
    <a name="logo"></a>
        <img src="<?=WDCPREFIX?>/users/<?=$user->login?>/logo/<?=$user->logo?>" border="0"  alt="'.$emp['compname'].'">
        <br/>
        <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditProfile', '<?=$user->uid?>_0', 0, '', {'change_id': 0, 'ucolumn': 'logo', 'utable': 'employer'})">Редактировать</a>
</div>
<?php } ?>
<?php if ( $user->company ) { ?>
<div class="b-layout b-layout_pad_20 b-layout_box">
    <a name="company"></a>
        <?php $sResume = $user->isChangeOnModeration( $user->uid, 'company' ) && $user->is_pro != 't' ? $stop_words->replace($user->company) : $user->company; ?>
        <?=reformat($sResume)?>
        <br/><br/>
        <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditProfile', '<?=$user->uid?>_0', 0, '', {'change_id': 0, 'ucolumn': 'company', 'utable': 'employer'})">Редактировать</a>
</div>
<?php } ?>
<? } ?>



<?
$limit = 10;

  $recs = $recoms->teamsInEmpFavorites($user->login, $error);
  
	if ($user->blocks[4] && $recs) { ?>
<div class="b-layout" style="padding:2px 20px;background-color: #E5EAF5;border-top: 1px solid #C6C6C6;color: #666666;font-weight: bold;">В избранном у работодателей</div>
<div class="b-layout b-layout_pad_20 b-layout_box">
      <div class=" izbr">
        <div class="izbr-odd">
		<?php
        $pt=0;
        $k=0;
        $allCnt = $realCnt = count($recs);
        if($allCnt>$limit) $allCnt = $limit;
        $iOdd = ceil($allCnt/2);
        notes::getNotesUsers($recs, $notes, 0, $iOdd,1);?>
            </div>
            <div class="izbr-even">
        <?php
        notes::getNotesUsers($recs, $notes, $iOdd, $allCnt, 1);
        $pt = 15;
        ?>
        </div><!--izbr-even-->
        </div><!-- izbr -->
</div>
  <? if($realCnt > $limit) { ?>
<div class="b-layout b-layout_pad_20">
        <a class="blue" href='/users/<?=$user->login?>/all/?mode=1'><b>Все (<?=$realCnt?>)</b></a>
</div>
  <? } ?>
<? } ?>

<?
  $recs = $recoms->teamsInFrlFavorites($user->login, $error);
	if ($user->blocks[5] && $recs) { ?>
<div class="b-layout" style="padding:2px 20px;background-color: #E5EAF5;border-top: 1px solid #C6C6C6;color: #666666;font-weight: bold;">В избранном у фрилансеров</div>


<div class="b-layout b-layout_pad_20 b-layout_box">
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
        notes::getNotesUsers($recs, $notes, 0, $iOdd, 2);?>
            </div>
            <div class="izbr-even">
        <?php
        notes::getNotesUsers($recs, $notes, $iOdd, $allCnt, 2);
        $pt = 15;
        ?>
        </div><!--izbr-even-->
        </div><!-- izbr -->
</div>
  <? if($realCnt > $limit) { ?>
<div class="b-layout b-layout_pad_20">
        <a class="blue" href='/users/<?=$user->login?>/all/?mode=2'><b>Все (<?=$realCnt?>)</b></a>
</div>
  <? } ?>
<? } ?>


<? $limit = 10; 
$recs = $recoms->teamsFavorites($user->login, $error, true);
?>
<? if ($user->blocks[1]) { ?>
<div class="b-layout" style="padding:2px 20px;background-color: #E5EAF5;border-top: 1px solid #C6C6C6;color: #666666;font-weight: bold;">Избранные</div>
<div class="b-layout b-layout_pad_20 b-layout_box">
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
</div>
  <? if($realCnt > $limit) { ?>
<div class="b-layout b-layout_pad_20">
        <a class="blue" href='/users/<?=$user->login?>/all/?mode=4'><b>Все (<?=$realCnt?>)</b></a>
</div>
  <? } ?>

<? }
   if ($user->blocks[2])
   { 
     $uid = get_uid();

     if(!($communes = commune::GetCommunes(NULL, $user->GetUid($e), NULL, commune::OM_CM_MY, $uid)))
       $communes = array();

     $commCnt = count($communes);
     if ($commCnt) {
?>
<div class="b-layout" style="padding:2px 20px;background-color: #E5EAF5;border-top: 1px solid #C6C6C6;color: #666666;font-weight: bold;">Создал сообщества (<?=$commCnt?>)</div>

<div class="b-layout b-layout_pad_20 b-layout_box">
      <table cellspacing="0" cellpadding="0" style="width:100%; border:0">
        <col/>
        <col/>
        <col style="width:10px"/>
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
            <td  style="text-align:right" class="commune-lo">
				<div id="idCommRating_<?=$comm['id']?>" class="b-voting b-voting_float_right">
                            <?=__commPrntRating($comm, $uid)?>
                </div>
					<div><?=__commPrntJoinButton($comm, $uid, "users/".$_SESSION['login']."/info/", 2)?></div>
					<div id="commSubscrButton_<?=$comm['id']?>"><?=__commPrntSubmitButton($comm, $uid, null, false)?></div>
            </td>
          </tr>       
        <tr><td colspan="3"><br/></td></tr>
        
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
                <?=__commPrntJoinButton($comm, $uid, "users/".$_SESSION['login']."/info/", 2)?>
              </div>
            </td>
            <td style="text-align:right">
              <div>
                <div id="idCommRating_<?=$comm['id']?>">
                  <?=__commPrntRating($comm, $uid)?>
                </div>
              </div>
            </td>
          </tr>
          <tr><td colspan="3"><br/></td></tr>
          <?php }?>
          
        <? } ?>
      </table>
</div>
<?    }
   }
   if ($user->blocks[3])
   { 
     require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");

     if(!($communes = commune::GetCommunes(NULL, NULL, $user->uid, commune::OM_CM_JOINED, $uid)))
       $communes = array();

     $commCnt = count($communes);

?>
<div class="b-layout" style="padding:2px 20px;background-color: #E5EAF5;border-top: 1px solid #C6C6C6;color: #666666;font-weight: bold;">Состоит в сообществах (<?=$commCnt?>)</div>
<div class="b-layout b-layout_pad_20 b-layout_box">
      <table cellspacing="0" cellpadding="0" style="width:100%; border:0">
        <col/>
        <col/>
        <col style="width:10px"/>
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
            <td style="text-align:right" class="commune-lo">
				<div id="idCommRating_<?=$comm['id']?>" class="b-voting b-voting_float_right">
                   <?=__commPrntRating($comm, $uid)?>
                </div>
				<div><?=__commPrntJoinButton($comm, $uid, "users/".$_SESSION['login']."/info/", 2)?></div>
				<div id="commSubscrButton_<?=$comm['id']?>"><?=__commPrntSubmitButton($comm, $uid, null, false)?></div>
			</td>
          </tr>
        <tr><td colspan="3"><br/></td></tr>
        <!-- NEW -->            
        
        
        <?php if(false){?>
          <tr style="vertical-align:top">
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
                <?=__commPrntJoinButton($comm, $uid, "users/".$_SESSION['login']."/info/", 2)?>
              </div>
            </td>
            <td  style="text-align:right">
              <div>
                <div id="idCommRating_<?=$comm['id']?>">
                  <?=__commPrntRating($comm, $uid)?>
                </div>
              </div>
            </td>
          </tr>
          <tr><td colspan="3"><br/></td></tr>
          <?php }?>
       <? } ?>
      </table>
</div>
<? } ?>
<span id="noteFormContent"></span>