<?
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/teams.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/notes.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/notes.common.php");
  $xajax->printJavascript('/xajax/');
  $recoms = new teams;
  $recs = array();
  $head = '';
  
if($_SESSION['uid']) {
    $note = notes::GetNotes($_SESSION['uid'], null, $error);

    if(count($note) > 0)
        foreach($note as $key=>$value) {
            $notes[$value['to_id']] = $value;
        }
    $type = 1;    
}
  
  switch($mode)
  {
    case 1:  // В избранном у работодателей.
      if(!is_emp($user->role) && ($user->blocks[3] || $uid==$user->uid)) $recs = $recoms->teamsInEmpFavorites($user->login, $error);
      if(is_emp($user->role) && ($user->blocks[4] || $uid==$user->uid)) $recs = $recoms->teamsInEmpFavorites($user->login, $error);
      $head = "<a href='/users/".$user->login."/'>".$user->uname." ".$user->usurname."</a> [<a href='/users/".$user->login."/'>".$user->login."</a>] в избранном у работодателей";
      break;
    case 2:  // В избранном у фрилансеров.
      if(!is_emp($user->role) && ($user->blocks[4] || $uid==$user->uid)) $recs = $recoms->teamsInFrlFavorites($user->login, $error);
      if(is_emp($user->role) && ($user->blocks[5] || $uid==$user->uid)) $recs = $recoms->teamsInFrlFavorites($user->login, $error);
      $head = "<a href='/users/".$user->login."/'>".$user->uname." ".$user->usurname."</a> [<a href='/users/".$user->login."/'>".$user->login."</a>] в избранном у фрилансеров";
      break;
    case 3:  // Избранные.
      if($user->blocks[5] || $uid==$user->uid)
        $recs = $recoms->teamsFavorites($user->login, $error, true);
      $head = "Избранные <a href='/users/".$user->login."/'>".$user->uname." ".$user->usurname."</a> [<a href='/users/".$user->login."/'>".$user->login."</a>]";
      break;
    case 4:  // Моя команда
      if($user->blocks[1] || $uid==$user->uid)
        $recs = $recoms->teamsFavorites($user->login, $error, true);
      $head = "Избранные <a href='/users/".$user->login."/'>".$user->uname." ".$user->usurname."</a> [<a href='/users/".$user->login."/'>".$user->login."</a>]";
      break;
  }

  if(!$recs)
    $recs = array();
  
  
  
  //Получаем is_profi
  $ids = array();
  if ($recs) {
      foreach($recs as $rec) {
          if(is_emp($rec['role'])) {
              continue;
          }
          
          $ids[] = $rec['uid'];
      }
  }

  if($ids) {
      $recsProfi = $user->getUsersProfi($ids);
  }
  
  
?>

<h1 class="b-page__title"><?=$head?></h1>

      <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_10">
      <? 
         $i=$k=0;
         $colCnt=3;
         foreach ($recs as $rec) {
            
            if(isset($recsProfi[$rec['uid']])) {
                $rec['is_profi'] = $recsProfi[$rec['uid']];
            } 
             
            $k++;
            if(count($notes[$rec['uid']]) > 0) {
                $note = $notes[$rec['uid']];
            } else {
                $note = false;
            }
           if($i % $colCnt == 0) {
             if($i)
               print('</tr>');
             print('<tr class="b-layout__tr">');
           }
           
           $cls = is_emp($rec['role'])?"emp":"frl";
    
      ?>
        <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_width_33ps b-layout__td_width_full_ipad <?= ($k==3?'last':'')?>">
              <div id="td_user_<?=$rec['uid']?>" class="izbr-item">
                <a href="/users/<?=$rec['login']?>" title="<?=$rec['uname']?> <?=$rec['usurname']?>"><?=view_avatar($rec['login'], $rec['photo'],1,1,'b-pic b-pic_fl')?></a>
                <div class="izbr-text">
                  <span id="elm-offset-<?= $rec['uid']."-".$type?>"></span> 
                  <span class="user-inf">
                    <span class="<?=$cls?>name11"><a href="/users/<?=$rec['login']?>/" class="<?=$cls?>name11" title="<?=($rec['uname']." ".$rec['usurname'])?>"><?=($rec['uname']." ".$rec['usurname'])?></a> [<a href="/users/<?=$rec['login']?>/" class="<?=$cls?>name11" title="<?=$rec['login']?>"><?=$rec['login']?></a>]</span> <?= view_mark_user($rec);?>
                  </span>
                  <?php if(!is_emp($rec['role'])) {?>
                        Специализация: <?= professions::GetProfNameWP($rec['spec'], ' / ', "не указано", "lnk-666", true)?>
                    <?php }//if?>
                  <?php if($_SESSION['uid'] && $_SESSION['uid'] != $rec['uid']) {?>
                  <div class="userFav_<?=$rec['uid']?>">
                        <?php if($note === false) { ?>
                        <div class="sent-mark"><a href="javascript:void(0)" onclick="xajax_getNotesForm(<?= $rec['uid']?>, false, <?= $type?>);">Оставить заметку</a>&nbsp;<span></span></div>
                        <?php } else { //if ?>
                            <?php include ("tpl.notes-textitem.php"); ?>
                        <?php } //else ?>
                         
                  </div>
                  <?php } //if?>
                </div>
              </div>
              
        </td>
    <?  if($k==3) $k = 0; 
        $i++; } 
        if($i) print('</tr>');
      ?>
      </table>
      <style type="text/css">
	  .izbr-item{ width:auto !important;}
	  </style>
<span id="noteFormContent"></span>

