<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title></title>
</head>
<body bgcolor="#ffffff" marginwidth="0" marginheight="0" link="#006ed6"  bottommargin="0" topmargin="0" rightmargin="0" leftmargin="0" style="margin:0">

<table bgcolor="#ffffff" width="100%">
<tbody><tr>
<td bgcolor="#ffffff">
<center>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
    <tbody>
        <tr>
            <td width="20"></td>
            <td>
                <font color="#7e7e7e" size="2" face="arial">
                    Чтобы не пропустить ни одного письма от команды FL.ru, добавьте наш адрес no_reply@free-lance.ru в вашу адресную книгу. 
                    <a href="https://feedback.fl.ru/topic/532678-instruktsiya-po-dobavleniyu-email-adresa-flru-v-spisok-kontaktov/">Инструкция</a>
                </font>
            </td>
            <td width="20"></td>
        </tr>
    </tbody>
</table>
<br/>
<br/>
<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
    <tbody><tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td valign="top" width="70" ><a href="<?=$host?>" target="_blank"><img src="<?=$host?>/images/logo_50x50.png" width="55" height="55" border="0"></a></td>
        <td valign="middle" >
            <font color="#000000" size="6" face="arial">
                <?=$title?> за <?=date( 'j', $date ) . ' ' . monthtostr(date('n', $date),true);?>
            </font>
        </td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
</tbody>
</table>

<?=$projects?>

<table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left;" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="600">
    <tbody>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="20" colspan="3"></td>
    </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20"></td>
        <td valign="top">
              <font color="#7e7e7e" size="1" face="arial">
                Это письмо сформировано вам автоматически, потому что вы подписаны на ежедневную рассылку проектов - отвечать на него не нужно.
                Вы можете <a  style="color:#006ed6" target="_blank" href="<?=$host?><?=$unsubscribe_url?>">отписаться и больше не получать эту рассылку</a>
              </font>
        </td>
        <td  bgcolor="#ffffff" width="20"></td>
    </tr>
    <tr>
        <td  bgcolor="#ffffff" width="20" height="20" colspan="3"><?php if($track_url){ ?><img src="<?=$track_url?>" /><?php } ?></td>
    </tr>
</tbody>
</table>

</center>
</td>
</tr>
</tbody></table>

</body>
</html>