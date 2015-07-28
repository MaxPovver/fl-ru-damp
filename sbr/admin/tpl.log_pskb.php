<h3>Логи PSKB</h3>
<br/>
<div class="form form-vigruzka">
    <b class="b1"></b>
    <b class="b2"></b>
    <div class="form-in">
        <div>
            <div class="form-block first">
                <div class="form-el">
                    <label class="form-label">Поиск</label>
                    <span class="form-input">
                        <input type="text" name="query" id="log_query" value="" title="Ищет по логу -- log LIKE '%value%'"/>
                    </span>
                </div>
                <div class="form-el">
                    <label class="form-label">Тип группы</label>
                    <span class="form-input">
                        <select name="logname" id="log_name">
                            <option value="">-- Не важно --</option>
                            <? foreach ($log_pskb->getNameGroupLog($lc_id) as $v) { ?>
                                <option value="<?= $v ?>"><?= $v ?></option>
                            <? } ?>
                        </select>
                    </span>
                </div>
            </div>
            <div class="form-block last">
                <div class="form-el form-btn">
                    <input type="button" class="i-btn" value="Посмотреть" onClick="xajax_aFindLogPSKB('<?=$lc_id?>', $('log_query').get('value'), $('log_name').get('value'))"/>
                </div>
            </div>
        </div>
    </div>
    <b class="b2"></b>
    <b class="b1"></b>
</div>
<br/>
<span id="log_content_<?=$lc_id?>"></span>