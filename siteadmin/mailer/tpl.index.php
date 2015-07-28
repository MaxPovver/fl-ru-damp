<div class="b-layout">
    <a href="/siteadmin/mailer/?action=digest" class="b-button b-button_flat b-button_flat_green b-button_float_right b-button_margleft_10">Новый дайджест</a>
    <a href="/siteadmin/mailer/?action=create" class="b-button b-button_flat b-button_flat_green b-button_float_right">Новая рассылка</a>
    <h2 class="b-layout__title b-layout__title_padbot_30">Рассылки</h2>
		
    <?php include("tpl.filter.php"); ?>
    
    <?php if($list_mailer) { ?>
        <?php include ("tpl.list.php"); ?>
    <?php } else {//if?>
        <strong>Рассылок не найдено</strong>
    <?php }//else?>
    
    <?= new_paginator($page, $pages, 3, "%s?".urldecode(url('emp,frl,from,to,sending,users,draft,regular,pause,sort,digest,mailer', array('page' => '%d')))."%s")?>

</div>