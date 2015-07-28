<?php

/**
 * Шаблон вариантов попапов
 * @todo: для реакции на нажания кнопок в попапе (data-popup... итп) здесь используется унифицированный механизм разработанный в bar_ext.js см popuper(); 
 */

$link = getFriendlyURL("project", array('id' => $project['id'], 'name' => $project['name']));
$name = reformat($project['name'], 30, 0, 1);
$kind = $project['kind'];
$is_show_popup = (bool)strpos($_SERVER["HTTP_REFERER"], "/registration/?from_prj={$project['id']}");

$project_type = $kind == 4 ? 'вакансию' : 'проект';
$url = $kind == 4 ? '/projects/?kind=4' : '/projects/';

$needs = array();
?>
<div id="project_answer_popup" class="b-shadow b-shadow_center b-shadow_width_450 b-shadow_pad_20 b-shadow_zindex_3 <?php if(!$is_show_popup): ?>b-shadow_hide<?php endif; ?>">

<?php if(($project['pro_only'] == 't' && !$is_pro) && ($project['verify_only'] == 't' && !$is_verify)): ?>
    
   <div  class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20">
       Для ответа на <?=$project_type?> "<a class="b-layout__link" href="<?=$link?>"><?=$name?></a>" вам необходимо приобрести аккаунт PRO и пройти верификацию.
   </div>
   <div class="b-buttons">
      <a data-popup-ok="true" href="/payed/" class="b-button b-button_flat b-button_flat_green">Купить аккаунт PRO</a>
      <a data-popup-ok="true" href="javascript: quickVerShow();" class="b-button b-button_flat b-button_flat_green">Верифицироваться</a>
      <div class="b-layout__txt b-layout__txt_padtop_10"><a href="<?=$url?>" class="b-layout__link">Посмотреть другие проекты</a></div>
   </div>
    
<?php elseif($project['verify_only'] == 't' && !$is_verify): ?>
    
   <div  class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20">
       Для ответа на <?=$project_type?> "<a class="b-layout__link" href="<?=$link?>"><?=$name?></a>" вам необходимо пройти верификацию.
   </div>
   <div class="b-buttons">
      <a data-popup-ok="true" data-popup-copy-attr="href" href="javascript: void(0);" class="b-button b-button_flat b-button_flat_green">Верифицироваться</a>
      <span class="b-layout__txt b-layout__txt_valign_middle"> &#160; <a href="<?=$url?>" class="b-layout__link">Посмотреть другие проекты</a></span>
   </div>
    
<?php else: ?>
    
    <div  class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20">
        Для ответа на <?=$project_type?> "<a class="b-layout__link" href="<?=$link?>"><?=$name?></a>" вам необходимо приобрести аккаунт PRO.
    </div>
    <div class="b-buttons">
        <a data-popup-ok="true" data-popup-copy-attr="href" href="javascript: void(0);" class="b-button b-button_flat b-button_flat_green">Купить аккаунт PRO</a>
        <span class="b-layout__txt b-layout__txt_valign_middle"> &#160; <a href="<?=$url?>" class="b-layout__link">Посмотреть другие проекты</a></span>
    </div>
    
<?php endif; ?>
    
    <span class="b-shadow__icon b-shadow__icon_close"></span>
</div> 