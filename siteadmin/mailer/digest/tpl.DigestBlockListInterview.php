<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody>
        <tr>
            <td  bgcolor="#ffffff" width="20"></td>
            <td  width="20"></td>
            <td ><font color="#444444" size="3" face="arial,sans-serif"><b><a href="<?= $this->host . '/interview/'?>" style=" color:#0F71C8" target="_blank">Интервью</a></b></font></td>
            <td  width="20"></td>
            <td  bgcolor="#ffffff" width="20"></td>
        </tr>
    </tbody></table>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody>
        <tr>
            <td  bgcolor="#ffffff" width="20" height="20"></td>
            <td  width="20"></td>
            <td colspan="5" ></td>
            <td  width="20"></td>
            <td  bgcolor="#ffffff" width="20"></td>
        </tr>
        <? foreach($this->html_data as $user) { $i++;?>
            <? if($i %2 != 0) { $end = false; ?>
            <tr>
                <td  bgcolor="#ffffff" width="20"></td>
                <td  width="20"></td>
            <? } //if?>
         
            <td width="60" >
                <a href="<?= $this->getLinkById($user['id'])?>" target="_blank">
                <?= view_avatar($user['login'], $user['photo'])?>
                </a>
            </td>
            <td >
                <font color="#000000" size="2" face="arial,sans-serif"><a href="<?= $this->getLinkById($user['id'])?>" style="color:#000000" target="_blank"><?= ($user['uname'] . " ". $user['usurname']); ?></a></font><br>
                <font color="<?= is_emp($user['role']) ? "#6db335" : "#fd6c30"?>" size="2" face="arial,sans-serif">[<a href="<?= $this->getLinkById($user['id'])?>" style="color:<?= is_emp($user['role']) ? "#6db335" : "#fd6c30"?>" target="_blank"><?= $user['login']?></a>]</font><br>
                <font color="#444444" size="1" face="arial,sans-serif"><?= date('d.m.Y', strtotime($user['post_time']))?></font>
            </td>
            <?= ( $i %2 != 0 ? '<td  width="20">&nbsp;</td>' : ""); ?>
            
            <? if($i %2 == 0) { $end = true; ?>
                <td  width="20"></td>
                <td  bgcolor="#ffffff" width="20"></td>
            </tr>
            <tr>
                <td  bgcolor="#ffffff" width="20" height="20"></td>
                <td  width="20"></td>
                <td colspan="5" ></td>
                <td  width="20"></td>
                <td  bgcolor="#ffffff" width="20"></td>
            </tr>
            <? } //if?>
        <? } //foreach ?>
        <? if(!$end) {?>
                <td width="60" ></td>
                <td ></td>
                <td  width="20"></td>
                <td  bgcolor="#ffffff" width="20"></td>
            </tr>
            <tr>
                <td  bgcolor="#ffffff" width="20" height="20"></td>
                <td  width="20"></td>
                <td colspan="5" ></td>
                <td  width="20"></td>
                <td  bgcolor="#ffffff" width="20"></td>
            </tr>
        <? }//if?>
        <tr>
            <td  bgcolor="#ffffff" width="20" height="20"></td>
            <td  width="20"></td>
            <td colspan="5" ></td>
            <td  width="20"></td>
            <td  bgcolor="#ffffff" width="20"></td>
        </tr>    
    </tbody>
</table>