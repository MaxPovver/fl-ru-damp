<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
        <title></title>
    </head>
    <body bgcolor="#ffffff" marginwidth="0" marginheight="0" link="#0F71C8"  bottommargin="0" topmargin="0" rightmargin="0" leftmargin="0" style="margin:0">
        <table bgcolor="#ffffff" width="100%">
            <tbody>
                <tr>
                    <td bgcolor="#ffffff">
                        <center>
                            <table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
                                <tbody><tr>
                                        <td  bgcolor="#ffffff" width="20"></td>
                                        <td  height="100" width="20"></td>
                                        <td  height="100">
                                            <font color="#0F71C8" size="5" face="arial,sans-serif">
                                            <a href="/siteadmin/mailer/?action=<?= ( $preview == 1 ? "digest_edit" : "report" );?>&id=<?= $digest_id;?>">Назад</a>
                                            </forn>
                                        </td>
                                        <td  height="100"></td>
                                        <td  height="100" width="20"></td>
                                        <td  bgcolor="#ffffff" width="20"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </center>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?= $blocks->createHTMLMessage(); ?>
    </body>
</html>