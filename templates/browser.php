<script type="text/javascript">
    function cookieSet(cname,val,days){
        var exdate=new Date();
        exdate.setDate(exdate.getDate()+days);
        document.cookie=cname+ "=" +escape(val)+
            ((days==null) ? "" : ";expires="+exdate.toGMTString());
    }
    function compatWrnClose() {
        el = document.getElementById('nots');
        el.parentNode.removeChild(el);
        cookieSet('browserCompatWrn', '1', 356);
    }
</script>
<div class="b-fon b-fon_width_full b-fon_padbot_10">
	<span class="b-fon__bord-attent"></span>
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffe679">
        <div class="b-fon__txt b-fon__txt_center"><span class="b-fon__txt b-fon__txt_bold">Ваш браузер устарел.</span> Старый браузер —&nbsp;угроза безопасности вашего компьютера. <a class="b-fon__link b-fon__link_relative b-fon__link_zindex_1" href="/browser_outdated/">Узнать подробнее о проблеме</a> </div>
	</div>
	<span class="b-fon__bord-attent"></span>
</div>
