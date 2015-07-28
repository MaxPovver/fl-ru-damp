<?  
//// debug /////////////////////////////////////////////////////////////////////
  if($DEBUG)
    if(!hasPermissions('users') && $_SESSION['login']!='sll' || !$login) { header('Location: /404.php'); exit; }
  // Классы закладок.

  $bmCls = getBookmarksStyles(promotion::BM_COUNT, $bm);
?>
  <table border="0" width="100%" cellpadding="0" cellspacing="0">
    <tr valign="middle">
      <td align="left">
        <h1>Статистика</h1>
      </td>
      <td align="right">&nbsp;</td>
    </tr>
  </table>
  <div id="header">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr align="center" class="user_menu">
        <td width="10" height="21"><img src="/images/lsq.gif" alt="" width="100%" height="21" border="0"></td>
        <td class="<?=$bmCls[promotion::BM_PROGNOSES]?>">
          <a style="width:100%" href="?bm=<?=promotion::BM_PROGNOSES?><?=($DEBUG?"&user={$login}":'')?>">Прогнозы</a>
        </td>
        <td class="<?=$bmCls[promotion::BM_GUESTS]?>">
          <a style="width:100%" href="?bm=<?=promotion::BM_GUESTS?><?=($DEBUG?"&user={$login}":'')?>">Посетители</a>
        </td>
        <td width="14"><img src="/images/<?=$bmCls[promotion::BM_COUNT]?>" alt="" width="14" height="21" border="0"></td>
        <td><img src="/images/lsq.gif" alt="" width="100%" height="21" border="0"></td>
      </tr>
    </table>
  </div>
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr valign="top">
      <td bgcolor="#FFFFFF" class="br bb bl gray-bc promotion" style="padding:10px 30px 120px 15px">
        <h1>Извините, страница временно недоступна. Повторите попытку чуть позже.</h1>
      </td>
    </tr>
  </table>
