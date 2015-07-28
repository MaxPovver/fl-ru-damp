<?php
if(!defined('IN_STDF')) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 * Представление для виджета TServiceCatalogPromo
 *
 *
 * @var TServiceCatalogPromo $this
 * @var users $user
 */

//Текущий пользователь фрилансер?
$is_frl = (get_uid(false) && !is_emp());

?>
<?php /*
<div class="b-pic-tu-banner">
<div class="b-pic-tu-banner__img">
    <div class="b-pic-tu-banner__content">
      <?php if($is_frl){ ?>
        <p>
            Новый раздел FL.ru с фиксированными услугами по фиксированной цене
        </p> 
        <br/>
        <a class="b-pic-tu-banner__content-btn" href="<?php echo tservices_helper::new_url()?>" onClick="yaCounter6051055.reachGoal('add_new_tu');">Опубликовать услугу</a>  
      <?php }else{ ?>
        <p class="b-txt_margbot_null">
            Новый раздел FL.ru с фиксированными услугами по фиксированной цене
        </p>
      <?php } ?>
    </div>
    <div class="b-pic-tu-banner__cloud">
      Сочиню песню про что угодно и кого угодно
      <p class="b-pic-tu-banner__cloud-price">за 600 р. и 2 дня</p>
      <div class="b-pic-tu-banner__cloud-corner"></div>
    </div>
</div>
</div>
*/ ?>