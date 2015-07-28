<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/sbr.common.php' );
$xajax->printJavascript( '/xajax/' );
?>
<h3>Документы ИТО</h3>
<!-- Фильтр старт -->
<div class="form form-acnew form-payd_advice">
    <b class="b1"></b>
    <b class="b2"></b>
    <div class="form-in">
        <div id="slideBlock" class="slideBlock">
            <form method="GET">
                <input type="hidden" id="cmd" name="cmd" value="generate">
                
                <div class="form-block first">
                    <div class="form-el">
                        <label class="form-l"><b>Период</b></label>
                        <div class="form-value fvs">
                            <select name="month">
                            <? foreach($months as $n=>$m) { ?>
                                <option value="<?=$n?>" <?= ( $n == date('n') ? 'selected="selected"' : '' )?>><?=$m?></option>
                            <? } ?>
                            </select>
                            <select name="year">
                            <? for($y=2012;$y<=date('Y');$y++) { ?>
                                <option value="<?=$y?>"  <?= ( $y == date('Y') ? 'selected="selected"' : '' )?>><?=$y?></option>
                              <? } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-el">
                        <label class="form-l"><b>Формат документа</b></label>
                        <div class="form-value fvs">ODT <input type="radio" name="doc" value="odt" />&nbsp;&nbsp;XLSX <input type="radio" name="doc" value="xlsx" checked/></div>
                    </div>
                </div>
                <div class="form-block last">
                    <div class="form-el form-btns">
                       <button type="submit">Сгенерировать</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <b class="b2"></b>
    <b class="b1"></b>
</div>
<!-- Фильтр стоп -->

<div class="search-lenta">

<? if ($docs_ito) { ?>
    <iframe src="about:blank" name="formframe" style="width:1px;height:1px;visibility:hidden;"></iframe>
    
    <div class="search-item c">
        <span style="display:inline-block;text-align:center;width:100px;">
            <b>Месяц</b>
        </span>
        <span style="display:inline-block;text-align:center;width:150px;">
            <b>Дата создания</b>
        </span>
        <span style="display:inline-block;text-align:center;width:200px;">
            <b>Документ</b>
        </span>
        <span style="display:inline-block;text-align:center;width:250px;">
            <b>Действия</b>
        </span>
    </div>
    
    <? foreach ($docs_ito as $doc) { $ext = substr(strrchr($doc['fname'], "."), 1); ?>
        <div class="search-item c">
            <span style="display:inline-block;text-align:center;width:100px;">
                <?= $months[date('n', strtotime($doc['date_period']))]?> <?= date('Y', strtotime($doc['date_period']))?>
            </span>
            <span style="display:inline-block;text-align:center;width:150px" id="date_create_<?=date('Yn', strtotime($doc['date_period']));?>">
                <?= date("d.m.Y H:i", strtotime($doc['date_create']));?>
            </span>
            <span style="display:inline-block;text-align:left;width:200px;">
                <a href="<?= WDCPREFIX?>/<?=$doc['path'].$doc['fname']?>" target="_blank" id="file_name_<?=date('Yn', strtotime($doc['date_period']));?>">Скачать ИТО за <?= $months[date('n', strtotime($doc['date_period']))]?> <?= date('Y', strtotime($doc['date_period']))?>.<?= $ext;?></a>
            </span>
            <span style="display:inline-block;text-align:center;width:100px;padding-bottom:5px;">
                <input type="button" value="переформировать" onclick="xajax_aCreateDocITO('<?=$doc['date_period']?>', '<?=$ext;?>')"/>
            </span>
            <span style="display:inline-block;text-align:center;width:150px;">
                <form action="" method="POST" enctype="multipart/form-data" target="formframe" id="upload_form_<?=$doc['id']?>">
                    <input type="hidden" name="doc_id" value="<?= $doc['id'];?>">
                    <input type="hidden" id="cmd" name="cmd" value="upload">
                    <span class="b-post__txt b-post__txt_relative b-post__txt_overflow_hidden b-post__txt_zoom_1">
                        <input class='b-file__input b-file__input_size_auto' type='file' id='attachedfiles_file_<?=$doc['id']?>' name='attachedfiles_file' onchange="$('upload_form_<?=$doc['id']?>').submit(); window.document.body.style.cursor = 'wait';">
                        <label for="attachedfiles_file_<?=$doc['id']?>" id="upload_link_<?=$doc['id']?>"><input type="button" value="загрузить"/></label>
                    </span>
                </form>
            </span>
        </div>
    <? } ?>
    
<? } else {?>
    Документов ИТО нет.
<? } ?>

</div>

<script type="text/javascript">
    function successUploadFile(tid, fid, time, link, name) {
        $(tid).set('html', time);
        $(fid).set('href', link);
        $(fid).set('html', name);
    }
window.addEvent('domready', function() {
    <?
    if ( $sZeroClipboard ) {
        echo 'ZeroClipboard.setMoviePath("'.$GLOBALS['host'].'/scripts/zeroclipboard/ZeroClipboard.swf");';
        echo $sZeroClipboard;
    }
    ?>
});
</script>