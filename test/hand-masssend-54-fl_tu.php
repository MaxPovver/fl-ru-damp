<?php

ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp.php';
require_once '../classes/users.php';


/**
 * Логин пользователя от кого осуществляется рассылка
 * 
 */
$sender = 'admin';

$eHost = $GLOBALS['host'];

$eSubject = "Представляем новый сервис на сайте fl.ru";

$eMessage = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <!-- NAME: 1:2 COLUMN - BANDED -->
        <meta http-equiv="Content-Type" content="text/html; charset=WINDOWS-1251">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Представляем вам новый сервис на сайте fl.ru</title>
        
        <!--[if gte mso 6]>
        <style>
            table.mcnFollowContent {width:100% !important;}
            table.mcnShareContent {width:100% !important;}
        </style>
        <![endif]-->
    <style type="text/css">
        body,#bodyTable,#bodyCell{
            height:100% !important;
            margin:0;
            padding:0;
            width:100% !important;
            background-color:#FFFFFF;
        }
        table{
            border-collapse:collapse;
        }
        img,a img{
            border:0;
            outline:none;
            text-decoration:none;
        }
        h1,h2,h3,h4,h5,h6{
            margin:0;
            padding:0;
        }
        p{
            margin:1em 0;
            padding:0;
        }
        a{
            word-wrap:break-word;
        }
        .ReadMsgBody{
            width:100%;
        }
        .ExternalClass{
            width:100%;
        }
        .ExternalClass,.ExternalClass p,.ExternalClass span,.ExternalClass font,.ExternalClass td,.ExternalClass div{
            line-height:100%;
        }
        table,td{
            mso-table-lspace:0pt;
            mso-table-rspace:0pt;
        }
        #outlook a{
            padding:0;
        }
        img{
            -ms-interpolation-mode:bicubic;
        }
        body,table,td,p,a,li,blockquote{
            -ms-text-size-adjust:100%;
            -webkit-text-size-adjust:100%;
        }
        #bodyCell{
            padding:0;
        }
        .mcnImage{
            vertical-align:bottom;
        }
        .mcnTextContent img{
            height:auto !important;
        }
        body,#bodyTable{
            background-color:#FFFFFF;
        }
        #bodyCell{
            border-top:0;
        }
        h1{
            color:#606060 !important;
            display:block;
            font-family:Helvetica;
            font-size:40px;
            font-style:normal;
            font-weight:bold;
            line-height:125%;
            letter-spacing:-1px;
            margin:0;
            text-align:left;
        }
        h2{
            color:#404040 !important;
            display:block;
            font-family:Helvetica;
            font-size:26px;
            font-style:normal;
            font-weight:bold;
            line-height:125%;
            letter-spacing:-.75px;
            margin:0;
            text-align:left;
        }
        h3{
            color:#606060 !important;
            display:block;
            font-family:Helvetica;
            font-size:18px;
            font-style:normal;
            font-weight:bold;
            line-height:125%;
            letter-spacing:-.5px;
            margin:0;
            text-align:left;
        }
        h4{
            color:#808080 !important;
            display:block;
            font-family:Helvetica;
            font-size:16px;
            font-style:normal;
            font-weight:bold;
            line-height:125%;
            letter-spacing:normal;
            margin:0;
            text-align:left;
        }
        #templatePreheader{
            background-color:#FFFFFF;
            border-top:0;
            border-bottom:0;
        }
        .preheaderContainer .mcnTextContent,.preheaderContainer .mcnTextContent p{
            color:#606060;
            font-family:Helvetica;
            font-size:11px;
            line-height:125%;
            text-align:left;
        }
        .preheaderContainer .mcnTextContent a{
            color:#606060;
            font-weight:normal;
            text-decoration:underline;
        }
        #templateHeader{
            background-color:#cccccc;
            color:#cccccc;
            border-top:0;
            border-bottom:0;
        }
        .headerContainer .mcnTextContent,.headerContainer .mcnTextContent p{
            color:#606060;
            font-family:Helvetica;
            font-size:15px;
            line-height:150%;
            text-align:left;
        }
        .headerContainer .mcnTextContent a{
            color:#6DC6DD;
            font-weight:normal;
            text-decoration:none;
        }
        #templateBody{
            background-color:#FFFFFF;
            border-top:0;
            border-bottom:0;
        }
        .bodyContainer .mcnTextContent,.bodyContainer .mcnTextContent p{
            color:#606060;
            font-family:Helvetica;
            font-size:15px;
            line-height:150%;
            text-align:left;
        }
        .bodyContainer .mcnTextContent a{
            color:#6DC6DD;
            font-weight:normal;
            text-decoration:underline;
        }
        #templateColumns{
            background-color:#FFFFFF;
            border-top:0;
            border-bottom:0;
        }
        .leftColumnContainer .mcnTextContent,.leftColumnContainer .mcnTextContent p{
            color:#606060;
            font-family:Helvetica;
            font-size:15px;
            line-height:150%;
            text-align:left;
        }
        .leftColumnContainer .mcnTextContent a{
            color:#6DC6DD;
            font-weight:normal;
            text-decoration:underline;
        }
        .rightColumnContainer .mcnTextContent,.rightColumnContainer .mcnTextContent p{
            color:#606060;
            font-family:Helvetica;
            font-size:15px;
            line-height:110%;
            text-align:left;
        }
        .rightColumnContainer .mcnTextContent a{
            color:#6DC6DD;
            font-weight:normal;
            text-decoration:none;
        }
        #templateFooter{
            background-color:#323232;
            border-top:0;
            border-bottom:0;
        }
        .footerContainer .mcnTextContent,.footerContainer .mcnTextContent p{
            color:#606060;
            font-family:Helvetica;
            font-size:11px;
            line-height:125%;
            text-align:left;
        }
        .footerContainer .mcnTextContent a{
            color:#606060;
            font-weight:normal;
            text-decoration:underline;
        }
    @media only screen and (max-width: 480px){
        body,table,td,p,a,li,blockquote{
            -webkit-text-size-adjust:none !important;
        }

}   @media only screen and (max-width: 480px){
        body{
            width:100% !important;
            min-width:100% !important;
        }

}   @media only screen and (max-width: 480px){
        table[class=mcnTextContentContainer]{
            width:100% !important;
        }

}   @media only screen and (max-width: 480px){
        table[class=mcnBoxedTextContentContainer]{
            width:100% !important;
        }

}   @media only screen and (max-width: 480px){
        table[class=mcpreview-image-uploader]{
            width:100% !important;
            display:none !important;
        }

}   @media only screen and (max-width: 480px){
        img[class=mcnImage]{
            width:100% !important;
        }

}   @media only screen and (max-width: 480px){
        table[class=mcnImageGroupContentContainer]{
            width:100% !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=mcnImageGroupContent]{
            padding:9px !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=mcnImageGroupBlockInner]{
            padding-bottom:0 !important;
            padding-top:0 !important;
        }

}   @media only screen and (max-width: 480px){
        tbody[class=mcnImageGroupBlockOuter]{
            padding-bottom:9px !important;
            padding-top:9px !important;
        }

}   @media only screen and (max-width: 480px){
        table[class=mcnCaptionTopContent],table[class=mcnCaptionBottomContent]{
            width:100% !important;
        }

}   @media only screen and (max-width: 480px){
        table[class=mcnCaptionLeftTextContentContainer],table[class=mcnCaptionRightTextContentContainer],table[class=mcnCaptionLeftImageContentContainer],table[class=mcnCaptionRightImageContentContainer],table[class=mcnImageCardLeftTextContentContainer],table[class=mcnImageCardRightTextContentContainer]{
            width:100% !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=mcnImageCardLeftImageContent],td[class=mcnImageCardRightImageContent]{
            padding-right:18px !important;
            padding-left:18px !important;
            padding-bottom:0 !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=mcnImageCardBottomImageContent]{
            padding-bottom:9px !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=mcnImageCardTopImageContent]{
            padding-top:18px !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=mcnImageCardLeftImageContent],td[class=mcnImageCardRightImageContent]{
            padding-right:18px !important;
            padding-left:18px !important;
            padding-bottom:0 !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=mcnImageCardBottomImageContent]{
            padding-bottom:9px !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=mcnImageCardTopImageContent]{
            padding-top:18px !important;
        }

}   @media only screen and (max-width: 480px){
        table[class=mcnCaptionLeftContentOuter] td[class=mcnTextContent],table[class=mcnCaptionRightContentOuter] td[class=mcnTextContent]{
            padding-top:9px !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=mcnCaptionBlockInner] table[class=mcnCaptionTopContent]:last-child td[class=mcnTextContent]{
            padding-top:18px !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=mcnBoxedTextContentColumn]{
            padding-left:18px !important;
            padding-right:18px !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=columnsContainer]{
            display:block !important;
            max-width:600px !important;
            width:100% !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=mcnTextContent]{
            padding-right:18px !important;
            padding-left:18px !important;
        }

}   @media only screen and (max-width: 480px){
        table[class=templateContainer],table[id=templateColumns],table[class=templateColumn]{
            max-width:600px !important;
            width:100% !important;
        }

}   @media only screen and (max-width: 480px){
        h1{
            font-size:24px !important;
            line-height:125% !important;
        }

}   @media only screen and (max-width: 480px){
        h2{
            font-size:20px !important;
            line-height:125% !important;
        }

}   @media only screen and (max-width: 480px){
        h3{
            font-size:18px !important;
            line-height:125% !important;
        }

}   @media only screen and (max-width: 480px){
        h4{
            font-size:16px !important;
            line-height:125% !important;
        }

}   @media only screen and (max-width: 480px){
        table[class=mcnBoxedTextContentContainer] td[class=mcnTextContent],td[class=mcnBoxedTextContentContainer] td[class=mcnTextContent] p{
            font-size:18px !important;
            line-height:125% !important;
        }

}   @media only screen and (max-width: 480px){
        table[id=templatePreheader]{
            display:block !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=preheaderContainer] td[class=mcnTextContent],td[class=preheaderContainer] td[class=mcnTextContent] p{
            font-size:14px !important;
            line-height:115% !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=headerContainer] td[class=mcnTextContent],td[class=headerContainer] td[class=mcnTextContent] p{
            font-size:18px !important;
            line-height:125% !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=bodyContainer] td[class=mcnTextContent],td[class=bodyContainer] td[class=mcnTextContent] p{
            font-size:18px !important;
            line-height:125% !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=leftColumnContainer] td[class=mcnTextContent],td[class=leftColumnContainer] td[class=mcnTextContent] p{
            font-size:18px !important;
            line-height:125% !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=rightColumnContainer] td[class=mcnTextContent],td[class=rightColumnContainer] td[class=mcnTextContent] p{
            font-size:18px !important;
            line-height:125% !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=footerContainer] td[class=mcnTextContent],td[class=footerContainer] td[class=mcnTextContent] p{
            font-size:14px !important;
            line-height:115% !important;
        }

}   @media only screen and (max-width: 480px){
        td[class=footerContainer] a[class=utilityLink]{
            display:block !important;
        }

}</style></head>

    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="margin: 0;padding: 0;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #F2F2F2;height: 100% !important;width: 100% !important;">
        <center>
            <table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;margin: 0;padding: 0;background-color: #F2F2F2;height: 100% !important;width: 100% !important;">
                <tr>
                    <td align="center" valign="top" id="bodyCell" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;margin: 0;padding: 0;border-top: 0;height: 100% !important;width: 100% !important;">
                        <!-- BEGIN TEMPLATE // -->
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                            <tr>
                                <td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                    <!-- BEGIN TEMPLATE // -->
                        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                            <tr>
                                <td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                    <!-- BEGIN PREHEADER // -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templatePreheader" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #FFFFFF;border-top: 0;border-bottom: 0;">
                                        <tr>
                                            <td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                <table border="0" cellpadding="0" cellspacing="0" width="600" class="templateContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                    <tr>
                                                        <td valign="top" class="preheaderContainer" style="padding-top: 9px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnTextBlockOuter">
        <tr>
            <td valign="top" class="mcnTextBlockInner" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                
                <table align="left" border="0" cellpadding="0" cellspacing="0" width="366" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding: 9px 0px 15px 18px;color: #666666;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;font-family: Helvetica;font-size: 11px;line-height: 125%;text-align: left;">
                        
                            С наступившими вас Новым годом и Рождеством!
                        </td>
                    </tr>
                </tbody></table>
                
                
            </td>
        </tr>
    </tbody>
</table></td>
                                                    </tr>
                                                </table>
                                            </td>                                            
                                        </tr>
                                    </table>
                                    <!-- // END PREHEADER -->
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top" style=" background-color:#ffffff;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                    <!-- BEGIN HEADER // -->
                                    <table bgcolor="#d2d2d2" border="0" cellpadding="0" cellspacing="0" width="600" id="templateHeader" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #d2d2d2;border-top: 0;border-bottom: 0;">
                                        <tr>
                                            <td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                    <tr>
                                                        <td valign="top" class="headerContainer" style="padding-top: 10px;padding-bottom: 10px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnImageBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnImageBlockOuter">
            <tr>
                <td valign="top" style="padding: 9px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;" class="mcnImageBlockInner">
                    <table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" class="mcnImageContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                        <tbody><tr>
                            <td class="mcnImageContent" valign="top" style="padding-right: 9px;padding-left: 9px;padding-top: 0;padding-bottom: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                
                                    
                                        <img align="left" alt="" src="https://gallery.mailchimp.com/232009edbbee7d0fe99b1cca1/images/111.1.png" style="max-width: 384px;padding-bottom: 0;display: inline !important;vertical-align: bottom;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;" class="mcnImage">
                                    
                                
                            </td>
                        </tr>
                    </tbody></table>
                </td>
            </tr>
    </tbody>
</table></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- // END HEADER -->
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                    <!-- BEGIN BODY // -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateBody" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #FFFFFF;border-top: 0;border-bottom: 0;">
                                        <tr>
                                            <td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                <table border="0" cellpadding="0" cellspacing="0" width="600" class="templateContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                    <tr>
                                                        <td valign="top" class="bodyContainer" style="padding-top: 10px;padding-bottom: 10px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;"><table border="0" cellpadding="0" cellspacing="0" width="600" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnTextBlockOuter">
        <tr>
            <td valign="top" class="mcnTextBlockInner" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                
                <table align="left" border="0" cellpadding="0" cellspacing="0" width="600" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding: 9px 18px;color: #666666;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;font-family: Helvetica;font-size: 15px;line-height: 150%;text-align: left;">
                        
                            <span style="color:#696969;font-size:12px;font-family:arial,sans-serif;">Здравствуйте!<br>
<br>
Возможно вы еще не знаете, но совсем недавно на нашем сайте был запущен новый сервис Типовые услуги, с помощью которого вы сможете подробно и в доступной форме рассказать заказчикам о своих услугах и условиях их предоставления.</span>
                        </td>
                    </tr>
                </tbody></table>
                
            </td>
        </tr>
    </tbody>
</table>




                
            </td>
        </tr>
    </tbody>
</table>
</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- // END BODY -->
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                    <!-- BEGIN COLUMNS // -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateColumns" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #FFFFFF;border-top: 0;border-bottom: 0;">
                                        <tr>
                                            <td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                <table border="0" cellpadding="0" cellspacing="0" width="600" class="templateContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                    <tr>
                                                        <td align="left" valign="top" class="columnsContainer" width="50%" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                            <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateColumn" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                                <tr>
                                                                    <td valign="top" class="leftColumnContainer" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnTextBlockOuter">
        <tr>
            <td valign="top" class="mcnTextBlockInner" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                
                <table align="left" border="0" cellpadding="0" cellspacing="0" width="300" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding: 15px 18px 9px;color: #666666;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;font-family: Helvetica;font-size: 15px;line-height: 150%;text-align: left;">
                        
                            <h2 class="null" style="margin: 0;padding: 0;display: block;font-family: Helvetica;font-size: 26px;font-style: normal;font-weight: bold;line-height: 125%;letter-spacing: -.75px;text-align: left;color:#009900;">Типовые услуги:</h2>

                        </td>
                    </tr>
                </tbody></table>
                
            </td>
        </tr>
    </tbody>
</table><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnBoxedTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnBoxedTextBlockOuter">
        <tr>
            <td valign="top" class="mcnBoxedTextBlockInner" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                
                <table align="left" border="0" cellpadding="0" cellspacing="0" width="300" class="mcnBoxedTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        
                        <td style="border:0; padding-top: 9px;padding-left: 18px;padding-bottom: 9px;padding-right: 18px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                        
                            <table bgcolor="#EBFFD7" border="0" cellpadding="18" cellspacing="0" class="mcnTextContentContainer" width="100%" style="background-color: #EBFFD7;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                <tbody><tr>
                                    <td valign="top" class="mcnTextContent" style="border:0; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif;font-size: 12px;text-align: left;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;line-height: 150%;">
                                        <span style="font-family:arial,sans-serif;font-size:12px;"><b>1.&nbsp;Максимум информации</b><br>
<span style="font-size:11px;font-family:arial,sans-serif;">В удобной единой форме карточек мы соединили отзывы, работы, описания, брифы, сроки и расценки по каждой вашей услуге. Теперь всё наглядно и просто!</span></span>
                                    </td>
                                </tr>
                            </tbody></table>
                        </td>
                    </tr>
                </tbody></table>
                
            </td>
        </tr>
    </tbody>
</table><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnBoxedTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnBoxedTextBlockOuter">
        <tr>
            <td valign="top" class="mcnBoxedTextBlockInner" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                
                <table align="left" border="0" cellpadding="0" cellspacing="0" width="300" class="mcnBoxedTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        
                        <td style="padding-top: 9px;padding-left: 18px;padding-bottom: 9px;padding-right: 18px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                        
                            <table  bgcolor="#EBFFD7" border="0" cellpadding="18" cellspacing="0" class="mcnTextContentContainer" width="100%" style="background-color: #EBFFD7;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                <tbody><tr>
                                    <td valign="top" class="mcnTextContent" style="font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif;font-size: 12px;text-align: left;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;line-height: 150%;">
                                        <span style="font-family:arial,sans-serif;font-size:12px;"><b>2. Экономия времени</span></b><br>
<span style="font-family: arial, sans-serif; font-size: 11px;">Не тратьте время на переговоры с каждым новым заказчиком. Все детали можно указать в своих Типовых услугах – и при их заказе сразу переходить к работе.</span>
                                    </td>
                                </tr>
                            </tbody></table>
                        </td>
                    </tr>
                </tbody></table>
                
            </td>
        </tr>
    </tbody>
</table><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnBoxedTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnBoxedTextBlockOuter">
        <tr>
            <td valign="top" class="mcnBoxedTextBlockInner" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                
                <table align="left" border="0" cellpadding="0" cellspacing="0" width="300" class="mcnBoxedTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        
                        <td style="padding-top: 9px;padding-left: 18px;padding-bottom: 9px;padding-right: 18px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                        
                            <table bgcolor="#EBFFD7" border="0" cellpadding="18" cellspacing="0" class="mcnTextContentContainer" width="100%" style="background-color: #EBFFD7;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                <tbody><tr>
                                    <td valign="top" class="mcnTextContent" style="font-family: Arial, sans-serif;font-size: 12px;text-align: left;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;line-height: 150%;">
                                        <span style="font-family:arial,sans-serif;font-size:12px;"><b>3. Больше заказов</span></b><br>
<span style="font-family: arial, sans-serif; font-size: 11px;">Покажите, какие и в каком качестве услуги вы оказываете. Эффектно заполненные Типовые услуги привлекают внимание еще большего числа заказчиков, а с ними и новые выгодные предложения.</span>
                                    </td>
                                </tr>
                            </tbody></table>
                        </td>
                    </tr>
                </tbody></table>
                
            </td>
        </tr>
    </tbody>
</table></td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        <td align="left" valign="top" class="columnsContainer" width="50%" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                            <table align="right" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateColumn" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                                <tr>
                                                                    <td valign="top" class="rightColumnContainer" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnCaptionBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnCaptionBlockOuter">
        <tr>
            <td class="mcnCaptionBlockInner" valign="top" style="padding: 9px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                

<table align="left" border="0" cellpadding="0" cellspacing="0" class="mcnCaptionBottomContent" width="false" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody><tr>
        <td class="mcnCaptionBottomImageContent" align="left" valign="top" style="padding: 0 9px 9px 9px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
        
            

            <img alt="" src="https://gallery.mailchimp.com/232009edbbee7d0fe99b1cca1/images/222.png" width="264" style="max-width: 310px;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;vertical-align: bottom;" class="mcnImage">
            
        
        </td>
    </tr>
    <tr>
        <td class="mcnTextContent" valign="top" style="padding: 0 9px 0 9px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;font-family: Helvetica;font-size: 15px;line-height: 110%;text-align: left;" width="264">
            
        </td>
    </tr>
</tbody></table>





            </td>
        </tr>
    </tbody>
</table></td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            
                    </tr>
                    <tr><td><table border="0" cellpadding="0" cellspacing="0" width="100%" id="templateBody" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #FFFFFF;border-top: 0;border-bottom: 0;">
                                        <tr>
                                            <td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                <table border="0" cellpadding="0" cellspacing="0" width="600" class="templateContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                    <tr>
                                                        <td valign="top" class="bodyContainer" style="padding-top: 10px;padding-bottom: 10px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;"><table border="0" cellpadding="0" cellspacing="0" width="600" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnTextBlockOuter">
        <tr>
            <td valign="top" class="mcnTextBlockInner" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                
                
                <table border="0" cellpadding="0" cellspacing="0" width="600" class="mcnImageBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnImageBlockOuter">
            <tr>
                <td valign="top" style="padding: 0px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;" class="mcnImageBlockInner">
                    <table align="left" width="600" border="0" cellpadding="0" cellspacing="0" class="mcnImageContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                        <tbody><tr>
                            <td class="mcnImageContent" valign="top" style="padding-right: 0px;padding-left: 0px;padding-top: 0;padding-bottom: 0;text-align: center;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                
                                    
                                        <a href="http://www.fl.ru/users/'."%USER_LOGIN%".'/tu/?utm_source=mailing&utm_medium=email&utm_campaign=tp_promo130114"><img align="center" alt="" src="https://gallery.mailchimp.com/232009edbbee7d0fe99b1cca1/images/333.3.png" width="565" style="max-width: 565px;padding-bottom: 0;display: inline !important;vertical-align: bottom;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;" class="mcnImage"></a>
                                    
                                
                            </td>
                        </tr>
                    </tbody></table>
                </td>
            </tr>
    </tbody>
</table>
                <table align="left" border="0" cellpadding="0" cellspacing="0" width="600" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding: 15px 18px 9px;color: #666666;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;font-family: Helvetica;font-size: 15px;line-height: 150%;text-align: left;">
                        
                            <span style="color:#696969;font-size:12px;font-family:arial,sans-serif;">
Ознакомиться с функционалом сервиса и добавить услуги вы можете в своем профиле на вкладке «Типовые услуги». Подробное описание всех функций доступно в <a href="https://feedback.fl.ru/knowledgebase/category/id/885" target="_self" style="word-wrap: break-word;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #6DC6DD;font-weight: normal;text-decoration: underline;"><u>Базе знаний</u></a>.<br>
Также приглашаем вас в <a href="https://www.fl.ru/commune/drugoe/5100/free-lanceru/8535645/zapuskaem-tipovyie-uslugi.html" target="_self" style="word-wrap: break-word;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #6DC6DD;font-weight: normal;text-decoration: underline;"><u>наше корпоративное сообщество</u></a>, будем рады предложениям, идеям и замечаниям.<br><br>
Надеемся, что новый сервис будет полезным и удобным для вас.<br>
С уважением, команда fl.ru<br></span>
                        </td></tr>
                </tbody></table>
                
                
                                
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnDividerBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnDividerBlockOuter">
        <tr>
            <td class="mcnDividerBlockInner" style="padding: 10px 18px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                <table class="mcnDividerContent" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-top-width: 1px;border-top-style: solid;border-top-color: #999999;border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        <td style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                            <span></span>
                        </td>
                    </tr>
                </tbody></table>
            </td>
        </tr>
    </tbody>
</table>
                <table border="0" cellpadding="0" cellspacing="0" width="600" id="templateBody" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #FFFFFF;border-top: 0;border-bottom: 0;">
                                        <tr>
                                            <td valign="top" class="bodyContainer" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnTextBlockOuter">
        <tr>
            <td valign="top" class="mcnTextBlockInner" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                
                <table align="left" border="0" cellpadding="0" cellspacing="0" width="282" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding-top: 9px;padding-left: 18px;padding-bottom: 9px;padding-right: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;font-family: Helvetica;font-size: 15px;line-height: 150%;text-align: left;">
                        
                            <span style="color: #696969;font-size: 11px;line-height: 22px;font-family: arial, sans-serif;"><i>PS: Кстати, уже доступны примеры заполненных Типовых услуг:<br>
- в нашем&nbsp;</i></span><i style="color: #696969;font-family: Helvetica;font-size: 11px;line-height: 22px;"><a href="https://www.fl.ru/users/fl-test/tu/" style="word-wrap: break-word;color: #6DC6DD;font-family: arial, sans-serif;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;font-weight: normal;text-decoration: underline;" target="_self">тестовом аккаунте</a>;<br>
<span style="font-family: arial, sans-serif;">- или в&nbsp;<a href="https://www.fl.ru/freelancers/" style="word-wrap: break-word;color: #6DC6DD;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;font-weight: normal;text-decoration: underline;" target="_self">профилях фрилансеров</a> (для просмотра профилей сначала настройте фильтр каталога, как показано справа).</span></i>
                        </td>
                    </tr>
                </tbody></table>
                
                <table align="right" border="0" cellpadding="0" cellspacing="0" width="282" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding-top: 9px;padding-right: 18px;padding-bottom: 9px;padding-left: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;font-family: Helvetica;font-size: 15px;line-height: 150%;text-align: left;">
                        
                            <img align="none" height="141" src="https://gallery.mailchimp.com/232009edbbee7d0fe99b1cca1/images/1_2.1ee6f6d1b14d9.png" style="width: 276px;height: 141px;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;" width="276">
                        </td>
                    </tr>
                </tbody></table>
                
            </td>
        </tr>
    </tbody>
</table></td>
                                        </tr>
                                    </table>

                
            
                                </td>
            </tr>
    </tbody>
</table>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- // END COLUMNS -->
                                    
                                    
                                    
                                    
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                      <!-- BEGIN FOOTER // -->
                                    <table bgcolor="#d2d2d2" border="0" cellpadding="0" cellspacing="0" width="600" id="templateFooter" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #d2d2d2;border-top: 0;border-bottom: 0;">
                                        <tr>
                                            <td valign="top" class="footerContainer" style="padding-bottom: 9px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;"><table border="0" cellpadding="0" cellspacing="0" width="600" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnTextBlockOuter">
        <tr>
            <td valign="top" class="mcnTextBlockInner" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                
                <table align="left" border="0" cellpadding="0" cellspacing="0" width="370" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding-top: 18px;padding-left: 18px;padding-bottom: 9px;padding-right: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;font-family: Helvetica;font-size: 11px;line-height: 125%;text-align: left;">
                        
                            <a href="http://vk.com/free_lanceru" style="word-wrap: break-word;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;font-weight: normal;text-decoration: underline;"><img align="none" height="40" src="https://gallery.mailchimp.com/232009edbbee7d0fe99b1cca1/images/vk_1.1.png" style="width: 40px;height: 40px;margin-top: 5px;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;" width="40"></a>&nbsp;<a href="http://www.facebook.com/freelanceru" style="word-wrap: break-word;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;font-weight: normal;text-decoration: underline;"><img align="none" height="40" src="https://gallery.mailchimp.com/232009edbbee7d0fe99b1cca1/images/fb_1.1.png" style="width: 40px;height: 40px;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;" width="40"></a>&nbsp;<a href="https://twitter.com/free_lanceru" style="word-wrap: break-word;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;font-weight: normal;text-decoration: underline;"><img align="none" height="40" src="https://gallery.mailchimp.com/232009edbbee7d0fe99b1cca1/images/tw_1.1.png" style="width: 40px;height: 40px;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;" width="40"></a><br>
</td>
                    </tr>
                </tbody></table>
                
                <table align="right" border="0" cellpadding="0" cellspacing="0" width="230" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding-top: 27px;padding-right: 18px;padding-bottom: 9px;padding-left: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;font-family: Helvetica;font-size: 11px;line-height: 125%;text-align: left;">
                        <span style="font-family:arial,sans-serif;"><em>Copyright © 2014, Все права защищены.</em><br>
<a href="'."{$eHost}/unsubscribe?ukey=%UNSUBSCRIBE_KEY%".'" target="_self" style="word-wrap: break-word;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;font-weight: normal;text-decoration: underline;">Отписаться от рассылки</a></span>
                        
                          
                        </td>
                    </tr>
                </tbody></table>
                
            </td>
        </tr>
    </tbody>
</table></td>
                                        </tr>
                                    </table>
                                    <!-- // END FOOTER -->
                                </td>
                            </tr>
                        </table>
                        <!-- // END TEMPLATE -->
                    </td>
                </tr>
            </table>
        </center>
    </body>
</html>';

// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$DB = new DB('master');
$cnt = 0;



$limit = 8000;  
$sql = "
SELECT u.uid, u.login, u.email, u.uname, u.usurname, usk.key AS ukey 
FROM freelancer AS u 
LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid 
WHERE substring(u.subscr from 8 for 1)::integer = 1 AND u.is_banned = B'0' AND ( NOW() - u.last_time ) < '2 year' 
AND u.uid NOT IN (SELECT tservices.user_id FROM tservices WHERE tservices.active=true AND tservices.deleted=false)
";

$sender = $DB->row("SELECT * FROM users WHERE login = ?", $sender);
if (empty($sender)) {
    die("Unknown Sender\n");
}

$mail = new smtp;
$mail->subject = $eSubject;  // заголовок письма
$mail->message = $eMessage; // текст письма
$mail->recipient = ''; // свойство 'получатель' оставляем пустым
$spamid = $mail->send('text/html');
if (!$spamid) die('Failed!');
// с этого момента рассылка создана, но еще никому не отправлена!
// допустим нам нужно получить список получателей с какого-либо запроса
$i = 0;
$mail->recipient = array();
$res = $DB->query($sql);
while ($row = pg_fetch_assoc($res)) {
    if($row['email'] == '') continue;
    if ( strlen($row['ukey']) == 0 ) {
        $row['ukey'] = users::writeUnsubscribeKey($row["uid"], true);
    }
    $mail->recipient[] = array(
        'email' => $row['email'],
        'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 
                         'USER_LOGIN' => $row['login'],
                         'SBR_ID' => $row['sbr_id'],
                         'DATE_SBR' => date('d.m.Y', strtotime($row['closed'])),
                         'UNSUBSCRIBE_KEY' => $row['ukey'])
    );
    if (++$i >= 30000) {
        $mail->bind($spamid);
        $mail->recipient = array();
        $i = 0;
    }
    $cnt++;
}
if ($i) {
    $mail->bind($spamid);
    $mail->recipient = array();
}

echo "OK. Total: {$cnt} users\n";