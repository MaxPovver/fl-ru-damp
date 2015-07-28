<div class="i-shadow">
    <div id="ov-izbr-2" class="b-shadow b-shadow_pad_20 b-shadow_width_240 b-shadow_zindex_11 b-shadow_top_40" style="display:none;">
        <input type="hidden" name="rating" id="note_rating" value="0<?//(int)$req['rating']?>">
        <input type="hidden" name="userid" id="note_userid" value="<?= $req['uid']?>">
        <?/*<ul class="izbr-choose">
            <li <?php if($req['rating'] == 1) print('class="active"');?>><a href="javascript:void(0)" onClick="$('note_rating').set('value', 1);">Положительная</a></li>
            <li <?php if($req['rating'] == 0) print('class="active"'); else if(strlen($req['rating'])==0) print('class="active"');?>><a href="javascript:void(0)" onClick="$('note_rating').set('value', 0);">Нейтральная</a></li>
            <li <?php if($req['rating'] == -1) print('class="active"');?>><a href="javascript:void(0)" onClick="$('note_rating').set('value', -1);">Отрицательная</a></li>
        </ul>*/?>
        <div class="b-textarea">
            <textarea class="b-textarea__textarea" cols="" rows="" id="notesTxt" onkeyup="checknote(this)"><?= $req['n_text']?></textarea>
        </div>
        <div class="b-buttons b-buttons_padtop_10">
            <?php /*if($req['login']){?>
                <a href="javascript:void(0)" class="btn-del" onclick="if(confirm('Вы действительно хотите удалить заметку?')) xajax_delNote(<?= $_SESSION['uid']?>, <?= $req['uid']?>)">Удалить</a>
            <?php } //if */?>
            <a class="b-button b-button_flat b-button_flat_green" href="javascript:void(0)" onclick="xajax_addNotes($('note_userid').get('value'), $('notesTxt').get('value'), $('note_rating').get('value'),  '<?= ($req['login']?"upd":"add")?>', <?=$type?>); this.addClass('b-button_disabled')">Сохранить</a>
            <span class="b-buttons__txt"><a href="#" onclick="$(this).getParent('div.b-shadow').setStyle('display', 'none'); return false;" class="b-buttons__link">Отменить</a></span>
        </div>
    </div>
</div>
