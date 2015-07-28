<script type="text/javascript">
var curFBulletsBox = 2;
var ac_sum = <?= $_SESSION['ac_sum'];  ?>;
var op     = <?= freelancer_offers::SUM_FM_COST; ?>;

function CheckFlds() {
    var error = 0;
    <?php if(!$is_edit){ ?>
    if (op > ac_sum) {
        $('error_buy').set('html', 'На вашем счету не хватает ' + (Math.ceil((op - ac_sum)*100)/100) + ' FM для покупки. <a id="no_money_link" href="/bill/">Пополнить счет</a>');
        $('error_no_money').setStyle('display', 'block');
        error = 1;
    } 
    <?php } //if?>
    var filled = $('button').hasClass('btn-filled') ? true : false; // была ли форма полностью заполнена
    if(($('f1').get('value').trim())=='') {
        if (filled)
        {
            $('offer_title_error').setStyle('display','block');
        }        
        error = 1;
    } 
    if(($('f2').get('value').trim())=='') {
        if (filled)
        {
            $('offer_descr_error').setStyle('display','block');
        }
        error = 1;
        
    } 
    var category_error = 0;
    var el = $('cat_line');
    if(el.getElement('select[name^=categories]').value==0) {
        if (filled)
        {
            $('offer_category_error').setStyle('display','block');
        }
        error = 1;
    } 
    if(error==1) {
        $('button').addClass('b-button_disabled');
        return false;
    }
    else
    {
        $('button').addClass('btn-filled');
    }
    return true;
}
function ClearErrorBox(id_error_box) {
    if (!$('button').hasClass('btn-filled')) // если форма не была ни разу полностью заполнена
    {
        CheckFlds();
    }
    
    $(id_error_box).setStyle('display', 'none');
    if($('error_no_money') == undefined) {
        $('button').removeClass('b-button_disabled'); 
        return true;
    }
    if($('error_no_money').getStyle('display') == 'none' && $('button').hasClass('btn-filled')) {
        $('button').removeClass('b-button_disabled');    
    }
}
</script>
<? include_once($_SERVER['DOCUMENT_ROOT'].'/filter_specs.php');?>
<div class="lancer-public">
    <h2><?= ($is_edit?"Редактирование предложения":"Публикация предложения"); ?></h2><br/>
    <form action="/public/offer/" id="frl_offers" method="POST" onsubmit="return false;">
        <?php if(!$is_edit) { ?>
        <input type="hidden" name="action" value="create">
        <?php } else { //if?>
        <input type="hidden" name="action" value="update"> 
        <input type="hidden" name="fid" value="<?=$fid?$fid:$offer['id']?>"> 
        <input type="hidden" name="red" value="<?=($back = __paramInit('string', 'red', 'red', ''))?>">
        <input type="hidden" name="page" value="<?= intval($_GET['page'])?>">
        <?php } // else?>      
        <fieldset>
            <div class="form-block">
                <div class="form-el">
                    <label class="form-l">Заголовок</label>
                    <div class="form-value">
                        <input type="text" class="i-txt" name="title" id="f1" maxlength="<?= freelancer_offers::MAX_SIZE_TITLE;?>" value="<?=htmlspecialchars($offer['title']?$offer['title']:stripslashes($_POST['title']))?>" onkeydown="ClearErrorBox('offer_title_error')">
                        <?php if($error['title_max']) {?>
                        <div class="attention" id="offer_descr_max_error"><p>&nbsp;<strong>Максимальное количество символов <?= freelancer_offers::MAX_SIZE_TITLE ?></strong></p></div> 
                        <?php } //if?>
                        <div class="attention" style="display:none;" id="offer_title_error"><p>&nbsp;<strong>Поле не заполнено</strong></p></div>
                    </div>
                </div>
                <div class="form-el">
                    <label class="form-l">Текст</label>
                    <div class="form-value">
                        <textarea  class="tawl" cols="40" rel="<?= freelancer_offers::MAX_SIZE_DESCRIPTION ?>" rows="10" name="descr" id="f2" onkeydown="ClearErrorBox('offer_descr_error');"><?=htmlspecialchars($offer['descr']?$offer['descr']:stripslashes($_POST['descr']))?></textarea>
                        <br/>
                        <?php if($error['descr_max']) {?>
                        <div class="attention" id="offer_descr_max_error"><p>&nbsp;<strong>Максимальное количество символов <?= freelancer_offers::MAX_SIZE_DESCRIPTION ?></strong></p></div> 
                        <?php } //if?>
                        <div class="attention" style="display:none;" id="offer_descr_error"><p>&nbsp;<strong>Поле не заполнено</strong></p></div>  
                    </div>
                </div>
            </div>
            <div class="form-block">
                <div class="form-el">
                    <label class="form-l">Раздел</label>
                    <div class="select-group">
                        <div class="form-value" id="cat_line">
                            <select name="categories"  onchange="RefreshSubCategory(this); ClearErrorBox('offer_category_error');">
                                <option value="0">Выберите раздел</option>
                                <?php foreach($categories as $cat) { if($cat['id']<=0) continue; ?>
                                <option value="<?=$cat['id']?>" <?=(($cat['id'] == $offer['category_id'] || $cat['id'] == $_POST['categories']) ? ' selected' : '')?>><?=$cat['name']?></option>
                                <?php } // foreach ?>
                            </select> 
                            <br/>
                            <select name="subcategories" <?if($offer['category_id']==0 && !$_POST['subcategories']):?>disabled<?endif;?> class="subcat">
                                <option value="0" <? if($ccat['subcategory_id'] == 0) echo "selected"; ?>>Все специализации</option>
                                <?php for ($i=0; $i<sizeof($categories_specs); $i++) { ?>
                                <option value="<?=$categories_specs[$i]['id']?>"<? if ($categories_specs[$i]['id'] == $offer['subcategory_id'] || $categories_specs[$i]['id'] == $_POST['subcategories']) echo(" selected") ?>><?=$categories_specs[$i]['profname']?></option>
                                <?php } //for ?>
                            </select>
                            <div class="attention" style="display:none;" id="offer_category_error"><p>&nbsp;<strong>Не выбран раздел и подраздел</strong></p></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if(!$is_edit) {?>
            <div class="form-block">
                <div class="form-el">
                    <label class="form-l">Стоимость</label>
					<div class="price"><?= freelancer_offers::SUM_FM_COST?> FM</div>
                    <div class="attention" id="error_no_money" style="display:none">
                        <p><strong>Недостаточно средств на счету</strong></p>
                        <p id="error_buy">В данный момент на счету <?= $_SESSION['ac_sum'];  ?> FM <a href="#">Пополнить счет</a></p>
                    </div>
                </div>
            </div>
            <?php } //if?>
            <br/>
            <a id="button" class="b-button b-button_margleft_115 b-button_rectangle_color_transparent <?= (!$is_edit?"b-button_disabled":"")?>" onclick="if( CheckFlds() && $('button').hasClass('b-button_disabled') == false ) { $('button').addClass('b-button_disabled'); $('frl_offers').submit(); }" href="javascript:void(0)">
                <span class="b-button__b1">
					<span class="b-button__b2">
						<span class="b-button__txt"><?= ($is_edit==true?"Сохранить":"Опубликовать")?></span>
					</span>
				</span>
            </a>
            <?php if($is_edit) { ?>
            &nbsp;&nbsp;&nbsp;
            <a class="b-button b-button_rectangle_color_transparent" onclick="window.location = '/projects/?kind=8'">
				<span class="b-button__b1">
					<span class="b-button__b2">
						<span class="b-button__txt">Отменить</span>
					</span>
				</span>
			</a>
            <?php }//if?>
            <p style="margin-top: 10px;">
            Подробное описание назначения раздела &laquo;Сделаю&raquo; и порядка размещения объявления<br/>находится <a href="https://feedback.fl.ru/">здесь</a>.
            </p>
        </fieldset>
    </form>
    <div class=" public-inf">
        <h3>Дайте работодателям больше шансов найти именно вас</h3>
        <p>Публикуя предложения на главной странице, вы значительно увеличиваете приток новых работодателей.</p>
    </div>
</div>