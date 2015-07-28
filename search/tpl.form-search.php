<? if($type == 'projects') { //if 1.2 0014574?>	 
<script>	 
kword = search_kwords;	 
</script>	 
<? } elseif($type != 'users') { //if 1.1  0014574?>	 
<script>	 
kword = kword.combine(search_kwords);	 
</script>	 
<? } ?>
<div id="search-form">
    <form action="/search/?type=<?= $type?>/" id="main-search-form" method="get">
       <div>
    
        <?php include($_SERVER['DOCUMENT_ROOT']."/search/tpl.search-menu.php");?>
        
        <input type="hidden" id="search-action" name="action" value="search" />
        <input type="hidden" name="type" value="<?= $type?>" />
        <?php if (isset($_SESSION['search_elms']) && isset($_SESSION['search_string']) && $_SESSION['search_string']): ?>
            <?php foreach ($_SESSION['search_elms'] as $key=>$total) { ?>
                <input type="hidden" id="search_elms_<?=$key?>" name="search_elms[<?=$key?>]" value="<?=$total?>" />
            <?php } ?>
        <?php endif; ?>
        <?php if(isset($_GET['only_tab'])) {?> 
        <input type="hidden" name="only_tab" value="1">
        <?php }//?>
        <div class="b-fon b-fon_bg_f2 b-fon_pad_10">
            
                <fieldset class="search-block">
                    <div class="flt-block <?= isset($_SESSION['search_advanced'][$type])?"":"last";?>">
                          <div class="b-search <?php if($view_advanced){ ?>b-search_margright_370<?php } else {?>b-search_margright_200<?php } ?> b-search_marg_null_iphone">
                                  <table class="b-search__table" cellpadding="0" cellspacing="0"><tr class="b-search__tr"><td class="b-search__input">
                                      <div id="body_1" class="b-input  b-input_height_24">
                                              <input id="search-request" class="b-input__text " placeholder="Например, <?=$search_input_hint?>" autocomplete="off"  name="search_string" type="text" value="<?= htmlspecialchars($search_string?str_replace($_SESSION['string_professions'], "", $search_string):"")?>" />
                                              <input id="search-hint" name="search_hint" type="hidden" value="<?=$search_input_hint?>">
                                      </div>
                                  </td><td class="b-search__button">
                                  <a id="search-button" class="b-button b-button_flat b-button_flat_grey b-button_margleft_5" href="javascript:void(0)">Найти</a>
                                  </td></tr></table>
                           </div>
                          <div class="search-ext">
							  <?php if($view_advanced){ ?><a href="javascript:void(0)" id="search-advanced-button"><?= $name_advanced_search;?></a><?php }//if?>
                              <?php include($_SERVER['DOCUMENT_ROOT']."/search/tpl.user-limit-block.php"); ?>
                          </div>
                    </div>
                </fieldset>
                
                <?php if(!$after_block) include($_SERVER['DOCUMENT_ROOT']."/search/".$search_advanced_tpl);?>
                
        </div>
       </div>
    </form>
</div><!--/#search-form-->
<?php if($after_block && $search_advanced_tpl) include($_SERVER['DOCUMENT_ROOT']."/search/".$search_advanced_tpl);?>
                