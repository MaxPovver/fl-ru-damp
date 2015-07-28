<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/xajax/masssending.common.php';
$xajax->printJavascript('/xajax/');

?><script type="text/javascript">
	
	<?=(empty($params['sbr'])? "var vsbr = 0; // sbr rank spoiler \n": "var vsbr = 1; \n")?>
	var exrate = 1;//<?=$exrates[14]?>;
	var mysum = <?=round($_SESSION['ac_sum'],2)?>;
	
	var profs = [ ];
	<?
	$gr = 0;
	for ($i=0; $i<count($professions); $i++) {
		if ($gr != $professions[$i]['prof_group']) {
			echo "\tprofs[{$professions[$i]['prof_group']}] = [];\n";
			$gr = $professions[$i]['prof_group'];
		}
		echo "\tprofs[{$professions[$i]['prof_group']}][ profs[{$professions[$i]['prof_group']}].length ] = { id: {$professions[$i]['id']}, name: '".trim($professions[$i]['name'])."' };\n";
	}
	?>

</script>
<script type="text/javascript">
<? 
// восстанавливаем массивы с данными, если была сессия

if (!empty($params['locations'])) {
	$tmp = array();
	foreach ($params['locations'] as $val) {
		if ($val['country']['id']) {
			$tmp[] = "locations.values[locations.values.length] = {
				country: { id: {$val['country']['id']}, name: '".htmlspecialchars($val['country']['name'])."' },
				city: { id: ".intval($val['city']['id']).", name: '".(intval($val['city']['id'])? htmlspecialchars($val['city']['name']): '')."' },
				count: {$val['count']},
				cost: {$val['cost']}
			}\n";
		}
	}
	for ($i=0; $i<count($tmp)-1; $i++) echo $tmp[$i];
}

if (!empty($params['professions'])) {
	$tmp = array();
	foreach ($params['professions'] as $val) {
		if ($val['group']['id']) {
			$tmp[] = "professions.values[professions.values.length] = {
				group: { id: {$val['group']['id']}, name: '".htmlspecialchars($val['group']['name'])."' },
				profession: { id: ".intval($val['profession']['id']).", name: '".(intval($val['profession']['id'])? htmlspecialchars($val['profession']['name']): '')."' },
				count: {$val['count']},
				cost: {$val['cost']}
			}\n";
		}
	}
	for ($i=0; $i<count($tmp)-1; $i++) echo $tmp[$i];
}
?>
</script>
<div class="b-menu b-menu_crumbs">
    <ul class="b-menu__list">
        <li class="b-menu__item"><a href="/service/" class="b-menu__link">Все услуги</a>&nbsp;&rarr;&nbsp;</li>
    </ul>
</div>
<h1 class="b-page__title">Рассылка по каталогу</h1>
<div class="b-menu b-menu_line">
    <ul class="b-menu__list">
        <li class="b-menu__item<?= $fromSearch ? '' : ' b-menu__item_active' ?>"><a href="/masssending/" class="b-menu__link">Простая рассылка</a></li>
        <li class="b-menu__item b-menu__item_last<?= $fromSearch ? ' b-menu__item_active' : '' ?>"><a href="/masssending/?from_search=1" class="b-menu__link">Из поиска</a></li>
    </ul>
</div>
<div class="masssending-block c">
<form id="frm" action="<?=$_SERVER['REQUEST_URI']?>" method="POST">
	<div class="masssending-content">
        <? if ($fromSearch === 0) {
            include($_SERVER['DOCUMENT_ROOT'] . "/masssending/tpl.masssending.php");
        } elseif ($fromSearch === 2) {
            include($_SERVER['DOCUMENT_ROOT'] . "/masssending/tpl.masssending_from_search.php");
        } else {
            include($_SERVER['DOCUMENT_ROOT'] . "/masssending/tpl.masssending_from_search_new.php");
        }
        ?>
	</div>

</form>
</div>

<script type="text/javascript">


var MultiFileSetting = {
    input: document.getElementById('mf-file'),
    files: document.getElementById('mf-files-list'),
    load:  document.getElementById('mf-load'),
    backend: '/masssending/upload.php',
    maxfiles: <?=masssending::MAX_FILES?>,
    sessTTL: <?=masssending::SESS_TTL?>
}
			
var i = 0;
var uploaded = [ ];
<?
if (!empty($_COOKIE['mass-files']) && !empty($_SESSION['masssending']['files']) && is_array($_SESSION['masssending']['files'])) {
    $files = explode(',', $_COOKIE['mass-files']);
    for ($i=0; $i<count($files); $i++) {
        foreach ($_SESSION['masssending']['files'] as $file) {
            if ($files[$i] == $file['id']) {
                $filename = addslashes($file['displayname']);
                if(strlen($filename) > 45) {
                    $filename = substr($filename, 0, 30) . "..." . substr($filename, strlen($filename)-10, strlen($filename));
                }
                echo "
                    uploaded[i++] = { 
                        id: {$file['id']}, 
                        filename: '".htmlspecialchars($file['filename'], ENT_QUOTES)."',
                        displayname: '".$filename."',
                        filetype: '".htmlspecialchars($file['filetype'], ENT_QUOTES)."',
                    };";
            }
        }
    }
}
?>

new MultiFile(MultiFileSetting, uploaded);

<?
$i = 0;
if (!empty($params['costs'])) {
    foreach ($params['costs'] as $val) {
        if (intval($val['cost_from']) || intval($val['cost_to'])) {
?>
            costs.add( { cost_from: <?=intval($val['cost_from'])?>, cost_to: <?=intval($val['cost_to'])?>, cost_period: '<?=$val['cost_period']?>', cost_type: <?=intval($val['cost_type'])?> } );
<?
            $i++;
        }
    }
}
if (!$i) echo "costs.add();";
?>

</script>

<? if (!empty($params) && !$fromSearch) { ?>
<script type="text/javascript">

if ( document.getElementById('sbr_is_positive') ) {
    spam.values.sbr_is_positive = document.getElementById('sbr_is_positive').checked;
}
if ( document.getElementById('sbr_not_negative') ) {
    spam.values.sbr_not_negative = document.getElementById('sbr_not_negative').checked;
}
spam.values.opi_is_positive = document.getElementById('opi_is_positive').checked;
spam.values.opi_not_negative = document.getElementById('opi_not_negative').checked;
spam.values.favorites = document.getElementById('favorites').checked;
spam.values.free = document.getElementById('free').checked;
spam.values.portfolio = document.getElementById('portfolio').checked;
spam.values.sbr = document.getElementById('sbr_main_check') && document.getElementById('sbr_main_check').checked;
spam.values.discharge1 = document.getElementById('sbr_check1') && document.getElementById('sbr_check1').checked;
spam.values.discharge2 = document.getElementById('sbr_check2') && document.getElementById('sbr_check2').checked;
spam.values.discharge3 = document.getElementById('sbr_check3') && document.getElementById('sbr_check3').checked;
spam.values.inoffice = document.getElementById('inoffice').checked;
spam.values.is_pro = document.getElementById('chk-pro').checked;
spam.values.expire_from = document.getElementById('expire_from').value;
spam.values.expire_to = document.getElementById('expire_to').value;
spam.values.msg = document.getElementById('msg').value;

spam.send();

</script>
<? } ?>
