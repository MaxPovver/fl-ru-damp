<?php include_once("act.portfolio.php"); // Файл для обработки логики ?>
<?php 
if($user->login == $_SESSION['login']) {
    uploader::init(array(), $templates, 'portfolio');
}?>
<script type="text/javascript">
    var categoryList = new Object();
    var currencyList = {0:"USD", 1:"Евро", 2:"Руб"}
    var timeTypeList = {0:"в часах", 1:"в днях", 2:"в месяцах", 3:"в минутах"}
</script>

<?php if($_SESSION['login'] == $user->login) { ?>
    <?php if($user->is_pro!='t' && (int) $user->spec == 0) { ?>
        <div class="b-fon b-fon_pad_20">
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
                <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span><a class="b-layout__link" href="/users/<?= $user->login; ?>/setup/specsetup/">Выберите специализацию</a>. Это небходимо, чтобы попасть в каталог фрилансеров, в котором вас найдут заказчики
            </div>
        </div>
    <?php } elseif($user->is_pro!='t' && (int) $user->spec != 0) { ?>
        <div class="b-fon b-fon_pad_20">
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
                <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span>Внимание! Вы отображаетесь в каталоге только по своей специализации. Чтобы увеличить количество специализаций, необходимо перейти на аккаунт <?= view_pro();?>
            </div>
        </div>
    <?php } ?>
<? } ?>


<script type="text/javascript">var HTML_KWORDTMPL='<?=$html_keyword_js?>'</script>
<div class="prtfl " id="portfolio_info">
    <div class="prtfl-r"><a name="spec_text"></a>
        <p><?= reformat2( $sSpecText, 50, 0,  0 )?></p>
        <?php if ( hasPermissions('users') ) { ?>
        <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditProfile', '<?=$user->uid?>_0', 0, '', {'change_id': 0, 'ucolumn': 'spec_text', 'utable': 'freelancer'})">Редактировать</a>
        <?php } ?>
    </div>
    <div class="prtfl-l">
        <p><?= access_view('Специализация', '<a class="lnk-666" href="/users/'.$user->login.'/setup/specsetup/">%s</a>', $is_owner) ?>:&nbsp;&nbsp;<?=professions::GetProfNameWP($user->spec,' / ', "Нет специализации")?></p>
        <? if ($is_pro) {?><p><?= access_view('Дополнительные специализации', '<a class="lnk-666" href="/users/'.$user->login.'/setup/specaddsetup/">%s</a>', $is_owner) ?>:&nbsp;&nbsp;<?=$specs_add_string?></p><? } ?>
        <?php if($user->exp > 0) {?>
        <p>Опыт работы:&nbsp;&nbsp;<?=view_exp($user->exp)?></p>
        <?php } //if?>
        <?php if($user->in_office == 't') { ?>
        <p><strong>Ищу долгосрочную работу <span class="run-men" >в офисе</span></strong></p>
        <?php } //if?>
        <?php if ($user->cost_hour > 0) { ?>
        <p><strong>Стоимость часа работы</strong> &mdash; <span class="money"><?=view_cost2($user->cost_hour, '', '', false, $user->cost_type_hour)?></span></p>
        <?php } //if?>
        <?php if ($user->cost_month > 0) { ?>
        <p><strong>Стоимость месяца работы</strong> &mdash; <span class="money"><?=view_cost2($user->cost_month, '', '', false, $user->cost_type_month)?></span></p>
        <?php } //if?>
    </div>         
    <?php if ($is_owner) { ?>
    <div class="b-layout__txt">
        <a class="b-layout__link" href="/users/<?= $user->login; ?>/setup/portfolio/">Настроить портфолио</a>
    </div>
    <?php } //if ?>
</div>

<?php
foreach($pp as $prof_id=>$prjs) {
    if($user->is_pro != 't' && ($prof_id==professions::BEST_PROF_ID || $prof_id==professions::CLIENTS_PROF_ID)) continue;
    $pinfo = $pname[$prof_id]; 
    if(!$pinfo['id'] && !$is_owner) continue;
    $work = current($prjs);
    $is_count_project = true;
?>
<div id="professions_works_<?= $prof_id?>" class="b-profile-work">
    <script type="text/javascript">
        categoryList[<?=$prof_id?>] = '<?= ($prof_id >= 0 ? $pinfo['mainprofname'] . ' / ' : '') . $pinfo['profname']?>';
    </script>
    <div class="stripe" <?= ($pinfo['is_pro_profession'] ? ' style="background:#ffeda9"':' style="background:#E5EAF5"')?> id="profession_<?= $prof_id?>">
        <? include ("tpl.profession.item.php");?>
    </div>

    <div id="prof_works_<?= $prof_id?>" class="b-profile-work-collection">
        <? include ("tpl.portfolio.works.php");?>
    </div>
</div>
<?php }//foreach

if(!$is_count_project) { 
    if($is_owner) {
        if($is_not_spec) {
            $_SESSION['text_spec'] = true;
            $aHref = "/users/{$_SESSION['login']}/setup/portfsetup/";
        } else {
            $aHref = "/users/{$_SESSION['login']}/setup/portfolio/#prof{$first_profs['id']}";
            $_SESSION['text_spec'] = false;
        }
        ?>
        <div class="add-work-b">
        	<p>В вашем портфолио сейчас нет ни одной работы</p><br/>
            <a class="b-button b-button_flat b-button_flat_green" href="<?= $aHref?>">Добавить работу</a>
        </div>
    <?php } else {//if?>
        <h2 style="text-align: center;"><?= ($user->tab_name_id == "1"?"Нет услуг":"Нет работ")?></h2>
    <?php } //else?>
<?php } //if ?>