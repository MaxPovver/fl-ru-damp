{{include "header.tpl"}}

<?/* STYLE будет убран как только будет верстка, сделано тут чтобы не засорят ксс файлы лишним кодом*/?>
<style>
	.ico {
		float:left;
	}
	.text {
		float:left;
		padding-left:5px;
	}
	a.ajax {
        text-decoration: none;
        border-bottom:1px dashed #003399;
    }
    
    a.ajax:hover {
        text-decoration: none;
        border-bottom:1px dashed #6BB24B;
    }
</style>

<div class="body clear">
    <div class="main  clear">
        <h2>О проекте</h2>
        <div class="rcol-big">
            <div class="press-center clear">
                {{include "press_center/press_menu.tpl"}}
                <div class="pc-content">
                    <? if(is_moder() || is_admin()) { ?><div style="float:right;">[<a href="javascript:void(0);" onclick="admin.openPopup('cblog', 0, {afterOk:function(data) {var el = Ext.get('bl_list');el.insertHtml('beforeBegin',data.html);}}); return false;">Добавить блог</a>]</div><? } ?>
                    <? /* if(is_moder() || is_admin()): ?><div style="float:right"><a href="#bottom" onClick="$('#add_new').toggle();"><img src="/images/btn.gif"></a></div><? endif;*/ ?>
                    <h3>Корпоративный блог</h3>
                    <div id="bl_list">
                    <? if($$blogs): ?>
                    	<? foreach($$blogs as $k=>$blog): ?>
                    		 {{include "my_corporative_item.tpl"}}
                    	<? endforeach;?>
                    </div>
                    <? else: ?>
                    	<div class="pc-blog">
                        <h4><a href="">Корпоративные блоги</a></h4>
                        <p>Это корпоративный блог, здесь пока ничего нет поэтому здесь есть этот пост</p>
                        <ul class="clear">
                            <li class="pcb-comment"><a href="">Комментарии</a> <span>0</span></li>
                            <li>Опубликовано 20.02.2009 в 21:30</li>
                        </ul>
                    </div>
                    <? endif; ?>
                    
            <?=paginator($$page_corp, $$pages_corp, PAGINATOR_PAGES_COUNT, "%s/about2/corporative/page/%d/%s");?>
           <? if(0) {?> <div class="rss">
                <a href="" class="ico_rss"><img src="/images/ico_rss.gif" alt="RSS" width="36" height="14" /></a>
            </div>
            <? } ?>
                </div>                
               <a name="bottom" ></a> 
            </div>
        </div>
    </div>
</div>
{{include "footer.tpl"}}