<? if($this->name) { ?>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody>
        <tr>
            <td  bgcolor="#ffffff" width="20"></td>
            <td  width="20"></td>
            <td  >
                <font color="#000000" size="5" face="arial,sans-serif">
                    <? if($this->link) { ?>
                    <a href="<?= $this->link;?>" style="color:#0F71C8" target="_blank"><?= $this->name; ?></a>
                    <? } else { //if?>
                    <?= $this->name; ?>
                    <? }//else?>
                </font>
            </td>
            <td ></td>
            <td width="20"></td>
            <td  bgcolor="#ffffff" width="20"></td>
        </tr>
    </tbody>
</table>
<? }//if?>

<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody>
        <tr>
            <td  bgcolor="#ffffff" height="10" width="20"></td>
            <td  width="20"></td>
            <td ></td>
            <td  width="20"></td>
            <td  bgcolor="#ffffff" width="20"></td>
        </tr>
        <tr>
            <td  bgcolor="#ffffff" width="20"></td>
            <td  width="20"></td>
            <td ><font color="#444444" size="2" face="arial,sans-serif"><?= reformat($this->text); ?></font></td>
            <td  width="20"></td>
            <td  bgcolor="#ffffff" width="20"></td>
        </tr>
        <tr>
            <td  bgcolor="#ffffff" width="20" height="50"></td>
            <td  width="20"></td>
            <td ></td>
            <td  width="20"></td>
            <td  bgcolor="#ffffff" width="20"></td>
        </tr>
    </tbody>
</table>