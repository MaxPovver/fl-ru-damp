<table style="margin-top: 0; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%">
                      <tbody>
                      <tr>
                          <td  bgcolor="#ffffff" height="20"></td>
                          <td  bgcolor="#ffffff" height="20"></td>
                          <td  bgcolor="#ffffff" height="20"></td>
                      </tr>
                      <tr>
                          <td  bgcolor="#ffffff" width="20"></td>
                          <td  align="left" ><b><font color="#000000" size="3" face="arial,sans-serif"><a href="<?=$p["link"] ?>" target="_blank" style="color:#000"><?=$p["name"] ?></a></font></b><?php if ($p["pro_only"] == 't') {?> <a target="_blank" href="https://www.fl.ru/payed/?utm_source=newsletter4&utm_medium=email&utm_campaign=notif_ed_pro"><img src="<?=WDCPREFIX ?>/images/letter/pro-f.png" width="25" height="12" border="0" style="margin-bottom:-1px;"></a><?} ?><?php
                           if ($p["verify_only"] == 't') {?> <a target="_blank" href="https://www.fl.ru/promo/verification/?utm_source=newsletter4&utm_medium=email&utm_campaign=notif_ed_verif"><img src="<?=WDCPREFIX?>/images/letter/ver.png" width="15" height="15"></a><?} ?></td>
                          <td  bgcolor="#ffffff" width="20"></td>
                      </tr>
                      <tr>
                          <td  bgcolor="#ffffff" height="5"></td>
                          <td  bgcolor="#ffffff"></td>
                          <td  bgcolor="#ffffff"></td>
                      </tr>
                      <tr>
                          <td  bgcolor="#ffffff" width="20"></td>
                          <td  align="left" ><b><font color="#4ea828" size="3" face="arial,sans-serif"><?=$p["cost"] > 0 ? to_money($p["cost"]) : '' ?> <?=$p["measure"] /* р./проект */?></font></b></td>
                          <td  bgcolor="#ffffff" width="20"></td>
                      </tr>
                      <tr>
                          <td  bgcolor="#ffffff" height="5"></td>
                          <td  bgcolor="#ffffff"></td>
                          <td  bgcolor="#ffffff"></td>
                      </tr>
                      <tr>
                          <td  bgcolor="#ffffff" width="20"></td>
                          <td  align="left" ><font color="#000000" size="3" face="arial,sans-serif"><?=$p["descr"]?></font></td>
                          <td  bgcolor="#ffffff" width="20"></td>
                      </tr>
                      <tr>
                          <td  bgcolor="#ffffff" height="5"></td>
                          <td  bgcolor="#ffffff"></td>
                          <td  bgcolor="#ffffff"></td>
                      </tr>
                      <tr>
                          <td  bgcolor="#ffffff" width="20"></td>
                          <td  align="left" ><font color="#808080" size="2" face="arial,sans-serif"><?=$p["str_kind"] ?></font></td>
                          <td  bgcolor="#ffffff" width="20"></td>
                      </tr>
                  </tbody>
                  </table>