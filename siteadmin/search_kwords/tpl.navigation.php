<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<div class="m-cl-bar-sort3">
    <a name="#tabs"></a>
    <a href="./" class="lnk-dot-666">Поисковые запросы</a></strong>
    &nbsp;&nbsp;&nbsp;
    <a href="./?tab=filters" class="lnk-dot-666">Фильтры</a>
    &nbsp;&nbsp;&nbsp;
    <a href="./?tab=rules" class="lnk-dot-666">Условия фильтрации</a>
    &nbsp;&nbsp;&nbsp;
    <a href="./?tab=top" class="lnk-dot-666">Топ запросов</a>
    &nbsp;&nbsp;&nbsp;
    <a href="javascript:void(0)" onclick="$('settingsBlock').toggle()" class="lnk-dot-666">Настройки</a>
</div>

<div id="settingsBlock" style="display: <?= $action == 'save_settings' ? 'block' : 'none'?>;">
    <br/>
    <form name="frm" method="post" action="">
        <input type="hidden" name="action" value="save_settings"/>
        <div class="form form-cnc">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <div class="form-block first">
                    <h3>Настройки</h3>
                    <div class="form-el">
                        <label class="form-l">Лимит:</label>
                        <div class="form-value">
                            <input type="text" name="min_cnt" class="sw205" value="<?= intval($settings['min_cnt']) ?>"/>
                        </div>
                    </div>
                    <div class="form-el">
                        В итоговую таблицу будут попадать только те запросы, значение коэффициента (кол-во повторов * кол-во совпадений) которых БОЛЬШЕ указанного лимита.
                    </div>
                </div>
                <div class="form-block last">
                    <div class="form-el form-btns flm">
                        <button type="submit">Сохранить</button>
                        <button onclick="$('settingsBlock').toggle();return false;">Закрыть</button>
                    </div>
                </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
    </form>
</div>