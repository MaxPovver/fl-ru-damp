<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
    <tbody>
        <tr>
            <td  bgcolor="#ffffff" width="20"></td>
            <td  width="20"></td>
            <td ><font color="#444444" size="3" face="arial,sans-serif"><b><a href="<?= $this->host; ?>/konkurs/" style=" color:#0F71C8" target="_blank">Топ <?= $this->getListSize();?> конкурсов</a> с наиболее высоким бюджетом за неделю</b></font></td>
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
        
        <?php foreach($this->html_data as $project) { ?>
        
        <tr>
            <td  bgcolor="#ffffff" width="20"></td>
            <td  width="20"></td>
            <td >
                <font color="#444444" size="2" face="arial,sans-serif"><a href="<?= $this->getLinkById($project['id']);?>" style=" color:#0F71C8" target="_blank"><?= $project['sTitle'];?></a></font>&#160;&#160;
                <font color="#599f39" size="2" face="arial,sans-serif"><?= $project['str_cost']?></font>&#160;&#160;
                <font color="#b3b3b3" size="2" face="arial,sans-serif"><?= projects::getSpecsStr($project['id'],' / ', ', ', false);?></font>
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