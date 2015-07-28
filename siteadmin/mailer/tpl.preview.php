<div class="b-layout">	
    <h2 class="b-layout__title b-layout__title_padbot_30">
        Предпросмотр рассылки: <a href="/siteadmin/mailer/?action=edit&id=<?=$message['id']?>"><?= stripslashes($message['subject'])?></a> 
        &#160;&#160;&#160;<a class="b-layout__link b-layout__link_fontsize_13" href="/siteadmin/mailer/">Все рассылки</a>
        <a target="_blank" class="b-layout_link b-layout__link_fontsize_13 b-layout__link_float_right" href="/siteadmin/mailer/?action=preview_only&id=<?=$message['id']?>">Только письмо</a>
    </h2>
    <iframe src="/siteadmin/mailer/?action=preview_only&id=<?=$message['id']?>" width="100%" height="1000px"></frame>
</div>	    