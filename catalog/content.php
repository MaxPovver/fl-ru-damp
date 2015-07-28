<div class="b-layout__right b-layout__right_relative b-layout__right_width_72ps b-layout__right_float_right">
  <div class="b-post__txt">
    <?php if($section_direction) {?>
    <h2><?= reformat($section_direction['dir_name']) ?></h2>
    <?= reformat($section_direction['page_content'],100,0,-1) ?>
    <?php } elseif (!$section_content) { //if?>
    <h2>Информационный раздел</h2>
    <?= reformat($seo->subdomain['content'],100,0,-1); // @todo вроде бы эту инфу надо выводить, пока не точно.?>
    <?php } else { //if?>
        <h2><?= $section_content['name_section']?></h2>
        <?php $GLOBALS['disable_link_processing'] = true; ?>
        <?= reformat($section_content['content_before'], 100,0,-1);?>
        <?php $GLOBALS['disable_link_processing'] = false; ?>
        <?php if($dinamic_content !== false && is_array($dinamic_content)) { ?>
        <div class="seo-best">
            <div class="form fs-o">
                <b class="b1"></b>
                <b class="b2"></b>
                <div class="form-in">
                    <h3 class="seo-title">Фрилансеры по данному направлению</h3>
                    <style type="text/css">
                    	.form-in .b-icon__shield {top: 3px;}
						@media screen and (min-width:0\0) {
							.form-in .seo-best-item .b-icon__shield {top: 2px;}
						}
                    </style>
                    <?php $n=0; foreach($dinamic_content as $key => $dContent) { $n++; $info_for_reg = @unserialize($dContent['info_for_reg']);?>
                    <div class="seo-best-item">
                        <?= view_avatar($dContent['login'], $dContent['photo']);?>
                        <h4><a href="/users/<?= $dContent['login']?>/"><?= $dContent['uname']." ".$dContent['usurname']." [{$dContent['login']}]"?></a><?= view_mark_user(array('login'=>$dContent['login'], 'is_pro' => true, 'role'=> $dContent['role'], 'is_team'=>$dContent['is_team']))?>
                        &#160;<?= $dContent['completed_sbr_cnt'] ? view_sbr_shield() : '' ?></h4>
                        <p>На сайте: <?= ElapsedMnths(strtotime($dContent['reg_date']))?></p>
                        <p>
                        <?php 
                        if(!$info_for_reg['country'] || get_uid(false)) {
                            if(intval($dContent['country'])!=0)  {
                              print country::GetCountryName($dContent['country']);
                              if (($dContent['city'] && !($info_for_reg['city'])) || get_uid(false)) {
                                  if(intval($dContent['city'])!=0) {
                                    print ", ".city::GetCityName($dContent['city']); 
                                  }
                              } 
                            }
                        } //else
                        ?>
					    </p>
                    </div>
                    <?php
                    if($n==3) {
                      echo '<div style="clear:both;">&nbsp;</div>';
                      $n=0;
                    }
                    ?>
                    <?php }//foreach?>
                </div>
                <b class="b2"></b>
                <b class="b1"></b>
             </div>
        </div>
        <?php }//if?>
        
        <?php if($dinamic_content_articles !== false && !($dinamic_content !== false && is_array($dinamic_content))) { ?>
        <div class="block-seo-links">
            <div class="form fs-o">
                <b class="b1"></b>
                <b class="b2"></b>
                <div class="form-in">
                    <ul>
                        <?php for($i=0;$i<count($dinamic_content_articles);$i++) { if($i==3) { $continue = true; break; }?>
                        <li><a href="<?=getFriendlyURL('article', $dinamic_content_articles[$i]['article_id'])?>"><?= reformat($dinamic_content_articles[$i]['title'], 25, 0, 1)?></a></li>
                        <?php }//endfor;?>
                    </ul>
                    <?php if($continue) { ?>
                    <ul>
                        <?php for($i=3;$i<count($dinamic_content_articles);$i++) {?>
                        <li><a href="<?=getFriendlyURL('article', $dinamic_content_articles[$i]['article_id'])?>"><?= reformat($dinamic_content_articles[$i]['title'], 25, 0, 1)?></a></li>
                        <?php }//for?>
                    </ul>
                    <?php }//if?>                 
                </div>
            <b class="b2"></b>
            <b class="b1"></b>
            </div>
        </div>
        <?php }//if?>
        <?php $GLOBALS['disable_link_processing'] = true; ?>
        <?= reformat($section_content['content_after'],100,0,-1);?>
        <?php $GLOBALS['disable_link_processing'] = false; ?>
    <?php } //else?>
  </div>
</div>
<style type="text/css">.b-icon__ver, .b-icon__shield{ position:relative; top:2px;}</style>
<div class="b-layout__left b-layout__left_width_25ps">
  <div class="seo-block">
    <form id="region_frm" action="" method="get">
        <? if ($direct_id) { ?>
        <input id="f_direction" name="direction" type="hidden" value="<?= $direct_link ?>"/>
        <? } ?>
      <fieldset>
        <div class="form-el">
          <div class="form-value">
            <script type="text/javascript">var host = '<?=preg_replace('~^'.HTTP_PREFIX.'(www\.)?~',"",$host)?>'; var allHost = '<?=(preg_match('~'.HTTP_PREFIX.'www\.~', $host)? 'www.': '')?>' + host; </script>
            <select id="f_region" name="subdomain" onchange="if($('f_region').get('value') != '') { if($('f_region').get('value') == 0) {this.set('disabled', true);}; if($('f_region').get('value') == 'all') { url = '<?=HTTP_PREFIX?>'+allHost+'/catalog/'; } else { url = '<?=HTTP_PREFIX?>'+$('f_region').get('value')+'.'+host+'/catalog/'; }; if($('f_direction')) { url = url+$('f_direction').get('value')+'/'; }; window.location = url; }">
                  <option value="all" <?=(($seo->subdomain['id'] == -1)?'selected="selected"':'')?>>Все</option>
                  <?php
                  $subdomains = $seo->getSubdomainsByDirectID($direct_id);
                  foreach($countries as $country) {
                    $country_options = "<option value=''>{$country['country_name']}</option>";
                    $n = 0;
                    foreach($subdomains as $key=>$row) {
                      if($row['country_id']!=$country['id']) continue;
                      $country_options .= "<option value='{$row['subdomain']}' ".(($seo->subdomain['id'] == $row['id'])?'selected="selected"':'').">&nbsp;&nbsp;{$row['name_subdomain']}</option>";
                      $n = 1;
                    }
                    if($n) {
                      $subdomain_options .= $country_options;
                    }
                  }
                  ?>
                  <?=$subdomain_options?>

            </select>
		  </div>
		  <label class="form-l">Регион:</label>
        </div>
      </fieldset>
    </form>
  </div>

<?
//echo '<pre>'; var_dump($section_content); echo '</pre>';
//echo '<pre>'; var_dump($sections); echo '</pre>';
?>    
    <div class="m-cat-main c">
    <b class="b1"></b>
    <b class="b2"></b>
      <ul id="accordion">
      <? foreach ($directions as $direction) { ?>
          <li class="all-employers"><span class="wrap-item"><a 
                      href="<?=seo::getFriendlyURL($subdomain, $direction['name_section_link'], '');?>"><?= reformat($direction['dir_name']) ?></a></span></li>
      <?php $grnum = 0; if($sections[$direction['id']]) foreach($sections[$direction['id']] as $key=>$section) { if(!$section['subsection']) { continue; } $count_section = count($section['subsection']);?>
        <li>
          <a class="toggler" href="javascript:void(null);" onclick=""><?=$section['name_section']?></a>  
          <ul class="element" id="submenu<?=$section['id']?>">
          <?php if($count_section > 0) { $k=0;?>
            <?php foreach($section['subsection'] as $i=>$subsection) { if($section_content['id'] == $subsection['id'] || $section['id']==$catid) $gr_show = $grnum;?>
            <?php if($k==0) { ?><script type="text/javascript">initCI('submenu<?=$section['id']?>')</script><?php }//if?>
            <?/*@todo необходимо сделать переход сразу на поддомен вида http://subdomain.free-lance.ru/catalog/section/123/ если мы находимся во всех регионах*/?>
            <li <?= ($section_content['id'] == $subsection['id']?'class="active"':'');?>> <a href="<?=seo::getFriendlyURL($subdomains[$subsection['subdomain_id']]['subdomain'], $direction['name_section_link'], $subsection['name_section_link'], $section['name_section_link'])?>"><?= $subsection['name_section']?></a></li>
            <?php $k++;}// foreach?>
          <?php } //if?>
          </ul>
        </li>
      <?php $grnum++;} //foreach?>
      <? } ?>
      <?php if($direct_id) { ?>
      <li class="all-employers"><span class="wrap-item"><a href="<?=seo::getFriendlyURL($subdomain, '', '');?>">Все направления</a></span></li>
      <?php } ?>
      </ul>
    <b class="b2"></b>
    <b class="b1"></b>
    </div>
    <script type="text/javascript">asynccall('initCtg(<?=(isset($gr_show)?$gr_show:-1)?>)');</script>

  
  <div class="banner_240x400"></div>                         
</div>

