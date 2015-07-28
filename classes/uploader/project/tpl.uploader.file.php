<div class="qq-progress-bar" id="uploader.progress{idFile}" style="display:none">
    <div class="quiz-progress-num qq-progress-bar-percent" style="width:0%">0%</div>
    <div class="quiz-progress-bar">
        <div class="quiz-progress-bar qq-progress-bar-line" style="width:0%;background-color:#74bb54;"></div>
    </div>
</div>

<div class="b-icon-layout qq-upload-spinner" id="uploader.spinner{idFile}" style="display: none;">    
    <table class="b-icon-layout__table">                               
            <tr>                                   
                <td class="b-icon-layout__icon">
                    <img class="b-fon__loader load-spinner" src="/images/loader-gray.gif" alt="" height="24" width="24">
                </td>                                
                <td class="b-icon-layout__files qq-upload-spinner-text">Идет загрузка файла…</td>                              
                <td class="b-icon-layout__size">&nbsp;</td>                                   
                <td class="b-icon-layout__operate">&nbsp;</td>                               
            </tr>                            
    </table>                       
</div> 

<div class="b-icon-layout" id="uploader.file{idFile}" file="" style="display:none" onComplete="var e = $$('.qq-upload-project').getElements('.b-fon__item'); var m = new Array();  e[0].each(function(elm) { if(elm.getStyle('display') != 'none') { m.push(elm); } elm.removeClass('b-fon__item_last'); }); (m.length && m[m.length-1].addClass('b-fon__item_last'));">
    <table class="b-icon-layout__table">                                    
            <tr>                                        
                <td class="b-icon-layout__icon"><i class="b-icon b-icon_top_-1 qq-upload-ico"></i></td>                                       
                <td class="b-icon-layout__files"><div class="b-layout__txt"><a class="b-icon-layout__link qq-upload-file" href="javascript:void(0)" target="_blank">{fileName}</a></div></td>                                       
                <td class="b-icon-layout__size"><div class="b-layout__txt b-layout__txt_fontsize_11 qq-upload-size"></div></td>    
                <td class="b-icon-layout__operate"><div class="b-layout__txt"><a class="b-layout__link b-layout__link_dot_c10600 b-layout__link_fontsize_11 qq-upload-delete" href="javascript:void(0)">Удалить</a></div></td>                                   
            </tr>
    </table>
</div>
