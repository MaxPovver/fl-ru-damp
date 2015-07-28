<div class="qq-progress-bar" id="uploader.progress{idFile}" style="display:none">
    <div class="quiz-progress-num qq-progress-bar-percent" style="width:0%">0%</div>
    <div class="quiz-progress-bar">
        <div class="quiz-progress-bar qq-progress-bar-line" style="width:0%;background-color:#74bb54;"></div>
    </div>
</div>

<div class="b-fon__item b-fon__item_last qq-upload-spinner" id="uploader.spinner{idFile}" style="display: none;">    
    <table class="b-icon-layout wdh100">                               
        <tbody>
            <tr>                                   
                <td class="b-icon-layout__icon">
                    <img class="b-fon__loader load-spinner" src="/images/loader-gray.gif" alt="" height="24" width="24">
                </td>                                
                <td class="b-icon-layout__files qq-upload-spinner-text">Идет загрузка файла…</td>                              
                <td class="b-icon-layout__size">&nbsp;</td>                                   
                <td class="b-icon-layout__operate">&nbsp;</td>                               
            </tr>                            
        </tbody>
    </table>                       
</div> 

<div class="b-fon__item b-fon__item_last" id="uploader.file{idFile}" file="" style="display:none" onComplete="this._options.element.getElement('.qq-upload-image').setProperty('src', this._options.element.getElement(this._getClass('file')).getProperty('href'))">
    <table class="b-icon-layout" style="width:400px;">
        <colgroup>
            <col width="200">
            <col width="100">
            <col width="50">
            <col width="50">
        </colgroup>
        <tbody style="width:400px;">
            <tr>                                        
                <td class="b-icon-layout__icon">
                    <i class="b-icon qq-upload-ico"></i>
                    <a href="javascript:void(0)" class="ckedit-image-src">
                    <img class="qq-upload-image" src="#" style="width:100px;" onclick="var p = $(this).getParent('div.cke_dialog_body'); p.getElement('a[id^=cke_info]').click(); var i = p.getElement('div.cke_dialog_page_contents:first-child table tr:first-child input'); i.set('value', this.src); i.focus()"/>
                    </a>
                </td>                                       
                <td class="b-icon-layout__files">
                    <a class="b-icon-layout__link qq-upload-file" href="javascript:void(0)" target="_blank">{fileName}</a>
                </td>                                       
                <td class="b-icon-layout__size qq-upload-size"></td>    
                <td class="b-icon-layout__operate"><a class="b-icon-layout__link b-icon-layout__link_dot_a23e3e qq-upload-delete" href="javascript:void(0)">Удалить</a></td>                                   
            </tr>
        </tbody>                                
    </table>
</div>