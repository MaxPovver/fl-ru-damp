<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
        <title><!--{get_res code="chat.error_page.title"}--></title>
        <meta http-equiv="Content-Type" content="text/html; charset=<!--{$browser_charset}-->" />
        <link rel="shortcut icon" href="<!--{$webim_root}-->/images/favicon.ico" type="image/x-icon"/>
        <link rel="stylesheet" type="text/css" href="<!--{$webim_root}-->/css/admin_chat.css?<!--{$version}-->" />
    </head>
    <body bgcolor="#FFFFFF" background="<!--{$webim_root}-->/images/bg.gif" text="#000000" link="#C28400" vlink="#C28400" alink="#C28400" marginwidth="0" marginheight="0" leftmargin="0" rightmargin="0" topmargin="0" bottommargin="0">
        <table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td valign="top">
                    <table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td height="75"></td>
                            <td class="window">
                                <h1><!--{get_res code="chat.error_page.head"}--></h1>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td height="100%"></td>
                            <td>
                                <table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td width="15"><img src='<!--{$webim_root}-->/images/wincrnlt.gif' width="15" height="15" border="0" alt="" /></td>
                                        <td width="100%" background="<!--{$webim_root}-->/images/winbg.gif" class="bgcy"><img src='<!--{$webim_root}-->/images/free.gif' width="1" height="1" border="0" alt="" /></td>
                                        <td width="15"><img src='<!--{$webim_root}-->/images/wincrnrt.gif' width="15" height="15" border="0" alt="" /></td>
                                    </tr>
                                    <tr>
                                        <td height="100%" bgcolor="#FED840"><img src='<!--{$webim_root}-->/images/free.gif' width="1" height="1" border="0" alt="" /></td>
                                        <td background="<!--{$webim_root}-->/images/winbg.gif" class="bgcy">

                                    <!--{if $errors}-->
                                        <!--{get_res code="errors.header"}-->
                                        <!--{foreach from=$errors item=e}-->
                                            <!--{get_res code="errors.prefix"}-->
                                                <!--{$e}-->
                                            <!--{get_res code="errors.suffix"}-->
                                        <!--{/foreach}-->
                                        <!--{get_res code="errors.footer"}-->
                                    <!--{/if}-->

                                        </td>
                                        <td bgcolor="#E8A400"><img src='<!--{$webim_root}-->/images/free.gif' width="1" height="1" border="0" alt="" /></td>
                                    </tr>
                                    <tr>
                                        <td><img src='<!--{$webim_root}-->/images/wincrnlb.gif' width="15" height="15" border="0" alt="" /></td>
                                        <td background="<!--{$webim_root}-->/images/winbg.gif" class="bgcy"><img src='<!--{$webim_root}-->/images/free.gif' width="1" height="1" border="0" alt="" /></td>
                                        <td><img src='<!--{$webim_root}-->/images/wincrnrb.gif' width="15" height="15" border="0" alt="" /></td>
                                    </tr>
                                </table>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td height="70"></td>
                            <td>
                                <table width="100%" cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td width="100%" align="right">
                                            <table cellspacing="0" cellpadding="0" border="0">
                                                <tr>
                                                    <td><a href="javascript:window.close();" title="<!--{get_res code="chat.error_page.close"}-->"><img src='<!--{$webim_root}-->/images/buttons/back.gif' width="25" height="25" border="0" alt="" /></a></td>
                                                    <td width="5"></td>
                                                    <td class="button"><a href="javascript:window.close();" title="<!--{get_res code="chat.error_page.close"}-->"><?php echo getlocal("chat.error_page.close") ?></a></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td width="30"><img src='<!--{$webim_root}-->/images/free.gif' width="30" height="1" border="0" alt="" /></td>
                            <td width="100%"><img src='<!--{$webim_root}-->/images/free.gif' width="540" height="1" border="0" alt="" /></td>
                            <td width="30"><img src='<!--{$webim_root}-->/images/free.gif' width="30" height="1" border="0" alt="" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>