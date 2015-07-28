{{include "header.tpl"}}
<div class="body clear">
    <div class="main  clear">
        <h2>СМИ о Фри-лансе</h2>
        <div class="rcol-big">
            <div class="press-center clear">
                {{include "press_center/press_menu.tpl"}}
                <div class="pc-content">
                    <div style="float:right;">[<a href="javascript:void(0);" onclick="history.go(-1);"><strong style="font-weight:bold;">Назад</strong></a>]</div>
                    <h3><?=reformat($$title);?></h3>
                    <div class="pc-text oldStyles">
                        <?=reformat($$text);?>
                        <br><br>
                        <div style="color: rgb(102, 102, 102); font-size: 11px;"><?=reformat($$sign);?></div><br/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{include "footer.tpl"}}