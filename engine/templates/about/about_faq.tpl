{{include "header.tpl"}}
<div class="body clear">
    <div class="main  clear">
        <h2>О проекте</h2>
        <div class="rcol-big">
            <div class="press-center clear">
                {{include "press_center/press_menu.tpl"}}
                <div class="pc-content">
                    <? if($$faq_el) : ?>
                        <? if(hasPermissions('about')) { ?>
                            <div style="float:right;">[<a href="javascript:void(0);" onclick="admin.openPopup('faq', <?=$$faq_el["id"];?>);">Редактировать вопрос</a>]</div>
                        <? } ?>
                        <h3><?=$$faq_el["question"];?></h3>
                        <?=$$faq_el["answer"];?>
                    <? else: ?>
                        <? if(hasPermissions('about')) { ?><div style="float:right;">[<a href="javascript:void(0);" onclick="admin.openPopup('faq', 0); return false;">Добавить вопрос</a>]</div><? } ?>
                        <? if($$razdels) foreach($$razdels as $razdel) :?>
                            <? if(sizeof($$faq[$razdel["id"]])) : ?>
                                <h4><?=$razdel["name"];?>
                                    <? if(hasPermissions('about')) { ?>
                                        <a href="javascript:void(0);" onclick="admin.loadAndExec('faqList', 'faqClass.editRadzelItem', [<?=$razdel["id"];?>, '<?= $razdel["name"];?>', function() {admin.reload()}]);"><img height="19" width="20" border="0" align="absmiddle" src="/team/images/ico_edit.gif" alt="Редактировать раздел вопросов"/></a>
                                    <? } ?></h4>
                                
                                <? foreach($$faq[$razdel["id"]] as $faq_el) :?>
                                    <a href="/about/faq/<?=($faq_el["url"]?$faq_el["url"]:"id/" . $faq_el["id"]);?>/"><?=$faq_el["question"];?></a>
                                    <? if(hasPermissions('about')) { ?>
                                        <a href="javascript:void(0);" onclick="admin.openPopup('faq', <?=$faq_el["id"];?>);"><img height="19" width="20" border="0" align="absmiddle" src="/team/images/ico_edit.gif" alt="Редактировать вопрос"/></a>
                                    <? } ?>
                                    <br/>
                                <? endforeach;?>
                                <br/>
                                <br/>
                                <br/>
                            <? endif;?>
                        <? endforeach;?>
                    <? endif;?> 
                </div>
            </div>
        </div>
    </div>
</div>
{{include "footer.tpl"}}