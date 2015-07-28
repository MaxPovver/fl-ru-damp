<?php

$result['name'] = htmlspecialchars($result['name'], ENT_QUOTES, 'CP1251', false);
$result['descr'] = htmlspecialchars($result['descr'], ENT_QUOTES, 'CP1251', false);

if (!$top_projects) {
    list($name, $descr) = $element->mark(array((string)$result['name'], (string) $result['descr']));
} else {
    $name = $result['name'];
    $descr = $result['descr'];
}
$priceby_str = getPricebyProject($result['priceby']);
if($result['cost']=='' || $result['cost']==0) { $priceby_str = ""; }
$is_top = ($result["payed"] && date("Y.m.d H:i")<date("Y.m.d H:i",strtotime($result['top_to'])));
if($result['kind'] == 2 || $result['kind'] == 7) $result['offers_count'] -= $result['deleted_count'];
?>
<div class="search-lenta-item c <?= $result['is_color']=='t'?"colored":""?>">
    <span class="number-item"><?= $i?>.</span>
    <div class="search-item-body <?=($result['is_bold']=='t'?"prj-weight":"")?> <?=($result['prefer_sbr'] == 't'?"has_sbr":"")?> <?=($is_top?"has_top":"")?>">
        <?php if ($result['cost']) { ?>
        <span class="search-price"><?= CurToChar($result['cost'], (int)$result['currency']) ?><?= $priceby_str?></span>
        <?php } else { //if?>
        <span class="bujet-dogovor">По договоренности</span>
        <?php } //else?>
        <?if($result['logo_path'] != '') { ?>
        <? if ($result['link'] != "") { ?>
            <a href="http://<?= formatLink($result['link']) ?>" target="_blank">
                <img src="<?= WDCPREFIX.'/'.$result['logo_path'] ?>" alt="" class="sch-logo" />
            </a>
        <? } else { ?>
            <img src="<?= WDCPREFIX.'/'.$result['logo_path'] ?>" alt="" class="sch-logo" />
        <? } ?>
        <? }//if?>
        <h3>
        <?php if($is_top) { ?>
        <img class="search-tp" src="/images/tp.png" width="17" height="20">
        <?php }//if?>
        <?php /* #0019741 if($result['prefer_sbr'] == 't'){ ?>
        <img src="/images/sbr_p.png" class="sbr_p" title="Работодатель&nbsp;хочет&nbsp;работать&nbsp;через&nbsp;Cделку&nbsp;без&nbsp;риска" alt="Работодатель хочет работать через Cделку без риска">
        <?php } *///if?>
        <a href="<?=getFriendlyURL("project", $result['id'])?>"><?= reformat(strip_tags($name, "<em><br>"), 40, 0, 1);?></a></h3>
        <p><?= reformat(strip_tags(deleteHiddenURLFacebook($descr), "<em><br>"), 40, 0, 1)?></p>
        <?if($result['pro_only'] == 't' && $result['verify_only'] != 't') { ?>
        <ul class="project-info">
            <li><br />Только для <a class="b-layout__link" href="/payed/"><span title="владельцев платного аккаунта" alt="владельцев платного аккаунта" class="b-icon b-icon__pro b-icon__pro_f"></span></a></li>
        </ul>
        <?php } elseif($result['verify_only'] == 't' && $result['pro_only'] != 't') { ?> 
        <ul class="project-info">
            <li><br />Только для<?=view_verify() ?></li>
        </ul>
        <?php } elseif($result['verify_only'] == 't' && $result['pro_only'] == 't') { ?> 
        <ul class="project-info">
            <li><br />Отвечать на проект могут только пользователи с аккаунтом <?=view_pro()?> и верифицированным аккаунтом<?=view_verify() ?></li>
        </ul>
        <?php }//if?>
        
        <div class="search-meta-bl"> 
            <?php if($result['exec_id'] > 0) {?>
            <span class="search-answer"><a href="<?= getFriendlyURL("project", $result['id']) ?>"><?= ($result['kind'] == 7 || $result['kind'] == 2 ?"Победитель":"Исполнитель")?> определён</a></span>
            <?php } else {?>
            <span class="search-answer"><a href="<?= getFriendlyURL("project", $result['id']) ?>"><?=project_status_link($result['kind'], $result['offers_count'])?></a></span>
            <?php }//else?>
            <ul class="search-meta">
                <?php if($result['payed'] && ($result['kind'] != 2 && $result['kind'] != 7)) { ?><li><strong>Платный проект</strong></li><?php } //if?>
                <?php if($result['kind'] == 2 || $result['kind'] == 7) { ?>
                <li class="red">Конкурс</li>
                <?php } else if ($result['kind'] == 4) { //if?>
                <li class="pi-office">Вакансия <?= (($result['country']) ? "(".$result['country_name'] . (($result['city']) ? ", " . $result['city_name'] : "" ) . ")" : "") ?></li>
                <?php } //else if?>
                <?php if($result['kind'] == 2 || $result['kind'] == 7) { ?>
                    <?if(strtotime($result['end_date']) > time()) { ?>
                    <li>до окончания осталось: <?= ago_pub_x(strtotime($result['end_date']), "ynjGx") ?></li> 
                    <? } else {?>
                    <li>завершен</li>
                    <? }?>
                <?php } else { //if?>
                    <li><?= ago_pub_x(strtotime($result['create_date']), "ynjGx", 0, true) ?></li>
                <?php }//else?>
            </ul>
        </div>
    </div>
</div><!--/search-lenta-item-->