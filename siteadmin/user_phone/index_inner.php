<h3>Пользователи / Мобильные телефоны</h3>
<!-- Фильтр старт -->
<a name="a_user_search_filter" id="a_user_search_filter"></a>
<div class="form form-acnew">
	<b class="b1"></b>
	<b class="b2"></b>
	<div class="form-in">
        <h4 class="toggle"><a href="javascript:void(0);" onclick="var mySlide = new Fx.Slide('slideBlock').toggle();" class="lnk-dot-666">Фильтр</a></h4>
        <div id="slideBlock" class="slideBlock">
            <form name="frm_user_phone_filter" id="frm_user_search_filter" method="GET">
                <input type="hidden" id="cmd" name="cmd" value="filter">
            <div class="form-block first">
                <div class="form-el">
                    <label class="form-l">Телефон:</label>
                    <div class="form-value fvs">
                        <input value="<?=$search_phone?>" name="search_phone" id="search_phone" type="text" class="i-txt fvsi" /><br/>
						<div class="b-check">
							<input id="search_phone_exact" class="b-check__input" name="search_phone_exact" type="checkbox" value="1" <?=($search_phone_exact ? ' checked="checked"' : '')?> />
							<label for="search_phone_exact" class="b-check__label b-check__label_fontsize_13">точное совпадение</label>
						</div>
                    </div>
                </div>
            </div>
            <div class="form-block last">
                <div class="form-el form-btns">
                    <button type="submit">Найти</button>
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

<?php 

if ( $users ) { 
    foreach ( $users as $aOne ) {  
        $sObjName = $aOne['uname'] .' '. $aOne['usurname'] .' ['. $aOne['login'].']';
        $sObjLink = "/users/{$aOne['login']}";
?>
    <div class="search-item c">
        <div class="div-user">
        	<a target="_blank" href="<?=$sObjLink?>"><?=view_avatar($aOne['login'], $aOne['photo'], 1)?></a><br>
        </div>
        <div class="search-item-info">
            <h4><?=view_mark_user(array(
                                    "login"      => $aOne['login'],
                                    "is_pro"  => $aOne['is_pro'],
									"is_pro_test" => $aOne['is_pro_test'],
									"is_team"     => $aOne['is_team'],
									"role"        => $aOne['role']), '', true, '');
            ?><?=$session->view_online_status($aOne['login'], false, '')?><a target="_blank" href="<?=$sObjLink?>" class="<?=(is_emp($aOne['role']) ? 'employer' : 'freelancer')?>-name"><?=$sObjName?></a></h4>
            <div class="safety">
                <b>Телефон (юр. лицо):</b> <span id="email_value<?=$aOne['uid']?>" class="safetyvalue"><?=$aOne['_2_mob_phone']?></span><br/>
                <b>Телефон (физ. лицо):</b> <span id="email_value<?=$aOne['uid']?>" class="safetyvalue"><?=$aOne['_1_mob_phone']?></span>
            </div>
            <div class="safety">
                <a href="<?=$sObjLink?>/setup/finance/" target="_blank" class="lnk-dot-999">Изменить</a>
            </div>
        </div>
    </div>
<?php
    }
      
    if ( $pages > 1 ) {
        $sHref = e_url( 'page', null );
        $sHref = e_url( 'page', '', $sHref );
        echo get_pager2( $pages, $page, $sHref );
    }
} elseif ( $cmd == 'filter' ) { ?>
    Нет пользователей, удовлетворяющих условиям выборки
<?php } //elseif?>

</div>

<script type="text/javascript">
window.addEvent('domready', function() {
    <?
    if ( $sZeroClipboard ) {
        echo 'ZeroClipboard.setMoviePath("'.$GLOBALS['host'].'/scripts/zeroclipboard/ZeroClipboard.swf");';
        echo $sZeroClipboard;
    }
    ?>
});
</script>