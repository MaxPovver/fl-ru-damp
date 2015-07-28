<? if (!$_SESSION['F'] && !$_SESSION['E'] && $sbr->isAdmin() && $stage->status == sbr_stages::STATUS_INARBITRAGE) { ?>
<div class="b-layout b-layout_float_right b-layout_pad_10 b-layout_margtop_20">
    <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_inline-block">Арбитр</div>&nbsp;
    <div class="b-combo b-combo_inline-block" id="arbitr_combo_div">
        <script>
            arbitrsList = {};
            <? foreach ($arbitrsList as $arbitr) { ?>
                arbitrsList['<?= $arbitr['id'] ?>'] = '<?= $arbitr['name'] ?>';
            <? } ?>
        </script>
        <style type="text/css">#arbitr_combo_div .b-layout__table {width:150px;}</style>
        <div class="b-combo__input b-combo__input_width_150  b-combo__input_arrow_yes b-combo__input_multi_dropdown b-combo__input_init_arbitrsList drop_down_default_<?= (int)$stage->arbitrage['arbitr_id'] ?>">
            <input id="selected_arbitr" class="b-combo__input-text" name="selected_arbitr" type="text" size="80" onchange="xajax_setArbitr(<?= (int)$stage->arbitrage['id'] ?>, $('selected_arbitr_db_id').get('value'))" />
            <span class="b-combo__arrow"></span>
        </div>
    </div>
</div>
<? } ?>
<div class="nr-h c">
	<div class="nr-start">
      <a href="?site=admin">Администрирование</a>
	</div>
    <div class="nr-docs" style="width:300px;padding:10px;background:<?=($_SESSION['E'] ? '#E6F5C6' : ($_SESSION['F'] ? '#E6E6E5' : '#FFCECE'))?>">
        <ul>
            <li>Вы зашли как
              <? if($_SESSION['E']) { ?>
                <a href="/users/<?=$_SESSION['E']?>/" class="employer-name">ЗАКАЗЧИК [<?=$_SESSION['E']?>]</a>
              <? } else if($_SESSION['F']) { ?>
                <a href="/users/<?=$_SESSION['F']?>/" class="freelancer-name">ИСПОЛНИТЕЛЬ [<?=$_SESSION['F']?>]</a>
              <? } else { ?>
                <a href="/users/<?=$_SESSION['login']?>/" class="arbitrage-name">АДМИНИСТРАТОР</a>
              <? } ?>
            </li>
            <li id="admin_access_sw">
                <?php if(hasPermissions('sbr')) {?>
                <a href="javascript:;" class="lnk-dot-blue" onclick="document.getElementById('admin_access_bx').style.display='block';this.parentNode.style.display='none'">Сменить уровень доступа</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <?php }//else?>
                <a href="<?=($site_uri ? $site_uri.'&' : '?')?>access=U">Выйти</a>
            </li>
            <li id="admin_access_bx" style="display:none">
              <? if(!$_SESSION['E'] && $sbr->emp_login) { ?>
              <a href="<?=($site_uri ? $site_uri.'&' : '?')?>access=A&E=<?=$sbr->emp_login?>">Заказчик</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <? } if(!$_SESSION['F'] && $sbr->frl_login) { ?>
              <a href="<?=($site_uri ? $site_uri.'&' : '?')?>access=A&F=<?=$sbr->frl_login?>">Исполнитель</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <? } if($_SESSION['E'] || $_SESSION['F']) { ?>
              <a href="<?=($site_uri ? $site_uri.'&' : '?')?>access=A">Администратор</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <? } ?>
              <a href="javascript:;" class="lnk-dot-blue" onclick="document.getElementById('admin_access_sw').style.display='block';this.parentNode.style.display='none'">Отмена</a>
            </li>
		</ul>
	</div>
</div>
