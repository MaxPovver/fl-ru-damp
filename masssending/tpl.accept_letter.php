<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
    <title></title>
</head>
<body bgcolor="#ffffff" marginwidth="0" marginheight="0" link="#396ea9" bottommargin="0" topmargin="0" rightmargin="0" leftmargin="0" style="margin:0">
    <table bgcolor="#ffffff" width="100%">
        <tr>
            <td bgcolor="#ffffff">
                <center>
                    <table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
                        <tbody><tr>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                            <td class="pad_null" height="20" width="20"></td>
                            <td class="pad_null" height="20" width="20"></td>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                        </tr>
                        </tbody>
                    </table>

                    <table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
                        <tr>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                            <td class="pad_null" width="20"></td>
                            <td class="pad_null">
                                <font color="#444444" size="2" face="tahoma,sans-serif">Здравствуйте, <?= $params['name'] ?>.<br><br>
                                    Ваша заявка на рассылку была рассмотрена и одобрена модераторами сайта Free-lance.ru.&nbsp;
                                    Фрилансерам выбранных вами специализаций будет отправлено сообщение следующего содержания:<br><br>
                                    ----
                                    <br><br>
                                    <?= $params['message'] ?>
                                    <br><br>
                                    ----
                                </font>
                            </td>
                            <td class="pad_null" width="20"></td>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                        </tr>
                    </table>
                    <table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
                        <tbody>
                        <tr>
                            <td class="pad_null" bgcolor="#ffffff" width="20" height="30"></td>
                            <td class="pad_null" width="20"></td>
                            <td class="pad_null" width="100"></td>
                            <td class="pad_null" valign="middle"></td>
                            <td class="pad_null" width="20"></td>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                        </tr>
                        <tr>
                            <td class="pad_null" bgcolor="#ffffff" width="20" height="20"></td>
                            <td class="pad_null" width="20"></td>
                            <td class="pad_null" width="100"><a target="_blank" href="<?= $host ?>/masssending/pay/<?=$params['id']?>/<?= $UTM ?>"><img src="cid:<?= $cid; ?>" width="96" height="36" border="0"></a></td>
                            <td class="pad_null" valign="middle">&nbsp;&nbsp;&nbsp;
                                <b>
                                    <font color="#fd6c30" size="2" face="tahoma,sans-serif"><?= to_money($params['amount'], 2) . ending(to_money($params['amount']), ' рубль', ' рубля', ' рублей')?></font>
                                </b>
                            </td>
                            <td class="pad_null" width="20"></td>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                        </tr>
                        <tr>
                            <td class="pad_null" bgcolor="#ffffff" width="20" height="40"></td>
                            <td class="pad_null" width="20"></td>
                            <td class="pad_null" width="100"></td>
                            <td class="pad_null" valign="middle"></td>
                            <td class="pad_null" width="20"></td>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                        </tr>
                        </tbody>
                    </table>
                    <table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff; text-align:left" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
                        <tr>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                            <td class="pad_null" width="20"></td>
                            <td class="pad_null"><font color="#444444" size="2" face="tahoma,sans-serif">Подробная информация по услуге «Платная рассылка по каталогу» размещена в нашем&nbsp;<a target="_blank" style="color:#0f71c8;" href="https://feedback.fl.ru/topic/397509-rassyilka-po-katalogu-sistema-sozdaniya-rassyilki-oplata-rassyilki/?<?= $UTM ?>">сообществе поддержки</a>.<br><br>

                                    По всем возникающим вопросам обращайтесь в нашу&nbsp;<a target="_blank" style="color:#0f71c8;" href="https://feedback.free-lance.ru?<?= $UTM ?>">службу поддержки</a>.<br><br>

                                    Вы можете отключить уведомления&nbsp;на <a target="_blank" style="color:#0f71c8;" href="<?= $host ?>/unsubscribe?ukey=%UNSUBSCRIBE_KEY%&<?= $UTM ?>">этой странице</a>.<br><br></font></td>
                            <td class="pad_null" width="20"></td>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                        </tr>
                        <tr>
                            <td class="pad_null" bgcolor="#ffffff" width="20" height="20"></td>
                            <td class="pad_null" width="20"></td>
                            <td class="pad_null"></td>
                            <td class="pad_null" width="20"></td>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                        </tr>
                    </table>
                    <table style="margin-top: 0pt; margin-left: auto; margin-right: auto; background-color: #ffffff;" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="740">
                        <tr>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                            <td class="pad_null" bgcolor="#ffffff">
                                <i><font color="#4d4d4d" size="2" face="tahoma,sans-serif">Приятной работы с Free-lance.ru!</font></i>
                            </td>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                        </tr>
                        <tr>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                            <td class="pad_null" bgcolor="#ffffff" height="20" width="20"></td>
                            <td class="pad_null" bgcolor="#ffffff"></td>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                            <td class="pad_null" bgcolor="#ffffff" width="20"></td>
                        </tr>
                    </table>

                </center>
            </td>
        </tr>
    </table>
</body>
</html>