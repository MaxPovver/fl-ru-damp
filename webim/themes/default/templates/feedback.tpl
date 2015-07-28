<!--{include file='control/head.tpl'}--> 
        <div class="header">
            <!--{include file='control/header_inner.tpl' close_class="window-close-simple"}-->
            <h1><a href="<!--{$url}-->" onclick="window.open('<!--{$url}-->');return false;"><img src="<!--{$logo}-->" alt="<!--{$company}-->" /></a></h1>
            <h2><!--{get_res code="consult.title"}--> / <!--{get_res code="consult.title3"}--></h2>
            <link type="text/css" rel="stylesheet" href="/css/nav.css" />
            <script type="text/javascript">/*Будет объединен с feedback.js позже, когда понадобится добавить новый функционал на страницу обращения в обратку. */
                var formHasError = 0; // 1  - Файл слишком велик, 2 - Файл слишком мал 
                function checkSizeOfFile(evt) {
                    formHasError = 0;
                    if ( !evt.target.files ) {
                        showErrorMsg(evt.target);
                        return;
                    }
                    var css = 'btnr-disabled';
                    if ( $('fb-agree1').checked ) {
                        $('feedback-send1').removeClass(css);
                    }
                    for (var i = 0; i < evt.target.files.length; i++) {
                        if (evt.target.files[i].size > 5 * 1024 * 1024 || evt.target.files[i].size == 0) {
                            if ( evt.target.files[i].size != 0 ) formHasError = 1;
                             else formHasError = 2;
                            $('feedback-send1').addClass(css);
                        }
                    }
                    showErrorMsg(evt.target);
                }
                function showErrorMsg(input) {
                    var v = "none";
                    if (formHasError) {
                        v = "block";
                    }
                    var n = 1;
                    var ls = $$("input.i-file");
                    for (var j = 0; j < ls.length; j++) {
                        var f = false;
                        if (formHasError) {
                            f = true;
                        } else {
                            f = false;
                        }
                        if (ls[j] == input) {
                            n = j + 1;
                            f = false;
                        }
                        ls[j].disabled = f;
                    }
                    $("err_attach_1").setStyle("display", v).setStyle("margin-top", 28*n + 'px').getElements("strong")[0].set("text", formHasError == 1 ?  "Файл слишком велик" : "Нельзя загрузить пустой файл");
                }
                //$$("div.container")[0].set("min-width", null);
                var ls = document.getElementsByTagName("div");
                for (var i = 0; i < ls.length; i++) {
                   // if (ls[i].className == "container") {                        
                   //     ls[i].setAttribute("style", "min-width:0px !important");
                   // }
                }
                window.resizeTo(620, 773);
                var departList = {
                        0:"Выберите раздел",
                        1:"Вопрос по сервисам сайта",
                        2:"Обнаружена ошибка на сайте",
                        8:"Жалоба на обман со стороны пользователя",
                        5:"Безопасная сделка",
                        3:"Финансовый вопрос",
                        10:"Реклама",
                        7:"Ваши предложения по улучшению сайта Free-lance.ru",
                        6:"Отправить жалобу руководству на Службу поддержки"
               };
              var webimAction = "refresh";
              function asyncSend() {
                  var cl = $('feedback-send1').className;
                  if(cl.indexOf('disabled') + 1) {
                      return false;
                  }
                  webim = true;
                  var frm = new formasync();
                  frm.index = 1;
                  webimAction = "close";
                  $("backToChatLink").style.display = "none";
                  frm.send(document.getElementById('feedbackform'));
                  return false;
              }
              window.addEvent('load', function() {
                     new mAttach2(document.getElementById('files_block1'), 10, {p:'btn-add', m:'btn-del', nv:true, onRemove:onRemoveFile, onAdd:onAddFile});
                     try {
	                     $("fb-agree1").addEvent(
	                         'change',
	                         function () {
	                             var css = 'btnr-disabled';
	                             if (this.checked && !formHasError) {
	                                 $('feedback-send1').removeClass(css);
	                             } else {
	                                 $('feedback-send1').addClass(css);
	                             }
	                         }
	                     );
	                     } catch(e) { //IE8
	                        $("fb-agree1").onclick = function() {
                                 if (this.checked && !formHasError) {
                                     $('feedback-send1').className = $('feedback-send1').className.replace(/\s?btnr-disabled\s?/, " "); 
                                 } else {
                                     $('feedback-send1').className += " btnr-disabled";
                                 }
                            }
	                     }
                         function onRemoveFile(fileBlock) {
                             if (Browser.ie && Browser.version == 8) {
                                 return;
                             }
                             var input = fileBlock.getElement("input.i-file");
                             if (input.files) {
                                 for (var i = 0; i < input.files.length; i++) {
                                     if (input.files[i].size > 5 * 1024 * 1024) {
                                         formHasError = 0;
                                         if ( $('fb-agree1').checked ) {
                                             $('feedback-send1').removeClass('btnr-disabled');
                                         }
                                         showErrorMsg(fileBlock);
                                     }
                                 }
                             }
                         }
                         function onAddFile(input, n){
                             if ( input.addEventListener ) {
                                input.addEventListener("change", checkSizeOfFile, false);
                             }
                             if (formHasError) {
                                 input.disabled = true;
                             }
                         }
                         
                     if ( $("files_block1").addEventListener ) {
                        $("files_block1").addEventListener("change", checkSizeOfFile, false);
                     }
                 }
              );
              var stopProcess = false;
              window.interval = setInterval(
              function () {
                  if (!window['sendRefresh']) {
                      window['sendRefresh'] = true;
                      var req = new Request({
                        url: '/webim/thread.php', 
                        onSuccess: function(){
                            window['sendRefresh'] = false;
                            if (stopProcess) {
                                if ( !(Browser.ie && Browser.version <= 8) ) {
                                    clearInterval(window.interval);
                                }
                            }
                        },
                        onFailure: function(){
                            window['sendRefresh'] = false;
                            if (stopProcess) {
                                if ( !(Browser.ie && Browser.version <= 8) ) {
                                    clearInterval(window.interval);
                                }
                            }
                        }
                    });
                    if ( webimAction == "close" ) {
                        stopProcess = true;
                    }
                    req.post("act=" + webimAction + "&thread=" + GET('thread') + '&token=' + GET('token') + '&lastid=' + GET('lastid') + '&visitor=true');
                  }
              }
              , 2000);
            </script>
        </div>
        <div class="content">
            <form id="feedbackform" action="feedback.php" method="post" enctype="multipart/form-data">
                <div class="form">
                                        <b class="b1"></b>
                                        <b class="b2"></b>
                                        <div class="form-in">
                                        <div class="b-layout__txt b-layout__txt_padbot_10"><a href="<!--{$chaturi}-->" class="b-layout__link" style="display:<!--{if isset($hidebacklink)}-->none<!--{else}-->block<!--{/if}-->;" id="backToChatLink">Вернуться к чату</a></div>
                                        
                                        
                                    <div class="form fs-o" style="padding-bottom:15px">
                                        <b class="b1"></b>
                                        <b class="b2"></b>
                                        <div class="form-in b-check">
                                            <input type="checkbox" id="fb-agree1" name="fb-agree1" value="" class="b-check__input"><label for="fb-agree1" class="b-check__label b-check__label_fontsize_13">Я подтверждаю, что не нашел ответов на свой вопрос в <a href="http://feedback.free-lance.ru" class="b-layout__link" target="_blank">разделе помощи</a></label>
                                        </div>
                                        <b class="b2"></b>
                                        <b class="b1"></b>
                                    </div>
                                    
                                    <div class="b-form b-form_clear_both">
                                        <div class="b-form__name b-form__name_padtop_5 b-form__name_fontsize_13">Тема:</div>
                                        <div class="b-combo b-combo_inline-block">
                                            <div class="b-combo__input b-combo__input_width_170  b-combo__input_arrow_yes b-combo__input_multi_dropdown b-combo__input_init_departList show_all_records b-combo__input_resize drop_down_default_0">
                                                <input type="text" value="Выберите раздел" size="80" id="department" class="b-combo__input-text" readonly onchange="$('err_department_1').style.display='none';" onfocus="$('err_department_1').style.display='none';" style="height:30px" />
                                                <span class="b-combo__arrow"></span> 
                                            </div>
                                        </div>
                                        
                                        <div class="tip-out">
                                            <div style="display:none;" class="tip tip-t2" id="err_department_1">
                                                <div class="tip-in">
                                                    <div class="tip-txt">
                                                        <div class="tip-txt-in">
                                                            <span class="middled"><strong>Необходимо выбрать тему вопроса</strong></span>
                                                        </div>
                                                    </div>
                                                 </div>
                                            </div>
                                       </div>
                                        
                                    </div>
                                            <div style="clear:both; padding:0;" class="form-block first">
                                                                                            <div class="form-el">
                                                    <label class="form-label2">* Изложите суть вопроса:</label>
                                                    <div class="b-textarea">
                                                        <textarea id="fb-msg1" name="message" rows="5" cols="20" class="b-textarea__textarea" ></textarea>
                                                    </div><div class="tip-out"><div id="err_msg_1" class="tip tip-t2" style="display:none;"><div class="tip-in"><div class="tip-txt"><div class="tip-txt-in"><span class="middled"><strong>Вы должны заполнить это поле</strong></span></div></div></div></div></div>
                                                    <div class="ffs-lnk-files">
                                                        Прикрепить файлы к сообщению
                                                    </div>
                                                    <div class="cl-form-files c">
                                                        <ul class="form-files-list">
                                                            <li class="c" id="files_block1"><div style="overflow:hidden; float:left; width:256px;"><input type="file" name="attach[]"  size="23" class="i-file"></div>
                                                            </li>
                                                        </ul>
                                                        <div class="tip-out">
                                                            <div id="err_attach_1" class="tip tip-t2" style="display:none; margin-top: 28px;" onclick="$('err_attach_1').style.display='none';">
                                                                <div class="tip-in">
                                                                    <div class="tip-txt">
                                                                        <div class="tip-txt-in">
                                                                            <span class="middled">
                                                                                <strong>Ошибка при загрузке файла</strong>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-files-inf">
                                                            <strong class="form-files-max">Максимальный размер файлов - 5 Мб</strong>
                                                            Файлы следующих форматов запрещены к загрузке: ade, adp, bat, chm, cmd, com, cpl, exe, hta, ins, isp, jse, lib, mde, msc, msp, mst, pif, scr, sct, shb, sys, vb, vbe, vbs, vxd, wsc, wsf, wsh                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-block last">
                                                <div class="form-el form-btns">
                                                    <div class="form-captcha">
                                                        <label class="label-captcha">Введите код:</label>
                                                        <input type="text" onfocus="feedbackClearError(5,1);" id="fb-rndnum1" name="captcha" value="" onkeydown="if (event.keyCode == 13) return asyncSend();" class="i-txt">&nbsp; 
                                                        <img src="/image.php?r=<!--{$RAND}-->" id="feedback-capcha1" width="130" height="60" onClick="document.getElementById('feedback-capcha1').src = '/image.php?r='+Math.random(); $('fb-rndnum1').focus();" style="vertical-align: middle;">
                                                        <a href="#" onclick="$('feedback-capcha1').src = '/image.php?r='+Math.random(); $('fb-rndnum1').focus(); return false;">Обновить код</a>
                                                        <input type="hidden" name="u_token_key" value="<!--{$u_token_key}-->" />
                                                        <input type="hidden" name="feedback-success1" />
                                                       <div class="tip-out"><div id="err_captcha_1" class="tip tip-t2" style="display:<!--{if isset($errorcaptcha)}-->block<!--{else}-->none<!--{/if}-->;"><div class="tip-in"><div class="tip-txt"><div class="tip-txt-in"><span class="middled"><strong>Заполнено с ошибкой</strong></span></div></div></div></div></div>
                                                    </div>
                                                    <a href="javascript:void(0);" onclick="return asyncSend();" id="feedback-send1" class="btnr btnr-disabled btnr-green2"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Отправить сообщение</span></span></span></a>
                                                </div>
                                            </div>
                                        </div>
                                        <b class="b2"></b>
                                        <b class="b1"></b>
                                    </div>
            </form>
        </div>
<!--{include file='control/foo.tpl'}-->
