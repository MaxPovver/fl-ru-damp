<script type="text/javascript">
    /**
     * 
     **/
    function about_docs_showInfo(info, name, link, old_link, rename_name) {
        $('newlink_block').setStyle('display', 'none');
        $('oldlink_block').setStyle('display', 'none');
        $('msg').set('text', '');
        $('err_msg').set('text', '');
        if (name) {
            $('msg').set('text', info);
            $('newlink').set('html', '<a href="' + link + '" >' + link + '</a>');
            $('newlink_block').setStyle('display', null);
            if (old_link) {
                $('oldlink').set('html', '<a href="' + old_link + '" >' + rename_name + '</a>');
                $('oldlink_block').setStyle('display', null);
            }
        } else {
            $('err_msg').set('text', info);
        } 
    }
</script><h2 style="color:#666666; font-family:arial; font-size:20pt;" >Загрузка файлов на DAV сервер</h2>
<div style="color:#a0a0a0 solid 1px; pading 5px 7px;">
<iframe id="upload" scrolling="no" src="/siteadmin/davupload/?mode=files&view=form&v=1" border="0" style="border:none; overflow: hidden;width:100%; height:250px; border:solid 1px #a0a0a0" ></iframe>
<div id="uploadresult" style="text-align:;left; margin-left:25px; font-size:11pt;">
    <span style="color:green;" id="msg"></span><br>
    <span style="color:red;" id="err_msg"></span><br>
    <div id="newlink_block" style="display:none"><span>Cсылка на файл: </span><span id="newlink">http://dhsajkdhsajdkhasjkhsd.dsj/dsadad/dsadasd/dsadasdsad.txt</span></div>
    <div id="oldlink_block" style="display:none"><span>Cсылка на старый файл: </span><span  id="oldlink">http://dhsajkdhsajdkhasjkhsd.dsj/dsadad/dsadasd/dsadasdsad.txt</span></div>
</div>