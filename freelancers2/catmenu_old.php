<style type="text/css">
.prf-this{
	color:#89D363 !important;
	white-space:nowrap;
	font-size:10px !important;
	display:inline !important;
	float:none !important;
	}
.prf-this span{
	text-decoration:underline;
	display:inline !important;
	float:none !important;
	font-size:10px !important;
	}
#menu_active .prf-this{
	color: #039 !important;
	}
#menu_active .prf-this span{
	color: #039 !important;
	}
</style>
<h2 class="fl2_header" style="margin-bottom:5px">Каталог</h2>
<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/firstpage.php");
$pUStat = firstpage::ShowStats(); 
?>
<span style="color: #333; font-family: Tahoma, sans-serif;"> <b style="color:#6BB24B"><?=$pUStat['u']['count']?></b> <?=$pUStat['u']['phrase']?><br/></span>
<? if(!$_SESSION['uid'] || (is_emp() && !$_SESSION['anti_uid'])) { ?>
  <div class="fl2_register_to_do">
    <a href="/registration/?type=frl" class="org">Зарегистрироваться</a> как фрилансер
  </div>
<? } ?>
<div id="fl2_sidemenu">
	<div class="all_freelancers" style="background-color: #6BB24B; ">
		<span><?=$gr_count['-1']?></span><a href="<?=($_SERVER['PHP_SELF'] == '/search/index.php') ? "/employers/" : "/employers/"?>" class="menu_link">Работодатели</a>
	</div>
	<div class="all_freelancers" style="background-color: #6BB24B; ">
		<span><?=$gr_count['-1']?></span><a href="<?=($_SERVER['PHP_SELF'] == '/search/index.php' ) ? "/freelancers/" : "/freelancers/"?>" class="menu_link">Все фрилансеры</a>
	</div>
<?
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
	$prfs = new professions();
	$profs = $prfs->GetAllProfessions("",0, 1);
    $spec_orig = null;
    $specs = $specs_add = array();
	if ($uid && !is_emp()) {
    if($specs = professions::GetProfessionsByUser($uid, FALSE))
      $specs = professions::GetMirroredProfs(implode(',', $specs));
	}
	$cur_prof = NULL;
	$iter = 0;
	$p_size = sizeof($profs);
	$prof = $profs[$iter++];
	$grnum = 0;
	while ($iter <= $p_size){
		if (!$prof) break;
		$lastgrname = $prof['groupname'];
		if (!$lastgrname) break;
		$proj_groups[] = array('name' => $lastgrname, 'id' => $prof['groupid']);
		$num = 1; ?>
		<div class="display gr<?=$prof['groupid']?>">
		<!--<span><?=$gr_count[$prof['groupid']]?></span>--><a href="javascript:void(null);" class="menu_link" title="<?=$prof['groupname']?>"><?=$prof['groupname']?></a>
		</div><div class="menu_content">
				<? do {
				    $in_spec = ($uid && ((is_array($specs) && in_array($prof['id'], $specs)))); ?>
			<div><a href="/freelancers/<?=$prof['link']?>/" title="<?=htmlspecialchars($prof['profname'])?>" <? if ($prof['id'] == $prof_id) { ?>id="menu_active"<? } ?>><span><?=$prof['count']?></span><?=$prof['profname']?><? if ($in_spec) { ?>&nbsp;<span class="prf-this">&larr;&nbsp;<span>Вы&nbsp;здесь</span></span><? } ?></a></div>
				<?
					if ($prof['id'] == $prof_id) {
					  $cur_prof = $prof;
						$group_id = $prof['groupid'];
						$gr_init_num = $grnum;
					}
					$prof = $profs[$iter++];
					$num++;
				} while ($lastgrname == $prof['groupname']) ?>
		</div>
		<?
		$grnum++;
	} ?>
</div>
<br>
<script type="text/javascript">
  new Fx.Accordion($('fl2_sidemenu_ch'), 'div.display', 'div.menu_content',
    { opacity: false, alwaysHide: true, show: <?=(isset($gr_init_num))?$gr_init_num:"-1"?>, duration: 400,
      onActive: function(toggler, element) { toggler.setStyle('backgroundImage', "url('/images/white-arrowd.gif')"); },
      onBackground: function(toggler, element) { toggler.setStyle('backgroundImage', "url('/images/white-arrow.gif')"); } 
    });
  document.getElementById('fl2_sidemenu').style.visibility = 'visible';
</script>
