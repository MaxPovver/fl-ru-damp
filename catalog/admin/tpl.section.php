<?php $count_section = count($section['subsection']);?>
<div><b onclick="if(confirm('¬ы действительно хотите удалить раздел?')) xajax_deleteSection(<?= $section['id']?>, false)"class="seo-del"></b><b onclick="xajax_loadFormEdit(<?= $section['id']?>, false, <?= $direct_id ?>);" class="seo-edit"></b><b onclick="xajax_loadForm(<?= $section['id']?>, <?= $direct_id ?>);" class="seo-open"></b><?php if($count_section > 0) {?><i></i><?php }//if?><span class="pos_num_<?=$section['pos_num']?>"><?=$section['pos_num']?>.</span><?= $section['name_section']?> (<?= $count_section?>)</div>
<ul>
<?php if($count_section > 0) {?>
    <?php foreach($section['subsection'] as $i=>$subsection) {?>
    <li <?= ((($i+1) == $count_section)?'class="last"':'');?>><b onclick="if(confirm('¬ы действительно хотите удалить раздел?')) xajax_deleteSection(<?= $subsection['id']?>, <?= $section['id']?>)" class="seo-del"></b><b onclick="xajax_loadFormEdit(<?= $subsection['id']?>, <?= $section['id']?>, <?= $direct_id ?>);" class="seo-edit"></b><span><?= $section['pos_num']?>.<?=$i+1?>.</span><?= $subsection['name_section']?></li>
    <?php }// foreach?>
<?php } //if?>
</ul>