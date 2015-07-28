<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr align="center" class="user_menu">
	<td width="16" height="21"><img src="/images/lsq.gif" alt="" width="16" height="21" border="0"></td>
	<td class="<? if ($activ_tab==1) print("act_menu"); else print ("user_menu_l")?>" onClick="document.location='/contacts/new'"><a href="/contacts/new/">Новые</a></td>
	<td class="<? if ($activ_tab==2) print("act_menu"); else { ($activ_tab == 1)? print ("user_menu_la") : print ("user_menu"); }?>" onClick="document.location='/contacts/'"><a href="/contacts/">Общие</a></td>
	<td class="<? if ($activ_tab==3) print("act_menu"); else { ($activ_tab == 2)? print ("user_menu_la") : print ("user_menu"); }?>" onClick="document.location='/contacts/team/'"><a href="/contacts/team/">Избранные</a></td>
	<td width="95" class="<? if ($activ_tab==4) print("act_menu"); else { ($activ_tab == 3)? print ("user_menu_la") : print ("user_menu"); }?>" onClick="document.location='/contacts/ignor/'"><a href="/contacts/ignor/">Игнорирую</a></td>
	<td width="14"><img src="/images/<?=($activ_tab == 4)? "menu_activ_r" : "menu_passiv_r"?>.gif" alt="" width="14" height="21" border="0"></td>
	<td><img src="/images/lsq.gif" alt="" width="100%" height="21" border="0"></td>
</tr>
</table>
