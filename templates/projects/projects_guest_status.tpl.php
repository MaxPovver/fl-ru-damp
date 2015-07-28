                   <table class="b-layout__table b-layout__table_width_full">
                      <tr class="b-layout__tr">
                         <td class="b-layout__td b-layout__td_width_60">
                            <img src="/images/tu/ico_po_executor.png" class="b-pic" />    
                         </td>
                         <td class="b-layout__td b-layout__td_valign_mid">
                            <div class="b-layout__txt">
                               Исполнитель определен: <a class="b-layout__link b-layout__link_color_000 b-layout__link_bold" href="/users/<?=$exec_info['login']?>"><?=($exec_info['uname']." ".$exec_info['usurname'])?> [<?=$exec_info['login']?>]</a> <?= view_mark_user($exec_info);?>
                            </div>
                         </td>
                      </tr>
                   </table>