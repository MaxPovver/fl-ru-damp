<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
  <div class="b-shadow b-shadow_pad_20 b-shadow_width_760 b-shadow_zindex_3" id="ov-notice4" style="display: none;">
                <?php /*
             	<a class="close" href="javascript:void(0);" onclick="adminLogOverlayClose();"><img height="21" width="21" alt="" src="/images/btn-close.png"></a>
             	*/ ?>
             	<h4>Список предупреждений <a id="a_user_warns" href="#"><span id="s_user_warns"></span></a> (<span id="e_user_warns"></span>/<span id="n_user_warns"></span>)</h4>
             	<div id="d_user_warns"></div>
                
              	<div id="b_user_warns" class="bun-button">
              		<button>Забанить</button>
				</div>
 </div>