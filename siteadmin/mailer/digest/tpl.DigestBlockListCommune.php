<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left;" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody>
        <tr>
            <td  bgcolor="#ffffff" width="20" height="40" valign="top"></td>
            <td  width="20"></td>
            <td >
                <font color="#444444" size="3" face="arial,sans-serif"><b>Обсуждают в </b><a href="<?= $this->host; ?>/commune/" style="color:#0F71C8" target="_blank"><b>сообществах</b></a></font>

            </td>
            <td  width="20"></td>
            <td  bgcolor="#ffffff" width="20"></td>
        </tr>
    </tbody>
</table>

<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody>
        <?php foreach($this->html_data as $i => $blog) { ?>
        <tr>
            <td  bgcolor="#ffffff" width="20"></td>
            <td  width="20"></td>
            <td >
                <font color="#444444" size="2" face="arial,sans-serif"><a href="<?= $this->getLinkById($blog['id'])?>" style="color:#0F71C8" target="_blank" ><?= $blog['title'] ? $blog['title'] : "Без названия";?></a></font>
            </td>
            <td  width="20"></td>
            <td  bgcolor="#ffffff" width="20"></td>
        </tr>
        <tr>
            <td  bgcolor="#ffffff" width="20" height="5"></td>
            <td  width="20"></td>
            <td ></td>
            <td  width="20"></td>
            <td  bgcolor="#ffffff" width="20"></td>
        </tr>
        <tr>
            <td  bgcolor="#ffffff" width="20"></td>
            <td  width="20"></td>
            <td >
                <font color="#4d4d4d" size="1" face="arial,sans-serif"><?= date('d.m.Y', strtotime($blog['post_time']));?></font>
            </td>
            <td  width="20"></td>
            <td  bgcolor="#ffffff" width="20"></td>
        </tr>
        <tr>
            <td  bgcolor="#ffffff" width="20" height="10"></td>
            <td  width="20"></td>
            <td ></td>
            <td  width="20"></td>
            <td  bgcolor="#ffffff" width="20"></td>
        </tr>
        <?php }//foreach?>
        <tr>
            <td  bgcolor="#ffffff" width="20" height="50"></td>
            <td  width="20"></td>
            <td ></td>
            <td  width="20"></td>
            <td  bgcolor="#ffffff" width="20"></td>

        </tr>
    </tbody>
</table>