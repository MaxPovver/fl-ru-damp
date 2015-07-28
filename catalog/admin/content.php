<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/seo.common.php");
$xajax->printJavascript('/xajax/');
?>
<script>
CKEDITOR.config.customConfig = '/scripts/ckedit/config_simple.js';
</script>
<a name="top"></a> 
<h1>Администрирование (<?= $seo->subdomain['name_subdomain']?>)</h1>
<table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top: 10px;"> 
  <tbody>
    <tr> 
	  <td valign="top" height="400" bgcolor="#FFFFFF" class="box2"> 
        <div class="seo-admin">

          <div class="seo-right" id="form_content">
            <?php include "tpl.form-main.php";?>
          </div><!--seo-right-->
          <div class="seo-left">
          <form id="form-filter" method="get" action="">
            <div >Направление: 
                <select name="direction" onchange="$('form-filter').submit()">
                    <option value="-1">-- не указано</option>
                    <?php if($directions) foreach($directions as $row) { ?>
                    <option value="<?= $row['name_section_link']?>" <?= ($direct_id == $row['id'])?'selected="selected"':''?>><?= $row['dir_name']?></option>
                    <?php } //foreach?>
                </select>
                    <div class="seo-open" style="display:inline-block; cursor:pointer; width:15px;" onclick="xajax_loadDirectForm();">&nbsp;</div>
                    <? if ($direct_id && $direct_id != -1) { ?>
                    <div class="seo-edit" style="display:inline-block; cursor:pointer; width:15px;" onclick="xajax_loadDirectForm(<?= $direct_id ?>);">&nbsp;</div>
                    <div class="seo-del" style="display:inline-block; cursor:pointer; width:15px;" onclick="if (confirm('Уверены?')) xajax_deleteDirection(<?= $direct_id ?>);">&nbsp;</div>
                    <? } ?>
            </div><br/>
            <div >Перейти в регион: 
<!--                <select name="subdomain" onchange="location.href='/catalog/admin/?subdomain=' + this.value;">-->
                <select id="f_region" name="subdomain" onchange="if($('f_region').get('value')!='') { $('form-filter').submit(); }">
                  <option value="all" <?=(($seo->subdomain['id'] == -1)?'selected="selected"':'')?>>Все</option>
                  <?php
                  foreach($countries as $country) {
                    $country_options = "<option value=''>{$country['country_name']}</option>";
                    foreach($subdomains as $key=>$subdomain) {
                      if($subdomain['country_id']!=$country['id']) continue;
                      $country_options .= "<option value='{$subdomain['subdomain']}' ".(($seo->subdomain['id'] == $subdomain['id'])?'selected="selected"':'').">&nbsp;&nbsp;{$subdomain['name_subdomain']}</option>";
                    }
                    $subdomain_options .= $country_options;
                  }
                  ?>
                  <?=$subdomain_options?>
                </select>
            </div><br/>
            <? if ($direct_id != -1) { ?>
            <span class="add-new"><a href="javascript:void(0)" onclick="xajax_loadForm(false, <?= $direct_id ?>);"><i></i>Добавить новый раздел</a></span>
            <? } ?>
            <ul id="section_content">
              <?php if($sections) foreach($sections as $k=>$section) { ?>
                <li id="section_<?= $section['id']?>" <?=(in_array($section['id'], $activeItems) ? 'class="active"' : '')?>><?php include("tpl.section.php");?></li>
              <?php } //foreach?>
            </ul>
          </form>
          </div><!--seo-left-->
          <?php if($seo->subdomain['id'] == 0) { reset($subdomains); $s = current($subdomains); ?>
          <script type="text/javascript">xajax_loadMainForm(<?=$s['id']?>, '<?=$is_save?>', '<?=$msgtext?>');</script>
          <?php } //if?>          
          
        </div>
      </td>
    </tr> 
  </tbody>
</table>	