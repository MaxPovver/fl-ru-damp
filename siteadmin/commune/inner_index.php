<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if(!(hasPermissions('adm') && hasPermissions('communes'))) {
  exit;
}
  if(!($groups = commune::GetGroups()))
    $groups = array();

  $grpCnt = count($groups);

?>
<script type="text/javascript">

  function reOrder(__this, direct)
  {
    var oCurr = __this.offsetParent.parentNode;
    var tbl = oCurr.parentNode;
    var thisIndex = oCurr.rowIndex;
    var prev = tbl.rows.item(thisIndex + direct);
    var nCurr = prev.cloneNode(true);
    tbl.replaceChild(oCurr, prev);
    var newx = tbl.insertRow(thisIndex);
    tbl.replaceChild(nCurr, newx);
    var oCells = oCurr.cells;
    var nCells = nCurr.cells;
    var oUp = oCells.item(2);
    var nUp = nCells.item(2);
    var oDown = oCells.item(3);
    var nDown = nCells.item(3);
    var n = nUp.innerHTML;
    nUp.innerHTML = oUp.innerHTML;
    oUp.innerHTML = n;
    n = nDown.innerHTML;
    nDown.innerHTML = oDown.innerHTML;
    oDown.innerHTML = n;
  }


</script>
<strong>Сообщества</strong><br><br>
<? if ($_GET['result']=='success') { ?>
  <div>
    <img src="/images/ico_ok.gif" alt="" border="0" height="18" width="19"/>&nbsp;&nbsp;Готово!
  </div>
<? } ?>
<? if ($error) { ?>
  <div>
    <?=view_error($error)?>
  </div>
<? } ?>
<br><br>
<form action="/siteadmin/commune/" method="post">
  <input name="action" type="hidden" value="Insert"/>
  <table width="100%" border="0" cellspacing="0" cellpadding="2" class="tbl-pad5">
    <col style="width:70px"/>
    <col/>
    <tr>
      <td style="width:50px;">
        Название
      </td>
      <td>
							<div class="b-input b-input_width_200">
        <input class="b-input__text" maxlength="<?=commune::GROUP_NAME_MAX_LENGTH?>" type="text" name="name"/>
							</div>
      </td>
    </tr>
    <? if ($alert['name']) { ?>
      <tr>
        <td colspan="2">
          <?=view_error($alert['name'])?>
        </td>
      </tr>
    <? } ?>
    <tr valign="top">
      <td>
        Описание
      </td>
      <td>
							<div class="b-input">
        <input class="b-input__text" maxlength="<?=commune::GROUP_DESCR_MAX_LENGTH?>" type="text" name="descr"/>
							</div>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <br/>
        <br/>
        <input style="width:150px" type="submit" value="Добавить раздел"/>
        <br/><br/>
      </td>
    </tr>

  </table>
</form>
<div style="border-top:1px dotted #7f9db9">&nbsp;</div> 
<br/>
<form id="idEditForm" action="/siteadmin/commune/" method="post">
  <input name="action" type="hidden" value="Update"/>
  <table id="idEditTbl" width="100%" border="0" cellspacing="0" cellpadding="2" style="table-layout:fixed;border:6px solid white" class="tbl-pad5">
    <col style="width:180px"/>
    <col/>
    <col style="width:25px"/>
    <col style="width:25px"/>
    <col style="width:25px"/>
    <? for($i=0; $i<$grpCnt; $i++) {
    
         $grp = $groups[$i]
         // style="background:#7f9db9"
    ?>
      <tr valign="middle">
        <td>
          <input name="id[]" type="hidden" value="<?=$grp['id']?>"/>
							<div class="b-input">
          <input class="b-input__text" maxlength="<?=commune::GROUP_NAME_MAX_LENGTH?>" name="name[]" type="text" value="<?=$grp['name']?>"/>
       </div>
							   <? 
             if ($alert['name[]'][$grp['id']]) {
               print(view_error($alert['name[]'][$grp['id']]));
             } 
          ?>
        </td>
        <td>
							<div class="b-input">
          <input class="b-input__text" maxlength="<?=commune::GROUP_DESCR_MAX_LENGTH?>" type="text" name="descr[]" value="<?=$grp['descr']?>"/>
       </div>
        </td>
        <? if(!$i) { ?>
          <td align="center">&nbsp;
            
          </td>
        <? } else { ?>
          <td align="center">
            <a href="#"><span
                    onmouseover="this.style.background='#d0d0d0'" onmouseout="this.style.background=''"
                    onclick="reOrder(this,-1)"
              ><img src="/images/ico_up.gif" alt="" width="9" height="9" border="0"></span></a>
          </td>
        <? } ?>
        <? if($i==$grpCnt-1) { ?>
          <td>&nbsp;
            
          </td>
        <? } else { ?>
          <td>
            <a href="#"><span
                    onmouseover="this.style.background='#d0d0d0'" onmouseout="this.style.background=''"
                    onclick="reOrder(this,1)"
              ><img src="/images/ico_down.gif" alt="" width="9" height="9" border="0"></span></a>
          </td>
        <? } ?>
        <td align="center">
          <a id="del_comm_<?=$grp['id']?>" href="?id=<?=$grp['id']?>&action=Delete" onclick="return addTokenToLink('del_comm_<?=$grp['id']?>', 'Вы уверены?');"><img src="/images/ico_close.gif" alt="удалить" width="9" height="9" border="0"/></a>
        </td>
      </tr>
    <? } ?>
    <tr>
      <td colspan="5" align="right">
        <br/>
        <br/>
        <input style="width:150px" type="submit" value="Сохранить"/>
      </td>
    </tr>
  </table>
</form>
