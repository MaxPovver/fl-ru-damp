<?php
                                 ////////////

   global $id, $comm, $alert, $user_mod;

  // Все админы (модераторы, упрявляторы).
  if(!($admins = commune::GetMembers($id, commune::MEMBER_ADMIN | commune::JOIN_STATUS_ACCEPTED))) // Хотя модераторы всегда is_accepted.
    $admins = array();

  $adminCnt = count($admins);
?>

  <table border="0" width="100%" cellpadding="0" cellspacing="0">
    <tr valign="middle">
      <td>
        <h1><a style="color:#666" href="?id=<?=$comm['id']?>">Сообщество &laquo;<?=$comm['name']?>&raquo;</a></h1>
      </td>
    </tr>
  </table>

  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr valign="top">
      <td height="400" bgcolor="#FFFFFF" class="box commune" style="padding:35px 25px 20px 30px">
        <table border="0" width="100%" cellpadding="0" cellspacing="0">
          <col style="width:100px"/>
          <col/>
          <tr valign="top">
            <td>
              <b>Администрация</b>
              <? if($user_mod & (commune::MOD_ADMIN | commune::MOD_COMM_AUTHOR | commune::MOD_COMM_MANAGER)) { ?> <?// Хотя comm_manager сюда не попадет. На всякий случай... ?>
                <br/><br/>
                <a class="blue" href="?id=<?=$id?>&site=Admin.members"><b>Участники</b></a>
              <? } ?>
            </td>
            <td style="padding-left:40px">
              <table border="0" width="100%" cellpadding="0" cellspacing="0">
                <col/>
                <col style="width:165px"/>
                <col style="width:195px"/>
                <col style="width:125px"/>
                <? if($adminCnt <= commune::MAX_ADMIN_COUNT) { ?>
                  <tr valign="top">
                    <td colspan="4" style="padding:0 0 50px 10px">
                      <div>
                        <b>Добавить в администрацию</b> <? // !!! А если забанен?>
                      </div>
                      <div style="padding-top:2px">
                        Не больше шести человек
                      </div>
                      <div style="padding-top:10px">
                        <form action="." method="get">
                          <input type="hidden" name="id" value="<?=$id?>"/>
                          <input type="hidden" name="site" value="Admin"/>
                          <input type="hidden" name="action" value="do.Add.admin"/>
                          <input type="text" name="user_login" style="width:200px"/>&nbsp;&nbsp;
                          <input type="submit" style="width:90px" value="Добавить"/>
                          <?=(isset($alert['user_login']) ? view_error($alert['user_login']) : '')?>
                        </form>
                      </div>
                    </td>
                  </tr>
                <? } ?>
                <? if($adminCnt) { ?>
                  <tr valign="middle">
                    <td align="center">&nbsp;</td>
                    <td align="center">
                      <b>Модерирование</b>
                    </td>
                    <td align="center">
                      <b>Управление людьми</b><br/>
                      Бан/Приглашения/Удаления
                    </td>
                    <td align="center">&nbsp;</td>
                  </tr>
                <? } ?>
                <tr valign="top">
                  <td colspan="4">&nbsp;</td>
                </tr>
                <? if($adminCnt) { ?>
                  <form action="?id=<?=$id?>&site=Admin" method="post">
                    <input type="hidden" name="action" value="do.Update.admin"/>
                    <? foreach($admins as $adm) {
                    ?>
                      <input type="hidden" name="member_id[]" value="<?=$adm['id']?>"/>
                      <tr valign="middle">
                        <td align="left" style="padding:15px 0 15px 20px;border-top: 1px solid #DCDBD9;">
                          <table border="0" width="100%" cellpadding="0" cellspacing="0">
                            <col style="width:10px"/>
                            <col/>
                            <tr valign="top">
                              <td>
                                <?=__commPrntUsrAvtr($adm)?>
                              </td>
                              <td align="left" style="padding:0 0 0 15px">
                                <?=__commPrntUsrInfo($adm)?>
                                <div style="padding-top:4px">
                                  <textarea name="note[]" class="ba bClr" style="overflow:hidden;width:100%;height:40px"><?=$adm['note']?></textarea>
                                <div>
                              </td>
                            </tr>
                          </table>
                        </td>
                        <td style="padding:15px 0 15px 15px;border-top: 1px solid #DCDBD9;" align="center">
                          <input type="checkbox" <?=($adm['is_moderator']=='t' ? 'checked ' : '')?>
                                 onclick="this.nextSibling.value=(this.checked ? 1 : 0)"
                          /><input name="is_moderator[]" type="hidden" value="<?=($adm['is_moderator']=='t' ? 1 : 0)?>"/>
                        </td>
                        <td style="border-top: 1px solid #DCDBD9;" align="center">
                          <input type="checkbox" <?=($adm['is_manager']=='t' ? 'checked ' : '')?>
                                 onclick="this.nextSibling.value=(this.checked ? 1 : 0)"
                          /><input name="is_manager[]" type="hidden" value="<?=($adm['is_manager']=='t' ? 1 : 0)?>"/>
                        </td>
                        <td style="border-top: 1px solid #DCDBD9;" align="center">
                          <a class="blue" href="?id=<?=$id?>&site=Admin&m=<?=$adm['id']?>&action=do.Remove.admin"><b>Удалить</b></a>
                        </td>
                      </tr>
                    <? } ?>
                    <tr valign="top">
                      <td colspan="4" style="padding:50px 0 25px 10px">
                        <input type="submit" style="width:110px" value="Сохранить"/>
                      </td>
                    </tr>
                  </form>
                <? } ?>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>