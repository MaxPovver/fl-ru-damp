  <h1 class="b-page__title">Фрилансеры <?=$rank?>-го разряда</h1>
        <? if($users) { ?>
          <table class="b-layout__table b-layout__table_width_full" border="0" cellspacing="0" cellpadding="0">
          <col style="width:33%"/>
          <col style="width:33%"/>
          <col style="width:33%"/>
          <? 
             $i=0;
             foreach ($users as $u) {
    
               if($i % $COLCNT == 0) {
                 if($i)
                   print('</tr>');
                 print('<tr class="b-layout__tr">');
               }
    
          ?>
            <td class="b-layout__one b-layout__one_padbot_30">
              <div class="b-user">
               <a class="b-user__link" href="/users/<?=$u['login']?>" title="<?=$u['uname']?> <?=$u['usurname']?>">
                   <?=view_avatar($u['login'], $u['photo'])?>
                  </a>
                      <?=view_user($u)?> 
                  <div class="b-user__txt"><?=$u['prof_name']?></div>
              </div>            
            </td>
        <?   
            $i++; } 
            if($i) print('</tr>');
          ?>
          </table>
          <style type="text/css">.b-icon__ver{ vertical-align:top;}</style>
        <? } ?>
        <?php
        $sHref = "%s?rank={$rank}&page=%d%s";
        $pages = ceil($count / $limit);
        echo new_paginator($page, $pages, 3, $sHref, true, "page");
        ?>
